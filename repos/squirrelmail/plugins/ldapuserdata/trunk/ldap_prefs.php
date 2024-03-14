<?php
/**
 * ldap_prefs.php
 *
 * Copyright (c) 1999-2009 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@noc.uoa.gr>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions for manipulating user preferences
 * stored in an LDAP database.
 *
 * $Id: ldap_prefs.php,v 1.7 2007/06/14 12:05:20 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

/** Includes */
require(SM_PATH . 'plugins/ldapuserdata/config.php');

sqsession_is_active();

/**
 * Renumber Highlight List
 *
 * When a highlight option is deleted the preferences module must renumber the
 * list.
 */
function renumberHighlightList() {
    $j = 0;
    for($i = 0; $i < 10 ; $i++) {
        $highlightarray[$i] = $_SESSION['ldap_prefs_cache']['highlight'.$i];
        if($highlightarray[$i]) {
            $newarray[$j] = $highlightarray[$i];
            $j++;
        }
    }
    for($i = 0; $i < 10 ; $i++) {
        $_SESSION['ldap_prefs_cache']['highlight'.$i] = $newarray[$i];
    }
    return true;
}

/**
 * Return the value for the requested preferences item
 *
 * @param string $data_dir Dummy Variable for the data directory store.
 * @param string $username Username Logged in.
 * @param string $string Name of requested preferences item.
 * @param string $default
 * @return string
 */
function getPref($data_dir, $username, $string, $default = '') {

    global $prefs_default;
    sqgetGlobalVar('identities_map', $identities_map, SQ_SESSION);
    sqgetGlobalVar('ldapidentities', $ldapidentities, SQ_SESSION);
    sqgetGlobalVar('alternatenames', $alternatenames, SQ_SESSION);
    sqgetGlobalVar('ldap_prefs_cache', $ldap_prefs_cache, SQ_SESSION);

    if(!isset($ldap_prefs_cache)) {
        /* No cache. Something is seriously broken. Should never end up here. */
        return false;
    }

    if ($string == 'email_address') {
        /* Default email address */
        return $ldap_prefs_cache['email_address'];

    } elseif (strstr($string, 'email_address')) {
        /* Email - specific identity */
        $identity_no = str_replace('email_address', '', $string);
        if (isset($identities_map[$identity_no])) {
            $identity = $identities_map[$identity_no];
            if(isset($ldapidentities[$identity])) {
                return $identity;
            } elseif(in_array($identity, $ldapidentities['main']['email_address'])) {
                return $identity;
            }
        }

    } elseif ($string == 'full_name' || strstr($string, 'full_name') ) {
        $identity_no = substr(strrchr($string, "full_name"), strlen('full_name'));
        
        /* Specific identity */
        if ($string != 'full_name' && isset($identities_map[$identity_no])) {
            $identity = $identities_map[$identity_no];

            if(isset($ldapidentities[$identity])) {
                /* a mailauthorizedaddress identity: */
                if(isset($ldap_prefs_cache['namepreferred']) &&
                   in_array($ldap_prefs_cache['namepreferred'],$ldapidentities[$identity]['allowed_names'])) {
                    return $ldap_prefs_cache['namepreferred'];
                } else {
                    return $ldapidentities[$identity]['allowed_names'][0];
                }
            } else {
                /* a mail/mailalternateaddress identity: */
                if(isset($ldap_prefs_cache['namepreferred']) &&
                   in_array($ldap_prefs_cache['namepreferred'], $ldapidentities['main']['allowed_names'])) {
                    return $ldap_prefs_cache['namepreferred'];
                } else {
                    return $ldap_prefs_cache['full_name'];
                }
            }

        /* No identity - assuming default stuff. */
        } else {
            if(isset($ldap_prefs_cache['namepreferred'])
               && in_array($ldap_prefs_cache['namepreferred'], $ldapidentities['main']['allowed_names']) 
               ) {
                return $ldap_prefs_cache['namepreferred'];
            } else {
                return $ldap_prefs_cache['full_name'];
            }
        }

    } elseif ($string == 'namepreferred') {
            if(isset($ldap_prefs_cache['namepreferred']) 
                && isset($alternatenames)
                && in_array($ldap_prefs_cache['namepreferred'], $alternatenames) ) {
                return $ldap_prefs_cache['namepreferred'];
            } else {
                return $ldap_prefs_cache['full_name'];
            }

    /* Has to return the _number_ of the current identity */
    } elseif ($string == 'identity') {

         if(isset($ldap_prefs_cache['mailpreferred'])) {
             foreach($identities_map as $no=>$ml) {
                if($ldap_prefs_cache['mailpreferred'] == $ml) {
                    return $no;
                }
            }
        } else {
            return 0;
        }

    } elseif ($string == 'identities') {
        /* For compatibility with compose.php: return the
         * number of available LDAP identities. */
        if(isset($identities_map)) {
            return (sizeof($identities_map)+1);
        } else {
            return 1;
        }

    } elseif (strstr($string, 'reply_to')) {
        if(isset($ldap_prefs_cache['reply_to'])) {
            return $ldap_prefs_cache['reply_to'];
        }

    } elseif(isset($ldap_prefs_cache[$string])) {
        /* Pref is set by user. */
        return $ldap_prefs_cache[$string];

    } elseif (isset($prefs_default[$string])) {
        /* Return default system setting. */
        return $prefs_default[$string];
    }
}

/**
 * Set the requested preferences item to the value provided.
 *
 * @param string $data_dir Dummy Variable for the data directory store.
 * @param string $username Username Logged in.
 * @param string $string Name of requested preferences item.
 * @param string $set_to Requested Value to set preferences item to.
 * @return int Always 1.
 */
function setPref($data_dir, $username, $string, $set_to = '') {
    if($string == 'security_tokens') {
        $_SESSION['ldap_prefs_cache']['security_tokens'] = $set_to;
        return;
    }

    if ($string == 'full_name' || $string == 'mail' || $string == 'imapServerAddress' || $string == 'dn') {
        print "<strong>Error: I was told to change something I shouldn't ($string).</strong><br />";
        return;
    }
    
    if($string=='email_address') {
        $string = 'mailpreferred';
    }

    if(isset($_SESSION['ldap_prefs_cache'][$string]) && ($_SESSION['ldap_prefs_cache'][$string] == $set_to)) {
        return;
    } else {
        $_SESSION['ldap_prefs_cache'][$string] = $set_to;
        if($string == 'trash_folder') {
            if ($set_to != 'none' ) {
                $_SESSION['ldap_prefs_cache']['move_to_trash'] = 1;
            } else {
                $_SESSION['ldap_prefs_cache']['move_to_trash'] = 0;
            }
        }
        if($string == 'sent_folder') {
            if ($set_to != 'none' ) {
                $_SESSION['ldap_prefs_cache']['move_to_sent'] = 1;
            } else {
                $_SESSION['ldap_prefs_cache']['move_to_sent'] = 0;
            }
        }
        if($string == 'draft_folder') {
            if ($set_to != 'none' ) {
                $_SESSION['ldap_prefs_cache']['save_as_draft'] = 1;
            } else {
                $_SESSION['ldap_prefs_cache']['save_as_draft'] = 0;
            }
        }
        return;
    }
    return;
}

/**
 * Remove the requested preferences item altogether.
 *
 * @param string $data_dir Dummy Variable for the data directory store.
 * @param string $username Username Logged in.
 * @param string $string Name of requested preferences item.
 * @return int 1
 */
function removePref($data_dir, $username, $string) {

    setPref($data_dir, $username, $string, '');
    if(substr($string, 0,9) == 'highlight') {
        if (!(renumberHighlightList())) {
            print "Error while renumbering...";
        }
    }
    return;
}

/**
 * Write the Signature.
 * @param string $data_dir Dummy Variable for the data directory store.
 * @param string $username Username Logged in.
 * @return int
 */
function setSig($data_dir, $username, $number, $value) {
    return setPref($data_dir, $username, 'signature', $value);
}

/**
 * Read the signature.
 * @param string $data_dir Dummy Variable for the data directory store.
 * @param string $username Username Logged in.
 * @return string
 */
function getSig($data_dir, $username, $number) {
    return getPref($data_dir, $username, 'signature', $number);
}

