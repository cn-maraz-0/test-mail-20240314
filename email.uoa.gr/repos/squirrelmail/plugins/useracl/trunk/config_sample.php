<?php
/**
 * useracl.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2006 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
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
 * - 'sql': Check table in SQL database
 * - 'none': No check is performed
 *
 * @var string One of 'mailbox', 'ldap', 'sql' or 'none'
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
 * If check user method is "SQL", then specify the database DSN
 * to connect to and the table and field to check.
 */
$useracl_sql_dsn = 'pgsql://squirrelmail:qwerty99@unix(/tmp)/squirrelmail';
$useracl_sql_table = 'accounts';
$useracl_sql_username_field = 'id';


/**
 * Restrict only to INBOX.* Folders. Only works for Cyrus Normal Namespace.
 * This is a hack until we properly support NAMESPACE.
 * @var boolean
 */
global $useracl_only_inbox;
$useracl_only_inbox = true;

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

/**
 * How to link to useracl page from Squirrelmail pages?
 * An array with any or more of the values:
 *   'top'    -> Top Squirrelmail navigation links
 *   'folder' -> Panel in folders Page
 * @var array
 */
global $useracl_links;
$useracl_links = array('top', 'folders');
