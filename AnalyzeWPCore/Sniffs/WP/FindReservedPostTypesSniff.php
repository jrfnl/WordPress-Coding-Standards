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
 * Sniff to find pot type names reserved for WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.NamingConventions.ValidPostTypeSlug` sniff.
 *
 * @since 3.0.0
 */
final class FindReservedPostTypesSniff extends AbstractFunctionParameterSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'register_post_type' => array(
				'functions' => array(
					'register_post_type',
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

		$param = PassedParameters::getParameterFromStack( $parameters, 1, 'post_type' );

		// We only look for single quoted strings here as we won't be able to determine the name if it is a double quoted string.
		$skipOver                                = Tokens::$emptyTokens;
		$skipOver[ \T_CONSTANT_ENCAPSED_STRING ] = \T_CONSTANT_ENCAPSED_STRING;

		$hasNonTextString = $this->phpcsFile->findNext( $skipOver, $param['start'], ( $param['end'] + 1 ), true );
		if ( false !== $hasNonTextString ) {
			return;
		}

		$text = $this->phpcsFile->findNext( array( \T_CONSTANT_ENCAPSED_STRING ), $param['start'], ( $param['end'] + 1 ) );
		$name = TextStrings::stripQuotes( $this->tokens[ $text ]['content'] );

		$this->phpcsFile->recordMetric( $text, 'Reserved post types', $name );
	}
}
