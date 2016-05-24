<?php
global $BIB;

function render_page() {
	global $BIB;
	echo $BIB->twig->render('login.html');
}

if($_SERVER['REQUEST_METHOD']=='GET') {
    render_page();
} else {
    $logged_in = $BIB->authenticate($_POST['password']);
    if($logged_in) {
		if(array_key_exists('next',$_GET)) {
			redirect($_GET['next']);
		}
        redirect(reverse('index'));
    } else {
        render_page();
    }
}
