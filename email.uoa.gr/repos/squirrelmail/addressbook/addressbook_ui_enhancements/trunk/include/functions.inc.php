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

/**
 * Before the list of addresses:
 * * Print informational message box
 * @return void
 */
function addressbook_ui_enhancements_before_list_do() {
    addressbook_ui_enhancements_print_message_box_do();
}

/**
 * After each row: empty div for the placeholder of the AJAX data
 */
function addressbook_ui_enhancements_after_row_do(&$args) {
    global $line, $color;
    $base_uri = sqm_baseuri();
    $row = $args[1];

    $myid = $row['backend'].':'.$row['nickname'];

    echo '<tr class="abook_info_row" id="abook_info_row_'.$myid.'"><td colspan="4">'.
        '<div id="abook_extended_info_'.$myid.'" class="abook_ui_extended_info">
            <img src="'.$base_uri.'plugins/javascript_libs/images/loading.gif" alt="" />
        </div>'.
        '</td></tr>';
}

/**
 * In the <tr of each row: the row ID.
 *
 * @param array $row
 */
function addressbook_ui_enhancements_row_tag_do(&$row) {
    global $line;
    $myid = $row['backend'].':'.$row['nickname'];
    $out = ' id="abook_main_row_'.$myid.'"' .
        ($line % 2 ? ' class="abook_row_alternate"' : '');
    return $out;
}
   
/**
 * In the <td of columns "name" and "nickname": enabling the mouse click to 
 * expand the information.
 *
 * @param string $columnName 'nickname' or 'name'
 */
function addressbook_ui_enhancements_name_col_do(&$row, $columnName = '') {
    global $color;
    $base_uri = sqm_baseuri();

    $myid = $row['backend'].':'.$row['nickname'];

    return ' class="abook_ui_expand_link' . ($columnName == 'name' ? ' abook_ui_col_name' : '') . '"';
}

/**
 * After all rows: New form for adding a contact
 */
function addressbook_ui_enhancements_bottom_do() {
    global $color;
    
    textdomain ('addressbook_ui_enhancements');

    echo '<a name="AddAddress"></a>' . "\n" .
        '<form name="FormAddNew" id="FormAddNew" target="'.SM_PATH . 'plugins/addressbook_ui_enhancements/addressbook_handler.php" method="post">'.
        '<div id="abook_add_new">
        <input type="button" name="abook_add_new_start" id="abook_add_new_start" value="'._("Add a new Contact").'" />
        </div>';
    echo '</form>
        <br/><p>&nbsp;</p><br/>';
    
    textdomain ('squirrelmail');
}

/**
 * Sanitize an entry array (ldap data).
 * This function will strtolower() all case-insensitive attributes and attribute names.
 *
 * @param array &$entry
 * @return void
 * @author avel
 * @see addressbook_ui_enhancements_sanitize_single_entry()
 */
function addressbook_ui_enhancements_sanitize_entry_array(&$entry) {
    if(!isset($entry['dn'])) {
        // single-entry array.
        addressbook_ui_enhancements_sanitize_single_entry($entry);
    } else {
        for($i=0; $i<$entry['count']; $i++) {
            addressbook_ui_enhancements_sanitize_single_entry($entry[$i]);
        }
    }
}

/**
 * Helper function for sanitizing ldap entries array
 *
 * @see addressbook_ui_enhancements_sanitize_entry_array()
 */
function addressbook_ui_enhancements_sanitize_single_entry(&$entry) {
    /* attributes whose values will be lower-cased: */
    $attrs = array('edupersonorgunitdn', 'edupersonprimaryorgunitdn',
        'uoauserapps', 'edupersonorgdn', 'edupersonprimaryaffiliation',
        'edupersonaffiliation', 'eduorgsuperioruri',
        'uoauserappsnewaccount','uoauserappseditaccount','uoauserappsundergrads',
        'uoauserappstransitioning','uoauserappschangepass'
    );

    if(isset($entry['dn'])) {
        $entry['dn'] = strtolower($entry['dn']);
    }

    // lowercase all keys. e.g. homePostalAddress => homepostaladdress
    for($i=0; $i<$entry['count']; $i++) {
        $attr = $entry[$i];
        if(strtolower($attr) != $attr) {
            $old_key = $entry[$i];
            $new_key = strtolower($entry[$i]);

            $entry[$new_key] = $entry[$entry[$i]];
            $entry[$i] = $new_key;
            
            unset($entry[$old_key]);
        }
    }

    foreach($attrs as $attr) {
        // Lowercase values for the attributes above.
        if(isset($entry[$attr]['count']) && $entry[$attr]['count'] > 0 ) {
            for($j=0; $j<$entry[$attr]['count']; $j++) {
                $entry[$attr][$j] = strtolower($entry[$attr][$j]);
            }
        }
    }
}

/**
 * Extract an array of the actual attributes we want, from the 'groups' 
 * array of the configuration file.
 * @param array
 * @return array
 */
function addressbook_ui_enhancements_editable_attrs_flat($abookAttrs) {
    $addressbook_ui_enhancements_editable_attrs = array();
    foreach($abookAttrs as $group=>$attrs) {
        $addressbook_ui_enhancements_editable_attrs = array_merge($addressbook_ui_enhancements_editable_attrs, $attrs);
    }
    return $addressbook_ui_enhancements_editable_attrs;
}

/**
 * Prepare the ldap entry arrays to be passed to ldap_modify() and 
 * ldap_mod_delete(), and at the same time perform data validation and 
 * transforming.
 *
 * @param array $oldobject
 * @param array $newobject
 * @return array array($info_mod, $info_del, $errors)
 */
function addressbook_ui_enhancements_prepare_edit_entry($oldobject, $newobject) {
    global $abookAttrs, $ldq_attributes, $charset;
    include_once(SM_PATH . 'plugins/directory/include/functions.php');
    
    $info_mod = $info_del = $errmsg = array();

    $addressbook_ui_enhancements_editable_attrs =
        addressbook_ui_enhancements_editable_attrs_flat($abookAttrs);
    
    foreach($addressbook_ui_enhancements_editable_attrs as $a) {
        /* Deleted attributes: they existed in the old array, but not
         * in the new one. */
        if(isset($oldobject[$a]) && $oldobject[$a]['count'] > 0 && (
          !isset($newobject[$a]) || (isset($newobject[$a]) && (sizeof($newobject[$a]) == 0) || empty($newobject[$a][0])) )) {
              $info_del[$a] = array();
              // fb("Deleted array Attribute: ".$a."");
              continue;
        }

        /* Modified or new attributes. */
        $changeFlag = false;
        if (!isset($oldobject[$a]) && !empty($newobject[$a])) {
            // fb("changeFlag1 = true");
            $changeFlag = true;
        }
        if(isset($oldobject[$a]) && $oldobject[$a]['count'] != sizeof($newobject[$a])) {
            // fb("changeFlag2 = true");
            $changeFlag = true;
        }
        if(isset($oldobject[$a]) && $oldobject[$a]['count'] == sizeof($newobject[$a])) {
            for($i=0; $i<$oldobject[$a]['count']; $i++ ) {
                if($oldobject[$a][$i] != $newobject[$a][$i]) {
                    // fb("changeFlag3 = true");
                    $changeFlag = true;
                    break;
                }
            }
        }

        if($changeFlag) {
            // fb("New / Changed Attribute: $a = ".print_r($newobject[$a])."");
            $count = 0;
            foreach($newobject[$a] as $val) {
                $val2 = trim($val);
                if(!empty($val2)) {
                    $info_mod[$a][$count] = directory_string_convert($val2, 'UTF-8', $charset);
                    $count++;
                }
            }
        }

        /*
        if(is_array($newobject[$a])) {
            fb("<B>Array Attribute: $a = "; print_r($newobject[$a]); echo "</B><BR>");
            $info_mod[$a] = $newobject[$a];
        }

        if( isset($oldobject[$a]) && $oldobject[$a]['count']>0 && empty($newobject[$a])) {
            fb("<B>Deleted Attribute: ".$a."</B><BR>");
            $info_del[$a] = array();
        }
         */
    }
    return array($info_mod, $info_del, $errmsg);
}

/**
 * Has the name or the email address changed? This is to determine if we need to
 * refresh the main addressbook table rows that contain these fields.
 *
 * @param array $info_mod
 * @param array $info_del
 * @return boolean
 */
function addressbook_ui_enhancements_has_main_row_data_changed(&$info_mod, &$info_del) {
    $attrs_to_look_for = array('cn', 'givenname', 'sn', 'mail', 'uid', 'displayname');
    foreach($attrs_to_look_for as $attr) {
        if(isset($info_mod[$attr])  && !empty($info_mod[$attr])) {
            return true;
        }
        if(isset($info_del[$attr])  && !empty($info_del[$attr])) {
            return true;
        }
    }
    return false;
}

/**
 * Return the most friendly string from an LDAP-style object to identify it
 *
 * @param array $object
 * @return string
 */
function addressbook_ui_enhancements_friendly_name($object) {
    if(isset($object['cn']) && !empty($object['cn'][0])) {
        return $object['cn'][0];

    } elseif(isset($object['sn']) && !empty($object['sn'][0])) {
        if(isset($object['givenname']) && !empty($object['givenname'][0])) {
            return $object['givenname'][0] . ' ' . $object['sn'][0];
        } else {
            return $object['sn'][0];
        }
    } elseif(isset($object['displayname']) && !empty($object['displayname'][0])) {
        return $object['displayname'][0];

    } else {
        return _("(Unknown Entry)");
    }
}

