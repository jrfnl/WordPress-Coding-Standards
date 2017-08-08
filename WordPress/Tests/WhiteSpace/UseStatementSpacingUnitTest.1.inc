<?php

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
