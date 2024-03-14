<?php
/**
 * directory.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: directory.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Main routine for directory window
 */

/**
 * Define's and include's
 */
define('SM_PATH', "../../");
include_once (SM_PATH . 'include/validate.php');
include_once (SM_PATH . 'include/load_prefs.php');

$prev = bindtextdomain ('directory', SM_PATH . 'plugins/directory/locale');
textdomain ('directory');

include_once (SM_PATH . "plugins/directory/config.php");

include_once (SM_PATH . "plugins/directory/html.php");
include_once (SM_PATH . "plugins/directory/javascript.php");

include_once (SM_PATH . "plugins/directory/functions.php");
include_once (SM_PATH . "plugins/directory/constants.php");
include_once (SM_PATH . "plugins/directory/display.php");

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(SM_PATH . "plugins/directory/custom/$ldq_custom.php")) {
	include_once (SM_PATH . "plugins/directory/custom/$ldq_custom.php");
} else {
	print 'bleh';
}
  	



/**
 * Variable import
 */
sqgetGlobalVar('ldq_searchfor', $ldq_searchfor, SQ_POST);
sqgetGlobalVar('ldq_searchby', $ldq_searchby, SQ_POST);
sqgetGlobalVar('ldq_comparetype', $ldq_comparetype, SQ_POST);
sqgetGlobalVar('ldq_querystr', $ldq_querystr, SQ_POST);
sqgetGlobalVar('ldq_sortby', $ldq_sortby, SQ_POST);
sqgetGlobalVar('Submit_Button', $Submit_Button, SQ_POST);
sqgetGlobalVar('popup', $popup, SQ_GET);
sqgetGlobalVar('formname', $formname, SQ_GET);
sqgetGlobalVar('inputname', $inputname, SQ_GET);

$directory_output_type = getPref($data_dir, $username, "directory_output_type");
$compose_new_win = getPref($data_dir, $username, 'compose_new_win');
$location = get_location();

directory_LoadPrefs();

if(isset($popup)) {

	$wintitle = _("Directory");

	$js = '';

	if(isset($popup) && isset($ldq_querystr)) {
    
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

		if(isset($formname)) {
			if(!isset($inputname)) {
				$inputname = 'newuser';
			}
			$js .= directory_insert_javascript_custom($formname, $inputname);
		}

		$js .= "// --></script>";
	}

	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayHtmlHeader($wintitle, $js);

} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayPageHeader($color, "None");
}
$prev = bindtextdomain ('directory', SM_PATH . 'plugins/directory/locale');
textdomain ('directory');

$lang_iso = getPref($data_dir, $username, 'language');
$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];


if (isset($_POST['add']) ) {
	$rows = sizeof($ldq_querystr) + 1;
} elseif ( isset($_POST['less']) ) {
	$rows = sizeof($ldq_querystr) - 1;
} elseif (isset($ldq_querystr)) {
	$rows = sizeof($ldq_querystr);
} else {
	$rows = 1;
}

if(isset($_POST['restrict'])) {
	$restrict = $_POST['restrict'];
} else {
	$restrict = array('*');
}

/* -------------- Catch common errors here ----------------- */

if (isset($_POST['search']) && empty($ldq_querystr[0]) ) {
	$error = '<p>'. _("You have to define at least one search term.") . '</p>';
	directory_print_section_end();
}   


	

/* ---------- Gather together attributes that are to be displayed. --------- */
$attributes = array();

if(!isset($directory_output_type)) {
	/* Put defaults */
	$directory_output_type = $directory_default_output_type;
	$attributes = $ldq_default_attrs;
}

foreach($ldq_enable_attrs as $attr) {
	$Var = "directory_showattr_" . $attr;
	if(isset($$Var) && $$Var == '1') {
		$attributes[] = $attr;
	}
}

	
/** ----------  Cache OrgUnitDNs ----------  */
if(isset($_SESSION['orgs'])) {
	$orgs = $_SESSION['orgs'];
	// $orgs2 = $_SESSION['orgs2'];
	$orgs3 = $_SESSION['orgs3'];
} else {
	$ldq_lds=0;
	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Port = $ldap_server[$ldq_lds]['port'];
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxrows = $ldap_server[$ldq_lds]['maxrows'];
	$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];

	if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
		$error = "Could not connect to LDAP server " . $ldq_Server;
	}
	if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
		$error = "Unable to bind to LDAP server";
	} else {

		$orgs = array();

		$orgs['*']['struct'] = '';
		$orgs['*']['text'] = '<strong>'.$ldap_server[$ldq_lds]['name'].' '.
			_("(All)") . '</strong>';
		// $orgs['*']['selected'] = true;
		
		$orgfilter = "(&(objectclass=uoastructuralunit)(objectclass=eduorg))";
		$orgattr = array('cn', 'uoastructuraltype', 'eduorgsuperioruri');
		if (!($orgresult = ldap_search($ldq_ldap, $ldq_base, $orgfilter, $orgattr, 0))) {
		 	print '<p align="center"><strong>No org. units found</strong></p>';
		}
	        $orgentry = ldap_get_entries ($ldq_ldap, $orgresult);
		/* Build orgs array */
		for($i=0; $i<$orgentry['count']; $i++) {
			$struct = 'uoastructuraltype';
			$struct_lang = $struct.';lang-'.$ldq_lang;
			$attr = 'cn';
			$attr_lang = $attr.';lang-'.$ldq_lang;
			
			/* Struct */
			if(isset($orgentry[$i][$struct_lang][0])) {
				$orgs[$orgentry[$i]['dn']]['struct'] = directory_string_convert($orgentry[$i][$struct_lang][0], "UTF-8", $charset);
			} else {
				$orgs[$orgentry[$i]['dn']]['struct'] = directory_string_convert($orgentry[$i][$struct][0], "UTF-8", $charset);
			}

			/* Text */
			if(isset($orgentry[$i][$attr_lang][0])) {
				$orgs[$orgentry[$i]['dn']]['text'] = directory_string_convert($orgentry[$i][$attr_lang][0], "UTF-8", $charset);
			} else {
				$orgs[$orgentry[$i]['dn']]['text'] = directory_string_convert($orgentry[$i][$attr][0], "UTF-8", $charset);
			}
			/* Path */
			$brkdn = ldap_explode_dn($orgentry[$i]['dn'], 1);
			for($j=$brkdn['count']-3; $j >= 0 ;  $j--) {
				// print $brkdn[$j] . "<br />";
				$orgs[$orgentry[$i]['dn']]['path'][] = $brkdn[$j]; 
			}
			
			/* eduOrgSuperiorUri */
			if(isset($orgentry[$i]['eduorgsuperioruri'][0])) {
				$urlinfo = parse_url($orgentry[$i]['eduorgsuperioruri'][0]);
				$orgs[$orgentry[$i]['dn']]['superior']['dn'] = str_replace('/', '', $urlinfo['path']);
				$orgs[$orgentry[$i]['dn']]['superior']['host'] =  $urlinfo['host'];
			}
				
		}
		
		/* Sorting */
		$temp = array();
		$orgs3 = array();

		/* Sort algorithm #3:  */
		foreach ($orgs as $dn => $info) {
			if(isset($info['superior'])) {
				$temp[$info['superior']['dn']][] = $dn;
			} else {
				$temp['top'][] = $dn;
			}
		}
		
		foreach($temp['top'] as $no=>$dn) {
			/* 1 */
			$orgs3[$dn] = array();
			$orgs3[$dn]['text'] = $orgs[$dn]['text'];
			$orgs3[$dn]['struct'] = $orgs[$dn]['struct'];
			if(isset($temp[$dn])) {
				/* 2 */
				$orgs3[$dn]['sub'] = array();
				foreach($temp[$dn] as $no2=>$dn2) {
					$orgs3[$dn]['sub'][$dn2] = array();
					$orgs3[$dn]['sub'][$dn2]['text'] = $orgs[$dn2]['text'];
					$orgs3[$dn]['sub'][$dn2]['struct'] = $orgs[$dn2]['struct'];
					if(isset($temp[$dn2])) {
						/* 3 */
						$orgs3[$dn]['sub'][$dn2]['sub'] = array();
						foreach($temp[$dn2] as $no3=>$dn3) {
							$orgs3[$dn]['sub'][$dn2]['sub'][$dn3] = array();
							$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['text'] = $orgs[$dn3]['text'];
							$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['struct'] = $orgs[$dn3]['struct'];
							if(isset($temp[$dn3])) {
								/* 4 */
								$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['sub'] = array();
								foreach($temp[$dn3] as $no4=>$dn4) {
									$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['sub'][$dn4] = array();
									$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['sub'][$dn4]['text'] = $orgs[$dn4]['text'];
									$orgs3[$dn]['sub'][$dn2]['sub'][$dn3]['sub'][$dn4]['struct'] = $orgs[$dn4]['struct'];
									/* Do more :-) */
								}
							}
						}
					}
				}
			}
		}

		// print_r($orgs3);

		// foreach ($orgs3 as $dn => $children) {
		

		/* Sort algorithm #4:  */
		/*
		for($i=0;$i<$orgentry['count'];$i++){
			$search_array['dn'][$i] = trim($orgentry[$i]['dn']);
			$search_array['cn'][$i] = trim($orgentry[$i]['cn'][0]);
			$search_array['UoAStructuralType'][$i] = trim($orgentry[$i]['uoastructuraltype'][0]);
			if(isset($orgentry[$i]['eduorgsuperioruri'][0]))
				$search_array['eduorgsuperioruri'][$i] = trim($orgentry[$i]['eduorgsuperioruri'][0]);
			else
				$search_array['eduorgsuperioruri'][$i] = '/top_level';
	
			foreach($languages as $lang) {
				if(isset($orgentry[$i]["cn;lang-".$lang]))
					$search_array['cn;lang-'.$lang][$i] = trim($orgentry[$i]["cn;lang-".$lang][0]);
				if(isset($orgentry[$i]["uoastructuraltype;lang-".$lang]))
					$search_array['UoAStructuralType;lang-'.$lang][$i] = trim($orgentry[$i]["uoastructuraltype;lang-".$lang][0]);
			}

		}
		// $_SESSION['alluoastructuralunits'] = $search_array;
		$alluoastructuralunits = $search_array;
		display_UoAStructural_listbox($alluoastructuralunits,'', 'lala[]');
		*/



		/* Sort algorithm #2:  */
		/*
		foreach ($orgs as $dn => $info) {
			
				if(sizeof($info['path']) == 1) {
					$orgs2[$info['path'][0]] = array();

					$orgs2[$info['path'][0]]['dn'] = $dn;
					$orgs2[$info['path'][0]]['dn'] = $info['text'];
					$orgs2[$info['path'][0]]['dn'] = $info['struct'];

				} elseif(sizeof($info['path']) == 2) {
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]] = array();

					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['dn'] = $dn;
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['text'] = $info['text'];
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['struct'] = $info['struct'];

				} elseif(sizeof($info['path']) == 3) {
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]] = array();

					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['dn'] = $dn;
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['text'] = $info['text'];
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['struct'] = $info['struct'];

				} elseif(sizeof($info['path']) == 4) {
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]] = array();

					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]]['dn'] = $dn;
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]]['text'] = $info['text'];
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]]['struct'] = $info['struct'];

				} elseif(sizeof($info['path']) == 5) {
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]][$info['path'][4]] = array();

					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]][$info['path'][4]]['dn'] = $dn;
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]][$info['path'][4]]['text'] = $nfo['text'];
					$orgs2[$info['path'][0]]['sub'][$info['path'][1]]['sub'][$info['path'][2]]['sub'][$info['path'][3]][$info['path'][4]]['struct'] = $nfo['struct'];
				}
		}
		*/


		/* Sorting Algorithm  #1 */
		/*
		for($i=0; $i<$orgentry['count']; $i++) {
			$struct = 'uoastructuraltype';
			$struct_lang = $struct.';lang-'.$ldq_lang;
			$attr = 'cn';
			$attr_lang = $attr.';lang-'.$ldq_lang;
			if(isset($orgentry[$i][$attr_lang][0])) {
				if(isset($orgentry[$i][$struct_lang][0])) {
					$orgs[directory_string_convert($orgentry[$i][$struct_lang][0], "UTF-8", $charset)][$orgentry[$i]['dn']] = directory_string_convert($orgentry[$i][$attr_lang][0], "UTF-8", $charset);
				} else {
					$orgs[$orgentry[$i][$struct][0]][$orgentry[$i]['dn']] = directory_string_convert($orgentry[$i][$attr_lang][0], "UTF-8", $charset);
				}
			} else {
				$orgs[$orgentry[$i]['dn']] = $orgentry[$i][$attr][0];
			}
		}
		*/
		$_SESSION['orgs'] = $orgs;
		// $_SESSION['orgs2'] = $orgs2;
		$_SESSION['orgs3'] = $orgs3;
	}
}

/** ----------  Print Page Header ----------  */

directory_printheader( _("Directory Service") );
directory_print_all_sections_start();


/* -------------- Print search form  ----------------- */
directory_print_section_start(_("Search"));

print '<form action="'.$PHP_SELF.'" method="post">' . 
	'<table cellspacing="1" cellpadding="3" border="0">';
	/*
	'<colgroup align="right" width="0*"></colgroup>'.
	'<colgroup align="left" width="30%"></colgroup>'. 
	'<colgroup align="left"></colgroup>';
	*/

/* objectclass the user wants to search in */
print '<td align="right"><strong>' . _("Search For") . ':</strong></td>'.
	'<td align="left"><select name="ldq_searchfor"> ';

foreach ($ldq_enable_searchfor as $no=>$sf) {
	if(array_key_exists($sf, $ldq_searchobjs)) {
		print '<option value="'.$sf.'"';
		if (isset($ldq_searchfor) && $sf == $ldq_searchfor) {
			print ' selected=""';
		}	
		print '>'.$ldq_searchobjs[$sf]['text'].'</option>';
	}
}
print '</select></td></tr>';

if(!isset($ldq_searchby)) {
	$ldq_searchby = array('cn');
}

for($i=0; $i<$rows; $i++ ) {
	/* Start row */
	print '<tr><td align="right">';
	if ($i == 0) {
		print '<strong>' . _("Criteria") . ':</strong> ';
	} else {
		print _("and");
	}

	/* attribute the user wants to search by */
	print '</td><td align="right" nowrap="">';

	print '<select name="ldq_searchby['.$i.']">';
	foreach ($ldq_searchattrs as $no=>$attr) {
		print '<option value="'.$attr.'"';
		if (isset($ldq_searchby[$i]) && $attr == $ldq_searchby[$i]) {
			print ' selected=""';
		}
		print '>'.$ldq_attributes[$attr]['text'].'</option>';
	}
	print '</select>';

	/* type of search (is|contains) */
	print '<select name="ldq_comparetype['.$i.']">';
	foreach ($ldq_comparetypes as $type=>$info) {
		print '<option value="'.$type.'"';
		if (isset($ldq_comparetype[$i]) && $ldq_comparetype[$i] == $type) {
			print ' selected=""';
		}
	print '>'.$info['text'].'</option>';
	}   
	print '</select>';

	/* search string */
	print '</td><td align="left">';
	print '<input type="text" name="ldq_querystr['.$i.']" value="';
	if(isset($ldq_querystr[$i])) {
		print $ldq_querystr[$i];
	}

	print '" /><br />';
	/* End row */
	print '</td></tr>';
}


print '<tr><td></td><td align="right">';
if($rows > 1) {
	print '<input type="submit" accesskey="'. _("l") .'" name="less" value="'. _("Less...") . '">';
}
print '</td><td align="left">';
if($i<5) {
	print '<input type="submit" accesskey="'. _("m") .'" name="add" value="'. _("More...") . '">';
}   
print '</td></tr>';


/** EXPERIMENTAL restrict option */
if($ldq_restrict_ou) {
	print "\n" . '<tr><td align="right"><strong>'. _("Restrict:") .'</strong></td>' .
		'<td colspan="2" align="center">';
	
	/* For Sorting altorithm #2/3 */
	
	print '<select size="10" multiple="" name="restrict[]">';
	// directory_print_orgs2($orgs2, 0);
	directory_print_orgs3($orgs3, 0);
	print '</select>';
	
	/* For Sorting altorithm #1 */
	/*
	print '<select size="0" name="restrict">';
	foreach($orgs as $structtype=>$stuff) {
		echo '<optgroup label="'.$structtype.'">';
		foreach ($stuff as $dn => $text) {
			echo '<option value="'.urlencode($dn).'">'.$text."</option>\n";
		}
		echo '</optgroup>';
	}
	print '</select>';
	*/
	print '</td></tr>';
}


/* Sort By */
print '<tr><td align="right"><strong>' . _("Sort By:") . '</strong></td>'.
	'<td colspan="2" align="left"><select name="ldq_sortby">';




foreach ($ldq_searchattrs as $no=>$attr) {
	print '<option value="'.$attr.'"';
	if (isset($ldq_sortby) && $attr == $ldq_sortby) {
		print ' selected=""';
	}
	print '>'.$ldq_attributes[$attr]['text'].'</option>';
}
print "</select>" .
	'</td></tr>';

/* Submit */
print ' <tr><td align="center" colspan="3">' . 
	' <input type="submit" name="search" style="text-weight:bold" value="'. _("Search") . '" />' .
	' <input type="reset" name="reset" value="'. _("Clear") . '" />';
	if(isset($popup)) {
		print ' <input type="button" name="close" onClick="return self.close()" value="'. _("Close") . '" />';
	}

print '</td></tr></table>';
print "</form>";

directory_print_section_end();



/** ----------  Do search & Print results ----------  */

if (isset($_POST['search']) && isset($ldq_querystr) && !isset($error) ) {

directory_print_section_start( _("Search Results") );

/** Convert search string to UTF-8 because that's what LDAP understands.  */
if(strtoupper($charset) != "UTF-8") {
	for($i=0; $i<sizeof($ldq_querystr); $i++) {
		$ldq_querystr[$i] = directory_string_convert($ldq_querystr[$i], $charset, "UTF-8");
		$ldq_querystr[$i] = str_replace('\\', '\\5c', $ldq_querystr[$i]);
		/* Don't escape asterisk... */
		/* $ldq_querystr[$i] = str_replace('*', '\\2a', $ldq_querystr[$i]); */
		$ldq_querystr[$i] = str_replace('(', '\\28', $ldq_querystr[$i]);
		$ldq_querystr[$i] = str_replace(')', '\\29', $ldq_querystr[$i]);
	}
}


/** Build search filter. */
      $ldq_tfilter = array();

      for($i=0; $i<sizeof($ldq_querystr); $i++) {

        if( ! (strlen($ldq_querystr[$i]) > 0 ) ) {
	   break;
	}   

        switch ($ldq_comparetype[$i]) {
           case 'is':
              $ldq_tfilter[$i] = $ldq_querystr[$i];
              break;

           case 'contains':
           default:
              $ldq_tfilter[$i] = "*$ldq_querystr[$i]*";
              break;
        }
        if ($ldq_tfilter[$i] == '***') {
           $ldq_tfilter[$i] = "*";
	}   
      
        if($ldq_searchby[$i] == 'mail' && $ldq_enablemailalternate == true ) {
            $ldq_filter[$i] = '|(mail='.$ldq_tfilter[$i].')(mailAlternateAddress='.$ldq_tfilter[$i].')';
        } else {
            $ldq_filter[$i] = $ldq_searchby[$i] . "=" . $ldq_tfilter[$i];
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


/** 
 * Now take the filter and "AND" in the various restrictions, if any.
 */

if(isset($restrict)){
	if(sizeof($restrict) > 1) {
		$restrict_filter = '|';
		for($k=0;$k<sizeof($restrict);$k++) {
			$restrict_filter .= '(edupersonorgunitdn='.$restrict[$k].')';
		}

	} elseif(sizeof($restrict) == 1) {
		$restrict_filter = 'edupersonorgunitdn='.$restrict[0];
	}
}

$ldq_filter = "(&";

if(isset($ldq_searchobjs[$ldq_searchfor]['filter'])) {
	$ldq_filter .= '(' . $ldq_searchobjs[$ldq_searchfor]['filter'] . ')';
}

if(isset($restrict_filter)) {
	$ldq_filter .= "(".$restrict_filter.")";
}

$ldq_filter .= '(' . $ldq_finalfilter . '))';

   
/**
 * Perform search for each LDAP server configured in squirrelmail.
 */
for ($ldq_lds=0 ; $ldq_lds < count($ldap_server) ; $ldq_lds++) {

	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Port = $ldap_server[$ldq_lds]['port'];
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxrows = $ldap_server[$ldq_lds]['maxrows'];
	$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];
	print "<h3>" . $ldap_server[$ldq_lds]["name"] . "</h3>";

         if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
		print ("Could not connect to LDAP server " . $ldq_Server);
		continue;
         }
         if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
               print ("Unable to bind to LDAP server<BR>\n");
	       continue;
         }

	if(isset($restrict) && !in_array('*', $restrict)) {
		print '<p>' . _("Search restricted to the following organizational units:") . '</p><ul>';
		foreach($restrict as $re) {
			print '<li>' . $orgs[$re]['struct'] . ' ' .$orgs[$re]['text'] . '</li>';
		}
		print '</ul>';
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
	 	// print "<br>DEBUG: extra referrer attributes = ";
		// print_r($extra_referrer_attributes);
	}
	if(!empty($extra_search_attributes)) { 
		$extra_search_attributes = array_unique($extra_search_attributes);
		// print "<br>extra search attributes = ";
		// print_r($extra_search_attributes);
	}

	$ldq_tattr[] = 'uid';

	if(isset($ldq_searchobjs[$ldq_searchfor]['rdn'])) {
		$ldq_base = $ldq_searchobjs[$ldq_searchfor]['rdn'] . ',' . $ldq_base;
	}

         /** Perform search! */
	 $old_error_reporting = error_reporting(E_ALL & ~(E_WARNING | E_NOTICE )); 

	 // print $ldq_filter;
	 
	 if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,
	   $ldq_tattr, 0, $ldq_maxres, $ldq_timeout))) {
	 	print '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
         }
	 error_reporting($old_error_reporting);
	 $ldq_errno = ldap_errno($ldq_ldap);
	 if($ldq_errno == 4) {
	 	print '<p align="center"><strong>';
		print _("Warning: Too many results were found, a partial list follows.");
		print '</strong><br/>';
		print _("You may redefine your search to get more specific results.") . '</p><br />';
	}
        $ldq_entry = ldap_get_entries ($ldq_ldap, $ldq_result);


	 /**
	  * Perform an additional search, if second-level results need to be
	  * displayed. For instance, in the eduOrg schema. */

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
		
			//print " FILTER = <b>$extra_filter</b>";
			if (!($ldq_result2 = ldap_search($ldq_ldap, $ldq_base, $extra_filter,
				$extra_search_attributes, 0, $ldq_maxres, $ldq_timeout))) {
				print "Second level search failed.";
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
			// print "<pre>\nRESULTS:\n"; print_r($follow); print "</pre>";
		}
	}
	*/
	ldap_close($ldq_ldap);
	       
	if($directory_output_type=='multitable') {
		directory_dispresultsMulti ($attributes, $ldq_entry, $ldq_sortby);
	} else {
		/* "onetable" */
		directory_dispresultsSingle ($attributes, $ldq_entry, $ldq_sortby);
	}

} /* Foreach LDAP Server */
 
directory_print_section_end();

} /* Search is set */

directory_print_all_sections_end();
directory_printfooter();
			
print '</body></html>';

?>
