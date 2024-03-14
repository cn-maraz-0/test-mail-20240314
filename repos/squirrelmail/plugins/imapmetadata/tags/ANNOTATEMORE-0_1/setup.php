<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2005-2006 The SquirrelMail Project Team, Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: setup.php,v 1.2 2006/02/08 12:50:19 avel Exp $
 * @package plugins
 * @subpackage annotatemore
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_annotatemore() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['menuline']['annotatemore'] = 'annotatemore_menuline';
}

/**
 * Display menuline link
 * @return void
 */
function annotatemore_menuline() {
    /*
	bindtextdomain('annotatemore', SM_PATH . 'plugins/annotatemore/locale');
	textdomain ('annotatemore');
    */
		
	displayInternalLink('plugins/annotatemore/annotate_tests.php',_("Annotations"));
	echo "&nbsp;&nbsp\n";

    /* 
	bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
    */
}

/**
 * Versioning information
 * @return string
 */
function annotatemore_version() {
	return '0.1';
}

?>
