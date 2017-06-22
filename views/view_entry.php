<?php
global $BIB;

$entry = get_entry($entry_key);

if(get($_GET,'show_pdf',false)) {
	$_SESSION['show_pdf'] = true;
}
if(get($_GET,'hide_pdf',false)) {
	$_SESSION['show_pdf'] = false;
}
$format = get($_GET,'format','html');

$ignore_fields = array('abstract','comment','title','url','author','urldate','day','month','year','collections');
if($entry->is_arxiv()) {
    $ignore_fields = array_merge($ignore_fields,array('archivePrefix','eprint','primaryClass'));
}
$show_fields = array();
foreach($entry->fields as $key=>$value) {
    if($value && !in_array($key,$ignore_fields)) {
        $show_fields[$key] = $value;
    }
}

$collections = $BIB->entry_collections($entry);

switch($format) {
case 'html':
	render('entry.html',array(
		'entry'=>$entry,
		'collections' => $collections,
		'delete'=>$BIB->router->generate('delete_entry',array('key'=>$entry->key)),
		'show_pdf'=>get($_SESSION,'show_pdf',false),
		'ignore_fields' => $ignore_fields,
		'show_fields' => $show_fields
	));
	break;
case 'json':
	$collections_data = array();
	foreach($collections as $collection) {
		$collections_data[] = $collection->name;
	}
	$data = array(
		'title' => $entry->title,
		'pdf' => $entry->pdf(),
		'key' => $entry->key,
		'type' => $entry->type,
		'nicetype' => $BIB->type_options[$entry->type],
		'collections' => $collections_data,
		'author' => $entry->fields['author'],
		'abstract' => $entry->abstract,
		'url' => $entry->urls[0],
		'urls' => $entry->urls,
		'view' => url_origin($_SERVER).reverse('view_entry',array('key'=>$entry->key))
	);
	echo json_encode($data);
}
