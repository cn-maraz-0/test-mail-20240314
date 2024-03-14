<?php
/**
 * misc.php
 *
 * Functions that do not fit elsewhere.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: misc.php,v 1.1 2005/06/27 12:41:48 avel Exp $
 * @copyright (c) 2004-2005 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Determines if an IP address lies within a particular network
 *
 * As described by php-net at dreams4net dot com on 08-Aug-2002 09:31
 *
 * @param string $network
 * @param string $mask
 * @param string $ip
 * @return boolean
 * @author php-net at dreams4net dot com
 */
function IP_Match($network, $mask, $ip) {
	$ip_long=ip2long($ip);
	$network_long=ip2long($network);
	$mask_long=ip2long($mask);

	if (($ip_long & $mask_long) == $network_long) {
		return true;
	} else {
		return false;
	}
}

?>
