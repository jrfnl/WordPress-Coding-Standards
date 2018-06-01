<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Theme;

use WordPress\Sniff;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Check if a theme uses include(_once) or require(_once) when get_template_part() should be used.
 *
 * @link    https://make.wordpress.org/themes/handbook/review/required/#core-functionality-and-features
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.xx.0
 */
class FileIncludeSniff extends Sniff {

	/**
	 * Regex template for the "files allowed for inclusion" check.
	 *
	 * This regex makes sure that the filename is either the complete string
	 * OR at the very end of the string, but preceded by a `/`.
	 *
	 * @var string
	 */
	const ALLOWED_REGEX_TEMPLATE = '`(?:^|/)(?:%s)$`';

	/**
	 * A list of file from which include/require calls are allowed/expected.
	 *
	 * @var array
	 */
	protected $file_whitelist = array(
		'functions.php'                   => true,
		'class-tgm-plugin-activation.php' => true,
	);

	/**
	 * A list of non-template files which are expected to be `include`d/`require`d.
	 *
	 * @var array
	 */
	protected $allowed_for_inclusion = array(
		'class-tgm-plugin-activation.php',
	);

	/**
	 * Internal storage for the "files allowed for inclusion" regex once parsed.
	 *
	 * @var string
	 */
	private $allowed_regex = '';

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		// Prepare the "files allowed for inclusion" regex only once.
		$regex_parts = array();
		foreach ( $this->allowed_for_inclusion as $file_name ) {
			$regex_parts[] = preg_quote( $file_name, '`' );
		}
		$regex_parts = array_filter( $regex_parts );

		if ( ! empty( $regex_parts ) ) {
			$this->allowed_regex = sprintf( self::ALLOWED_REGEX_TEMPLATE, implode( '|', $regex_parts ) );
		}

		return Tokens::$includeTokens;
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 */
	public function process_token( $stackPtr ) {

		$file_name = basename( $this->phpcsFile->getFileName() );
		if ( defined( 'PHP_CODESNIFFER_IN_TESTS' ) ) {
			// Allow for the whitelisted file functionality to be unit tested.
			$file_name = str_replace( '.inc', '.php', $file_name );
		}

		if ( isset( $this->file_whitelist[ $file_name ] ) ) {
			// Whitelisted file. We can ignore any other include/require statements found in this file.
			return $this->phpcsFile->numTokens;
		}

		$end = $this->phpcsFile->findNext( array( T_SEMICOLON, T_CLOSE_TAG ), ( $stackPtr + 1 ) );
		if ( false === $end ) {
			// Live coding/parse error.
			return;
		}

		if ( '' !== $this->allowed_regex ) {
			for ( $i = $end; $i > $stackPtr; $i-- ) {
				if ( isset( Tokens::$stringTokens[ $this->tokens[ $i ]['code'] ] ) === false ) {
					continue;
				}

				if ( preg_match( $this->allowed_regex, $this->strip_quotes( $this->tokens[ $i ]['content'] ) ) > 0 ) {
					// File which is allowed to be included/required.
					return;
				}

				break;
			}
		}

		$this->phpcsFile->addWarning(
			'Check that %s is not being used to load template files. "get_template_part()" should be used to load template files.',
			$stackPtr,
			'Found',
			array( $this->tokens[ $stackPtr ]['content'] )
		);
	}

}
