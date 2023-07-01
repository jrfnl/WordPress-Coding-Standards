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
use PHPCSUtils\Utils\PassedParameters;
use PHPCSUtils\Utils\TextStrings;
use WordPressCS\WordPress\AbstractFunctionParameterSniff;

/**
 * Sniff to find defined WP time constants within WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.WP.CronInterval` sniff.
 *
 * @since 3.0.0
 */
final class FindWPTimeConstantsSniff extends AbstractFunctionParameterSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'define' => array(
				'functions' => array(
					'define',
				),
			),
		);
	}

	// TODO: the sniff should also look for non-OO `const ...` declarations.

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
		$param = PassedParameters::getParameterFromStack( $parameters, 1, 'constant_name' );
		if ( false === $param ) {
			return;
		}

		// We only look for single quoted strings here as we won't be able to determine the name if it is a double quoted string or something else.
		$skipOver                                = Tokens::$emptyTokens;
		$skipOver[ \T_CONSTANT_ENCAPSED_STRING ] = \T_CONSTANT_ENCAPSED_STRING;

		$constantName     = '';
		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $param['start'], ( $param['end'] + 1 ), true );
		if ( false === $hasNonTextString ) {
			$text         = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $param['start'], ( $param['end'] + 1 ) );
			$constantName = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );
		}

		if ( '' === $constantName ) {
			return;
		}

		if ( preg_match( '`_IN_SECONDS$`', $constantName ) !== 1 ) {
			return;
		}

		$this->phpcsFile->recordMetric( $text, 'Time constant found', $constantName );
	}
}
