<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\WhiteSpace;

use WordPress\Sniff;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Check for and enforces spacing in `use` statements.
 *
 * Generic:
 * - There should be exactly one space between `use`, `function`, `const` keywords
 *   and the start of the classname.
 * - If an alias is given, there should be exactly one space before and one space
 *   after the `as` keyword and the alias should be on the same line as the "thing"
 *   being aliased.
 * - If `private`, `public`, `protected` or `final` keywords are used, again surrounded
 *   by exactly one space (except when they are directly followed by a semi-colon).
 * - Multi-use statements: no space before the comma, exactly one space or a new line after.
 * - Multi-use statement: subsequent items should be indented one tab in from the `use` keyword.
 *
 * Namespace use statements:
 * - Class names should not start with a `\` as that is a given.
 * - Group use statements:
 *   - There should be no space between the namespace separator and the brace.
 *   - There should be exactly one space or a new line after the open brace.
 *   - There should be exactly one space or a new line before the close brace.
 *   - There should be no space between the close brace and the colon.
 *   - Should be either truly single line or truly multi-line, i.e.:
 *     Single line: all items on one line and the open and close brace on that same line.
 *     Multi-line:
 *     - Open brace on same line as `use` keyword, each item and the close brace
 *       on their own line.
 *     - Close brace aligned with the `use` keyword.
 *     - Items indented one tab in from the `use` keyword.
 *   - Trailing comma's are allowed after the last item in a group use statement. (PHP 7.2)
 *
 * Closure use statements:
 * - There should be exactly one space between the function close parenthesis and the
 *   `use` keyword.
 * - There should be exactly one space between the `use` statement and the open parenthesis.
 * - There should be exactly one space on the inside of each of the use statement parenthesis.
 * - There should be exactly one space between the close parenthesis and the open brace.
 *
 * <code>
 *   function ( $quantity, $product ) use ( $tax, &$total ) {};
 * </code>
 *
 * Trait use statements:
 * - There should be exactly one space before the open brace.
 * - Other rules around single/multi-line same as for namespaces.
 * - There should be exactly one space before and after the `insteadof` keyword.
 *
 *
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.14.0
 */
class UseStatementSpacingSniff extends Sniff {

	/**
	 * Check for blank lines on start/end of control structures.
	 *
	 * @var boolean
	 */
//	public $blank_line_check = false;

	/**
	 * Check for blank lines after control structures.
	 *
	 * @var boolean
	 */
//	public $blank_line_after_check = true;

	/**
	 * Require for space before T_COLON when using the alternative syntax for control structures.
	 *
	 * @var string one of 'required', 'forbidden', 'optional'
	 */
//	public $space_before_colon = 'required';

	/**
	 * How many spaces should be between a T_CLOSURE and T_OPEN_PARENTHESIS.
	 *
	 * `function[*]() {...}`
	 *
	 * @since 0.7.0
	 *
	 * @var int
	 */
//	public $spaces_before_closure_open_paren = -1;

	/**
	 * Tokens for which to ignore extra space on the inside of parenthesis.
	 *
	 * For functions, this is already checked by the Squiz.Functions.FunctionDeclarationArgumentSpacing sniff.
	 * For do / else / try, there are no parenthesis, so skip it.
	 *
	 * @since 0.11.0
	 *
	 * @var array
	 */
/*	private $ignore_extra_space_after_open_paren = array(
		T_FUNCTION => true,
		T_CLOSURE  => true,
		T_DO       => true,
		T_ELSE     => true,
		T_TRY      => true,
	);
*/

/*
	private $keyword_tokens = array(
		'T_USE' =>
		'T_COMMA':
		'T_AS':
		'T_INSTEADOF':
		'T_PUBLIC':
		'T_PROTECTED':
		'T_PRIVATE':
		'T_FINAL':
  	);
*/

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @since 0.14.0
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_USE,
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
        if ( $token['code'] === T_USE ) {
            var_dump( $token );
            var_dump( $this->get_use_type( $ptr ) );
        }
    }
    unset( $ptr, $token );
    $dumped = true;
}


		/*
		Walk
		*/

		$use_type = $this->get_use_type( $stackPtr );
		
		if ( 'closure' === $use_type ) {
		}








		$close_token = T_SEMICOLON;
		$multiline   = false;
		$first_comma = null;

		for ( $i = $stackPtr; $i < $this->phpcsFile->numTokens; $i++ ) {
			if ( $this->tokens[ $i ]['code'] === $close_token ) {
				break;
			}

			if ( isset( Tokens::$emptyTokens[ $this->tokens[ $i ]['code'] ] ) ) {
				continue;
			}

			switch( $this->tokens[ $i ]['type'] ) {
				case 'T_COMMA':
					if ( ! isset( $first_comma ) ) {
						// Use this to walk back to correct multi-line if found out later ?
						$first_comma = $i;
					}

					if ( ! isset( $this->tokens[ ( $i + 1  ) ] ) ) {
						break;
					}

					if ( T_WHITESPACE !== $this->tokens[ ( $i + 1 ) ]['code'] ) {
						// There should be whitespace after the %s keyword.
						// $data = array( strtolower( $this->tokens[ $i ]['content'] ) );
						// Add one space before.
					} elseif ( ' ' !== $this->tokens[ ( $i + 1 ) ]['content'] ) {
						// There should be exactly one space before
						// Replace token.
					}
					break;

				case 'T_USE':
				case 'T_AS':
				case 'T_INSTEADOF':
				case 'T_PUBLIC':
				case 'T_PROTECTED':
				case 'T_PRIVATE':
				case 'T_FINAL':


					if ( T_USE !== $this->tokens[ $i ]['code'] && T_COMMA !== $this->tokens[ $i ]['code'] ) {
						if ( T_WHITESPACE !== $this->tokens[ ( $i - 1 ) ]['code'] ) {
							// There should be whitespace before the %s keyword.
							// $data = array( strtolower( $this->tokens[ $i ]['content'] ) );
							// Add one space before.
						} elseif ( ' ' !== $this->tokens[ ( $i - 1 ) ]['content'] ) {
							// There should be exactly one space before
							// Replace token.
						}
					}

					if ( ! isset( $this->tokens[ ( $i + 1  ) ] ) ) {
						break;
					}

					if ( ( isset( Tokens::$scopeModifiers[ $this->tokens[ $i ]['code'] ] )
							|| T_FINAL === $this->tokens[ $i ]['code'] )
						&& T_SEMICOLON === $this->tokens[ ( $i + 1  ) ]['code']
					) {
						break;
					}

					if ( T_WHITESPACE !== $this->tokens[ ( $i + 1 ) ]['code'] ) {
						// There should be whitespace after the %s keyword.
						// $data = array( strtolower( $this->tokens[ $i ]['content'] ) );
						// Add one space before.
					} elseif ( ' ' !== $this->tokens[ ( $i + 1 ) ]['content'] ) {
						// There should be exactly one space before
						// Replace token.
					}

					//..
					break;

				case 'T_STRING':
					if ( 'function' === $this->tokens[ $i ]['content']
						|| 'const' === $this->tokens[ $i ]['content']
					) {

						// Check spacing
						// Check lowercase
					}
					//..
					break;

				case 'T_CURLY_OPEN_BRACE':
					//..
					if ( $this->tokens[ $i ]['line'] !== $this->tokens[ $closer ]['line'] ) {
						$multi_line === true;
					}
					$close_token = T_CURLY_CLOSE_BRACE;
					break;



			}
		}



/*
		$this->spaces_before_closure_open_paren = (int) $this->spaces_before_closure_open_paren;

		if ( isset( $this->tokens[ ( $stackPtr + 1 ) ] ) && T_WHITESPACE !== $this->tokens[ ( $stackPtr + 1 ) ]['code']
			&& ! ( T_ELSE === $this->tokens[ $stackPtr ]['code'] && T_COLON === $this->tokens[ ( $stackPtr + 1 ) ]['code'] )
			&& ! ( T_CLOSURE === $this->tokens[ $stackPtr ]['code']
				&& 0 >= $this->spaces_before_closure_open_paren )
		) {
			$error = 'Space after opening control structure is required';
			$fix   = $this->phpcsFile->addFixableError( $error, $stackPtr, 'NoSpaceAfterStructureOpen' );

			if ( true === $fix ) {
				$this->phpcsFile->fixer->addContent( $stackPtr, ' ' );
			}
		}

		if ( ! isset( $this->tokens[ $stackPtr ]['scope_closer'] ) ) {

			if ( T_USE === $this->tokens[ $stackPtr ]['code'] && 'closure' === $this->get_use_type( $stackPtr ) ) {
				$scopeOpener = $this->phpcsFile->findNext( T_OPEN_CURLY_BRACKET, ( $stackPtr + 1 ) );
				$scopeCloser = $this->tokens[ $scopeOpener ]['scope_closer'];
			} elseif ( T_WHILE !== $this->tokens[ $stackPtr ]['code'] ) {
				return;
			}
		} else {
			$scopeOpener = $this->tokens[ $stackPtr ]['scope_opener'];
			$scopeCloser = $this->tokens[ $stackPtr ]['scope_closer'];
		}

		// Alternative syntax.
		if ( isset( $scopeOpener ) && T_COLON === $this->tokens[ $scopeOpener ]['code'] ) {

			if ( 'required' === $this->space_before_colon ) {

				if ( T_WHITESPACE !== $this->tokens[ ( $scopeOpener - 1 ) ]['code'] ) {
					$error = 'Space between opening control structure and T_COLON is required';
					$fix   = $this->phpcsFile->addFixableError( $error, $scopeOpener, 'NoSpaceBetweenStructureColon' );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->addContentBefore( $scopeOpener, ' ' );
					}
				}
			} elseif ( 'forbidden' === $this->space_before_colon ) {

				if ( T_WHITESPACE === $this->tokens[ ( $scopeOpener - 1 ) ]['code'] ) {
					$error = 'Extra space between opening control structure and T_COLON found';
					$fix   = $this->phpcsFile->addFixableError( $error, ( $scopeOpener - 1 ), 'SpaceBetweenStructureColon' );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->replaceToken( ( $scopeOpener - 1 ), '' );
					}
				}
			}
		}

		$parenthesisOpener = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $stackPtr + 1 ), null, true );
var_dump( $parenthesisOpener );
		// If this is a function declaration.
		if ( T_FUNCTION === $this->tokens[ $stackPtr ]['code'] ) {

			if ( T_STRING === $this->tokens[ $parenthesisOpener ]['code'] ) {

				$function_name_ptr = $parenthesisOpener;

			} elseif ( T_BITWISE_AND === $this->tokens[ $parenthesisOpener ]['code'] ) {

				// This function returns by reference (function &function_name() {}).
				$parenthesisOpener = $this->phpcsFile->findNext(
					Tokens::$emptyTokens,
					( $parenthesisOpener + 1 ),
					null,
					true
				);
				$function_name_ptr = $parenthesisOpener;
			}

			if ( isset( $function_name_ptr ) ) {
				$parenthesisOpener = $this->phpcsFile->findNext(
					Tokens::$emptyTokens,
					( $parenthesisOpener + 1 ),
					null,
					true
				);

				// Checking this: function my_function[*](...) {}.
				if ( ( $function_name_ptr + 1 ) !== $parenthesisOpener ) {

					$error = 'Space between function name and opening parenthesis is prohibited.';
					$fix   = $this->phpcsFile->addFixableError(
						$error,
						$stackPtr,
						'SpaceBeforeFunctionOpenParenthesis',
						$this->tokens[ ( $function_name_ptr + 1 ) ]['content']
					);

					if ( true === $fix ) {
						$this->phpcsFile->fixer->replaceToken( ( $function_name_ptr + 1 ), '' );
					}
				}
			}
		} elseif ( T_CLOSURE === $this->tokens[ $stackPtr ]['code'] ) {

			// Check if there is a use () statement.
			if ( isset( $this->tokens[ $parenthesisOpener ]['parenthesis_closer'] ) ) {

				$usePtr = $this->phpcsFile->findNext(
					Tokens::$emptyTokens,
					( $this->tokens[ $parenthesisOpener ]['parenthesis_closer'] + 1 ),
					null,
					true,
					null,
					true
				);

				// If it is, we set that as the "scope opener".
				if ( T_USE === $this->tokens[ $usePtr ]['code'] ) {
					$scopeOpener = $usePtr;
				}
			}
		}

		if (
			T_COLON !== $this->tokens[ $parenthesisOpener ]['code']
			&& T_FUNCTION !== $this->tokens[ $stackPtr ]['code']
		) {

			if (
				T_CLOSURE === $this->tokens[ $stackPtr ]['code']
				&& 0 === $this->spaces_before_closure_open_paren
			) {

				if ( ( $stackPtr + 1 ) !== $parenthesisOpener ) {
					// Checking this: function[*](...) {}.
					$error = 'Space before closure opening parenthesis is prohibited';
					$fix   = $this->phpcsFile->addFixableError( $error, $stackPtr, 'SpaceBeforeClosureOpenParenthesis' );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->replaceToken( ( $stackPtr + 1 ), '' );
					}
				}
			} elseif (
				(
					T_CLOSURE !== $this->tokens[ $stackPtr ]['code']
					|| 1 === $this->spaces_before_closure_open_paren
				)
				&& ( $stackPtr + 1 ) === $parenthesisOpener
			) {

				// Checking this: if[*](...) {}.
				$error = 'No space before opening parenthesis is prohibited';
				$fix   = $this->phpcsFile->addFixableError( $error, $stackPtr, 'NoSpaceBeforeOpenParenthesis' );

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addContent( $stackPtr, ' ' );
				}
			}
		}

		if (
			T_WHITESPACE === $this->tokens[ ( $stackPtr + 1 ) ]['code']
			&& ' ' !== $this->tokens[ ( $stackPtr + 1 ) ]['content']
		) {
			// Checking this: if [*](...) {}.
			$error = 'Expected exactly one space before opening parenthesis; "%s" found.';
			$fix   = $this->phpcsFile->addFixableError(
				$error,
				$stackPtr,
				'ExtraSpaceBeforeOpenParenthesis',
				$this->tokens[ ( $stackPtr + 1 ) ]['content']
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->replaceToken( ( $stackPtr + 1 ), ' ' );
			}
		}

		if ( T_CLOSE_PARENTHESIS !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['code'] ) {
			if ( T_WHITESPACE !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['code'] ) {
				// Checking this: $value = my_function([*]...).
				$error = 'No space after opening parenthesis is prohibited';
				$fix   = $this->phpcsFile->addFixableError( $error, $stackPtr, 'NoSpaceAfterOpenParenthesis' );

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addContent( $parenthesisOpener, ' ' );
				}
			} elseif ( ( ' ' !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['content']
				&& "\n" !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['content']
				&& "\r\n" !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['content'] )
				&& ! isset( $this->ignore_extra_space_after_open_paren[ $this->tokens[ $stackPtr ]['code'] ] )
			) {
				// Checking this: if ([*]...) {}.
				$error = 'Expected exactly one space after opening parenthesis; "%s" found.';
				$fix   = $this->phpcsFile->addFixableError(
					$error,
					$stackPtr,
					'ExtraSpaceAfterOpenParenthesis',
					$this->tokens[ ( $parenthesisOpener + 1 ) ]['content']
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->replaceToken( ( $parenthesisOpener + 1 ), ' ' );
				}
			}
		}

		if ( isset( $this->tokens[ $parenthesisOpener ]['parenthesis_closer'] ) ) {

			$parenthesisCloser = $this->tokens[ $parenthesisOpener ]['parenthesis_closer'];

			if ( T_CLOSE_PARENTHESIS !== $this->tokens[ ( $parenthesisOpener + 1 ) ]['code'] ) {

				// Checking this: if (...[*]) {}.
				if ( T_WHITESPACE !== $this->tokens[ ( $parenthesisCloser - 1 ) ]['code'] ) {
					$error = 'No space before closing parenthesis is prohibited';
					$fix   = $this->phpcsFile->addFixableError( $error, $parenthesisCloser, 'NoSpaceBeforeCloseParenthesis' );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->addContentBefore( $parenthesisCloser, ' ' );
					}
				} elseif ( ' ' !== $this->tokens[ ( $parenthesisCloser - 1 ) ]['content'] ) {
					$prevNonEmpty = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, ( $parenthesisCloser - 1 ), null, true );
					if ( $this->tokens[ ( $parenthesisCloser ) ]['line'] === $this->tokens[ ( $prevNonEmpty + 1 ) ]['line'] ) {
						$error = 'Expected exactly one space before closing parenthesis; "%s" found.';
						$fix   = $this->phpcsFile->addFixableError(
							$error,
							$stackPtr,
							'ExtraSpaceBeforeCloseParenthesis',
							$this->tokens[ ( $parenthesisCloser - 1 ) ]['content']
						);

						if ( true === $fix ) {
							$this->phpcsFile->fixer->replaceToken( ( $parenthesisCloser - 1 ), ' ' );
						}
					}
				}

				if (
					T_WHITESPACE !== $this->tokens[ ( $parenthesisCloser + 1 ) ]['code']
					&& ( isset( $scopeOpener ) && T_COLON !== $this->tokens[ $scopeOpener ]['code'] )
				) {
					$error = 'Space between opening control structure and closing parenthesis is required';
					$fix   = $this->phpcsFile->addFixableError( $error, $scopeOpener, 'NoSpaceAfterCloseParenthesis' );

					if ( true === $fix ) {
						$this->phpcsFile->fixer->addContentBefore( $scopeOpener, ' ' );
					}
				}
			}

			if ( isset( $this->tokens[ $parenthesisOpener ]['parenthesis_owner'] )
				&& ( isset( $scopeOpener )
				&& $this->tokens[ $parenthesisCloser ]['line'] !== $this->tokens[ $scopeOpener ]['line'] )
			) {
				$error = 'Opening brace should be on the same line as the declaration';
				$fix   = $this->phpcsFile->addFixableError( $error, $parenthesisOpener, 'OpenBraceNotSameLine' );

				if ( true === $fix ) {
					$this->phpcsFile->fixer->beginChangeset();

					for ( $i = ( $parenthesisCloser + 1 ); $i < $scopeOpener; $i++ ) {
						$this->phpcsFile->fixer->replaceToken( $i, '' );
					}

					$this->phpcsFile->fixer->addContent( $parenthesisCloser, ' ' );
					$this->phpcsFile->fixer->endChangeset();
				}
				return;

			} elseif (
				T_WHITESPACE === $this->tokens[ ( $parenthesisCloser + 1 ) ]['code']
				&& ' ' !== $this->tokens[ ( $parenthesisCloser + 1 ) ]['content']
			) {

				// Checking this: if (...) [*]{}.
				$error = 'Expected exactly one space between closing parenthesis and opening control structure; "%s" found.';
				$fix   = $this->phpcsFile->addFixableError(
					$error,
					$stackPtr,
					'ExtraSpaceAfterCloseParenthesis',
					$this->tokens[ ( $parenthesisCloser + 1 ) ]['content']
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->replaceToken( ( $parenthesisCloser + 1 ), ' ' );
				}
			}
		}

		if ( false !== $this->blank_line_check && isset( $scopeOpener ) ) {
			$firstContent = $this->phpcsFile->findNext( T_WHITESPACE, ( $scopeOpener + 1 ), null, true );

			// We ignore spacing for some structures that tend to have their own rules.
			$ignore = array(
				T_FUNCTION             => true,
				T_CLOSURE              => true,
				T_CLASS                => true,
				T_ANON_CLASS           => true,
				T_INTERFACE            => true,
				T_TRAIT                => true,
				T_DOC_COMMENT_OPEN_TAG => true,
				T_CLOSE_TAG            => true,
				T_COMMENT              => true,
			);

			if ( ! isset( $ignore[ $this->tokens[ $firstContent ]['code'] ] )
				&& $this->tokens[ $firstContent ]['line'] > ( $this->tokens[ $scopeOpener ]['line'] + 1 )
			) {
				$error = 'Blank line found at start of control structure';
				$fix   = $this->phpcsFile->addFixableError( $error, $scopeOpener, 'BlankLineAfterStart' );

				if ( true === $fix ) {
					$this->phpcsFile->fixer->beginChangeset();

					for ( $i = ( $scopeOpener + 1 ); $i < $firstContent; $i++ ) {
						$this->phpcsFile->fixer->replaceToken( $i, '' );
					}

					$this->phpcsFile->fixer->addNewline( $scopeOpener );
					$this->phpcsFile->fixer->endChangeset();
				}
			}

			if ( $firstContent !== $scopeCloser ) {
				$lastContent = $this->phpcsFile->findPrevious( T_WHITESPACE, ( $scopeCloser - 1 ), null, true );

				$lastNonEmptyContent = $this->phpcsFile->findPrevious( Tokens::$emptyTokens, ( $scopeCloser - 1 ), null, true );

				$checkToken = $lastContent;
				if ( isset( $this->tokens[ $lastNonEmptyContent ]['scope_condition'] ) ) {
					$checkToken = $this->tokens[ $lastNonEmptyContent ]['scope_condition'];
				}

				if ( ! isset( $ignore[ $this->tokens[ $checkToken ]['code'] ] )
					&& $this->tokens[ $lastContent ]['line'] <= ( $this->tokens[ $scopeCloser ]['line'] - 2 )
				) {
					for ( $i = ( $scopeCloser - 1 ); $i > $lastContent; $i-- ) {
						if ( $this->tokens[ $i ]['line'] < $this->tokens[ $scopeCloser ]['line']
							&& T_OPEN_TAG !== $this->tokens[ $firstContent ]['code']
						) {
							// TODO: Reporting error at empty line won't highlight it in IDE.
							$error = 'Blank line found at end of control structure';
							$fix   = $this->phpcsFile->addFixableError( $error, $i, 'BlankLineBeforeEnd' );

							if ( true === $fix ) {
								$this->phpcsFile->fixer->beginChangeset();

								for ( $j = ( $lastContent + 1 ); $j < $scopeCloser; $j++ ) {
									$this->phpcsFile->fixer->replaceToken( $j, '' );
								}

								$this->phpcsFile->fixer->addNewlineBefore( $scopeCloser );
								$this->phpcsFile->fixer->endChangeset();
							}
							break;
						}
					}
				}
			}
			unset( $ignore );
		}

		if ( ! isset( $scopeCloser ) || true !== $this->blank_line_after_check ) {
			return;
		}

		// {@internal This is just for the blank line check. Only whitespace should be considered,
		// not "other" empty tokens.}}
		$trailingContent = $this->phpcsFile->findNext( T_WHITESPACE, ( $scopeCloser + 1 ), null, true );
		if ( false === $trailingContent ) {
			return;
		}

		if ( T_COMMENT === $this->tokens[ $trailingContent ]['code'] ) {
			// Special exception for code where the comment about
			// an ELSE or ELSEIF is written between the control structures.
			$nextCode = $this->phpcsFile->findNext( Tokens::$emptyTokens, ( $scopeCloser + 1 ), null, true );

			if ( T_ELSE === $this->tokens[ $nextCode ]['code'] || T_ELSEIF === $this->tokens[ $nextCode ]['code'] ) {
				$trailingContent = $nextCode;
			}

			// Move past end comments.
			if ( $this->tokens[ $trailingContent ]['line'] === $this->tokens[ $scopeCloser ]['line'] ) {
				if ( preg_match( '`^//[ ]?end`i', $this->tokens[ $trailingContent ]['content'], $matches ) > 0 ) {
					$scopeCloser     = $trailingContent;
					$trailingContent = $this->phpcsFile->findNext( T_WHITESPACE, ( $trailingContent + 1 ), null, true );
				}
			}
		}

		if ( T_ELSE === $this->tokens[ $trailingContent ]['code'] && T_IF === $this->tokens[ $stackPtr ]['code'] ) {
			// IF with ELSE.
			return;
		}

		if ( T_WHILE === $this->tokens[ $trailingContent ]['code'] && T_DO === $this->tokens[ $stackPtr ]['code'] ) {
			// DO with WHILE.
			return;
		}

		if ( T_CLOSE_TAG === $this->tokens[ $trailingContent ]['code'] ) {
			// At the end of the script or embedded code.
			return;
		}

		if ( isset( $this->tokens[ $trailingContent ]['scope_condition'] )
			&& T_CLOSE_CURLY_BRACKET === $this->tokens[ $trailingContent ]['code']
		) {
			// Another control structure's closing brace.
			$owner = $this->tokens[ $trailingContent ]['scope_condition'];
			if ( in_array( $this->tokens[ $owner ]['code'], array( T_FUNCTION, T_CLOSURE, T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT ), true ) ) {
				// The next content is the closing brace of a function, class, interface or trait
				// so normal function/class rules apply and we can ignore it.
				return;
			}

			if ( ( $this->tokens[ $scopeCloser ]['line'] + 1 ) !== $this->tokens[ $trailingContent ]['line'] ) {
				// TODO: Won't cover following case: "} echo 'OK';".
				$error = 'Blank line found after control structure';
				$fix   = $this->phpcsFile->addFixableError( $error, $scopeCloser, 'BlankLineAfterEnd' );

				if ( true === $fix ) {
					$this->phpcsFile->fixer->beginChangeset();

					$i = ( $scopeCloser + 1 );
					while ( $this->tokens[ $i ]['line'] !== $this->tokens[ $trailingContent ]['line'] ) {
						$this->phpcsFile->fixer->replaceToken( $i, '' );
						$i++;
					}

					// TODO: Instead a separate error should be triggered when content comes right after closing brace.
					if ( T_COMMENT !== $this->tokens[ $scopeCloser ]['code'] ) {
						$this->phpcsFile->fixer->addNewlineBefore( $trailingContent );
					}
					$this->phpcsFile->fixer->endChangeset();
				}
			}
		}
*/
	} // End process_token().
	
	
	/**
	 * Check for correct spacing around a comma found in a use statement.
	 *
	 * @since 0.14.0
	 *
	 * @param int $commaPtr Pointer to the comma in the token stack.
	 * @param int $stackPtr Pointer to the use keyword in the token stack.
	 *
	 * @return void
	 */
	protected function process_comma( $commaPtr, $stackPtr ) {
		if ( ! isset( $this->tokens[ ( $commaPtr - 1  ) ] ) ) {
			return;
		}

		if ( T_WHITESPACE === $this->tokens[ ( $commaPtr - 1 ) ]['code'] ) {
			// There should be no whitespace before the comma.
/*
			$newlines = 0;
			$spaces   = 0;
			for ( $i = ( $commaPtr - 1 ); $i > $stackPtr; $i-- ) {

				if ( T_WHITESPACE === $this->tokens[ $i ]['code'] ) {
					if ( $this->tokens[ $i ]['content'] === $this->phpcsFile->eolChar ) {
						$newlines++;
					} else {
						$spaces += $this->tokens[ $i ]['length'];
					}
				} elseif ( T_COMMENT === $this->tokens[ $i ]['code'] ) {
					break;
				}
			}

			$space_phrases = array();
			if ( $spaces > 0 ) {
				$space_phrases[] = $spaces . ' spaces';
			}
			if ( $newlines > 0 ) {
				$space_phrases[] = $newlines . ' newlines';
			}
			unset( $newlines, $spaces );

			$fix = $this->phpcsFile->addFixableError(
				'Expected 0 spaces between "%s" and comma; %s found',
				$maybe_comma,
				'SpaceBeforeComma',
				array(
					$this->tokens[ $last_content ]['content'],
					implode( ' and ', $space_phrases ),
				)
			);

			if ( true === $fix ) {
				$this->phpcsFile->fixer->beginChangeset();
				for ( $i = $item['end']; $i > $last_content; $i-- ) {

					if ( T_WHITESPACE === $this->tokens[ $i ]['code'] ) {
						$this->phpcsFile->fixer->replaceToken( $i, '' );

					} elseif ( T_COMMENT === $this->tokens[ $i ]['code'] ) {
						// We need to move the comma to before the comment.
						$this->phpcsFile->fixer->addContent( $last_content, ',' );
						$this->phpcsFile->fixer->replaceToken( $maybe_comma, '' );

						/*
						 * No need to worry about removing too much whitespace in
						 * combination with a `//` comment as in that case, the newline
						 * is part of the comment, so we're good.
						 * /

						break;
					}
				}
				$this->phpcsFile->fixer->endChangeset();
			}
*/
		}
		
		if ( ! isset( $this->tokens[ ( $commaPtr + 1  ) ] ) ) {
			// Shouldn't happen, but just in case.
			return;
		}


		if ( T_WHITESPACE !== $this->tokens[ ( $commaPtr + 1 ) ]['code'] ) {
			// There should be whitespace after each comma in a use statement.
			// $data = array( strtolower( $this->tokens[ $i ]['content'] ) );
			// Add one space before.
		} elseif ( ' ' !== $this->tokens[ ( $commaPtr + 1 ) ]['content'] ) {
			// There should be exactly one space after the comma
			// Replace token.
		}
		
/*
			$next_token = $this->tokens[ ( $maybe_comma + 1 ) ];

			if ( T_WHITESPACE === $next_token['code'] ) {

				if ( false === $single_line && $this->phpcsFile->eolChar === $next_token['content'] ) {
					continue;
				}

				$next_non_whitespace = $this->phpcsFile->findNext(
					T_WHITESPACE,
					($maybe_comma + 1 ),
					$closer,
					true
				);

				if ( false === $next_non_whitespace
					|| ( false === $single_line
						&& $this->tokens[ $next_non_whitespace ]['line'] === $this->tokens[ $maybe_comma ]['line']
						&& T_COMMENT === $this->tokens[ $next_non_whitespace ]['code'] )
				) {
					continue;
				}

				$space_length = $next_token['length'];
				if ( 1 === $space_length ) {
					continue;
				}

				$fix = $this->phpcsFile->addFixableError(
					'Expected 1 space between comma and "%s"; %s found',
					$maybe_comma,
					'SpaceAfterComma',
					array(
						$this->tokens[ $next_non_whitespace ]['content'],
						$space_length,
					)
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->replaceToken( ( $maybe_comma + 1 ), ' ' );
				}
			} else {
				// This is either a comment or a mixed single/multi-line array.
				// Just add a space and let other sniffs sort out the array layout.
				$fix = $this->phpcsFile->addFixableError(
					'Expected 1 space between comma and "%s"; 0 found',
					$maybe_comma,
					'NoSpaceAfterComma',
					array( $next_token['content'] )
				);

				if ( true === $fix ) {
					$this->phpcsFile->fixer->addContent( $maybe_comma, ' ' );
				}
			}
		}
*/
	}

} // End class.
