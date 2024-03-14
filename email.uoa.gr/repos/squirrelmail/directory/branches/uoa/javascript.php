<?php
/**
 * javascript.php
 *
 * Copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: javascript.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Function to include JavaScript code.
 * Copied from src/addrbook_search.php
 * @param string $parent_type
 */
function directory_insert_javascript($parent_type = '') {
//   <SCRIPT LANGUAGE="Javascript"><!--

    if(empty($parent_type)) {
        $parent_type = 'compose';
    }

    switch($parent_type) {
        case 'compose':
        case 'useracl':
       }
    $js = '

    function to_and_close($addr) {
        to_address($addr);
        parent.close();
    }

    function to_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to.value =
                    parent.opener.document.compose.send_to.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to.value = $addr;
            }
        }
    }

    function cc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_cc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_cc.value =
                    parent.opener.document.compose.send_to_cc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_cc.value = $addr;
            }
        }
    }

    function bcc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_bcc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_bcc.value =
                    parent.opener.document.compose.send_to_bcc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_bcc.value = $addr;
            }
        }
    }

';
// // --></SCRIPT>

return $js;
} /* End of included JavaScript */


/**
 * Function to include JavaScript code.
 * @param string $pfn Parent Form Name.
 * @param string $pin Parent Form's Input Name.
 */
function directory_insert_javascript_custom($pfn = 'compose', $pin = 'newuser') {

    $js = '

    function add_and_close($addr) {
        add_text($addr);
        parent.close();
    }

    function add_text($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.'.$pfn.';

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.'.$pfn.'.'.$pin.'.value) {
                prefix = ", ";
                parent.opener.document.'.$pfn.'.'.$pin.'.value =
                    parent.opener.document.'.$pfn.'.'.$pin.'.value + ", " + $addr;
            } else {
                parent.opener.document.'.$pfn.'.'.$pin.'.value = $addr;
            }
        }
    }
';

return $js;
}

?>
