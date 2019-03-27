<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\WordPress\Sniffs\WP;

use WordPressCS\WordPress\AbstractFunctionParameterSniff;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Util\Sniffs\Conditions;

/**
 * Flag passing an anonymous function or class as the callback when registering a hook-in
 * as these are difficult to unhook with the current WP Plugins API.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   2.2.0
 *
 * @uses    \WordPressCS\WordPress\Sniff::$custom_test_class_whitelist
 */
class DiscourageAnonymousHookinsSniff extends AbstractFunctionParameterSniff {

	/**
	 * The group name for this group of functions.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	protected $group_name = 'anonymous';

	/**
	 * List of hook-in functions to which a callback is expected to be passed.
	 *
	 * @link https://codex.wordpress.org/Plugin_API
	 *
	 * @since 2.2.0
	 *
	 * @var array <string function_name> => <int paramter position>
	 */
	protected $target_functions = array(
		'add_filter'                 => 2,
		'add_action'                 => 2,
		'register_activation_hook'   => 2,
		'register_deactivation_hook' => 2,
		'register_uninstall_hook'    => 2,
	);

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @since 2.2.0
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_token( $stackPtr ) {
		// Anonymous hook-ins used in a unit test context are irrelevant.
		// NOTE: Actually... this should probably not be allowed either as it could influence other unit tests when things aren't being unhooked after a test.
		if ( $this->is_token_in_test_method( $stackPtr ) === true ) {
			return;
		}

		return parent::process_token( $stackPtr );
	}

	/**
	 * Process the parameters of a matched function.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param array  $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 * @param array  $parameters      Array with information about the parameters.
	 *
	 * @return void
	 */
	public function process_parameters( $stackPtr, $group_name, $matched_content, $parameters ) {
		$target_param_position = $this->target_functions[ $matched_content ];
		if ( isset( $parameters[ $target_param_position ] ) === false ) {
			return;
		}
		
		$target_param = $parameters[ $target_param_position ];
		$param_start  = $target_param['start'];
		$param_end    = ($target_param['end'] + 1);
		
		$has_anon = $this->phpcsFile->findNext( [T_ANON_CLASS, T_CLOSURE], $param_start, $param_end );
		if ( $has_anon !== false ) {
			$error_msg  = 'Using a closure as the callback for a hook-in is forbidden as it makes unhooking the action/filter very difficult';
			$error_code = 'ClosureFound';

			if ( T_ANON_CLASS === $this->tokens[ $has_anon ]['code'] ) {
				$error_msg  = 'Using an anonymous class in the callback for a hook-in is forbidden as it makes unhooking the action/filter very difficult';
				$error_code = 'AnonClassFound';
			}

			$this->phpcsFile->addError(
				$error_msg,
				$has_anon,
				$error_code
			);
			return;
		}
		
		/*
		 * The anonymous function/class may be declared earlier and assigned to a variable.
		 *
		 * If the parameter is just and only a variable, search within the function scope to see if we can
		 * determine whether it is a closure/anonymous class.
		 */

		$ignore   = Tokens::$emptyTokens;
		$ignore[] = T_VARIABLE;
		
		$non_var = $this->phpcsFile->findNext( $ignore, $param_start, $param_end, true );
		if ( $non_var !== false ) {
			// Something other than a variable encountered. Bow out.
			return;
		}
		
		$function = Conditions::getLastCondition( $this->phpcsFile, $stackPtr, [ T_FUNCTION, T_CLOSURE ] );
		if ( $function === false ) {
			// Ignore variables used in the global namespace as they may well be defined in another file.
			return;
		}

		// Get the variable.
		$var = $this->phpcsFile->findNext( T_VARIABLE, $param_start, $param_end );
		if ( $this->tokens[ $var ]['content'] === '$GLOBALS' ) {
			// Ignore global variables as they may well be defined in another file.
			return;
		}
		
		$function_opener = $this->tokens[ $function ]['scope_opener'];
		$content         = $this->tokens[ $var ]['content'];
		$current         = $stackPtr;
		do {
			$current = $this->phpcsFile->findPrevious( T_VARIABLE, ( $current - 1 ), $function_opener, false, $content );
			if ( $current === false ) {
				// Variable is not declared within this function.
				return;
			}
			
			// Check if variable is found between parenthesis, if so, check for assignment with T_EQUAL to the var between here and close parenthesis
			// If not, continue
			// If so check for closure/anon
			   // If so, throw error
			   // if not and was "straight" assignment, return.
			   // If not and was key assignment, continue.

			// Otherwise check till end of statement - same checks

/*
			is_assignment( $stackPtr )

		static $valid = array(
			\T_VARIABLE             => true,
			\T_CLOSE_SQUARE_BRACKET => true,
		);

		// Must be a variable, constant or closing square bracket (see below).
		if ( ! isset( $valid[ $this->tokens[ $stackPtr ]['code'] ] ) ) {
			return false;
		}

		$next_non_empty = $this->phpcsFile->findNext(
			Tokens::$emptyTokens,
			( $stackPtr + 1 ),
			null,
			true,
			null,
			true
		);

		// No token found.
		if ( false === $next_non_empty ) {
			return false;
		}

		// If the next token is an assignment, that's all we need to know.
		if ( isset( Tokens::$assignmentTokens[ $this->tokens[ $next_non_empty ]['code'] ] ) ) {
			return true;
		}

		// Check if this is an array assignment, e.g., `$var['key'] = 'val';` .
		if ( \T_OPEN_SQUARE_BRACKET === $this->tokens[ $next_non_empty ]['code'] ) {
			return $this->is_assignment( $this->tokens[ $next_non_empty ]['bracket_closer'] );
		}

		return false;
*/
		} while ( true );

/*
-> findnext in param for T_ANON_CLASS and T_CLOSURE
-> if not found, check if the param **is** a variable (not if it contains one) and if it is, see about finding the variable declaration within scope, if found and "simple", check the same till end of statement. If not found or not "simple", i.e. array[] assignment or $callback .= etc, i.e. not a straight equal after variable, then warn no matter what

*/

/*
		// We're only interested in the third parameter.
		if ( false === isset( $parameters[3] ) || 'true' !== strtolower( $parameters[3]['raw'] ) ) {
			$errorcode = 'MissingTrueStrict';

			/*
			 * Use a different error code when `false` is found to allow for excluding
			 * the warning as this will be a conscious choice made by the dev.
			 * /
			if ( isset( $parameters[3] ) && 'false' === strtolower( $parameters[3]['raw'] ) ) {
				$errorcode = 'FoundNonStrictFalse';
			}

			$this->phpcsFile->addWarning(
				'Not using strict comparison for %s; supply true for third argument.',
				( isset( $parameters[3]['start'] ) ? $parameters[3]['start'] : $parameters[1]['start'] ),
				$errorcode,
				array( $matched_content )
			);
			return;
		}
*/
	}

}

/*
https://make.wordpress.org/core/2019/03/26/coding-standards-updates-for-php-5-6/

Other things to check:
- Do some tests maybe with short ternary syntax ?

*/
