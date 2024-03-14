<?php
/**
 * ldapfolderinfo - pagetop.php
 *
 * Functions for the informational text displayed at the top of a page header.
 * (Messages List, Message Display).
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage ldapfolderinfo
 * @version $Id: pagetop.inc.php,v 1.3 2006/11/22 13:21:23 avel Exp $
 */

include_once(SM_PATH . 'plugins/ldapfolderinfo/include/functions.inc.php');

/**
 * Informational text in pagetop bar.
 * @return string
 */
function ldapfolderinfo_pagetop_do($string = '') {
    global $mailbox; 
    if(isset($mailbox)) {
        $mailboxparts = explode('.', $mailbox);

        $fn = '';
        for($i = 0; $i < sizeof($mailboxparts); $i++ ) {
            if ( $mailboxparts[$i] == 'INBOX' ) {
                $fn = _("INBOX");
            } else {
                $fn .= imap_utf7_decode_local($mailboxparts[$i]);
            }
            if ($i != (sizeof($mailboxparts)-1) )
            $fn .= ".";
        }
        
        $string .= ' <small> '. sprintf( _("(Full Path: %s)"), $fn ) . '</small>';
    
        sqgetGlobalVar('ldapfolderinfo', $folderinfo, SQ_SESSION);
    
        if(is_shared_folder($mailbox)) {
            if(isset($folderinfo[$mailbox])) {
                if(isset($folderinfo[$mailbox]['description'])) {
                    $string .= ' - '.$folderinfo[$mailbox]['description'];
                }
        
                if(isset($folderinfo[$mailbox]['mail'])) {
                    // TODO: Perform sqimap_myrights() on folder, or try to
                    // cache the myrights in an appropriate place.
                    // if(strstr($folderinfo[$mailbox]['perm'], "p")) {
                        $comp_uri = 'src/compose.php?mailbox='.urlencode($mailbox).'&amp;send_to='.urlencode($folderinfo[$mailbox]['mail']);
                        $string .= ' - ' .makeComposeLink($comp_uri, _("Post to Folder"));
                    // }
                }
            }
        
        } elseif(is_user_folder($mailbox, $userfolderinfo)) {
            // HANDLED BY useracl plugin
            // $string .= ' - ' . sprintf( _("Shared by User: %s"), ldapfolderinfo_user_link($userfolderinfo['username']) );
        }
    }
    return $string;
}

