<?php
/**
 * useracl.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: config_sample.php,v 1.6 2004/08/09 13:07:26 avel Exp $
 *
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
 * TODO - Not implemented yet.
 */
$useracl_root_username = 'cyrusadmin';
$useracl_root_password = 'secret';

/**
 * Restrict only to INBOX.* Folders. Only works for Cyrus Normal Namespace.
 * This is a hack until we properly support NAMESPACE.
 * @var boolean
 */
$useracl_only_inbox = false;

/**
 * Show full names in addition to short usernames. They will be searched in the
 * LDAP Directory defined by $ldap_server_no.
 */
$show_ldap_cn = false;

/**
 * Enable option for notifying user?
 * @var boolean
 */
$useracl_enable_notify = true;

/**
 * Show iconic image submit buttons?
 * @var boolean
 */
$useracl_show_images = true;

/**
 * Insert here a URL that describes the shared folders functionality. Leave
 * this empty ('') to disable.
 * @var string
 */

$useracl_notify_url = '';
//$useracl_notify_url = "http://mail.example.org/help/mail_folders_help.html";

?>
