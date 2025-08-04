<?php
global $BIB;
use Cocur\Slugify\Slugify;

$entry = get_entry($entry_key);

$text = '';
$keys = ['title','author','abstract','comment'];
foreach($keys as $key) {
	$text .= "\n".$entry->$key;
}

function render_form($form,$entry,$text) {
	global $BIB;
	$allwords = preg_split('/\W+/',$text);
	$words = array();
	foreach($allwords as $word) {
		$word = strtolower($word);
		if(strlen($word)>2) {
			$words[$word] = true;
		}
	}
	$words = array_keys($words);
	asort($words);
	$ctx = array(
		'text'=>$text,
		'words'=>$words,
		'entry'=>$entry
	);
	echo $BIB->twig->render('edit_entry_tags.html',$ctx);
}

if($_SERVER['REQUEST_METHOD']=='GET') {
	render_form($form,$entry,$text);
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
        $entry->fields['year'] = $form->cleaned_data['year'];
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
        redirect(reverse('view_entry',array('entry_key'=>$entry->key)));
	}
}
