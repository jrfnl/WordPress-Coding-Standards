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
	 * The functions targetted for examination by this sniff.
	 *
	 * @since 0.xx.0
	 *
	 * Sources:
	 * - wp-admin/menu.php
	 * - wp-admin/network/menu.php
	 * - wp-admin/user/menu.php
	 *
	 * Last updated: 2018-06-04 / WP 4.9.6.
	 *
	 * @var array
	 */
	protected $target_functions = array(
		'remove_menu_page'    => true,
		'remove_submenu_page' => true,
	);

	/**
	 * List of file names used by WP core for the theme - "Appearance" - admin submenu.
	 *
	 * Includes some WP core pages which normally don't appear in the menu, but belong to the
	 * theme category. Some of these may have been in the menu in older WP versions.
	 *
	 * Sources:
	 * - wp-admin/menu.php
	 * - wp-admin/network/menu.php
	 *
	 * Last updated: 2018-06-04 / WP 4.9.6.
	 *
	 * @var array
	 */
	protected $theme_subpages = array(
		'themes.php'        => true,
		'customize.php'     => true, // Special cased in the logic below.
		'widgets.php'       => true,
		'nav-menus.php'     => true,
		'theme-install.php' => true,
		'theme-editor.php'  => true,
	);


themes.php
//add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );
http://localhost/wp/4.9_nl/wp-admin/customize.php?return=%2Fwp%2F4.9_nl%2Fwp-admin%2F
widgets.php (?)
customize.php
nav-menus.php
//add_query_arg( array( 'autofocus' => array( 'control' => 'header_image' ) ), $customize_url );
http://localhost/wp/4.9_nl/wp-admin/customize.php?return=%2Fwp%2F4.9_nl%2Fwp-admin%2F&autofocus%5Bcontrol%5D=header_image
add_query_arg( array( 'autofocus' => array( 'control' => 'background_image' ) ), $customize_url );
http://localhost/wp/4.9_nl/wp-admin/customize.php?return=%2Fwp%2F4.9_nl%2Fwp-admin%2Fthemes.php&autofocus%5Bcontrol%5D=background_image
theme-editor.php

http://localhost/wp/4.9_nl/wp-admin/customize.php?theme=2016child&return=%2Fwp%2F4.9_nl%2Fwp-admin%2Fthemes.php
http://localhost/wp/4.9_nl/wp-admin/customize.php?autofocus%5Bpanel%5D=widgets&return=%2Fwp%2F4.9_nl%2Fwp-admin%2Fwidgets.php
http://localhost/wp/4.9_nl/wp-admin/customize.php?autofocus%5Bpanel%5D=nav_menus&return=%2Fwp%2F4.9_nl%2Fwp-admin%2Fnav-menus.php%3Faction%3Dedit%26menu%3D0
http://localhost/wp/4.9_nl/wp-admin/customize.php?autofocus%5Bsection%5D=custom_css

themes.php
theme-install.php
theme-editor.php

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
		if ( ! isset( $parameters[1], $parameters[2] ) ) {
			// Live coding, parse error or other code error. Not our concern.
			return;
		}

		$toplevel_menu = $this->strip_quotes( $parameters[1]['raw'] );
		if ( 'themes.php' !== $parameters[1]['raw']) {
			$this->phpcsFile->addError(
				'Removing admin pages is not allowed from within a theme.',
				$stackPtr,
				'RemoveSubMenuPageFound'
			);
		}

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
