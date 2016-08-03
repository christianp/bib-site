<?php
require_once('arxiv-info.php');
global $BIB;

if($_SERVER['REQUEST_METHOD']=='GET') {
	$defaults = array_merge($_GET,array('type'=>'article'));
	if(preg_match('#^https?://arxiv.org/abs/(?P<id>\d+\.\d+|\w+/\d{7})#',$_GET['url'],$matches)) {
		$defaults = get_arxiv_info($matches['id']);
	}
} else {
	$defaults = array();
}
$form = new Form(
	array(
		'type' => array(
			'type' => 'select',
			'required'=>'true',
			'options' => array(
				'article' => 'Article',
				'book' => 'Book',
				'online' => 'Web page',
				'misc' => 'Miscellaneous'
			)
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
		'author' => array('type'=>'text'),
		'abstract' => array('type'=>'textarea'),
		'comment' => array('type'=>'textarea'),
        'extra_fields' => array(
            'fields' => array(
                'name' => array('type'=>'text'),
                'value' => array('type'=>'text')
            ),
            'multi'=>true
        )
	),
	$defaults
);

function render_form($form) {
	render('new_entry.html',array('form'=>$form));
}

if($_SERVER['REQUEST_METHOD']=='GET') {
	if(!$form->data['key']) {
		$form->data['key'] = preg_replace('/\W/','',$form->data['title']);
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
                'author' => $form->cleaned_data['author'],
                'comment' => $form->cleaned_data['comment'],
				'urldate' => date('Y-m-d')
			)
        );
        foreach($form->cleaned_data['extra_fields'] as $field) {
            $entry->fields[$field['name']] = $field['value'];
        }
        $BIB->db->records[$entry->key] = $entry;
        $BIB->save_database();
        redirect(reverse('view_entry',array('key'=>$entry->key)));
	}
}
