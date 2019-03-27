<?php

/*
 * These are all fine.
 */
add_filter( 'global-function', 'function_name' );
add_action( 'static-class-method', array( 'ClassName', 'method_name' ) );
register_activation_hook( 'static-class-method', [ __CLASS__, $method_name ] );
register_deactivation_hook( 'static-class-method', 'ClassName::method_name' );
register_uninstall_hook( 'static-class-method', 'parent::method_name' );
add_action( 'namespaced-static-class-method', array( 'MyNS\Sub\ClassName', 'method_name' ) );
add_filter( 'object-method', array( $this, 'method_name' ) );
add_action( 'object-implementing-invoke', new MyObject() );
add_filter( 'static-class-method', self::class . '::method_name' );

/*
 * Check against both anonymous functions, as well as anonymous classes.
 */
add_filter( 'closure-detected', function() {} );

add_action(
	'closure-detected-not-anon-class',
	function() {
		return new class() {};
    }
);

Add_FILTER( 'case-insensitive-function-name-match', FUNCTION() {} );

register_activation_hook( 'anon-class-detected', [ new class() {
    function method_name($val) {
        return ++$val;
    }
}, 'method_name' ] );

register_deactivation_hook(
	'anon-class-detected-not-closure',
	array(
		new class() {
			function method_name() {
				$closure = function() {};
			}
		},
		'method_name'
	)
);

register_uninstall_hook('variable-could-be-closure', $callback );

// Check for variable definition within scope (if possible).
// Also check this with non-closure callbacks defined outside function call (to make sure they don't throw an error).

function some_thing($param) {

	add_filter( 'variable-could-be-closure', $param );

	$closure = function() use () {};
	add_filter( 'variable-is-closure', $closure );

	$callback = ['ClassName', 'method_name'];
	add_filter( 'variable-is-fine', $callback );
}
