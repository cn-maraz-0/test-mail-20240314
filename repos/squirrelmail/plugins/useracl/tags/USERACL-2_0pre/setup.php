<?php
/**
 * setup.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: setup.php,v 1.8 2004/08/09 14:46:05 avel Exp $
 *
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

   	displayInternalLink('plugins/useracl/useracl.php', _("Shares"));
	echo "&nbsp;&nbsp\n";

	bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
}    

/**
 * Version information
 */
function useracl_version() {
	return '2.0pre';
}
 
?>
