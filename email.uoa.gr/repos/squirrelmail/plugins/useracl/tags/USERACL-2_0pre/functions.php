<?php
/**
 * functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: functions.php,v 1.5 2004/08/09 13:07:26 avel Exp $
 *
 * Functions related to the business logic of the plugin
 */

/**
 * Create an array of permissions with human-readable strings ('read',
 * 'append') instead of IMAP ACL strings ('lrsw', 'lrswip'
 *
 * @param array $perm_array
 * @return array
 *
 * @author Christos Soulios <soulbros@noc.uoa.gr>
 * @author Pavlos Drandakis <pdrados@noc.uoa.gr>
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 */
function create_human_readable_permarray($perm_array){
	$perm_array2=array();
	foreach($perm_array as $mbox=>$permarr ){
		if(sizeof($permarr) > 0) {
		$perm_array2[$mbox] = array();
		foreach($permarr as $user=>$perm ){
			switch (trim($perm)){
				case 'p':
					$perm_array2[$mbox][$user] = 'postonly';
					break;
				case 'lrs':
				case 'lrsw':
					$perm_array2[$mbox][$user] = 'read';
					break;
				case 'lrswip':
					$perm_array2[$mbox][$user] = 'append';
					break;
				case 'lrswipd':
					$perm_array2[$mbox][$user] = 'delete';
					break;
				default:
					break;
				
			}
		}
		}
	}
	return $perm_array2;
}

/**
 * Set permission (ACL) for user. Mainly a wrapper for sqimap_setacl().
 *
 * This function also does the check for existing user, according to the
 * plugin's configuration.
 *
 * @param object $imap_stream IMAP Connection stream
 * @param string $mbox Mailbox Name
 * @param string $user Username to give permission to
 * @param mixed $permission Permission in human-readable form. The parameter
 *  should be an array of permissions, or just a string with a single
 *  permission. 
 * @param string $perm Permission in IMAP ACL form ('lrsw'). Will use this in
 *  favour of $permission if it is non-empty.
 * @global string $check_user_method What method to use, to check if user
 * exists
 * @return int >0 on success; 0 if user does not exist
 */
function set_permission($imap_stream, $mbox, $user, $permission = '', $perm = ''){
	global $check_user_method, $acl;
	if(!empty($perm)) {
		$myacl = $perm;

	} elseif(!empty($permission)) {
		/* Human-readable name is really obsolete now. I don't use it
		 * in this plugin, but here is the function fixed to use the
		 * constant $acl.
		 * The parameter should be an array of permissions, or just a
		 * string with a single permission. 
		 */
		$myacl = '';
		if(is_array($permission)) {
			foreach($acl as $a=>$info) {
				if(isset($permission[$a])) {
					$myacl .= $info['acl'];
				}
			}
		} else {
			if(isset($acl[$permission])) {
				$myacl .= $acl[$permission]['acl'];
			}
		}
	} else {
		$myacl = '';
	}
	
	if ($user == 'anyone' || $user == 'anonymous') {
		$add = 1;
	} else {
		switch($check_user_method) {
		case 'mailbox':
			/* TODO */
			global $useracl_root_username, $useracl_root_password;
			$add = 1;
			break;

		case 'ldap':
			global $ldap, $ldap_server, $ldap_server_no;
			$filter = "(&(uid=$user)(objectclass=mailRecipient))";
			$attributes = array('uid', 'mail');
			if (!($sr = ldap_search($ldap, $ldap_server[$ldap_server_no]['base'],
			  $filter, $attributes))) {
				print "Could not search for username.";
			}
			$entries = ldap_get_entries ($ldap, $sr);
			if($entries['count'] > 0 ) {
				$add = 1;
			} else {
				$add = 0;
			}
			break;
		
		case 'none':
		default:
			$add = 1;
			break;
		}
	}
	
	if($add == 1) {
		return sqimap_setacl($imap_stream, $mbox, $user, $myacl);
	}
	return $add;
}
?>
