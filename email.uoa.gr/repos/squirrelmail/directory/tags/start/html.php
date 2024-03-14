<?php
/**
 * html.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: html.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

function directory_printheader($title) {

	global $color;
	
	print '<br>
	<table bgcolor="'.$color[0].'" width="95%" align="center" cellpadding="2" cellspacing="0" border="0">
	<tr><td align="center">
	    <strong>'. $title . '</strong>';
	    
	print '
	    <table width="100%" border="0" cellpadding="5" cellspacing="0">
	    <tr><td bgcolor="'.$color[4].'" align="center">
	';
}

function directory_print_all_sections_start() {

	echo '<table width="85%" cols="1" align="center" cellpadding="2" cellspacing="0" border="0">';

}

function directory_print_section_start($title) {

	global $color, $error;

	print "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>".
	     $title .
	     "</B></TD></TR>";

	if(isset($error)) {
		print '<TR><TD BGCOLOR="'.$color[2].'" ALIGN="CENTER"><p><font color="'.$color[8].'"><strong>'.
		$error . '</strong></font></TD></TR>';
	
	}

	print "<TR><TD BGCOLOR=\"$color[0]\" >";

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
