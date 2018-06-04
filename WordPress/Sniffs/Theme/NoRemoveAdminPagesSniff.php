<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\Theme;

use WordPress\AbstractFunctionParameterSniff;

/**
 * Forbids the removing of WP Core admin pages from within themes.
 *
 * @link    https://make.wordpress.org/themes/handbook/review/required/theme-check-plugin/#admin-menu
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.xx.0
 */
class NoRemoveAdminPagesSniff extends AbstractFunctionParameterSniff {

	/**
	 * The group name for this group of functions.
	 *
	 * @since 0.xx.0
	 *
	 * @var string
	 */
	protected $group_name = 'remove_admin_pages';

	/**
	 * @TODO !!!!!
	 *
	 * Array of function, position, argument, and replacement function for restricted argument.
	 *
	 * @since 0.xx.0
	 *
	 * Sources:
	 * - wp-admin/menu.php
	 * - wp-admin/network/menu.php
	 *
	 * Last updated: 2018-06-04 / WP 4.9.6.
	 *
	 * @var array Multi-dimentional array with parameter details.
	 *            @type string Function name. {
	 *                @type int target Parameter positions. {
	 *                    @type string Alternative.
	 *                }
	 *            }
	 */
	protected $target_functions = array(
		'remove_menu_page'    => true,
		'remove_submenu_page' => array(
			'menu_slug' => array(
				'submenu_slug' => array(
					'alt' => 'home_url()',
				),
				'wpurl' => array(
					'alt' => 'site_url()',
				),
			),
		),
	);

	/**
	 * List of file names used by WP core for the admin menus.
	 *
	 * Sources:
	 * - wp-admin/menu.php
	 * - wp-admin/network/menu.php
	 *
	 * Last updated: 2018-06-04 / WP 4.9.6.
	 *
	 * @var array <string File name> => <bool Whether the query string should be examined>
	 */
	protected $core_admin_pages = array(
		'index.php' => true
	);

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'remove_menu_page' => array(
				'type'      => 'error',
				'message'   => 'Removing top-level admin pages is not allowed from within a theme.',
				'functions' => array(
					'remove_menu_page',
				),
			),
			'remove_submenu_page' => array(
				'type'      => 'error',
				'message'   => 'Removing WP core submenu admin pages is not allowed from within a theme.',
				'functions' => array(
					'remove_submenu_page',
				),
			),
		);
	}
//function remove_menu_page( $menu_slug ) {
//function remove_submenu_page( $menu_slug, $submenu_slug )

	/**
	 * Process the parameters of a matched function.
	 *
	 * @since 0.xx.0
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param array  $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 * @param array  $parameters      Array with information about the parameters.
	 *
	 * @return void
	 */
	public function process_parameters( $stackPtr, $group_name, $matched_content, $parameters ) {
		if ( 'remove_menu_page' === $matched_content ) {
			$this->phpcsFile->addError(
				'Removing top-level admin pages is not allowed from within a theme.',
				$stackPtr,
				'RemoveMenuPageFound'
			);
			
			return;
		}
		
		// Handle `remove_submenu_page()`.
		
		// Check if param 1 - menu slug - is one of the core slugs
		// if not, bow out OR MAYBE BETTER: only allow removing from the `themes` submenu ?
		
		// Check if param 2 - submenu slug - is one of the core slugs
		// if not, bow out
		
		// Otherwise, throw error

/*
			// Find closing parenthesis of the function call & do gettokensasstring()
			// remove superfluous whitespace & line breaks from resulting string.


			$this->phpcsFile->addError(
				'Removing WP core submenu admin pages is not allowed from within a theme. Found: %s',
				$stackPtr,
				'RemoveSubMenuPageFound',
				array( $function_call_string )
			);

*/

/*
		$paramCount = count( $parameters );
		foreach ( $this->target_functions[ $matched_content ] as $position => $parameter_args ) {
			if ( $position > $paramCount ) {
				break;
			}
			if ( ! isset( $parameters[ $position ] ) ) {
				continue;
			}
			$is_dynamic_parameter = $this->phpcsFile->findNext( array( T_CONSTANT_ENCAPSED_STRING, T_ARRAY, T_FALSE, T_TRUE, T_NULL, T_LNUMBER, T_WHITESPACE ), $parameters[ $position ]['start'], ( $parameters[ $position ]['end'] + 1 ), true, null, true );

			$matched_parameter = $this->strip_quotes( $parameters[ $position ]['raw'] );

			if ( ! $is_dynamic_parameter && ! isset( $this->target_functions[ $matched_content ][ $position ][ $matched_parameter ] ) ) {
				continue;
			}
		}
*/
	}

}
