<?php
/**
 * Unit test class for WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Tests\Theme;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the NoRemoveAdminPages sniff.
 *
 * @package WPCS\WordPressCodingStandards
 * @since   0.xx.0
 */
class NoRemoveAdminPagesUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList() {
		return array(
			9  => 1,
			10 => 1,
			11 => 1,
			12 => 1,
			26 => 1,
			27 => 1,
			34 => 1,
			35 => 1,
			36 => 1,
			37 => 1,
			38 => 1,
			39 => 1,
			40 => 1,
			41 => 1,
			44 => 1,
			45 => 1,
			46 => 1,
			47 => 1,
			52 => 1,
			53 => 1,
			54 => 1,
			55 => 1,
			56 => 1,
			57 => 1,
		);
	}

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList() {
		return array(
			30 => 2,
			31 => 1,
		);
	}

}
