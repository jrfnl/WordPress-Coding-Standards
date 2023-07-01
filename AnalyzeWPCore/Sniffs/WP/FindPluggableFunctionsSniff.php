<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\AnalyzeWPCore\Sniffs\WP;

use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Parentheses;
use PHPCSUtils\Utils\PassedParameters;
use PHPCSUtils\Utils\TextStrings;
use WordPressCS\WordPress\AbstractFunctionParameterSniff;

/**
 * Sniff to find conditionally defined functions within WP Core, which are considered "pluggable".
 *
 * The sniff is intended as a helper for updating the `WordPress.NamingConventions.PrefixAllGlobals` sniff.
 *
 * @since 3.0.0
 */
final class FindPluggableFunctionsSniff extends AbstractFunctionParameterSniff {

	/**
	 * List of all PHP native functions.
	 *
	 * Using this list rather than a call to `function_exists()` prevents
	 * false negatives from user-defined functions when those would be
	 * autoloaded via a Composer autoload files directives.
	 *
	 * @var array
	 */
	private $built_in_functions;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// Get a list of all PHP native functions.
		$all_functions            = get_defined_functions();
		$this->built_in_functions = array_flip( $all_functions['internal'] );
		$this->built_in_functions = array_change_key_case( $this->built_in_functions, \CASE_LOWER );
	}

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'function_exists' => array(
				'functions' => array(
					'function_exists',
				),
			),
		);
	}

	/**
	 * Process the parameters of a matched function.
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param string $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 * @param array  $parameters      Array with information about the parameters.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_parameters( $stackPtr, $group_name, $matched_content, $parameters ) {
		$inIf = Parentheses::getLastOwner( $this->phpcsFile, $stackPtr, array( \T_IF ) );
		if ( false === $inIf ) {
			return;
		}

		if ( isset( $this->tokens[ $inIf ]['scope_opener'] ) === false ) {
			return;
		}

		$functionParam = PassedParameters::getParameterFromStack( $parameters, 1, 'function' );
		if ( false === $functionParam ) {
			return;
		}

		// Note: not taking method prefixes into account as we're only looking for non-OO functions.
		for ( $firstInIf = ( $this->tokens[ $inIf ]['scope_opener'] + 1 ); $firstInIf < $this->phpcsFile->numTokens; $firstInIf++ ) {
			if ( isset( Tokens::$emptyTokens[ $this->tokens[ $firstInIf ]['code'] ] ) ) {
				continue;
			}

			// Skip over attributes.
			if ( \T_ATTRIBUTE === $this->tokens[ $firstInIf ]['code']
				&& isset( $this->tokens[ $firstInIf ]['attribute_closer'] )
			) {
				$firstInIf = $this->tokens[ $firstInIf ]['attribute_closer'];
				continue;
			}

			break;
		}

		if ( \T_FUNCTION !== $this->tokens[ $firstInIf ]['code'] ) {
			return;
		}

		$functionName = FunctionDeclarations::getName( $this->phpcsFile, $firstInIf );
		if ( empty( $functionName ) ) {
			return;
		}

		if ( isset( $this->built_in_functions[ strtolower( $functionName ) ] ) ) {
			// Backfill for PHP native function.
			return;
		}

		// Okay, so the basic requirements have been met. Now check in more detail.
		$nameFromParam = '';

		// We only look for single quoted strings here as we won't be able to determine the name if it is a double quoted string.
		$skipOver                                = Tokens::$emptyTokens;
		$skipOver[ \T_CONSTANT_ENCAPSED_STRING ] = \T_CONSTANT_ENCAPSED_STRING;

		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $functionParam['start'], ( $functionParam['end'] + 1 ), true );
		if ( false === $hasNonTextString ) {
			$text          = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $functionParam['start'], ( $functionParam['end'] + 1 ) );
			$nameFromParam = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );
		}

		if ( '' === $nameFromParam ) {
			return;
		}

		$namespaceName = Namespaces::determineNamespace( $this->phpcsFile, $firstInIf );
		$name          = $functionName;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $functionName;
		}

		if ( strtolower( $name ) !== strtolower( $nameFromParam ) ) {
			return;
		}

		// Usage of `stripQuotes` is to ensure `stdin_path` passed by IDEs does not include quotes.
		$file = TextStrings::stripQuotes( $this->phpcsFile->getFileName() );
		if ( 'STDIN' === $file ) {
			return;
		}
		if ( ( strpos( $file, '/src/wp-content/themes/' ) !== false
			|| strpos( $file, '\\src\\wp-content\\themes\\' ) !== false )
			&& stripos( $name, 'twenty' ) !== 0
		) {
			// Ignore WP Core functions polyfilled by themes to allow for supporting older WP installs.
			return;
		}

		$this->phpcsFile->recordMetric( $firstInIf, 'Pluggable function found', $name );
	}
}
