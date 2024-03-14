<?php
/**
 * addressbook_handler.php
 *
 * Handles all incoming requests asynchrously.
 *
 * @copyright &copy; 2007-2008 The SquirrelMail Project Team, Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../../');

/** SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/addressbook.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');

/** Includes of configuration and ldap schemata */
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/config.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/constants.inc.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/html_components.inc.php');

sq_bindtextdomain('directory', SM_PATH . 'plugins/directory/locale');
textdomain ('directory');
include_once(SM_PATH . 'plugins/directory/schemas/main.php');
include_once(SM_PATH . 'plugins/directory/schemas/eduorg.php');
include_once(SM_PATH . 'plugins/directory/schemas/uoa.php');
include_once(SM_PATH . 'plugins/directory/config.php');
textdomain ('squirrelmail');

/** lets get the global vars we may need */
sqgetGlobalVar('key',         $key,          SQ_COOKIE);

sqgetGlobalVar('username',    $username,     SQ_SESSION);
sqgetGlobalVar('onetimepad',  $onetimepad,   SQ_SESSION);
sqgetGlobalVar('base_uri',    $base_uri,     SQ_SESSION);
sqgetGlobalVar('delimiter',   $delimiter,    SQ_SESSION);

sqgetGlobalVar('pabaction',   $pabaction,    SQ_GET);
sqgetGlobalVar('pabobject',   $pabobject,    SQ_GET);
sqgetGlobalVar('editobject',  $editobject,   SQ_POST);
$editdata = array();
if(!empty($editobject) && !empty($pabobject)) {
    $editdata = $editobject[$pabobject];
    /* In the future, we could have a "save all" button that would save all edited entries. Here, we would
     * gather all data in the editobject array. */
}


$abook = addressbook_init(true, true);
if($abook->localbackend == 0) {
    exit();
}

/* set up charset */
sqgetGlobalVar('language', $language, SQ_GET);
if(!isset($language)) {
    $language = $lang_iso = getPref($data_dir, $username, 'language');
}
$ldq_lang = substr($lang_iso, 0, 2);
global $charset;
$charset = $languages[$lang_iso]['CHARSET'];


/* Input validation */

/* pabobject is the usual form: backend:nickname , where backend is an integer. */
if(isset($pabobject)) {
    if(!strstr($pabobject, ':')) die();
    list($backend, $nick) = explode(':', $pabobject, 2);
    if(!is_numeric($backend)) die();
}

$sm_language = getPref($data_dir, $username, 'language');
set_up_language($sm_language);

switch($pabaction) {
    case 'retrieve':
        /* ============= Retrieve a single entry from addressbook, properly formatted. ============= */
        $res = $abook->backends[1]->lookup_extended($nick);
        
        if($res) {
            addressbook_ui_enhancements_sanitize_single_entry($res);
            echo '<div style="text-align:left">';
            echo addressbook_ui_enhancements_print_user_profile($res, $pabobject);
            echo '</div>';
        }
        exit;
        break;

    case 'confirmdelete':
        /* ============= Confirm Deletion of an entry from addressbook ============= */
        $res = $abook->backends[1]->lookup_extended($nick);
        
        echo addressbook_ui_enhancements_print_deletion_confirmation($res, $pabobject);
        exit;
        break;

    case 'delete':
        /* ============= Delete an entry from addressbook ============= */
        $res = $abook->backends[1]->lookup_extended($nick);
        if(empty($res['dn'])) {
            echo ABOOK_UI_ERROR_DURING_DELETE . '||' . $nick . '||' . _("Could not determine the dn (distinguished name) of this entry.");
            exit;
        }

        $resDel = $abook->backends[1]->delete_extended($nick);

        $additional_return = '||' . addressbook_ui_enhancements_friendly_name($editdata) . '||' . ($resDel !== true ? $resDel : '');
        if($resDel === true) {
            echo ABOOK_UI_DELETED_SUCCESSFULLY . $additional_return;
            exit;
        } else {
            echo ABOOK_UI_ERROR_DURING_DELETE . $additional_return;
            exit;
        }

    case 'edit':
        /* ============= Edit an addressbook entry ============= */
        $res = $abook->backends[1]->lookup_extended($nick);
        if($res) {
            addressbook_ui_enhancements_sanitize_single_entry($res);
            echo '<div style="text-align:left">';
            echo addressbook_ui_enhancements_print_edit_page($res, $pabobject);
            echo '</div>';
        }
        exit;
        break;
    
    case 'addnew':
        /* ============= Add a new addressbook entry ============= */
        $res = null;
        $pabobject = "1:0";
        echo addressbook_ui_enhancements_print_addnew($res, $pabobject);
        exit;
        break;
    
    case 'save':
        /* ============= Save changes to an addressbook entry or add a new entry ============= */
        $addnew = false;
        if($nick == '0') {
            // "0:0" designates adding a new entry.
            $addnew = true;
            $res = array();
        }

        if(!$addnew) {
            $res = $abook->backends[1]->lookup_extended($nick);
        }

        if(!$addnew && !$res)
            exit;

        if($addnew) {
            list($info_mod, $info_del, $errors) = addressbook_ui_enhancements_prepare_edit_entry($res, $editdata);
            $res2 = $abook->backends[1]->add_extended($info_mod);
            if($res2 == false) {
                $errors[] = $abook->backends[1]->error;
            }
            
            $additional_return = '||' . addressbook_ui_enhancements_friendly_name($editdata) . '||' . (!empty($errors)? $errors[0] : '');

            if($res2 == false) {
                echo ABOOK_UI_ERROR_DURING_SAVE . $additional_return;
            } else {
                echo ABOOK_UI_SAVED_SUCCESSFULLY_UPDATE_NEEDED . $additional_return;
            }
        } else {
            addressbook_ui_enhancements_sanitize_single_entry($res);
            list($info_mod, $info_del, $errors) = addressbook_ui_enhancements_prepare_edit_entry($res, $editdata);
            $main_row_data_changed = addressbook_ui_enhancements_has_main_row_data_changed($info_mod, $info_del);

            $res2 = $abook->backends[1]->modify_extended($nick, $info_mod);
            if($res2 == false) {
                $errors[] = $abook->backends[1]->error;
            } else {
                // now try to delete the rest of the attributes according to $info_del
                foreach($info_del as $attr => $dummy) {
                    $dn = $abook->backends[1]->get_entry_dn($nick);
                    $res3 = $abook->backends[1]->ldap->deleteAttribute($dn, $attr);
                }
            }
            
            $additional_return = '||' . addressbook_ui_enhancements_friendly_name($res) . '||' . (!empty($errors)? $errors[0] : '');
            if($res2) {
                if($main_row_data_changed) {
                    echo ABOOK_UI_SAVED_SUCCESSFULLY_UPDATE_NEEDED . $additional_return;
                } else {
                    echo ABOOK_UI_SAVED_SUCCESSFULLY . $additional_return;
                }
            } else {
                echo ABOOK_UI_ERROR_DURING_SAVE . $additional_return;
            }
        }
        exit;
        break;
    
    case 'show_error':
        /* ============= Show error message ============= */
        echo '<p>'. _("An error has been encountered while modifying your personal addressbook.") . '</p>'.
             '<p>'. sprintf( _("The error was: %s"), 'Test') . '</p>'.
             '<p>'.  _("If the problem persists, please contact your administrator.") . '</p>';
        break;

    default:
        exit;
        break;
}

