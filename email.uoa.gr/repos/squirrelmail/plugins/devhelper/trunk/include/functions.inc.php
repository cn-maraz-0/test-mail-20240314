<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * Extra / miscellaneous functions.
 *
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: functions.inc.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
 * @package plugins
 * @subpackage devhelper
 */


/**
 * Display javascript-enabled link for toggling div display.
 * @param string $name
 * @param string $text
 * @param boolean $start
 * @return string
 * @author avel
 */
function devhelper_togglediv_link($name, $text, $start = true, $divgroup=array()) {
	$out = '<a onclick="ToggleShowDiv(\''.$name.'\'); ';
	if(!empty($divgroup)) {
		foreach($divgroup as $div) {
			if($div != $name ) {
				$out .= 'HideTab(\''.$div.'\');';
			}
		}
	}
	$out .= ' return true;">';

	if($start) {
		$out .= '<img src="images/triangle.gif" alt="&gt;" name="'.$name.'_img" border="0" />';
	} else {
		$out .= '<img src="images/opentriangle.gif" alt="&gt;" name="'.$name.'_img" border="0" />';
	}
	$out .= ' '.$text.'</a>';
	return $out;
}

?>
