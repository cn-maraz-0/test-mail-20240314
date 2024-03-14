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
 * Prints the javascript functions for addressbook enhancements.
 *
 * @return string
 */
function addressbook_ui_javascripts_main() {
    global $color, $base_uri;
    
    sq_bindtextdomain('addressbook_ui_enhancements', SM_PATH . 'plugins/addressbook_ui_enhancements/locale');
    textdomain ('addressbook_ui_enhancements');

    include_once(SM_PATH .'plugins/uoa_enhancements/functions_common.php');
    $ua = uoa_browser_info();
    $msie = false;
    if(isset($ua['msie'])) {
        $msie = true;
    }
    
    $out = "    
    Event.observe(window, 'load', function() {
        if($('FormAddrsAbook') != null) {
            $('FormAddrsAbook').observe('click', respondToClick);
            $('FormAddrsAbook').observe('mouseover', respondToMouseOver);
            $('FormAddrsAbook').observe('mouseout', respondToMouseOut);

        }
        if($('abook_add_new_start') != null) {
            $('abook_add_new_start').observe('click', addNewContactStart);
        }
    });

    function determineMyIdFromGenericId(genericId) {
        var splitPos = genericId.indexOf(':');
        if(splitPos > 0) {
            // FIXME - remove the 1:
            return '1:' + genericId.substr(splitPos+1);
        }
        return null;
    }

    function respondToMouseOver(event) {
        var element = event.element();
        myId = determineMyIdFromGenericId(element.parentNode.id);

        if(myId == null) return;

        $('abook_main_row_' + myId).style.background = '".$color[16]."';
        $('abook_main_row_' + myId).style.cursor = 'pointer';
    }

    function respondToMouseOut(event) {
        var element = event.element();
        myId = determineMyIdFromGenericId(element.parentNode.id);

        if(myId == null) return;

        row = document.getElementById('abook_info_row_' + myId);

        if(row.style.display != 'table-row' && row.style.display != 'block') {
            // Not expanded
            restoreOriginalRowColor(myId);
        } else {
            // Expanded
            var newcolor = '".$color[0]."';
            $('abook_main_row_' + myId).style.background = newcolor;
        }   

        // $('abook_main_row_' + myId).style.cursor = 'default';
    }


    function respondToClick(event) {
        var element = event.element();
        var myId = null;
        myId = determineMyIdFromGenericId(element.parentNode.id);

        if(element.hasClassName('abook_ui_expand_link')) {
            // TODO: extra argument of line number
            addressbookUiToggleExpandInfo(myId, 1);
        }
    }

    function addressbookUiLoading(myId) {
          Element.update('abook_extended_info_' + myId, '<img src=\\'".$base_uri."plugins/javascript_libs/images/loading.gif\\' />');
    }
    
    function addressbookUiAddNew() {
            new Ajax.Updater('abook_add_new',
               '".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=addnew', {
               method:'get',
               onComplete: function(transport) {
                   Effect.ScrollTo('FormAddNew');
               }
            });
    }
    function addressbookUiResetAddNew() {
        $('abook_add_new').innerHTML = '<input type=\"button\" name=\"abook_add_new_start\" id=\"abook_add_new_start\" onclick=\"addNewContactStart();\" value=\"" ._("Add a new Contact"). "\" />';
    }
    
    function addressbookUiToggleExpandInfo(myId, line) {
        var row = document.getElementById('abook_info_row_' + myId);
        if(row.style.display != 'table-row' && row.style.display != 'block') {
    ";
    if($msie) {
        $out .= " row.style.display = 'block'; ";
    } else {
        $out .= "
            try {
                row.style.display='table-row';
            } catch(e) {
                row.style.display = 'block';
            }";
    }
    $out .= "
            document.getElementById('abook_main_row_' + myId).style.background = '".$color[0]."';
            new Ajax.Updater('abook_extended_info_' + myId,
               '".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=retrieve&pabobject=' + encodeURIComponent(myId), {
               method:'get'
            });

        } else {
            new Effect.BlindUp('abook_info_row_' + myId, {duration: 0.4});
            restoreOriginalRowColor(myId);
            setTimeout('addressbookUiLoading(\\'' + myId + '\\');', 500);
       }
    }
        
    function restoreOriginalRowColor(myId) {
        if($('abook_main_row_' + myId).hasClassName('abook_row_alternate')) {
            var newcolor = '".$color[12]."';
        } else {
            var newcolor = '".$color[4]."';
        }
        $('abook_main_row_' + myId).style.background = newcolor;
    }
    
    function addressbookUiShow(myId) {
        new Ajax.Updater('abook_extended_info_' + myId, '".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=retrieve&pabobject=' + encodeURIComponent(myId), {
           method:'get'
        });
    }
    
    function addressbookUiEdit(myId) {
        // addressbookUiLoading(myId);
        new Ajax.Updater('abook_extended_info_' + myId,
            '".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=edit&pabobject=' + encodeURIComponent(myId), {
            method:'get'
        });
    }
    
    function addressbookUiDelete(myId) {
        new Ajax.Request('".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=delete&pabobject=' + encodeURIComponent(myId), {
           method:'post',
           onSuccess: function(transport){
                var responseraw = transport.responseText || 'no response text';
                var responseArray=responseraw.split('||');
                    if(responseArray[0] != null) {
                        var response = responseArray[0];
                    }
                    if(responseArray[1] != null) {
                        var infoText = responseArray[1];
                    }
                    if(responseArray.length == 3 && responseArray[2] != null) {
                        var errorMsg = responseArray[2];
                    }

                if(response == ".ABOOK_UI_DELETED_SUCCESSFULLY.") {
                    var successMsg = '<img src=\"../plugins/famfamfam/images/icons/tick.png\" alt=\"\" /> ' +
                        '". _("Entry for %s has been deleted successfully.") ." <br/>".
                        _("Click on &quot;Refresh Addressbook&quot; link to update the contacts list.") ."';
                    var msg = successMsg.replace(/%s/, infoText);
                    addressbookUiMsg(msg, 'info');
                    $('abook_message_refresh').style.display = 'block';
                    setTimeout('addressbookUiToggleExpandInfo(\'' + myId + '\', 1)', 1000);
                }
                if(response == ".ABOOK_UI_ERROR_DURING_DELETE.") {
                    var errorMsg1 = '". _("Error while deleting entry for %s.") ."';
                    var errorMsg2 = '". _("Error message: %s") ."';
                    var msg = errorMsg1.replace(/%s/, infoText) + '<br/>' + errorMsg2.replace(/%s/, errorMsg);;
                    addressbookUiMsg(msg, 'error');
                    setTimeout('addressbookUiToggleExpandInfo(myId, 1)', 1000);
                }
          },
          onFailure: function(){ alert('Error: Could not delete entry.') }
        });
    }

    function addressbookUiSave(myId) {
        var formname = 'FormAddrsAbook';
        var addnew = false;
        if(myId == '1:0') {
            formname = 'FormAddNew';
            addnew = true;
        }

        new Ajax.Request('".$base_uri."plugins/addressbook_ui_enhancements/addressbook_handler.php?pabaction=save&pabobject=' + encodeURIComponent(myId), {
           method:'post',
           parameters: $(formname).serialize(true),
           onSuccess: function(transport){
                var responseraw = transport.responseText || 'no response text';
                var responseArray=responseraw.split('||');

                    if(responseArray[0] != null) {
                        var response = responseArray[0];
                    }
                    if(responseArray[1] != null) {
                        var infoText = responseArray[1];
                    }
                    if(responseArray.length == 3 && responseArray[2] != null) {
                        var errorMsg = responseArray[2];
                    }

                if(response == ".ABOOK_UI_SAVED_SUCCESSFULLY.") {
                    var successMsg = '<img src=\"../plugins/famfamfam/images/icons/tick.png\" alt=\"\" /> '+
                        '". _("Entry for %s has been Saved Successfully.") ."';
                    var msg = successMsg.replace(/%s/, infoText);
                    addressbookUiMsg(msg, 'info');
                    setTimeout('addressbookUiToggleExpandInfo(\'' + myId + '\', 1)', 1000);
                }
                if(response == ".ABOOK_UI_SAVED_SUCCESSFULLY_UPDATE_NEEDED.") {
                    if(addnew) {
                        // Force a refresh of the page.
                        document.location = 'addressbook.php?saved=true';
                        exit;
                    }

                    var successMsg = '<img src=\"../plugins/famfamfam/images/icons/tick.png\" alt=\"\" /> ' +
                        '". _("Entry for %s has been Saved Successfully.") ." <br/>".
                        _("Click on &quot;Refresh Addressbook&quot; link to update the contacts list.") ."';
                    var msg = successMsg.replace(/%s/, infoText);
                    addressbookUiMsg(msg, 'info');
                    $('abook_message_refresh').style.display = 'block';
                    setTimeout('addressbookUiToggleExpandInfo(\'' + myId + '\', 1)', 1000);
                }   
                if(response == ".ABOOK_UI_ERROR_DURING_SAVE.") {
                    var errorMsg1 = '". _("Error while saving entry for %s.") ."';
                    if(addnew) {
                        errorMsg1 = '". _("Error while adding entry.") ."';
                    } else {
                        errorMsg1 = errorMsg1.replace(/%s/, infoText);
                    }
                    var errorMsg2 = '". _("Error message: %s") ."';
                    var msg = errorMsg1 + '<br/>' + errorMsg2.replace(/%s/, errorMsg);;
                    addressbookUiMsg(msg, 'error');
                    setTimeout('addressbookUiToggleExpandInfo(myId, 1)', 1000);
                }   
          },
          onFailure: function(){ alert('Error: Could not save entry.') }
        });
    }
    
    /**
      * Display an error or success message in a box
      */
    function addressbookUiMsg(msg, type) {
        var isMSIE = /*@cc_on!@*/false;

        if(isMSIE) {
            alert(msg.replace(/(<([^>]+)>)/ig,\"\").replace(/&quot;/ig,\"\"));
        } else {
            if(type == 'error') {
                msgStyle = {color:'red', font: 'bold', display:'block'};
            } else {
                msgStyle = {color:'green', font: 'bold', display:'block'};
            }
            $('abook_message_box').update(msg).setStyle(msgStyle);
            setTimeout('new Effect.Fade(\\'abook_message_box\\')', 6000);
        }
        return true;
    }

    function addressbookUiDisplayProfileMsg(myId, html) {
        $('profile_msgs_' + myId).innerHtml = 'lala!!!';
    }

    function addressbookUiJsError() {
        //console.log('Error during operation on object');
    }

    function addressbookUiEditFocus(id) {
        document.getElementById(id).className = 'abook_form_element_active';
    }
    function addressbookUiEditBlur(id) {
        document.getElementById(id).className = 'abook_form_element';
    }

    function addNewContactStart() {
        $('abook_add_new_start').disabled = true;
        addressbookUiAddNew();
    }
    ";


    textdomain ('squirrelmail');
    return $out;
}

