<?php
global $BIB;

$entry = $BIB->db->records[$entry_key];
echo $BIB->twig->render('entry.html',array('entry'=>$entry,'delete'=>$BIB->router->generate('delete_entry',array('key'=>$entry->key))));
