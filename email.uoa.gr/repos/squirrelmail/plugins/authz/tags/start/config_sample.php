<?php
/**
 * authz plugin for Squirrelmail 1.4+
 *
 * Configuration File
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @copyright &copy; 2006 Alexandros Vellis <avel@noc.uoa.gr>, the SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: config_sample.php,v 1.1.1.1 2006/10/06 14:26:53 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
global  $authz_use_imap_tls_proxy, $authz_imap_auth_mech_proxy, $authz_imapPort_tls,
	$authz_cyrusadmin_accounts, $authz_allowalladmins;

/**
 * @var boolean Enable TLS for proxy authentication connections. For normal 
 * connections, the Squirrelmail configuration will be honored.
 */
$authz_use_imap_tls_proxy   = true;

/**
 * @var string Authentication Mechanism for proxy authentication connections 
 * (usually plain, or even digest-md5, but cannot be LOGIN)
 */
$authz_imap_auth_mech_proxy = 'plain';

/**
 * @var int Port to use for TLS connections to IMAP server.
 */
$authz_imapPort_tls           = 993;

/**
 * @var array Username mapping for cyrusadmin-type (proxy) accounts.
 * Format is: 
 * 'username that will be used in the Squirrelmail login interface' =>
 * 'cyrus adminaccount to be used on Cyrus'.
 * This mapping can be used for shorthand purposes.
 */
$authz_cyrusadmin_accounts = array(
	'cyradm' => 'cyrusimap'
);

/** @var boolean
 * Should I try to log in as every username given to me? If set to false, only
 * the cyrus accounts above will be allowed through the Squirrelmail interface.
 */
$authz_allowalladmins = true;

/**
 * @var string
 * Delimiter with which to separate the authz & authc from a single login input
 * box. Default is ':'.
 */
$authz_delimiter = ':';

?>
