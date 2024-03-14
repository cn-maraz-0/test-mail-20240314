<?php
/*
 * JunkFolder plugin for Squirrelmail 1.4+
 *
 * Copyright (c) 2003 Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * $Id: setup.php,v 1.1.1.1 2003/10/02 15:55:02 avel Exp $
 */
   
include_once(SM_PATH . 'plugins/junkfolder/config.php');

function squirrelmail_plugin_init_junkfolder() {
	global $squirrelmail_plugin_hooks;
      
	$squirrelmail_plugin_hooks['special_mailbox']['junkfolder'] = 'junkfolder_markspecial';
	$squirrelmail_plugin_hooks['right_main_after_header']['junkfolder'] = 'junkfolder_right_notice';
	$squirrelmail_plugin_hooks['folders_bottom']['junkfolder'] = 'junkfolder_createbutton';
}

function junkfolder_markspecial($box) {

	global $junkfolder_user;

	if($box == $junkfolder_user) {
		return true;
	}
}
   

function junkfolder_right_notice() {
	global  $note, $mailbox, $junkfolder_user, $junkfolder_useimage,
		$junkfolder_days;

	if($mailbox == $junkfolder_user) {
		
		$note = '';
		
		$prev = bindtextdomain ('junkfolder', SM_PATH . 'plugins/junkfolder/locale');
		textdomain ('junkfolder');

		if($junkfolder_useimage == true) {
			$note .= '<img src="../plugins/junkfolder/images/warning.png" alt="Warning" title="Warning" align="center" /><br /> ';

		}
		
		if($junkfolder_days == 1) {
			$st = _("day");
		} else {
			$st = _("days");
		}

		$note .= _("Notice: This is your Junk Folder.") . " ";
		$note .= sprintf( _("Messages older than %s %s will be automatically removed from this folder.") , $junkfolder_days, $st);

		$prev = bindtextdomain('squirrelmail', SM_PATH . 'locale');
		textdomain('squirrelmail');

	}
}


function junkfolder_createbutton() {

	global  $note, $mailbox, $junkfolder_user, $junkfolder_useimage,
		$junkfolder_days, $color, $boxes, $skip_folders;
	
	$prev = bindtextdomain ('junkfolder', SM_PATH . 'plugins/junkfolder/locale');
	textdomain ('junkfolder');

echo html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
	html_tag( 'tr' ) .
	html_tag( 'td', '', 'center', $color[4] ) .

	html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
		html_tag( 'tr',
		html_tag( 'td', '<b>' . _("Create Junk Folder") . '</b>', 'center', $color[9] )
		) .
		html_tag( 'tr' ) .
		html_tag( 'td', '', 'center', $color[0] ) .

		     '<form name="createjunkfolder" action="../plugins/junkfolder/junkfolder_create.php"
		     method="POST">';

		echo '<p>' . _("The Junk Folder is a special type of folder that you can use to store Junk Mail such as SPAM (unsolicited mails).") . ' ';
		
		if($junkfolder_days == 1) {
			$st = _("day");
		} else {
			$st = _("days");
		}

		printf( _("Messages older than %s %s will be automatically removed from this folder.") , $junkfolder_days, $st);

		$exists = false;
		
		for($i=0; $i<sizeof($boxes); $i++) {
			if($boxes[$i]['unformatted'] == $junkfolder_user ) {
				$exists=true;
				break;
			}
		}
			

		if ($exists==true){
			echo '</p><br />';
			echo _("Junk Folder already exists.");
			echo ' <a href="../src/right_main.php?mailbox='.urlencode($junkfolder_user).'">';
			echo _("View Junk Folder contents");
			echo '</a></p>';

		} else {

			echo '</p><br /><input type="submit" name="createjunkfolder" value="';
			echo _("Create Junk Folder") ;
		
			echo '" >';
		}

		echo '</form>';

		$prev = bindtextdomain('squirrelmail', SM_PATH . 'locale');
		textdomain('squirrelmail');


echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."</table>\n";

echo '</td></tr>
</table>';


}

function junkfolder_version() {
	return '1.0';
}
 
?>
