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
 * Discouraged WordPress functions.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.xx.0
 */
class DiscouragedWPFunctionsSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(
			'site_url' => array(
				'type'      => 'warning',
				'message'   => '%s() found. In most cases home_url() is a better function to use.',
				'functions' => array(
					'site_url',
					'get_home_url',
				),
			),
			'archive_title' => array(
				'type'      => 'warning',
				'message'   => '%s() found. In most cases the_archive_title() is a better function to use.',
				'functions' => array(
					'single_cat_title',
					'single_tag_title',
				),
			),
			'archive_description' => array(
				'type'      => 'warning',
				'message'   => '%s() found. In most cases the_archive_description() is a better function to use.',
				'functions' => array(
					'category_description',
					'tag_description',
				),
			),
			'archive_pagination' => array(
				'type'      => 'warning',
				'message'   => '%s() found. In most cases the_posts_pagination() is a better function to use.',
				'functions' => array(
					'paginate_links',
				),
			),
		);
	}

}
