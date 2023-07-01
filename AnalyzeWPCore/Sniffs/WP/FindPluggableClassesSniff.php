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
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Parentheses;
use PHPCSUtils\Utils\PassedParameters;
use PHPCSUtils\Utils\TextStrings;
use WordPressCS\WordPress\AbstractFunctionParameterSniff;

/**
 * Sniff to find conditionally defined classes within WP Core, which are considered "pluggable.
 *
 * The sniff is intended as a helper for updating the `WordPress.NamingConventions.PrefixAllGlobals` sniff.
 *
 * @since 3.0.0
 */
final class FindPluggableClassesSniff extends AbstractFunctionParameterSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'class_exists' => array(
				'functions' => array(
					'class_exists',
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

		$classParam = PassedParameters::getParameterFromStack( $parameters, 1, 'class' );
		if ( false === $classParam ) {
			return;
		}

		$skipOver  = Tokens::$emptyTokens;
		$skipOver += Collections::classModifierKeywords();

		for ( $firstInIf = ( $this->tokens[ $inIf ]['scope_opener'] + 1 ); $firstInIf < $this->phpcsFile->numTokens; $firstInIf++ ) {
			if ( isset( $skipOver[ $this->tokens[ $firstInIf ]['code'] ] ) ) {
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

		if ( \T_CLASS !== $this->tokens[ $firstInIf ]['code'] ) {
			return;
		}

		$className = ObjectDeclarations::getName( $this->phpcsFile, $firstInIf );
		if ( empty( $className ) ) {
			return;
		}

		// TODO: Exclude PHP native classes.

		// Okay, so the basic requirements have been met. Now check in more detail.
		$nameFromParam = '';

		// We only look for single quoted strings here as we won't be able to determine the name if it is a double quoted string.
		$skipOver                                = Tokens::$emptyTokens;
		$skipOver[ \T_CONSTANT_ENCAPSED_STRING ] = \T_CONSTANT_ENCAPSED_STRING;

		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $classParam['start'], ( $classParam['end'] + 1 ), true );
		if ( false === $hasNonTextString ) {
			$text          = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $classParam['start'], ( $classParam['end'] + 1 ) );
			$nameFromParam = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );
		}

		if ( '' === $nameFromParam ) {
			// Check for class_exists( Name::class ).
			if ( preg_match( '`([^ :]+)::class$`', $classParam['clean'], $matches ) !== 1 ) {
				return;
			}

			// TODO: should get use statement resolution.
			$nameFromParam = $matches[1];
		}

		if ( '' === $nameFromParam ) {
			return;
		}

		$namespaceName = Namespaces::determineNamespace( $this->phpcsFile, $firstInIf );
		$name          = $className;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $className;
		}

		if ( strtolower( $name ) !== strtolower( $nameFromParam ) ) {
			return;
		}

		$this->phpcsFile->recordMetric( $firstInIf, 'Pluggable classes found', $name );
	}
}
