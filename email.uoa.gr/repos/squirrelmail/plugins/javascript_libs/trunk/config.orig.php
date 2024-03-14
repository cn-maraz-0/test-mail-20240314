<?php
/*
 * Javascript libraries management framework for Squirrelmail Plugins.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team
 * @package plugins
 * @subpackage javascript_libs
 */

/**
 * Use 'minify' javascript optimizer?
 *
 * 0 => Do not use minified versions. Useful for development & debugging.
 * 1 => Use on-the-fly generated minified files. (Not PHP4 compatible).
 * 2 => Use cached minified files
 *
 */
define('JAVASCRIPT_LIBS_USE_MINIFY', 2);

