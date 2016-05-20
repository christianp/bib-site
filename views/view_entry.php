<?php
global $BIB;

$entry = get_entry($entry_key);

if($_GET['show_pdf']) {
	$_SESSION['show_pdf'] = true;
}
echo $BIB->twig->render('entry.html',array(
	'entry'=>$entry,
	'delete'=>$BIB->router->generate('delete_entry',array('key'=>$entry->key)),
	'show_pdf'=>$_SESSION['show_pdf']
));
