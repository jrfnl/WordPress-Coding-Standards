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
add_filter( 'object-parent-method', array( $this, 'parent::method_name' ) );
add_action( 'object-implementing-invoke', new MyObject() );
add_action( 'object-implementing-invoke-namespaced', new MyNamespace\MyObject() );
add_filter( 'static-class-method', self::class . '::method_name' );

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

// Using anonymous hook-ins in unit tests isn't OK either.
class MyTest extends WP_UnitTestCase {
	function test_this() {
		add_filter( 'closure-detected', function() {} ); // Bad.
	}

	function helper_for_test() {
		register_activation_hook( 'anon-class-detected', [ new class() {}, 'method_name' ] ); // Bad.
	}
}

// Check for variable definition within scope (if possible).
// Also check this with non-closure callbacks defined outside function call (to make sure they don't throw an error).

register_uninstall_hook('variable-could-be-closure', $callback ); // Ignore, variable declaration not found within scope.

function foo( $param ) {

	// This one should be ignored. Variable declaration not found above hookin within scope.
	add_filter( 'variable-could-be-closure-but-undetermined', $param );

	// Not a closure. Part of the callback name is a hard-coded string.
	add_filter( 'variable-is-fine', $partial . '_test' );
	add_filter( 'variable-is-fine', "{$partial}_test" );
	add_filter( 'variable-is-fine', [ $class, $partial . '_test' ] );

	// This one should be ignored. Variable declaration not found above hookin within scope.
	add_filter( 'variable-could-be-closure-but-undetermined', $GLOBALS['var'] );

	// This one should be ignored. Variable declaration not found above hookin within scope.
	add_filter( 'variable-could-be-closure-but-undetermined', $args['callback'] );

	// Variable declared after hookin should not confuse the sniff.
	$param = 'callback';

	$foo  = 'namespace::';
	$foo .= function() { return 'function_name'; }();
	if ( $foo !== false ) {
		add_filter( 'variable-could-be-closure-but-undetermined', $foo ); // Ignore.
	}

	$static_class_method = ['ClassName', 'method_name'];
	add_filter( 'variable-is-fine', /* comment */ $static_class_method ); // OK.

	$function_name = 'function_name';
	add_filter( 'variable-is-fine', $function_name ); // OK.

	$obj_method = [$this, 'method_name'];
	add_filter( 'variable-is-fine', $obj_method ); // OK.

	$static_class_method = ["ClassName", 'method_name'];
	add_filter( 'variable-is-fine', /* comment */ $static_class_method ); // OK.

	$static_class_method_2 = 'ClassName::method_name';
	add_filter( 'variable-is-fine', $static_class_method_2 ); // OK.

	$magic_class_method = array( __CLASS__, 'method_name'];
	add_filter( 'variable-is-fine', $magic_class_method ); // OK.

	$declared_class_method = array( get_declared_class(), 'method_name'];
	add_filter( 'variable-is-fine', $declared_class_method ); // OK.

	$declared_class_method_2 = array( static::class, 'method_name'];
	add_filter( 'variable-is-fine', $declared_class_method_2 ); // OK.

	$object_implementing_invoke = new Invokable();
	add_filter( 'variable-is-fine', $object_implementing_invoke ); // OK.

	$closure = function() use () {};
	add_filter( 'variable-is-closure', $closure /* comment */ ); // Bad.

	$callback   = [new class() {}];
	$callback[] = 'method_name';
	add_filter( 'variable-is-anon-class', $callback ); // Bad.

	$anon_class = new class() {};
	add_filter( 'variable-is-anon-class', array( $anon_class, 'method_name' ) ); // Bad.
}
