<?php
/**
 * ldap_prefs.php
 *
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@noc.uoa.gr>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions for manipulating user preferences
 * stored in an LDAP database.
 *
 * $Id: ldap_prefs.php,v 1.1.1.1 2004/01/02 16:07:15 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

/**
 * Includes
 */
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

	if(isset($_SESSION['ldap_prefs_cache'])) {

		$ldap_prefs_cache = $_SESSION['ldap_prefs_cache'];

		if ($string == 'namepreferred') {
			/* Chosen name (based on available 'cn's from LDAP */
			if(isset($ldap_prefs_cache['namepreferred'])) {
				return $ldap_prefs_cache['namepreferred'];
			} else {
				return $ldap_prefs_cache['full_name'];
			}
			return $ldap_prefs_cache['full_name'];

		} elseif ($string == 'email_address') {
			/* Default email address */
			return $ldap_prefs_cache['email_address'];

		} elseif (strstr($string, 'email_address')) {
			/* Email - specific identity */
			$identity_no = substr(strrchr($string, "email_address"), -1);
			if (isset($_SESSION['identities_map'][$identity_no])) {
				$identity = $_SESSION['identities_map'][$identity_no];
				return $_SESSION['identities'][$identity]['email_address'];
			}

		} elseif (strstr($string, 'full_name')) {
			/* Full name - specific identity */
			$identity_no = substr(strrchr($string, "full_name"), -1);
			if (isset($ldap_prefs_cache['identities_map'][$identity_no])) {
				$identity = $_SESSION['identities_map'][$identity_no];
				if(isset($_SESSION['identities'][$identity]['full_name'])) {
					return $_SESSION['identities'][$identity]['full_name'];
				} elseif(isset($ldap_prefs_cache['namepreferred'])) {
					return $ldap_prefs_cache['namepreferred'];
				} else {
					return $ldap_prefs_cache['full_name'];
				}
			/*
			} elseif(isset($ldap_prefs_cache['namepreferred'])) {
				return $ldap_prefs_cache['namepreferred'];
				*/
			} else {
				return $ldap_prefs_cache['full_name'];
			}

		/* Has to return the _number_ of the current identity */
		/*
		} elseif ($string == 'identity') {
			 if(isset($ldap_prefs_cache['identity'])) {
				if(is_numeric($ldap_prefs_cache['identity'])) {
			 		foreach($_SESSION['identities'] as $no=>$id) {
						if($ldap_prefs_cache['identity'] == $no) {
							return $no;
						}
					}
				} else {
					if (isset($_SESSION['identities_map'])) {
						$identity = $_SESSION['identities_map'][$identity_no];
						return $_SESSION['identities'][$identity]['email_address'];
					}
				}
			} else {
				return 0;
			}
			*/

		} elseif ($string == 'identities') {
			/* For compatibility with compose.php: return the
			 * number of available LDAP identities. */
			if(isset($_SESSION['identities'])) {
				return (sizeof($_SESSION['identities']) + 1);
			} else {
				return 1;
			}

		} elseif(isset($ldap_prefs_cache[$string])) {
			/* Pref is set by user. */
			// print $ldap_prefs_cache[$string] . '<BR> ' ;
			return $ldap_prefs_cache[$string];

		} elseif (isset($prefs_default[$string])) {
			/* Return default system setting. */
			// print  $prefs_default[$string]. ' (default) <BR>';
			return $prefs_default[$string];
		}

	} else {
		/* No cache. Something is seriously broken. Should never end up here. */
		// print_r($_SESSION);
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

	if ($string == 'full_name' || $string == 'mail' || $string == 'imapServerAddress' || $string == 'dn') {
		print "<strong>Error: I was told to change something I shouldn't ($string).</strong><br />";
		return;
	}

	if(isset($_SESSION['ldap_prefs_cache'])) {
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

?>
