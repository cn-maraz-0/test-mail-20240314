<?php
/**
 * eduorg.inc.php
 *
 * Functions specific to eduOrg LDAP Schema.
 *
 * Copyright (c) 2003-2005 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: eduorg.inc.php,v 1.4 2007/07/05 08:37:26 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Search for eduOrgUnit objects in the LDAP server, and cache their
 * information by saving it into the session.
 *
 * @global array $ldap_server
 * @global array $orgs
 * @global array $orgs3
 */
function cache_orgunitdns() {
	global $ldap_server, $ldq_lang, $ldq_ou_filter, $charset, $orgs, $orgs3, $gcache;
    
    if(function_exists('uoa_initialize_cache')) {
        uoa_initialize_cache();
        if(isset($gcache)) {
            if(  (($orgs = $gcache->load('orgs_'.$ldq_lang)) !== false) &&
               (($orgs3 = $gcache->load('orgs3_'.$ldq_lang)) !== false) ) {

                 $_SESSION['orgs'] = $orgs;
                 $_SESSION['orgs3'] = $orgs3;
                 return;
            }
        }
    }
    
	$ldq_lds=0;
	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Port = $ldap_server[$ldq_lds]['port'];
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxrows = $ldap_server[$ldq_lds]['maxrows'];
	$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];
	$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
	$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];

	if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
		$error = "Could not connect to LDAP server " . $ldq_Server;
	}
	if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
		$error = "Unable to bind to LDAP server";
	} else {

		$orgs = array();

		$orgs['*']['struct'] = '';
		$orgs['*']['text'] = '<strong>'.$ldap_server[$ldq_lds]['name;lang-'.$ldq_lang].' '. _("(All)") . '</strong>';
		
		$orgfilter = $ldq_ou_filter;
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
			$dn = strtolower($orgentry[$i]['dn']);
			
			/* Struct */
			if(isset($orgentry[$i][$struct_lang][0])) {
				$orgs[$dn]['struct'] = directory_string_convert($orgentry[$i][$struct_lang][0], "UTF-8", $charset);
			} elseif(isset($orgentry[$i][$struct][0])) {
				$orgs[$dn]['struct'] = directory_string_convert($orgentry[$i][$struct][0], "UTF-8", $charset);
			}

			/* Text */
			if(isset($orgentry[$i][$attr_lang][0])) {
				$orgs[$dn]['text'] = directory_string_convert($orgentry[$i][$attr_lang][0], "UTF-8", $charset);
			} elseif(isset($orgentry[$i][$attr][0])) {
				$orgs[$dn]['text'] = directory_string_convert($orgentry[$i][$attr][0], "UTF-8", $charset);
			}
			/* Path */
			$brkdn = ldap_explode_dn($dn, 1);
			for($j=$brkdn['count']-3; $j >= 0 ;  $j--) {
				// print $brkdn[$j] . "<br />";
				$orgs[$dn]['path'][] = $brkdn[$j]; 
			}

			/* eduOrgSuperiorUri */
			if(isset($orgentry[$i]['eduorgsuperioruri'][0])) {
                list($orgs[$dn]['superior']['host'], $orgs[$dn]['superior']['dn']) =
                    directory_parse_eduorg_superior_url($orgentry[$i]['eduorgsuperioruri'][0]);
			}
			unset($dn);
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

        if(isset($gcache)) {
            $gcache->save($orgs, 'orgs_'.$ldq_lang);
            $gcache->save($orgs3, 'orgs3_'.$ldq_lang);
        }
	}
}

/**
 * This function will find all the children of a specific eduOrg ou. It is used
 * to make proper LDAP filters, when a superior eduOrgUnit is selected.
 *
 * @param array $restrict
 * @param array $inferior_final
 * @return void
 */
function directory_find_inferior($restrict, &$inferior_final) {
	global $orgs;
	$inferior = array();
	foreach($restrict as $no=>$restrictdn) {
		foreach ($orgs as $orgdn => $data) {
			if(isset($data['superior']['dn']) && $data['superior']['dn'] == $restrictdn) {
				// print "Hit. $orgdn <BR>";
				$inferior_final[] = $orgdn;
				$inferior[] = $orgdn;
			} else {
				continue;
			}
		}
		if(!empty($inferior)) {
			// print "<BR><BR>calling directory_find_inferior("; print_r($inferior); print_r($inferior_final);
			directory_find_inferior($inferior, $inferior_final);
		}
	}
}

?>
