<?php
$BIB->router->map('GET','', function() {
	require __DIR__ . '/views/index.php';
},'index');

$BIB->router->map('GET','rss',function() {
    require __DIR__ . '/views/rss.php';
},'rss');

$BIB->router->map('GET','collection/[key:key]', function($slug) {
	require __DIR__ . '/views/view_collection.php';
},'view_collection');

$BIB->router->map('GET|POST','collection/[key:key]/edit',array(
    'view'=>function($slug) {
        require __DIR__ . '/views/edit_collection.php';
    },
    'login_required' => true
),'edit_collection');

$BIB->router->map('GET','random',function() {
	global $BIB;
	$key = array_rand($BIB->db->records);
	return redirect(reverse('view_entry',array('key'=>$key)).'?'.http_build_query($_GET));
},'random_entry');

$BIB->router->map('GET','entry/[key:key]',function($entry_key) {
	require __DIR__ . '/views/view_entry.php';
},'view_entry');

$BIB->router->map('GET|POST','entry/[key:key]/edit',array(
    'view'=>function($entry_key) {
        require __DIR__ . '/views/edit_entry.php';
    },
    'login_required' => true
),'edit_entry');

$BIB->router->map('GET|POST','entry/[key:key]/delete',array(
    'view'=>function($entry_key) {
        require __DIR__ . '/views/delete_entry.php';
    },
    'login_required' => true
),'delete_entry');

$BIB->router->map('GET|POST','new',array(
    'view'=>function() {
	    require __DIR__ . '/views/new_entry.php';
    },
    'login_required' => true
),'new_entry');

$BIB->router->map('GET','export.bib',function() {
	global $BIB;
    header('Content-Type: application/x-bibtex');
    echo $BIB->db->as_bib();
},'export');

$BIB->router->map('GET','export.json',function() {
	global $BIB;
    echo json_encode($BIB->db->as_json());
},'export_json');

$BIB->router->map('GET|POST','login',function() {
	require __DIR__ . '/views/login.php';
},'login');


