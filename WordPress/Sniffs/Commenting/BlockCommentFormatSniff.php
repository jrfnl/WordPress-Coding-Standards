<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Commenting;

use WordPress\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Verifies the formatting of a multi-line comment.
 *
 * - A multi-line comment should not be empty.
 * - The comment opener and closer should each be the only content on their own line.
 * - The first content of the multi-line comment should start on the line below the opener.
 * - The last content of the multi-line comment should be on the line above the closer.
 * - Each line in the comment should start with a star.
 * - Stars should be aligned with the first star in the comment opener.
 * - There should be a minimum of one space after each star (unless it is an empty comment line).
 *
 * This sniff doesn't concern itself with single-line vs multi-line comment styles.
 * This sniff doesn't concern itself with the indentation of the comment opener.
 * That is the concern of the `ScopeIndent` sniff.
 * Once that sniff has done it's work, however, this sniff will ensure that the indentation
 * of subsequent lines of the comment is aligned when compared to the indentation of the
 * comment opener on the first line.
 * The sniff will fix indentation with spaces. Tabs vs spaces is the concern of another sniff.
 *
 * @link    https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.16.0
 */
class BlockCommentFormatSniff extends Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_COMMENT,
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


ini_set( 'xdebug.overload_var_dump', 1 );

static $dumped = false;
if($dumped === false) {
	echo "\n";
	foreach( $this->tokens as $ptr => $token ) {
		if ( ! isset( $token['length'] ) ) {
			$token['length'] = strlen($token['content']);
		}
		if ( $token['code'] === T_WHITESPACE || $token['code'] === T_DOC_COMMENT_WHITESPACE ) {
			if ( strpos( $token['content'], "\t" ) !== false ) {
				$token['content'] = str_replace( "\t", '\t', $token['content'] );
			}
			if ( isset( $token['orig_content'] ) ) {
				$token['content'] .= ' :: Orig: ' . str_replace( "\t", '\t', $token['orig_content'] );
			}
		}
		echo $ptr . ' :: L' . str_pad( $token['line'] , 3, '0', STR_PAD_LEFT ) . ' :: C' . $token['column'] . ' :: ' . $token['type'] . ' :: (' . $token['length'] . ') :: ' . $token['content'] . "\n";
//		  if ( $token['code'] === T_WHILE || $token['code'] === T_DO || $token['code'] === T_FUNCTION ) {
//			  var_dump( $token );
//		  }
	}
	unset( $ptr, $token );
	$dumped = true;
}

return;

		/*
		 * Check one line comments.
		 * - Check for empty one-liners.
		 * - Check for exactly one space after opener.
		 * - Check for exactly one space before closer.
		 */
		if ( '*/' === substr( $this->tokens[ $stackPtr ]['content'], -2 ) ) {
			if ( preg_match( '`^/\*(\s*)(\S.*\S)?(\s*)\*/$`', $this->tokens[ $stackPtr ]['content'], $matches ) === 1 ) {
				if ( ! isset( $matches[2] ) || '' === $matches[2] ) {
					$this->phpcsFile->addError( 'Empty comment found.', $stackPtr, 'Empty' );
				} else {
					$fixes = array();
					$space_after_opener  = ( ! isset( $matches[1] ) ) ? 0 : strlen( $matches[1] );
					$space_before_closer = ( ! isset( $matches[3] ) ) ? 0 : strlen( $matches[3] );

					if ( 0 === $space_after_opener ) {
						$fixes[] = $this->phpcsFile->addFixableError(
							'There should be one space between the comment opener and the content.',
							$stackPtr,
							'NoSpaceAfterOpener'
						);
					} elseif ( $space_after_opener > 1 ) {
						$fixes[] = $this->phpcsFile->addFixableError(
							'There should be exactly one space between the comment opener and the content. Found %s',
							$stackPtr,
							'SpaceAfterOpener',
							array( $space_after_opener )
						);
					}

					if ( 0 === $space_before_closer ) {
						$fixes[] = $this->phpcsFile->addFixableError(
							'There should be one space between the comment content and the closer.',
							$stackPtr,
							'NoSpaceBeforeCloser'
						);
					} elseif ( $space_before_closer > 1 ) {
						$fixes[] = $this->phpcsFile->addFixableError(
							'There should be exactly one space between the comment content and the closer. Found %s',
							$stackPtr,
							'SpaceBeforeCloser',
							array ( $space_before_closer )
						);
					}
					
					// Fix it in one go as otherwise, we'd go into a fixer loop.
					if ( ! empty( $fixes ) ) {
						$replacement = '/* ' . $matches[2] . ' */';
						$this->phpcsFile->fixer->replaceToken( $stackPtr, $replacement );
					}
				}
			}

			return;
		}


		/*
		 * Find the end of multi-line block comment.
		 */
		$lines = array( $i );
		$comment_content = '';
		for ( $i = $stackPtr; $i < $this->phpcsFile->numTokens; $i++ ) {
			if ( T_COMMENT !== $this->tokens[ $i ]['code'] ) {
				$comment_closer = ( $i - 1 );
				break;
			}

			if ( '*/' === substr( $this->tokens[ $i ]['content'], -2 ) ) {
				$lines[]        = $i;
				$comment_closer = $i;
				break;
			}

			$lines[] = $i;
		}

		if ( ! isset( $comment_closer ) ) {
			// No comment closer found. Live coding.
			return;
		}

		// empty ?

		// Examine comment closer.
			// If ltrim( closer ) !== '*/' -> Comment closer should be on a line by itself.
			// If next non-whitespace on same line -> comment closer should be on a line by itself.

		// Examine what's before the comment opener.
			// If non-whitespace before -> Comment opener should be on a line by itself.
			// return as we don't know the indentation needed.

			// If rtrim( opener ) !== '/*' -> Comment opener should be on a line by itself.

		// Determine indentation first line.

		// Examine the lines in between
			// If trim() === '' -> indent + star + eol
			// `([\t ]*)(\*?)( *)(.*)`
			// [1] => check indent against first line indent
			// [2] => check that there is a star
			// [3] => check that there is at least one space


		if ( ! isset( $this->tokens[ $stackPtr ]['comment_closer'] ) ) {
			return;
		}

		$comment_closer = $this->tokens[ $stackPtr ]['comment_closer'];

		$empty = [
			T_DOC_COMMENT_WHITESPACE,
			T_DOC_COMMENT_STAR,
		];

		$next_content = $this->phpcsFile->findNext(
			array( T_DOC_COMMENT_WHITESPACE, T_DOC_COMMENT_STAR ),
			( $stackPtr + 1 ),
			$comment_closer,
			true
		);
		if ( false === $next_content ) {
			// No content at all.
			$this->phpcsFile->addError( 'A docblock should not be empty.', $stackPtr, 'Empty' );
		}

		/*
		 * Check whether the docblock closer is on its own line.
		 */
		$prev = $this->phpcsFile->findPrevious( T_DOC_COMMENT_WHITESPACE, ( $comment_closer - 1 ), null, true );
		if ( $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $prev ]['line'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'The docblock closer should be on a line by itself. Content found before closer.',
				$comment_closer,
				'ContentBeforeCloser'
			);

			if ( true === $fix ) {
				$trimmed = rtrim( $this->tokens[ $prev ]['content'], ' ' );
				$this->phpcsFile->fixer->replaceToken( $prev, $trimmed . $this->phpcsFile->eolChar );
			}
		}

		$next = $this->phpcsFile->findNext( T_WHITESPACE, ( $comment_closer + 1 ), null, true );
		if ( $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $next ]['line'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'The docblock closer should be on a line by itself. Content found after closer.',
				$comment_closer,
				'ContentAfterCloser'
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->addNewline( $comment_closer );
			}
		}
		unset( $prev, $next );

		/*
		 * Check docblock closer style.
		 */
		if ( '*/' !== $this->tokens[ $comment_closer ]['content'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'Block comments must be ended with */',
				$comment_closer,
				'WrongEnd'
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->replaceToken( $comment_closer, '*/' );
			}
		}

		/*
		 * Check whether the docblock opener is on its own line.
		 */
		$next = $this->phpcsFile->findNext( T_DOC_COMMENT_WHITESPACE, ( $stackPtr + 1 ), null, true );
		if ( $this->tokens[ $stackPtr ]['line'] === $this->tokens[ $next ]['line'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'The docblock opener should be on a line by itself. Content found after opener.',
				$stackPtr,
				'ContentAfterOpener'
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->addNewline( $stackPtr );
			}
		}

		$prev = $this->phpcsFile->findPrevious( T_WHITESPACE, ( $stackPtr - 1 ), null, true );
		if ( $this->tokens[ $stackPtr ]['line'] === $this->tokens[ $prev ]['line'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'The docblock opener should be on a line by itself. Content found before opener.',
				$stackPtr,
				'ContentBeforeOpener'
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->addNewline( $prev );
			}

			// If there is content before the opener, the alignment will be off anyway, so stop here.
			return;
		}

		unset( $prev, $next );

		/*
		 * Determine the required indentation based on the docblock opener.
		 */
		$required_indent = '';
		$required_column = $this->tokens[ $stackPtr ]['column'] + 1;

		if ( T_WHITESPACE === $this->tokens[ ( $stackPtr - 1 ) ]['code']
			&& $this->tokens[ ( $stackPtr - 1 ) ]['line'] === $this->tokens[ $stackPtr ]['line']
		) {
			// If tabs are being converted to spaces by the tokeniser, the
			// original content should be used instead of the converted content.
			if ( isset( $this->tokens[ ( $stackPtr - 1 ) ]['orig_content'] ) ) {
				$required_indent = $this->tokens[ ( $stackPtr - 1 ) ]['orig_content'];
			} else {
				$required_indent = $this->tokens[ ( $stackPtr - 1 ) ]['content'];
			}
		}

		$required_indent .= ' ';

		/*
		 * Check if that each line starts with a star and that the stars are correctly aligned.
		 */
		for ( $i = ( $stackPtr + 1 ); $i <= $comment_closer; $i++ ) {
			if ( 1 !== $this->tokens[ $i ]['column'] ) {
				continue;
			}

			if ( T_DOC_COMMENT_WHITESPACE !== $this->tokens[ $i ]['code'] ) {
				if ( T_DOC_COMMENT_STAR !== $this->tokens[ $i ]['code']
					&& T_DOC_COMMENT_CLOSE_TAG !== $this->tokens[ $i ]['code']
				) {
					$fix = $this->phpcsFile->addFixableError(
						'Each line in a docblock should start with an asterisk.',
						$i,
						'NoStar'
					);
					if ( true === $fix ) {
						$insert = $required_indent . '*';
						if ( isset( $this->tokens[ ( $i + 2 ) ] )
							&& $this->tokens[ $i ]['line'] === $this->tokens[ ( $i + 2 ) ]['line']
						) {
							// Not a blank line.
							$insert .= ' ';
						} else {
							$insert .= $this->phpcsFile->eolChar;
						}

						$this->phpcsFile->fixer->addContentBefore( $i, $insert );
					}
				} else {
					$error = 'Expected %s space(s) before the asterisk; %s found';
					$data  = array(
						( $required_column - 1 ),
						( $this->tokens[ $i ]['column'] - 1 ),
					);

					$fix = $this->phpcsFile->addFixableError( $error, $i, 'NoSpaceBeforeStar', $data );
					if ( true === $fix ) {
						$this->phpcsFile->fixer->addContentBefore( $i, $required_indent );
					}

					$this->verify_space_after_star( $i );
				}
			} elseif ( T_DOC_COMMENT_WHITESPACE === $this->tokens[ $i ]['code'] ) {

				if ( isset( $this->tokens[ ( $i + 1 ) ] ) ) {

					if ( T_DOC_COMMENT_STAR !== $this->tokens[ ( $i + 1 ) ]['code']
						&& T_DOC_COMMENT_CLOSE_TAG !== $this->tokens[ ( $i + 1 ) ]['code']
					) {
						$fix = $this->phpcsFile->addFixableError(
							'Each line in a docblock should start with an asterisk.',
							$i,
							'NoStar'
						);
						if ( true === $fix ) {
							$replacement = $required_indent . '*';
							if ( isset( $this->tokens[ ( $i + 2 ) ] )
								&& $this->tokens[ $i ]['line'] === $this->tokens[ ( $i + 2 ) ]['line']
							) {
								// Not a blank line.
								$replacement .= ' ';
							} else {
								$replacement .= $this->phpcsFile->eolChar;
							}

							$this->phpcsFile->fixer->replaceToken( $i, $replacement );
						}
					} else {

						if ( $this->tokens[ ( $i + 1 ) ]['column'] !== $required_column ) {
							$error = 'Expected %s space(s) before the asterisk; %s found';
							$data  = array(
								( $required_column - 1 ),
								( $this->tokens[ ( $i + 1 ) ]['column'] - 1 ),
							);

							$fix = $this->phpcsFile->addFixableError( $error, $i, 'SpaceBeforeStar', $data );
							if ( true === $fix ) {
								$this->phpcsFile->fixer->replaceToken( $i, $required_indent );
							}
						}

						$this->verify_space_after_star( $i + 1 );
					}
				}
			}
		}
	}

	/**
	 * Verify the spacing after a comment star.
	 *
	 * There should be - at least - one space between the asterix and the comment
	 * content. More is accepted to allow for, for instance, param comment alignment.
	 *
	 * @param int $stackPtr Stackpointer to the comment star.
	 *
	 * @return void
	 */
	protected function verify_space_after_star( $stackPtr ) {
		if ( T_DOC_COMMENT_STAR !== $this->tokens[ $stackPtr ]['code'] ) {
			return;
		}

		if ( ! isset( $this->tokens[ ( $stackPtr + 2 ) ] )
			|| $this->tokens[ $stackPtr ]['line'] !== $this->tokens[ ( $stackPtr + 2 ) ]['line']
		) {
			// Line is empty.
			return;
		}

		if ( T_DOC_COMMENT_WHITESPACE !== $this->tokens[ ( $stackPtr + 1 ) ]['code'] ) {
			$fix = $this->phpcsFile->addFixableError(
				'Expected 1 space after the asterisk; 0 found',
				$stackPtr,
				'NoSpaceAfterStar'
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->addContent( $stackPtr, ' ' );
			}
		} elseif ( T_DOC_COMMENT_TAG === $this->tokens[ ( $stackPtr + 2 ) ]['code']
			&& ' ' !== $this->tokens[ ( $stackPtr + 1 ) ]['content']
		) {
			$error = 'Expected 1 space after the asterisk; %s found';
			$data  = array( strlen( $this->tokens[ ( $stackPtr + 1 ) ]['content'] ) );
			$fix   = $this->phpcsFile->addFixableError( $error, $stackPtr, 'SpaceAfterStar', $data );
			if ( true === $fix ) {
				$this->phpcsFile->fixer->replaceToken( ( $stackPtr + 1 ), ' ' );
			}
		}
	}

}//end class
