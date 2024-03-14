<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * This devhelper page just shows various session variables pretty-printed
 * via dumpr() function.
 *
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: devhelper_variables.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
 * @package plugins
 * @subpackage devhelper
 */

/** Includes */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'include/load_prefs.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/date.php');
}

include_once(SM_PATH . 'plugins/devhelper/config.php');
include_once(SM_PATH . 'plugins/devhelper/include/dumpr.php');
include_once(SM_PATH . 'plugins/devhelper/include/functions.inc.php');

$js = '<script language="Javascript" type="text/javascript"><!--' . "\n" .
<<<JSSNIPPET
function el(id) {
  if (document.getElementById) {
    return document.getElementById(id);
  }
  return false;
}
function ShowDiv(divname) {
  if(el(divname)) {
    el(divname).style.display = "block";
  }
  return false;
}
function HideDiv(divname) {
  if(el(divname)) {
    el(divname).style.display = "none";
  }
	  if(el('divstate_'+divname)) {
	  	el('divstate_'+divname).value = 0;
	  }
}
function HideTab(divname) {
	if(el(divname)) {
		img_name = divname + '_img';
		el(divname).style.display = "none";
		if(document[img_name]) {
			document[img_name].src = "images/triangle.gif";
		}	
	}
	  if(el('divstate_'+divname)) {
	  	el('divstate_'+divname).value = 0;
	  }
}
function ToggleShowDiv(divname) {
	
	/* var divstate = 'divstate_' + divname; */

  if(el(divname)) {
    img_name = divname + '_img';
    if(el(divname).style.display == "none") {
      el(divname).style.display = "";
	  if(document[img_name]) {
	  	document[img_name].src = "images/opentriangle.gif";
	  }	
	  if(el('divstate_' + divname )) {
	  	el('divstate_'+divname).value = 1;
	  }
	} else {
      el(divname).style.display = "none";
	  if(document[img_name]) {
	  	document[img_name].src = "images/triangle.gif";
	  }	
	  if(el('divstate_'+divname)) {
	  	el('divstate_'+divname).value = 0;
	  }
	}
  }	
}
function arrowtoggleimg(img_name) {
	if(document[img_name].src.search('triangle.gif') != -1) {
		document[img_name].src = "images/opentriangle.gif";
	} else {
		document[img_name].src = "images/triangle.gif";
	}
}
JSSNIPPET
. "// --></script>";

$session_vars = array(
    'list_cache' => 'LIST Cache',
    'listsub_cache' => 'LSUB Cache',
    'delimiter' => 'Def. Mailbox delimiter',
    'sqimap_capabilities' => 'IMAP4 Capabilities',
    'sqimap_namespace' => 'IMAP4 Namespace',
    'prefs_cache' => 'Prefs Cache',
    'boxesnew' => 'IMAP Mailboxes',
    'template_file_hierarchy' => 'Template File Hierarchy',
    'mailbox_cache' => 'Mailbox Cache'
);
if(in_array('ldapfolderinfo', $plugins)) {
    $session_vars['ldapfolderinfo'] = 'LDAP Folder Information';
}
if(in_array('ldapuserdata', $plugins)) {
    $session_vars['ldap_prefs_cache'] = 'LDAP Prefs Backend Cache';
    $session_vars['prefs_before'] = 'Last-Saved LDAP Prefs';
    $session_vars['ldapidentities'] = 'LDAP Identities';
    $session_vars['identities_map'] = 'LDAP Identities Map';
    $session_vars['ludObjectClasses'] = 'LDAP Objectclasses';
    $session_vars['ludDisabledServices'] = 'LDAP Disabled Services';
}
if(in_array('avelsieve', $plugins)) {
    $session_vars['rules'] = 'Avelsieve Rules';
    $session_vars['scriptinfo'] = 'Avelsieve Script Info';
}

if(!empty($devhelper_session_vars)) {
    foreach ($devhelper_session_vars as $n=>$d) {
        $session_vars[$n] = $d;
    }
}


/* Output starts here */

displayHtmlHeader("DevHelper - Session Variables", $js, false, true);

$c = 0;
foreach($session_vars as $v=>$t) {
    echo devhelper_togglediv_link($v, $t, true, array_keys($session_vars)) . ' &nbsp;';
    $c++; if($c % 5 == 0) echo '<br/>';
}

foreach($session_vars as $v=>$t) {
    echo '<div id="'.$v.'" style="display:none">
        <h3>'.$t.' ($_SESSION[\''.$v.'\'])</h3>' .
        (isset($_SESSION[$v]) ? dumpr($_SESSION[$v], true) : '<em>Not set</em>') .
        '</div>';
}

?>
