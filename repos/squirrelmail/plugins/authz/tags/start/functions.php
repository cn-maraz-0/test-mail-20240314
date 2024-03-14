<?php
/**
 * authz plugin for Squirrelmail 1.4+
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
 * @version $Id: functions.php,v 1.1.1.1 2006/10/06 14:26:53 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
/**
 * This function will break up the login string into two separate strings.
 * One of them will be the authz, the other one will be the authc.  Then, the 
 * corresponding global variables will be set and also registered into session.
 *
 * @return void
 */
function authz_parse_loginusername_do() {
	global  $login_username, $cyrusadmin_accounts, $authz,
            $imap_auth_mech, $authz_imap_auth_mech_proxy,
            $use_imap_tls, $authz_use_imap_tls_proxy,
            $imapPort, $authz_imapPort_tls,
            $authz_allowalladmins, $authz_delimiter;


	$as = explode($authz_delimiter, $login_username, 2);
	
	if(sizeof($as) == 1) { 
		/* Normal users; no authz */
		return;

	} else {
		/* Proxy Authorization Login */

		$temp_username = $as[0];
		$authz = $as[1];
	
		/* Do mapping */
		foreach ($cyrusadmin_accounts as $cyradm_sq => $cyradm_cy) {
			if($temp_username == $cyradm_sq) {
				$login_username = $cyradm_cy;
				$n = true;
			}
		}
	
		if($authz_allowalladmins == true && !isset($n)) { // Try to login ANW
			$login_username = $temp_username;
		}
		
		$imap_auth_mech = $imap_auth_mech_proxy;
		$use_imap_tls = $use_imap_tls_proxy;
		$imapPort = $imapPort_tls;
	
		sqsession_register ($authz, 'authz');
		return;
	}
}
?>
