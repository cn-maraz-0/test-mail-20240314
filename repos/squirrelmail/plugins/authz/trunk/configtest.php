<?php
/**
 * authz plugin for Squirrelmail
 *
 * Configtest function
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @copyright &copy; 2006 Alexandros Vellis <avel@noc.uoa.gr>, the SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: configtest.php,v 1.2 2006/10/18 13:11:38 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
/**
 * Perform configtest check for authz
 *
 * @return boolean false: no fatal errors; true: fatal errors
 */
function authz_configtest_do() {
    global  $authz_use_imap_tls, $authz_imap_auth_mech, $authz_imapPort_tls,
	    $authz_cyrusadmin_accounts, $authz_allowalladmins, $authz_delimiter;

    if(!file_exists(SM_PATH . 'plugins/authz/config.php')) {
        do_err('AuthZ plugin is enabled but its configuration file is missing; please copy over
                plugins/authz/config_sample.php to plugins/authz/config.php and change it
                accordingly.<br/>', true);
        return true;
    }

    if($authz_imap_auth_mech == 'cram-md5' || $authz_imap_auth_mech == 'login') {
        do_err('AuthZ plugin is enabled, but it is misconfigured;
        the SASL authentication mechanism '.strtoupper($authz_imap_auth_mech).' does not
        support different authentication / authorization identites, thus you will not be
        able to perform proxy logins.<br/>', false);
    }
    
    if($authz_imap_auth_mech == 'plain' && !$authz_use_imap_tls) {
        do_err('AuthZ plugin is enabled, but you have PLAIN SASL
            authentication mechanism, whereas you have <em>not</em> set the TLS transport
            layer (via $authz_use_imap_tls in plugins/authz/config.php). Proxy logins
            (i.e. with different authentication / authorization IDs) might not work.<br/>', false);
    }


    return false;
}

?>
