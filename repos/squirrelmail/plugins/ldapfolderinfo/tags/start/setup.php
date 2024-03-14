<?php
/**
 * ldapfolderinfo
 *
 * Displays information about user's folders, such as quota, ownership,
 * permission, and of course sizes and number of messages.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @author Robin Rainton <robin@rainton.com>
 * @package plugins
 * @subpackage ldapfolderinfo
 */

/**
 * Squirrelmail plugin initialize
 * @return string
 */
function squirrelmail_plugin_init_ldapfolderinfo() {
    global $squirrelmail_plugin_hooks;
    /* Application Logic Hooks */
    $squirrelmail_plugin_hooks['left_main_before']['ldapfolderinfo'] = 'ldapfolderinfo_left_before';
    /* Template / Display Hooks */
    $squirrelmail_plugin_hooks['menuline']['ldapfolderinfo'] = 'ldapfolderinfo_menuline';
    $squirrelmail_plugin_hooks['folders_bottom']['ldapfolderinfo']   = 'ldapfolderinfo_display_verbose_link';
    $squirrelmail_plugin_hooks['template_construct_left_main.tpl']['ldapfolderinfo']  = 'ldapfolderinfo_left_after';
    $squirrelmail_plugin_hooks['template_construct_page_header.tpl']['ldapfolderinfo'] = 'ldapfolderinfo_tpl_pagetop';
    $squirrelmail_plugin_hooks['uoa_read_menubar_buttons.tpl_reply_links']['ldapfolderinfo'] = 'ldapfolderinfo_display_reply_folder';
}

/**
 * Hooks for "left-before".
 *
 * 1) Cache the ldapfolderinfo.
 * 2) Call the corresponding function to display the quota root usage in the left
 * frame.
 * @return string
 */
function ldapfolderinfo_left_before() {
    global $ldapfolderinfo, $mailboxes;

    include SM_PATH . 'plugins/ldapfolderinfo/include/constants.php';

    if(isset($_SESSION['ldapfolderinfo'])) {
        $ldapfolderinfo = $_SESSION['ldapfolderinfo'];
    } else {
        include SM_PATH . 'plugins/ldapfolderinfo/include/functions.inc.php';
        include SM_PATH . 'plugins/ldapuserdata/config.php';
        include SM_PATH . 'plugins/ldapuserdata/functions.php';
        $ldapfolderinfo = ldapfolderinfo_cache_ldap($mailboxes);
    }

    include_once(SM_PATH . 'plugins/ldapfolderinfo/include/quota.inc.php');
    include_once(SM_PATH . 'plugins/ldapfolderinfo/include/quota_display.inc.php');
    ldapfolderinfo_display_quota_usage_do();
}

/**
 * Hooks for left-after:
 * (new style, SM 1.5):
 *
 * Display link to Folder information / sizes page.
 *
 * @param array $args
 * @return array
 */
function ldapfolderinfo_left_after(&$args) {
    return array('left_main_after' => ldapfolderinfo_terse_link());
}

/**
 * Informational text in pagetop bar.
 *
 * @param array $args
 * @return array
 */
function ldapfolderinfo_tpl_pagetop(&$args) {
    bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
    $prev = textdomain ('ldapfolderinfo');

    include_once(SM_PATH . 'plugins/ldapfolderinfo/include/pagetop.inc.php');
    $args[1]->assign('current_folder_str_additional', ldapfolderinfo_pagetop_do());
    
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
    return $args;
}


/**
 * Display a verbose link on the Folders page
 * @return string
 */
function ldapfolderinfo_display_verbose_link() {

    global $color;
    
    bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
    textdomain ('ldapfolderinfo');

    echo html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
    html_tag( 'tr' ) .
    html_tag( 'td', '', 'center', $color[4] ) .

    html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
        html_tag( 'tr',
        html_tag( 'td', '<b>' . _("Folders Information") . '</b>', 'center', $color[9] )
        ) .
        html_tag( 'tr' ) .
        html_tag( 'td', '', 'center', $color[0] ) ;

    

    echo '<p><img src="../plugins/ldapfolderinfo/images/info.png" width="18" height="18" border="0" alt="i" " align="middle" /> '.
        
        _("For more information about your folders, please see the").
        ' <a href="../plugins/ldapfolderinfo/folder_sizes.php" target="right" title="'.
        _("Information about size, quota and ownership of available folders") . '">'.
        _("Folders Information Page").
        '</a>.</p>';
    
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');


    echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."</table>\n";

    echo '</td></tr>
    </table>';
}

/**
 * Display a terse link on the left frame, after the folder list.
 * @return string
 */
function ldapfolderinfo_terse_link() {
    global $color;
    
    bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
    textdomain ('ldapfolderinfo');

    $out = '<p align="center">'.
        '<a href="../plugins/ldapfolderinfo/folder_sizes.php" target="right" title="' .
        _("Information about size, quota and ownership of available folders") . '">' .
        '<img src="../plugins/ldapfolderinfo/images/info.png" width="18" height="18" border="0" alt="i" " align="middle" /> '.
        _("Folders Information") . '</a></p>';
    
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');

    return $out;
}

/**
 * Link to reply straight to shared folder.
 * compose.php will handle the proper address needed, according to the value of 
 * $mailbox that is passed.
 *
 * Extra hook: 'uoa_read_menubar_buttons.tpl_reply_links' in file
 * templates/default/read_menubar_buttons.tpl.
 *
 * @return void
 */
function ldapfolderinfo_display_reply_folder(&$t) {
    global $mailbox, $button_onclick;
    include_once(SM_PATH . 'plugins/ldapfolderinfo/include/functions.inc.php');
    //if(!strstr(strtolower($mailbox), "inbox")) {
    if(is_shared_folder($mailbox)) {
        echo '<input type="submit" name="smaction_reply_folder" value="'. _("Reply to Folder") .'" '. $t[1]['button_onclick'].'/>';
    }
}


/**
 * Display menuline link
 * @return void
 */
function ldapfolderinfo_menuline() {
    displayInternalLink('plugins/ldapfolderinfo/subscription.php',_("Subscribe"));
    echo "&nbsp;&nbsp\n";
}    

/**
 * Squirrelmail plugin information
 * @return array
 */
function ldapfolderinfo_info() {
    return array(
        'version' => '0.1',
        'requirements' => 'University of Athens LDAP-enabled Cyrus (http://email.uoa.gr) and configured LDAP server.'.
            'Also, plugin useracl, and if LDAP functionality is desired, plugins directory and ldapuserdata.'
    );
}

/**
 * Versioning information
 * @return string
 */
function ldapfolderinfo_version() {
    $info = ldapfolderinfo_info();
    return $info['version'];
}


