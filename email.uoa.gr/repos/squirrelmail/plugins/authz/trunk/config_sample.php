<?php
/**
 * authz plugin for Squirrelmail
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
 * @version $Id: config_sample.php,v 1.2 2006/10/09 14:41:55 avel Exp $
 * @package plugins
 * @subpackage authz
 */
   
global  $authz_use_imap_tls, $authz_imap_auth_mech, $authz_imapPort_tls,
	$authz_cyrusadmin_accounts, $authz_allowalladmins, $authz_delimiter;

/**
 * @var int Enable TLS for proxy authentication connections. For normal 
 * connections, the Squirrelmail configuration will be honored.
 * 0: no encryption
 * 1: use TLS
 * 2: use STARTTLS (PHP >= 5 only)
 */
$authz_use_imap_tls = 1;

/**
 * @var string Authentication Mechanism for proxy authentication connections 
 * (usually plain, or even digest-md5, but cannot be LOGIN)
 */
$authz_imap_auth_mech = 'plain';

/**
 * @var int Port to use for TLS connections to IMAP server.
 */
global $authz_imapPort_tls;
$authz_imapPort_tls = 993;

/**
 * @var array Username mapping for cyrusadmin-type (proxy) accounts.
 * Format is: 
 * 'username that will be used in the Squirrelmail login interface' =>
 * 'cyrus adminaccount to be used on Cyrus'.
 * This mapping can be used for shorthand purposes.
 * This array will also be used to define usernames to allow performing
 * proxy logins.
 */
global $authz_cyrusadmin_accounts;
$authz_cyrusadmin_accounts = array(
	'cyrusimap' => 'cyrusimap',
	'cyrusadmin' => 'cyrusadmin'
);

/** @var boolean
 * Should I try to log in as every username given to me? If set to false, only
 * the cyrus accounts above will be allowed through the Squirrelmail interface.
 */
global $authz_allowalladmins;
$authz_allowalladmins = true;

/**
 * @var string
 * Delimiter with which to separate the authz & authc from a single login input
 * box. Default is ':'.
 */
$authz_delimiter = ':';

?>
