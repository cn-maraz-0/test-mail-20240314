<?php
/**
 * editeduorginfo.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Edit User's Profile in Directory Server. This form allows to edit certain
 * attributes that can be defined by the user.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: editeduorginfo.php,v 1.7 2007/07/05 08:37:26 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Define's and include's
 */
include_once ('config.php');

if($ldq_standalone) {
	include_once ('standalone/standalone.php');
} else {
	$public_mode = false;
	$logged_in = true;
	define('SM_PATH', "../../");
	define('DIR_PATH', SM_PATH . "plugins/directory/");
	include_once (SM_PATH . 'include/validate.php');
	include_once (SM_PATH . 'include/load_prefs.php');
	$language = $lang_iso = getPref($data_dir, $username, 'language');
}

$prev = sq_bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "include/html.php");
include_once (DIR_PATH . "include/javascript.php");
include_once (DIR_PATH . "include/functions.php");
include_once (DIR_PATH . "include/constants.php");
include_once (DIR_PATH . "include/display.php");
include_once (DIR_PATH . "include/edit.php");

if($ldq_support_eduperson) {
	include_once(DIR_PATH . 'include/eduorg.inc.php');
	include_once(DIR_PATH . 'include/eduorg_html.inc.php');
} else {
	print "Error: No EduOrg Support enabled in config.php";
	exit;
}

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "include/custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "include/custom/$ldq_custom.php");
}

/**
 * Variable import
 */
sqgetGlobalVar('dn', $ldq_dn, SQ_FORM);
sqgetGlobalVar('newdn', $newdn, SQ_POST);
if(isset($_GET['mode']) && $_GET['mode'] == 'add') {
    $mode = 'add';
} else {
    $mode = 'edit';
}

if(empty($ldq_dn) && $mode == 'edit') {
    die("Error: No Organizational Unit Specified.");
}

$printform = true;

$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

$editlink = directory_access_level();
if($editlink == 0 ) {
	print "Access Denied";
	exit;
}

if(!$ldq_standalone) {
	$location = get_location();
}

directory_LoadPrefs();

if($ldq_standalone) {
	displayPageHeader($color, _("Edit Organizational Unit Information") . ' - ' . _("Directory Service"));
} else {
	sq_bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayPageHeader($color, "None");
}

sq_bindtextdomain ('directory_editprofile', DIR_PATH . 'locale');
textdomain ('directory_editprofile');

include_once (DIR_PATH . "include/edit.php");
include_once (DIR_PATH . "schemas/descriptions.php");

$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];

mb_internal_encoding($charset);

/* ---------------- LDAP setup ---------------- */
$ldq_lds = 0;
$ldq_Server = $ldap_server[$ldq_lds]['host'];
$ldq_Port = $ldap_server[$ldq_lds]['port'];
$ldq_base = $ldap_server[$ldq_lds]['base'];
$ldq_maxres = $ldap_server[$ldq_lds]['maxrows'];
$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];


/* ---------- Validation and Catch common errors here ------------- */

if (isset($_POST['submitchanges'])) {

    if(!empty($newdn)) {
        // TODO: validate newdn based on basedn and/or other rules.
    }

	$newinfo = $_POST['myprofile'];

	/* Convert every single string to UTF-8 */
	foreach($newinfo as $attr => $ni) {
		if(!is_array($ni)) {
			$newinfo[$attr] = directory_string_convert($newinfo[$attr], $charset, "UTF-8");
		}
	}

	/* Validation of labeleduri attributes */
	if(isset($newinfo['labeleduri'])) {
		$unsetcount = 0;
		for($i=0; $i<sizeof($newinfo['labeleduri']); $i++) {
			if(empty($newinfo['labeleduri'][$i]['url'])) {
				$unsetcount++;
			} else {
				if(isset($newinfo['labeleduri'][$i]['desc'])) {
					$tmp = $newinfo['labeleduri'][$i]['url'].' '.
					directory_string_convert($newinfo['labeleduri'][$i]['desc'], $charset, "UTF-8");
				} else {
					$tmp = $newinfo['labeleduri'][$i]['url'];
				}
				$labeleduri_tmp[] = $tmp;
			}
		}
		if($unsetcount > 1 && $unsetcount == sizeof($newinfo['labeleduri'])) {
			$info_del['labeleduri'] = array();
		}
		if(isset($labeleduri_tmp)) {
			if($nonempty == true) {
				// echo "heh? ";
			} else {
				// echo "OK... newinfo['labeleduri'] = ". print_r($labeleduri_tmp);
				$newinfo['labeleduri'] = $labeleduri_tmp;
			}
		}
	}
	
	/* fix of eduorgsuperioruri attributes */
	if(isset($newinfo['eduorgsuperioruri'])) {
		$newinfo['eduorgsuperioruri'] = 'ldap://'.$ldq_Server.'/'.$newinfo['eduorgsuperioruri'];
	}

	/* Validation of attributes with 'posvals' */
	foreach($newinfo as $attr => $ni) {
        // determine the actual attribute name
	    if(strstr($attr, ';lang-')) {
            $a = substr($attr, 0, strpos($attr,';lang-'));
        } else {
            $a = $attr;
        }
        // At the moment, only single-value logic.
        if(!empty($ni)) { 
            // controlled vocabulary check
            if(isset($ldq_attributes[$a]['posvals'])) {
                if(!in_array($ni, $ldq_attributes[$a]['posvals'])) {
                    //unset($newinfo[$attr]);
                    $changed_array = true;
                }
            }
        }
	}
}   

	
/** ----------  Cache OrgUnitDNs ----------  */
if(isset($_SESSION['orgs'])) {
	$orgs = $_SESSION['orgs'];
	$orgs3 = $_SESSION['orgs3'];
} else {
	/* Orgs Not cached into session. */
	global $orgs, $orgs3;
	cache_orgunitdns();
}


/** ----------  Get Object From LDAP ----------  */

if(isset($ldap_server[$ldq_lds]['binddn'])) {
	$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
}
if(isset($ldap_server[$ldq_lds]['bindpw'])) {
	$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];
}

if(isset($ldap_server[$ldq_lds]['writedn'])) {
	$ldq_write_dn = $ldap_server[$ldq_lds]['writedn'];
} else {
	$ldq_bind_dn = $ldap_server[$ldq_lds]['binddn'];
}

if(isset($ldap_server[$ldq_lds]['writepw'])) {
	$ldq_write_pass = $ldap_server[$ldq_lds]['writepw'];
} else {
	$ldq_pass = $ldap_server[$ldq_lds]['bindpw'];
}

if (!($ldq_ldap=ldap_connect($ldq_Server,$ldq_Port))) {
	echo ("Could not connect to LDAP server " . $ldq_Server);
	exit;
}

if(isset($ldq_bind_dn)) {
	if (!ldap_bind($ldq_ldap, $ldq_bind_dn, $ldq_pass)) {
		echo ("Unable to bind to LDAP server<BR>\n");
		exit;
	}
}

    /** --- Gather attributes to ask LDAP --- */

	$ask_attrs = $ldq_eduorg_editable_attrs;

	foreach ($ask_attrs as $attr) {
		if(isset($ldq_attributes[$attr]['disabled']) &&
		  $ldq_attributes[$attr]['disabled'] == true) {
			continue;
		}

		$ldq_tattr[] = $attr;

		/** Additional attributes */
		if (isset($ldq_attributes[$attr]['additional_attrs']) &&
		  is_array($ldq_attributes[$attr]['additional_attrs']) ) {
			foreach($ldq_attributes[$attr]['additional_attrs'] as $additional) {
				$ldq_tattr[] = $additional;
			}	
		}

	}

	$ldq_tattr[] = 'ou';

	$ldq_tattr = array_unique($ldq_tattr);

	// TODO
	/*
	$ldq_searchfor = 'schools';

	if(isset($ldq_searchobjs[$ldq_searchfor]['rdn'])) {
		$ldq_base = $ldq_searchobjs[$ldq_searchfor]['rdn'] . ',' . $ldq_base;
	}
	*/
    
    if($mode == 'edit') {
        $dn = $ldq_dn;
    } elseif($mode == 'add') {
        $ldq_dn = $dn = $newdn;
    }
	
    $entry = array();
    if(!empty($ldq_dn)) {
    	$ldq_filter = directory_build_filter_from_dn($ldq_dn);

	    /** Perform search! */
        $ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,$ldq_tattr, 0, $ldq_maxres, $ldq_timeout);

        if(!$ldq_result) {
            $error = _("Search failed.");
        } else {
            $entry = ldap_get_entries ($ldq_ldap, $ldq_result);
            sanitize_entry_array($entry);

            if($entry['count'] != 1 && $mode == 'edit') {
                $error = '<p align="center"><strong>' . _("No entries or multiple entries found.") . '</strong></p>';

            } elseif($entry['count'] >= 1 && $mode == 'add') {
                $error = sprintf( _("This dn actually already exists, edit here: %s"),
                    '<a href="editeduorginfo.php?dn='.urlencode($newdn).'">Linky</a>');
            } elseif($entry['count'] == 0 && $mode == 'add') {
                $entry = array();
                $expldn = ldap_explode_dn($dn, 1);
            }
        }
    }

/** ----------  Perform Changes, if any.  ----------  */

if ( isset($_POST['submitchanges']) && !isset($error)) {

	foreach($ldq_eduorg_editable_attrs as $attr) {
		foreach($editprofile_langs as $l) {
			if($l != 'en') {
				$a = $attr.';lang-'.$l;
			} else {
				$a = $attr;
			}

			if(isset($newinfo[$a])) {
				if ( (!isset($entry[0][$a]) && !empty($newinfo[$a])) ||
			     	(isset($entry[0][$a])) && 
			     	(!is_array($newinfo[$a]) && $newinfo[$a] != $entry[0][$a][0])
			   	)
				{
					// echo "<B>New / Changed Attribute: $a = ".$newinfo[$a]."</B><BR>";
					$info[$a] = $newinfo[$a];
				}
	
				if(is_array($newinfo[$a])) {
					// echo "<B>Array Attribute: $a = "; print_r($newinfo[$a]); echo "</B><BR>";
					$info[$a] = $newinfo[$a];
	
				}
	
				if( isset($entry[0][$a]) && $entry[0][$a]['count']>0 && empty($newinfo[$a])) {
					// echo "<B>Deleted Attribute: ".$a."</B><BR>";
					$info_del[$a] = array();
				}
			} else {
				if( isset($entry[0][$a]) && $entry[0][$a]['count']>0 ) {
					// echo "<B>Deleted array Attribute: ".$a."</B><BR>";
					$info_del[$a] = array();
				}
			}
		}
	}

	if( (isset($info) && sizeof($info)>0) || (isset($info_del) && sizeof($info_del)>0) ) {
		ldap_bind($ldq_ldap, $ldq_write_dn, $ldq_write_pass);

        /*
		echo "<PRE>";
		echo " DEBUG: ldap_modify($ldq_ldap, $dn, ";
		@print_r($info);
		echo " DEBUG: ldap_mod_del($ldq_ldap, $dn, ";
		@print_r($info_del);
		echo "</PRE>";
         */
        $success = false;
        $update_error = false;

        if($mode == 'edit') {
            if(isset($info)) {
                if(!ldap_modify($ldq_ldap, $dn, $info)) {
                    $update_error = true;
                    $error = _("Could not update your entry on the Directory Server.");
                } else {
                    $success = true;
                }
            }
            
            if(isset($info_del) && !isset($error)) {
                if(!ldap_mod_del($ldq_ldap, $dn, $info_del)) {
                    $update_error = true;
                    $success = false;
                    $error = _("Could not update your entry on the Directory Server.");
                } else {
                    $success = true;
                }
            }
        } elseif($mode == 'add') {
            if(isset($info) && isset($newdn)) {
                // Add these important attributes, that are not applied in editing
                $info['ou'] = $expldn[0];
                $info['objectClass'] = $ldq_eduorg_new_objectclass;

                if(!ldap_add($ldq_ldap, $newdn, $info)) {
                    $update_error = true;
                    $error = _("Could not insert new entry in the Directory Server."). '<br/>'. ldap_err2str(ldap_errno($ldq_ldap));
                } else {
                    $success = true;
                }
            }
        }
			
		if($success && !$update_error) {
			$printform = false;
            // Delete cache so as to re-get the new orgs on next page load
            unset($_SESSION['orgs']);
            unset($_SESSION['orgs3']);
		}
	}
}



ldap_close($ldq_ldap);

/** ====================== Presentation Logic ====================== */

/** ----------  Print Page Header ----------  */

directory_print_all_sections_start();


/** ----------  Print Error message if it exists ----------  */

if(isset($error)) {
	directory_print_section_start( _("Error Encountered") );
	echo '<tr><td bgcolor="'.$color[2].'" align="center"><p><font color="'.$color[8].'"><strong>'.
	$error . '</strong></font></td></tr>';
	directory_print_section_end();
}

/** ----------  Print successful modification / addition message if it exists ----------  */

if(isset($success) && $success) {
    directory_print_section_start(_("Edit Organizational Unit Information"));
    echo '<div style="text-align:center;">'.
        _("Your details have been successfully modified on the Directory Server.").
        '<p><a href="editeduorginfo.php?dn='.urlencode($ldq_dn).'">'. _("Return to Directory Profile Edit Page") . '</a></p>'.
        '</div>';
    directory_print_section_end();
}

/* -------------- print edit form  ----------------- */

if($printform == true) {

	echo '<form action="'.$PHP_SELF.'?mode='.$mode.'" method="post">'.
		' <input type="hidden" name="dn" value="'.htmlspecialchars($ldq_dn).'" />';
	
	directory_print_section_start(_("Edit Organizational Unit Information"));
	echo '<table cellspacing="1" cellpadding="3" border="0" width="90%" align="center">';
    directory_print_editable_attributes($ldq_eduorg_editable_attrs, $entry, false, $mode);
	echo '</table>';
	directory_print_section_end();
	
	echo ' <tr><td align="center" colspan="3">' . 
		' <input type="submit" name="submitchanges" style="font-weight:bold" value="'. _("Submit Changes") . '" />' .
		' <input type="reset" name="reset" value="'. _("Reset") . '" />'.
		'</form><br/>&nbsp;<br/>';

}
	
directory_print_all_sections_end();
echo '</td></tr></table>';
echo '</body></html>';

?>
