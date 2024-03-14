<?php
/**
 * functions.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: functions.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
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
function ShowOption($Var, $value, $Desc) {
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
 * @param string $text Optional URI description.
 * @param string $title Optional URI verbose description.
 * @param string $xtra Optional Extra HTML elements.
 * @return string Properly formatted HTML that contains the hyperlink and text.
 */
function directory_href($type, $val, $text = '', $title = '', $xtra = '') {
	global $ldq_attributes;

	$val = str_replace('$', '<br />', $val);
	
	if(!isset($ldq_attributes[$type]['url'])) {
		return $val;
	} else {
		switch ($ldq_attributes[$type]['url']) {
			case 'eduorg':
				$eduorg_uri = 'plugins/directory/showeduorginfo.php?dn='.urlencode($val);
     		 		return makeComposeLink($eduorg_uri, $text);
				break;
			case 'mailto':
     		 		return makeComposeLink('src/compose.php?send_to='.urlencode($val), $val);
				// $uri = 'mailto:'.urlencode($val);
				break;
			case 'callto':
				$uri = 'callto://'.urlencode($val);
				break;
			case 'labeled':
				return directory_print_labeledurl($val);	
			case 'raw':
			default:
				$uri = urlencode($val);
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

?>
