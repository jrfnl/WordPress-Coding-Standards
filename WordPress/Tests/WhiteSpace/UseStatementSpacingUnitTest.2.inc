<?php

/*
 * Use with closures.
 */
$message = 'hello';

$example = function ($arg) use ($message) {};
$example = function () use (&$message) {};
$callback = function ($quantity, $product) use ($tax, &$total) {};

// @codingStandardsChangeSetting WordPress.WhiteSpace.ControlStructureSpacing spaces_before_closure_open_paren 0

$closureWithArgsAndVars = function( $arg1, $arg2 ) use ( $var1, $var2 ) {}; // Ok.
$closureWithArgsAndVars = function ( $arg1, $arg2 ) use ( $var1, $var2 ) {}; // Bad.

// @codingStandardsChangeSetting WordPress.WhiteSpace.ControlStructureSpacing spaces_before_closure_open_paren 1

$closureWithArgsAndVars = function ( $arg1, $arg2 ) use ( $var1, $var2 ) {}; // Ok.

$closureWithArgsAndVars = function ( $arg1, $arg2 ) use( $var1, $var2 ) {}; // Bad, no space before open parenthesis prohibited.
$closureWithArgsAndVars = function ( $arg1, $arg2 ) use  ( $var1, $var2 ) {}; // Bad, expected exactly one space before opening parenthesis.

$closureWithArgsAndVars = function ( $arg1, $arg2 ) use ( $var1, $var2 ){}; // Bad, space between closing parenthesis and control structure required.
$closureWithArgsAndVars = function ( $arg1, $arg2 ) use ( $var1, $var2 )  {}; // Bad, expected exactly one space between closing parenthesis and control structure.

$closureWithArgsAndVars = function ( $arg1, $arg2 )use ( $var1, $var2 ) {}; // Bad, expected exactly one space between closing parenthesis and control structure.
$closureWithArgsAndVars = function ( $arg1, $arg2 )  use ( $var1, $var2 ) {}; // Bad, expected exactly one space between closing parenthesis and control structure.

// Namespaces.
use Foo\Admin;

// @codingStandardsChangeSetting WordPress.WhiteSpace.ControlStructureSpacing spaces_before_closure_open_paren -1

$closureWithArgsAndVars = function( $arg1, $arg2 ) use ( $var1, $var2 ) {}; // Ok.
$closureWithArgsAndVars = function ( $arg1, $arg2 ) use ( $var1, $var2 ) {}; // Ok.
