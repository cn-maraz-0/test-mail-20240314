<?php
/**
 * ldap.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: ldap.php,v 1.2 2004/08/09 13:07:26 avel Exp $
 *
 * Some functions useful for handling of LDAP stuff.
 */

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

?>
