<?php

include('/file.ext'); // Warning.
include_once('/file.ext'); // Warning.
require('/file.ext'); // Warning.
require_once('/file.ext'); // Warning.
include dirname( __FILE__ ) . '/myfile.php'; // Warning.
require_once $path_to_file; // Warning.

include_once dirname( __FILE__ ) . '/inc/tgm/class-tgm-plugin-activation.php'; // OK.
include_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php/tgm.php'; // Warning.
include_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php' . '/tgm.php'; // Warning.
include_once dirname( __FILE__ ) . 'adjusted-class-tgm-plugin-activation.php'; // Warning.
include_once dirname( __FILE__ ) . '/inc/adjusted-class-tgm-plugin-activation.php'; // Warning.

// This needs to be the last test in the file.
// OK for reason that this is unfinished code/parse error.
include_once '/directory/'
