<?php
/**
 * standalone_html.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage standalone
 * @version $Id: standalone_html.php,v 1.8 2005/04/07 16:29:36 avel Exp $
 * @copyright (c) 2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Functions for standalone HTML printing.
 */

function displayPageHeader($color, $title, $searchform = 'yes', $xtra = '') {

global $PHP_SELF, $language;

print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head><title>'. $title .'</title>
<link rel="STYLESHEET" type="text/css" href="style/directory.css" title="Directory Services Stylesheet">
<style type="text/css">
body {
';
if(strstr($PHP_SELF, 'editprofile.php')) {

} elseif(strstr($PHP_SELF, 'browse.php')) {
	echo 'background-image: url(images/text_browse_'.$language.'.png);';
} elseif(strstr($PHP_SELF, 'vcard.php')) {
	echo 'background-image: url(images/text_vcard_'.$language.'.png);';

} elseif(strstr($PHP_SELF, 'policy.php')) {
	echo 'margin-left: 1px; margin-top: 1px;';

} elseif(strstr($PHP_SELF, 'showeduorginfo.php')) {
	echo 'background-image: url(images/text_orginfo_'.$language.'.png);';

} elseif ($searchform == 'yes') {
	echo 'background-image: url(images/text_search_'.$language.'.png);';

} else {
	echo 'background-image: url(images/text_results_'.$language.'.png);';
	$expandbutton = true;
}
echo '
}
</style>
';
/*
if($searchform == 'yes') {
echo '<link rel="STYLESHEET" type="text/css" href="style/directory.css" title="Directory Style Definitions" />';
} else {
echo '<link rel="STYLESHEET" type="text/css" href="style/directory.css" title="Directory Style Definitions" />';
}
*/
echo '</head><body background="images/text_search_'.$language.'.png" '.$xtra.'>';
if(isset($expandbutton)) {
	print '<div id="maxbutton"><a href="javascript:parent.resizeFrame(\'5,*\')">'.
	'<img src="images/up-down.gif" alt="'. _("Minimize / Maximize") .'" '.
	'title="'. _("Minimize / Maximize") .'" border="0" /></a></div>';

}

return;
}

?>
