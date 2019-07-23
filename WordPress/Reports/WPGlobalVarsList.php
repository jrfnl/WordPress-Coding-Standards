<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\WordPress\Reports;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reports\Report;

/**
 * Generate a new WP global variables list.
 *
 * Run it over WP Core using the following command:
 * phpcs -p ./src/ --report=Reports\WPGlobalVarsList --standard=WordPress --extensions=php --ignore=/src/wp-content/* --sniffs=WordPress.WP.GlobalVariablesOverride --basepath=./
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since 2.2.0
 */
class WPGlobalVarsList implements Report {

    /**
     * Generate a partial report for a single processed file.
     *
     * Function should return TRUE if it printed or stored data about the file
     * and FALSE if it ignored the file. Returning TRUE indicates that the file and
     * its data should be counted in the grand totals.
     *
     * @param array                       $report      Prepared report data.
     * @param \PHP_CodeSniffer\Files\File $phpcsFile   The file being reported on.
     * @param bool                        $showSources Show sources?
     * @param int                         $width       Maximum allowed line width.
     *
     * @return bool
     */
    public function generateFileReport( $report, File $phpcsFile, $showSources = false, $width = 80 ) {
        $metrics = $phpcsFile->getMetrics();
        foreach ( $metrics as $metric => $data ) {
			if ( 'Global variables' !== $metric ) {
				continue;
			}

            foreach ( $data['values'] as $value => $count ) {
                echo $value . PHP_EOL;
            }
        }

        return true;
    }

    /**
     * Prints a newly generated WP global variables list.
     *
     * @param string $cachedData    Any partial report data that was returned from
     *                              generateFileReport during the run.
     * @param int    $totalFiles    Total number of files processed during the run.
     * @param int    $totalErrors   Total number of errors found during the run.
     * @param int    $totalWarnings Total number of warnings found during the run.
     * @param int    $totalFixable  Total number of problems that can be fixed.
     * @param bool   $showSources   Show sources?
     * @param int    $width         Maximum allowed line width.
     * @param bool   $interactive   Are we running in interactive mode?
     * @param bool   $toScreen      Is the report being printed to screen?
     *
     * @return void
     */
    public function generate(
        $cachedData,
        $totalFiles,
        $totalErrors,
        $totalWarnings,
        $totalFixable,
        $showSources = false,
        $width = 80,
        $interactive = false,
        $toScreen = true
    ) {
        $lines = explode( PHP_EOL, $cachedData );
        array_pop( $lines );

        if ( empty( $lines ) ) {
            return;
        }

        $variables  = array();
        $max_length = 0;
        foreach ( $lines as $line ) {
			$var               = trim( $line );
			$length            = strlen( $var );
			$max_length        = max( $length, $max_length );
			$variables[ $var ] = true;
        }

        ksort( $variables, SORT_STRING | SORT_FLAG_CASE );

        echo PHP_EOL, "\033[1m", 'WORDPRESS GLOBAL VARIABLES', "\033[0m", PHP_EOL;
        echo str_repeat( '-', 70 ), PHP_EOL;
        echo "\t", 'protected $wp_globals = array(', PHP_EOL;

        $key_size = 35; // Existing key size for easier compare.
		// $key_size = ( $max_length + 3 ); // Space + 2 quotes.

        foreach ( $variables as $var => $ignore ) {
			echo "\t\t", str_pad( "'" . $var . "'", $key_size, ' ', STR_PAD_RIGHT ), '=> true,', PHP_EOL;
        }

		echo "\t", ');', PHP_EOL;

        echo str_repeat( '-', 70 ), PHP_EOL;
    }

}
