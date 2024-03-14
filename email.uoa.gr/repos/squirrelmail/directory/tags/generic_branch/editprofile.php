<?php
/**
 * editprofile.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: editprofile.php,v 1.22 2005/04/20 08:59:22 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Edit User's Profile in Directory Server. This form allows to edit certain
 * attributes that can be defined by the user.
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

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "javascript.php");
include_once (DIR_PATH . "functions.php");
include_once (DIR_PATH . "constants.php");
include_once (DIR_PATH . "display.php");

if(isset($ldq_custom) && !(empty($ldq_custom)) &&
  file_exists(DIR_PATH . "custom/$ldq_custom.php")) {
	include_once (DIR_PATH . "custom/$ldq_custom.php");
}

/**
 * Variable import
 */
$compose_new_win = getPref($data_dir, $username, 'compose_new_win');

if(!$ldq_standalone) {
	$location = get_location();
}

directory_LoadPrefs();

sqgetGlobalVar('printform', $printform, SQ_GET);
sqgetGlobalVar('showvertical', $showvertical, SQ_GET);

if($ldq_standalone) {
	displayPageHeader($color, _("Your Profile") . ' - ' . _("Directory Service"));
} else {
	$prev = bindtextdomain ('squirrelmail', SM_PATH . 'locale');
	textdomain ('squirrelmail');
	displayPageHeader($color, "None");
}

$prev = bindtextdomain ('directory_editprofile', DIR_PATH . 'locale');
textdomain ('directory_editprofile');

include_once (DIR_PATH . "editprofile_functions.php");
include_once (DIR_PATH . "schemas/descriptions.php");

$ldq_lang = substr($lang_iso, 0, 2);
$charset = $languages[$lang_iso]['CHARSET'];


$showprofile = true;
if(!isset($showvertical)) {
	$showvertical = false;
}
if(!isset($printform)) {
	$printform = false;
} else {
	if($printform) {
		$showprofile = false;
	}
}


/**
 * ------------------ User validation ------------------
 */

if(isset($_POST['loginsubmit'])) {

	$login_username = $_POST['login_username'];
	$login_password = $_POST['login_password'];

	$ldq_lds = 0;
	$ldq_Server = $ldap_server[$ldq_lds]['host'];
	$ldq_Port = $ldap_server[$ldq_lds]['port'];
	$ldq_base = $ldap_server[$ldq_lds]['base'];
	$ldq_maxres = $ldap_server[$ldq_lds]['maxrows'];
	$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];
	
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
	
	$ldq_filter = '(uid='.$login_username.')';

	$ldq_tattr = array('uid', 'userpassword');

	/** Perform search! */
	 if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,
	   $ldq_tattr, 0, $ldq_maxres, $ldq_timeout))) {
	 	echo '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
        }
        $entry = ldap_get_entries ($ldq_ldap, $ldq_result);
	sanitize_entry_array($entry);
	ldap_close($ldq_ldap);

	if($entry['count'] != 1 ) {
		$logged_in = false;
	} else {
		$dn = $entry[0]['dn'];
		$ldap_password=substr($entry[0]['userpassword'][0], 7);
	
		if($ldap_password == crypt($login_password,$ldap_password)) {
			$logged_in = true;
			$_SESSION['logged_in']  = true;
			$username = $login_username;
			$_SESSION['username'] = $login_username;
		} else {
			$logged_in = false;
		}
	}
}

/**
 * ---------- Login Form ---------
 */
if(!$logged_in) {
	echo '<h1>' . _("Your Profile") . ' - ' . _("Directory Service") . '</h1>';
	
	directory_print_all_sections_start();

	if(isset($_POST['loginsubmit'])) {
		directory_print_section_start( _("Error Encountered") );
		echo '<TR><TD BGCOLOR="'.$color[2].'" ALIGN="CENTER">'.
			'<p><font color="'.$color[8].'"><strong>'.
			_("Login Failed: Unknown User or Password Incorrect.").
			'</strong></font></TD></TR>';
		directory_print_section_end();
	}


	directory_print_section_start(_("Login"));
	echo '<p align="center">';
	if(isset($_GET['loggedout'])) {
		echo _("Successfully logged out. You can login again below:");
	} else {
		echo _("To update your profile, you must first login:");
	}
	echo '
	</p>
	<form name="loginform" action="editprofile.php" method="post">
	<table align="center" width="50%" cellspacing="3" cellpadding="4">
	<tr bgcolor="'.$color[12].'"><td align="right">'.
	_("Username") .
	'</td><td align="left">
	<input name="login_username" size="10" />
	</tr><tr bgcolor="'.$color[12].'"><td align="right">'.
	_("Password") .
	'</td><td align="left">
	<input name="login_password" type="password" size="10" />
	</td></tr>
	<tr bgcolor="'.$color[4].'"><td colspan="2" align="center">
	<input type="submit" name="loginsubmit" value="'. _("Login") . '" />
	</td></tr>
	</table>
	</form>';
	directory_print_section_end();

	if(isset($editprofile_url) && !empty($editprofile_url)) {
		directory_print_section_start(_("Attributes that need to be approved first"));
		echo '<p>' .
		sprintf( _("If you would like to change other important attributes of your profile, you need to <a target=\"_blank\" href=\"%s\">make an application through the User Services</a>."), $editprofile_url) .
		'</p>';
		directory_print_section_end();
		directory_print_all_sections_end();
	}
	echo '</body></html>';
	exit;
}

/* ---------- Validation and Catch common errors here ------------- */

if (isset($_POST['submitchanges'])) {
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

	/* Validation of attributes with 'posvals' */

	foreach($newinfo as $attr => $ni) {
		foreach($editprofile_langs as $l) {
			if($l != 'en') {
				$a = $attr.';lang-'.$l;
			} else {
				$a = $attr;
			}
			if(isset($ni[$a]) && isset($ldq_attributes[$attr]['posvals'])) {
				for($i=0; $i<sizeof($ni[$a]); $i++) {
					if(!in_array($ni[$a][$i], $ldq_attributes[$attr]['posvals'])) {
						unset($ni[$a][$i]);
						$changed_array = true;
					}
				}
			}
			if(isset($changed_array)) {
				$ni[$a] = array_values($ni[$a]);
				unset($changed_array);
			}
		}
	}
}   


	
/* ---------- Gather together attributes that are to be displayed. --------- */
$attributes = array();

if(!isset($directory_output_type)) {
	/* Put defaults */
	$directory_output_type = $directory_default_output_type;
	$attributes = $ldq_default_attrs;
}

if($public_mode == true && isset($ldq_enable_attrs_public) ) {
	$ldq_enable_attrs = $ldq_enable_attrs_public;
}

foreach($ldq_enable_attrs as $attr) {
	$Var = "directory_showattr_" . $attr;
	if(isset($$Var) && $$Var == '1') {
		$attributes[] = $attr;
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

$ldq_lds = 0;
$ldq_Server = $ldap_server[$ldq_lds]['host'];
$ldq_Port = $ldap_server[$ldq_lds]['port'];
$ldq_base = $ldap_server[$ldq_lds]['base'];
$ldq_maxres = $ldap_server[$ldq_lds]['maxrows'];
$ldq_timeout = $ldap_server[$ldq_lds]['timeout'];

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

	$ask_attrs = array_merge($ldq_editable_attrs, $ldq_enable_attrs, $ldq_searchattrs);
	$ask_attrs = array_unique($ask_attrs);

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

	$ldq_tattr[] = 'uid';

	if(!empty($ldq_privacy_attribute)) {
		$ldq_tattr[] = $ldq_privacy_attribute;
	}
	if(!empty($ldq_privacy_attribute_internal)) {
		$ldq_tattr[] = $ldq_privacy_attribute_internal;
	}
	
	$ldq_tattr = array_unique($ldq_tattr);

	$ldq_searchfor = 'people';

	if(isset($ldq_searchobjs[$ldq_searchfor]['rdn'])) {
		$ldq_base = $ldq_searchobjs[$ldq_searchfor]['rdn'] . ',' . $ldq_base;
	}
	
	
	$ldq_filter = '(uid='.$username.')';

	/** Perform search! */
	 if (!($ldq_result = ldap_search($ldq_ldap, $ldq_base, $ldq_filter,
	   $ldq_tattr, 0, $ldq_maxres, $ldq_timeout))) {
	 	echo '<p align="center"><strong>' . _("No entries found.") . '</strong></p>';
        }
        $entry = ldap_get_entries ($ldq_ldap, $ldq_result);
	sanitize_entry_array($entry);

	$dn = $entry[0]['dn'];


/** ----------  Perform Changes, if any.  ----------  */

if ( isset($_POST['submitchanges']) && !isset($error)) {

	$ask_attrs = array_merge($ldq_editable_attrs, $ldq_editable_attrs);
	$ask_attrs = array_unique($ask_attrs);

	foreach($ldq_editable_attrs as $attr) {
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
				echo "<B>Deleted Attribute: ".$a."</B><BR>";
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

		if(isset($info) && !(ldap_modify($ldq_ldap, $dn, $info))) {
			$update_error = true;
			$error = _("Could not update your entry on the Directory Server.");
		}
		
		if(isset($info_del) && !(ldap_mod_del($ldq_ldap, $dn, $info_del))) {
			$update_error = true;
			$error = _("Could not update your entry on the Directory Server.");
		}
			
		if(!isset($update_error)) {
			directory_print_all_sections_start();
			directory_print_section_start(_("Your Profile"));
			echo '<div style="text-align:center;">'.
				_("Your details have been successfully modified on the Directory Server.").
				'<p><a href="editprofile.php">'. _("Return to Directory Profile Edit Page") . '</a></p>'.
				'</div>';
			directory_print_section_end();
			directory_print_all_sections_end();
			echo '</body></html>';
			exit;
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



/* -------------- print edit form  ----------------- */

if($showprofile == true) {

	directory_print_section_start(_("Your Profile"));
	
	echo '<p>'. _("This page shows how your profile looks to people who browse the Directory Service, and allows you to edit or hide certain attributes from Internet or Campus users.") . '</p>' ;
	
	echo '<div style="text-align:center"><strong>
	<a href="editprofile.php?printform=1">' . _("Proceed to Edit Profile") . '</a>
	</strong>
	</div>
	';
	
	if($ldq_standalone) {
		echo '<p>' . _("If you have finished editing, please <a href=\"signout.php\">logout</a> for security reasons.") . '</p>';
	}
	
	$attributes = array_merge($ldq_enable_attrs, $ldq_searchattrs);
	$attributes = array_unique($attributes);
	
	echo '<p>' . _("Currently your entry in the Directory looks like this:") . ' ';
	
	if($showvertical) {
		echo '<a href="editprofile.php">' . _("(Show in vertical tables)");
	} else {
		echo '<a href="editprofile.php?showvertical=1">' . _("(Show in horizontal tables, to compare easily)");
	}
	echo '</a></p>';
	
	echo '<h2>' . _("Public - Internet Users") . '</h2>';
	
	$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
	textdomain ('directory');
	
	$ldq_privacy_attribute = 'uoaprivate';
	if($showvertical) {
		directory_dispresultsSingle($attributes, $entry, 'cn');
	} else {
		directory_dispresultsMulti($attributes, $entry, 'cn');
	}
	
	$prev = bindtextdomain ('directory_editprofile', DIR_PATH . 'locale');
	textdomain ('directory_editprofile');
	
	echo '<h2>' . _("University Members") . '</h2>';
	
	$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
	textdomain ('directory');
	
	$ldq_privacy_attribute = 'uoaprivateinternal';
	if($showvertical) {
		directory_dispresultsSingle($attributes, $entry, 'cn');
	} else {
		directory_dispresultsMulti($attributes, $entry, 'cn');
	}
	
	$prev = bindtextdomain ('directory_editprofile', DIR_PATH . 'locale');
	textdomain ('directory_editprofile');

	echo '<div style="text-align:center"><strong>
	<a href="editprofile.php?printform=1">' . _("Proceed to Edit Profile") . '</a>
	</strong>
	</div>
	';
	
	directory_print_section_end();

} /* Show profile */


if($printform == true) {

	echo '<form action="'.$PHP_SELF.'" method="post">';
	
	/**
 	* Freely editable attributes -- will be committed to LDAP at once.
 	*/
	directory_print_section_start(_("Freely Editable Attributes"));
	echo '<p>' . _("The following attributes can be freely changed to reflect your preferences.") . '</p>';
	echo '<table cellspacing="1" cellpadding="3" border="0" width="90%" align="center">';
	directory_print_editable_attributes($ldq_editable_attrs, $entry);
	echo '</table>';
	directory_print_section_end();
	
	
	/**
 	* Submit
 	*/
	echo ' <tr><td align="center" colspan="3">' . 
		' <input type="submit" name="submitchanges" style="font-weight:bold" value="'. _("Submit Changes") . '" />' .
		' <input type="reset" name="reset" value="'. _("Reset") . '" />'.
		'</form><br/>&nbsp;<br/>';
	
	
	/**
 	* Link to some other application form, if it exists.
 	*/
	if(isset($editprofile_url) && !empty($editprofile_url)) {
		directory_print_section_start(_("Attributes that need to be approved first"));
		echo '<p>' .
			sprintf( _("If you would like to change other important attributes of your profile, you need to <a target=\"_blank\" href=\"%s\">make an application through the User Services</a>."), $editprofile_url) .
			'</p>';
		directory_print_section_end();
	}
	
} /* echo form end */
	
directory_print_all_sections_end();
echo '</td></tr></table>';
echo '</body></html>';


/**
 * Function definitons follow.
 */

?>
