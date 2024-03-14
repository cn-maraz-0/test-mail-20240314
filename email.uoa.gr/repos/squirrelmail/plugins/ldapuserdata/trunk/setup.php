<?php
/**
 *  SquirrelMail LDAP Prefs Backend Plugin (ldapuserdata)
 *  By Alexandros Vellis <avel@users.sourceforge.net>
 *  Based on Retrieve User Data Plugin
 *  by Ralf Kraudelt <kraude@wiwi.uni-rostock.de>
 *
 * Copyright (c) 1999-2009 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * $Id: setup.php,v 1.10 2006/12/19 12:17:27 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

/**
 * Ldapuserdata plugin registration
 */
function squirrelmail_plugin_init_ldapuserdata() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['prefs_backend']['ldapuserdata'] = 'ldapuserdata_register_prefs_backend';
    $squirrelmail_plugin_hooks['login_verified']['ldapuserdata'] = 'ldapuserdata_read';
    $squirrelmail_plugin_hooks['logout']['ldapuserdata'] = 'ldapuserdata_write';
    $squirrelmail_plugin_hooks['options_personal_save']['ldapuserdata'] = 'ldapuserdata_flush';
    $squirrelmail_plugin_hooks['options_display_save']['ldapuserdata'] = 'ldapuserdata_flush';
    $squirrelmail_plugin_hooks['options_folder_save']['ldapuserdata'] = 'ldapuserdata_flush';
    $squirrelmail_plugin_hooks['mail_fetch_after_fetch']['ldapuserdata'] = 'ldapuserdata_flush';
    $squirrelmail_plugin_hooks['options_save']['ldapuserdata'] = 'ldapuserdata_flush';
    $squirrelmail_plugin_hooks['compose_send']['ldapuserdata'] = 'ldapuserdata_compose_check';
  
    $squirrelmail_plugin_hooks['optpage_loadhook_display']['ldapuserdata']   = 'ldapuserdata_display_options';
    $squirrelmail_plugin_hooks['optpage_loadhook_personal']['ldapuserdata']  = 'ldapuserdata_display_options';
    $squirrelmail_plugin_hooks['optpage_loadhook_folder']['ldapuserdata']    = 'ldapuserdata_display_options';
    $squirrelmail_plugin_hooks['optpage_loadhook_highlight']['ldapuserdata'] = 'ldapuserdata_display_options';
    $squirrelmail_plugin_hooks['optpage_loadhook_order']['ldapuserdata']     = 'ldapuserdata_display_options';
    
    //$squirrelmail_plugin_hooks['options_identities']['ldapuserdata']     = 'ldapuserdata_display_options';
}

/**
 * Register our prefs backend with Squirrelmail, using the new hook function
 * 'prefs_backend' which resides in functions/prefs.php. This works since
 * 1.4.3rc1 and in -DEVEL (1.5.0).
 *
 * return string The path to the Prefs backend file, which implements
 * getPref(), setPref() et al.
 */
function ldapuserdata_register_prefs_backend() {
    return 'plugins/ldapuserdata/ldap_prefs.php';
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
    if (!sqsession_is_registered('user_is_logged_in')) {
        return;
    }
    include_once(SM_PATH . 'plugins/ldapuserdata/functions.php');
    ldapuserdata_write_do();
}

/**
 * Wrapper function for Writing of User Preferences to LDAP while saving the
 * options. This function is used inside the user session in Squirrelmail.
 *
 * @see ldapuserdata_write_do()
 */
function ldapuserdata_flush() {
    include_once( SM_PATH . 'plugins/ldapuserdata/functions.php');
    ldapuserdata_write_do();
}

/**
 * Wrapper function for checking of desirable objectclass while sending mail.
 *
 * @see ldapuserdata_compose_check_do()
 */
function ldapuserdata_compose_check(&$argv) {
    include_once( SM_PATH . 'plugins/ldapuserdata/functions.php');
    return ldapuserdata_compose_check_do($argv);
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
 * Remove options that are not displayed.
 * Inspired by vlogin plugin. :)
 */
function ldapuserdata_display_options() {
    include_once( SM_PATH . 'plugins/ldapuserdata/functions.php');
    ldapuserdata_display_options_do();
}

/**
 * Return information about plugin.
 * @return array
 */
function ldapuserdata_info() {
   return array(
       'english_name' => 'LDAPUserData: LDAP Prefs Backend',
       'version' => '0.5svn',
       'summary' => 'Squirrelmail Preferences backend, that retrieves and stores certain critical / important data to an LDAP server.'
   );
}

/**
 * Return plugin version.
 * @return string
 */
function ldapuserdata_version() {
   $info = ldapuserdata_info();
   return $info['version'];
}

