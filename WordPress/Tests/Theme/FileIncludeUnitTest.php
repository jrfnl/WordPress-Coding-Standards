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
 * Unit test class for the Theme_FileInclude sniff.
 *
 * @package WPCS\WordPressCodingStandards
 * @since   0.xx.0
 */
class FileIncludeUnitTest extends AbstractSniffUnitTest {

	/**
	 * Get a list of all test files to check.
	 *
	 * @param string $testFileBase The base path that the unit tests files will have.
	 *
	 * @return string[]
	 */
	protected function getTestFiles( $testFileBase ) {
		$extra_files_path = dirname( $testFileBase ) . DIRECTORY_SEPARATOR . 'FileInclude' . DIRECTORY_SEPARATOR;

		return array(
			$testFileBase . 'inc',
			$extra_files_path . 'functions.inc',
		);
	}

	/**
	 * Returns the lines where errors should occur.
	 *
	 * @return array <int line number> => <int number of errors>
	 */
	public function getErrorList() {
		return array();
	}

	/**
	 * Returns the lines where warnings should occur.
	 *
	 * @param string $testFile The name of the file being tested.
	 *
	 * @return array <int line number> => <int number of warnings>
	 */
	public function getWarningList( $testFile = '' ) {
		switch ( $testFile ) {
			case 'FileIncludeUnitTest.inc':
				return array(
					3  => 1,
					4  => 1,
					5  => 1,
					6  => 1,
					7  => 1,
					8  => 1,
					11 => 1,
					12 => 1,
					13 => 1,
					14 => 1,
				);

			case 'functions.inc':
			default:
				return array();
		}
	}

}
