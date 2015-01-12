<?php

$GLOBALS['wpdb'] = 'test';

global $wpdb;
$wpdb = 'test';

global $post;
$post = get_post( 1 ); // override ok


function local_var() {
	$post = 'test'; // ok
}

function global_var() {
	global $post;
	$post = 'test'; // bad

	$GLOBALS['wpdb'] = 'test'; // bad
}

${action} = 'test'; // bad

{$action} = 'test'; // bad

$var = 'action';
${$var} = 'anotheraction'; // bad

$GLOBALS[ 'p' . 'a' . 'ge' ] = 'test'; // bad

$var_name = 'wp_admin_bar';
$GLOBALS[ $var_name ] = 'test'; // bad