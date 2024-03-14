<?php
/**
 * functions.php
 *
 * Functions related to the business logic of the plugin
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2006 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
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

		case 'sql':
			require_once(SM_PATH . 'plugins/useracl/sql.php');
			$add = sql_search($user);
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

/**
 * Convert character set of a string.
 *
 * This is used for stuff retrieved by LDAP. (Common names etc.)
 *
 * @param string $string String to convert.
 * @param string $from_charset Original charset.
 * @param string $to_charset Destination charset.
 * @return string Converted string.
 */
function useracl_string_convert($string, $from_charset, $to_charset) {
    
    if(strcasecmp($from_charset, $to_charset) == 0 ) {
        return $string;
    }

    if(function_exists("mb_convert_encoding")) {
        return mb_convert_encoding($string, $to_charset, $from_charset);

    } elseif(function_exists("recode_string")) {
        return recode_string("$from_charset..$to_charset", $string);
    
    } elseif(function_exists("iconv")) {
        return iconv($from_charset, $to_charset, $string);

    } else {
        return $string;
    }
}    


/**
 * Check if given mailbox is a folder that does not belong to a user, but is a
 * global shared folder.
 *
 * @param string $mbox
 * @return boolean
 */
function is_shared_folder($mbox) {
    global $delimiter;
    if(empty($delimiter)) {
        include_once(SM_PATH . 'functions/imap_general.php');
        $delimiter = sqimap_get_delimiter();
    }

    sqgetGlobalVar('sqimap_namespace', $sqimap_namespace, SQ_SESSION);
    if(isset($sqimap_namespace) && !empty($sqimap_namespace)) {
        /* Namespace - enabled logic */
        foreach($sqimap_namespace['shared'] as $no=>$ns) {
            if($ns['prefix'] == '') {
                /* In this case, we just want to check that we are NOT in
                 * some other namespace. */
                if(!is_user_folder($mbox)) {
                    return true;
                    /* TODO: */
                    if(!is_personal_folder($mbox)) {
                        return true;
                    }
                }
            }
            //print "debug: ". substr($mbox, 0, sizeof($ns['prefix'])) . " vs ". $ns['prefix'];
            if(substr($mbox, 0, sizeof($ns['prefix'])) == $ns['prefix']) {
                    syslog(LOG_DEBUG, "true!");
                return true;
            }
        }
        return false;

    } else {
        /* Old way to check, deprecated. Only applicable for Cyrus normal 
         * namespace */
        $parts = explode($delimiter, $mbox, 2);
        if(strtolower($parts[0]) != 'user' && strtolower($parts[0]) != 'inbox') {
            return true;
        }
        return false;
    }
}

/**
 * Check if given mailbox is a folder that belongs to a user.
 * @param string $mbox
 * @return boolean
 * @todo add namespace logic
 */
function is_user_folder($mbox) {
    global $delimiter;
    if(empty($delimiter)) {
        include_once(SM_PATH . 'functions/imap_general.php');
        $delimiter = sqimap_get_delimiter();
    }
    $parts = explode($delimiter, $mbox, 3);
    if($parts[0] == 'user') {
        return true;
    }
    return false;

    /* TODO */
    sqgetGlobalVar('sqimap_namespace', $sqimap_namespace, SQ_SESSION);
    if(isset($sqimap_namespace) && !empty($sqimap_namespace)) {
        /* Namespace - enabled logic */
        foreach($sqimap_namespace['shared'] as $no=>$ns) {
        }
    }
}

/**
 * Check if given mailbox is a folder that belongs to me, i.e. personal namespace.
 *
 * @param string $mbox
 * @return boolean
 * @todo add namespace logic
 */
function is_personal_folder($mbox) {
    global $delimiter;
    if(empty($delimiter)) {
        include_once(SM_PATH . 'functions/imap_general.php');
        $delimiter = sqimap_get_delimiter();
    }
    $parts = explode($delimiter, $mbox);
    if($parts[0] == 'INBOX') {
        return true;
    }
    if(!is_user_folder($mbox) && !is_shared_folder($mbox)) {
        return true;
    }
    return false;
}


/**
 * Gather all mailboxes to be displayed to the UI.
 * This would be a normal sqimap_mailbox_list() call, but we also need to
 * remove all folders that do not make sense to present:
 * 1) The folders where we do not have the 'a' flag, thus cannot change
 *    ACLs anyway.
 * 2) Honor the $useracl_only_inbox config flag
 *
 * @param object $imapConnection
 * @return array
 */
function useracl_mailbox_list($imapConnection) {
    global $useracl_only_inbox;

    /* Build folder struct */
    $boxes = sqimap_mailbox_list($imapConnection);

    /* 1) Remove folders that are not subfolders of INBOX, if only_inbox == true.
     * At the same time remove \NoSelect folders. */
    $boxcount = count($boxes);
    for ($boxnum = 0; $boxnum < $boxcount; $boxnum++) {
        $mb = $boxes[$boxnum]['unformatted'];
           
        if( ( ($useracl_only_inbox == true) &&
              (!strstr($mb, 'INBOX') || $mb == 'INBOX') )
              ||
            in_array('noselect', $boxes[$boxnum]['flags'])) {

            unset($boxes[$boxnum]);
        }
    }

    /* 2) For the remaining folders: remove the ones where we do not have admin
     * access. */
    $boxes = array_values($boxes);
    $boxcount = count($boxes);
    for ($boxnum = 0; $boxnum < $boxcount; $boxnum++) {
        $mb = $boxes[$boxnum]['unformatted'];
        if(!strstr(sqimap_myrights($imapConnection, $mb), 'a')) {
            unset($boxes[$boxnum]);
        }
    }

    $boxes = array_values($boxes);
    return $boxes;
}


