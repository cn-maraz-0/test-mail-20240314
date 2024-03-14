<?php
/**
 * browse.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Main routine for browse Organizations window
 *
 * @package directory
 * @subpackage main
 * @version $Id: browse.php,v 1.9 2005/04/19 17:01:57 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

include_once ('config.php');

if($ldq_standalone) {
	include_once ('standalone/standalone.php');
} else {
	define('SM_PATH', "../../");
	define('DIR_PATH', SM_PATH . "plugins/directory/");
	include_once (SM_PATH . 'include/validate.php');
	include_once (SM_PATH . 'include/load_prefs.php');
	$language = $lang_iso = getPref($data_dir, $username, 'language');
}

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "javascript.php");
include_once (DIR_PATH . "functions.php");
include_once (DIR_PATH . "constants.php");
include_once (DIR_PATH . "display.php");

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "custom/$ldq_custom.php");
}

/* Variable import */
sqgetGlobalVar('ldq_searchfor', $ldq_searchfor, SQ_POST);
sqgetGlobalVar('ldq_searchby', $ldq_searchby, SQ_POST);
sqgetGlobalVar('ldq_comparetype', $ldq_comparetype, SQ_POST);
sqgetGlobalVar('ldq_querystr', $ldq_querystr, SQ_POST);
sqgetGlobalVar('ldq_sortby', $ldq_sortby, SQ_POST);
sqgetGlobalVar('Submit_Button', $Submit_Button, SQ_POST);
sqgetGlobalVar('popup', $popup, SQ_GET);
sqgetGlobalVar('formname', $formname, SQ_FORM);
sqgetGlobalVar('inputname', $inputname, SQ_FORM);

$directory_output_type = getPref($data_dir, $username, "directory_output_type");
$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

if(!$ldq_standalone) {
	$location = get_location();
}

directory_LoadPrefs();

if($ldq_standalone) {
	if(isset($_GET['dn'])) {
		$xtra = 'onLoad="window.location.hash=\''.urlencode($_GET['dn']).'\'"';
	} else {
		$xtra = '';
	}
	displayPageHeader($color, _("Directory Service"), 'no');
} else {
	if(isset($popup) && $popup == 1) {
		displayHtmlHeader(_("Browse"), '', false);
	} else {
		$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
		textdomain ('squirrelmail');
		displayPageHeader($color, "None");
	}
}


$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];


if(isset($_SESSION['orgs'])) {
	$orgs = $_SESSION['orgs'];
	$orgs3 = $_SESSION['orgs3'];
} else {
	/* Orgs Not cached into session. */
	global $orgs, $orgs3;
	cache_orgunitdns();
}

if(isset($_GET['expand'])) {
	$expand = $_GET['expand'];
	$expand = array_unique($expand);
} else {
	$expand = array();
}

if(isset($_GET['collapse'])) {
	$collapse = $_GET['collapse'];
	if(in_array($collapse, $expand)) {
		while( ($k = array_search($collapse, $expand)) !== false ) {
			unset($expand[$k]);
		}
	}
}
$expand = array_values($expand);

if(isset($_GET['dn'])) {
	$current = $_GET['dn'];
	if(!in_array($current, $expand)) {
		$expand[] = $current;
	}
} else {
	$current = '';
}

/* ------------------- Presentation --------------------- */

if($ldq_standalone) {
	echo directory_navbar() . '<br/><br/>';
} else {
	directory_print_all_sections_start();
	directory_print_section_start('<small>'. _("Directory Service") . '</small> - ' . _("Browse") . ' - <small>' .directory_navbar() . '</small>');
	directory_print_section_end();
	echo '<tr><td>';
}

directory_print_browse_tree($orgs3, 0, 1, $current, $expand);

if(!$ldq_standalone) {
	echo '</td></tr>';
	directory_print_all_sections_end();
}

echo '</body></html>';

?>
