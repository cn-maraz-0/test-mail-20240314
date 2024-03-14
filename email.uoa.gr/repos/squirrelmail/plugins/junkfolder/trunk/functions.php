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
 * $Id: functions.php,v 1.3 2006/12/27 15:04:50 avel Exp $
 */

/**
 * Functions for Junkfolder Plugin
 */

include_once(SM_PATH . 'plugins/junkfolder/config.php');
   
/**
 * Display notice to be placed above folder list.
 */
function junkfolder_right_notice_do() {
	global  $junkfolder_user, $junkfolder_useimage, $junkfolder_days, $plugins, $data_dir, $username;

	if(in_array('ldapuserdata', $plugins)) {
		$junkfolderDays = getpref($data_dir, $username, 'junkprune', $junkfolder_days);
	}

	$out = '';
	
	$prev = bindtextdomain ('junkfolder', SM_PATH . 'plugins/junkfolder/locale');
	textdomain ('junkfolder');

	if($junkfolder_useimage == true) {
		$out .= '<img src="../plugins/junkfolder/images/warning.png" alt="'. _("Warning") .
		' title="'. _("Warning") .'" align="center" /><br /> ';

	}
	
	if($junkfolderDays == 1) {
		$st = _("day");
	} else {
		$st = _("days");
	}

	$out .= _("Notice: This is your Junk Folder.") . " ";
	
	if($junkfolderDays > 0) {
		$out .= sprintf( _("Messages older than %s %s will be automatically removed from this folder.") ,
			$junkfolderDays, $st);
	} else {
		$out .= _("The amount of time that messages will stay in this folder has not been set yet.");
        if($junkfolder_days > 0) {
            $out .= ' '. sprintf(_("The default number of %s days will be used instead."), $junkfolder_days). ' '.
		        sprintf( _("This means that messages older than %s %s will be automatically removed from this folder.") , $junkfolder_days, $st);
        }
	}
	$out .= ' ' .  _("You can set the number of days in the") . ' '.
		'<a href="options.php?optpage=folder" target="right">' . 
		_("Folder Options Page") . '</a>.';

    echo $out;

	$prev = bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain('squirrelmail');
}

/**
 * Display Create Junkfolder button
 */
function junkfolder_createbutton_do () {
	global  $mailbox, $junkfolder_user, $junkfolder_useimage,
		$junkfolder_days, $junkfolder_autocreate, $color, $boxes,
		$skip_folders, $plugins;
	
	$prev = bindtextdomain ('junkfolder', SM_PATH . 'plugins/junkfolder/locale');
	textdomain ('junkfolder');
	
	if(in_array('ldapuserdata', $plugins)) {
		if(isset($_SESSION['ldap_prefs_cache']['junkprune'])) {
			$junkfolderDays = $_SESSION['ldap_prefs_cache']['junkprune'];
		} else {
			$junkfolderDays = -1;
		}
	}


echo html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
	html_tag( 'tr' ) .
	html_tag( 'td', '', 'center', $color[4] ) .

	html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
		html_tag( 'tr',
		html_tag( 'td', '<b>' . _("Junk Folder") . '</b>', 'center', $color[9] )
		) .
		html_tag( 'tr' ) .
		html_tag( 'td', '', 'center', $color[0] ) .

		     '<form name="createjunkfolder" action="../plugins/junkfolder/junkfolder_create.php"
		     method="POST">';

		echo '<p>' . _("The Junk Folder is a special type of folder that you can use to store Junk Mail such as SPAM (unsolicited mails).") . ' ';
		
		if($junkfolderDays == 1) {
			$st = _("day");
		} else {
			$st = _("days");
		}

	
		if(in_array('ldapuserdata', $plugins)) {
			// print '<form method="post" action="folders.php">';
			/*
			$form = '<input type="text" size="2" maxsize="2" name="junkfolder_days" value="'.$junkfolderDays.'" />';
			*/
			/*
			$form = '<select size="0" name="junkfolder_days" value="'.$junkfolderDays.'" />';
			for($i=1; $i<=30; $i++) {
				$form .= '<option value="'.$i.'"';
				if($junkfolderDays == $i) {
					$form .= ' selected=""';
				}
				$form .= '>'.$i.'</option>';
			}
			$form .= '</select>';
			*/

			print '</p><p>';

			if($junkfolderDays > 0) {
				$form = '<strong>' . $junkfolderDays . '</strong>';
				printf( _("Messages older than %s %s will be automatically removed from this folder.") , $form, $st);
			} elseif($junkfolderDays == 0) {
				print _("Messages are never removed automatically from this folder. Note that if you have set this folder in your SPAM rule, it can grow quickly and you'll have to delete the emails manually. You can set messages older than a defined number of days to be deleted automatically.");
			} else {
				print _("The amount of time that messages will stay in this folder has not been set yet.");
                if($junkfolder_days > 0) {
                    print ' '. sprintf(_("The default number of %s days will be used instead."), $junkfolder_days). ' ';
			        printf( _("This means that messages older than %s %s will be automatically removed from this folder.") , $junkfolder_days, $st);
                }

			}

			print ' ' .  _("You can set the number of days in the") . ' '.
				'<a href="options.php?optpage=folder" target="right">' . 
				_("Folder Options Page") . '</a>.';

			// print ' <input type="submit" name="junkfolder_set_days" value="'. _("Set") .'" />';
			// print '</form>';
		} else {
			printf( _("Messages older than %s %s will be automatically removed from this folder.") , $junkfolderDays, $st);
		}
		echo '</p><br />';

		$exists = false;
		
		for($i=0; $i<sizeof($boxes); $i++) {
			if($boxes[$i]['unformatted'] == $junkfolder_user ) {
				$exists=true;
				break;
			}
		}
			

		if ($exists==true){
			echo '<p>' . _("Junk Folder already exists.") . '<br />';
			echo ' <a href="../src/right_main.php?mailbox='.urlencode($junkfolder_user).'">';
			echo _("View Junk Folder contents");
			echo '</a></p>';

		} else {
			echo '<p><input type="submit" name="createjunkfolder" value="'.
				_("Create Junk Folder") . '" /></p>';
			if($junkfolder_autocreate == true) {
				echo '<p>'. _("Note that the Junk Folder will be autocreated, if you have configured a filter to place messages in it.") . '</p>';
			}
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


/**
 * Options Setting for Number of days.
 */
function junkfolder_options_do() {
	global $optpage_data, $junkfolder_days, $plugins, $data_dir, $username;

	if(in_array('ldapuserdata', $plugins)) {
    $junkfolderDays = getpref($data_dir, $username, 'junkprune');
	if(empty($junkfolderDays)) { 
        $junkfolderDays = $junkfolder_days;
    }
	
	$prev = bindtextdomain ('junkfolder', SM_PATH . 'plugins/junkfolder/locale');
	textdomain ('junkfolder');

	$optpage_data['grps']['junkfolder'] = _("Junk Folder Options");
	$optionValues = array();
	$optionValues[] = array(
		'name' => 'junkprune',
		'caption' => _("Messages older than this number, in days, will be purged from the Junk Folder"),
		'type' => SMOPT_TYPE_STRLIST,
		'refresh' => SMOPT_REFRESH_NONE,
		'posvals' => array(
			0 => _("Never"),
			1 => '1',
			2 => '2',
			3 => '3',
			4 => '4',
			5 => '5',
			6 => '6',
			7 => '7',
			8 => '8',
			9 => '9',
			10 => '10',
			11 => '11',
			12 => '12',
			13 => '13',
			14 => '14',
			15 => '15',
			16 => '16',
			17 => '17',
			18 => '18',
			19 => '19',
			20 => '20',
			21 => '21',
			22 => '22',
			23 => '23',
			24 => '24',
			25 => '25',
			26 => '26',
			27 => '27',
			28 => '28',
			29 => '29',
			30 => '30'
		),
		'initial_value' => $junkfolderDays,
		// 'save' => ....
		'size' => SMOPT_SIZE_TINY
	);
	$optpage_data['vals']['junkfolder'] = $optionValues;
	
	$prev = bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain('squirrelmail');
	}
}

?>
