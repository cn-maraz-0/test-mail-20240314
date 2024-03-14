<?php
/**
 * exported_functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage include
 * @version $Id: exported_functions.php,v 1.1 2007/01/03 14:20:00 avel Exp $
 * @copyright (c) 1999-2006 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Link to Directory UserInfo (vcard.php) page, if directory plugin is enabled.
 * @param string $user Username.
 * @return string
 */
function directory_user_link($user) {
	global $plugins, $base_uri;
	if(in_array('directory', $plugins)) {
		$vcard_uri = 'plugins/directory/vcard.php?uid='.urlencode($user);
		$vcard_txt = $user;
		$out = makeComposeLink($vcard_uri, $vcard_txt);
	} else {
		$out = $user;
	}
	return $out;
}

?>
