<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id: setup.php,v 1.18 2005/03/15 16:39:54 avel Exp $
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2004 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage avelsieve
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_avelsieve() {
	global $squirrelmail_plugin_hooks;

	$squirrelmail_plugin_hooks['optpage_register_block']['avelsieve'] =
		'avelsieve_optpage_register_block';
	$squirrelmail_plugin_hooks['menuline']['avelsieve'] =
		'avelsieve_menuline';
	$squirrelmail_plugin_hooks['read_body_header']['avelsieve'] =
		'avelsieve_commands_menu';
}

/**
 * Register options block page
 * @return void
 */
function avelsieve_optpage_register_block() {
	global $optpage_blocks;
	if (defined('SM_PATH')) {
		bindtextdomain ('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
	} else {
		bindtextdomain ('avelsieve', '../plugins/avelsieve/locale');
	}
	textdomain ('avelsieve');

	$optpage_blocks[] = array(
		'name' => _("Message Filters"),
		'url'  => '../plugins/avelsieve/table.php',
		'desc' => _("Server-Side mail filtering enables you to add criteria in order to automatically forward, delete or place a given message into a folder."),
		'js'   => false
	);
	if (defined('SM_PATH')) {
		bindtextdomain('squirrelmail', SM_PATH . 'locale');
	} else {
		bindtextdomain ('squirrelmail', '../locale');
	}
	textdomain('squirrelmail');
}
   
/**
 * Display menuline link
 * @return void
 */
function avelsieve_menuline() {
	global $avelsieveheaderlink;
	include_once(SM_PATH . 'plugins/avelsieve/config/config.php');

	if($avelsieveheaderlink) {
		bindtextdomain('avelsieve', SM_PATH . 'plugins/avelsieve/locale');
		textdomain ('avelsieve');
		
		displayInternalLink('plugins/avelsieve/table.php',_("Filters"));
		echo "&nbsp;&nbsp\n";

		bindtextdomain('squirrelmail', SM_PATH . 'locale');
		textdomain ('squirrelmail');
	}
}    

/**
 * While showing a message, display filter commands.
 * @return void
 * @see avelsieve_commands_menu_do()
 */
function avelsieve_commands_menu() {
	include_once(SM_PATH . 'plugins/avelsieve/include/message_commands.inc.php');
	avelsieve_commands_menu_do();
}

/**
 * Versioning information
 * @return string
 */
function avelsieve_version() {
	return '1.9.2';
}

?>
