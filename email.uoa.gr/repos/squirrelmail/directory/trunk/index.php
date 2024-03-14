<?php
/**
 * directory.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: index.php,v 1.4 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Redirect to login page if in Squirrelmail; redirect to main page if in
 * standalone mode. 
 */

include_once ('config.php');

if($ldq_standalone) {
	header("Location:frameset.php\n\n");
	exit();
} else {
	header("Location:../../src/login.php\n\n");
	exit();
}

?>
