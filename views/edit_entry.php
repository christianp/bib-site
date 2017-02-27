<?php
global $BIB;

$entry = get_entry($entry_key);

$defaults = array(
    'type' => $entry->type,
    'key' => $entry->key,
    'title' => $entry->title,
	'author' => $entry->author,
	'abstract' => get($entry->fields,'abstract',''),
	'comment' => get($entry->fields,'comment',''),
    'url' => implode(" ",$entry->urls),
    'extra_fields' => array()
);
foreach($entry->fields as $name=>$value) {
    if(!in_array($name,array('title','author','url','abstract','comment'))) {
        $defaults['extra_fields'][] = array('name'=>$name,'value'=>$value);
    }
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
        $entry->fields['abstract'] = $form->cleaned_data['abstract'];
        $entry->fields['comment'] = $form->cleaned_data['comment'];
        foreach($form->cleaned_data['extra_fields'] as $field) {
            $entry->fields[$field['name']] = $field['value'];
        }

        $BIB->db->records[$entry->key] = $entry;
        $BIB->save_database();
        redirect(reverse('view_entry',array('key'=>$entry->key)));
	}
}
