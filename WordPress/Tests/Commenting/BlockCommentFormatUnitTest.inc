<?php

/*
 * This is a correctly formatted multi-line block comment.
 *
 * This is another paragraph.
 */

		/*
		 * This is a correctly formatted multi-line block comment.
		 *
		 * This is another paragraph.
		 */

/* Correct - one line multi-line block comment. */

	// Empty multi-line block comment + wrong style closer.
	/*
	 *
	 **/

	// Empty without stars.
	/*


	*/

// Empty one-liner.
/* */

/*one-line comment without space after opener */
/* one-line comment without space before closer*/
/*one-line comment without surrounding spaces*/

/*         one-line comment with too much space after opener */
/* one-line comment with too much space before closer    */
/*    one-line comment with too much space around it    */

		/* The open tag should be on its own line.
		 *
		 * The close tag is not on its own line. */

$var = 'a'; /* The open tag should be on its own line.
		 *
		 * The close tag is not on its own line.
		 */ apply_filters( 'something', $number );

/* Another comment. */ /* The open tag should be on its own line.
		 *
		 * The close tag is not on its own line.
		 *//* Another comment. */ // More comments.

/*
 *
 *
 * Incorrectly formatted:
 * - Blank lines at start of multi-line block comment.
 * - Blank lines at end of multi-line block comment.
 *
 * @param int $number Number of things.
 *
 *
 */

		/*
		 * Incorrect: multiple subsequent empty lines.
		 *
		 *
		 * This is a long description.
		 *
		 *
		 *
		 *
		 * This is more description.
		 */

/*
 *Incorrectly formatted: There are no spaces after the stars.
 *
 *This is another paragraph.
 */

/*
 *      Incorrectly formatted: There are too many spaces after the stars.
 *
 *         This is another paragraph.
 */

/*
         Incorrectly formatted: The stars are missing and the indentation is off.

This is another paragraph.
 */

		/*
		         Incorrectly formatted: The stars are missing and the indentation is off.

	This is another paragraph.
		 */

/*
* This is an INcorrectly aligned multi-line block comment: no space before (most) stars.
 *
* This is another paragraph.
*/

		/*
		* This is an INcorrectly aligned multi-line block comment: no space before (most) stars.
		*
		 * This is another paragraph.
		*/

/*
  * This is an INcorrectly aligned multi-line block comment: too much space before stars.
  *
 * This is another paragraph.
  */

		/*
		  * This is an INcorrectly aligned multi-line block comment: too much space before stars.
		 *
		  * This is another paragraph.
		  */

		/*
 * This is a INcorrectly aligned multi-line block comment: stars not aligned.
 *
 * This is another paragraph.
 */

		/*
			 * This is a INcorrectly aligned multi-line block comment: stars not aligned.
			 *
			 * This is another paragraph.
			 */

		/*
	* This is a INcorrectly aligned multi-line block comment: stars not aligned.
	*
	* This is another paragraph.
	*/

		/*
			* This is a INcorrectly aligned multi-line block comment: stars not aligned.
	*
		* This is another paragraph.
			*/

		/*
	  * This is a INcorrectly aligned multi-line block comment: stars not aligned.
	  *
	  * This is another paragraph.
	  */

		/*
			   * This is a INcorrectly aligned multi-line block comment: stars not aligned.
			   *
			   * This is another paragraph.
			   */
