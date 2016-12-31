<?php
global $BIB;

$entry = get_entry($entry_key);

if(get($_GET,'show_pdf',false)) {
	$_SESSION['show_pdf'] = true;
}
if(get($_GET,'hide_pdf',false)) {
	$_SESSION['show_pdf'] = false;
}

$ignore_fields = ['abstract','title','url','author','urldate','month','year'];
if($entry->is_arxiv()) {
    $ignore_fields = array_merge($ignore_fields,['archivePrefix','eprint','primaryClass']);
}

echo $BIB->twig->render('entry.html',array(
	'entry'=>$entry,
	'delete'=>$BIB->router->generate('delete_entry',array('key'=>$entry->key)),
    'show_pdf'=>get($_SESSION,'show_pdf',false),
    'ignore_fields' => $ignore_fields
));
