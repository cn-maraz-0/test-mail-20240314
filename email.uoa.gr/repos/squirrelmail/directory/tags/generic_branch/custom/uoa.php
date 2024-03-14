<?php
/**
 * uoa.php
 *
 * Copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage custom
 * @version $Id: uoa.php,v 1.15 2005/04/19 17:01:57 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * UoA (National and Kapodistrian University of Athens, http://www.uoa.gr )
 * Custom Functions. Most probably not useful for anyone else.
 */

unset($affiliations_map['member']);
unset($affiliations_map['alum']);

/**
 * Search for OrgUnit dn's, and cache them into session.
 * @global array $ldap_server
 * @global array $orgs
 * @global array $orgs3
 */
function cache_orgunitdns() {
	global $ldap_server, $ldq_lang, $charset, $orgs, $orgs3;

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
		
		/* TODO - perhaps move these to configuration file */
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
			$dn = strtolower($orgentry[$i]['dn']);
			
			/* Struct */
			if(isset($orgentry[$i][$struct_lang][0])) {
				$orgs[$dn]['struct'] = directory_string_convert($orgentry[$i][$struct_lang][0], "UTF-8", $charset);
			} else {
				$orgs[$dn]['struct'] = directory_string_convert($orgentry[$i][$struct][0], "UTF-8", $charset);
			}

			/* Text */
			if(isset($orgentry[$i][$attr_lang][0])) {
				$orgs[$dn]['text'] = directory_string_convert($orgentry[$i][$attr_lang][0], "UTF-8", $charset);
			} else {
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
				$urlinfo = parse_url($orgentry[$i]['eduorgsuperioruri'][0]);
				$orgs[$dn]['superior']['dn'] = str_replace('/', '', strtolower($urlinfo['path']));
				$orgs[$dn]['superior']['host'] =  strtolower($urlinfo['host']);
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
	}
}

/**
 * Custom Function for UoAStructuralType attribute.
 */
function display_UoAStructural_listbox($structural, $selected, $name){
	
	$temp = array();
	foreach($structural['eduorgsuperioruri'] as $key => $value){
		$parse_array = parse_url($value);
		$sup = substr($parse_array['path'],1);
		if(!isset($temp[$sup]))
			$temp[$sup][0] = $key;
		else
			$temp[$sup][count($temp[$sup])] = $key;
	}
	$display = array();
	$start = array_values($temp['top_level']);
	$depth = 0;
	find_children($start,$structural, $temp, $display, $depth);
	$display = array_reverse($display);
	echo "<pre>";print_r($display);echo "</pre>";

	print '<select size=10 width=100 NAME="'.$name.'" multiple>';
	global $ldap_base_dn;
	if(is_array($selected) && in_array($ldap_base_dn,$selected))
		print '<option value="'.$ldap_base_dn.'" selected';
	else
		print '<option value="'.$ldap_base_dn.'"';
	print '> --- All People  --- </option>';
	foreach($display as $dn => $attr){
		if(is_array($selected) && in_array($dn,$selected))
			print '<option value="'.$dn.'" selected>';
		else
			print '<option value="'.$dn.'">';
		$tab='';
		for($i=0;$i<$attr['depth'];$i++){
			$tab.='--';
		}
		print $tab.$attr['cn'];
		print '</option>';
	}
	print '</select>';
}

/**
 * Recursive function for traversing a custom tree...
 */
function find_children(&$current, $full, $init, &$result, &$depth){
	//print "<pre>"; print_r($result); print "</pre>";
	//print "<pre>"; print_r($depth); print "</pre>";
	if(count($current)){
		$dn = $full['dn'][$current[0]];
		if( (isset($init[$dn])) && (!isset($result[$full['dn'][$init[$dn][0]]])) ){
			$next = array_values($init[$dn]);
			$depth++;
			foreach($next as $val){
				array_unshift($current,$val);
			}
			find_children($current, $full, $init, $result, $depth);
		}
		else{
			if(isset($init[$dn][0])){
				$depth--;
			}
			$result[$dn]['cn'] = $full['cn'][$current[0]];
			$result[$dn]['UoAStructuralType'] = $full['UoAStructuralType'][$current[0]];
			$result[$dn]['depth'] = $depth;
			$temp = array_shift($current);
			find_children($current, $full, $init, $result, $depth);
		}
	}
}


/**
 * Print "select" options for Restrict to Organizational Units feature.
 * This algorithm needs a properly formatted "$orgs2" array.
 * @param array $orgs2
 * @param int $level
 * @return void
 */
function directory_print_orgs2($orgs2, $level) {
	global $restrict;

	foreach($orgs2 as $id=>$info) {
		$ind = '&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}

		if(isset($info['text'])) {
			echo '<option value="'.$info['dn'].'"';
			if(isset($restrict) && in_array($info['dn'], $restrict)) {
				echo ' selected=""';
			}
			echo '>'.$indent.$info['struct']. ' ' .$info['text']."</option>\n";
		}
		if(isset($info['sub'])) {
			directory_print_orgs2($info['sub'], $level+1);
		}
	}
}

/**
 * Print "select" options for Restrict to Organizational Units feature.
 * This algorithm needs a properly formatted "$orgs3" array.
 * @param array $orgs3
 * @param int $level
 * @global array $restrict
 * @global int $trim_at
 * @return void
 */
function directory_print_orgs3($orgs3, $level) {
	global $restrict, $trim_at;
	foreach($orgs3 as $dn=>$info) {
				print $dn;
		$ind = '&nbsp;&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}
		if(isset($info['text'])) {
			if(isset($trim_at) && strlen($info['text']) > $trim_at && $dn != '*') {
				$text = substr($info['text'], 0, $trim_at);
				$text .= '...';
			} else {
				$text = $info['text'];
			}
			echo '<option value="'.$dn.'"';
			if(isset($restrict) && in_array($dn, $restrict)) {
				echo ' selected=""';
			}
			echo '>'.$indent.$info['struct']. ' ' .$text."</option>\n";
		}
		if(isset($info['sub'])) {
			directory_print_orgs3($info['sub'], $level+1);
		}
	}
}

function directory_print_browse_tree($orgs3, $level, $stop_at = 1, $current = '', $expand = array()) {
	global $restrict, $trim_at, $ldq_standalone;
	
	if($stop_at == 0) {
		return;
	}

	if($ldq_standalone) {
		$target_browse = 'dirbrowse';
		$target_results = 'dirresults';
	} else {
		$target_browse = '_self';
		$target_results = '_self';
	}

	foreach($orgs3 as $dn=>$info) {

		if(isset($niaou)) unset($niaou);
		if(isset($gab)) unset($gab);

		$ind = '&nbsp;&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}
		if(isset($info['text'])) {
			if(isset($trim_at) && strlen($info['text']) > $trim_at && $dn != '*') {
				$text = substr($info['text'], 0, $trim_at);
				$text .= '...';
			} else {
				$text = $info['text'];
			}
			
			echo $indent;
				
				/* Set up "Rest of URL", for all links below! */
				$restofurl = '';
				if(isset($level)) {
					$restofurl .= '&amp;level='.$level;
				}
				if(isset($expand)) {
					foreach($expand as $exp) {
						$restofurl .= '&amp;expand[]='.urlencode($exp);
					}
				}

			global $formname, $inputname, $popup;
			if(!empty($formname)) {
				$restofurl .= '&amp;formname='.urlencode($formname);
			}
			if(!empty($inputname)) {
				$restofurl .= '&amp;inputname='.urlencode($inputname);
			}
			if(isset($popup) && $popup == 1) {
				$restofurl .= '&amp;popup=1';
			}

			/* Plus/Minus gif, expand/collapse functionality */
			if(array_key_exists('sub', $info)) {
				if(in_array($dn, $expand) ) {
					echo '<a href="browse.php?collapse='.urlencode($dn).$restofurl.'">'.
						'<img src="images/toc-minus.gif" alt="[-]" border="0" /></a>';
				} else {
					echo '<a href="browse.php?expand[]='.urlencode($dn).$restofurl.'#'.urlencode($dn).'">'.
						'<img src="images/toc-plus.gif" alt="[+]" border="0" /></a>';
				}
					
			} else {
					echo '<img src="images/toc-blank.gif" alt="[ ]"border="0" />';
			}

			/* Text & Link Print */
			if($dn == $current) {
				echo '<strong><a name="'.urlencode($dn).'">';
				$niaou = true;

			} elseif($dn == '*') {
				echo '<em>';
				$gab = true;
			} else {
				// echo '<a href="browse.php?dn='.urlencode($dn).$restofurl;
				// echo '&amp;expand[]='.urlencode($dn).'" target="dirresults">';
				
				$selfuri = 'browse.php?dn='.urlencode($dn).$restofurl.
					'&amp;expand[]='.urlencode($dn);
				$selftarget = urlencode($dn);

				echo '<a name="'.urlencode($dn).'" href="showeduorginfo.php?dn='.urlencode($dn).$restofurl.'"';
				echo ' target="'.$target_results.'" onClick="window.location.href=\''.$selfuri.'#'.$selftarget.'\';'.
					// 'window.location.hash=\''.$selftarget.'\';'.
					'return true;">';

			}
			
			echo $info['struct']. ' ' .$text;

			if(isset($niaou)) {
				echo '</a></strong> '.
				'<a href="directory.php?searchform=no&amp;browseorgdn='.urlencode($dn).'" '.
				'title="'._("Browse All People in this Organizational Unit").'" target="'.$target_results.'">'.
				'<img src="images/people-16.gif" alt="'. _("Browse") .'" align="center" border="0" /></a>'.

				' <a href="directory.php?searchform=yes&amp;restrict[]='.urlencode($dn).'&amp;mode=2" '.
				'title="'._("Search for People in this Organizational Unit") .'" target="'.$target_browse.'">'.
				'<img src="images/search-16.gif" alt="'._("Search").'" align="center" border="0" /></a>';
				

			} elseif(isset($gab)) {
				echo '</em> ';
				
			} else {
				echo '</a>';
			}
			echo "<br />\n";
		}

		if(isset($info['sub']) && in_array($dn, $expand) ) {
			//print "directory_print_browse_tree(".$info['sub'].", $level+1, $stop_at, $current)\n<br>";
			directory_print_browse_tree($info['sub'], $level+1, $stop_at, $current, $expand);
		} elseif(isset($info['sub'])) {
			directory_print_browse_tree($info['sub'], $level+1, $stop_at-1, $current, $expand);
		}
	}
}


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
