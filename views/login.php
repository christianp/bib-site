<?php
global $BIB;

function render() {
	global $BIB;
	echo $BIB->twig->render('login.html');
}

if($_SERVER['REQUEST_METHOD']=='GET') {
    render();
} else {
    $logged_in = $BIB->authenticate($_POST['password']);
    if($logged_in) {
        redirect(reverse('index'));
    } else {
        render();
    }
}
