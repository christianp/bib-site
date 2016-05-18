<?php
require_once('main.inc.php');

$BIB->router->map('GET','/', function() {
	require __DIR__ . '/views/index.php';
},'index');

$BIB->router->map('GET','/entry/[*:key]',function($entry_key) {
	require __DIR__ . '/views/view_entry.php';
},'view_entry');

$BIB->router->map('GET|POST','/entry/[*:key]/delete',array(
    'view'=>function($entry_key) {
        require __DIR__ . '/views/delete_entry.php';
    },
    'login_required' => true
),'delete_entry');

$BIB->router->map('GET|POST','/new',array(
    'view'=>function() {
	    require __DIR__ . '/views/new_entry.php';
    },
    'login_required' => true
),'new_entry');

$BIB->router->map('GET','/export.bib',function() {
    header('Content-Type: application/x-bibtex');
    echo $bib->as_bib();
},'export');

$BIB->router->map('GET|POST','/login',function() {
	require __DIR__ . '/views/login.php';
},'login');

// match current request url
$match = $BIB->router->match();

// call closure or throw 404 status
if( $match) {
    $target = $match['target'];
    if(is_array($target) && isset($target['login_required'])) {
        if(!$BIB->logged_in()) {
            redirect(reverse('login'));
        }
    }
    $view = is_callable($target) ? $target : $target['view'];
    call_user_func_array( $view, $match['params'] ); 
} else {
    // no route was matched
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
