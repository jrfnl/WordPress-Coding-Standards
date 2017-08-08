<?php

/*
 * Use with traits.
 */
class ezcReflectionMethod extends ReflectionMethod {
    use ezcReflectionReturnInfo;
}

class MyHelloWorld {
    use \Hello, World;
}

class MyHelloWorld {
    use \Hello,
		World;
}


class MyClass1 {
    use \HelloWorld { sayHello as protected; }
}

class MyClass2 {
    use HelloWorld {
		sayHello as private myPrivateHello;
	}
}

class SomeThing {
	use Meta_Submodule_Trait, \Settings_Submodule_Trait {
		Meta_Submodule_Trait::get_meta_fields as protected _get_meta_fields;
	}
}

class Aliased_Talker {
	use A, B {
		B::smallTalk insteadof A;
		A::bigTalk insteadof B;
		B::bigTalk as talk;
	}
}
class Foo
{
    use A, B, C {
        C::bar insteadof A, B;
    }
}


/*
 * Use with closures.
 */
$message = 'hello';

$example = function ($arg) use ($message) {};
$example = function () use (&$message) {};
$callback = function ($quantity, $product) use ($tax, &$total) {};


/*
 * Use with namespaces.
 */
namespace foo;

use ArrayObject;
use My\Full\NSname;
use My\Full\Classname as Another;

// Importing a function / constant (PHP 5.6+)
use function My\Full\functionName;
use function My\Full\functionName as func;
use const My\Full\CONSTANT;

// Multi-use statement.
use My\Full\Classname as Another, My\Full\NSname;

use My\Full\Classname as Another,
	My\Full\NSname;

// Group use statements (PHP 7+)
use some\name\{ClassA, ClassB, ClassC as C};
use some\name\{
	ClassA,
	ClassB,
	ClassC as C
};
use function some\name\{fn_a, fn_b, fn_c};
use const some\name\{ConstA, ConstB, ConstC};

// Group use with trailing comma (PHP 7.2+)
use some\name\{
	ClassA,
	ClassB,
	ClassC as C,
};
