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
use PHPCSUtils\Utils\Parentheses;
use PHPCSUtils\Utils\PassedParameters;
use PHPCSUtils\Utils\TextStrings;
use WordPressCS\WordPress\AbstractFunctionParameterSniff;
use WordPressCS\WordPress\Helpers\ContextHelper;

/**
 * Sniff to find conditionally defined constants within WP Core, which are allowed to be overloaded.
 *
 * The sniff is intended as a helper for updating the `WordPress.NamingConventions.PrefixAllGlobals` sniff.
 *
 * @since 3.0.0
 */
final class FindPluggableConstantsSniff extends AbstractFunctionParameterSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'defined' => array(
				'functions' => array(
					'defined',
				),
			),
		);
	}

	// TODO: the sniff should also look for `const ...` declarations wrapped within an if( defined() ) {}.

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

		if ( isset( $this->tokens[ $inIf ]['scope_opener'], $this->tokens[ $inIf ]['scope_closer'] ) === false ) {
			return;
		}

		$definedParam = PassedParameters::getParameterFromStack( $parameters, 1, 'constant_name' );
		if ( false === $definedParam ) {
			return;
		}

		$foundDefine = false;
		for ( $firstInIf = ( $this->tokens[ $inIf ]['scope_opener'] + 1 ); $firstInIf < $this->tokens[ $inIf ]['scope_closer']; $firstInIf++ ) {
			if ( isset( Tokens::$emptyTokens[ $this->tokens[ $firstInIf ]['code'] ] ) ) {
				continue;
			}

			// Allow for `\define()`.
			if ( \T_NS_SEPARATOR === $this->tokens[ $firstInIf ]['code'] ) {
				continue;
			}

			// Skip over attributes.
			if ( \T_ATTRIBUTE === $this->tokens[ $firstInIf ]['code']
				&& isset( $this->tokens[ $firstInIf ]['attribute_closer'] )
			) {
				$firstInIf = $this->tokens[ $firstInIf ]['attribute_closer'];
				continue;
			}

			if ( \T_STRING === $this->tokens[ $firstInIf ]['code']
				&& strtolower( $this->tokens[ $firstInIf ]['content'] ) === 'define'
			) {
				$foundDefine = true;
				break;
			}

			if ( \T_NAME_FULLY_QUALIFIED === $this->tokens[ $firstInIf ]['code']
				&& strtolower( $this->tokens[ $firstInIf ]['content'] ) === '\define'
			) {
				$foundDefine = true;
				break;
			}
		}

		if ( false === $foundDefine ) {
			return;
		}

		$hasOpenParens = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $firstInIf + 1 ), null, true );
		if ( false === $hasOpenParens || \T_OPEN_PARENTHESIS !== $this->tokens[ $hasOpenParens ]['code'] ) {
			// Not a function call.
			return;
		}

		// Exclude function definitions, class methods, and namespaced calls.
		if ( ContextHelper::has_object_operator_before( $this->phpcsFile, $firstInIf ) === true ) {
			return;
		}

		if ( ContextHelper::is_token_namespaced( $this->phpcsFile, $firstInIf ) === true ) {
			return;
		}

		$search                   = Tokens::$emptyTokens;
		$search[ \T_BITWISE_AND ] = \T_BITWISE_AND;

		$prev = $this->phpcsFile->findPrevious( $search, ( $firstInIf - 1 ), null, true );

		// Skip sniffing on function, OO definitions or for function aliases in use statements.
		$invalid_tokens  = Tokens::$ooScopeTokens;
		$invalid_tokens += array(
			\T_FUNCTION => \T_FUNCTION,
			\T_NEW      => \T_NEW,
			\T_AS       => \T_AS, // Use declaration alias.
		);

		if ( isset( $invalid_tokens[ $this->tokens[ $prev ]['code'] ] ) ) {
			return;
		}

		$defineParam = PassedParameters::getParameter( $this->phpcsFile, $firstInIf, 1, 'constant_name' );
		if ( false === $defineParam ) {
			return;
		}

		// Okay, so the basic requirements have been met. Now check in more detail.

		// We only look for single quoted strings here as we won't be able to determine the name if it is a double quoted string or something else.
		$skipOver                                = Tokens::$emptyTokens;
		$skipOver[ \T_CONSTANT_ENCAPSED_STRING ] = \T_CONSTANT_ENCAPSED_STRING;

		// Check defined().
		$nameFromDefined  = '';
		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $definedParam['start'], ( $definedParam['end'] + 1 ), true );
		if ( false === $hasNonTextString ) {
			$text            = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $definedParam['start'], ( $definedParam['end'] + 1 ) );
			$nameFromDefined = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );
			$nameFromDefined = ltrim( $nameFromDefined, '\\' ); // Strip off potential FQN namespace separator.
		}

		if ( '' === $nameFromDefined ) {
			return;
		}

		// Check define().
		$nameFromDefine   = '';
		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $defineParam['start'], ( $defineParam['end'] + 1 ), true );
		if ( false === $hasNonTextString ) {
			$text           = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $defineParam['start'], ( $defineParam['end'] + 1 ) );
			$nameFromDefine = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );
		}

		if ( '' === $nameFromDefine ) {
			return;
		}

		if ( defined( $nameFromDefine ) ) {
			// Backfill for PHP native constants.
			return;
		}

		// Case-sensitive comparison as PHP treats constants case-sensitively (unless explicitly not declared like that).
		if ( $nameFromDefined !== $nameFromDefine ) {
			return;
		}

		$this->phpcsFile->recordMetric( $firstInIf, 'Pluggable constant found', $nameFromDefine );
	}
}
