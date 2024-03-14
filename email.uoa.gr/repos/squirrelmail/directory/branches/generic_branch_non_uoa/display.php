<?php
/**
 * display.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: display.php,v 1.18.2.1 2005/04/21 11:37:34 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
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
 * @return void
 */
function directory_dispresultsMulti ($attributes, $entry, $sortby) {
	global $languages, $data_dir, $username, $compose_new_win, $base_uri,
		$color, $ldq_attributes, $ldq_lang, $charset, $orgs, $popup,
		$ldq_enablemailalternate, $ldq_privacy_attribute,
		$ldq_privacy_url, $ldq_skip_empty_attributes;

	if ($entry['count'] == 0) {
		print _("No records found") . "<br/>\n";
		return;
	}
	
	/**
	 * sort the entries. First, build an array with the sortby as the key and
	 * the entry index as the value
	 */
	for ($i=0 ; $i < $entry['count']; $i++) {
		$Val = $entry[$i][$sortby][0] . $i;
		$sorted[$Val] = $i;
	}

	if ($sortby == 'cn') {
		uksort ($sorted, 'directory_compcns');
	} else {
		uksort ($sorted, 'directory_compattrs');
	}
		

	/**
	 * Print the table!
	 */
	foreach ($sorted as $key=>$i) {
	
		/**
		 * LDAP Privacy attribute, if it is set.
		 * Gather all attributes to be private, in array $privateattrs.
		 */
		 if(!empty($ldq_privacy_attribute)) {
			if(isset($entry[$i][$ldq_privacy_attribute])) {
				for($l=0; $l<$entry[$i][$ldq_privacy_attribute]['count']; $l++) {
					$privateattrs[] = strtolower($entry[$i][$ldq_privacy_attribute][$l]);
				}
			} else {
				$privateattrs = array();
			}
		}
		print '<table border="0" align="center" cellspacing="3" cellpadding="3">';
		foreach ($attributes as $attr) {
			if(empty($entry[$i][$attr]) && $ldq_skip_empty_attributes) {
				continue;
			}
			print '<tr><td bgcolor="'.$color[12].'" align="right">'.
				$ldq_attributes[$attr]['text'].'</td><td bgcolor="'.$color[4].'">';

			if(isset($entry[$i][$attr])) {
				directory_print_attribute($attr, $entry[$i], $privateattrs);
			}
			/** END **/
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
 * @return void
 */
function directory_dispresultsSingle ($attributes, &$entry, $sortby, $secondary = '') {
	global $languages, $data_dir, $username, $compose_new_win, $plugins,
		$base_uri, $color, $ldq_attributes, $ldq_lang, $charset,
		$ldq_enablemailalternate, $location, $follow, $orgs,
		$formname, $inputname, $popup,
		$ldq_privacy_attribute, $ldq_privacy_url, $ldq_standalone;
   
	if ($entry['count'] == 0) {
		print _("No records found") . "<BR>\n";
		return;
	}

	/**
	 * sort the entries.
	 * First, build an array with the sortby as the key and the entry index
	 * as the value. */
	
	if($entry['count'] == 1) {
		$sorted[$entry[0][$sortby][0]] = 0;
	} else {
		for ($i=0; $i<$entry['count']; $i++) {
			$Val = $entry[$i][$sortby][0] . $i;
			$sorted[$Val] = $i;
		}
		if ($sortby == 'cn') {
			uksort ($sorted, 'directory_compcns');
		} else {
			uksort ($sorted, 'directory_compattrs');
		}
	}
		


	/**
	 * Table Header
	 */
	print '<table border="0" cellspacing="2" cellpadding="2">';
	print '<th><tr bgcolor="'.$color[3].'">';
	if(isset($popup)) {
		print '<td></td>';
	}
	print '<td></td>';
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

	/**
	 * Table content
	 */
	foreach ($sorted as $key=>$i) {
	
		/**
		 * LDAP Privacy attribute, if it is set.
		 * Gather all attributes to be private, in array $privateattrs.
		 */
		$privateattrs = array();
		 if(!empty($ldq_privacy_attribute)) {
			if(isset($entry[$i][$ldq_privacy_attribute])) {
				for($l=0; $l<$entry[$i][$ldq_privacy_attribute]['count']; $l++) {
					$privateattrs[] = strtolower($entry[$i][$ldq_privacy_attribute][$l]);
				}
			}
		}

		/**
		 * Start Printing
		 */
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

			if(!empty($formname)) {
				if($formname != 'compose' && isset($inputname)) {
				/* Parent form was something other than
				 * compose, e.g. useracl */
        			print html_tag( 'td', '<small>'.
				  '<a href="javascript:add_and_close('."'".$userid."');\">"._("Add")."</a>");

				} else {
					/* Parent form was compose.php */
        			print html_tag( 'td', '<small>'.
			  			'<a href="javascript:to_address('."'".$email."');\">" ._("To") . "</a> | " .
			  			'<a href="javascript:cc_address('."'".$email."');\">" . _("Cc") . "</a> | " .
			  			'<a href="javascript:bcc_address('."'".$email."');\">". _("Bcc") . "</a></small>",
			  			'center', '', ' width="5%" nowrap' );
				}
			}

			} else {
				print '<td></td>';
			}

		}
		
		/* VCard HyperLink */
		print '<td>';
		if(!empty($entry[$i]['uid'][0])) {
			if($ldq_standalone) {
				$vcard_uri = 'vcard.php?uid='.urlencode($entry[$i]['uid'][0]);
				print '<a href="'.$vcard_uri.'&amp;vcard=1"><img src="images/vcard.gif" alt="VCard" border="0" /></a>';
				$txt = '<img src="images/tip.gif" alt="'. _("Show User Profile").'" border="0" />';
				print '<a href="'.$vcard_uri.'">'.$txt.'</a>';

			} else {
				$vcard_link = 'vcard.php?uid='.urlencode($entry[$i]['uid'][0]);
				$vcard_uri = 'plugins/directory/'.$vcard_link;
				print '<a href="'.$vcard_link.'&amp;vcard=1"><img src="images/vcard.gif" alt="VCard" border="0" /></a>';
				$txt = '<img src="images/tip.gif" alt="'. _("Show User Profile").'" border="0" />';
		     		print makeComposeLink($vcard_uri, $txt);
			}
		}
		print '</td>';
		
		/* Values!! */
		foreach ($attributes as $attr) {
			// $Var = "directory_showattr_" . $attr;
			// global $$Var;
			// if ($$Var == "on") {
			print '<td>';
			directory_print_attribute($attr, $entry[$i], $privateattrs);
			print '</td>';
			// }
		}

		/* Shares */
		if(in_array('useracl', $plugins) && !isset($popup)) {
			echo '<td align="center">';
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


/**
 * Print value of an attribute. To be used inside a table cell.
 *
 * @param string $attr
 * @param array $values A single object entry, as returned by ldap_get_entries()
 * (with 'count' key). For instance, $entry = $entries[$i].
 * @param array $privateattrs
 * @return void
 *
 * @see directory_print_href()
 */
function directory_print_attribute($attr, $entry, $privateattrs = array()) {
	global $ldq_privacy_url, $ldq_enablemailalternate, $ldq_attributes,
		$ldq_lang, $charset, $orgs, $popup, $formname, $inputname;

	/* Just print a line break if there is no entry. */
	if (!array_key_exists($attr, $entry) ||
	  ( array_key_exists($attr, $entry) && ($entry[$attr]['count'] == 0))  ) {
		echo '<br/>';
		return;
	}
	
	if(in_array($attr, $privateattrs)) {
		/* Don't show since user wants to keep it private */
		 if(isset($ldq_privacy_url) && !empty($ldq_privacy_url)) {
		 	echo '<a href="'.$ldq_privacy_url.'" target="_blank">';
		}
		echo '<img src="images/private.gif" width="16" height="16" alt="'._("Private").'" border="0" /> '.
		 	_("Private");
		if(isset($ldq_privacy_url) && !empty($ldq_privacy_url)) {
		 	echo '</a>';
		}
	} else {
		
		$uri_xtra = '';
		if(isset($popup) && $popup == 1) {
			$uri_xtra = 'popup=1';
			if(!empty($formname)) {
				$uri_xtra = '&amp;formname='.$formname;
			}
			if(!empty($inputname)) {
				$uri_xtra = '&amp;inputname='.$inputname;
			}
		}

		for ($x=0 ; $x < $entry[$attr]['count'] ; $x++) {
			switch ($attr) {
				case 'labeleduri':
					/* split up url and label parts */
					echo directory_print_labeledurl($entry[$attr][$x]);
					break;
				case 'mail':
					$val = $entry[$attr][$x];
					echo directory_href('mail', $val);
					if($ldq_enablemailalternate == true) {
						if(isset ($entry['mailalternateaddress']['count']) &&
						  ($entry['mailalternateaddress']['count'] > 0 )) {
							for($k=0; $k<$entry['mailalternateaddress']['count']; $k++) {
								echo "<br /><small>";
								$val2 = $entry['mailalternateaddress'][$k];
								echo directory_href('mail', $val2);
								echo '</small>';
							}
						}
					} 
					break;

				default:
					/* Use language attribute value, if it is available */
					$attr_lang = $attr.';lang-'.$ldq_lang;
					if(array_key_exists($attr_lang, $entry) &&
					  !empty($entry[$attr_lang][$x]) &&
					  ($entry[$attr_lang][$x] != " ") ) {

						$val = directory_string_convert($entry[$attr_lang][$x], "UTF-8", $charset);
					} else {
						$val = $entry[$attr][$x];
					}

					if(isset($ldq_attributes[$attr]['map'])) {
						$val = $ldq_attributes[$attr]['map'][trim($val)];
					}

					if($attr == 'edupersonaffiliation') {
						if(isset($entry['edupersonprimaryaffiliation'][0]) &&
						   $entry['edupersonprimaryaffiliation'][0] == $entry[$attr][$x]) {
							$important = true;
						}
					}
					
					if($attr == 'edupersonorgunitdn') {
						if(isset($entry['edupersonprimaryorgunitdn'][0]) &&
						   $entry['edupersonprimaryorgunitdn'][0] == $entry[$attr][$x]) {
							$important = true;
						}
					}

					if(isset($ldq_attributes[$attr]['followme'])) {
						$val = strtolower($val);
						if(isset($follow[$val])) {
							$val = directory_href($attr, $val, $uri_xtra, $follow[$val]);
						} elseif(isset($orgs[$val])) {
							$val = directory_href($attr, $val, $uri_xtra,
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
			echo $val . "<br />";
			}
		}
	}
}
?>
