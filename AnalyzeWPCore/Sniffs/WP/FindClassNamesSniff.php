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
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\TextStrings;

/**
 * Sniff to find all classes within WP Core.
 *
 * The sniff is intended as a helper for updating the `WordPress.WP.ClassNameCase` sniff.
 *
 * Use the `info` report to get to the output.
 *
 * @since 3.0.0
 */
final class FindClassNamesSniff implements Sniff {

	/**
	 * Cache of classes for which a metric already has been recorded.
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
		return Tokens::$ooScopeTokens;
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

		// Usage of `stripQuotes` is to ensure `stdin_path` passed by IDEs does not include quotes.
		$file = TextStrings::stripQuotes( $phpcsFile->getFileName() );
		if ( 'STDIN' === $file ) {
			return;
		}

		$namespaceName = Namespaces::determineNamespace( $phpcsFile, $stackPtr );

		$name = $className;
		if ( '' !== $namespaceName ) {
			$name = $namespaceName . '\\' . $className;
		}

		if ( isset( $this->seen_classes[ $name ] ) === true ) {
			// Prevent the alphabetic ordering being scewed due to duplicate class declarations.
			return;
		}
		$this->seen_classes[ $name ] = true;

		$tokens = $phpcsFile->getTokens();
		$type   = 'class';
		if ( \T_INTERFACE === $tokens[ $stackPtr ]['code'] ) {
			$type = 'interface';
		} elseif ( \T_TRAIT === $tokens[ $stackPtr ]['code'] ) {
			$type = 'trait';
		} elseif ( \T_ENUM === $tokens[ $stackPtr ]['code'] ) {
			$type = 'enum';
		}

		if ( strpos( $file, '/src/wp-includes/ID3/' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\ID3\\' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'GetID3 ' . $type, $name );
			return;
		}

		if ( strpos( $file, '/src/wp-includes/PHPMailer/' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\PHPMailer\\' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'PHPMailer ' . $type, $name );
			return;
		}

		if ( strpos( $file, '/src/wp-includes/Requests/' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\Requests\\' ) !== false
			|| strpos( $file, '/src/wp-includes/class-requests.php' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\class-requests.php' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'Requests ' . $type, $name );
			return;
		}

		if ( strpos( $file, '/src/wp-includes/SimplePie/' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\SimplePie\\' ) !== false
			|| strpos( $file, '/src/wp-includes/class-simplepie.php' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\class-simplepie.php' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'SimplePie ' . $type, $name );
			return;
		}

		if ( strpos( $file, '/src/wp-includes/php-ai-client/' ) !== false
			|| strpos( $file, '\\src\\wp-includes\\php-ai-client\\' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'AI Client ' . $type, $name );
			return;
		}

		if ( strpos( $file, '/src/wp-content/themes/' ) !== false
			|| strpos( $file, '\\src\\wp-content\\themes\\' ) !== false
		) {
			$phpcsFile->recordMetric( $stackPtr, 'Theme ' . $type, $name );
			return;
		}

		$phpcsFile->recordMetric( $stackPtr, 'WP Core ' . $type, $name );
	}
}
