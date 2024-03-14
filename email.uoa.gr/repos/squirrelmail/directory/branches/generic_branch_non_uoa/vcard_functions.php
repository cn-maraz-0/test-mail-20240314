<?php
/**
 * vcard_functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: vcard_functions.php,v 1.3 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Various functions related to vCard sending
 */

/**
 * Easily Gather vCard information
 *
 * @param string $me
 * @param string $attr
 * @param string $varname
 * @global array $privateattrs
 * @return void
 */
function vcget($me, $attr, $varname = '') {

	if(empty($varname)) {
		$varname = $attr;
	}
	global $$varname, $ldq_lang, $privateattrs;

	$attrlang = $attr . ';lang-'.$ldq_lang;

	if(isset($privateattrs) && in_array($attr, $privateattrs)) {
		$$varname = '';
	} else {
		if(isset($me[$attrlang]) && $me[$attrlang]['count'] > 0) {
			$$varname = trim($me[$attrlang][0]);
		} elseif(isset($me[$attr]) && $me[$attr]['count'] > 0 ) {
			$$varname = trim($me[$attr][0]);
		} else {
			$$varname = '';
		}
	}
}

?>
