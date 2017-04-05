<?php
global $BIB;
use Cocur\Slugify\Slugify;

$entry = get_entry($entry_key);

$defaults = array(
    'type' => $entry->type,
    'key' => $entry->key,
    'title' => $entry->title,
	'author' => $entry->author,
	'abstract' => get($entry->fields,'abstract',''),
	'comment' => get($entry->fields,'comment',''),
    'url' => implode(" ",$entry->urls),
	'urldate' => get($entry->fields,'urldate',''),
    'extra_fields' => array(),
	'collections' => array_map(function($c){return $c->name;},$BIB->entry_collections($entry))
);
foreach($entry->fields as $name=>$value) {
    if(!in_array($name,array('title','author','url','abstract','comment','urldate','collections'))) {
        $defaults['extra_fields'][] = array('name'=>$name,'value'=>$value);
    }
}

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
		'urldate' => array('type'=>'text'),
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
	global $BIB;
	echo $BIB->twig->render('edit_entry.html',array('form'=>$form));
}

if($_SERVER['REQUEST_METHOD']=='GET') {
	render_form($form);
} else {
    $form->clean();
	if($form->cleaned_data['title']==='' || $form->cleaned_data['key']==='') {
		render_form($form);
    } else {
        $newkey = $form->cleaned_data['key'];

        if($newkey !== $entry->key && isset($BIB->db->records[$newkey])) {
            render_form($form);
            die();
        }

        unset($BIB->db->records[$entry->key]);
        $entry->type = $form->cleaned_data['type'];
        $entry->key = $form->cleaned_data['key'];
        $entry->fields = array();
        $entry->fields['title'] = $form->cleaned_data['title'];
        $entry->fields['author'] = $form->cleaned_data['author'];
        $entry->fields['url'] = $form->cleaned_data['url'];
        $entry->fields['urldate'] = $form->cleaned_data['urldate'];
        $entry->fields['abstract'] = $form->cleaned_data['abstract'];
        $entry->fields['comment'] = $form->cleaned_data['comment'];
        foreach($form->cleaned_data['extra_fields'] as $field) {
            $entry->fields[$field['name']] = $field['value'];
		}
		$entry->fields['collections'] = implode(",",array_map(function($collection_name) {
			$slugify = new Slugify();
			return $slugify->slugify($collection_name);
		},$form->cleaned_data['collections']));

        $BIB->db->records[$entry->key] = $entry;
        $BIB->save_database();
        redirect(reverse('view_entry',array('key'=>$entry->key)));
	}
}
