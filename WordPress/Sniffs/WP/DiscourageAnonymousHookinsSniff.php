<?php

/*
-> findnext in param for T_ANON_CLASS and T_CLOSURE
-> if not found, check if the param **is** a variable (not if it contains one) and if it is, see about finding the variable declaration within scope, if found and "simple", check the same till end of statement. If not found or not "simple", i.e. array[] assignment or $callback .= etc, i.e. not a straight equal after variable, then warn no matter what

*/


/*
https://make.wordpress.org/core/2019/03/26/coding-standards-updates-for-php-5-6/

Other things to check:
- Will short arrays pass WPCS anyway ?
  - Check if any sniffs would need adjusting.
- Move assignments in condition check to Core
- Check with Pento about moving strict comparison checks to Core
- Do some tests maybe with short ternary syntax ?

*/

