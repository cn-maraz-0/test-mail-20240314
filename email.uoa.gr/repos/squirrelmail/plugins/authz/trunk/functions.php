<?php
/**
 * authz plugin for Squirrelmail
 *
 * Functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @copyright &copy; 2006 Alexandros Vellis <avel@noc.uoa.gr>, the SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: functions.php,v 1.2 2006/10/09 14:41:56 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
include_once(SM_PATH . 'plugins/authz/config.php');

/**
 * This function will break up the login string into two separate strings.
 * One of them will be the authz, the other one will be the authc.  Then, the 
 * corresponding global variables will be set and also registered into session.
 *
 * @return void
 */
function authz_parse_loginusername_do() {
    global  $login_username, $authz_cyrusadmin_accounts,
            $imap_auth_mech, $authz_imap_auth_mech,
            $use_imap_tls, $authz_use_imap_tls,
            $imapPort, $authz_imapPort_tls,
            $authz_allowalladmins, $authz_delimiter;

    $as = explode($authz_delimiter, $login_username, 2);
    
    if(sizeof($as) == 1) { 
        /* Normal users; no authz */
        //syslog(LOG_DEBUG, 'Normal Login, no authz');
        return;

    } else {
        /* Proxy Authorization Login
         * Parsed from: "cyrusadmin:uid", where : is the delimiter
         * */
        $authz = $as[0];
        $authc = $as[1];
    
        /* Do mapping */
        foreach ($authz_cyrusadmin_accounts as $cyradm_sq => $cyradm_cy) {
            if($authz == $cyradm_sq) {
                $authz = $cyradm_cy;
                $n = true;
            }
        }
    
        if($authz_allowalladmins == true && !isset($n)) { // Try to login ANW
            $login_username = $authc;
        }
        $login_username = $authc;

        $GLOBALS['authz'] = $authz;
        return;
    }
}

/**
 * Since we don't have the session actually set up at hook login_before,
 * now is the time to save the authz value into session.
 *
 * @param void
 * @return void
 */
function authz_save_authz_do() {
    global $authz;
    if(!empty($authz)) {
        sqsession_register ($authz, 'authz');
    }
}

?>
