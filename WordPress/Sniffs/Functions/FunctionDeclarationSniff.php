<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Functions;

use WordPress\Sniff;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Enforces spacing for function declarations.
 *
 * - There should be no space between the function name and the open parenthesis.
 * - The `function` keyword should be followed by one space with the
 *   exception of closures where the spacing depends on the property value.
 * - If applicable, the `use` keyword should be both preceded and followed by one space.
 * - There should be exactly one space after each closing parenthesis.
 * - If a function declaration is multi-line:
 *   - Each parameter, including the first, should start on a new line.
 *   - The close parenthesis should be on the next line after the last parameter.
 *
 * Note:
 * - This sniff does not cover function declaration argument spacing. This is
 *   checked by the `Squiz.Functions.FunctionDeclarationArgumentSpacing` sniff.
 * - This sniff does not cover the placement of the function braces. This is
 *   checked by the `Generic.Functions.OpeningFunctionBraceKernighanRitchie` sniff.
 * - This sniff does not cover the indentation of the function close brace. This is
 *   checked by the `Generic.WhiteSpace.ScopeIndent` sniff.
 * - This sniff does not check whether all methods have a visibility declared.
 *   This is covered by the `Squiz.Scope.MethodScope` sniff (included in `Extra`).
 * - This sniff does not cover the order of the modifier keywords. This is
 *   covered by the `PSR2.Methods.MethodDeclaration` sniff (included in `Extra`).
 * - This sniff does not cover the spacing around modifier keywords. This is
 *   covered by the `Squiz.WhiteSpace.ScopeKeywordSpacing` sniff (included in `Extra`).
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.14.0 Function declaration spacing was previously checked through
 *                 the `WordPress.WhiteSpace.ControlStructureSpacing` sniff.
 */
class FunctionDeclarationSniff extends Sniff {

	/**
	 * How many spaces should be between a T_CLOSURE and T_OPEN_PARENTHESIS.
	 *
	 * `function[*]() {...}`
	 *
	 * `-1` means: don't check. 0 or a positive integer enforces that many spaces between the
	 * function keyword and the closure open parenthesis.
	 *
	 * @since 0.7.0
	 * @since 0.14.0 This property was moved to this sniff from the `ControlStructureSpacing` sniff.
	 *
	 * @var int
	 */
	public $spaces_before_closure_open_paren = -1;

    /**
     * Returns an array of patterns to check are correct.
     *
     * @return array
     */
    protected function getPatterns()
    {
        return array(
                'function abc( ... );',
                'function abc( ... )',
                'abstract function abc( ... );',
                'function ( ... )',
                'function ( ... ) use ( ... )',
               );

    }//end getPatterns()

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_FUNCTION,
			T_CLOSURE,
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

/*
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
*/
	}

}//end class
