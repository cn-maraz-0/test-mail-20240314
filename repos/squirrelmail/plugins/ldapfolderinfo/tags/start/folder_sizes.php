<?php
/**
 * folder_sizes.php
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @author Robin Rainton <robin@rainton.com>
 * @package plugins
 * @subpackage ldapfolderinfo
 */

/**
 * Includes
 */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');

    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'include/load_prefs.php');
}

include_once(SM_PATH . 'functions/imap.php');

include_once(SM_PATH . 'plugins/ldapfolderinfo/include/constants.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/config.php');
include_once(SM_PATH . 'plugins/useracl/imap_acl.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/include/quota.inc.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/include/quota_display.inc.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/include/functions.inc.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/include/folder_sizes.inc.php');

include_once(SM_PATH . 'plugins/directory/functions.php');

sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
sqgetGlobalVar('authz', $authz, SQ_SESSION);

sqgetGlobalVar('delimiter', $delimiter,     SQ_SESSION);

$imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);

if($askldap == true) {
    include_once SM_PATH . "plugins/ldapuserdata/functions.php";
    include_once SM_PATH . "plugins/ldapuserdata/config.php";
    $ldap = ldapuserdata_ldap_connect('squirrel');
} else {
    $ldap = false;
}

displayPageHeader($color, 'None');
    
bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
textdomain ('ldapfolderinfo');

$location = get_location();

ldapfolderinfo_folder_sizes_list($imapConnection, $ldap);

if($ldap) {
    ldap_close($ldap);
}

sqimap_logout($imapConnection);

echo '</body></html>';

