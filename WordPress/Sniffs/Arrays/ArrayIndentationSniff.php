<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Arrays;

use WordPress\Sniff;
use WordPress\PHPCSHelper;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Enforces WordPress array indentation for multi-line arrays.
 *
 * @link    https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/#indentation
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.12.0
 * @since   0.13.0 Class name changed: this class is now namespaced.
 *
 * {@internal This sniff should eventually be pulled upstream as part of a solution
 * for https://github.com/squizlabs/PHP_CodeSniffer/issues/582 }}
 */
class ArrayIndentationSniff extends Sniff {

	/**
	 * Should tabs be used for indenting?
	 *
	 * If TRUE, fixes will be made using tabs instead of spaces.
	 * The size of each tab is important, so it should be specified
	 * using the --tab-width CLI argument.
	 *
	 * {@internal While for WPCS this should always be `true`, this property
	 * was added in anticipation of upstreaming the sniff.
	 * This property is the same as used in `Generic.WhiteSpace.ScopeIndent`.}}
	 *
	 * @var bool
	 */
	public $tabIndent = true;

	/**
	 * The --tab-width CLI value that is being used.
	 *
	 * @var int
	 */
	private $tab_width;

	/**
	 * Tokens to ignore when determining the start of an array item.
	 *
	 * Property is enriched in the register() method.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $start_ignore_tokens = array(
		\T_WHITESPACE             => \T_WHITESPACE,
		\T_DOC_COMMENT_WHITESPACE => \T_DOC_COMMENT_WHITESPACE,
	);

	/**
	 * Content to expect at the start of an inline comment.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $comment_openers = array(
		'//' => true,
		'/*' => true,
	);

	/**
	 * Tokens to ignore for subsequent lines in a multi-line array item.
	 *
	 * Property is set in the register() method.
	 *
	 * @var array
	 */
	private $ignore_tokens = array();

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		/*
		 * Set the $ignore_tokens property.
		 *
		 * Existing heredoc, nowdoc and inline HTML indentation should be respected at all times.
		 */
		$this->ignore_tokens = Tokens::$heredocTokens;
		unset( $this->ignore_tokens[ \T_START_HEREDOC ], $this->ignore_tokens[ \T_START_NOWDOC ] );
		$this->ignore_tokens[ \T_INLINE_HTML ] = \T_INLINE_HTML;

/*
		if ( isset( Tokens::$phpcsCommentTokens ) ) {
			$this->start_ignore_tokens = $this->start_ignore_tokens + Tokens::$phpcsCommentTokens;
		}
*/
		return array(
			\T_ARRAY,
			\T_OPEN_SHORT_ARRAY,
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process_token( $stackPtr ) {
		if ( ! isset( $this->tab_width ) ) {
			$this->tab_width = PHPCSHelper::get_tab_width( $this->phpcsFile );
		}
ini_set( 'xdebug.overload_var_dump', 1 );

static $dumped = false;
if($dumped === false) {
    echo "\n";
    foreach( $this->tokens as $ptr => $token ) {
        if ( ! isset( $token['length'] ) ) {
            $token['length'] = strlen($token['content']);
        }
        if ( $token['code'] === T_WHITESPACE || (defined('T_DOC_COMMENT_WHITESPACE') && $token['code'] === T_DOC_COMMENT_WHITESPACE) ) {
            if ( strpos( $token['content'], "\t" ) !== false ) {
                $token['content'] = str_replace( "\t", '\t', $token['content'] );
            }
            if ( isset( $token['orig_content'] ) ) {
                $token['content'] .= ' :: Orig: ' . str_replace( "\t", '\t', $token['orig_content'] );
            }
        }
        echo $ptr . ' :: L' . str_pad( $token['line'] , 3, '0', STR_PAD_LEFT ) . ' :: C' . $token['column'] . ' :: ' . $token['type'] . ' :: (' . $token['length'] . ') :: ' . $token['content'] . "\n";
/*
        if ( $token['code'] === T_COMMENT || isset( $this->phpcsCommentTokens[ $token['type'] ] ) ) {
            var_dump( $token );
        }
*/
    }
    unset( $ptr, $token );
    $dumped = true;
}

		/*
		 * Determine the array opener & closer.
		 */
		$array_open_close = $this->find_array_open_close( $stackPtr );
		if ( false === $array_open_close ) {
			// Array open/close could not be determined.
			return;
		}

		$opener = $array_open_close['opener'];
		$closer = $array_open_close['closer'];

		if ( $this->tokens[ $opener ]['line'] === $this->tokens[ $closer ]['line'] ) {
			// Not interested in single line arrays.
			return;
		}

		/*
		 * Check the closing bracket is lined up with the start of the content on the line
		 * containing the array opener.
		 */
		$opener_line_spaces = $this->get_indentation_size( $opener );
		$closer_line_spaces = ( $this->tokens[ $closer ]['column'] - 1 );

		if ( $closer_line_spaces !== $opener_line_spaces ) {
			$error      = 'Array closer not aligned correctly; expected %s space(s) but found %s';
			$error_code = 'CloseBraceNotAligned';

			/*
			 * Report & fix the issue if the close brace is on its own line with
			 * nothing or only indentation whitespace before it.
			 */
			if ( 0 === $closer_line_spaces
				|| ( \T_WHITESPACE === $this->tokens[ ( $closer - 1 ) ]['code']
					&& 1 === $this->tokens[ ( $closer - 1 ) ]['column'] )
			) {
				$this->add_array_alignment_error(
					$closer,
					$error,
					$error_code,
					$opener_line_spaces,
					$closer_line_spaces,
					$this->get_indentation_string( $opener_line_spaces )
				);
			} else {
				/*
				 * Otherwise, only report the error, don't try and fix it (yet).
				 *
				 * It will get corrected in a future loop of the fixer once the closer
				 * has been moved to its own line by the `ArrayDeclarationSpacing` sniff.
				 */
				$this->phpcsFile->addError(
					$error,
					$closer,
					$error_code,
					array( $opener_line_spaces, $closer_line_spaces )
				);
			}

			unset( $error, $error_code );
		}

		/*
		 * Verify & correct the array item indentation.
		 */
		$array_items = $this->get_function_call_parameters( $stackPtr );
		if ( empty( $array_items ) ) {
			// No array items found.
			return;
		}

		$expected_spaces      = ( $opener_line_spaces + $this->tab_width );
		$expected_indent      = $this->get_indentation_string( $expected_spaces );
		$end_of_previous_item = $opener;

		foreach ( $array_items as $param_nr => $item ) {
			$end_of_this_item = ( $item['end'] + 1 );

/*
			// Find the line on which the item starts.
			$first_content       = $this->phpcsFile->findNext(
				$this->start_ignore_tokens,
				$item['start'],
				$end_of_this_item,
				true
			);

			$first_fixer_content = $first_content;
*/

			// Deal with trailing comments belonging to the previous array item.
/*			while ( false !== $first_content
				&& ( \T_COMMENT === $this->tokens[ $first_content ]['code']
					|| isset( $this->phpcsCommentTokens[ $this->tokens[ $first_content ]['type'] ] ) )
			) {
*/
			$first_content = ( $item['start'] - 1 );
			do {
				// Find the line on which the item starts.
				$first_content = $this->phpcsFile->findNext(
					$this->start_ignore_tokens,
					( $first_content + 1 ),
					$end_of_this_item,
					true
				);

				if ( false === $first_content ) {
					break;
				}
				
				if ( \T_COMMENT !== $this->tokens[ $first_content ]['code']
					&& isset( $this->phpcsCommentTokens[ $this->tokens[ $first_content ]['type'] ] ) === false
				) {
					break;
				}

				// First content found is a comment or PHPCS annotation. This may be (part of) a trailing comment.
				$first_two_chars = substr( ltrim( $this->tokens[ $first_content ]['content'] ), 0, 2 );
				$last_two_chars  = substr( rtrim( $this->tokens[ $first_content ]['content'] ), -2 );

				if ( $this->tokens[ $first_content ]['line'] !== $this->tokens[ $end_of_previous_item ]['line']
					&& isset( $this->phpcsCommentTokens[ $this->tokens[ $first_content ]['type'] ] )
					&& isset( $this->comment_openers[ $first_two_chars] )
				) {
					// Non-trailing fixer comment belongs to this item.
					$first_fixer_content = $first_content;
					continue;
				}

				// TODO: this will break on a second trailing comment.
				if ( $this->tokens[ $first_content ]['line'] !== $this->tokens[ $end_of_previous_item ]['line']
					&& ( 1 !== $this->tokens[ $first_content ]['column']
						|| isset( $this->comment_openers[ $first_two_chars] ) )
				) {
					// New
					break;
				}

			} while ( true );

			if ( false === $first_content ) {
				$end_of_previous_item = $end_of_this_item;
				continue;
			}

			if ( ! isset( $first_fixer_content ) ) {
				$first_fixer_content = $first_content;
			}

			// Bow out from reporting and fixing mixed multi-line/single-line arrays.
			// That is handled by the ArrayDeclarationSpacingSniff.
			if ( $this->tokens[ $first_content ]['line'] === $this->tokens[ $end_of_previous_item ]['line']
				|| ( 1 !== $this->tokens[ $first_content ]['column']
					&& \T_WHITESPACE !== $this->tokens[ ( $first_content - 1 ) ]['code'] )
			) {
				return $closer;
			}

			$found_spaces = ( $this->tokens[ $first_content ]['column'] - 1 );

			if ( $found_spaces !== $expected_spaces ) {
				$this->add_array_alignment_error(
					$first_content,
					'Array item not aligned correctly; expected %s spaces but found %s',
					'ItemNotAligned',
					$expected_spaces,
					$found_spaces,
					$expected_indent
				);
			}

			// No need for further checking if this is a one-line array item.
/*
			if ( $this->tokens[ $first_content ]['line'] === $this->tokens[ $item['end'] ]['line'] ) {
				$end_of_previous_item = $end_of_this_item;
				continue;
			}
*/
			/*
			 * Multi-line array items.
			 *
			 * Verify & if needed, correct the indentation of subsequent lines.
			 * Subsequent lines may be indented more or less than the mimimum expected indent,
			 * but the "first line after" should be indented - at least - as much as the very first line
			 * of the array item.
			 * Indentation correction for subsequent lines will be based on that diff.
			 */

			// Find first token on second line of the array item.
			// If the second line is a heredoc/nowdoc, continue on until we find a line with a different token.
			// Same for the second line of a multi-line text string.
			for ( $ptr = ( $first_content + 1 ); $ptr <= $item['end']; $ptr++ ) {

/*
				if ( isset( $this->phpcsFile->tokenizer->ignoredLines[ $this->tokens[ $ptr ]['line'] ]['all'] ) ) {
					// Skip past lines which only contain PHPCS annotations and are being ignored completely.
					continue;
				}
*/
				if ( $this->tokens[ $first_content ]['line'] !== $this->tokens[ $ptr ]['line']
					&& 1 === $this->tokens[ $ptr ]['column']
					&& false === $this->ignore_token( $ptr )
				) {
					break;
				}
			}

			$first_content_on_line2 = $this->phpcsFile->findNext(
				$this->start_ignore_tokens,
				$ptr,
				$end_of_this_item,
				true
			);

			if ( false === $first_content_on_line2 ) {
				/*
				 * Apparently there were only tokens in the ignore list on subsequent lines.
				 *
				 * In that case, the comma after the array item might be on a line by itself,
				 * so check its placement.
				 */
				if ( $this->tokens[ $item['end'] ]['line'] !== $this->tokens[ $end_of_this_item ]['line']
					&& \T_COMMA === $this->tokens[ $end_of_this_item ]['code']
					&& ( $this->tokens[ $end_of_this_item ]['column'] - 1 ) !== $expected_spaces
				) {
					$this->add_array_alignment_error(
						$end_of_this_item,
						'Comma after multi-line array item not aligned correctly; expected %s spaces, but found %s',
						'MultiLineArrayItemCommaNotAligned',
						$expected_spaces,
						( $this->tokens[ $end_of_this_item ]['column'] - 1 ),
						$expected_indent
					);
				}
				
				/*
				 * Also check for potential multi-line trailing comments.
				 */
				$next_item_or_trailing_comment = $this->phpcsFile->findNext(
					$this->start_ignore_tokens,
					( $end_of_this_item + 1 ),
					$closer,
					true
				);

				if ( $this->tokens[ $next_item_or_trailing_comment ]['line'] === $this->tokens[ $end_of_this_item ]['line']
					&& ( \T_COMMENT === $this->tokens[ $next_item_or_trailing_comment ]['code']
						|| isset( $this->phpcsCommentTokens[ $this->tokens[ $next_item_or_trailing_comment ]['type'] ] ) )
					&& substr( ltrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), 0, 2 ) === '/*'
					&& substr( rtrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), -2 ) !== '*/'
				) {

					/*
					 * Found multi-line trailing comment.
					 * Fix lines until the end of the trailing comment is found.
					 * Fix PHPCS annotation lines regardless of whether this is a fixer run to force fix them.
					 */
					$fix    = false;
					$thrown = false;
					while ( ( $next_item_or_trailing_comment = $this->phpcsFile->findNext( $this->start_ignore_tokens, ( $next_item_or_trailing_comment + 1 ), $closer, true ) ) !== false
						&& ( \T_COMMENT === $this->tokens[ $next_item_or_trailing_comment ]['code']
							|| isset( $this->phpcsCommentTokens[ $this->tokens[ $next_item_or_trailing_comment ]['type'] ] ) )
						&& 1 === $this->tokens[ $next_item_or_trailing_comment ]['column']
					) {

						$found_spaces_on_line    = $this->get_indentation_size( $next_item_or_trailing_comment );
						$expected_spaces_on_line = ( $expected_spaces + ( $found_spaces_on_line - $found_spaces ) );
						$expected_spaces_on_line = max( $expected_spaces_on_line, 0 ); // Can't be below 0.
						$expected_indent_on_line = $this->get_indentation_string( $expected_spaces_on_line );

						if ( $found_spaces_on_line !== $expected_spaces_on_line ) {
							if ( false === $thrown ) {
								$fix = $this->phpcsFile->addFixableError(
									'Trailing comment after multi-line array item not aligned correctly; expected %s spaces, but found %s',
									$next_item_or_trailing_comment,
									'MultiLineTrailingCommentNotAligned',
									array( $expected_spaces_on_line, $found_spaces_on_line )
								);
							}

							if ( true === $fix
								|| isset( $this->phpcsCommentTokens[ $this->tokens[ $next_item_or_trailing_comment ]['type'] ] )
							) {
								$actual_comment = ltrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] );
								$replacement    = $expected_indent_on_line . $actual_comment;
	
								$this->phpcsFile->fixer->replaceToken( $next_item_or_trailing_comment, $replacement );
							}
						}

						if ( substr( rtrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), -2 ) === '*/' ) {
							// Find end of line
							/*
							for ( $i = ( $next_item_or_trailing_comment + 1 ); $i < $closer; $i++ ) {
								if ( $this->tokens[ $i ]['line'] !== $this->tokens[ $next_item_or_trailing_comment ]['line'] ) {
									--$i;
									break;
								}
							}
							*/

							$end_of_this_item = ( $next_item_or_trailing_comment + 1 );

							if ( isset( $array_items[ $param_nr + 1 ] ) ) {
								if ( $end_of_this_item > $array_items[ $param_nr + 1 ]['end'] ) {
									// This must have been the last item in the array and it had a trailing comment.
									break 2;
								}
				
								$array_items[ $param_nr + 1 ]['start'] = $end_of_this_item;
							}

							break;
						}
					}
					unset( $fix, $thrown );
				}

				$end_of_previous_item = $end_of_this_item;
				continue;
			}

			$found_spaces_on_line2    = $this->get_indentation_size( $first_content_on_line2 );
			$expected_spaces_on_line2 = $expected_spaces;

			if ( $found_spaces < $found_spaces_on_line2 ) {
				$expected_spaces_on_line2 += ( $found_spaces_on_line2 - $found_spaces );
			}

			if ( $found_spaces_on_line2 !== $expected_spaces_on_line2 ) {

				$fix = $this->phpcsFile->addFixableError(
					'Multi-line array item not aligned correctly; expected %s spaces, but found %s',
					$first_content_on_line2,
					'MultiLineArrayItemNotAligned',
					array(
						$expected_spaces_on_line2,
						$found_spaces_on_line2,
					)
				);

				if ( true === $fix ) {
					$expected_indent_on_line2 = $this->get_indentation_string( $expected_spaces_on_line2 );

					$this->phpcsFile->fixer->beginChangeset();

/*
				if ( isset( $this->phpcsFile->tokenizer->ignoredLines[ $this->tokens[ $ptr ]['line'] ]['all'] ) ) {
					// Skip past lines which only contain PHPCS annotations and are being ignored completely.
					continue;
				}
$first_content
*/
					// Fix second line for the array item.
					if ( 1 === $this->tokens[ $first_content_on_line2 ]['column']
						&& \T_COMMENT === $this->tokens[ $first_content_on_line2 ]['code']
// 							|| isset( $this->phpcsCommentTokens[ $this->tokens[ $first_content_on_line2 ]['type'] ] ) )
					) {
						$actual_comment = ltrim( $this->tokens[ $first_content_on_line2 ]['content'] );
						$replacement    = $expected_indent_on_line2 . $actual_comment;

						$this->phpcsFile->fixer->replaceToken( $first_content_on_line2, $replacement );

					} else {
						$this->fix_alignment_error( $first_content_on_line2, $expected_indent_on_line2 );
					}

					// Fix subsequent lines.
					for ( $i = ( $first_content_on_line2 + 1 ); $i <= $item['end']; $i++ ) {
						// We're only interested in the first token on each line.
						if ( 1 !== $this->tokens[ $i ]['column'] ) {
							if ( $this->tokens[ $i ]['line'] === $this->tokens[ $item['end'] ]['line'] ) {
								// We might as well quit if we're past the first token on the last line.
								break;
							}
							continue;
						}

						$first_content_on_line = $this->phpcsFile->findNext(
							$this->start_ignore_tokens,
							$i,
							$end_of_this_item,
							true
						);

						if ( false === $first_content_on_line ) {
							break;
						}

						// Ignore lines with heredoc and nowdoc tokens and subsequent lines in multi-line strings.
						if ( true === $this->ignore_token( $first_content_on_line ) ) {
							$i = $first_content_on_line;
							continue;
						}

						$found_spaces_on_line    = $this->get_indentation_size( $first_content_on_line );
						$expected_spaces_on_line = ( $expected_spaces_on_line2 + ( $found_spaces_on_line - $found_spaces_on_line2 ) );
						$expected_spaces_on_line = max( $expected_spaces_on_line, 0 ); // Can't be below 0.
						$expected_indent_on_line = $this->get_indentation_string( $expected_spaces_on_line );

						if ( $found_spaces_on_line !== $expected_spaces_on_line ) {
							if ( 1 === $this->tokens[ $first_content_on_line ]['column']
								&& ( \T_COMMENT === $this->tokens[ $first_content_on_line ]['code']
									|| isset( $this->phpcsCommentTokens[ $this->tokens[ $first_content_on_line2 ]['type'] ] ) )
							) {
								$actual_comment = ltrim( $this->tokens[ $first_content_on_line ]['content'] );
								$replacement    = $expected_indent_on_line . $actual_comment;

								$this->phpcsFile->fixer->replaceToken( $first_content_on_line, $replacement );
							} else {
								$this->fix_alignment_error( $first_content_on_line, $expected_indent_on_line );
							}
						}

						// Move past any potential empty lines between the previous non-empty line and this one.
						// No need to do the fixes twice.
						$i = $first_content_on_line;
					}

					/*
					 * Check the placement of the comma after the array item as it might be on a line by itself.
					 */
					if ( $this->tokens[ $item['end'] ]['line'] !== $this->tokens[ $end_of_this_item ]['line']
						&& \T_COMMA === $this->tokens[ $end_of_this_item ]['code']
						&& ( $this->tokens[ $end_of_this_item ]['column'] - 1 ) !== $expected_spaces_on_line2
					) {
						$this->fix_alignment_error( $end_of_this_item, $expected_indent_on_line2 );
					}

					/*
					 * Check for a multi-line trailing comment and if found, fix the indentation.
					 */
					$next_item_or_trailing_comment = $this->phpcsFile->findNext(
						$this->start_ignore_tokens,
						( $end_of_this_item + 1 ),
						$closer,
						true
					);

					if ( $this->tokens[ $next_item_or_trailing_comment ]['line'] === $this->tokens[ $end_of_this_item ]['line']
						&& ( \T_COMMENT === $this->tokens[ $next_item_or_trailing_comment ]['code']
							|| isset( $this->phpcsCommentTokens[ $this->tokens[ $next_item_or_trailing_comment ]['type'] ] ) )
						&& substr( ltrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), 0, 2 ) === '/*'
						&& substr( rtrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), -2 ) !== '*/'
					) {

						/*
						 * Found multi-line trailing comment.
						 * Fix lines until the end of the trailing comment is found.
						 */
						while ( ( $next_item_or_trailing_comment = $this->phpcsFile->findNext( $this->start_ignore_tokens, ( $next_item_or_trailing_comment + 1 ), $closer, true ) ) !== false
							&& ( \T_COMMENT === $this->tokens[ $next_item_or_trailing_comment ]['code']
								|| isset( $this->phpcsCommentTokens[ $this->tokens[ $next_item_or_trailing_comment ]['type'] ] ) )
							&& 1 === $this->tokens[ $next_item_or_trailing_comment ]['column']
						) {

							$found_spaces_on_line    = $this->get_indentation_size( $next_item_or_trailing_comment );
							$expected_spaces_on_line = ( $expected_spaces_on_line2 + ( $found_spaces_on_line - $found_spaces_on_line2 ) );
							$expected_spaces_on_line = max( $expected_spaces_on_line, 0 ); // Can't be below 0.
							$expected_indent_on_line = $this->get_indentation_string( $expected_spaces_on_line );

							if ( $found_spaces_on_line !== $expected_spaces_on_line ) {
								$actual_comment = ltrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] );
								$replacement    = $expected_indent_on_line . $actual_comment;

								$this->phpcsFile->fixer->replaceToken( $next_item_or_trailing_comment, $replacement );
							}

							if ( substr( rtrim( $this->tokens[ $next_item_or_trailing_comment ]['content'] ), -2 ) === '*/' ) {
								// Find end of line
								/*
								for ( $i = ( $next_item_or_trailing_comment + 1 ); $i < $closer; $i++ ) {
									if ( $this->tokens[ $i ]['line'] !== $this->tokens[ $next_item_or_trailing_comment ]['line'] ) {
										--$i;
										break;
									}
								}
								*/

								$end_of_this_item = ( $next_item_or_trailing_comment + 1 );

								if ( isset( $array_items[ $param_nr + 1 ] ) ) {
									if ( $end_of_this_item > $array_items[ $param_nr + 1 ]['end'] ) {
										// This must have been the last item in the array and it had a trailing comment.
										break 2;
									}
					
									$array_items[ $param_nr + 1 ]['start'] = $end_of_this_item;
								}

								break;
							}
						}
					}

					$this->phpcsFile->fixer->endChangeset();
				}
			}

			$end_of_previous_item = $end_of_this_item;
		}
	}

	/**
	 * Should the token be ignored ?
	 *
	 * This method is only intended to be used with the first token on a line
	 * for subsequent lines in an multi-line array item.
	 *
	 * @param int $ptr Stack pointer to the first token on a line.
	 *
	 * @return bool
	 */
	protected function ignore_token( $ptr ) {
		$token_code = $this->tokens[ $ptr ]['code'];

		if ( isset( $this->ignore_tokens[ $token_code ] ) ) {
			return true;
		}

		/*
		 * If it's a subsequent line of a multi-line sting, it will not start with a quote
		 * character, nor just *be* a quote character.
		 */
		if ( \T_CONSTANT_ENCAPSED_STRING === $token_code
			|| \T_DOUBLE_QUOTED_STRING === $token_code
		) {
			// Deal with closing quote of a multi-line string being on its own line.
			if ( "'" === $this->tokens[ $ptr ]['content']
				|| '"' === $this->tokens[ $ptr ]['content']
			) {
				return true;
			}

			// Deal with subsequent lines of a multi-line string where the token is broken up per line.
			if ( "'" !== $this->tokens[ $ptr ]['content'][0]
				&& '"' !== $this->tokens[ $ptr ]['content'][0]
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine the line indentation whitespace.
	 *
	 * @param int $ptr Stack pointer to an arbitrary token on a line.
	 *
	 * @return int Nr of spaces found. Where necessary, tabs are translated to spaces.
	 */
	protected function get_indentation_size( $ptr ) {

		// Find the first token on the line.
		for ( ; $ptr >= 0; $ptr-- ) {
			if ( 1 === $this->tokens[ $ptr ]['column'] ) {
				break;
			}
		}

		$whitespace = '';

		if ( \T_WHITESPACE === $this->tokens[ $ptr ]['code']
			|| \T_DOC_COMMENT_WHITESPACE === $this->tokens[ $ptr ]['code']
		) {
			return $this->tokens[ $ptr ]['length'];
		}

		/*
		 * Special case for multi-line, non-docblock comments.
		 * Only applicable for subsequent lines in an array item.
		 *
		 * First/Single line is tokenized as T_WHITESPACE + T_COMMENT
		 * Subsequent lines are tokenized as T_COMMENT including the indentation whitespace.
		 */
		if ( \T_COMMENT === $this->tokens[ $ptr ]['code']
			|| isset( $this->phpcsCommentTokens[ $this->tokens[ $ptr ]['type'] ] )
		) {
			$content        = $this->tokens[ $ptr ]['content'];
			$actual_comment = ltrim( $content );
			$whitespace     = str_replace( $actual_comment, '', $content );
		}

		return \strlen( $whitespace );
	}

	/**
	 * Create an indentation string.
	 *
	 * @param int $nr Number of spaces the indentation should be.
	 *
	 * @return string
	 */
	protected function get_indentation_string( $nr ) {
		if ( 0 >= $nr ) {
			return '';
		}

		// Space-based indentation.
		if ( false === $this->tabIndent ) {
			return str_repeat( ' ', $nr );
		}

		// Tab-based indentation.
		$num_tabs    = (int) floor( $nr / $this->tab_width );
		$remaining   = ( $nr % $this->tab_width );
		$tab_indent  = str_repeat( "\t", $num_tabs );
		$tab_indent .= str_repeat( ' ', $remaining );

		return $tab_indent;
	}

	/**
	 * Throw an error and fix incorrect array alignment.
	 *
	 * @param int    $ptr        Stack pointer to the first content on the line.
	 * @param string $error      Error message.
	 * @param string $error_code Error code.
	 * @param int    $expected   Expected nr of spaces (tabs translated to space value).
	 * @param int    $found      Found nr of spaces (tabs translated to space value).
	 * @param string $new_indent Whitespace indent replacement content.
	 */
	protected function add_array_alignment_error( $ptr, $error, $error_code, $expected, $found, $new_indent ) {

		$fix = $this->phpcsFile->addFixableError( $error, $ptr, $error_code, array( $expected, $found ) );
		if ( true === $fix ) {
			$this->fix_alignment_error( $ptr, $new_indent );
		}
	}

	/**
	 * Fix incorrect array alignment.
	 *
	 * @param int    $ptr        Stack pointer to the first content on the line.
	 * @param string $new_indent Whitespace indent replacement content.
	 */
	protected function fix_alignment_error( $ptr, $new_indent ) {
		if ( 1 === $this->tokens[ $ptr ]['column'] ) {
			$this->phpcsFile->fixer->addContentBefore( $ptr, $new_indent );
		} else {
			$this->phpcsFile->fixer->replaceToken( ( $ptr - 1 ), $new_indent );
		}
	}

}
