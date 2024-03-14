<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */

include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/config.php');

/**
 * Define a special onload() event. This is only for informational messages 
 * that check for various previous POST conditions (e.g addressbook entry added 
 * from abook_take plugin)
 * @return string
 */
function addressbook_ui_enhancements_onload_do($onload) {
    if(isset($_POST['addaddr']) && isset($_POST['addaddr']['SUBMIT']) && isset($_POST['addaddr']['nickname'])) {
        return "addressbookUiMsg('". sprintf( _("Entry for %s has been Saved Successfully."), htmlspecialchars($_POST['addaddr']['nickname'])) . "', 'info');";
    }
    return '';
}


/**
 * Print introduction / messages placeholder
 */
function addressbook_ui_enhancements_print_message_box_do() {
    global $base_uri, $abook, $alist, $color, $abook_help_url, $abook_help_import_url;
    $abook_entries_count = sizeof($alist);
    
    sq_bindtextdomain('addressbook_ui_enhancements', SM_PATH . 'plugins/addressbook_ui_enhancements/locale');
    textdomain ('addressbook_ui_enhancements');

    echo html_tag( 'table',  
        html_tag( 'tr',
            html_tag( 'td', "\n". '<strong>' . _("Personal Addressbook") . '</strong>' . "\n",
                'center', $color[0]
                )
            )
        , 'center', '', 'width="95%"' ) ."\n";
    echo '<table width="90%" border="0" cellpadding="1" cellspacing="0" align="center">' . "\n";

    $text = '';
    // The text changes according to the number of entries
    if($abook_entries_count < 10) {
        $text .= '<p class="abook_paragraph">'. _("Welcome to your personal addressbook.") . '</p>';

        if(!empty($abook_help_import_url)) {
            $text .= '<p class="abook_paragraph">'.
            '<img src="'.$base_uri.'plugins/famfamfam/icons/lightning.png" alt="" /> '.
            sprintf(_("Do you already keep an addressbook in your computer? Export it in <acronym title=\"Comma Separated Values\">CSV</acronym> format, and import it here. Read the <a href=\"%s\" target=\"_blank\">relevant instructions</a> and go to the <a href=\"%s\" target=\"_blank\">Addressbook Tools Page</a>."), $abook_help_import_url, $base_uri.'plugins/abook_tools/abook_tools.php').
            '</p>';
        }
    }
    if(true) {
        $text .= '<p class="abook_paragraph">'.
            '<img src="'.$base_uri.'plugins/famfamfam/icons/lightbulb.png" alt="" /> '.
            _("Tip: The addressbook is designed to be used not only in the Webmail, but also from any application of your choice, such as Outlook or Thunderbird, from your office or home.") .
            ' ';

        if(!empty($abook_help_url)) {
            $text .= sprintf( _("To learn more, read the <a href=\"%s\" target=\"_blank\">instructions on how to access your addressbook from anywhere</a>."), $abook_help_url );
        } else {
            $text .= _("Contact our helpdesk for more information.");
        }

        $text .= '</p>';
    }

    echo html_tag('tr',html_tag('td',$text)); 
    echo "</table>\n";
    echo '<br/>';

    // Message box for success messages etc.
    echo  '<div id="abook_message_box"></div>';
    
    echo  '<div id="abook_message_refresh">'.
        '<a href="addressbook.php">'.
        '<img src="'.$base_uri.'plugins/famfamfam/icons/arrow_refresh.png" alt="" /> <strong>'.
        _("Refresh Addressbook") .
        '</strong></a></div>';

    sq_bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain ('squirrelmail');
}

/**
 * Display an addressbook object.
 *
 * @param array $entry The LDAP object entry
 * @param string $pabobject backend:nickname
 */
function addressbook_ui_enhancements_print_user_profile(&$entry, $pabobject) {
    global $base_uri, $abookAttrs, $addressbook_ui_enhancements_editable_attrs_layout,
        $ldq_attributes, $color;

    sq_bindtextdomain('addressbook_ui_enhancements', SM_PATH . 'plugins/addressbook_ui_enhancements/locale');
    textdomain ('addressbook_ui_enhancements');

    if(!isset($base_uri)) $base_uri = sqm_baseuri();
    $iconBase = '../plugins/famfamfam/icons/';
    
    $out = '<div id="profile_msgs_'.$pabobject.'" class="abook_ui_profile_msgs"></div>';
    
        
    $out .= '<table border="0" width="100%" class="vcard">';
    
    // 1) Main stuff (excluded from groups below)
    $out .= '<tr><td colspan="'.sizeof($addressbook_ui_enhancements_editable_attrs_layout).'" align="center">';
    
    if(isset($entry['cn'])) {
        $header = 'h2';
        for($i = 0; $i < $entry['cn']['count']; $i++) {
            $out .= '<'.$header.' class="fn">'.htmlspecialchars($entry['cn'][$i]).'</'.$header.'>';
            $header = 'h3';
        }
    }

    if(isset($entry['mail'])) {
        for($i = 0; $i < $entry['mail']['count']; $i++) {
            $compose_uri = 'src/compose.php?send_to='.urlencode($entry['mail'][$i]);
            $out .= '<strong>'. makeComposeLink($compose_uri, $entry['mail'][$i]) . '</strong><br/>';
        }
    }

    // TODO: Photo
    /*
    $out .= '<tr bgcolor="'.$color[4].'"><td align="center" width="20%">'.
        (isset($entry['photo']) ?
        // TODO:
        '<img src="'.$base_uri.'plugins/addressbook_ui_enhancements/photo_decode.php?='.$entry['photo'].'" alt="'. _("Photo") . '" border="0" class="photo" />':
        '' //'<img src="'.$base_uri.'plugins/addressbook_ui_enhancements/images/photo_placeholder.jpg" alt="'. _("Photo") . '" border="0" class="photo" />'
        ).
    */
    
    $out .= '</td></tr>';

    // 2) Each layout e.g left, right printed out
    
    $omit_attrs = array('cn', 'mail', 'givenname', 'sn');
    $omit_groups = array('main');

    foreach($addressbook_ui_enhancements_editable_attrs_layout as $layout => $groups) {
        $out .= '<td valign="top" width="'.(100 / sizeof($addressbook_ui_enhancements_editable_attrs_layout)).'%">';
        foreach($groups as $group) {
            if(in_array($group, $omit_groups)) continue;
            $out .= '<table width="90%" border="0" cellspacing="2" cellpadding="3" align="center">';
            $attrs = $abookAttrs[$group];
            
            foreach($attrs as $attr) {
                if(in_array($attr, $omit_attrs)) continue;
                if(!isset($entry[$attr]) || $entry[$attr]['count'] == 0) continue; // skip empty attrs
                
                $out .= '<tr bgcolor="'.$color[4].'"><td align="right" width="30%">';

                switch($attr) {
                    case 'labeleduri':
                        $out .= '<small>' . $ldq_attributes[$attr]['text'] . ':</small> '.
                                '</td><td align="left" width="70%">';
                        for($i = 0; $i < $entry[$attr]['count']; $i++) {
                            $out .= '<img src="'.$base_uri.'plugins/addressbook_ui_enhancements/images/external_link.png" alt="" />'.
                                '<a href="'.htmlspecialchars($entry[$attr][$i]).'" class="url" target="_blank">'.
                                htmlspecialchars($entry[$attr][$i]).'</a><br/>';
                        }

                        break;
                    default: 
                        $out .= '<small>' . $ldq_attributes[$attr]['text'] . ':</small> '.
                                '</td><td align="left" width="70%">';

                        $mfClass = ''; // If there is microformat class defined.
                        if(isset($ldq_attributes[$attr]['class'])) {
                            $mfClass = '<span class="' . $ldq_attributes[$attr]['class'] . '">';
                        }

                        for($i = 0; $i < $entry[$attr]['count']; $i++) {
                            $out .= $mfClass . 
                                str_replace('$', '<br/>', htmlspecialchars($entry[$attr][$i])).
                                (empty($mfClass) ? '' : '</span>') . '<br/>';
                        }
                        break;
                }
                $out .= '</td></tr>';
            }
            $out .= '</table>';
        }
        $out .= '</td>';
    }
    $out .= '</tr>';

    // Edit / Delete links
    // Ugh, another table row. No way to center these float'ed buttons in CSS. :/
    $out .= '<tr><td class="abook_ui_button_rows" colspan="'.sizeof($addressbook_ui_enhancements_editable_attrs_layout).'" align="center">'.
        '<div class="buttonswrapper">'.
        '<div class="buttons" id="edit_delete_buttons_'.$pabobject.'">'.
        '<button type="button" name="edit_'.$pabobject.'" onclick="addressbookUiEdit(\''.$pabobject.'\');">'.
        '<img src="'.$iconBase.'page_edit.png" alt="" border="0" /> '. _("Edit").
         '</button>'.

        '<button type="button" name="delete_'.$pabobject.'" class="negative" onclick="addressbookUiDelete(\''.$pabobject.'\');">'.
        '<img src="'.$iconBase.'delete.png" alt="" border="0" /> '. _("Delete") .  '</button>'.
        '</div></div>';
    
    $out .= '</td></tr></table>';

    textdomain ('squirrelmail');
    return $out;
}

/**
 * Show the edit form for an addressbook object.
 *
 * @param array $entry The LDAP object entry
 * @param string $pabobject backend:nickname
 */
function addressbook_ui_enhancements_print_edit_page(&$entry, $pabobject) {
    global $base_uri, $charset, $addressbook_ui_enhancements_editable_attrs_layout,
         $abookAttrs, $group_descriptions, $ldq_attributes, $color, $directory_icon_path;

    if(!isset($base_uri)) $base_uri = sqm_baseuri();

    sq_bindtextdomain('addressbook_ui_enhancements', SM_PATH . 'plugins/addressbook_ui_enhancements/locale');
    textdomain ('addressbook_ui_enhancements');

    $group_descriptions = array(
        'main' => _("Main Details"),
        'work' => _("Work Information"),
        'contact' => _("Contact Details"),
        'address' => _("Address"),
        'other' => _("Other"),
    );

    $addnew = false;
    if($pabobject == '1:0') $addnew = true;

    /* Hack: Calculate first name, if 'cn' is available and 'givenname' does not exist. */
    if(!isset($entry['givenname']) && isset($entry['cn']) && isset($entry['sn'])) {
        global $ldapAbookFullnameTemplate;
        $entry['givenname'][0] = trim(str_replace($entry['sn'][0], '', $entry['cn'][0]));
        $entry['givenname']['count'] = 1;
    }

    //$out = '<form target="" method="POST" name="customEditForm_'.$pabobject.'">';
    $out = '';

    $out .= '<table border="0" width="100%" cellspacing="3" cellpadding="8"><tr>';
    foreach($addressbook_ui_enhancements_editable_attrs_layout as $layout => $groups) {
        $out .= '<td valign="top" width="'.(100 / sizeof($addressbook_ui_enhancements_editable_attrs_layout)).'%">';
        foreach($groups as $group) {
            $attrs = $abookAttrs[$group];
            $out .= '<fieldset class="abook_ui_fieldset">'.
                '<legend>'.$group_descriptions[$group].'</legend>'.
                '<ol>';

            foreach($attrs as $attr) {
                if(!isset($entry[$attr]) || $entry[$attr]['count'] == 0 ){
                    $out .= addressbook_edit_line($pabobject, $attr, '', 0);
                } else {
                    for($i = 0; $i < $entry[$attr]['count']; $i++) {
                        $out .= addressbook_edit_line($pabobject, $attr, $entry[$attr][$i], $i);
                        /* TODO
                        $out .= ($i > 0 ? 
                            '<a href="#"><img src="'.$base_uri.'plugins/famfamfam/icons/delete.png" alt="'._("Delete").'" title="'._("Delete").'" border="0" /></a>'  : ''
                         );
                         */
                    }
                    // TODO
                    //$out .= '<a href="#"><img src="'.$base_uri.'plugins/famfamfam/icons/add.png" alt="' . _("Add") .'" title="'._("Add").'" border="0" /></a>';
                }
            }
            $out .= '</ol></fieldset><br/>';
        }
        $out .= '</td>';
    }
    $out .= '</table>';
    
    $out .= '<br/>'.
            '<div class="abook_ui_button_rows">'.
            '<div class="buttons" id="save_cancel_buttons_'.$pabobject.'">'.
            '<input type="hidden" name="pabobject" value="'.htmlspecialchars($pabobject).'" />'.
            '<button type="button" class="positive" name="save" onclick="addressbookUiSave(\''.$pabobject.'\');" />'.
                '<img src="'.$base_uri.'plugins/famfamfam/icons/disk.png" alt="" /> '. 
                ($addnew ? _("Add New Contact") : _("Save Changes") ) .
            '</button>'.
                
            '<button type="button" class="negative" name="cancel" onclick="'.
            ($addnew ?
                'addressbookUiResetAddNew();' :
                'addressbookUiShow(\''.$pabobject.'\');'
            ) .
            '"/><img src="'.$base_uri.'plugins/famfamfam/icons/cancel.png" alt="" /> '. _("Cancel").
            '</button>' .

            '</div></div>';

    textdomain ('squirrelmail');
    return $out;
}

/**
 * Print add new contact edit form
 * @return string
 */
function addressbook_ui_enhancements_print_addnew(&$res, &$pabobject) {
    global $color;
    textdomain ('addressbook_ui_enhancements');
    $out = html_tag( 'table',
        html_tag( 'tr',
            html_tag( 'td', "\n". '<strong>' . _("Add a new Contact") . '</strong>' . "\n",
                'center', $color[0]
                )
            )
        , 'center', '', 'width="95%"' ) ."\n".
            addressbook_ui_enhancements_print_edit_page($res, $pabobject);

    textdomain ('squirrelmail');
    return $out;
}

/**
 * Print one form element (Textarea or input box) for the edit entry functionality.
 *
 * @return string
 */
function addressbook_edit_line($pabobject, $attr, $val = '', $index = 0) {
    global $ldq_attributes, $charset, $directory_icon_path;

    $out = '<li class="abook_ui_item">
            <label for="editobject_'.$attr.'_'.$index.'" class="abook_ui_label">' .
            ( $index > 0 ? 
                sprintf(_("%s #%s:"), $ldq_attributes[$attr]['text'], $index+1) :
                sprintf(_("%s:"),     $ldq_attributes[$attr]['text'])
            ).
            '</label> ';

    $out .= '<div class="abook_ui_input">';

    $out .= (isset($ldq_attributes[$attr]['image'])? '<img src="'.$directory_icon_path.$ldq_attributes[$attr]['image'].'" alt="" /> ' : '') ;

    $thisId = 'editobject_'.$pabobject.'_'.$attr.'_'.$index;
    $thisName = 'editobject['.$pabobject.']['.$attr.']['.$index.']"';

    if(isset($ldq_attributes[$attr]['input']) && $ldq_attributes[$attr]['input'] == 'textarea') {
         // freely editable textarea
         $out .= '<textarea rows="3" cols="21" class="abook_form_element" id="'.$thisId.'" name="'.$thisName.'"'.
                ' onfocus="addressbookUiEditFocus(\''.$thisId.'\');" onblur="addressbookUiEditBlur(\''.$thisId.'\');" >'.
                htmlspecialchars($val) .
                '</textarea>';
        
     } else {
         // Freely editable, Input Box
         $out .= '<input type="text" size="22" class="abook_form_element" id="'.$thisId.'" name="'.$thisName.'"'.
                ' onfocus="addressbookUiEditFocus(\''.$thisId.'\');" onblur="addressbookUiEditBlur(\''.$thisId.'\');"'.
                ' value="'.htmlspecialchars($val) .'" />';
     }
    
    if (isset($ldq_attributes[$attr]['inputdesc']) && $index == 0) {
        $out .= '<br /><small>'.$ldq_attributes[$attr]['inputdesc'] . '</small>';
        }
    $out .= '</div>';
    $out .= '</li>';
    return $out;
}

/**
 * HTML in-Div for DELETION CONFIRMATION, sent via AJAX.
 *
 * @param array $entry The LDAP object entry
 * @param string $pabobject backend:nickname
 */
function addressbook_ui_enhancements_print_deletion_confirmation(&$entry, $pabobject) {
    global $base_uri, $abookAttrs, $ldq_attributes;

    if(!isset($base_uri)) $base_uri = sqm_baseuri();
    $iconBase = $base_uri. 'plugins/famfamfam/icons/';
    
    textdomain ('addressbook_ui_enhancements');
    
    $formatted_name = ( isset($entry['displayname']) ? $entry['displayname'][0]  . ' - ' : '' ) .
            (isset($entry['cn']) ? $entry['cn'][0] : '');
    
     $out = '<div class="abook_ui_button_rows">'.
            '<div class="buttons" id="confirm_delete_buttons_'.$pabobject.'">'.
            sprintf( _("Are you sure you want to delete addressbook entry %s?"), $formatted_name). '<br/>'.

            '<input type="hidden" name="pabobject" value="'.htmlspecialchars($pabobject).'" />'.

            '<button type="button" class="negative" name="deladdr" onclick="addressbookUiDelete(\''.$pabobject.'\');" />'.
                '<img src="'.$iconBase.'delete.png" alt="" border="0" /> '. _("Delete") .
            '</button>'.

            '<button type="button" name="cancel" onclick="addressbookUiShow(\''.$pabobject.'\');" />'.
                '<img src="'.$base_uri.'plugins/famfamfam/icons/cancel.png" alt="" /> '. _("Cancel").
            '</button>'.

            '</div>'.
            '</div>';
    textdomain ('squirrelmail');
    return $out;
}

