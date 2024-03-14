<?php
/*
 * Javascript libraries management framework for Squirrelmail Plugins.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * This file is a wrapper for minify javascript optimizer. minify throws errors in 
 * PHP4, and that's why this file is included only if minify is enabled in 
 * config.php.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team
 * @package plugins
 * @subpackage javascript_libs
 */

/* Uhm, do not call me directly please. Only through an 'include'. */
if (basename(strip_tags($_SERVER['PHP_SELF'])) == 'minify_wrapper.php') {
    die();
}

/* This is the line that chokes in PHP4 with a parse error. */
$minifyJS  = new Minify(Minify::TYPE_JS);

