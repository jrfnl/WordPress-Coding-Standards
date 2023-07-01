<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\AnalyzeWPCore\Sniffs\WP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Scopes;

/**
 * Sniff to find functions which can be used to add admin pages within WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.Security.PluginMenuSlug` sniff.
 *
 * @since 3.0.0
 */
final class FindAdminMenuFunctionsSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( \T_FUNCTION );
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		if ( Scopes::isOOMethod( $phpcsFile, $stackPtr ) === true ) {
			// Method, not function. We're only interested in global functions.
			return;
		}

		$functionName = FunctionDeclarations::getName( $phpcsFile, $stackPtr );
		if ( preg_match( '`^add_\w+_page$`', $functionName ) !== 1 ) {
			return;
		}

		$params = FunctionDeclarations::getParameters( $phpcsFile, $stackPtr );
		if ( empty( $params ) ) {
			return;
		}

		$namespaceName = Namespaces::determineNamespace( $phpcsFile, $stackPtr );
		$name          = $functionName;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $functionName;
		}

		foreach ( $params as $pos => $param ) {
			if ( preg_match( '`^\$(?:\w+_)?slug$`', $param['name'] ) === 1 ) {
				$phpcsFile->recordMetric( $param['token'], 'Admin Menu adding functions', $name . ' - ' . $param['name'] . ' at pos ' . ( $pos + 1 ) );
			}
		}
	}
}
