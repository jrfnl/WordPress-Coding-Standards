<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\AnalyzeWPCore\Sniffs\WP;

use PHPCSUtils\Utils\Conditions;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Sniff to find function arguments marked as deprecated within WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.WP.DeprecatedParameters`
 * and the `WordPress.WP.DeprecatedParameterValues` sniffs.
 *
 * @since 3.0.0
 */
final class FindDeprecatedParametersSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Cache of functions for which a notice has been thrown.
	 *
	 * @var array<string, bool>
	 */
	private $seen_functions = array();

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		$targets   = parent::register();
		$targets[] = \T_FUNCTION;

		return $targets;
	}

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'deprecation' => array(
				'functions' => array(
					'_deprecated_argument',
				),
			),
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_token( $stackPtr ) {
		if ( \T_FUNCTION === $this->tokens[ $stackPtr ]['code'] ) {
			if ( Scopes::isOOMethod( $this->phpcsFile, $stackPtr ) === true ) {
				// Method, not function. We don't handle those (yet) in WPCS.
				return;
			}

			$params = FunctionDeclarations::getParameters( $this->phpcsFile, $stackPtr );
			if ( empty( $params ) ) {
				return;
			}

			// TODO: Get default value.
			// TODO: see about finding a @since tag which shows deprecation version.

			foreach ( $params as $pos => $param ) {
				if ( strpos( $param['name'], '$deprecated' ) === 0 ) {
					// Old-style WP way for deprecated params.
					$functionName = FunctionDeclarations::getName( $this->phpcsFile, $stackPtr );
					$this->phpcsFile->addError(
						'Parameter %d in function %s() is deprecated since ...',
						$param['token'],
						'Found',
						array( ( $pos + 1 ), $functionName )
					);
				}
			}
		}

		// For everything else defer to the abstract.
		parent::process_token( $stackPtr );

//		$phpcsFile->recordMetric( $stackPtr, 'Prefix all globals: potential prefixes - start of non-prefixed construct', strtolower( $matches[1] ) );
	}

	/**
	 * Process a matched token.
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param string $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_matched_token( $stackPtr, $group_name, $matched_content ) {
		$functionPtr = Conditions::getLastCondition( $this->phpcsFile, $stackPtr, array( \T_FUNCTION ) );
		if ( false === $functionPtr ) {
			return;
		}

		if ( Scopes::isOOMethod( $this->phpcsFile, $functionPtr ) === true ) {
			// Method, not function. We don't handle those (yet).
			return;
		}

		$functionName = FunctionDeclarations::getName( $this->phpcsFile, $functionPtr );

		// Maybe: check if first param is `define()` and if so, bow out, constant not param.
		// Check second param for version nr.
		// Check third param for alternative.

		$this->phpcsFile->addError(
			'Parameter in function %s() is deprecated since ...',
			$stackPtr,
			'Found',
			array( $functionName )
		);
	}
}
