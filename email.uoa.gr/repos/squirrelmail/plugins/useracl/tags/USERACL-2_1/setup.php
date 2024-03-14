<?php
/**
 * setup.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2006 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: setup.php,v 1.14 2006/07/25 10:31:44 avel Exp $
 *
 * Squirrelmail Plugin Functions
 */

/**
 * Plugin initialization
 */
function squirrelmail_plugin_init_useracl() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['menuline']['useracl'] = 'useracl_menuline';
	$squirrelmail_plugin_hooks['pagetop']['useracl'] = 'useracl_pagetop';
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
 * Place link in Squirrelmail page-top ("Current Folder: Foo").
 */
function useracl_pagetop() {
	bindtextdomain('useracl', SM_PATH . 'plugins/useracl/locale');
	textdomain ('useracl');

	include_once(SM_PATH . 'plugins/useracl/pagetop.php');
	$out = useracl_pagetop_do();

	bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');

    return $out;
}    

/**
 * Version information
 */
function useracl_version() {
	return '2.1';
}
 
?>
