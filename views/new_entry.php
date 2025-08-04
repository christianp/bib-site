<?php
require_once('arxiv-info.php');
global $BIB;
use Cocur\Slugify\Slugify;

if($_SERVER['REQUEST_METHOD']=='GET') {
	$defaults = array_merge($_GET,array('type'=>'article'));
	if(preg_match('#^https?://arxiv.org/abs/(?P<id>\d+\.\d+|\w+/\d{7})#',$_GET['url'],$matches)) {
		$defaults = get_arxiv_info($matches['id']);
    } else if(strlen($_GET['url'])>0) {
        $defaults = get_citation_info($_GET['url']);
    }
} else {
	$defaults = array();
}
foreach($defaults as $k=>$v) {
    if(is_string($v)) {
        $defaults[$k] = html_entity_decode($v);
    }
}
$defaults['collections'] = array();

$form = new Form(
	array(
		'type' => array(
			'type' => 'select',
			'required'=>'true',
			'options' => $BIB->type_options
		),
        'key' => array(
            'type'=>'text',
            'required'=>'true',
            'clean' => function($value) {
                return preg_replace('/[^a-zA-Z0-9]/','',$value);
            }
        ),
		'title' => array('type'=>'text','required'=>'true'),
		'url' => array('type'=>'text'),
		'year' => array('type'=>'text'),
		'author' => array('type'=>'text'),
		'abstract' => array('type'=>'textarea'),
		'comment' => array('type'=>'textarea'),
        'extra_fields' => array(
            'fields' => array(
                'name' => array('type'=>'text'),
                'value' => array('type'=>'text')
            ),
            'multi'=>true
		),
		'collections' => array(
			'type' => 'checkbox',
			'options' => array_map(function($collection){ return $collection->name;},$BIB->collections)
        )
	),
	$defaults
);

function render_form($form) {
	render('new_entry.html',array('form'=>$form));
}

if($_SERVER['REQUEST_METHOD']=='GET') {
	if(!get($form->data,'key','')) {
		$form->data['key'] = preg_replace('/\W/','',get($form->data,'title',''));
	}
	render_form($form);
} else {
    $form->clean();
	if($form->cleaned_data['title']==='' || $form->cleaned_data['key']==='') {
		render_form($form);
	} else {
		$entry = new BibEntry(
			$form->cleaned_data['type'],
			$form->cleaned_data['key'],
			array(
				'title' => $form->cleaned_data['title'],
				'abstract' => $form->cleaned_data['abstract'],
				'url' => $form->cleaned_data['url'],
				'year' => $form->cleaned_data['year'],
                'author' => $form->cleaned_data['author'],
                'comment' => $form->cleaned_data['comment'],
				'urldate' => date('Y-m-d')
			)
        );
        foreach($form->cleaned_data['extra_fields'] as $field) {
            $entry->fields[$field['name']] = $field['value'];
        }
		$entry->fields['collections'] = implode(",",array_map(function($collection_name) {
			$slugify = new Slugify();
			return $slugify->slugify($collection_name);
		},$form->cleaned_data['collections']));
        $BIB->db->records[$entry->key] = $entry;
        $BIB->save_database();
        redirect(reverse('view_entry',array('entry_key'=>$entry->key)));
	}
}
