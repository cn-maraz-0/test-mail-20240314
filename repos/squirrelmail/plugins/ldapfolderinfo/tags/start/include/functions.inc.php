<?php
/**
 * functions.php
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @author Robin Rainton <robin@rainton.com>
 * @package plugins
 * @subpackage ldapfolderinfo
 */

include_once(SM_PATH . 'plugins/useracl/imap_acl.php');
include_once(SM_PATH . 'plugins/directory/include/functions.php');

/**
 * Ask LDAP about folder information and save results in Session.
 *
 * @param array $boxes
 * @return array
 * @todo Use NAMESPACE
 */
function ldapfolderinfo_cache_ldap(&$boxes) {
    global $ldap_base_dn;
    $filteritemsuser = array();
    $filteritemsbb = array();
    
    for($i=0; $i<count($boxes); $i++) {
        if (!strstr($boxes[$i]['unformatted'], "INBOX")) {
            if(ereg("^user.",$boxes[$i]['unformatted'])) {
                $filteritemsuser[] = $boxes[$i]['unformatted'];
            } else {
                $filteritemsbb[] = $boxes[$i]['unformatted'];
            }
        }
    }

    if (sizeof($filteritemsbb) == 1 ) {
        $filterbb = "(&(ou=VirtUsersBB)(FolderName=$filteritemsbb[0]))";
    } elseif (sizeof($filteritemsbb) > 1) {
        $filterbb = "(&(ou=VirtUsersBB)(|";
        for($i=0; $i<sizeof($filteritemsbb); $i++) {
                $filterbb .= "(FolderName=$filteritemsbb[$i])";
        }
        $filterbb .= "))";
    }
    
    /* Make filter for user.* folders */
    if (sizeof($filteritemsuser) == 1 ) {
        $filteruser = "(&(ou=VirtUsers)(FolderName=$filteritemsuser[0]))";
    } elseif (sizeof($filteritemsuser) > 1) {
        $filteruser = "(&(ou=VirtUsers)(|";
        for($i=0; $i<sizeof($filteritemsuser); $i++) {
                $filteruser .= "(FolderName=$filteritemsuser[$i])";
        }
        $filteruser .= "))";
    }

    if(isset($filteruser) && isset($filterbb)) {
        $filter = "(|$filterbb"."$filteruser)";
    } elseif (isset($filteruser)) {
        $filter = $filteruser;
    } elseif (isset($filterbb)) {
        $filter = $filterbb;
    } else {
        $filter = false;
    }

    if($filter) {
        $ldap = ldapuserdata_ldap_connect('squirrel');
        $folderattributes = array('cn', 'mail', 'description', 'foldername'); /* We used to have owner here too */
    
        if (!($search_result = ldap_search($ldap, $ldap_base_dn, $filter, $folderattributes))) {
            print '<p align="center"><strong>' .
            _("No Folder Information Found") .
            '</strong></p>' ;
        }
        $info = ldap_get_entries($ldap, $search_result);
    
        $lang_iso = getPref($data_dir, $username, 'language');
        $lang = substr($lang_iso, 0, 2);
        
        for ($i=0; $i<$info['count']; $i++) {
            $folderinfo[$info[$i]['foldername'][0]]['mail'] = htmlspecialchars($info[$i]['mail'][0]);
    
            if(isset($info[$i]['description;lang-'.$lang]) ) {
                /* 1st shot: Localized description */
                if(function_exists("mb_convert_encoding")) {
                    $folderinfo[$info[$i]['foldername'][0]]['description'] = htmlspecialchars(
                    mb_convert_encoding($info[$i]['description;lang-'.$lang][0], $languages[$lang_iso]['CHARSET'], "UTF-8") );
                
                } elseif(function_exists("recode")) {
                    $folderinfo[$info[$i]['foldername'][0]]['description'] = htmlspecialchars(
                    recode("UTF-8..".$languages[$lang_iso]['CHARSET'] , $info[$i]['description;lang-'.$lang][0]));
    
                } elseif (function_exists("iconv")) { 
                    $folderinfo[$info[$i]['foldername'][0]]['description'] = htmlspecialchars(
                    iconv("UTF-8" , $languages[$lang_iso]['CHARSET'] , $info[$i]['description;lang-'.$lang][0]));
    
                } else {
                    $folderinfo[$info[$i]['foldername'][0]]['description'] = htmlspecialchars(
                    $info[$i]['description'][0]);
                }
            } elseif(isset( $info[$i]['description'][0])) {
                /* 2nd: Default description */
                $folderinfo[$info[$i]['foldername'][0]]['description'] = htmlspecialchars($info[$i]['description'][0]);
            } else {
                /* 3rd: A default description for all shared folders. */
                $folderinfo[$info[$i]['foldername'][0]]['description'] = _("Shared Folder");
            }
                
        }
        sqsession_register($folderinfo, 'ldapfolderinfo');
        return $folderinfo;
    }
}

/**
 * This function searches the tree from the node folder to the root folder to
 * search for an attribute
 *
 * This attribute may be one of the : owner, mailDomain, MaxDepth, MaxChild
 * This function provides a generic template for searching for any of the above
 * attributes for any type of BB folder or VirtUser Folder
 *
 * @author Christos Soulios (soulbros@noc.uoa.gr)
 * @param string $FolderName Name of Folder / Mailbox
 * @param string $attribute_name attribute to search for. Examples:
 *     * description
 *     * owner (returns: dn)
 *     * administrator (returns: dn)
 *     * allowmaildomain (returns: maildomains)
 *     * imappartition
 *     * maxquotaroot
 *     * mailhost
 *     * allowaplyto
 *     * maxdepth
 *     * maxchild
 *
 * @return mixed String or array.
 * @todo enable single-value return
 */
function ldapfolderinfo_get_folder_attribute($FolderName, $attribute_name) {
    // Find first set  of the sought attribute
    ldapfolderinfo_get_folder_parents($FolderName, $parents);
    
    global $ldap, $ldap_base_dn;
    
    for($i=0;$i<count($parents);$i++) {
        $search_folder_name=$parents[$i];
        $filter = "(&(FolderName=$search_folder_name)(objectClass=CyrusFolder))";
        $attributes[0] = $attribute_name;
        if(($result = ldap_search($ldap, $ldap_base_dn, $filter, $attributes))==FALSE) {
            //print "check_folder_attribute : Error while searching LDAP server";
            return false;
        }
    
        $entry = ldap_get_entries($ldap, $result);
        if(isset($entry[0][strtolower($attribute_name)][0])) {
            if($entry[0][strtolower($attribute_name)]['count'] == 1) {
                return $entry[0][strtolower($attribute_name)][0];
            } elseif ($entry[0][strtolower($attribute_name)]['count'] > 1) {
                for($j=0; $j<$entry[0][strtolower($attribute_name)]['count']; $j++) {
                    $ret[$j] = $entry[0][strtolower($attribute_name)][$j];
                }
                return $ret;
            } else {
                return '';
            }
        }
    }
}

/**
 * This function returns an array with the parents of the folder on the route
 * towards the root
 *
 * @param string $FolderName Name of Folder / Mailbox
 * @param array $parents array of results
 * @return int
 *
 * @author Christos Soulios <soulbros@noc.uoa.gr>
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 *
 * @todo use $delimiter
 * @todo use functions enabled for IMAP namespace
 */
function ldapfolderinfo_get_folder_parents($FolderName, &$parents) {
    global $delimiter;

    if(ereg("^user\.",$FolderName)) { // If folder is a VirtUser folder.
        $tokens = explode(".", $FolderName);
        array_shift($tokens);
        $tokens[0] = "user.".$tokens[0];
    } else {
        $tokens = explode(".", $FolderName);
    } // Now each $token cell contains each folder node in the folder tree
    //Find first set  of the sought attribute
    $count = 0;
    for($i=count($tokens) -1 ;$i>=0;$i--) {
        $parents[$count++] =implode(".", $tokens);
        unset($tokens[$i]); // Set search folder path one less than last one.
    }
    return 1;
}

/**
 * Get folder size.
 *
 * @param object $imap_stream
 * @param string $mailbox
 * @return int
 * @todo Add new way of getting folder size, via Annotations.
 * @todo Add compatible way of getting folder sizes
 */
function sqimap_get_used ($imap_stream, $mailbox) {
    return sqimap_avelused ($imap_stream, $mailbox);
}

/**
 * Issue an AVELUSED command to get folder size.
 *
 * @param object $imap_stream
 * @param string $mailbox
 * @return int
 */
function sqimap_avelused ($imap_stream, $mailbox) {
    fputs ($imap_stream, "a001 AVELUSED \"$mailbox\"\r\n");
    $read_ary = sqimap_read_data ($imap_stream, 'a001', false, $result, $message);

    for ($i = 0; $i < count($read_ary); $i++) {
        if (ereg("AVELUSED", $read_ary[$i])) {
            $size = ereg_replace("^.*[(](.*)[)].*$", "\\1", $read_ary[$i]);
            return $size;
        } else {
            return false;
        }
    }
}

/**
 * Get a more human-readable description of a user's folder
 *
 * @param $folder string
 * @return string
 */
function ldapfolderinfo_userfolder_to_humanreadable($folder) {
    global $base_uri, $plugins;

    $delimiter = sqimap_get_delimiter();
    $parts = explode($delimiter, $folder);
    if($parts[0] != 'user') {
        return $folder;
    }
    if(sizeof($parts) < 3) {
        return $folder;
    }

    $user = $parts[1];
    $folder_disp = '';
    for($i=2;$i<sizeof($parts); $i++) {
        $folder_disp .= $parts[$i];
        if(isset($parts[$i+1])) {
            $folder_disp .= $delimiter;
        }
    }
    
    $out = ldapfolderinfo_user_link($user);

    $out .= '</td><td>';
    $out .= $folder_disp;

    return $out;
}

/**
 * Link to Directory UserInfo (vcard.php) page, if directory plugin is enabled.
 * @param string $user Username.
 * @return string
 */
function ldapfolderinfo_user_link($user) {
    global $plugins, $base_uri;
    if(in_array('directory', $plugins)) {
        return directory_user_link($user);
    } else {
        return $user;
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
 * Check if given mailbox is a folder that belongs to a user.
 * @param string $mbox
 * @param array $return_parts In this array, the mailbox name will be splitted
 *     to its parts according to the IMAP delimiter.
 * @return boolean
 * @obsolete
 */
function is_user_folder_old($mbox, &$return_parts) {
    global $delimiter;
    $parts = explode($delimiter, $mbox, 3);
    if($parts[0] == 'user') {
        $return_parts['username'] = $parts[1];
        $return_parts['foldername'] = $parts[2];
        return true;
    }
    return false;
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
                return true;
            }
        }
        return false;

    } else {
        /* Old way to check, deprecated. Only applicable for Cyrus normal 
         * namespace */
        $parts = explode($delimiter, $mbox, 2);
        if($parts[0] != 'user' && strtolower($parts[0]) != 'inbox') {
            return true;
        }
        return false;
    }
}

