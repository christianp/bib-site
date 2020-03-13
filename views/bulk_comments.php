<?php
global $BIB;

$entries = $BIB->db->records;

if($_SERVER['REQUEST_METHOD']=='GET') {
	shuffle($entries);
	render('bulk_comments.html',array('entries'=>$entries));
} else {
	$data = json_decode(file_get_contents('php://input'));
	$key = $data->key;
	$comment = $data->comment;
	$entry = get_entry($key);
	$entry->fields['comment'] = $comment;
	$BIB->db->records[$entry->key] = $entry;
	$BIB->save_database();
}
