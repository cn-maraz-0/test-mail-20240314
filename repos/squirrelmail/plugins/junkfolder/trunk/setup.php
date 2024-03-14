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
 * $Id: setup.php,v 1.2 2004/01/02 16:12:58 avel Exp $
 *
 * @package plugins
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 */
   
/**
 * Plugin registration and wrappers for the Junkfolder plugin.
 */

include_once(SM_PATH . 'plugins/junkfolder/config.php');
  
/**
 * Squirrelmail Plugin registration.
 */
function squirrelmail_plugin_init_junkfolder() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['special_mailbox']['junkfolder'] = 'junkfolder_markspecial';
	$squirrelmail_plugin_hooks['right_main_after_header']['junkfolder'] = 'junkfolder_right_notice';
	$squirrelmail_plugin_hooks['folders_bottom']['junkfolder'] = 'junkfolder_createbutton';
	$squirrelmail_plugin_hooks['optpage_loadhook_folder']['junkfolder'] = 'junkfolder_options';
}

/**
 * Mark a junk folder as special
 */
function junkfolder_markspecial($box) {
	global $junkfolder_user;
	if($box == $junkfolder_user) {
		return true;
	}
}

/**
 * Wrapper for notice in folders page
 */
function junkfolder_right_notice() {
	global $junkfolder_user, $mailbox;
	if($mailbox == $junkfolder_user) {
		include_once(SM_PATH . 'plugins/junkfolder/functions.php');
		junkfolder_right_notice_do();
	}
}

/**
 * Wrapper for Create Junk Folder button Display, in folders page
 */
function junkfolder_createbutton() {
	include_once(SM_PATH . 'plugins/junkfolder/functions.php');
	junkfolder_createbutton_do();
}

/**
 * Junkfolder plugin version
 */
function junkfolder_version() {
	return '1.1';
}
 
/**
 * Wrapper for Function that defines Options Setting for Number of days.
 */
function junkfolder_options() {
	include_once(SM_PATH . 'plugins/junkfolder/functions.php');
	junkfolder_options_do();
}


?>
