<?php
/**
 * uoa.php
 *
 * Copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: uoa.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * UoA (National and Kapodistrian University of Athens, http://www.uoa.gr )
 * Custom Functions. Most probably not useful for anyone else.
 */

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
 * @param $orgs3 array
 * @param $level int
 * @return void
 */
function directory_print_orgs3($orgs3, $level) {
	global $restrict, $trim_at;


	foreach($orgs3 as $dn=>$info) {
		$ind = '&nbsp;&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}

		if(isset($info['text'])) {

			if(isset($trim_at) && strlen($info['text']) > $trim_at) {
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

?>
