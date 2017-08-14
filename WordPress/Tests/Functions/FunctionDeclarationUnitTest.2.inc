<?php

// PEAR.Functions.FunctionDeclaration -> space after function keyword
// PEAR.Functions.FunctionDeclaration -> space before (
// XXXX PEAR.Functions.FunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX

// Squiz.Functions.FunctionDeclaration -> generic message

// Squiz.Functions.MultiLineFunctionDeclaration -> space after function keyword
// Squiz.Functions.MultiLineFunctionDeclaration -> space before (
// XXXX Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
function     something     ( $par ) {
}

// PEAR.Functions.FunctionDeclaration -> space after function keyword
// PEAR.Functions.FunctionDeclaration -> space before use
// PEAR.Functions.FunctionDeclaration -> space after use

// Squiz.Functions.MultiLineFunctionDeclaration -> space after function keyword
// Squiz.Functions.MultiLineFunctionDeclaration -> space before use
// Squiz.Functions.MultiLineFunctionDeclaration -> space after use
$a = function   ( $par )use( $abc ) {
};

// XXXX PEAR.Functions.FunctionDeclaration -> space between function keyword and parenthesis XXXX
// XXXX Squiz.Functions.MultiLineFunctionDeclaration -> space between function keyword and parenthesis XXXX
$a = function( $par ) use ( $abc ) {
};

// PEAR.Functions.FunctionDeclaration -> space before (
// XXXX PEAR.Functions.FunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX

// Squiz.Functions.FunctionDeclaration -> generic message

// Squiz.Functions.MultiLineFunctionDeclaration -> space before (
// XXXX Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
function something_else ( $par )          {
}

// PEAR.Functions.FunctionDeclaration -> multi-line close parenthesis should be on next line

// Squiz.Functions.MultiLineFunctionDeclaration -> first param should be on next line
// Squiz.Functions.MultiLineFunctionDeclaration -> multi-line one param per line
// Squiz.Functions.MultiLineFunctionDeclaration -> multi-line close parenthesis should be on next line
function abc( $par_a,
	$par_b, $par_c ) {
}

// Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingBetween
// XXXX PEAR.Functions.FunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
// XXXX Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
function abc( ) {
}

// XXXX PEAR.Functions.FunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
// XXXX Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
function abc(): ?array {
}

// XXXX PEAR.Functions.FunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
// XXXX Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine -> brace should be on new line XXXX
function abc() : ?array {
}

function def() {
	/* something */ }

function def() {
} /* something */
