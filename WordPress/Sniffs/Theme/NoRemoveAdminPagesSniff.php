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
use PHP_CodeSniffer_Tokens as Tokens;

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
	 * Last updated: 2018-06-04 / WP 4.9.6 / trunk 5.0.
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
	 * Last updated: 2018-06-04 / WP 4.9.6 / trunk 5.0.
	 *
	 * @var array <string submenu slug> => <bool whether or not query args need examining>
	 */
	protected $theme_subpages = array(
		'themes.php'        => false,
		'customize.php'     => true,
		'widgets.php'       => false,
		'nav-menus.php'     => false,
		'theme-install.php' => false,
		'theme-editor.php'  => false,
		'custom-header'     => false, // WP <= 4.0.
		'custom-background' => false, // WP <= 4.0.
	);

	/**
	 * List of customizer autofocus areas used by WP core for the theme - "Appearance" - admin submenu.
	 *
	 * Includes some WP core customizer pages which normally don't appear in the menu, but belong to
	 * core nonetheless.
	 *
	 * Sources:
	 * - wp-admin/menu.php
	 * - wp-admin/nav-menus.php
	 * - wp-admin/theme-editor.php
	 * - wp-admin/widgets.php
	 *
	 * Last updated: 2018-06-04 / WP 4.9.6 / trunk 5.0.
	 *
	 * @var array
	 */
	protected $customizer_query_args = array(
		'control' => array(
			'header_image'     => true,
			'background_image' => true,
		),
		'panel' => array(
			'widgets'   => true,
			'nav-menus' => true,
		),
		'section' => array(
			'custom_css' => true,
		),
	);


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

		/*
		 * Handle `remove_submenu_page()` function calls.
		 */
		if ( ! isset( $parameters[1], $parameters[2] )
			|| ( isset( $parameters[1] ) && '' === $parameters[1]['raw'] )
			|| ( isset( $parameters[2] ) && '' === $parameters[2]['raw'] )
		) {
			// Live coding, parse error or other code error. Not our concern.
			return;
		}

		$string_or_empty_tokens = Tokens::$textStringTokens + Tokens::$emptyTokens;

		// Examine the parent menu slug.
		$first_non_text = $this->phpcsFile->findNext(
			$string_or_empty_tokens,
			$parameters[1]['start'],
			( $parameters[1]['end'] + 1 ),
			true
		);

		if ( false !== $first_non_text ) {
			$this->phpcsFile->addWarning(
				'Admin submenu page removal detected. Parent menu could not be determined. Found: %s',
				$stackPtr,
				'RemoveSubMenuPageUnknownParent',
				array( $parameters[1]['raw'] )
			);
		} else {
			$toplevel_menu = $this->strip_quotes( $parameters[1]['raw'] );
			if ( 'themes.php' !== $toplevel_menu ) {
				$this->phpcsFile->addError(
					'Removing WP core submenu admin pages is not allowed from within a theme.',
					$stackPtr,
					'RemoveSubMenuPageNonTheme'
				);
			}
		}

		// Examine the submenu slug.
		$target_param = $parameters[2];
		$first_text   = $this->phpcsFile->findNext(
			Tokens::$textStringTokens,
			$target_param['start'],
			( $target_param['end'] + 1 )
		);

		if ( false === $first_text ) {
			$this->phpcsFile->addWarning(
				'Admin submenu page removal detected. Submenu could not be determined. Found: %s',
				$stackPtr,
				'RemoveSubMenuPageUnknownSubmenu',
				array( $target_param['raw'] )
			);
			
			return;
		}

		$first_non_text = $this->phpcsFile->findNext(
			$string_or_empty_tokens,
			$target_param['start'],
			( $target_param['end'] + 1 ),
			true
		);

		// Allow for the submenu slug to be wrapped in a call to `esc_url()`.
		if ( 'esc_url' === $this->tokens[ $first_non_text ]['content'] ) {
			$next = $this->phpcsFile->findNext(Tokens::$emptyTokens, ( $first_non_text + 1 ), ( $parameters[2]['end'] + 1 ), true );
			if ( false !== $next ) {
				$first_param = $this->get_function_call_parameter( $first_non_text, 1 );
				if ( false !== $first_param ) {
					$target_param   = $first_param;
					$first_non_text = $this->phpcsFile->findNext(
						$string_or_empty_tokens,
						$target_param['start'],
						( $target_param['end'] + 1 ),
						true
					);
				}
			}
		}

		if ( false === $first_non_text ) {
			$submenu_slug = $this->strip_quotes( $parameters[2]['raw'] );
			foreach ( $this->theme_subpages as $theme_subpage => $examine ) {

				if ( strpos( $submenu_slug, $theme_subpage ) === 0 ) {
					if ( false === $examine || $submenu_slug === $theme_subpage ) {
						$this->phpcsFile->addError(
							'Removing WP core submenu admin pages is not allowed from within a theme.',
							$stackPtr,
							'RemoveSubMenuPageTheme'
						);
						
						return;
					}
					
					// TODO: examine customize query args
				}
			}

			return;
		}


//$customizer_query_args

		// Examine submenu for typical customizer query args
		
		


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
$customize_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );

remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => array( 'control' => 'header_image' ) ), 'customize.php' ) ); // Error.
remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => array( 'control' => 'background_image' ) ), $customize_url ); ); // Error.
remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => [ 'panel' => 'widgets' ] ), 'customize.php' ) ); // Error.
remove_submenu_page('themes.php', add_query_arg(['autofocus'=>['panel'=>'nav-menus']],'customize.php')); // Error.
remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => array( 'section' => 'custom_css' ) ), 'customize.php' ) ); // Error.

$page = remove_submenu_page( 'themes.php', esc_url( $customize_url ) ); // Warning.
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
