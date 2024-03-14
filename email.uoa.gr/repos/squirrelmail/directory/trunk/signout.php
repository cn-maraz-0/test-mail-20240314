<?php
/**
 * signout.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage standalone
 * @version $Id: signout.php,v 1.3 2004/07/13 11:08:33 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Signout from standalone mode
 */

include_once ('config.php');
if($ldq_standalone) {
	include_once ('standalone/standalone.php');
	
	if($logged_in) {
		session_destroy();
	}
	header("Location: editprofile.php?loggedout=1");
	exit;
}

?>
