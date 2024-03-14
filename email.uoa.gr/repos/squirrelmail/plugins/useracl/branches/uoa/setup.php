<?php
/**
 * useracl.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package useracl
 */

/**
 * Squirrelmail Plugin Functions
 */

/**
 * Plugin initialization
 */
function squirrelmail_plugin_init_useracl() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['menuline']['useracl'] = 'useracl_menuline';
}

/**
 * Place Link to Squirrelmail's Menu Line
 */
function useracl_menuline() {
	bindtextdomain('useracl', SM_PATH . 'plugins/useracl/locale');
	textdomain ('useracl');

   	displayInternalLink('plugins/useracl/useracl.php', _("Shares") ,'right');
	echo "&nbsp;&nbsp\n";

	bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
}    

/**
 * Version information
 */
function useracl_version() {
	return '1.0';
}
 
?>
