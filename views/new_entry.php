<?php
global $BIB;

class Form {
	function Form($fields,$defaults=array()) {
		$this->fields = $fields;
		$this->data = $defaults;
		foreach($this->fields as $field=>$options) {
			if(isset($_GET[$field])) {
				$this->data[$field] = $_GET[$field];
			}
			if(isset($_POST[$field])) {
				$this->data[$field] = $_POST[$field];
            }
		}
    }

    function clean() {
        $this->cleaned_data = array();
        foreach($this->fields as $field=>$options) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;
            if(isset($options['clean']) && is_callable($options['clean'])) {
                $value = call_user_func_array($options['clean'],array($value));
            }
            $this->cleaned_data[$field] = $value;
        }
    }
}

$form = new Form(
	array(
		'type' => array(
			'type' => 'select',
			'required'=>'true',
			'options' => array(
				'article' => 'Article',
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
		'comment' => array('type'=>'textarea'),
	),
	array('type'=>'article')
);

function render($form) {
	global $BIB;
	echo $BIB->twig->render('new_entry.html',array('form'=>$form));
}

if($_SERVER['REQUEST_METHOD']=='GET') {
	render($form);
} else {
    $form->clean();
	if($form->cleaned_data['title']==='' || $form->cleaned_data['key']==='') {
		render($form);
	} else {
		$entry = new BibEntry(
			$form->cleaned_data['type'],
			$form->cleaned_data['key'],
			array(
				'title' => $form->cleaned_data['title'],
				'url' => $form->cleaned_data['url'],
                'author' => $form->cleaned_data['author'],
                'comment' => $form->cleaned_data['comment'],
				'urldate' => date('Y-m-d')
			)
        );
        $BIB->db->records[$entry->key] = $entry;
        $BIB->save_database();
        redirect(reverse('view_entry',array('key'=>$entry->key)));
	}
}
