<?php
/**
 * Unit test class for WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

/**
 * Unit test class for the UseStatementSpacing sniff.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since   0.14.0
 */
class UseStatementSpacingUnitTest extends AbstractSniffUnitTest {

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList( $testFile = '' ) {

		switch ( $testFile ) {
			// Use with namespaces.
			case 'UseStatementSpacingUnitTest.1.inc':
				return array(
					17 => 1,
					18 => 1,
					19 => 1,
					20 => 1,
					22 => 1,
					23 => 1,
					24 => 1,
					25 => 1,
					26 => 1,
					27 => 1,
					29 => 1,
					30 => 1,
					32 => 1,
					33 => 1,
					35 => 1,
					36 => 1,
					37 => 1,
					39 => 1,
					40 => 1,
					42 => 1,
					51 => 1,
					52 => 1,
					63 => 1,
					65 => 1,
					66 => 1,
				);

			// Use with closures.
			case 'UseStatementSpacingUnitTest.2.inc':
				return array(
					16 => 1,
					22 => 1,
					26 => 1,
					27 => 1,
					29 => 1,
					30 => 1,
					31 => 1,
					39 => 1,
					41 => 1,
					42 => 1,
					43 => 1,
					45 => 1,
					46 => 1,
					47 => 1,
				);

			// Use with traits.
			case 'UseStatementSpacingUnitTest.3.inc':
				return array(
					16 => 1,
					22 => 1,
					26 => 1,
					27 => 1,
					29 => 1,
					30 => 1,
					31 => 1,
					39 => 1,
					41 => 1,
					42 => 1,
					43 => 1,
					45 => 1,
					46 => 1,
					47 => 1,
					66 => 1,
					68 => 1,
					69 => 1,
					70 => 1,
					84 => 1,
					86 => 1,
					88 => 1,
					89 => 1,
				);

			default:
				return array();

		}

	} // end getErrorList()

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList() {
		return array();

	}

} // End class.
