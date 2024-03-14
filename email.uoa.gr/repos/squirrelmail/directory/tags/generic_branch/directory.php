<?php
/**
 * directory.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Main routine for directory window: Search Form + Search Results
 *
 * @package directory
 * @subpackage main
 * @version $Id: directory.php,v 1.33 2005/04/19 17:01:57 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
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
}

sqgetGlobalVar('language', $language, SQ_GET);
if(!isset($language)) {
	$language = $lang_iso = getPref($data_dir, $username, 'language');
}
$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "javascript.php");
include_once (DIR_PATH . "functions.php");
include_once (DIR_PATH . 'mailto.php');
include_once (DIR_PATH . "constants.php");
include_once (DIR_PATH . "display.php");

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "custom/$ldq_custom.php");
}
  	
/*
 * ============ Variable import ============ 
 */

$PHP_SELF = strip_tags($_SERVER['PHP_SELF']);
$self = basename($PHP_SELF);

if(isset($_POST['ldq_searchfor'])) {
	$ldq_searchfor = $_POST['ldq_searchfor'];
} else {
	$ldq_searchfor = $ldq_enable_searchfor[0];
}
sqgetGlobalVar('ldq_searchby', $ldq_searchby, SQ_POST);
sqgetGlobalVar('ldq_comparetype', $ldq_comparetype, SQ_POST);
sqgetGlobalVar('ldq_querystr', $ldq_querystr, SQ_POST);
sqgetGlobalVar('ldq_querystr_simple', $ldq_querystr_simple, SQ_POST);
sqgetGlobalVar('ldq_affiliation', $ldq_affiliation, SQ_POST);
sqgetGlobalVar('ldq_title', $ldq_title, SQ_POST);

if(isset($_POST['ldq_sortby'])) {
	$ldq_sortby = $_POST['ldq_sortby'];
} else {
	$ldq_sortby = $ldq_searchattrs[0];
}

sqgetGlobalVar('Submit_Button', $Submit_Button, SQ_POST);
sqgetGlobalVar('popup', $popup, SQ_FORM);
sqgetGlobalVar('formname', $formname, SQ_FORM);
sqgetGlobalVar('inputname', $inputname, SQ_FORM);

if(isset($_GET['mode'])) {
	$mode = $_GET['mode'];
} elseif(isset($_SESSION['ldq_mode'])) {
	$mode = $_SESSION['ldq_mode'];
} else {
	$mode = $ldq_default_mode;
}
$_SESSION['ldq_mode'] = $mode;

if(isset($_POST['searchform'])) {
	$searchform = $_POST['searchform'];
} elseif(isset($_GET['searchform'])) {
	$searchform = $_GET['searchform'];
} else {
	$searchform = 'yes';
}
if(isset($_GET['add']) || isset($_GET['less'])) {
	$searchform = 'yes';
}

sqgetGlobalVar('browseorgdn', $browseorgdn, SQ_GET);
sqgetGlobalVar('affiliates', $affiliates, SQ_GET);

$directory_output_type = getPref($data_dir, $username, "directory_output_type");
$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

if(!$ldq_standalone) {
	$location = get_location();
}

directory_LoadPrefs();

if (isset($_POST['add']) || isset($_GET['add']) ) {
	$rows = sizeof($ldq_querystr) + 1;
} elseif ( isset($_POST['less']) || isset($_GET['less']) ) {
	$rows = sizeof($ldq_querystr) - 1;
} elseif (isset($ldq_querystr)) {
	$rows = sizeof($ldq_querystr);
} else {
	$rows = 1;
}

if(isset($_GET['showrestrict'])) {
	$show_restrict = $_GET['showrestrict'];
} else {
	if($ldq_standalone) {
		$show_restrict = 0;
	} else {
		$show_restrict = 1;
	}
}

if(isset($_POST['restrict'])) {
	$restrict = $_POST['restrict'];

} elseif(isset($_GET['restrict'])) {
	$restrict = $_GET['restrict'];
	$show_restrict = 0;
} else {
	$restrict = array('*');
}

	
/* ---  Cache OrgUnitDNs -----  */
if(isset($_SESSION['orgs'])) {
	$orgs = $_SESSION['orgs'];
	$orgs3 = $_SESSION['orgs3'];
} else {
	/* Orgs Not cached into session. */
	global $orgs, $orgs3;
	cache_orgunitdns();
}

/* ---------- Gather together attributes that are to be displayed. --------- */
$attributes = array();

if(!isset($directory_output_type)) {
	/* Put defaults */
	$directory_output_type = $directory_default_output_type;
	$attributes = $ldq_default_attrs;
}

if($public_mode == true && isset($ldq_enable_attrs_public) ) {
	$ldq_enable_attrs = $ldq_enable_attrs_public;
}

foreach($ldq_enable_attrs as $attr) {
	$Var = "directory_showattr_" . $attr;
	if(isset($$Var) && $$Var == '1') {
		$attributes[] = $attr;
	}
}


/* ============== Catch common errors here ============== */

if (isset($_POST['search']) && ( empty($ldq_querystr[0]) && empty($ldq_querystr_simple) ) ) {
	$error[] = _("You have to define at least one search term.");
}   


/** --------  Perform search & echo results ----------  */


if ( (isset($_POST['search']) && (isset($ldq_querystr) || isset($ldq_querystr_simple) ) || isset($browseorgdn)) && !isset($error)) {

/** Convert search string to UTF-8 because that's what LDAP understands.  */
if(strtoupper($charset) != "UTF-8") {
	if(isset($ldq_querystr_simple)) {
		$ldq_querystr_simple_utf = directory_escape_ldap_string(directory_string_convert($ldq_querystr_simple, $charset, "UTF-8"));
	} elseif(isset($ldq_querystr)) {
		for($i=0; $i<sizeof($ldq_querystr); $i++) {
			$ldq_querystr_utf[$i] = directory_escape_ldap_string(directory_string_convert($ldq_querystr[$i], $charset, "UTF-8"));
		}
	}
} else {
	/* No conversion necessary */
	if(isset($ldq_querystr_simple)) {
		$ldq_querystr_simple_utf = $ldq_querystr_simple;
	} elseif(isset($ldq_querystr)) {
		for($i=0; $i<sizeof($ldq_querystr); $i++) {
			$ldq_querystr_utf[$i] = $ldq_querystr[$i];
		}
	}
}


/** Build search filter. */

if(isset($browseorgdn)) {
	$restrict = array();
	$restrict[0] = $browseorgdn;

	if(isset($affiliates)) {
		$ldq_finalfilter = 'edupersonaffiliation='.$affiliates;
	} else {
		$ldq_finalfilter = 'uid=*';
	}
} else {
	$ldq_tfilter = array();

	if(isset($ldq_querystr_simple_utf)) {
		/* Simple mode */
		$ldq_filter[0] = '|';
		foreach($ldq_searchattrs as $a) {
			if($a == 'mail' && $ldq_enablemailalternate == true ) {
				$ldq_filter[0] .= '(mail=*'.$ldq_querystr_simple_utf.'*)(mailAlternateAddress=*'.$ldq_querystr_simple_utf.'*)';
			} else {
				$ldq_filter[0] .= '('.$a.'=*'.$ldq_querystr_simple_utf.'*)';
			}
		}
		$ldq_filter[0] .= '';

	} else {
		/* Advanced mode */
	
		for($i=0; $i<sizeof($ldq_querystr_utf); $i++) {
			if( ! (strlen($ldq_querystr_utf[$i]) > 0 ) ) {
				break;
			}   
			
			switch ($ldq_comparetype[$i]) {
				case 'is':
					$ldq_tfilter[$i] = $ldq_querystr_utf[$i];
					break;
				case 'contains':
				default:
					$ldq_tfilter[$i] = "*$ldq_querystr_utf[$i]*";
					break;
			}
			if ($ldq_tfilter[$i] == '***') {
				$ldq_tfilter[$i] = "*";
			}   
      		
			if($ldq_searchby[$i] == 'mail' && $ldq_enablemailalternate == true ) {
				$ldq_filter[$i] = '|(mail='.$ldq_tfilter[$i].')(mailAlternateAddress='.$ldq_tfilter[$i].')';
			} else {
				$ldq_filter[$i] = $ldq_searchby[$i] . '=' . $ldq_tfilter[$i];
			}
		}
	}

	if(sizeof($ldq_filter) == 1){
		$ldq_finalfilter = $ldq_filter[0];
	} else {
		$ldq_finalfilter = '&';
		for($i=0; $i<sizeof($ldq_filter); $i++) {
			$ldq_finalfilter .= '(' . $ldq_filter[$i] . ')';
		}
	}	  
}

/* User-defined restrictions */

if(isset($restrict) && $ldq_restrict_ou){
	$inferior_final = array();
	directory_find_inferior($restrict, $inferior_final);
	if(!empty($inferior_final)) {
		$restrict_final = array_merge($restrict, $inferior_final);
		$restrict_final = array_values($restrict_final);
	} else {
		$restrict_final = $restrict;
	}
	if(sizeof($restrict_final) == 1 && $restrict_final[0] == '*') {
		unset($restrict_final[0]);
	}
	if(sizeof($restrict_final) > 1) {
		$restrict_filter = '|';
		for($k=0;$k<sizeof($restrict_final);$k++) {
			$restrict_filter .= '(edupersonorgunitdn='.$restrict_final[$k].')';
		}

	} elseif(sizeof($restrict_final) == 1) {
		$restrict_filter = 'edupersonorgunitdn='.$restrict_final[0];
	}
}

/* 
 * Now take the filter and "AND" in the various restrictions, if any.
 */

$ldq_filter = "(&";

if(isset($ldq_searchobjs[$ldq_searchfor]['filter'])) {
	$ldq_filter .= '(' . $ldq_searchobjs[$ldq_searchfor]['filter'] . ')';
}

if(isset($ldq_affiliation) && $ldq_affiliation != 'any' &&
  array_key_exists($ldq_affiliation, $affiliations_map)) {
	$ldq_filter .= '(edupersonaffiliation=' . $ldq_affiliation . ')';
}
if(isset($ldq_title) && $ldq_title != 'any' && $ldq_title != _("Any") ) {
	$ldq_filter .= '(title=' . directory_string_convert($ldq_title,$charset,'UTF-8') . ')';
}

if(isset($restrict_filter)) {
	$ldq_filter .= "(".$restrict_filter.")";
}

$ldq_filter .= '(' . $ldq_finalfilter . '))';

/*
 * Perform search for each LDAP server configured in squirrelmail.
 */
for ($ldq_lds=0 ; $ldq_lds < count($ldap_server) ; $ldq_lds++) {

	if(isset($ldq_bind_dn)) {
		unset($ldq_bind_dn);
	}
	if(isset($ldq_pass)) {
		unset($ldq_pass);
	}

	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Port = $ldap_server[$ldq_lds]['port'];
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxres = $ldap_server[$ldq_lds]['maxrows'];
	$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];

	if(isset($ldap_server[$ldq_lds]['binddn'])) {
		$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
	}
	if(isset($ldap_server[$ldq_lds]['bindpw'])) {
		$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];
	}

	if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
		$error[] = sprintf( _("Could not connect to LDAP server %s"), $ldq_server);
		continue;
	}
	if(isset($ldq_bind_dn)) {
		if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
			$error[] = sprintf( _("Unable to bind to LDAP server %s"), $ldq_server);
			continue;
		}
	}

	$extra_referrer_attributes = array();
	$extra_search_attributes = array();

	/** --- Gather attributes to ask LDAP --- */
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

	if(!in_array($ldq_sortby, $ldq_tattr)) {
		$ldq_tattr[] = $ldq_sortby;
	}

	if(!empty($ldq_privacy_attribute)) {
		$ldq_tattr[] = $ldq_privacy_attribute;
	}

	if(isset($ldq_searchobjs[$ldq_searchfor]['rdn'])) {
		$ldq_base = $ldq_searchobjs[$ldq_searchfor]['rdn'] . ',' . $ldq_base;
	}

	/** Perform search! */
	$old_error_reporting = error_reporting(E_ALL ^ E_WARNING); 

	if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,
		$ldq_tattr, 0, $ldq_maxres, $ldq_timeout))) {
		$noentries = true;
	}
	error_reporting($old_error_reporting);
	$ldq_errno = ldap_errno($ldq_ldap);
	if($ldq_errno == 4) {
		$toomanyresults = true;
	}
	$ldq_entry[$ldq_lds] = ldap_get_entries($ldq_ldap, $ldq_result);
	sanitize_entry_array($ldq_entry[$ldq_lds]);

	/*
 	 * Perform an additional search, if second-level results need to be
 	 * displayed. For instance, in the eduOrg schema.
 	 * Not yet implemented really.
 	 */

	/*
	if(isset($extra_search_attributes)) {
		$dns_to_search = array();
		foreach($extra_referrer_attributes as $attr) {
			for($i=0; $i<$ldq_entry['count']; $i++) {
				if(isset($ldq_entry[$i][$attr])) {
					for($j=0; $j<$ldq_entry[$i][$attr]['count']; $j++) {
						$dns_to_search[] = $ldq_entry[$i][$attr][$j];
					}
				}
			}
		}

		$dns_to_search = array_values(array_unique($dns_to_search));
		
		if(!empty($dns_to_search)) {
			$extra_filter = directory_build_filter_from_dn($dns_to_search);
		
			//echo " FILTER = <b>$extra_filter</b>";
			if (!($ldq_result2 = ldap_search($ldq_ldap, $ldq_base, $extra_filter,
				$extra_search_attributes, 0, $ldq_maxres, $ldq_timeout))) {
				echo "Second level search failed.";
			}
	        	$ldq_entry2 = ldap_get_entries ($ldq_ldap, $ldq_result2);
	
			$follow = array();
			for($k=0; $k<$ldq_entry2['count']; $k++) {
				$attr = 'cn';
				$attr_lang = $attr.';lang-'.$ldq_lang;
				if(isset($ldq_entry2[$k][$attr_lang])) {
					$follow[$ldq_entry2[$k]['dn']] = directory_string_convert($ldq_entry2[$k][$attr_lang][0], "UTF-8", $charset);
				} else {
					$follow[$ldq_entry2[$k]['dn']] = directory_string_convert($ldq_entry2[$k][$attr][0], "UTF-8", $charset);
				}
				
			}
			// echo "<pre>\nRESULTS:\n"; print_r($follow); echo "</pre>";
		}
	}
	*/
	ldap_close($ldq_ldap);
	

} /* Foreach LDAP Server */
 

} /* Search is set */


/*
 * ============== Presentation: Page Header ============== 
 */
if(isset($popup)) {

	$wintitle = _("Directory");
	$js = '';

	if(isset($popup) && (isset($ldq_querystr) || isset($ldq_querystr_simple) || isset($browseorgdn))) {
		
   		/* Note: Remove type="" if it does bad... */
   		$js = '<script language="Javascript" type="text/javascript"><!--' . "\n";
		// $js .= directory_insert_javascript();
    
		/* Include this too; it is not displayed in the pop-up window
		 * by default. */
       	$js .= "function comp_in_new(comp_uri) {\n".
               '    var newwin = window.open(comp_uri' .
               ', "_blank",'.
               '"width='.$compose_width. ',height='.$compose_height.
               ',scrollbars=yes,resizable=yes");'."\n".
               "}\n\n";

		if(isset($formname)) {
			if(empty($inputname)) {
				$inputname = 'newuser';
			}
			$js .= directory_insert_javascript($formname, $inputname);
		}
		$js .= "// --></script>";
	}

	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayHtmlHeader($wintitle, $js);

} else {
	if($ldq_standalone) {
		displayPageHeader($color, _("Directory Service"), $searchform, 'onLoad="initItems();"');
	} else {
		$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
		textdomain ('squirrelmail');
		displayPageHeader($color, "None", '');
	}
}

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

echo '<script language="Javascript" type="text/javascript"><!--' . "\n".
	affiliation_title_javascript().
	"// --></script>";



directory_print_all_sections_start();


/** =============  Error message, if exists ============= */

if(isset($error)) {
	directory_print_section_start( _("Error Encountered") );
	echo '<tr><td bgcolor="'.$color[2].'" align="center">';

	foreach($error as $e) {
		echo '<p><font color="'.$color[8].'"><strong>'.$e.'</strong></font></p>';
	}
	echo '</td></tr>';
	directory_print_section_end();
}


/* ================ Print search form  ================  */

if($searchform == 'yes') {
	directory_print_section_start('<small>'. _("Directory Service") . '</small> - ' . _("Search") .
		' - <small>'.directory_navbar().'</small>');
	echo '<form name="dirsearchform" action="'.$PHP_SELF.'" method="post"';
	if($ldq_standalone) {
		echo ' target="dirresults"';
	}
	echo '>';
	if($ldq_standalone) {
		echo '<input type="hidden" name="searchform" value="no" />';
	}
	echo '<table cellspacing="1" cellpadding="3" border="0" width="100%">';

	if($mode == DIR_SIMPLE) {

		/* Simple Form */

		echo '<tr><td align="right" width="45%">'.
			'<strong>' . _("Search For") . ':</strong> '.
			'</td><td align="left" width="55%">'.
			'<input type="text" name="ldq_querystr_simple" size="25" value="';
		if(isset($ldq_querystr_simple)) {
			echo htmlspecialchars($ldq_querystr_simple);
		}
		echo '" /><br />'.
			'</td></tr>';
		echo ' <tr><td align="center" colspan="3">' . 
			' <input type="submit" name="search" style="text-weight:bold" value="'. _("Search") . '" /> ';

	} else {
	
		/* Advanced Form */

	/* objectclass the user wants to search in */
	if(sizeof($ldq_enable_searchfor) > 1) {
		echo '<td align="right"><strong>' . _("Search For") . ':</strong></td>'.
			'<td align="left"><select name="ldq_searchfor"> ';
	
		foreach ($ldq_enable_searchfor as $no=>$sf) {
			if(array_key_exists($sf, $ldq_searchobjs)) {
				echo '<option value="'.$sf.'"';
				if (isset($ldq_searchfor) && $sf == $ldq_searchfor) {
					echo ' selected=""';
				}	
				echo '>'.$ldq_searchobjs[$sf]['text'].'</option>';
			}
		}
		echo '</select></td></tr>';
	} else {
		echo '<input name="ldq_searchfor" type="hidden" value="'.$ldq_enable_searchfor[0].'"/>';
	}
	
	if(!isset($ldq_searchby)) {
		$ldq_searchby = array('cn');
	}
	
	for($i=0; $i<$rows; $i++ ) {
		/* Start row */
		echo '<tr><td align="right">';
		if ($i == 0) {
			echo '<strong>' . _("Criteria") . ':</strong> ';
		} else {
			echo _("and");
		}
	
		/* attribute the user wants to search by */
		echo '</td><td align="right" nowrap="">';
	
		echo '<select name="ldq_searchby['.$i.']">';
		foreach ($ldq_searchattrs as $no=>$attr) {
			echo '<option value="'.$attr.'"';
			if (isset($ldq_searchby[$i]) && $attr == $ldq_searchby[$i]) {
				echo ' selected=""';
			}
			echo '>'.$ldq_attributes[$attr]['text'].'</option>';
		}
		echo '</select>';
	
		/* type of search (is|contains) */
		echo '<select name="ldq_comparetype['.$i.']">';
		foreach ($ldq_comparetypes as $type=>$info) {
			echo '<option value="'.$type.'"';
			if (isset($ldq_comparetype[$i]) && $ldq_comparetype[$i] == $type) {
				echo ' selected=""';
			}
		echo '>'.$info['text'].'</option>';
		}   
		echo '</select>';
	
		/* search string */
		echo '</td><td align="left">';
		echo '<input type="text" name="ldq_querystr['.$i.']" value="';
		if(isset($ldq_querystr[$i])) {
			echo htmlspecialchars($ldq_querystr[$i]);
		}
	
		echo '" /><br />';
		/* End row */
		echo '</td></tr>';
	}
	
	
	/** Restrict option */
	if($ldq_restrict_ou) {
		echo "\n" . '<tr><td align="right"><strong>'. _("Restrict:") .'</strong></td>' .
			'<td colspan="2" align="left">';
		
		if($show_restrict) {
			echo '<select size="10" multiple="" name="restrict[]">';
			directory_print_orgs3($orgs3, 0);
			echo '</select>';
		
		} else {
			foreach($restrict as $no=>$dn) {
				echo '<input type="hidden" name="restrict[]" value="'.$dn.'" />';
				echo '<img src="images/arrow-16.png" alt="=&gt;" align="center"> '.
				$orgs[$dn]['struct'].' '.$orgs[$dn]['text'].'<br />';
			}
			echo '<img src="images/goto-16.png" alt="=&gt;" align="center"> '.
				'<strong>';
			if($ldq_standalone) {
				echo '<a href="directory.php?showrestrict=1">'.
					_("Choose Multiple Organizational Units") .
					'</a>';
			} else {
				echo '<a href="directory.php">'. _("Clear") . '</a>';
			}
			echo '</strong><br/>';
		}
		echo '</td></tr>';
	}
	
	
	/* Affiliation + Titles filter; a set of two select boxes. Display only if
 	* $titles is set. */
	
	if(isset($titles)) {
		
		echo '<tr><td align="right"><strong>' . _("Affiliation") . ':</strong></td>'.
			'<td colspan="2" align="left"><select name="ldq_affiliation"';
			if($ldq_custom == 'uoa') {
				echo ' onChange="changeItems();"';
			}
		echo '>';
		echo '<option value="any">'. _("Any") .'</option>';
		foreach ($affiliations_map as $attr=>$desc) {
			echo '<option value="'.$attr.'"';
			if (isset($ldq_affiliation) && $attr == $ldq_affiliation) {
				echo ' selected=""';
			}
			echo '>'.$desc.'</option>';
		}
		echo "</select>";
		
		/* Title filter */
		echo ' &nbsp;<strong>' . _("Title") . ':</strong> '.
			'<select name="ldq_title">'.
			'<option value="any"';
		if($ldq_title == _("Any"))
			echo ' selected=""';
		echo '>'. _("Any") .'</option>';
		if(isset($ldq_affiliation) && $ldq_affiliation != 'any' &&
  		array_key_exists($ldq_affiliation, $affiliations_map)) {
			foreach($affiliate_titles[$ldq_affiliation] as $numbers=>$no) {
				if(isset($titles[$no])) {
					echo '<option';
					if(isset($ldq_title) && $ldq_title == $titles[$no][$ldq_lang]) {
						echo ' selected=""';
					}
					echo '>'.$titles[$no][$ldq_lang].'</option>';
				}
			}
		}
		echo '</select>' .
			'</td></tr>';
	}
	
	
	
	/* Sort By */
	echo '<tr><td align="right"><strong>' . _("Sort By:") . '</strong></td>'.
		'<td colspan="2" align="left"><select name="ldq_sortby">';
	
	foreach ($ldq_searchattrs as $no=>$attr) {
		echo '<option value="'.$attr.'"';
		if (isset($ldq_sortby) && $attr == $ldq_sortby) {
			echo ' selected=""';
		}
		echo '>'.$ldq_attributes[$attr]['text'].'</option>';
	}
	echo "</select>" .
		'</td></tr>';
	
	/* Submit */
	echo ' <tr><td align="center" colspan="3">' . 
		' <input type="submit" name="search" style="text-weight:bold" value="'. _("Search") . '" /> ';
	
	if($rows > 1) {
		if($ldq_standalone) {
			echo '<input type="button" accesskey="'. _("l") .'" name="less" value="'.
				_("Less...") . '" '.
				'onClick="document.forms.dirsearchform.action = \'directory.php?less=1&amp;showrestrict='.$show_restrict.'\';document.forms.dirsearchform.target = \'dirbrowse\';document.forms.dirsearchform.submit();">';
		} else {
			echo '<input type="submit" accesskey="'. _("l") .'" name="less" value="'.
				_("Less...") . '" />';
		}
	}
			
		
	if($i<5) {
		if($ldq_standalone) {
			echo '<input type="button" accesskey="'. _("m") .'" name="add" value="'.
				_("More...") . '" '.
				'onClick="document.forms.dirsearchform.action = \'directory.php?add=1\&amp;showrestrict='.$show_restrict.'\';document.forms.dirsearchform.target = \'dirbrowse\';document.forms.dirsearchform.submit();">';
		} else {
			echo '<input type="submit" accesskey="'. _("m") .'" name="add" value="'.
				_("More...") . '" />';
		}
	}   
	
	echo ' <input type="reset" name="reset" value="'. _("Clear") . '" />';
	} /* End advanced mode. */
	
	if(isset($popup)) {
		echo ' <input type="hidden" name="popup" value="1" />';
		echo ' <input type="button" name="close" onClick="return self.close()" value="'. _("Close") . '" />';
	}
	
	if(!empty($formname)) {
		echo ' <input type="hidden" name="formname" value="'.$formname.'" />';
	}
	if(!empty($inputname)) {
		echo ' <input type="hidden" name="inputname" value="'.$inputname.'" />';
	}

	echo '</td></tr></table>';
	echo "</form>";
	
	
	directory_print_section_end();
}

	
	
/* =============== Display Results =============== */

if(isset($ldq_entry)) {
	for ($ldq_lds=0 ; $ldq_lds < count($ldap_server) ; $ldq_lds++) {
		if(!isset($ldq_entry[$ldq_lds])) {
			continue;
		}
		directory_print_section_start( _("Search Results") . ' - ' . $ldap_server[$ldq_lds]['name;lang-'.$ldq_lang]);
	 	
		if(isset($noentries)) {
			echo '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
		}
	
		if(isset($restrict) && !in_array('*', $restrict)) {
			echo '<p>' . _("Search restricted to the following organizational units:") . '</p><ul>';
			foreach($restrict as $re) {
				echo '<li>' . $orgs[$re]['struct'] . ' ' .$orgs[$re]['text'] . '</li>';
			}
			echo '</ul>';
		}
		if(isset($toomanyresults)) {
			echo '<p align="center"><strong>';
			echo _("Warning: Too many results were found, a partial list follows.");
			echo '</strong><br/>';
			echo _("You may redefine your search to get more specific results.") . '</p><br />';
		}
		if($directory_output_type=='multitable') {
			directory_dispresultsMulti ($attributes, $ldq_entry[$ldq_lds], $ldq_sortby);
		} else {
			/* "onetable" */
			directory_dispresultsSingle ($attributes, $ldq_entry[$ldq_lds], $ldq_sortby);
		}
		directory_print_section_end();
	}
}

directory_print_all_sections_end();
echo '</body></html>';

?>
