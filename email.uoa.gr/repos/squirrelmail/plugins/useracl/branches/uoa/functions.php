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
		// $perm_array2[$mbox] = array();

		foreach($permarr as $user=>$perm ){
			switch (trim($perm)){
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
 * @param string $permission Permission in human-readable form
 * @global string $check_user_method What method to use, to check if user
 * exists
 * @return int >0 on success; 0 if user does not exist
 */
function set_permission($imap_stream, $mbox, $user, $permission){
	
	global $check_user_method;
	switch($permission){
		case 'none':{
			$acl = '';
			break;
		}
		case 'read':{
			$acl = 'lrs';
			break;
		}
		case 'append':{
			$acl = 'lrswip';
			break;
		}
		case 'delete':{
			$acl = 'lrswipd';
			break;
		}
	}

	// if($permission != 'none') {
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
	// }
	
	if($add == 1) {
		return sqimap_setacl($imap_stream, $mbox, $user, $acl);
	}
	return $add;
}
?>
