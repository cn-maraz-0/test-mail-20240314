<?php
/**
 * Autocomplete for Squirrelmail
 *
 * @package plugins
 * @subpackage javascript_autocomplete
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/addressbook.php');
require_once(SM_PATH . 'functions/html.php');

/** Includes of configuration and ldap schemata */
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/config.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/constants.inc.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/html_components.inc.php');

function javascript_autocomplete_compose_header() {
    global $data_dir, $username, $javascript_on, $devel_host;

    $method = getPref($data_dir, $username, 'javascript_autocomplete_options', 2);
    if($javascript_on) {
        if(isset($devel_host) && $devel_host) {
            echo '<script src="../plugins/javascript_autocomplete/javascripts/jquery-1.4.4.js" charset="utf-8"></script>
                  <script src="../plugins/javascript_autocomplete/javascripts/jquery.autoSuggest.js" charset="utf-8"></script>';
        } else {
            echo '<script src="../plugins/javascript_autocomplete/javascripts/jquery-1.4.4.min.js" charset="utf-8"></script>
                  <script src="../plugins/javascript_autocomplete/javascripts/jquery.autoSuggest.minified.js" charset="utf-8"></script>';
        }

        echo '
        <link rel="stylesheet" type="text/css" href="../plugins/javascript_autocomplete/styles/autoSuggest.css"></link>
        <link rel="stylesheet" type="text/css" href="../plugins/javascript_autocomplete/styles/autoSuggestUoa.css"></link>
        ';
        if(preg_match('/msie/', strtolower($_SERVER['HTTP_USER_AGENT']), $matches)) { 
            echo '<link rel="stylesheet" type="text/css" href="../plugins/javascript_autocomplete/styles/autoSuggestUoaIE.css"></link>';
        }
    }
}


function javascript_autocomplete_compose_main() {
    global $data_dir, $username, $plugins;
    
    $method = getPref($data_dir, $username, 'javascript_autocomplete_options', 2);
    // method will be:
    // 0 -> disable
    // 1 -> only for local addresses
    // 2 -> local addresses + directory (default)

    if($method == 0) return;
    
    echo <<<JJSS
<script language="javascript" type="text/javascript">
$(document).ready(function() { 
    var prefillto = '', prefillcc = '', prefillbcc = '';
    prefillto = $("input[name=send_to]")[0].value.replace(/\</g, '&lt;').replace(/\>/g, '&gt;');
    prefillcc = $("input[name=send_to_cc]")[0].value.replace(/\</g, '&lt;').replace(/\>/g, '&gt;');
    prefillbcc = $("input[name=send_to_bcc]")[0].value.replace(/\</g, '&lt;').replace(/\>/g, '&gt;');

JJSS;

    if($method == 1) {
        $abook = addressbook_init(true, true);
        $abookentries = $abook->backends[1]->list_addr('*');

        echo <<<JJSS

    var abookdata = {items: [
JJSS;
        foreach($abookentries as $entry) {
            if(!isset($entry['email'])) continue;
            
            if(strpos($entry['email'], ',')) {
                // many email addresses; split them.
                // suboptimal atm ,but gets the job somewhat done.
                $splits = preg_split('/,/', $entry['email']);
                $disp = $entry['firstname']." ".$entry['lastname'] . ' ' . sprintf( _("(%s recipient addresses)"), count($splits));
                $full = $entry['email'];
            } else {
                $disp = $full = $entry['firstname']." ".$entry['lastname'] . " &lt;".$entry['email']."&gt;"; 
            }
            //$disp = $entry['firstname']." ".$entry['lastname'];
            echo '{value: "'.$full.'", name: "'.$disp.'"},'."\n";
        }
        
        echo <<<JJSS
    ]};
JJSS;
    }
        
    echo <<<JJSS
    var commonOptions = {
        //minChars: 2,
        startText: '',
        selectedItemProp: "name",
        searchObjProps: "name",
        neverSubmit: true,
        resultsHighlight: true,
        retrieveLimit: 10,
        formatList: function(data, elem) {
            var src = data.src, prefix = '', suffix = '', new_elem;
            if(src == 'pab') {
                prefix = '<img src="../plugins/famfamfam/icons/vcard.png" alt="" /> ';
            } else if (src == 'directory') {
                prefix = '<img src="../plugins/famfamfam/icons/building.png" alt="" /> ';
                if(data.org) {
                    suffix = ' <small>(' + data.org + ')</small>';
                }
            }
            new_elem = elem.html(prefix + data.name + suffix);
            return new_elem;
        }
    };
    var send_to_options = {
        asHtmlID: 'send_to',
        preFill: prefillto
    }
    var send_to_cc_options = {
        asHtmlID: 'send_to_cc',
        preFill: prefillcc
    }
    var send_to_bcc_options = {
        asHtmlID: 'send_to_bcc',
        preFill: prefillbcc
    }
    for (var opt in commonOptions) { send_to_options[opt] = commonOptions[opt]; }
    for (var opt in commonOptions) { send_to_cc_options[opt] = commonOptions[opt]; }
    for (var opt in commonOptions) { send_to_bcc_options[opt] = commonOptions[opt]; }
        

JJSS;

    if($method == 1) {
        echo <<<JJSS
        $("input[name=send_to]").autoSuggest(abookdata.items, send_to_options);
        $("input[name=send_to_cc]").autoSuggest(abookdata.items, send_to_cc_options);
        $("input[name=send_to_bcc]").autoSuggest(abookdata.items, send_to_bcc_options);

JJSS;

    } elseif($method == 2) {
        echo <<<JJSS
        $("input[name=send_to]").autoSuggest('../plugins/javascript_autocomplete/ajax_handler.php', send_to_options);
        $("input[name=send_to_cc]").autoSuggest('../plugins/javascript_autocomplete/ajax_handler.php', send_to_cc_options);
        $("input[name=send_to_bcc]").autoSuggest('../plugins/javascript_autocomplete/ajax_handler.php', send_to_bcc_options);

JJSS;
    }

    echo <<<JJSS
});
    
</script>
JJSS;
}

