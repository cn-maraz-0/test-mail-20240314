<?php
/**
 * javascript.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: javascript.php,v 1.7 2005/04/19 17:01:57 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Function to include JavaScript code.
 *
 * Some stuff have been copied from Squirrelmail's src/addrbook_search.php
 *
 * @param string $pfn Parent Form Name.
 * @param string $pin Parent Form's Input Name.
 * @return string
 */
function directory_insert_javascript($pfn = 'compose', $pin = 'newuser') {
	if($pfn == 'compose') {
	/* Compose Form. The input name is ignored, and the send_to, send_cc etc.
	 * input names are used. */

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

	} else {

	/* Other form, for example could be useracl plugin, that supplies its
	 * formname and inputname. */

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

	}
	return $js;
}

/*
 * Javascript code for mapping of affiliation to titles functionality.
 *
 * This function returns Javascript code, that is needed to display select
 * checkboxes of different titles per eduPersonAffiliation. It probably needs
 * tweaking for different environments.
 *
 * @return string
 */
function affiliation_title_javascript() {
	global $affiliate_titles, $titles, $ldq_affiliation, $ldq_title,
	$ldq_lang;

	if(!isset($titles)) {
		return;
	}

$out = '
var items = new Array();
';

/* 'any' */
$out .= 'items["any"] = new Array(';

$output[] = '"' . _("Any") . '"';
foreach($titles as $no) {
	$output[] = '"'.$no[$ldq_lang].'"';
}
$out .= implode(',', $output) .  ");\n";
unset($output);


/* rest */
foreach($affiliate_titles as $title=>$numbers) {
	$out .= 'items["'.$title.'"] = new Array(';
	$output[] = '"' . _("Any") . '"';
	foreach($numbers as $no) {
		if(isset($titles[$no])) {
			$output[] = '"'.$titles[$no][$ldq_lang].'"';
		}
	}
	$out .= implode(',', $output) .  ");\n";
	unset($output);
}
$out .= '

function changeItems() {
	num=document.dirsearchform.ldq_affiliation.options[document.dirsearchform.ldq_affiliation.selectedIndex].value; 
	document.dirsearchform.ldq_title.options.length = 0; 
	for(i=0; i<items[num].length; i++){ 
		document.dirsearchform.ldq_title.options[i] = new Option(items[num][i], items[num][i]); 
	} 
}

function initItems(num, title) {
	if(!num) {
		num = "any";
	}
	if(!title) {
		title = "any";
	}
	document.dirsearchform.ldq_title.options.length = 0; 
	for(i=0; i<items[num].length; i++){ 
		document.dirsearchform.ldq_title.options[i] = new Option(items[num][i], items[num][i]); 
		//document.write("comparing" +  document.dirsearchform.ldq_title.options[i] + title);
		if(document.dirsearchform.ldq_title.options[i] == title) {
			document.dirsearchform.ldq_title.options[i].selected = true;
		}
	} 
}
';

$out .= "\n\n";


return $out;

}

?>
