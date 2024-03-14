<?php
/**
 * useracl.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 */

/**
 * Configuration File
 */

/**
 * What kind of check to perform, in order to determine if a user exists in the
 * system?
 *
 * Currently supported schemes:
 * - 'mailbox': User must have an existing INBOX (not implemented yet)
 * - 'ldap': User must exist in LDAP
 * - 'none': No check is performed
 *
 * @var string One of 'mailbox', 'ldap' or 'none'
 */
 
$check_user_method = 'none';


/**
 * If check user method is "LDAP", then specifify the LDAP server number of the
 * Squirrelmail configuration to check in.
 *
 * @var int
 */

$ldap_server_no = 0;

/**
 * If check user method is "mailbox", then supply here credentials which have
 * cyrusadmin rights, so that the existence of some other user's mailbox can be
 * checked. This method is not recommended.
 */
$useracl_root_username = 'cyrusadmin';
$useracl_root_password = 'secret';

/**
 * Show full names in addition to short usernames. They will be searched in the
 * LDAP Directory defined by $ldap_server_no.
 */
$show_ldap_cn = false;

?>

