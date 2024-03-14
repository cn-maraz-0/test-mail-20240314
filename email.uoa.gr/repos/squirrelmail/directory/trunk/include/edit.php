<?php
/**
 * editprofile_functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Functions related to editing attributes to LDAP.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: edit.php,v 1.4 2006/08/22 15:41:17 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

include_once(DIR_PATH . 'include/misc.php');

/**
 * Determine if editing is allowed.
 * You should probably edit this for your environment.
 * Default is to allow editing only from localhost.
 *
 * @return int
 */
function directory_access_level() {
	global $ldq_trusted_networks;

	if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
		return 2;
	}
	foreach($ldq_trusted_networks as $allowed) {
		if(IP_Match($allowed['network'], $allowed['mask'], $_SERVER['REMOTE_ADDR'])) {
			return 2;
		}
	}
	return 0;
}


/**
 * Print a table with attributes, in the manner of HTML form elements, and
 * their current values filled in.
 *
 * @var array $attrs Attributes to print.
 * @var array $entry Current LDAP entry array
 * @var boolean $printdescriptions
 * @var string $mode
 * @return void
 */
function directory_print_editable_attributes($attrs, $entry, $printdescriptions = true, $mode = 'edit') {
	global $color, $editprofile_langs, $ldq_attributes, $orgs3, $charset, $ldq_base;

	$toggle = false;

    if($mode == 'add') {
        echo '<tr>'.
        '<td align="right" width="40%"><strong>' . _("DN (Distinguished Name)") . ':</strong></td>'.
        '<td align="left" width="60%">'.
        '<input type="text" size="30" name="newdn" value=",'.$ldq_base.'" />'.
        '</td></tr>';
    }

	foreach($attrs as $attr) {
		echo '<tr';
		if ($toggle) {
			echo ' bgcolor="'.$color[12].'"';
		} else {
			echo ' bgcolor="'.$color[4].'"';
		}
		echo '>';


		echo '<td align="right" width="40%"><strong>' . $ldq_attributes[$attr]['text'] . ':</strong></td>'.
		'<td align="left" width="60%">';
			
		if(isset($ldq_attributes[$attr]['input']) && $ldq_attributes[$attr]['input'] == 'eduorgselect') {
			/* Select box for eduorg dn */
			if(isset($entry[0][$attr]['count']) && $entry[0][$attr]['count'] > 0 ) {
				global $restrict;
				for($i=0; $i < $entry[0][$attr]['count']; $i++) {
					list($urlhost, $urlpath) = directory_parse_eduorg_superior_url($entry[0][$attr][$i]);
                    $restrict[] = $urlpath;
                    unset($urlpath);
				}
			}
			echo '<select size="10" name="myprofile['.$attr.']">';
			directory_print_orgs3($orgs3, 0);
			echo '</select>';
			if(isset($entry[0][$attr])) {
				unset($restrict);
			}

		} elseif(isset($ldq_attributes[$attr]['posvals']) && $ldq_attributes[$attr]['input'] == 'checkboxes') {
			/* Controlled values - multiple choices available, many checkboxes */
			foreach($ldq_attributes[$attr]['posvals'] as $p) {
				echo '<input type="checkbox" name="myprofile['.$attr.'][]" value="'.$p.'" id="'.$attr.'_'.$p.'" ';
				if(isset($entry[0][$attr]) && in_array($p, $entry[0][$attr])) {
					echo ' checked=""';
				}
				echo '/>'.
				'<label for="'.$attr.'_'.$p.'">'.$ldq_attributes[$p]['text'].'</label><br/>';
			}

	
		} elseif(isset($ldq_attributes[$attr]['map'])) {
			/* Controlled values - single-value, select box */
			echo '<select size="0" name="'.$attr.'[]">';
			foreach($ldq_attributes[$attr]['map'] as $a=>$t) {
				echo '<option name="'.$a.'"';

				if (isset($entry[0][$attr][0]) && directory_string_convert($entry[0][$attr][0], $charset, 'UTF-8') == $a) {
					echo ' selected=""';
				}
				echo '>'.$t.'</option>';
				
			}
			echo '</select>';
		
		} elseif(isset($ldq_attributes[$attr]['url'])) {
			switch ($ldq_attributes[$attr]['url']) {
			case 'eduorg':
				echo '<select size="10" name="myprofile['.$attr.']">';
				directory_print_orgs3($orgs3, 0);
				echo '</select>';
	
				break;
			case 'labeled':
				if(isset($entry[0][$attr])) {
					for($k=0; $k < $entry[0][$attr]['count']; $k++) {
						$parts = explode(' ', $entry[0][$attr][$k], 2);
						if(!isset($parts[1])) {
							$parts[1] = '';
						}
						$labeleduris[$k]['url'] = $parts[0];
						$labeleduris[$k]['desc'] = directory_string_convert($parts[1], 'UTF-8', $charset);
					}
				}
				$k=0;
				if(isset($labeleduris)) {
					for($k=0; $k<sizeof($labeleduris); $k++) {
						echo _("URL:") . ' <input type="text" size="10" name="myprofile['.$attr.']['.$k.'][url]" '.
							'value="'.$labeleduris[$k]['url'].'" />'.
						_("Description:") . ' <input type="text" size="15" name="myprofile['.$attr.']['.$k.'][desc]" '.
							'value="'.$labeleduris[$k]['desc'].'" /><br/>';
						
					}
				}
				echo _("URL:") . ' <input type="text" size="10" name="myprofile['.$attr.']['.$k.'][url]" />'.
					_("Description:") . ' <input type="text" size="15" name="myprofile['.$attr.']['.$k.'][desc]" />';
				break;
			case 'callto':
			case 'mailto':
			default:
				echo '<input type="text" size="25" name="myprofile['.$attr.']" value="';
				if(isset($entry[0][$attr])) {
					for($j=0; $j<$entry[0][$attr]['count']; $j++) {
						echo htmlspecialchars($entry[0][$attr][$j]);
					}
				}
				echo '" />';
			}
	
		} else {
			if(empty($editprofile_langs)) {
				$editprofile_langs = array('en');
			}
			foreach($editprofile_langs as $l) {
				if($l != 'en') {
					$a = $attr.';lang-'.$l;
				} else {
					$a = $attr;
				}
				echo '<img src="images/flags/'.$l.'flag.png" alt="['.$l.']" /> ';
		
				if(isset($ldq_attributes[$attr]['posvals']) && $ldq_attributes[$attr]['input'] == 'select') {
					/* Controlled values - single select box */
					echo '<select size="0" name="myprofile['.$a.']">';
					foreach($ldq_attributes[$attr]['posvals'] as $p) {
						echo '<option value="'.$p.'"';
						if(isset($entry[0][$a]) && in_array(directory_string_convert($p, $charset, 'UTF-8'), $entry[0][$a])) {
							echo ' selected=""';
						}
						echo '>'.$p.'</option>';
					}
					echo '</select>';
                
                }elseif(isset($ldq_attributes[$attr]['input']) && $ldq_attributes[$attr]['input'] == 'textarea') {
                    echo '<textarea rows="8" cols="50" name="myprofile['.$a.']">'.
                        (isset($entry[0][$a][0])? htmlspecialchars(directory_string_convert($entry[0][$a][0], 'UTF-8', $charset)) : '' ).
                        '</textarea>';
	
				
				} else {
					/* Freely editable, Input Box */
					echo '<input type="text" size="30" name="myprofile['.$a.']" value="';
					if(isset($entry[0][$a])) {
						for($j=0; $j<$entry[0][$a]['count']; $j++) {
							echo htmlspecialchars(directory_string_convert($entry[0][$a][$j], 'UTF-8', $charset));
						}
					}
					echo '" />';
				}
				echo '<br/>';
			}
		}
	
		if ($printdescriptions && isset($ldq_attributes[$attr]['inputdesc'])) {
			echo '<br /><small>'.$ldq_attributes[$attr]['inputdesc'] . '</small>';
		}
		echo '</td></tr>';
		if($toggle == false) {
			$toggle = true;
		} else {
			$toggle = false;
		}
	}
}

?>
