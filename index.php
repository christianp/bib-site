<?php
require_once('main.inc.php');
require('routes.php');
// match current request url
$match = $BIB->router->match();

// call closure or throw 404 status
if( $match) {
    $target = $match['target'];
    if(is_array($target) && isset($target['login_required'])) {
        if(!$BIB->logged_in()) {
            redirect(reverse('login')."?".http_build_query(array('next'=>$_SERVER['REQUEST_URI'])));
        }
    }
    $view = is_callable($target) ? $target : $target['view'];
    call_user_func_array( $view, $match['params'] ); 
} else {
    // no route was matched
	respond_404();
}
