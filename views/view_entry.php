<?php
global $BIB;

if($_GET['show_pdf']) {
	$_SESSION['show_pdf'] = true;
}
$entry = $BIB->db->records[$entry_key];
$res = parse_record($entry->as_bib());
$newentry = $res[0];
echo $BIB->twig->render('entry.html',array(
	'new'=>($newentry),
	'entry'=>$entry,
	'delete'=>$BIB->router->generate('delete_entry',array('key'=>$entry->key)),
	'show_pdf'=>$_SESSION['show_pdf']
));
