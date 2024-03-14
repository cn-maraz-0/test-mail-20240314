<?php
/**
 * ldap_checkprefs.php
 *
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@noc.uoa.gr>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * A simple script to be used to check someone's preferences.
 * To be used like: ldap_checkprefs.php?uid=username
 * Not to be placed in public; remove the initial exit() and put it in a secure
 * place.
 *
 * $Id: ldap_checkprefs.php,v 1.1 2004/02/10 15:00:37 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

exit();

define(SM_PATH, "../../../");

include(SM_PATH . 'plugins/ldapuserdata/config.php');
include(SM_PATH . 'plugins/ldapuserdata/setup.php');

$ldap = ldapuserdata_ldap_connect('squirrel', false);

$userprefs = retrieve_data($_GET['uid'], 'normal');

echo '<table border="1">';
	echo '<th><tr><td>Key</td><td>Value</td></tr></th>';

foreach ($userprefs as $key=>$val ) {
	echo '<tr><td>';
	echo htmlspecialchars($key);
	echo '</td>';
	echo '<td>';
	if(is_array($val)) {
		foreach($val as $k=>$v) {
			echo "<em>".htmlspecialchars($k) . "</em> => " .htmlspecialchars($v)."<br />";
		}
	} else {
		echo htmlspecialchars($val);
	}
	echo '</td></tr>';
}
echo '</table>';


?>
