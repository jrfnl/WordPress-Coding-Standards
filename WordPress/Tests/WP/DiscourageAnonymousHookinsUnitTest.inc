<?php

// Make sure to cover all type of hook-ins.

// Check that all "normal" call-backs still pass.

// Check against both anonymous functions, as well as anonymous classes.

// Check with callback short arrays syntax too

add_filter( 'hook_name', function() {} );

add_filter( 'hook_name', array( new class() {}, 'method_name' ) );


// Check for variable definition within scope (if possible).
// Also check this with non-closure callbacks defined outside function call (to make sure they don't throw an error).

function some_thing() {
	$closure = function() use () {};

	add_filter( 'hook_name', $closure );
}
