<?php

/*
 * Use with closures.
 */
$example = function ( $arg ) use ( $message ) {}; // OK.
$example = function () use ( &$message ) {}; // OK.
$callback = function ( $quantity, $product ) use ( $tax, &$total ) {}; // OK.
$callback = function ( $quantity, $product ) use (
	$tax,
	&$total
) {}; // OK.

$example = function ( $arg )use ( $message ) {}; // Bad - no space before use.
$example = function ( $arg )    use ( $message ) {}; // Bad - too much space before use.
$example = function ( $arg ) use( $message ) {}; // Bad - no space between use and open parenthesis.
$example = function ( $arg ) use   ( $message ) {}; // Bad - too much space between use and open parenthesis.
// Covered by Squiz.Functions.FunctionDeclarationArgumentSpacing
// $example = function ( $arg ) use ($message ) {}; // Bad - no space after open parenthesis.
// $example = function ( $arg ) use (  $message ) {}; // Bad - too much space after open parenthesis.
// $example = function ( $arg ) use ( $message) {}; // Bad - no space before close parenthesis.
// $example = function ( $arg ) use ( $message   ) {}; // Bad - too much space before close parenthesis.
$example = function ( $arg ) use ( $message ){}; // Bad - no space between close parenthesis and open brace.
$example = function ( $arg ) use ( $message )    {}; // Bad - too much space between close parenthesis and open brace.

$example = function ( $arg )use($message){}; // Bad x 5.
$example = function () use(&$message) {}; // Bad x 3.

$callback = function ( $quantity, $product )use($tax ,&$total){}; // Bad x 7.
// Covered by Squiz.Functions.FunctionDeclarationArgumentSpacing
// $callback = function ( $quantity, $product ) use ( $tax ,&$total ) {}; // Bad x 2 - spacing around comma.
// $callback = function ( $quantity, $product ) use ( $tax,         &$total ) {}; // Bad - too much space after comma.
$callback = function ( $quantity, $product ) use ( $tax,
&$total ){}; // Bad - too much space after comma.
