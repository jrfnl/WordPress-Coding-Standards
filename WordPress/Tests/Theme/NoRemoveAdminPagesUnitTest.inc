<?php

remove_submenu_page( 'themes.php', 'parent-theme' ); // OK.
remove_submenu_page( 'themes.php', 'theme-mod' ); // OK.

/*
 * Top-level Menu Pages.
 */
remove_menu_page( 'index.php' ); // Error.
$page = remove_menu_page( 'edit.php' ); // Error.
remove_menu_page( 'themes.php' ); // Error.
remove_menu_page(
	'options-general.php' // Error.
);

/*
 * Submenu pages.
 */
// These are all incorrect function calls and will be ignored.
remove_submenu_page(); // OK.
remove_submenu_page(,'something'); // OK.
remove_submenu_page('themes.php'); // OK.

remove_submenu_page( 'options.php', 'options-reading.php' ); // Error.
remove_submenu_page( 'plugins.php', 'my-awesome-plugin' ); // Error.

$page = remove_submenu_page( 'themes.php', 'widgets.php' ); // Error.
$page = remove_submenu_page( 'themes.php', 'customize.php' ); // Error.
$page = remove_submenu_page( 'themes.php', 'theme-editor.php' ); // Error.
remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => array( 'control' => 'header_image' ) ), 'customize.php' ) ); // Error.
remove_submenu_page( 'themes.php', add_query_arg( array( 'autofocus' => array( 'control' => 'background_image' ) ), $customize_url ); ); // Error.

/*
remove_submenu_page( 'themes.php', 'parent-theme' ); // OK.
remove_submenu_page( 'themes.php', 'theme-mod' ); // OK.

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
*/