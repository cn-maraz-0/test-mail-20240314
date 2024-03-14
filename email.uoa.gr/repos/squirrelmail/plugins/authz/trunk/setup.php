<?php
/**
 * authz plugin
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
 * @version $Id: setup.php,v 1.2 2006/10/09 14:41:56 avel Exp $
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
	$squirrelmail_plugin_hooks['login_verified']['authz'] = 'authz_save_authz';
	$squirrelmail_plugin_hooks['configtest']['authz'] = 'authz_configtest';
}

/**
 * Wrapper to extract authcid/authzid from login box.
 * @return void
 * @see authz_parse_loginusername_do()
 */
function authz_parse_loginusername() {
    include_once(SM_PATH . 'plugins/authz/functions.php');
    authz_parse_loginusername_do();
}
   
/**
 * Wrapper to save authzid into session after login has been verified.
 * @return void
 * @see authz_save_authz_do()
 */
function authz_save_authz() {
    include_once(SM_PATH . 'plugins/authz/functions.php');
    authz_save_authz_do();
}

/**
 * Config Test
 * @return boolean
 */
function authz_configtest() {
	include_once(SM_PATH . 'plugins/authz/configtest.php');
	return authz_configtest_do();
}

/**
 * Return information about plugin
 * @return array
 */
function authz_info() {
   return array(
       'version' => '0.1-1.5.2',
       'requirements' => 'An IMAP server that supports SASL PLAIN or SASL DIGEST-MD5 and different'.
           ' authentication / authorization logins'
   );
}

/**
 * Return version info about this plugin
 * @return string
 */
function authz_version() {
   $info = authz_info();
   return $info['version'];
}

?>
