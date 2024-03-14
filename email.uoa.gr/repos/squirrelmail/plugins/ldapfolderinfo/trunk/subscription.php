<?php
/**
 * subscription.php
 *
 * A separate page for folder subscriptions.
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage ldapfolderinfo
 * @version $Id: subscription.php,v 1.2 2006/11/22 13:21:23 avel Exp $
 */

if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    require_once(SM_PATH . 'include/validate.php');
    require_once(SM_PATH . 'functions/plugin.php');
    require_once(SM_PATH . 'functions/html.php');
}

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/imap.php');

/* LDAP stuff */
include_once(SM_PATH . 'plugins/ldapuserdata/functions.php');
include_once(SM_PATH . 'plugins/ldapfolderinfo/include/functions.inc.php');
include_once(SM_PATH . 'plugins/useracl/imap_acl.php');

global $ldap, $folderattributes, $ldap_base_dn, $permarray, $username;

/* get globals we may need */

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

sqgetGlobalVar('success', $success, SQ_GET);

/* end of get globals */

$imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);
// force retrieval of a non cached folderlist
$boxes = sqimap_mailbox_list($imapConnection,true);

/* Actions */

/* Actions are handled ATM by existing Squirrelmail scripts. */

/*
if( (isset($_POST['subscribe']) || isset($_POST['unsubscribe']) ) && isset($_POST['mailbox']) ) {

   $mailbox = $_POST['mailbox'];

   if (isset($_POST['subscribe'])) {
    if($no_list_for_subscribe && $imap_server_type == 'cyrus') {
       if(!sqimap_mailbox_exists($imapConnection, $mailbox[0])) {
          header("Location: $location/folders.php?success=subscribe-doesnotexist");
          sqimap_logout($imapConnection);
          exit(0);
       }
    }
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_subscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'subscribe';

} elseif($_POST['unsubscribe']) {

    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_unsubscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'unsubscribe';
}

}
*/

$boxes_all = sqimap_mailbox_list_all ($imapConnection);
sqimap_logout($imapConnection);

$folderinfo = ldapfolderinfo_cache_ldap($boxes_all);

/**
 * ================== Presentation Logic ==================
 */

displayPageHeader($color, 'None');

bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
textdomain ('ldapfolderinfo');

echo '<br/>' .
html_tag( 'table', '', 'center', $color[0], 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) .
    html_tag( 'tr' ) .
    html_tag( 'td', '', 'center' ) . '<b>' . _("Folders") . '</b>' .
    html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
    html_tag( 'tr' ) .
    html_tag( 'td', '', 'center', $color[4] );

if ( isset($success) && $success ) {

    $td_str = '<strong>';

    switch ($success)
    {
        case 'subscribe':
            $td_str .=  _("Subscribed successfully!");
            break;
        case 'unsubscribe':
            $td_str .=  _("Unsubscribed successfully!");
            break;
        case 'subscribe-doesnotexist':
            $td_str .=  _("Subscription Unsuccessful - Folder does not exist.");
            break;
    }

    $td_str .= '</strong><br/>';


/* Subscribed Folders */
echo html_tag( 'table',
    html_tag( 'tr',
        html_tag( 'td', $td_str .
            '<a href="../../src/left_main.php" target=left>' . _("refresh folder list") . '</a>' , 'center' )
        ) ,
        'center', '', 'width="100%" cellpadding="4" cellspacing="0" border="0"' );
}

echo "\n<br/>";



/**
 * -------------------- UNSUBSCRIBE FOLDERS --------------------
 */

echo html_tag( 'table', '', 'center', '', 'width="95%" cellpadding="4" cellspacing="0" border="0"' ) .
            html_tag( 'tr',
                html_tag( 'td', '<b>' . _("Unsubscribe") . '</b>', 'center', $color[9], 'colspan="2"' )
            ) .
            html_tag( 'tr' ) .  html_tag( 'td', '', 'center', $color[0], 'width="100%"' );
        
        /* if ($count_special_folders < count($boxes)) { */ /* WTF? #1 */
    echo '<form action="../../src/folders_subscribe.php?method=unsub&amp;referrer=../plugins/ldapfolderinfo/subscription" method="POST">';
    echo _("You are already subscribed to these folders:") . '<br />';

    echo '<table align="center" border="0" width="99%" cellspacing="2" cellpadding="1">';
       
    $toggle = false;

    for ($i = 0; $i < count($boxes); $i++) {


        $use_folder = true;
        if ((strtolower($boxes[$i]["unformatted"]) != "inbox") &&
            ($boxes[$i]["unformatted"] != $trash_folder) &&
            ($boxes[$i]["unformatted"] != $sent_folder) &&
            ($boxes[$i]["unformatted"] != $draft_folder)) {
                $box = $boxes[$i]["unformatted-dm"];
            $box2 = str_replace(' ', '&nbsp;', imap_utf7_decode_local($boxes[$i]["unformatted-disp"]));
                echo '<tr';
                if ($toggle) {
                    echo ' bgcolor="'.$color[12].'"';
                }
                echo '><td><input type="checkbox" name="mailbox[]" value="'.$box.'" /></td>'; /* Checked? */
        echo '<td align="left"><a href="'. SM_PATH . 'src/right_main.php?PG_SHOWALL=0&amp;mailbox='.$box.'" target="right" style="text-decoration:none">'.$box2.'</a></td>';
        echo '<td align="left">';
        if(isset($folderinfo[$box]['description'])) {
            echo $folderinfo[$box]['description'];
        }
        echo '</td></tr>';
        }
        
        if(!$toggle) {
            $toggle = true;
        } elseif($toggle) {
            $toggle = false;
        }
    }
    echo '</table>';

    echo '<br> <input type=SUBMIT VALUE="' .
          _("Unsubscribe")
      . '" name="unsubscribe"></FORM><br />';

echo '</td></tr>
    </table>
    <br />
';


/**
 * -------------------- SUBSCRIBE TO FOLDERS (1) --------------------------
 */


echo html_tag( 'table', '', 'center', '', 'width="95%" cellpadding="4" cellspacing="0" border="0"' ) .
    html_tag( 'tr',
        html_tag( 'td', '<b>' . _("Subscribe to Global Folders") . '</b>', 'center', $color[9], 'colspan="2"' )
    ) .
    html_tag( 'tr' ) .  html_tag( 'td', '', 'center', $color[0], 'width="100%"' );


$box = '';
$box2 = '';
for ($i = 0, $q = 0; $i < count($boxes_all); $i++) {
    $use_folder = true;
    for ($p = 0; $p < count ($boxes); $p++) {
        if ($boxes_all[$i]['unformatted'] == $boxes[$p]['unformatted']) {
            $use_folder = false;
            continue;
        } else if ($boxes_all[$i]['unformatted-dm'] == $folder_prefix) {
            $use_folder = false;
        }
    }
    if ($use_folder == true) {
        $box[$q] = $boxes_all[$i]['unformatted-dm'];
        $box2[$q] = str_replace(' ', '&nbsp;', imap_utf7_decode_local($boxes_all[$i]['unformatted-disp']));
        $q++;
    }
}

if ($box && $box2) {

    echo '<form action="../../src/folders_subscribe.php?method=sub&amp;referrer=../plugins/ldapfolderinfo/subscription" method="POST">';

    echo _("You can subscribe to the following global shared folders of this system:") . '<br /><br />';

    echo '<table align="center" border="0" width="99%" cellspacing="2" cellpadding="1">';

    for ($q = 0; $q < count($box); $q++) {      
        if(!ereg("^user\.",$box[$q])) {
            echo '<tr';
            if ($toggle) {
                echo ' bgcolor="'.$color[12].'"';
            }
            echo '><td><input type="checkbox" name="mailbox[]" id="sub_'.$box[$q].'" value="'.$box[$q].'" ></td>';
            echo '<td align="left"><label for="sub_'.$box[$q].'">'.$box2[$q].'</label></td>';
            echo '<td align="left">';
            if(isset($folderinfo[$box[$q]]['description'])) {
                echo $folderinfo[$box[$q]]['description'];
            }
            echo '</td>';
            echo '</tr>';
            if(!$toggle) {
                $toggle = true;
            } elseif($toggle) {
                $toggle = false;
            }
        }
    }
    echo '</table>';
    echo '<br /><br />'
       . '<input type=SUBMIT VALUE="'. _("Subscribe") . "\" name=\"subscribe\">\n"
       . '</FORM>';
} else {
    echo _("No folders were found to subscribe to!");
}
    
echo '</td></tr>
    </table>
    <br />
';

    

/*
 * --------------- SUBSCRIBE TO OTHER USERS' FOLDERS (2) -------------
 */

echo html_tag( 'table', '', 'center', '', 'width="95%" cellpadding="4" cellspacing="0" border="0"' ) .
    html_tag( 'tr',
        html_tag( 'td', '<b>' . _("Subscribe to Folders shared by Other Users") . '</b>', 'center', $color[9], 'colspan="2"' )
    ) .
    html_tag( 'tr' ) .  html_tag( 'td', '', 'center', $color[0], 'width="100%"' ).
    
    '<form action="../../src/folders_subscribe.php?method=sub&amp;referrer=../plugins/ldapfolderinfo/subscription" method="POST">';

    echo _("Other users on this system share the following folders:") . '<br /><br />';

    $none = true;

    echo '<table align="center" border="0" width="99%" cellspacing="2" cellpadding="1">';
    $toggle = false;
    for ($q = 0; $q < count($box); $q++) {      
        if(ereg("^user\.",$box[$q])) {
            $none = false;
               echo '<tr';
            if ($toggle) {
                echo ' bgcolor="'.$color[12].'"';
            }
            echo '><td><input type="checkbox" name="mailbox[]" id="sub_'.$box[$q].'" value="'.$box[$q].'" ></td>';
            echo '<td align="left"><label for="sub_'.$box[$q].'">'.ldapfolderinfo_userfolder_to_humanreadable($box2[$q]).'</label></td>';
            echo '<td align="left">';
            if(isset($folderinfo[$box[$q]]['description'])) {
                echo $folderinfo[$box[$q]]['description'];
            }
            echo '</td></tr>';
        }
        if(!$toggle) {
            $toggle = true;
        } elseif($toggle) {
            $toggle = false;
        }
    }
    echo '</table>';

    if($none) {
        echo _("There are no other users that allow access to their folders at this time.");

    } else {
        echo '<br /><br />'
           . '<input type=SUBMIT VALUE="'. _("Subscribe") . "\" name=\"subscribe\">\n"
           . '</FORM>';
    }

echo '</td></tr>
    </table>
    <br />
</td></tr>
</table>
</body></html>
';

