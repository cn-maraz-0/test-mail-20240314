<?php
/**
 * html.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: html.php,v 1.9.2.1 2005/04/21 11:37:34 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

function directory_printheader($title) {

	global $color;
	
	print '<br>
	<table bgcolor="'.$color[0].'" width="100%" align="center" cellpadding="2" cellspacing="0" border="0">
	<tr><td align="center">
	    <strong>'. $title . '</strong>';
	    
	print '
	    <table width="100%" border="0" cellpadding="5" cellspacing="0">
	    <tr><td bgcolor="'.$color[4].'" align="center">
	';
}

function directory_print_all_sections_start() {

	echo '<table width="95%" cols="1" align="center" cellpadding="2" cellspacing="0" border="0">';

}

function directory_print_section_start($title) {
	global $color;
	print '<tr><td bgcolor="'.$color[9].'" align="center"><strong>'.$title.'</strong></td></tr>';
	print '<tr><td bgcolor="'.$color[0].'">';
}

function directory_print_section_end() {

	global $color;
	
	echo "</TD></TR>\n";
	echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";
}

function directory_print_all_sections_end() {

	echo "</table>";

}

function directory_printfooter() {

	print '</td></tr></table>';
	print '</td></tr></table>';

}

/**
 * Return the navigation bar for the various Directory Services options.
 * @return string
 */
function directory_navbar() {
	global $PHP_SELF, $ldq_standalone, $mode, $popup, $formname, $inputname,
		$ldq_support_eduperson;

	$opts_arr = array();

	if(isset($popup) && $popup == 1) {
		$opts_arr[] = 'popup=1';
	}
	if(!empty($formname)) {
		$opts_arr[] = 'formname='.urlencode($formname);
	}
	if(!empty($inputname)) {
		$opts_arr[] = 'inputname='.urlencode($inputname);
	}
	if($opts_arr) {
		$opts = implode('&amp;', $opts_arr);
	} else {
		$opts = '';
	}

	$out = '';

	if($ldq_support_eduperson) {
		/* Browse mode is available only when eduPerson / eduOrg are used. */
		$out .= _("Display Mode:") . ' ';
	
		if(strstr($PHP_SELF, 'browse.php')) {
			$out .= '<span class="active">'. _("Browse") .'</span>';
		} else {
			$out .= '<span class="inactive"><a href="browse.php';
			if($opts) {
				$out .= '?'.$opts;
			}
			$out .= '">'. _("Browse") .'</a></span>';
		}
		$out .= ' | ';
		if(strstr($PHP_SELF, 'directory.php')) {
			$out .= '<span class="active">'. _("Search") .'</span>';
		} else {
			$out .= '<span class="inactive"><a href="directory.php';
			if($opts) {
				$out .= '?'.$opts;
			}
			$out .= '">'. _("Search") .'</a></span>';
		}
		$out .= ' - ';
	}

	if(basename($PHP_SELF) == 'directory.php') {
		$out .= _("Operation Mode:") . ' ';
		if($mode == DIR_SIMPLE) {
			$out .= '<span class="active">'. _("Simple") .'</span>';
		} else {
			$out .= '<span class="inactive"><a href="'.$PHP_SELF.'?mode='.DIR_SIMPLE;
			if($opts) {
				$out .= '&amp;'.$opts;
			}
			$out .= '">'. _("Simple") .'</a></span>';
		}
		$out .= ' | ';
		if($mode == DIR_ADVANCED) {
			$out .= '<span class="active">'. _("Advanced") .'</span>';
		} else {
			$out .= '<span class="inactive"><a href="'.$PHP_SELF.'?mode='.DIR_ADVANCED;
			if($opts) {
				$out .= '&amp;'.$opts;
			}
			$out .= '">'. _("Advanced") .'</a></span>';
		}
	}
	return $out;
}

?>
