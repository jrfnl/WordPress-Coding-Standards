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
 * Verifies the formatting of a block comment.
 *
 * - A block comment should not be empty.
 * - For a multi-line block, the comment opener and closer should each be the only
 *   content on the line.
 * - The first content of the multi-line block comment should start on the line below the opener.
 * - The last content of the multi-line block comment should be on the line above the closer.
 * - Each line in a multi-line block comment should start with a star.
 * - Stars should be aligned with the star in the comment opener.
 * - There should be a minimum of one space after each star (unless it is an empty comment line).
 * - In the case of single-line block comment, there should be one space after the opener
 *   and before the closer.
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

		// Not interested in //-style inline comments.
		if ( substr( $this->tokens[ $stackPtr ]['content'], 0, 2 ) !== '/*' ) {
			return;
		}

		/*
		 * Check one line comments.
		 * - Check for empty one-liners.
		 * - Check for exactly one space after opener.
		 * - Check for exactly one space before closer.
		 */
		if ( '*/' === substr( $this->tokens[ $stackPtr ]['content'], -2 ) ) {
			if ( preg_match( '`^/\*(\s*)((?:\S.*\S)?)(\s*)\*/$`', $this->tokens[ $stackPtr ]['content'], $matches ) === 1 ) {
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

return;

		/*
		 * Determine the required indentation for multi-line block comments
		 * based on the block comment opener.
		 */
		$required_indent = '';
		$required_column = ( $this->tokens[ $stackPtr ]['column'] + 1 );
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
		
		// Set expected spaces for easy compare with converted content.
		$expected_spaces  = str_repeat( ' ', $required_column );

		/*
		 * Find all parts of a multi-line block comment.
		 */
		$comment_opener      = $stackPtr;
		$comment_closer      = null;
		$first_content_token = null;
		$last_content_token  = null;
		$lines               = array();

		$opener_content      = substr( $this->tokens[ $stackPtr ]['content'], 2 );
		$comment_text        = trim( $opener_content );
		if ( '' !== $comment_text ) {
			$first_content_token = $stackPtr;
		}

		for ( $i = ( $stackPtr + 1 ); $i < $this->phpcsFile->numTokens; $i++ ) {
			if ( T_COMMENT !== $this->tokens[ $i ]['code'] ) {
				break;
			}

			if ( preg_match( "`^([ \t]*)(\*?)([ \t]*)(\S?.*)$`s", $this->tokens[ $i ]['content'], $matches ) === 1 ) {

				$content         = ( isset( $matches[4] ) ? $matches[4] : '' );
				$trimmed_content = trim( $content );

				if ( '*/' === substr( $this->tokens[ $i ]['content'], -2 ) ) {
// Hier klopt nog iets niet....
					$trimmed_content  = substr( $trimmed_content, 0, ( strlen( $trimmed_content ) - 2 ) );
					$trimmed_content .= trim( $trimmed_content );
					$comment_closer   = $i;
//					break;
				}

				$comment_text .= $trimmed_content;

				$lines[ $i ] = array(
					'indent'           => ( isset( $matches[1] ) ? $matches[1] : '' ),
					'has_star'         => ( isset( $matches[2] ) && '*' === $matches[2] ? true : false ),
					'space_after_star' => ( isset( $matches[3] ) ? $matches[3] : '' ),
					'content'          => $content,
					'trimmed_content'  => $trimmed_content,
					'is_blank'         => ( '' === $trimmed_content ? true : false ),
				);

				if ( '' !== $trimmed_content ) {
					$last_content_token = $i;

					if ( ! isset( $first_content_token ) ) {
						$first_content_token = $i;
					}
				}

				if ( isset( $comment_closer ) ) {
					break;
				}

			} else {
				$lines[ $i ] = array(
					'malformed' => true,
				);
			}
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


		/**
		 * Check for empty block comments.
		 */
		$is_empty = false;
		if ( ! isset ( $first_content_token ) || trim( $comment_text ) === '' ) {
			$this->phpcsFile->addError( 'A block comment should not be empty.', $stackPtr, 'Empty' );

			$is_empty = true; // Prevent reporting on empty lines when block comment is completely empty.
		}

		/*
		 * Examine the docblock closer.
		 */
		if ( false === $is_empty ) {
			if ( ( $last_content_token + 1 ) < $comment_closer ) {

				$fix = $this->phpcsFile->addFixableError(
					'Blank line(s) found at end of block comment.',
					( $last_content_token + 1 ),
					'BlankLineBeforeCloser'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->beginChangeset();

					for ( $i = ( $last_content_token + 1 ); $i < $comment_closer; $i++ ) {
						if ( $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $i ]['line'] ) {
							break;
						}

						// Make double sure that we're not removing anything we shouldn't.
						if ( trim( $this->tokens[ $i ]['content'], " \t\r\n*" ) === '' ) {
							$this->phpcsFile->fixer->replaceToken( $i, '' );
						}
					}

					$this->phpcsFile->fixer->endChangeset();
				}
			}
		}
// fix from here
		$prev = $this->phpcsFile->findPrevious( T_DOC_COMMENT_WHITESPACE, ( $comment_closer - 1 ), null, true );
		if ( false !== $prev
			&& $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $prev ]['line']
		) {
			$fix = $this->phpcsFile->addFixableError(
				'The docblock closer should be on a line by itself. Content found before closer.',
				$prev,
				'ContentBeforeCloser'
			);

			if ( true === $fix ) {
				$trimmed = rtrim( $this->tokens[ $prev ]['content'], ' ' );
				$this->phpcsFile->fixer->replaceToken( $prev, $trimmed . $this->phpcsFile->eolChar );
			}
		}
		unset( $prev );

		$next = $this->phpcsFile->findNext( T_WHITESPACE, ( $comment_closer + 1 ), null, true );
		if ( false !== $next ) {
			if ( $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $next ]['line'] ) {
				$fix = $this->phpcsFile->addFixableError(
					'The docblock closer should be on a line by itself. Content found after closer.',
					$next,
					'ContentAfterCloser'
				);

				if ( true === $fix ) {
					// @JRF Or should this be add new line before $next ?
					$this->phpcsFile->fixer->addNewline( $comment_closer );
				}
			} elseif ( ( $this->tokens[ $comment_closer ]['line'] + 1 ) !== $this->tokens[ $next ]['line'] ) {
				$error      = 'There should be no blank line after a docblock.';
				$error_code = 'BlankLineAfter';

				if ( isset( Tokens::$commentTokens[ $this->tokens[ $next ]['code'] ] ) ) {
					// Only report the error, don't try to fix it as it would cause a fixer conflict.
					$this->phpcsFile->addError( $error, $comment_closer, $error_code );
				} else {
					$fix = $this->phpcsFile->addFixableError( $error, $comment_closer, $error_code );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->beginChangeset();

						for ( $i = ( $comment_closer + 1 ); $i < $next; $i++ ) {
							if ( $this->tokens[ $next ]['line'] === $this->tokens[ $i ]['line'] ) {
								break;
							}

							if ( $this->tokens[ $comment_closer ]['line'] === $this->tokens[ $i ]['line'] ) {
								continue;
							}

							$this->phpcsFile->fixer->replaceToken( $i, '' );
						}

						$this->phpcsFile->fixer->endChangeset();
					}
				}
			}
		}
		unset( $next );

		/*
		 * Check docblock closer style.
		 */
		if ( '*/' !== trim( $this->tokens[ $comment_closer ]['content'] ) ) {
			$this->phpcsFile->addError(
				'Block comments must be ended with */',
				$comment_closer,
				'WrongEnd'
			);
		}

		/*
		 * Examine the docblock opener.
		 */
/*OK*/		if ( false === $is_empty ) {
			if ( ( $stackPtr + 1 ) < $first_content_token ) {

				$fix = $this->phpcsFile->addFixableError(
					'Blank line(s) found at start of block comment.',
					( $stackPtr + 1 ),
					'BlankLineAfterOpener'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->beginChangeset();

					for ( $i = ( $stackPtr + 1 ); $i < $first_content_token; $i++ ) {
						if ( $this->tokens[ $first_content_token ]['line'] === $this->tokens[ $i ]['line'] ) {
							break;
						}

						// Make double sure that we're not removing anything we shouldn't.
						if ( trim( $this->tokens[ $i ]['content'], " \t\r\n*" ) === '' ) {
							$this->phpcsFile->fixer->replaceToken( $i, '' );
						}
					}

					$this->phpcsFile->fixer->endChangeset();
				}
			}
		}
// TODO
		if ( false === $allow_short ) {
			$next = $this->phpcsFile->findNext( T_DOC_COMMENT_WHITESPACE, ( $stackPtr + 1 ), null, true );
			if ( false !== $next
				&& $this->tokens[ $stackPtr ]['line'] === $this->tokens[ $next ]['line']
			) {
				$fix = $this->phpcsFile->addFixableError(
					'The docblock opener should be on a line by itself. Content found after opener.',
					$next,
					'ContentAfterOpener'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addNewline( $stackPtr );
				}
			}
			unset( $next );
		}

		$prev = $this->phpcsFile->findPrevious( T_WHITESPACE, ( $stackPtr - 1 ), null, true );
		if ( false !== $prev ) {
			if ( $this->tokens[ $stackPtr ]['line'] === $this->tokens[ $prev ]['line'] ) {
				$fix = $this->phpcsFile->addFixableError(
					'The docblock opener should be on a line by itself. Content found before opener.',
					$prev,
					'ContentBeforeOpener'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addNewline( $prev );
				}

				// If there is content before the opener, the alignment will be off anyway, so stop here.
				return;

			} elseif ( ( $this->tokens[ $stackPtr ]['line'] - $this->tokens[ $prev ]['line'] ) < 2 ) {
				$error      = 'A blank line is required before a docblock.';
				$error_code = 'NoBlankLineBefore';

				if ( isset( Tokens::$commentTokens[ $this->tokens[ $prev ]['code'] ] ) ) {
					// Only report the error, don't try to fix it as it would cause a fixer conflict.
					$this->phpcsFile->addError( $error, $stackPtr, $error_code );
				} else {
					$fix = $this->phpcsFile->addFixableError( $error, $stackPtr, $error_code );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->addNewline( $prev );
					}
				}
			}
		}
		unset( $prev );

		if ( true === $is_one_liner && true === $allow_short ) {
			/*
			 * Check spacing within a one-liner.
			 */
			if ( T_DOC_COMMENT_WHITESPACE !== $this->tokens[ ( $stackPtr + 1 ) ]['code'] ) {
				$fix = $this->phpcsFile->addFixableError(
					'There should be one space between the comment opener and the content.',
					$stackPtr,
					'NoSpaceAfterOpener'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addContent( $stackPtr, ' ' );
				}
			} elseif ( ' ' !== $this->tokens[ ( $stackPtr + 1 ) ]['content'] ) {
				$fix = $this->phpcsFile->addFixableError(
					'There should be one space between the comment opener and the content.',
					$stackPtr,
					'SpaceAfterOpener'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->replaceToken( ( $stackPtr + 1 ), ' ' );
				}
			}

			$pre_closer_content         = $this->tokens[ ( $comment_closer - 1 ) ]['content'];
			$trimmed_pre_closer_content = rtrim( $pre_closer_content, ' ' );
			$whitespace_before_closer   = str_replace( $trimmed_pre_closer_content, '', $pre_closer_content );
			if ( ' ' !== $whitespace_before_closer ) {
				$length     = strlen( $whitespace_before_closer );
				$error_code = ( 0 === $length ) ? 'NoSpaceBeforeCloser' : 'SpaceBeforeCloser';

				$fix = $this->phpcsFile->addFixableError(
					'There should be one space between the comment content and the closer. Found %s',
					$comment_closer,
					$error_code,
					array( $length )
				);

				if ( true === $fix ) {
					if ( 0 === $length ) {
						$this->phpcsFile->fixer->addContent( ( $comment_closer - 1 ), ' ' );
					} else {
						$this->phpcsFile->fixer->replaceToken( ( $comment_closer - 1 ), $trimmed_pre_closer_content . ' ' );
					}
				}
			}

			// No need to check the rest for one-liners.
			return;
		}

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
		 * Check that there are no consecutive blank lines.
		 */
		if ( false !== $first_doc_content ) {
			$prev_doc_content = $first_doc_content;
			while ( $next_doc_content = $this->phpcsFile->findNext( $this->empty_doc_tokens, ( $prev_doc_content + 1 ), $comment_closer, true ) ) {

				if ( $this->tokens[ $next_doc_content ]['line'] > ( $this->tokens[ $prev_doc_content ]['line'] + 2 ) ) {
					$fix = $this->phpcsFile->addFixableError(
						'Multiple consecutive blank lines are not allowed in a docblock.',
						( $prev_doc_content + 2 ),
						'SuperfluousBlankLines'
					);

					if ( true === $fix ) {
						$this->phpcsFile->fixer->beginChangeset();
						for ( $i = ( $prev_doc_content + 1 ); $i < $next_doc_content; $i++ ) {
							if ( $this->tokens[ $i ]['line'] === $this->tokens[ $next_doc_content ]['line'] ) {
								break;
							}

							$this->phpcsFile->fixer->replaceToken( $i, '' );
						}

						$this->phpcsFile->fixer->addContent(
							$prev_doc_content,
							$this->phpcsFile->eolChar . $required_indent . '*' . $this->phpcsFile->eolChar
						);
						$this->phpcsFile->fixer->endChangeset();
					}
				}

				$prev_doc_content = $next_doc_content;
			}
		}
		unset( $first_doc_content );

		/*
		 * Check that there is exactly one blank line before the first tag.
		 */
		if ( isset( $this->tokens[ $stackPtr ]['comment_tags'][0] ) ) {
			$first_tag        = $this->tokens[ $stackPtr ]['comment_tags'][0];
			$prev_doc_content = $this->phpcsFile->findPrevious(
				$this->empty_doc_tokens,
				( $first_tag - 1 ),
				( $stackPtr + 1 ), // The open tag should not be considered content.
				true
			);
			if ( false !== $prev_doc_content
				&& $this->tokens[ $first_tag ]['line'] < ( $this->tokens[ $prev_doc_content ]['line'] + 2 )
			) {
				$fix = $this->phpcsFile->addFixableError(
					'There must be exactly one blank line before the tags in a doc comment.',
					$first_tag,
					'NoBlankLineBeforeTags'
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addContent(
						$prev_doc_content,
						$this->phpcsFile->eolChar . $required_indent . '*' . $this->phpcsFile->eolChar
					);
				}
			}
			unset( $first_tag, $prev_doc_content );
		}

		/*
		 * Check that each line starts with a star, that the stars are correctly aligned.
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
