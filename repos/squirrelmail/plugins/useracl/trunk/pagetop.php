<?php
/**
 * useracl - pagetop.php
 *
 * Functions for the informational text displayed at the top of a page header.
 * (Messages List, Message Display).
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2008 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

/** Includes */
include_once(SM_PATH . 'plugins/useracl/html.php');

/**
 * Informational text in pagetop bar.
 * @return string
 */
function useracl_pagetop_do() {
    global $mailbox;

    sq_change_text_domain('useracl');

    sqgetGlobalVar('folderinfo', $folderinfo, SQ_SESSION);
    $location = get_location();
    $out = '';
    if(isset($mailbox)) {
        $acl_uri = 'plugins/useracl/useracl.php?mailbox='.urlencode($mailbox).'&amp;addacl=1';
        $disp = '<img src="'.$location.'/../plugins/useracl/images/open_folder_user.png" border="0" alt ="' . _("Add New User Permission") .'" title="'. _("Add New User Permission") .'" />';
        
        if(useracl_is_user_folder($mailbox, $folderinfo)) {
            $out .= ' &nbsp; ' . sprintf( ' &nbsp; ' . _("Shared by User: %s"), useracl_user_link($folderinfo['username']) );
        } elseif(strtolower($mailbox) != 'inbox' && !useracl_is_shared_folder($mailbox)) {
            $out .= ' &nbsp; ' . makeComposeLink($acl_uri, $disp);
        }
        $out .= "&nbsp;\n";
    }
    sq_change_text_domain('squirrelmail');
    return $out;
}

/**
 * Check if given mailbox is a folder that belongs to a user.
 * @param string $mbox
 * @param array $return_parts In this array, the mailbox name will be splitted
 *     to its parts according to the IMAP delimiter.
 * @return boolean
 */
function useracl_is_user_folder($mbox, &$return_parts) {
    global $delimiter;
    if(!isset($delimiter)) sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
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
function useracl_is_shared_folder($mbox) {
    global $delimiter;
    $parts = explode($delimiter, $mbox, 2);
    if($parts[0] != 'user' && strtolower($parts[0]) != 'inbox') {
        return true;
    }
    return false;
}

