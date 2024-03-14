<?php
/**
 * vcard.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: vcard.php,v 1.8.2.2 2006/07/26 08:16:47 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Display a user's information, or send that information as a VCard to the
 * user.
 */

/**
 * Define's and include's
 */
include_once ('config.php');
define('DEBUG_MODE', 0);

if($ldq_standalone) {
	include_once ('standalone/standalone.php');
} else {
	$public_mode = false;
    if (file_exists('../../include/init.php')) {
        include_once('../../include/init.php');
    	define('DIR_PATH', SM_PATH . "plugins/directory/");
    } else if (file_exists('../../include/validate.php')) {
	    define('SM_PATH', "../../");
    	define('DIR_PATH', SM_PATH . "plugins/directory/");
	    include_once (SM_PATH . 'include/validate.php');
    	include_once (SM_PATH . 'include/load_prefs.php');
    }
	$language = $lang_iso = getPref($data_dir, $username, 'language');
}

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

if(isset($_GET['vcard'])) {
	$vcard = true;
} else {
	$vcard = false;
}

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "javascript.php");
include_once (DIR_PATH . "functions.php");
include_once (DIR_PATH . 'mailto.php');
include_once (DIR_PATH . "constants.php");
if($vcard) {
	include_once (DIR_PATH . 'vcard.lib.php');
	include_once (DIR_PATH . 'vcard_functions.php');
} else {
	include_once (DIR_PATH . 'display.php');
}

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "custom/$ldq_custom.php");
}
  	
/** ----------  Cache OrgUnitDNs ----------  */
if(isset($_GET['language'])) {
	$lang_iso = $_GET['language'];
}

$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];

if($ldq_support_eduperson) {
	if(isset($_SESSION['orgs'])) {
		$orgs = $_SESSION['orgs'];
		// $orgs2 = $_SESSION['orgs2'];
		$orgs3 = $_SESSION['orgs3'];
	} else {
		/* Orgs Not cached into session. */
		global $orgs, $orgs3;
		cache_orgunitdns();
	}
}


/**
 * Variable import
 */
if(isset($_GET['uid'])) {
	$uid = $_GET['uid'];
} else {
	exit;
}


$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

/**
 * Build search filter
 */

$ldq_finalfilter = 'uid='.$uid;
$ldq_filter = "(&";
$ldq_searchfor = 'person';
if(isset($ldq_searchobjs[$ldq_searchfor]['filter'])) {
	$ldq_filter .= '(' . $ldq_searchobjs[$ldq_searchfor]['filter'] . ')';
}
$ldq_filter .= '(' . $ldq_finalfilter . '))';

/**
 * Search LDAP for $uid
 */

for ($ldq_lds=0 ; $ldq_lds < count($ldap_server) ; $ldq_lds++) {

	if(isset($ldq_bind_dn)) {
		unset($ldq_bind_dn);
	}
	if(isset($ldq_pass)) {
		unset($ldq_pass);
	}

	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	if(!empty($ldap_server[$ldq_lds]['port'])) {
		$ldq_Port = $ldap_server[$ldq_lds]['port'];
	} else {
		$ldq_Port = 389;
	}
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxres = $ldap_server[$ldq_lds]['maxrows'];
	if(isset($ldap_server[$ldq_lds]['timeout'])) {
		$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];
	} else {
		$ldq_timeout = 60;
	}

	if(isset($ldap_server[$ldq_lds]['binddn'])) {
		$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
	}
	if(isset($ldap_server[$ldq_lds]['bindpw'])) {
		$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];
	}
         if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
	 	exit;
         }
	 if(isset($ldq_bind_dn)) {
	         if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
		 	exit;
		 }
	}
        
	/** --- Gather attributes to ask LDAP --- */
	if($public_mode == true && isset($ldq_enable_attrs_public) ) {
		$ldq_enable_attrs = $ldq_enable_attrs_public;
	}

	foreach($ldq_enable_attrs as $attr) {
		$attributes[] = $attr;
	}

	foreach ($attributes as $attr) {
		if(isset($ldq_attributes[$attr]['disabled']) &&
		  $ldq_attributes[$attr]['disabled'] == true) {
			continue;
		}

		$ldq_tattr[] = $attr;

		/** Additional attributes */
		if (isset($ldq_attributes[$attr]['additional_attrs']) &&
		  is_array($ldq_attributes[$attr]['additional_attrs']) ) {
			foreach($ldq_attributes[$attr]['additional_attrs'] as $additional) {
				$ldq_tattr[] = $additional;
			}	
		}

		/** For these we need to ask for more information with a new
		 * LDAP search... */
		if(isset($ldq_attributes[$attr]['followme'])) {
			$extra_referrer_attributes[] = $attr;
			$extra_search_attributes[] = $ldq_attributes[$attr]['followme_show'];
		}
	}

	if(!empty($extra_referrer_attributes)) { 
		$extra_referrer_attributes = array_unique($extra_referrer_attributes);
	}
	if(!empty($extra_search_attributes)) { 
		$extra_search_attributes = array_unique($extra_search_attributes);
	}

	$ldq_tattr[] = 'uid';

	if(!empty($ldq_privacy_attribute)) {
		$ldq_tattr[] = $ldq_privacy_attribute;
	}

	if(isset($ldq_searchobjs[$ldq_searchfor]['rdn'])) {
		$ldq_base = $ldq_searchobjs[$ldq_searchfor]['rdn'] . ',' . $ldq_base;
	}

	 if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,
	   $ldq_tattr, 0, $ldq_maxres, $ldq_timeout))) {
	 	print '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
        }
        $entry = ldap_get_entries ($ldq_ldap, $ldq_result);
	sanitize_entry_array($entry);
	$me = $entry[0];
	if(isset($me[$ldq_privacy_attribute]) && $me[$ldq_privacy_attribute]['count'] > 0) {
		$privateattrs = $me[$ldq_privacy_attribute];
	}
}


if($vcard) {
	/**
	 * Build vCard & Send it away
	 */
	vcget($me, 'telephonenumber');
	vcget($me, 'mail');
	vcget($me, 'homephone');
	vcget($me, 'description');
	vcget($me, 'facsimiletelephonenumber');
	vcget($me, 'sn');
	vcget($me, 'cn');
	vcget($me, 'title');
	vcget($me, 'postofficebox');
	vcget($me, 'postaladdress');
	vcget($me, 'postalcode');
	vcget($me, 'l');
	vcget($me, 'mobile');
	vcget($me, 'labeleduri');
	
	$v = new vCard();
	
	if(!empty($telephonenumber)) {
		$v->setPhoneNumber($telephonenumber, "PREF;WORK;VOICE");
	}
	if(!empty($facsimiletelephonenumber)) {
		$v->setPhoneNumber($facsimiletelephonenumber, "PREF;WORK;FAX");
	}
	if(!empty($homephone)) {
		$v->setPhoneNumber($homephone, "HOME;VOICE");
	}
	if(!empty($cn)) {
		$v->setName($sn, $cn, "", $title);
	}
	if(!empty($postaladdress)) {
		$v->setAddress($postofficebox, "", $postaladdress, $l, "", "", "", 'WORK');
	}
	if(!empty($homepostaladdress)) {
		$v->setAddress("", "", $homepostaladdress, "", "", "", "", 'HOME');
	}
	if(!empty($mail)) {
		$v->setEmail($mail);
	}
	if(!empty($description)) {
		$v->setNote($description);
	}
	// "You can take some notes here.\r\nMultiple lines are supported via \\r\\n.");
	// $v->setURL(, "WORK");
	$output = $v->getVCard();
	$filename = $v->getFileName();
	
	if(DEBUG_MODE == 1) {
		Header("Content-Type: text/html; name=$filename; charset=UTF-8");
		print '<PRE>';
		echo $output;
		print '</PRE>';
	} else {
		Header("Content-Disposition: attachment; filename=$filename");
		Header("Content-Length: ".strlen($output));
		Header("Connection: close");
		Header("Content-Type: text/x-vcard; name=$filename; charset=UTF-8");
		echo $output;
	}

} else {
	/**
	 * Show User Profile, in HTML.
	 */


if ($compose_new_win == '1') {
	/* I am a popup window */
	$popup = 1;
	$wintitle = _("Personal Information");
	$js = '';
    		/* Note: Remove type="" if it does bad... */
    		$js = '<script language="Javascript" type="text/javascript"><!--' . "\n";
		$js .= directory_insert_javascript();
    
		/* Include this too; it is not displayed in the pop-up window
		 * by default. */
       		$js .= "function comp_in_new(comp_uri) {\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank",'.
                     '"width='.$compose_width. ',height='.$compose_height.
                     ',scrollbars=yes,resizable=yes");'."\n".
                     "}\n\n";
		$js .= "// --></script>";
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayHtmlHeader($wintitle, $js);
} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');

	if($ldq_standalone) {
		displayPageHeader($color, _("Personal Information"), 'no');
	} else {
		displayPageHeader($color, 'None');
	}
	$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
	textdomain ('directory');
}

directory_print_all_sections_start();
directory_print_section_start( _("Personal Information") );

directory_dispresultsMulti($attributes, $entry, 'cn');


directory_print_section_end();
directory_print_all_sections_end();

if($ldq_standalone || !$compose_new_win) {
	print '<center><a href="javascript:history.go(-1);">'. _("Back to Search Results") .'</a></center>';
} elseif ($compose_new_win == '1') {
	print '<center><INPUT TYPE="BUTTON" NAME="Close" onClick="return self.close()" VALUE='._("Close").'>'."</center>\n";
}


	
}
?>
