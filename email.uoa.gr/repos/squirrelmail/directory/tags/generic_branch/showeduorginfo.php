<?php
/**
 * showeduorginfo.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: showeduorginfo.php,v 1.16 2005/04/19 17:01:57 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Show information about a specific eduOrg Organizational Unit. To be called
 * by main directory service.
 */

/**
 * Define's and include's
 */
include_once ('config.php');

if($ldq_standalone) {
	include_once ('standalone/standalone.php');
} else {
	$public_mode = false;
	define('SM_PATH', "../../");
	define('DIR_PATH', SM_PATH . "plugins/directory/");
	include_once (SM_PATH . 'include/validate.php');
	include_once (SM_PATH . 'include/load_prefs.php');
	$language = $lang_iso = getPref($data_dir, $username, 'language');
}

define('DIRECTORY_DEBUG', 0);

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "functions.php");
include_once (DIR_PATH . "constants.php");

sqgetGlobalVar('formname', $formname, SQ_FORM);
sqgetGlobalVar('inputname', $inputname, SQ_FORM);
sqgetGlobalVar('popup', $popup, SQ_GET);

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "custom/$ldq_custom.php");
}
  	
if(isset($_SESSION['orgs'])) {
	$orgs = $_SESSION['orgs'];
	$orgs3 = $_SESSION['orgs3'];
}

if(isset($_GET['dn'])) {
	$ldq_dn = urldecode($_GET['dn']);
} else {
	if(DIRECTORY_DEBUG == 1) {
		/* Insert here your dn's for testing purposes. */
		$ldq_dn[] = 'ou=NetOpeCen,ou=Admin,dc=edu-net,dc=gr';
		$ldq_dn[] = 'ou=Phy,ou=Sci,ou=Schools,dc=edu-net,dc=gr';
		$ldq_dn[] = 'ou=SolStaPhy,ou=Phy,ou=Sci,ou=Schools,dc=edu-net,dc=gr';
	} else {
		print "No Organizational Unit Specified.";
		exit;
	}
}

$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

$ldq_lds = 0;
if(isset($_GET['lds'])) {
	/* TODO: Verify that we are allowed to ask this LDAP server... */
	if(is_numeric($_GET['lds'])) {
		$ldq_lds = $_GET[$ldq_lds];
	}
}

$ldq_Server = $ldap_server[$ldq_lds]['host'];
$ldq_Port = $ldap_server[$ldq_lds]['port'];
$ldq_base = $ldap_server[$ldq_lds]['base'];
$ldq_maxrows = $ldap_server[$ldq_lds]['maxrows'];
$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];

if(isset($ldap_server[$ldq_lds]['binddn'])) {
	$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
}
if(isset($ldap_server[$ldq_lds]['bindpw'])) {
	$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];
}

if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
	print ("Could not connect to LDAP server " . $ldq_Server);
	exit;
}
if(isset($ldq_bind_dn)) {
	if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
		print ("Unable to bind to LDAP server<BR>\n");
		exit;
	}
}

$ldq_tattr = $ldq_enable_ou_attrs;

foreach ($ldq_tattr as $attr) {
	/* Additional attributes */
	if(isset($ldq_attributes[$attr]['additional_attrs'])) {
		foreach($ldq_attributes[$attr]['additional_attrs'] as $additional) {
			$ldq_tattr[] = $additional;
		}
	}
}


/* Perform search for relevant dn */
$old_error_reporting = error_reporting(E_ALL & ~(E_WARNING | E_NOTICE )); 
$ldq_filter = directory_build_filter_from_dn($ldq_dn);

if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter, $ldq_tattr, 0, $ldq_maxrows, $ldq_timeout))) {
	print '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
}
error_reporting($old_error_reporting);
$ldq_errno = ldap_errno($ldq_ldap);

if($ldq_errno == 4) {
	$toomanyfound = true;
	print '<p align="center"><strong>'.
		_("Warning: Too many results were found, a partial list follows.").
		'</strong><br/>'.
		_("You may redefine your search to get more specific results.") . '</p><br />';
}
   
$ldq_entry = ldap_get_entries($ldq_ldap, $ldq_result);
sanitize_entry_array($ldq_entry);

/**
 * Perform second-level search if there are followme attributes (for the
 * SUPERIOR org unit, i.e)
 */
$followme_attrs = array();
foreach($ldq_tattr as $attr) {
	if(isset($ldq_attributes[$attr]['followme'])) {
		$followme_attrs[] = $attr;
	}
}
if(!empty($followme_attrs)) {
	$followme_search = array();
	foreach($followme_attrs as $followattr) {
		for($i=0; $i<$ldq_entry['count']; $i++) {
			if(isset($ldq_entry[$i][$followattr])) {
				$followme_search[] = $ldq_entry[$i][$followattr][0];
			}
		}
	}
}
$ldq_dn2 = array();
if(!empty($followme_search)) {
	foreach($followme_search as $followattr) {
		$url = parse_url($followattr);
		$ldq_dn2[] = str_replace('/', '', $url['path']);
	}

	if(!empty($ldq_dn2)) {
		$ldq_filter = directory_build_filter_from_dn($ldq_dn2);
		if (!($ldq_result2 = ldap_search($ldq_ldap, $ldq_base, $ldq_filter, $ldq_tattr, 0, $ldq_maxrows, $ldq_timeout))) {
			print "Could not follow you.";
		}
		$ldq_entry2 = ldap_get_entries ($ldq_ldap, $ldq_result2);
	}
}
ldap_close($ldq_ldap);



/* Formname & Inputname, to link to other pages such as useracl */
$restofurl = '';
if(!empty($formname)) {
	$restofurl .= '&amp;formname='.urlencode($formname);
}
if(!empty($inputname)) {
	$restofurl .= '&amp;inputname='.urlencode($inputname);
}
if(isset($popup) && $popup == 1) {
	$restofurl .= '&amp;popup=1';
}

/**
 * ------ Print results (presentation logic) ------ 
 */

if ($compose_new_win == '1') {
	/* I am a popup window */
	displayHtmlHeader(_("Organizational Unit Information"), '', false);
} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');

	if($ldq_standalone) {
		displayPageHeader($color, 'None', 'no');
	} else {
		displayPageHeader($color, 'None');
	}
	$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
	textdomain ('directory');
}

$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];

directory_print_all_sections_start();
directory_print_section_start('<small>'. _("Directory Service") . '</small> - ' . _("Organizational Unit Information") );

print '<table border="0" width="100%" cellspacing="2" cellpadding="5">';

foreach($ldq_tattr as $attr) {
	
	/* Don't display hidden fields */
	if(isset($ldq_attributes[$attr]['hide'])) {
		continue;
	}

	$attr_lang = $attr.';lang-'.$ldq_lang;
	
	/* 'header' column */
	echo '<tr><td width="25%" align="right" bgcolor="'.$color[12].'"><small>'  .
		$ldq_attributes[$attr]['text'] . '</small></td>';
	
	/* value columns */
	for($i = 0; $i<$ldq_entry['count']; $i++) {

		if(array_key_exists($attr_lang, $ldq_entry[$i]) &&
		     !is_null($ldq_entry[$i][$attr_lang][0]) &&
	             ($ldq_entry[$i][$attr_lang][0] != " ") ) {
		     
			$val = directory_string_convert($ldq_entry[$i][$attr_lang][0], "UTF-8", $charset);

		} elseif(array_key_exists($attr, $ldq_entry[$i])) {
			$val = $ldq_entry[$i][$attr][0];
		} else {
			$val = '';
	        }

		echo '<td width="*" align="left" bgcolor="'.$color[4].'">';

		if(isset($ldq_attributes[$attr]['important'])) {
			print '<strong>';
		}

		if(isset($ldq_attributes[$attr]['followme']) && isset($ldq_entry[$i][$attr])) {
			for($j=0; $j<$ldq_entry2['count']; $j++) {
				$url = parse_url($ldq_entry[$i][$attr][0]);
				$askdn = str_replace('/', '', $url['path']);

				if(isset($orgs[$askdn])) {
					/* Get from $orgs cache */
					$askdn_desc = '<small>('. htmlspecialchars($orgs[$askdn]['struct']) . ')</small> '. htmlspecialchars($orgs[$askdn]['text']);
				} else {
					/* ask LDAP - FIXME */
					if($ldq_entry2[$j]['dn'] == $askdn) {
						$attr2 = 'cn';
						$attr2_lang = $attr2.';lang-'.$ldq_lang;
						
		   				if(isset($ldq_entry2[$j][$attr2_lang][0]) &&
		   			   	!empty($ldq_entry2[$j][$attr2_lang][0]) &&
			           	$ldq_entry2[$j][$attr2_lang][0] != " " ) {
							$val = $ldq_entry2[$j][$attr2_lang][0];
						} else {
							$val = $ldq_entry2[$j][$attr2][0];
	
						}
						$askdn_desc = htmlspecialchars(directory_string_convert($val, "UTF-8", $charset));
						break;
					}
				}
			}
			print '<a href="showeduorginfo.php?dn='.urlencode($askdn).$restofurl.'">'.$askdn_desc.'</a>';
		} else {
			print directory_href($attr, $val);
		}
		
		if(isset($ldq_attributes[$attr]['important'])) {
			print '</strong>';
		}
		
		print '</td>';
	}
	print '</tr>';
}

if($ldq_standalone) {
	$sf = 'no';
} else {
	$sf = 'yes';
}

print '<tr><td colspan="2" align="center">'.
	_("In this Organizational Unit:")  .
	' <strong><a href="directory.php?searchform='.$sf.'&amp;browseorgdn='.urlencode($ldq_dn).$restofurl.'&amp;mode=2">'.
	'<img src="images/people.gif" align="center" border="0" /> ' .
	_("Browse All People") .'</a></strong> '.

	'<strong><a href="directory.php?searchform=yes&amp;restrict[]='.urlencode($ldq_dn).$restofurl.'&amp;mode=2"';
	
if($ldq_standalone) {
	print ' target="dirbrowse"';
}

print	'><img src="images/search-16.gif" align="center" border="0" /> ' .
	_("Search for People") .'</a></strong>'.

	'<p>'. _("Browse people with specific affiliation:");

	foreach($affiliations_map as $aff=>$text) {
		print '<a href="directory.php?searchform='.$sf.'&amp;browseorgdn='.urlencode($ldq_dn).$restofurl.
		'&amp;affiliates='.$aff.'&amp;mode=2">'.$text.'</a> ';
	}

	print '</p>';
	
	'</td></tr>';

print "</table>\n";

directory_print_section_end();
directory_print_all_sections_end();

if($ldq_standalone) {
	echo '<center><a href="javascript:history.go(-1);">'. _("Back to Search Results") .'</a></center>';
} elseif ($compose_new_win == '1' && isset($_GET['addacl'])) {
	print '<center>'.
		'<input type="button" name="close" onClick="return self.close()" VALUE='._("Close").'/>'.
		'</center>';
}

echo '</body></html>';

?>
