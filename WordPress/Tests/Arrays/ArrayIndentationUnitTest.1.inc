<?php

// Issue #1179.
b(
	array(
	1 => false, // wat
	2 => false,
	)
);

			$a = array(
				'post_type'    => 'post',
				'post_content' => '
Page 1/5
<!--nextpage-->
Page 2/3
<!--nextpage-->
Page 3/3
',
			);

// Test correct handling of PHPCS annotations.
$phpcs_whitelist_comment_test = array(
	1 => false, // phpcs:disable Standard.Category.Sniff
		2 => false, // phpcs:enable Standard.Category.Sniff
);

$phpcs_whitelist_comment_test = [
		/*
		 * phpcs:disable Standard.Category.Sniff -- for reasons.
		 */
		'truthy' => apply_filters( '...', true ),
		'falsy'  => apply_filters(
			'...',
			// phpcs:ignore Standard.Category.Sniff -- for reasons.
			true
		),
'third' =>
'something'
// comment
// phpcs:ignore Standard.Category.Sniff -- for reasons.
. 'else',
			'fourth' =>
				'something'
				// phpcs:disable Standard.Category.Sniff -- for reasons.
				// phpcs:disable Standard.Category.Sniff -- for reasons.
				. 'else',
];

$phpcs_whitelist_comment_test = [
// phpcs:enable Standard.Category.Sniff -- for reasons.
'something' =>
	apply_filters( '...', true ),
'something' =>
	// phpcs:enable Standard.Category.Sniff -- for reasons.
	apply_filters( '...', true ),
];
