<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Theme;

use WordPress\AbstractFunctionRestrictionsSniff;

/**
 * Forbids usage of certain PHP functions and recommends alternatives.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.xx.0
 */
class RestrictedPHPFunctionsSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(

			'eval' => array(
				'type'      => 'error',
				'message'   => '%s() is not allowed.',
				'functions' => array(
					'eval',
				),
			),

			'system_calls' => array(
				'type'      => 'error',
				'message'   => 'PHP system calls are often disabled by server admins and should not be in themes. Found %s.',
				'functions' => array(
					'exec',
					'passthru',
					'proc_open',
					'shell_exec',
					'system',
					'popen',
				),
			),

			'ini_set' => array(
				'type'      => 'error',
				'message'   => '%s is prohibited, themes should not change server PHP settings.',
				'functions' => array(
					'ini_set',
				),
			),

			'obfuscation' => array(
				'type'      => 'error',
				'message'   => '%s() is not allowed.',
				'functions' => array(
					'base64_decode',
					'base64_encode',
					'convert_uudecode',
					'convert_uuencode',
					'str_rot13',
				),
			),
		);
	}

}
