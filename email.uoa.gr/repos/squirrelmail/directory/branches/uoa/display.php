<?php
/**
 * display.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: display.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Display functions, for multiple and single tables output.
 */

/** 
 * Display HTML results from an LDAP query in multiple tables.
 *
 * @param array $attributes The list of attributes to display
 * @param array $entry The entries array from the ldap_get_entries search result
 * @param string $sortby
*/
function directory_dispresultsMulti ($attributes, $entry, $sortby) {
	global $languages, $data_dir, $username, $compose_new_win, $base_uri,
	$color, $ldq_attributes, $ldq_lang, $charset, $ldq_enablemailalternate;

	if ($entry["count"] == 0) {
		print _("No records found") . "<BR>\n";
		return;
	}
	
	/**
	 * sort the entries. First, build an array with the sortby as the key and
	 * the entry index as the value
	 */
	for ($i=0 ; $i < $entry["count"]; $i++) {
		$Val = $entry[$i][$sortby][0] . $i;
		$sorted[$Val] = $i;
	}

	if ($sortby == "cn")
		uksort ($sorted, "directory_compcns");
	else
		uksort ($sorted, "directory_compattrs");
		
	foreach ($sorted as $key=>$i) {
		print '<table border="0">';
		foreach ($attributes as $attr) {
			for ($x=0 ; $x < $entry[$i][$attr]["count"] ; $x++) {
				// print one row of table of attr/value pairs
				// print attribute cell
				print '<tr><td bgcolor="'.$color[12].'">'.$ldq_attributes[$attr]['text'].'</td><td bgcolor="'.$color[4].'">';
   
				switch ($attr) {
					case "labeledurl":
						/* split up url and label parts */
						print directory_print_labeledurl($entry[$i][$attr][$x]);
						break;
					case "mail":
						$val = $entry[$i][$attr][$x];
						print directory_href('mail', $val);
						if($ldq_enablemailalternate == true) {
							if(isset ($entry[$i]['mailalternateaddress']['count']) &&
							  ($entry[$i]['mailalternateaddress']['count'] > 0 )) {
								for($k=0; $k<$entry[$i]['mailalternateaddress']['count']; $k++) {
									print "<br /><small>";
									$val2 = $entry[$i]['mailalternateaddress'][$k];
									print directory_href('mail', $val2);
									print '</small>';
								}
							}
						} 
						break;
					default:
						$attr_lang = $attr.';lang-'.$ldq_lang;
						if(array_key_exists($attr_lang, $entry[$i]) &&
						  !is_null($entry[$i][$attr_lang][$x]) &&
						  ($entry[$i][$attr_lang][$x] != " ") ) {
							$val = directory_string_convert($entry[$i][$attr_lang][$x], "UTF-8", $charset);
						} else {
							$val = $entry[$i][$attr][$x];
						}
						print "$val<br />";
						print "</td></tr>";
				}
			}
		}
		print "</table>\n<br />";
	}
}

/**
 * Display HTML results from an LDAP query with each record being one row in
 * a single table.
 *
 * @param array $attributes The list of attributes to display
 * @param array $entry The entries array from the ldap_get_entries search result
 * @param string $sortby
 * @param array $secondary
*/
function directory_dispresultsSingle ($attributes, $entry, $sortby, $secondary = '') {
   
	global $languages, $data_dir, $username, $compose_new_win, $plugins,
		$base_uri, $color, $ldq_attributes, $ldq_lang, $charset,
		$ldq_enablemailalternate, $location, $follow, $orgs,
		$formname, $inputname, $popup;
   
	if ($entry['count'] == 0) {
		print _("No records found") . "<BR>\n";
		return;
	}

	/** sort the entries. First, build an array with the sortby as the key
	* and the entry index as the value. */
	
	for ($i=0; $i<$entry['count']; $i++) {
		$Val = $entry[$i][$sortby][0] . $i;
		$sorted[$Val] = $i;
	}

	if ($sortby == "cn")
		uksort ($sorted, "directory_compcns");
	else
		uksort ($sorted, "directory_compattrs");

	/** Table Header */
	print '<table border="0" cellspacing="2" cellpadding="2">';
	print '<th><tr bgcolor="'.$color[3].'">';
	if(isset($popup)) {
		print '<td></td>';
	}
	foreach ($attributes as $attr) {
		// $Var = "directory_showattr_" . $attr;
		// global $$Var;
		// if ($$Var == "on")
		print '<td>'.$ldq_attributes[$attr]['text'].'</td>';
	}
	/* Header for AddACL stuff (useracl plugin) */
		if(in_array('useracl', $plugins) && !isset($popup)) {
		print '<td><small>'. _("Add Share") .'</small></td>';
	}
	print '</tr></th>';

	$toggle = false;

	/** Table contents */
	foreach ($sorted as $key=>$i) {
		print '<tr';
		if ($toggle) {
			print ' bgcolor="'.$color[12].'"';
		} else {
			print ' bgcolor="'.$color[4].'"';
		}
		print '>';


		if(isset($popup)) {
		if(isset($entry[$i]['mail'][0])) {
			$email = $entry[$i]['mail'][0];
			$userid = $entry[$i]['uid'][0];


			if(isset($formname) && isset($inputname)) {
				/* Parent form was something other than
				 * compose, e.g. useracl */
        			print html_tag( 'td', '<small>'.
				  '<a href="javascript:add_and_close('."'".$userid."');\">"._("Add")."</a>");

			} else {
				/* Parent form was compose.php */
        		print html_tag( 'td', '<small>'.
			  '<a href="javascript:to_address('."'".$email."');\">To</A> | " .
			  '<a href="javascript:cc_address('."'".$email."');\">Cc</A> | " .
			  '<a href="javascript:bcc_address('."'".$email."');\">Bcc</A></small>",
			  'center', '', ' width="5%" nowrap' );
			}

			} else {
				print '<td></td>';
			}

		}

		foreach ($attributes as $attr) {
			// $Var = "directory_showattr_" . $attr;
			// global $$Var;
			// if ($$Var == "on") {
			print '<td>';

			if (!array_key_exists($attr, $entry[$i]) ||
			  ( array_key_exists($attr, $entry[$i]) && ($entry[$i][$attr]["count"] == 0))  ) {
				print '<br/>';
			}

			if (array_key_exists($attr, $entry[$i])) {
				for ($x=0 ; $x < $entry[$i][$attr]["count"] ; $x++) {
					switch ($attr) {
						case "labeledurl":
							/* split up url and label parts */
							print directory_print_labeledurl($entry[$i][$attr][$x]);
							break;
						case "mail":
							$val = $entry[$i][$attr][$x];
							print directory_href('mail', $val);
							if($ldq_enablemailalternate == true) {
								if(isset ($entry[$i]['mailalternateaddress']['count']) &&
								  ($entry[$i]['mailalternateaddress']['count'] > 0 )) {
									for($k=0; $k<$entry[$i]['mailalternateaddress']['count']; $k++) {
										print "<br /><small>";
										$val2 = $entry[$i]['mailalternateaddress'][$k];
										print directory_href('mail', $val2);
										print '</small>';
									}
								}
							} 
							break;

						default:
							/* Use language attribute value, if it is available */
							$attr_lang = $attr.';lang-'.$ldq_lang;
							if(array_key_exists($attr_lang, $entry[$i]) &&
							  !empty($entry[$i][$attr_lang][$x]) &&
							  ($entry[$i][$attr_lang][$x] != " ") ) {

								$val = directory_string_convert($entry[$i][$attr_lang][$x], "UTF-8", $charset);
							} else {
								$val = $entry[$i][$attr][$x];
							}

							if(isset($ldq_attributes[$attr]['map'])) {
								$val = $ldq_attributes[$attr]['map'][trim($val)];
							}

							if($attr == 'edupersonaffiliation') {
								if(isset($entry[$i]['edupersonprimaryaffiliation'][0]) &&
								   $entry[$i]['edupersonprimaryaffiliation'][0] == $entry[$i][$attr][$x]) {
									$important = true;
								}
							}
							
							if($attr == 'edupersonorgunitdn') {
								if(isset($entry[$i]['edupersonprimaryorgunitdn'][0]) &&
								   $entry[$i]['edupersonprimaryorgunitdn'][0] == $entry[$i][$attr][$x]) {
									$important = true;
								}
							}

							if(isset($ldq_attributes[$attr]['followme'])) {
								// print "running directory_href($attr, $val, ".$follow[$val] . ")";
								if(isset($follow[$val])) {
									$val = directory_href($attr, $val, $follow[$val]);
								} elseif(isset($orgs[$val])) {
									$val = directory_href($attr, $val,
									  '<small>' . $orgs[$val]['struct'] . '</small> ' . $orgs[$val]['text']);
								} else {
									$val = '';
								}
									
							} else {
								if(isset($ldq_attributes[$attr]['url'])) {
									$val = directory_href($attr, $val);
								}
							}
							if(isset($important)) {
								$val = '<strong>'.$val.'</strong>';
								unset($important);
							}
							print $val . "<br />";
					}

				}
			}
			print '</td>';
			// }
		}

		/* Shares */
		if(in_array('useracl', $plugins) && !isset($popup)) {
			echo '<td align="center" nowrap="">';
			$acl_uri = 'plugins/useracl/addacl.php?user='.$entry[$i]['uid'][0].'&amp;addacl=1';
			$disp = '<img src="'.$location.'/../useracl/images/public-folder-mini.gif" border="0" ' .
				'alt ="' . _("Add Share") .'" title="'. _("Add Share") .'" />';
			print makeComposeLink($acl_uri, $disp);
			print '</td>';
		}
	
		print "</tr>\n";
	
		if(!$toggle) {
			$toggle = true;
		} elseif($toggle) {
			$toggle = false;
		}
	}
	print "</table>\n";
}


?>
