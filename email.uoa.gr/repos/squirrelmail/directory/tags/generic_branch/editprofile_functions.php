<?php
/**
 * editprofile_functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: editprofile_functions.php,v 1.4 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Print a table with attributes and their current values
 * @var array $attrs Attributes to print.
 * @var array $entry Current LDAP entry array.
 * @return void
 */
function directory_print_editable_attributes($attrs, $entry) {
	global $color, $editprofile_langs, $ldq_attributes, $orgs3, $charset;

	$toggle = false;
	foreach($attrs as $attr) {
		print '<tr';
		if ($toggle) {
			print ' bgcolor="'.$color[12].'"';
		} else {
			print ' bgcolor="'.$color[4].'"';
		}
		print '>';


	print '<td align="right" width="40%"><strong>' . $ldq_attributes[$attr]['text'] . ':</strong></td>'.
	'<td align="left" width="60%">';
		

	/* Print widget */

	if(false && $ldq_attributes[$attr]['lala']) {
		print '<select size="10" multiple="" name="restrict[]">';
		directory_print_orgs3($orgs3, 0);
		print '</select>';

	} elseif(isset($ldq_attributes[$attr]['posvals'])) {
		foreach($ldq_attributes[$attr]['posvals'] as $p) {
			print '<input type="checkbox" name="myprofile['.$attr.'][]" value="'.$p.'" id="'.$attr.'_'.$p.'" ';
			if(isset($entry[0][$attr]) && in_array($p, $entry[0][$attr])) {
				echo ' checked=""';
			}
			print '/>'.
			'<label for="'.$attr.'_'.$p.'">'.$ldq_attributes[$p]['text'].'</label><br/>';
		}

	} elseif(isset($ldq_attributes[$attr]['map'])) {
		print '<select size="0" name="'.$attr.'">';
		foreach($ldq_attributes[$attr]['map'] as $a=>$t) {
			print '<option name="'.$a.'"';
			if (isset($entry[0][$attr][0]) && $entry[0][$attr][0] == $a) {
				print ' selected=""';
			}
			print '>'.$t.'</option>';
		}
		print '</select>';
	
	} elseif(isset($ldq_attributes[$attr]['url'])) {
		switch ($ldq_attributes[$attr]['url']) {
		case 'eduorg':
			print '<select size="10" name="myprofile['.$attr.']">';
			directory_print_orgs3($orgs3, 0);
			print '</select>';

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
					print _("URL:") . ' <input type="text" size="10" name="myprofile['.$attr.']['.$k.'][url]" '.
						'value="'.$labeleduris[$k]['url'].'" />'.
					_("Description:") . ' <input type="text" size="15" name="myprofile['.$attr.']['.$k.'][desc]" '.
						'value="'.$labeleduris[$k]['desc'].'" /><br/>';
					
				}
			}
			print _("URL:") . ' <input type="text" size="10" name="myprofile['.$attr.']['.$k.'][url]" />'.
				_("Description:") . ' <input type="text" size="15" name="myprofile['.$attr.']['.$k.'][desc]" />';
			break;
		case 'callto':
		case 'mailto':
		default:
			print '<input type="text" size="25" name="myprofile['.$attr.']" value="';
			if(isset($entry[0][$attr])) {
				for($j=0; $j<$entry[0][$attr]['count']; $j++) {
					print htmlspecialchars($entry[0][$attr][$j]);
				}
			}
			print '" />';
		}


	/*
	} elseif(isset($ldq_attributes[$attr]['posvals'])) {
		print '<select size="0" multiple="" name="'.$attr.'">';
		foreach($posvals as $p) {
			print '<option name="'.$p.'">'.$ldq_attributes[$p]['text'].'</option>';
		}
		print '</select>';
	*/


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
			print '<img src="images/flags/'.$l.'flag.gif" alt="['.$l.']" /> '.
				'<input type="text" size="25" name="myprofile['.$a.']" value="';
			if(isset($entry[0][$a])) {
				for($j=0; $j<$entry[0][$a]['count']; $j++) {
					print htmlspecialchars(directory_string_convert($entry[0][$a][$j], 'UTF-8', $charset));
				}
			}
			print '" /><br/>';
		}
		/*
		print '<input type="text" name="myprofile['.$attr.']" value="';
		if(isset($entry[0][$attr])) {
			for($j=0; $j<$entry[0][$attr]['count']; $j++) {
				print htmlspecialchars($entry[0][$attr][$j]);
			}
		}
		*/
	}

	if (isset($ldq_attributes[$attr]['inputdesc'])) {
		print '<br /><small>'.$ldq_attributes[$attr]['inputdesc'] . '</small>';
	}
	print '</td></tr>';
	if($toggle == false) {
		$toggle = true;
	} else {
		$toggle = false;
	}
	}
}



?>
