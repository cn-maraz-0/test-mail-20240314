<?php
/**
 * useracl.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 */

/**
 * Functions that output HTML markup, used for the presentation logic of the
 * plugin
 */

/**
 * Print permissions table header row
 *
 * @return void
 */
function useracl_print_table_header($separate = false) {
	global $color;

	print '<tr>';

	// '<TH WIDTH=10></TH>' . /* For checkbox */
	if($separate==true) {
		print '<th width="35%">' . _("Folder") . '</th>';
	}
	
	print '<th width="35%">' . _("User") . '</th>';
	
	if($separate!=true){
		print '<th width="10%"><small>' . _("None") . '</small></th>';
	}

	print '<th width="10%"><small>' . _("Read") . '</small></th>'.
	'<th width="10%"><small>' . _("Append") . '</small></th>' .
	'<th width="10%"><small>' . _("Delete") . '</small></th>' .
 	'<th width="10%"> </th></tr>';
}

/**
 * Print permissions table Add New User row
 *
 * @return void
 */
function useracl_print_addnew($formname = '') {
	global $color, $useracl_show_images ;

	print '<tr bgcolor="'.$color[4].'">'.
		// '<td></td>'. // For checkbox
		'<td align="left" nowrap=""><input type="text" name="newuser" value="" size="20" />';
	
	useracl_print_ldapsearchbutton(urlencode($formname));

	print '</td>'.
		'<td></td>';
	useracl_print_addnew_perms();
	print '<td align="center">';
	if($useracl_show_images) {
		print '<input type="image" name="adduser" alt="'. _("Add User") .'" value="add" '.
		'title="'. _("Add User") .'" src="images/add.gif" /></td>';
	} else {
		print '<input type="submit" name="adduser" value="'. _("Add User") .'" /></td>';
	}
	print "</tr>\n";

}

/**
 * Print permissions table New User permissions radio button columns
 *
 * @return void
 */
function useracl_print_addnew_perms() {

	print '<td align="center"><input type="radio" checked="" name="new_user_perm_radio" value="read"></td>'.
		'<td align="center"><input type="radio" name="new_user_perm_radio" value="append"></td>'.
		'<td align="center"><input type="radio" name="new_user_perm_radio" value="delete"></td>';
}

/**
 * Print permissions table row for existing user
 *
 * @param array $perm_array Array that describes this mailbox's ACLs
 * @param string $mbox Mailbox Name
 *
 * @return void
 */
function useracl_print_array($perm_array, $mbox){
	global $color, $names, $mails, $useracl_show_images;
	$tabs[0] = "none";
	$tabs[1] = "read";
	$tabs[2] = "append";
	$tabs[3] = "delete";

	/*
	$tabs[0] = "read";
	$tabs[1] = "append";
	$tabs[2] = "delete";
	*/

	$i=0;
	$toggle = false;

	foreach($perm_array as $user=>$perm){
		echo "<tr";
		if ($toggle) {
			print ' bgcolor="'.$color[12].'"';
		} else {
		 	print ' bgcolor="'.$color[4].'"';
		    }
		print '>';

		if(!$toggle) {
			$toggle = true;
		} elseif($toggle) {
			$toggle = false;
		}

		/* No checkbox. Instead we have the "none" radio button. */
		/*
		echo '<td align="center">' .
			// '<input type="checkbox" name="delete_checked['.$i.']" value="'.$user.'">'.
			'</td>';
		*/

		echo '<td align="left" nowrap="">';
		if(isset($mails[$user])) {
			$comp_uri = 'src/compose.php?mailbox='.$mbox.'&amp;send_to='.urlencode($mails[$user]);
		}
		
		if(isset($names[$user])) {
			$disp = $names[$user] . ' <small>('.$user.')</small>';
		} else {
			$disp = $user;
		}
		if(isset($comp_uri) && isset($disp)) {
			echo makeComposeLink($comp_uri, $disp);
		} else {
			echo $disp;
		}

		echo '</td>';

		for($k=0;$k<count($tabs);$k++) {
			// $radio_checked = $perm == $tabs[$k] ? 'checked=""' : '';
			//'<input type="radio" '.$radio_checked.' name=perm_radio['.$user.'] value='.$tabs[$k].'>'.
			
			echo '<td align="center"><input type="radio" name=perm_radio['.$user.'] value="'.$tabs[$k].'"';
			if($perm == $tabs[$k]) {
				echo ' checked=""';
			}
			echo '/>';
			if($perm == $tabs[$k]) {
				echo '<input type="hidden" name=perm_prev['.$user.'] value="'.$perm.'"/>';
			}
			echo '</td>';
		}

		echo '<td align="center">';
		if($useracl_show_images) {
			echo '<input type="image" name=update['.$user.'] value="'. _("Update") .'" '.
			'title="'. _("Update") .'" src="images/update.gif" />';
		} else {
			echo '<input type="submit" name=update['.$user.'] value="'. _("Update") .'"/>';
		}
		echo "</td></tr>\n";

		echo '<input type="hidden" name=all_users['.$i.'] VALUE="'.$user.'">';
		echo '<input type="hidden" name=user_position['.$user.'] VALUE="'.$i.'">';
		$i++;
	}
}
	

/**
 * Print permissions table footer row
 *
 * @return void
 */
function useracl_print_table_footer() {
	global $useracl_enable_notify, $useracl_show_images;
	
	echo '<tr> <td align="left" colspan="2">';
	// '<a href=\"javascript:SetChecked(1)\"> Check All </a> - <a href=\"javascript:SetChecked(0)\"> Clear All </a> ' .
	if($useracl_enable_notify) {
		echo '<input type="checkbox" name="notify" value="1" />';
		if($useracl_show_images) {
			echo '<img src="images/mail.gif" alt="' . _("Notify user(s) by email") .'" '.
				'title="' . _("Notify user(s) by email") .'" align="center" />';
		} else {
			// '<label for="notify">'.
			echo _("Notify user(s) by email");
			// .'</label>';
		}

	}

	echo '</td>'.
	'<td align="right" colspan="4">' .
	// FIXME
	// '<input type="submit" name="delete_checked" value="' . _("Delete Checked") . '">'.
	'<input type="submit" name="update_all" value="' . _("Update All") . '"> </td>'.
	'</tr>';
}

/**
 * Print New User Row for separate table, with mailbox select
 *
 * @param string $mybox Optional parameter for preselected mailbox field
 * @param string $myuser Optional parameter for prefilled user field
 * @return void
 */
function useracl_print_addnew_separate($mybox = '', $myuser = '') {
	global $color, $boxes, $useracl_show_images, $useracl_enable_notify;
	
	print '<tr bgcolor="'.$color[4].'">'.
		'<td><select name="mbox" size="0">';
	foreach($boxes as $no=>$box) {
		$mbox = $box['unformatted'];
		if(strstr($mbox, 'INBOX') && $mbox != 'INBOX' ) {
			print '<option value="'.urlencode($box['unformatted']).'"';
			if(!empty($mybox) && $mybox == $box['unformatted']) {
				print ' selected=""';
			}
			print '>'.$box['formatted'].'</option>';
		}
	}
	print '</select>';
	
	
	print '</td>'.
		'<td align="center"><input type="text" name="newuser" value="';
		if(!empty($myuser)) {
			print htmlspecialchars($myuser);
		}
	print '" size="18" />';

	/* Search LDAP button */
	useracl_print_ldapsearchbutton('form_addnew');
	
	/* Notification button */
	if($useracl_enable_notify) {
		echo '<br /><div align="center">';
		echo '<input type="checkbox" name="notify" value="1" />';
		if($useracl_show_images) {
			echo '<img src="images/mail.gif" alt="' . _("Notify user(s) by email") .'" '.
				'title="' . _("Notify user(s) by email") .'" align="center" />';
		} else {
			// '<label for="notify">'.
			echo _("Notify user(s) by email");
			// .'</label>';
		}
		echo '</div>';
	}
	
	print '</td>';
	useracl_print_addnew_perms();
	print '<td align="center"><input type="submit" name="adduser" value="'. _("Add User") .'" /></td>';
	print '</tr>';
	

}

/**
 * Print button that links to the directory plugin search page.
 * The button will be printed if you have enabled the 'directory' plugin.
 *
 * @param string $formname The formname to link to, by javascript means.
 * @see http://email.uoa.gr/projects/squirrelmail/directory.php
 */
function useracl_print_ldapsearchbutton($formname = 'form_addnew') {
	global $plugins, $useracl_show_images;

	if(in_array('directory', $plugins)) {
	        echo "<script type=\"text/javascript\"><!--\n document.write(\"";

		if($useracl_show_images) {
		echo 
		  "<a href=\\\"javascript:void(0);\\\"".
		  " onclick=\\\"window.open('../directory/directory.php?popup=1&amp;formname=$formname', ".
		  "'directory', 'status=no,scrollbars=yes,width=780,height=580,resizable=yes')\\\">" .
		  "<img src=\\\"images/search-16.gif\\\" alt=\\\""._("Search...").
		  "\\\" title=\\\""._("Search...")."\\\" align=\\\"center\\\" border=\\\"0\\\" /></a>\");" . "\n";

		} else {

		echo 
		  "<input type=\\\"button\\\" value=\\\""._("Search...").
		  "\\\" onclick=\\\"window.open('../directory/directory.php?popup=1&amp;formname=$formname', ".
		  "'directory', 'status=no,scrollbars=yes,width=780,height=580,resizable=yes')\\\">\");" . "\n";
		}

		echo "// --></SCRIPT>";
             
		/* Non-javascript browsers not supported at the moment. */
		/*
		echo "<NOSCRIPT>\n".
		  " <input type=submit name=\"html_dir_search\" value=\""._("Directory")."\">".
		  "</NOSCRIPT>\n";
		} else {
			echo ' <input type=submit name="html_dir_search" value="'._("Directory").'">' . "\n";
        	}
		*/
	}
}

/**
 * HTML Output functions follow.
 */


function useracl_html_printheader($title) {

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

function useracl_html_print_all_sections_start() {

	echo '<table width="95%" cols="1" align="center" cellpadding="2" cellspacing="0" border="0">';

}


function useracl_html_print_section_start($title) {

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

function useracl_html_print_section_end() {

	global $color;
	
	echo "</TD></TR>\n";
	echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";
}

function useracl_html_print_all_sections_end() {

	echo "</table>";

}

function useracl_html_printfooter() {

	print '</td></tr></table>';
	print '</td></tr></table>';

}


?>
