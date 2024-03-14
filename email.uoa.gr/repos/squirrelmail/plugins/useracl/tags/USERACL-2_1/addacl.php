<?php
/**
 * addacl.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2006 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: addacl.php,v 1.5 2006/07/25 09:08:26 avel Exp $
 *
 * This file can be called from other Squirrelmail plugins or pages, which want
 * to provide a direct link to add ACLs to a predefined username.
 *
 * An example for such kind of usage is provided by the directory plugin (see
 * http://email.uoa.gr/projects/squirrelmail/directory.php )
 *
 * This file is work in progress... Interface has not been finalized yet.
 */

if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    require_once(SM_PATH . 'include/validate.php');
    require_once(SM_PATH . 'functions/global.php');
    require_once(SM_PATH . 'functions/imap.php');
    require_once(SM_PATH . 'functions/display_messages.php');
}

$prev = bindtextdomain ('useracl', SM_PATH . 'plugins/useracl/locale');
textdomain ('useracl');
require_once(SM_PATH . 'plugins/useracl/config.php');
require_once(SM_PATH . 'plugins/useracl/imap_acl.php');
require_once(SM_PATH . 'plugins/useracl/functions.php');
require_once(SM_PATH . 'plugins/useracl/html.php');

sqgetGlobalVar('key',          $key,           SQ_COOKIE);
sqgetGlobalVar('username',     $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',   $onetimepad,    SQ_SESSION);
sqgetGlobalVar('delimiter',    $delimiter,     SQ_SESSION);

/* GET Variables that are passed from other pages */
/* e.g. addacl?mailbox=INBOX.foo&user=bar */
if(isset($_GET['mailbox'])) {
	$mybox = $_GET['mailbox'];
} else {
	$mybox = '';
}

if(isset($_GET['user'])) {
	$myuser = $_GET['user'];
} else {
	$myuser = '';
}


/* Printing */

$location = get_location();

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

if(sqimap_capability($imapConnection, 'ACL') == false ) {
	print 'IMAP server does not support the ACL capability, sorry.';
	sqimap_logout($imapConnection);
	exit;
}

$boxes = sqimap_mailbox_list($imapConnection);
sqimap_logout($imapConnection);


if ($compose_new_win == '1') {
	displayHtmlHeader(_("Add New User Permission"), '', false);
} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayPageHeader($color, $mailbox);

}

$prev = bindtextdomain ('useracl', SM_PATH . 'plugins/useracl/locale');
textdomain ('useracl');



useracl_html_printheader( _("Add New User Permission") );
useracl_html_print_all_sections_start();

useracl_html_print_section_start( _("Add New Share to User") );

print '<form name="form_addnew" action="useracl.php?addacl=1';
if(isset($myuser)){
	print '&amp;user='.$myuser;
}
print '" method="post">';
print '<table width="100%" border="0" >';
useracl_print_table_header(true);
useracl_print_addnew_separate($mybox, $myuser);
print '</table>';
print '</form>';

useracl_html_print_section_end();


useracl_html_print_all_sections_end();


$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');

if ($compose_new_win == '1') {
	print '<INPUT TYPE="BUTTON" NAME="Close" onClick="return self.close()" VALUE='._("Close").'></TD></TR>'."\n";
}

useracl_html_printfooter();

?>
