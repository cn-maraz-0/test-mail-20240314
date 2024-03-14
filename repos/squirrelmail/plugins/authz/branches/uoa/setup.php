<?php
/**
 * authz plugin for Squirrelmail 1.4+
 *
 * Main setup file for plugin registration.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @copyright &copy; 2006 Alexandros Vellis <avel@noc.uoa.gr>, the SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: setup.php,v 1.1.1.1 2006/10/06 14:26:53 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
include_once(SM_PATH . 'plugins/authz/config.php');

/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_authz() {
	global $squirrelmail_plugin_hooks;
      
	$squirrelmail_plugin_hooks['login_before']['authz'] = 'authz_parse_loginusername';
}

/**
 * Wrapper to main function call.
 * @return void
 */
function authz_parse_loginusername() {
    include_once(SM_PATH . 'plugins/authz/functions.php');
    authz_parse_loginusername_do();
}
   

/**
 * Plugin version
 * @return string
 */
function authz_version() {
	return '0.1';
}
 
?>
