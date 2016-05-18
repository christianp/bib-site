<?php
global $BIB;

$entry = $BIB->db->records[$entry_key];

if($_SERVER['REQUEST_METHOD']=='GET') {
    echo $BIB->twig->render('delete_entry.html',array('entry'=>$entry));
} else {
    $BIB->db->delete_record($entry);
    $BIB->save_database();
    redirect(reverse('index'));
}
