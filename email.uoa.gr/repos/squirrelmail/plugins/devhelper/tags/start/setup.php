<?php
/*
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id: setup.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2006 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage devhelper
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_devhelper() {
    global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['menuline']['devhelper'] = 'devhelper_menuline';
}

/**
 * Display menuline link
 * @return void
 */
function devhelper_menuline() {
	displayInternalLink('plugins/devhelper/devhelper_variables.php', "Dev::Variables");
	echo "&nbsp;&nbsp\n";
	displayInternalLink('plugins/devhelper/devhelper_unittesting.php', "Dev::UnitTesting");
	echo "&nbsp;&nbsp\n";
	displayInternalLink('plugins/devhelper/devhelper_tests.php', "Dev::Tests");
	echo "&nbsp;&nbsp\n";
	displayInternalLink('plugins/devhelper/devhelper_toolbox.php', "Dev::Toolbox");
	echo "&nbsp;&nbsp\n";
}    

/**
 * Return information about plugin
 * @return array
 */
function devhelper_info() {
   return array(
       'version' => '0.1'
   );
}

/**
 * Return version info about this plugin
 * @return string
 */
function devhelper_version() {
   $info = devhelper_info();
   return $info['version'];
}

?>
