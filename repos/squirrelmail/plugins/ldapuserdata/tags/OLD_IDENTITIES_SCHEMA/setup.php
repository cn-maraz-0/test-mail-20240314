<?php
/**
 *  SquirrelMail LDAP Prefs Backend Plugin (ldapuserdata)
 *  By Alexandros Vellis <avel@users.sourceforge.net>
 *  Based on Retrieve User Data Plugin
 *  by Ralf Kraudelt <kraude@wiwi.uni-rostock.de>
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * $Id: setup.php,v 1.2 2004/02/10 15:03:26 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

/**
 * Ldapuserdata plugin registration
 */
function squirrelmail_plugin_init_ldapuserdata() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['login_before']['ldapuserdata'] = 'ldapuserdata_read';
	$squirrelmail_plugin_hooks['logout']['ldapuserdata'] = 'ldapuserdata_write';
}

/**
 * Wrapper function for Reading of User Preferences from LDAP.
 *
 * @see ldapuserdata_read_do()
 */
function ldapuserdata_read() {
	include_once( SM_PATH . 'plugins/ldapuserdata/functions.php');
	ldapuserdata_read_do();
}

/**
 * Wrapper function for Writing of User Preferences to LDAP.
 *
 * @see ldapuserdata_write_do()
 */
function ldapuserdata_write() {
	include_once(SM_PATH . 'plugins/ldapuserdata/functions.php');
	ldapuserdata_write_do();
}

/**
 * Returns the imap server address for the logged in user.
 *
 * If your LDAP schema has mailHost in it (for instance, perdition schema), you
 * can tell your Squirrelmail to use this function so as to decide which IMAP
 * server to log in to. You will have to put the following line in your
 * config/config.php:
 *
 * $imapServerAddress = 'map:ldapuserdata_get_imapserveraddress';
 *
 * Special cases:
 * - If authz (authorization identity) exists in the session, returns the
 *   server address for that user, and disable imapproxy in case it is
 *   configured to be used.
 *
 * - If $imapproxymode == true, that is, we use imapproxy daemon to connect,
 *   return the imapproxy address (usually localhost) based on the mapping done
 *   in ldapuserdata/config.php.
 *
 * @return str IMAP host for the logged in user.
 */
function ldapuserdata_get_imapserveraddress() {
	include_once(SM_PATH . 'plugins/ldapuserdata/config.php');
	// ?
	global $imapproxymode;

	if (isset($_SESSION['authz'])) {
		/* Disable imapproxy because it cannot handle SASL PLAIN, and
		 * connect straight to the server. */
		return $_SESSION['imapServerAddress'];
	}

	if($imapproxymode == true) {
		global $ldapuserdata_imapproxyserv;
		if (isset($ldapuserdata_imapproxyserv[$_SESSION['imapServerAddress']])) {
			return ($ldapuserdata_imapproxyserv[$_SESSION['imapServerAddress']]);
		} else {
			return false;
		}

	} else {
		if(isset($_SESSION['imapServerAddress'])) {
			return $_SESSION['imapServerAddress'];
		} else {
			return false;
		}
	}
}

/**
 * Ldapuserdata version information.
 * @return string
 */
function ldapuserdata_version() {
	return '0.5cvs';
}

?>
