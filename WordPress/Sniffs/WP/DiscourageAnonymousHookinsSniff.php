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

/**
 * Flag passing an anonymous function or class as the callback when registering a hook-in.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   2.2.0
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
		
		$has_anon = $this->phpcsFile->findNext( [T_ANON_CLASS, T_CLOSURE], $target_param['start'], $target_param['end'] );
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
