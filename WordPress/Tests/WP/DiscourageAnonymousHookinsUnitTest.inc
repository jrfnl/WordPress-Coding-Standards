<?php

/*
 * These are all fine.
 */
add_filter( 'no-callback' ); // Invalid function call, but not our concern.
add_filter( 'global-function', 'function_name' );
add_action( 'static-class-method', array( 'ClassName', 'method_name' ) );
register_activation_hook( 'static-class-method', [ __CLASS__, $method_name ] );
register_deactivation_hook( 'static-class-method', 'ClassName::method_name' );
register_uninstall_hook( 'static-class-method', 'parent::method_name' );
add_action( 'namespaced-static-class-method', array( 'MyNS\Sub\ClassName', 'method_name' ) );
add_filter( 'object-method', array( $this, 'method_name' ) );
add_action( 'object-implementing-invoke', new MyObject() );
add_action( 'object-implementing-invoke-namespaced', new MyNamespace\MyObject() );
add_filter( 'static-class-method', self::class . '::method_name' );

// Using anonymous hook-ins in unit tests is OK.
class MyTest extends WP_UnitTestCase {
	function test_this() {
		add_filter( 'closure-detected', function() {} ); // OK.
	}

	function helper_for_test() {
		register_activation_hook( 'anon-class-detected', [ new class() {}, 'method_name' ] ); // OK.
	}
}

/*
 * Check against both anonymous functions, as well as anonymous classes.
 */
add_filter( 'closure-detected', function() {} ); // Bad.

add_action(
	'closure-detected-not-anon-class',
	function() { // Bad.
		return new class() {};
    }
);

Add_FILTER( 'case-insensitive-function-name-match', FUNCTION() {} ); // Bad.

register_activation_hook( 'anon-class-detected', [ new class() { // Bad.
    function method_name($val) {
        return ++$val;
    }
}, 'method_name' ] );

register_deactivation_hook(
	'anon-class-detected-not-closure',
	array(
		new class() { // Bad.
			function method_name() {
				$closure = function() {};
			}
		},
		'method_name'
	)
);

register_uninstall_hook('variable-could-be-closure', $callback ); // Ignore

// Check for variable definition within scope (if possible).
// Also check this with non-closure callbacks defined outside function call (to make sure they don't throw an error).

function some_thing($param) {

	add_filter( 'variable-could-be-closure-but-undetermined', $param ); // Ignore

	add_filter( 'variable-could-be-closure-but-undetermined', $GLOBALS['var'] ); // Ignore

	$foo  = 'namespace::';
	$foo .= function() { return 'function_name'; }();
	if ( $foo !== false ) {
		add_filter( 'variable-could-be-closure-but-undetermined', $foo ); // Ignore
	}

	$closure = function() use () {};
	add_filter( 'variable-is-closure', $closure /* comment */ ); // Bad.

	$callback = ['ClassName', 'method_name'];
	add_filter( 'variable-is-fine', /* comment */ $callback ); // OK.

	$callback   = [new class() {}];
	$callback[] = 'method_name';
	add_filter( 'variable-is-fine', $callback ); // Bad.
}
