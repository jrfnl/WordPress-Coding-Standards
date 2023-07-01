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
use PHPCSUtils\Utils\Conditions;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Scopes;
use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;
use WordPressCS\WordPress\Helpers\DeprecationHelper;

/**
 * Sniff to find classes marked as deprecated within WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.WP.DeprecatedClasses` sniff.
 *
 * @since 3.0.0
 */
final class FindDeprecatedClassesSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Cache of classes for which a notice has been thrown.
	 *
	 * @var array<string, bool>
	 */
	private $seen_classes = array();

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		$targets   = parent::register();
		$targets[] = \T_CLASS;

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
					'_deprecated_function',
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
		if ( \T_CLASS === $this->tokens[ $stackPtr ]['code'] ) {
			if ( DeprecationHelper::is_class_deprecated( $this->phpcsFile, $stackPtr ) === false ) {
				return;
			}

			$className     = ObjectDeclarations::getName( $this->phpcsFile, $stackPtr );
			$namespaceName = Namespaces::determineNamespace( $this->phpcsFile, $stackPtr );

			$name = $className;
			if ( '' !== $namespaceName ) {
				$name = $namespaceName . '\\' . $className;
			}

			if ( isset( $this->seen_classes[ $name ] ) === true ) {
				return;
			}

			$deprecatedTagPtr = $this->phpcsFile->findPrevious( \T_DOC_COMMENT_TAG, ( $stackPtr - 1 ), null, false, '@deprecated' );
			if ( false === $deprecatedTagPtr ) {
				// Shouldn't be possible.
				return;
			}

			$this->phpcsFile->addError( 'Class %s is deprecated since ...', $deprecatedTagPtr, 'Found', array( $name ) );
			$this->seen_classes[ $name ] = true;
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

		$functionName = FunctionDeclarations::getName( $this->phpcsFile, $functionPtr );
		if ( '__construct' !== strtolower( $functionName ) ) {
			return;
		}

		$classPtr = Scopes::validDirectScope( $this->phpcsFile, $functionPtr, Tokens::$ooScopeTokens );
		if ( false === $classPtr ) {
			return;
		}

		$className     = ObjectDeclarations::getName( $this->phpcsFile, $classPtr );
		$namespaceName = Namespaces::determineNamespace( $this->phpcsFile, $stackPtr );

		$name = $className;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $className;
		}

		if ( isset( $this->seen_classes[ $name ] ) === true ) {
			return;
		}

		$this->phpcsFile->addError( 'Class %s is deprecated since ...', $stackPtr, 'Found', array( $name ) );
		$this->seen_classes[ $name ] = true;
	}
}
