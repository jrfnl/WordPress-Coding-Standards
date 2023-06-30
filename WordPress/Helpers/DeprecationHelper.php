<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\WordPress\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;

/**
 * Helper utilities for checking whether something has been marked as deprecated.
 *
 * ---------------------------------------------------------------------------------------------
 * This class is only intended for internal use by WordPressCS and is not part of the public API.
 * This also means that it has no promise of backward compatibility. Use at your own risk.
 * ---------------------------------------------------------------------------------------------
 *
 * {@internal The functionality in this class will likely be replaced at some point in
 * the future by functions from PHPCSUtils.}
 *
 * @internal
 *
 * @since 3.0.0 The method in this class was previously contained in the
 *              `WordPressCS\WordPress\Sniff` class and has been moved here.
 */
final class DeprecationHelper {

	/**
	 * Check whether a function has been marked as deprecated via a @deprecated tag
	 * in the function docblock.
	 *
	 * @since 2.2.0
	 * @since 3.0.0 Moved from the Sniff class to this class.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of a T_FUNCTION
	 *                                               token in the stack.
	 *
	 * @return bool
	 */
	public static function is_function_deprecated( File $phpcsFile, $stackPtr ) {
		return self::is_deprecated( $phpcsFile, $stackPtr, Tokens::$methodPrefixes );
	}

	/**
	 * Check whether a language construct has been marked as deprecated via a @deprecated tag
	 * in the construct's docblock.
	 *
	 * @since x.x.x Split off from the `is_function_deprecated()` method.
	 *
	 * @param \PHP_CodeSniffer\Files\File   $phpcsFile The file being scanned.
	 * @param int                           $stackPtr  The position of a construct token in the stack.
	 * @param array<int|string, int|string> $skip_over Optional. List of tokens to ignore when trying to
	 *                                                 find the docblock for the construct.
	 *                                                 The list is expected to have the tokens
	 *                                                 as the array keys, value is irrelevant.
	 *                                                 Example: for a function docblock, the
	 *                                                 method prefixes, like `public`, `static`
	 *                                                 should be skipped over.
	 *
	 * @return bool
	 */
	public static function is_deprecated( File $phpcsFile, $stackPtr, array $skip_over = array() ) {
		$tokens = $phpcsFile->getTokens();
		if ( isset( $tokens[ $stackPtr ] ) === false ) {
			return false;
		}

		$skip_over[ \T_WHITESPACE ] = \T_WHITESPACE;

		for ( $comment_end = ( $stackPtr - 1 ); $comment_end >= 0; $comment_end-- ) {
			if ( isset( $skip_over[ $tokens[ $comment_end ]['code'] ] ) === true ) {
				continue;
			}

			if ( \T_ATTRIBUTE_END === $tokens[ $comment_end ]['code']
				&& isset( $tokens[ $comment_end ]['attribute_opener'] ) === true
			) {
				$comment_end = $tokens[ $comment_end ]['attribute_opener'];
				continue;
			}

			break;
		}

		if ( \T_DOC_COMMENT_CLOSE_TAG !== $tokens[ $comment_end ]['code'] ) {
			// Target doesn't have a doc comment or is using the wrong type of comment.
			return false;
		}

		$comment_start = $tokens[ $comment_end ]['comment_opener'];
		foreach ( $tokens[ $comment_start ]['comment_tags'] as $tag ) {
			if ( '@deprecated' === $tokens[ $tag ]['content'] ) {
				return true;
			}
		}

		return false;
	}
}
