<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Copyright (c) 2002-2003 Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * $Id: standalone.inc.php,v 1.2 2003/10/07 13:24:52 avel Exp $
 */

/**
 * Functions that are in use when being a standalone PHP app (that is, instead
 * of a Squirrelmail plugin).
 * 
 * Still not used. Just a draft.
 */

function sqsession_is_active() {

}

function sqsession_register($value, $name) {

}

function sqgetGlobalVar($name, $value, $source) {

}

function displayPageHeader($color, $title) {

}

function sqimap_get_delimiter($imapConnection) {

}

function get_location() {

}

function  sqimap_get_user_server ($imapServerAddress, $username) {
}


function listmailboxes_cyrusmaster() {
	return false;
}

function listmailboxes_php($imaphost, $username, $password) {

	$mbox = imap_open($imaphost, $username, $password,OP_HALFOPEN)
	      || die("can't connect: ".imap_last_error());
	
	$list = imap_getmailboxes($mbox,"{your.imap.host}","*");
	
	if(is_array($list)) {
		reset($list);
		while (list($key, $val) = each($list)) {
			print "($key) ";
			print imap_utf7_decode($val->name).",";
			print "'".$val->delimiter."',";
			print $val->attributes."<br>\n";
			}
		} else {
			print "imap_getmailboxes failed: ".imap_last_error()."\n";
		}
	
	imap_close($mbox);
	
	return $list;

}

?>
