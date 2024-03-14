<?php
/**
 * useracl.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package useracl
 */

/**
 * Main routine - Business and Presentation Logic (GUI) are in here
 */

/* Path for SquirrelMail required files. */
define('SM_PATH','../../');

require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'plugins/useracl/config.php');
require_once(SM_PATH . 'plugins/useracl/imap_acl.php');
require_once(SM_PATH . 'plugins/useracl/functions.php');
require_once(SM_PATH . 'plugins/useracl/html.php');
require_once(SM_PATH . 'plugins/useracl/mailnotify.php');

sqgetGlobalVar('key',          $key,           SQ_COOKIE);
sqgetGlobalVar('username',     $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',   $onetimepad,    SQ_SESSION);
sqgetGlobalVar('delimiter',    $delimiter,     SQ_SESSION);

if(isset($_POST['mbox'])) {
	$mbox = urldecode($_POST['mbox']);
}
	
if (isset($_GET['addacl'])) {
	$addacl = 1;
}

if(isset($_POST['perm_prev'])) {
	$perm_prev = $_POST['perm_prev'];
}

if(isset($_POST['mailbox'])) {
	$selected_mbox = urldecode($_POST['mailbox']);
} elseif(isset($_GET['mailbox'])) {
	$selected_mbox = urldecode($_GET['mailbox']);
}

if(isset($_POST['notify'])) {
	$notify = true;
} else {
	$notify = false;
}

if (isset($_POST['adduser'])) {
	$adduser = $_POST['adduser'];
}

if(isset($mbox) && isset($addacl)) {
	$selected_mbox = $mbox;
}

/*  Useful For mail notifications  */
$identity = getPref($data_dir, $username, 'identities');
if (isset($identity) && $identity != 'default') {
	$email_address = getPref($data_dir, $username,'email_address' . $identity);
	$full_name = getPref($data_dir, $username,'full_name' . $identity);
        // $reply_to = getPref($data_dir, $username,'reply_to' . $identity);
} else {
        $from_mail = getPref($data_dir, $username, 'email_address');
        $full_name = getPref($data_dir, $username, 'full_name');
        $reply_to = getPref($data_dir, $username,'reply_to');
}


/* i18n setup */
$lang_iso = getPref($data_dir, $username, 'language');
$lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];

$location = get_location();

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

if(sqimap_capability($imapConnection, 'ACL') == false ) {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $string = "<b><font color=$color[2]>\n" .
		  'IMAP server does not support the ACL capability, sorry.';
                "</b></font>\n";
        error_box($string,$color);
	sqimap_logout($imapConnection);
	exit;
}

$prev = bindtextdomain ('useracl', SM_PATH . 'plugins/useracl/locale');
textdomain ('useracl');

/*  ----- Handle POST Actions -----  */

if( isset($_POST['update_all']) || isset($_POST['update']) || isset($adduser) || $show_ldap_cn == true) {
	if($check_user_method == 'ldap') {
		/* Initialize ldap handle */
		if(!($ldap=ldap_connect($ldap_server[$ldap_server_no]['host'], $ldap_server[$ldap_server_no]['port']))) {
			print "Could not connect to LDAP server.";
		}
		if (isset($ldap_server[$ldap_server_no]['binddn'])) {
			if (!ldap_bind($ldap, $ldap_server[$ldap_server_no]['binddn'], $ldap_server[$ldap_server_no]['bindpw'])) {
				print "Could not bind to LDAP server.";
			}
		}
	}
}


if(isset($_POST['update_all'])) {
	/* Update All */
	$all_users = $_POST['all_users'];

	for($i=0;$i<count($all_users);$i++){
		$user = $all_users[$i];
		$permission = $_POST['perm_radio'][$user];
		if($perm_prev[$user] != $permission) {
			$re = set_permission($imapConnection, $mbox, $user, $permission);
			if($notify) {
				$notify_users[] = array('user' => $user, 'permission' => $permission, 'mailbox' => $mbox, 'type' => 'change');
			}
		}
	}

	if(isset($re)) {
		if($re) {
			$successmsg[] = sprintf( _("Successfully changed permissions for folder %s"), imap_utf7_decode_local($mbox));
		} else {
			$errormsg[] = sprintf( _("Failed to change permissions for folder %s"), imap_utf7_decode_local($mbox));
		}
	} elseif(!isset($_POST['newuser'])) {
		$errormsg[] = sprintf (_("No changes in permissions for folder %s"), imap_utf7_decode_local($mbox));
	}


} elseif(isset($_POST['update'])) {
	/* Update Single */
	$user_sent = array_keys($_POST['update']);
	$user = $user_sent[0];
	$permission = $_POST['perm_radio'][$user];
	
	if($perm_prev[$user] != $permission) {
		$re = set_permission($imapConnection, $mbox, $user, $permission);
		if($notify) {
			$notify_users[] = array('user' => $user, 'permission' => $permission, 'mailbox' => $mbox, 'type'=>'change');
		}
	}

	if(isset($re)) {
		if($re) {
			$successmsg[] = sprintf( _("Successfully changed permissions for folder %s"), imap_utf7_decode_local($mbox));
		} else {
			$errormsg[] = sprintf( _("Failed to change permissions for folder %s"), imap_utf7_decode_local($mbox));
		}
	} else {
		$errormsg[] = sprintf (_("No changes in permissions for folder %s"), imap_utf7_decode_local($mbox));
	}
		

} elseif(isset($_POST['delete_checked'])) {
	/* Delete Checked Permissions */
	/* FIXME */
	$delete_checked = $_POST['delete_checked'];

	for($i=0;$i<count($delete_checked);$i++){
		$user = $delete_checked[$i];
		if( sqimap_deleteacl($imapConnection, urldecode($mbox), urldecode($user))) {
			$successmsg[] = sprintf( _("Successfully changed permissions for folder %s"), imap_utf7_decode_local($mbox));
		} else {
			$errormsg[] = sprintf( _("Failed to change permissions for folder %s"), imap_utf7_decode_local($mbox));
		}
	}

}

if(isset($adduser) || (isset($_POST['update_all']) && isset($_POST['newuser']))) {

	/* Add New User */
	$user = trim($_POST['newuser']);
	if(strlen($user)) {
		$permission = $_POST['new_user_perm_radio'];
	
		if($user == $username) {
			/* Myself */
			$errormsg[] = _("Cannot modify permissions on your own folder; you will always have full rights on your folders.");

		} elseif(isset($perm_prev[$user]) && $perm_prev[$user] == $permission) {
			/* Same user, same permission */
			$neutralmsg[] = sprintf(_("User %s already has %s permission for folder %s."),
			  $user, _(ucfirst($permission), imap_utf7_decode_local($mbox)));
		
		} elseif(isset($perm_prev[$user]) && $perm_prev[$user] != $permission) {
			/* Same user, changed permission! */
			if($ret = set_permission($imapConnection, $mbox, $user, $permission)) {
				$successmsg[] = sprintf( _("User %s has been given %s permisssion for folder %s."),
				  $user, _(ucfirst($permission)), imap_utf7_decode_local($mbox));
				$notify_users[] = array('user' => $user, 'permission' => $permission, 'mailbox' => $mbox, 'type'=>'change');
			} else {
				$errormsg[] = sprintf( _("Failed to give user %s %s permission for folder %s."),
				  $user, _(ucfirst($permission)), imap_utf7_decode_local($mbox));
			}

		} elseif($ret = set_permission($imapConnection, $mbox, $user, $permission)) {
			/* Successfully added acl for user */
			$successmsg[] = sprintf( _("User %s has been given %s permisssion for folder %s."),
			  $user, _(ucfirst($permission)), imap_utf7_decode_local($mbox));
			$notify_users[] = array('user' => $user, 'permission' => $permission, 'mailbox' => $mbox, 'type'=>'new');

		} else {
			/* User does not exist */
			$errormsg[] = sprintf( _("Failed to give user %s %s permission for folder %s."),
			  $user, _(ucfirst($permission)), imap_utf7_decode_local($mbox));
			$errormsg[] = _("Specified user does not exist.");
		}
	}
}


/* --- Main --- */




/* ------------- Business Logic ---------  */

$boxes = sqimap_mailbox_list($imapConnection);


for ($boxnum = 0; $boxnum < count($boxes); $boxnum++) {
	$mbox = $boxes[$boxnum]['unformatted'];

	if(!strstr($mbox, 'INBOX') ||
	    $mbox == 'INBOX'  ||
	    in_array('noselect', $boxes[$boxnum]['flags'])) {
		unset($boxes[$boxnum]);
	}
}

// print_r($boxes);

$boxes = array_values($boxes);

//print "<b>reorderred</b>";
//print_r($boxes);
	    

$usernames = array(); /* Array to gather all usernames together */

foreach($boxes as $no=>$box) {

	$mbox = $box['unformatted'];

	/* Get ACLs only for subfolders of INBOX */
	/* FIXME: Alternate namespace */
	if(strstr($mbox, 'INBOX') && $mbox != 'INBOX' ) {
		sqimap_getacl($imapConnection, $mbox, $out);
		if(array_key_exists($username, $out)) {
			unset($out[$username]);
		}
		$perm[$mbox] = $out;
	}
}
$perm_array = create_human_readable_permarray($perm);
sqimap_logout($imapConnection);


if(isset($ldap)) {
	/* We require directory plugin */
	require_once(SM_PATH . 'plugins/directory/functions.php');
	/* Ask user full names so as to display nicely */
	foreach($perm as $mbox=>$pe) {
		$usernames = array_merge($usernames, array_keys($pe));
	}

	$usernames=array_unique($usernames);
	$usernames=array_values($usernames);

	if (!isset($ldap)) {
		if(!($ldap=ldap_connect($ldap_server[$ldap_server_no]['host'], $ldap_server[$ldap_server_no]['port']))) {
			print "Could not connect to LDAP server.";
		}
		if (isset($ldap_server[$ldap_server_no]['binddn'])) {
			if (!ldap_bind($ldap, $ldap_server[$ldap_server_no]['binddn'], $ldap_server[$ldap_server_no]['bindpw'])) {
				print "Could not bind to LDAP server.";
			}
		}
	}
	
	$filter = '(|(uid=';
	$filter .= implode(')(uid=', $usernames);
	$filter .= '))';

	if($show_ldap_cn == true) {
		$attributes = array('cn', 'uid', 'mail');
	} else {
		$attributes = array('uid', 'mail');
	}

	if (!($sr = ldap_search($ldap, $ldap_server[$ldap_server_no]['base'], $filter, $attributes))) {
		print "Could not search for usernames.";
	}
	$entries = ldap_get_entries ($ldap, $sr);

	$names = array();
	$mails = array();

	for($i=0; $i<$entries['count']; $i++) {

		if($show_ldap_cn == true) {
			if(isset($entries[$i]['cn;lang-'.$lang][0])) {
				$names[$entries[$i]['uid'][0]] =
				    directory_string_convert($entries[$i]['cn;lang-'.$lang][0],
				    $ldap_server[$ldap_server_no]['charset'], $charset);
			} elseif(isset($entries[$i]['cn'][0])) {
				$names[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
			}
		}

		if(isset($entries[$i]['mail'][0])) {
			$mails[$entries[$i]['uid'][0]] = $entries[$i]['mail'][0];
		}
	}
	ldap_close($ldap);
}
		

/* --- Mail Notification --- */

/**
 * If there is mail notification to send, display the reminder/notice.
 */
if(isset($notify_users) && $notify) {

	require_once(SM_PATH . 'class/deliver/Deliver.class.php');
	require_once(SM_PATH . 'functions/mime.php');
	require_once(SM_PATH . 'functions/identity.php');

	/*
	print "<pre>";
	print_r($notify_users);
	// $idents = get_identities();
	// print_r($idents);
	// if(sizeof($idents) == 1 ) {
	// 	print "one identity";
	// }
	print "</pre>";
	*/
	
	foreach($notify_users as $n=>$info) {
		// print "<br><b>Notify ". $no['user'].", ".$no['permission'].", ".$no['mailbox']."</b><br>";
		
		$notifyMessage = new Message();
		$rfc822_header = new Rfc822Header();
		$notifyMessage->rfc822_header = $rfc822_header;
		$notifyMessage->reply_rfc822_header = '';

		$body = useracl_prepare_notify_message($info);
		if(isset($mails)) {
			/* uids=>mails, taken from LDAP */
			$send_to = $mails[$info['user']];
		} else {
			/* Mail in the form of: userid@domain. Domain is the
			 * default domain taken from config/config.php. */
			$send_to = $info['user']. $domain;
		}

		/*

		if($check_user_method == 'ldap') {
			if (!isset($ldap)) {
				if(!($ldap=ldap_connect($ldap_server[$ldap_server_no]['host'], $ldap_server[$ldap_server_no]['port']))) {
				print "Could not connect to LDAP server.";
			}
			ldap_search
			*/
			
		if($info['type'] == 'change' && $info['permission'] == 'none') {
			/* 'remove' */
			$subject = sprintf("Notification for removal of access to shared folder, by user: %s, Folder: %s",
			  $username, imap_utf7_decode_local($info['mailbox']));
		} elseif($info['type'] == 'change') {
			/* 'change' */
			$subject = sprintf("Notification for changes in access to shared folder, by user: %s, Folder: %s",
			  $username, imap_utf7_decode_local($info['mailbox']));
		} else {
			/* 'new' */
			$subject = sprintf("Notification for new shared folder by user: %s, folder: %s",
			  $username, imap_utf7_decode_local($info['mailbox']));
		}	 
        
		$Result = deliverMessage($notifyMessage);
        	if (! $Result) {
			$failnotify[] = $info;

		} else {
			$successnotify[] = $info;
		}

		unset($notifyMessage);
	}

	if(isset($failnotify)) {
		foreach($failnotify as $n=>$info) {
			if(isset($names[$info['user']])) {
				$info['printname'] = $info['user'] . ' ('.$names[$info['user']].')';
			} else {
				$info['printname'] = $info['user'];
			}
		}
		
		if(sizeof($failnotify) == 1) {
			$errormsg[] = sprintf( _("Failed to send notification message to user %s"), $info['printname']);
		} elseif(sizeof($failnotify) > 1) {
			foreach($failnotify as $n=>$info) {
				$printnames[] = $info['printname'];
			}
			$errormsg[] = sprintf( _("Failed to send notification message to users: %s"), implode(', ',$printnames));
		}
	}


	if(isset($successnotify)) {
		foreach($successnotify as $n=>$info) {
			if(isset($names[$info['user']])) {
				$info['printname'] = $info['user'] . ' ('.$names[$info['user']].')';
				$successprint[] = $info['user'] . ' ('.$names[$info['user']].')';
			} else {
				$info['printname'] = $info['user'];
				$successprint[] = $info['user'];
			}
		}
		
		if(sizeof($successprint) == 1) {
			$successmsg[] = sprintf( _("Successfully sent notification message to user %s"), $successprint[0]);
		} elseif(sizeof($successprint) > 1) {
			$successmsg[] = sprintf( _("Successfully sent notification message to users: %s"), implode(', ',$successprint));
		}
	}


	/*
	echo '<form method="post" action="../../src/compose.php">';

	echo '<br />'
	. '<input type="hidden" name="send_to" value="' . htmlspecialchars($send_to) . '">'
	. '<input type="hidden" name="subject" value="' . htmlspecialchars($subject) . '">'
	. '<input type="hidden" name="body" value="' . htmlspecialchars($body) . '">'
	. '<input type="hidden" name="mailbox" value="' . htmlspecialchars($mailbox) . '">'
	. '<input type="submit" name="send" value="' . _("Send Mail") . '"><br /><br /></center>'
	. '</form>'
	. '</td></tr></table></p></body></html>';
	*/
}



/* ------------- Presentation Logic ---------  */

$js = <<<ECHO
<script language="JavaScript" type="text/javascript">
function SetChecked(val) {
	dml=document.usersList;
	len = dml.elements.length;
	var i=0;
	for( i=0 ; i<len ; i++) {
		if (dml.elements[i].name.substring(0,14)=='delete_checked') {
			dml.elements[i].checked=val;
		}
	}
}
</script>
ECHO;

// print "<pre>"; print_r($_POST); print "</pre>";

if ($compose_new_win == '1' && isset($_GET['addacl'])) {
	displayHtmlHeader(_("Add New User Permission"), '', false);
} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');

	displayPageHeader($color, '');

	$prev = bindtextdomain ('useracl', SM_PATH . 'plugins/useracl/locale');
	textdomain ('useracl');
}

// echo $js;

/*
print '<pre>';
print_r($_POST);
print '</pre>';
*/

$prev = bindtextdomain ('useracl', SM_PATH . 'plugins/useracl/locale');
textdomain ('useracl');

if(isset($successmsg)) {
	print '<div style="color:green;font-size:1.2em;text-align:center">';
	foreach($successmsg as $msg) {
		print $msg . '<br />';
	}
	print '</div>';
}

if(isset($neutralmsg)) {
	print '<div style="font-size:1.1em;text-align:center">';
	foreach($neutralmsg as $msg) {
		print $msg . '<br />';
	}
	print '</div>';
}
if(isset($errormsg)) {
	print '<div style="color:red;font-size:1.2em;text-align:center">';
	foreach($errormsg as $msg) {
		print $msg . '<br />';
	}
	print '</div>';
}


useracl_html_printheader( _("User Permissions") );
useracl_html_print_all_sections_start();

foreach($boxes as $no=>$box) {

	$mbox = $box['unformatted'];

	if (isset($_GET['addacl'])) {
		if($mbox != $selected_mbox) {
			continue;
		}
	}

	if(strstr($mbox, 'INBOX') && $mbox != 'INBOX' && 
	   (isset($perm_array[$mbox]) || isset($addacl))) {

	   	$formname = "form_".str_replace(array('.','%', '-') , array('_','_','_'),
			rawurlencode($mbox));

		useracl_html_print_section_start( sprintf( _("Current Permissions for Folder: %s") , 
		'<strong>'.imap_utf7_decode_local($mbox).'</strong>'));

		print '<form name="'.$formname.'" action="useracl.php?mailbox='.rawurlencode($mbox);
		if(isset($_GET['addacl'])) {
			print '&amp;addacl=1';
		}
		print '" method="post">';

		print '<input type="hidden" name="mbox" value="'.urlencode($mbox).'" />';
		print '<table width="100%" border="0" >';
		useracl_print_table_header();
		if(isset($perm_array[$mbox]))
			useracl_print_array($perm_array[$mbox], $mbox);
		else
			useracl_print_array(array(), $mbox);
		useracl_print_addnew($formname);
		useracl_print_table_footer();
		print '</table>';
		print '</form>';
		useracl_html_print_section_end();

		unset($formname);
	}
}



if( ( isset($_GET['addacl']) && !isset($selected_mbox) ) ||
    ( !isset($_GET['addacl']) ) ){

	useracl_html_print_section_start( _("Add New User Permission") );

	print '<form name="form_addnew" action="useracl.php';
	if(isset($_GET['addacl'])) {
		print '?addacl=1';
	}
	print '" method="post">';
	print '<table width="100%" border="0" >';
	useracl_print_table_header(true);
	useracl_print_addnew_separate();
	print '</table>';
	print '</form>';
	
	useracl_html_print_section_end();
}


useracl_html_print_all_sections_end();


$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');
if ($compose_new_win == '1' && isset($_GET['addacl'])) {
	print '<INPUT TYPE="BUTTON" NAME="Close" onClick="return self.close()" VALUE='._("Close").'></TD></TR>'."\n";
}


useracl_html_printfooter();


?>
