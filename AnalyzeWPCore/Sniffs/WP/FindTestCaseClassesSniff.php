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
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;

/**
 * Sniff to find all TestCase classes within the WP Core test suite.
 *
 * The sniff is intended as a helper for updating the `IsUnitTestHelper` class.
 *
 * Should only be run on the `tests/phpunit/includes/` directory!
 *
 * Use the `info` report to get to the output.
 *
 * @since 3.0.0
 */
final class FindTestCaseClassesSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( \T_CLASS );
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

		$className = ObjectDeclarations::getName( $phpcsFile, $stackPtr );
		if ( empty( $className ) ) {
			return;
		}

		if ( preg_match( '`TestCase`i', $className ) !== 1 ) {
			return;
		}

		$namespaceName = Namespaces::determineNamespace( $phpcsFile, $stackPtr );
		$name          = $className;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $className;
		}

		$phpcsFile->recordMetric( $stackPtr, 'Test Case classes ', $name );
	}
}
