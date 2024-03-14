<?php
/**
 * functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: functions.php,v 1.2 2007/09/03 10:35:16 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Compares attributes for sorting results
 */
function directory_compattrs ($left, $right) {
   return strcasecmp($left,$right);
}

/**
 * Converts a string with a name in it in firstname first format to a
 * lastname first formatted string
 * @param string $fnf
 * @return string
 */
function directory_lnf ($fnf) {
	$name = explode(" ", $fnf);
	if (count($name) > 1)
		$lnf = $name[count($name)-1];
	else
		$lnf = "";
	for ($i = 0; $i < count($name)-1; $i++)
		$lnf .= $name[$i];
	return $lnf;
}

/**
 * Compares cn attributes for sorting results by lastname.
 *
 * This is a bit of a hack for those of us who don't have surname attributes
 * in our LDAP records (so we can't just select it in "sort by").
 * @param string $left
 * @param string $right
 */
function directory_compcns ($left, $right) {
	// find last names and put it at start of strings
	$left_lnf = directory_lnf ($left);
	$right_lnf = directory_lnf ($right);
	return strcasecmp($left_lnf,$right_lnf);
}

/**
 * Format properly a Labeled URL value.
 *
 * @param string $val a labeledurl as shown in LDAP.
 * @return string HTML href link.
 */
function directory_print_labeledurl($val) {

	$val = $val . " <END>";
	$isfirsttoken = true;
	$out = '';

	for ($token = strtok($val, " "); $token != "<END>"; $token = strtok(" ")) {
		if ($isfirsttoken) {
			$out .= '<a href="'.$token.'" target="_blank">';
		}
		$out .= $token;

		if ($isfirsttoken) {
			$out .= "</a> ";
			$isfirsttoken = false;
		}
	}
	return $out;
}

/**
 * Convert character set of a string.
 * @param string $string String to convert.
 * @param string $from_charset Original charset.
 * @param string $to_charset Destination charset.
 * @return string Converted string.
 */
function directory_string_convert($string, $from_charset, $to_charset) {
    
    if(strcasecmp($from_charset, $to_charset) == 0 ) {
        return $string;
    }

    if(function_exists("mb_convert_encoding")) {
        return mb_convert_encoding($string, $to_charset, $from_charset);

    } elseif(function_exists("recode_string")) {
        return recode_string("$from_charset..$to_charset", $string);
    
    } elseif(function_exists("iconv")) {
        return iconv($from_charset, $to_charset, $string);

    } else {
        return $string;
    }
}

/**
 * Escape characters that are special to LDAP.
 *
 * @param string string
 * @return string
 */
function directory_escape_ldap_string($string) {
	$out = str_replace('\\', '\\5c', $string);
	/* Don't escape asterisk... */
	/* $out = str_replace('*', '\\2a', $out); */
	$out = str_replace('(', '\\28', $out);
	$out = str_replace(')', '\\29', $out);
	return $out;
}

/**
 * Load directory Preferences into the global scope.
 */
function directory_LoadPrefs() {
   global $data_dir, $username, $ldq_enable_attrs;
   global $directory_output_type;
   
   $directory_output_type = getPref($data_dir, $username, "directory_output_type");
   if ($directory_output_type == "")
      $directory_output_type = "OneTable";

   foreach ($ldq_enable_attrs as $attr) {
      $Var = "directory_showattr_" . $attr;
      global $$Var;
      $$Var = getPref($data_dir, $username, $Var);
      if ($$Var == "")
         $$Var = "on";
   }
}

/**
 * Show Option value from a select
 */
function directory_ShowOption($Var, $value, $Desc) {
    $Var = 'directory_' . $Var;
    
    global $$Var;
    
    echo '<option value="' . $value . '"';
    if (isset($$Var) && $$Var == $value)
    {
        echo ' selected=""';
    }
    echo '>' . $Desc . "</option>\n";
}

/**
 * Print checkboxes with available attributes.
 *
 * @param array $attributes Attributes to show checkboxes for.
 * @return void
 */
function directory_ShowCheckboxes($attributes) {

	global $ldq_attributes;

	foreach ($attributes as $attr) {
		$Var = "directory_showattr_" . $attr;
		global $$Var;
	 
		print '<input type="checkbox" id="'.$Var.'" name="'.$Var.'" ';
		if (isset($$Var) && $$Var == '1')
			print 'checked=""';
		print '> <label for="'.$Var.'">'.$ldq_attributes[$attr]['text'].'</label><br />';
	}
}

/**
 * Get a string and return a properly formatted text and/or hyperlink,
 * depending on the type of the attribute.
 *
 * @param string $type Type of attribute.
 * @param string $val URI value.
 * @param string $uri_xtra Optional Extra URL arguments.
 * @param string $text Optional URI description.
 * @param string $title Optional URI verbose description.
 * @param string $xtra Optional Extra HTML elements.
 * @return string Properly formatted HTML that contains the hyperlink and text.
 */
function directory_href($type, $val, $uri_xtra = '', $text = '', $title = '', $xtra = '') {
	global $ldq_attributes, $ldq_standalone, $squirrelmail_session_open;

	$val = str_replace('$', '<br />', $val);

	if(!isset($ldq_attributes[$type]['url'])) {
		return str_replace('\\n', '<br />', $val);
		return $val;
	} else {
		switch ($ldq_attributes[$type]['url']) {
			case 'eduorg':
				$val = strtolower($val);
				if($ldq_standalone) {
					$eduorg_uri = 'showeduorginfo.php?dn='.urlencode($val);
					if(!empty($uri_xtra)) {
						$eduorg_uri .= '&amp;'.$uri_xtra;
					}
					return '<a href="'.$eduorg_uri.'">'.$text.'</a>';
				} else {
					$eduorg_uri = 'plugins/directory/showeduorginfo.php?dn='.urlencode($val);
					if(!empty($uri_xtra)) {
						$eduorg_uri .= '&amp;'.$uri_xtra;
					}
	     		 	return makeComposeLink($eduorg_uri, $text);
				}
				break;
			case 'mailto':
				/* Make it spam-proof (somewhat) */
				$val = str_replace('@', '&#64;', htmlspecialchars($val));

				/* Standalone, AND there is no Squirrelmail
				 * session in the same server. */
				if($ldq_standalone) {
					/* EXPERIMENTAL Session sharing with Squirrelmail */
					// if(!$squirrelmail_session_open) {
						// $uri = 'mailto:'.$val;
						// break;
						return smailto($val);
					// }
				}
				$uri = 'src/compose.php?send_to='.$val;
	     		 	return makeComposeLink($uri, $val);
				break;
			case 'callto':
				/* Is this useful? Probably not! */
				$uri = 'callto://'.urlencode($val);
				break;
			/*
			case 'fax':
				$uri = 'mailto://remote-printer.recipient_name@fax_number.iddd.tpc.int';
				break;
			*/
			case 'labeled':
				return directory_print_labeledurl($val);	
			case 'raw':
			default:
				$uri = $val;
				$xtra .= 'target="_blank"';
				break;
		}
	}

	$out = '<a href="'.$uri.'"';
	if(!empty($title)) {
		$out .= ' title="'.$title.'"';
	}
	if(!empty($xtra)){
		$out .= ' '.$xtra;
	}
	$out .= '>';
	
	if(!empty($text)) {
		$out .= $text;
	} else {
		$out .= $val;
	}

	return $out;
}

/**
 * Build search filter from just a dn (recursive function).
 * @param array $dn
 * @param boolean $single
 * @return string
 * @todo Fix if depth > 1
 */
function directory_build_filter_from_dn($dn, $single = false) {
	global $ldq_ou_filter;

	if(is_array($dn)) {
		$filter = '|';
		for($i=0; $i<sizeof($dn); $i++) {
			$filter .= '(' . directory_build_filter_from_dn($dn[$i], true) . ')';
		}
		
	} else {

		$expldn = ldap_explode_dn($dn, 0);
		$depth = $expldn['count'] - 3;

		/* FIXME */
		/*
		if($depth > 1) {
			$filter = '&';
			for($i=0; $i< ($expldn['count'] - 3); $i++) {
				$filter .= '('.$expldn[$i].')';
			}
		} else {
			$filter = $expldn[0];
		}
		*/
		$filter = $expldn[0];
	
	}

	if($single == true)	{
		return $filter;
	} else {
		return '(&'. $ldq_ou_filter .'(' . $filter . '))';
	}
}

/**
 * Sanitize an entry array (ldap data).
 * This function will strtolower() all case-insensitive attributes.
 *
 * @param array &$entry
 * @return void
 * @author avel
 */
function sanitize_entry_array(&$entry) {
	/* attributes whose values will be lower-cased: */
	$attrs = array('edupersonorgunitdn', 'edupersonprimaryorgunitdn',
	'uoauserapps', 'edupersonorgdn', 'edupersonprimaryaffiliation',
	'edupersonaffiliation', 'eduorgsuperioruri');


	for($i=0; $i<$entry['count']; $i++) {
		$entry[$i]['dn'] = strtolower($entry[$i]['dn']);
		foreach($attrs as $attr) {
			if(isset($entry[$i][$attr]['count']) && $entry[$i][$attr]['count'] > 0 ) {
				for($j=0; $j<$entry[$i][$attr]['count']; $j++) {
					$entry[$i][$attr][$j] = strtolower($entry[$i][$attr][$j]);
				}
			}
		}
	}
}

/**
 * My function to parse URLs - instead of PHP's parse_url().
 *
 * This function is more liberal in that it allows an empty host
 * and returns just the host and path parts of URL.
 *
 * @param string $url
 * @return array array(host, path)
 */
function directory_parse_eduorg_superior_url($url) {
    preg_match('~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?~', $url, $matches);
    return array(
        ( !empty($matches[4]) ? strtolower($matches[4]) : ''),
        ( !empty($matches[5]) ? ltrim(strtolower($matches[5]), '/') : '')
    );
}

