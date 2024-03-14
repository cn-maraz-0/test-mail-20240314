<?php

/**
* RFC1738 compliant replacement to PHP's rawurldecode - which actually works with unicode (using utf-8 encoding)
* @author Ronen Botzer
* @param $source [STRING]
* @return unicode safe rawurldecoded string [STRING]
* @access public
*/
function utf8RawUrlDecode ($source) {
    $decodedStr = '';
    $pos = 0;
    $len = strlen ($source);

    while ($pos < $len) {
        $charAt = substr ($source, $pos, 1);
        if ($charAt == '%') {
            $pos++;
            $charAt = substr ($source, $pos, 1);
            if ($charAt == 'u') {
                // we got a unicode character
                $pos++;
                $unicodeHexVal = substr ($source, $pos, 4);
                $unicode = hexdec ($unicodeHexVal);
                $entity = "&#". $unicode . ';';
                $decodedStr .= utf8Encode ($entity);
                $pos += 4;
            }
            else {
                // we have an escaped ascii character
                $hexVal = substr ($source, $pos, 2);
                $decodedStr .= chr (hexdec ($hexVal));
                $pos += 2;
            }
        }
        else {
            $decodedStr .= $charAt;
            $pos++;
        }
    }

    return $decodedStr;
}

/** 
* takes a string of Unicode entities and converts it to a utf-8 encoded  
* string.  Each Unicode entity has either the form &[#]nnnnn; n={0..9} (W3C style)
* or %nn with n=[0..F] hex value.  The entity can  
* be displayed by any browser which supports utf-8 encoding.
* ASCII will not be modified. 
*<br />UTF-8 encoding: 
*<br /> bytes    bits  representation 
*<br /> 1        7     0bbbbbbb 
*<br /> 2        11    110bbbbb 10bbbbbb 
*<br /> 3        16    1110bbbb 10bbbbbb 10bbbbbb 
*<br /> 4        21    11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
* 
* @author Ronen Botzer 
* @param string $source string of Unicode entities
* @param boolean $w3cStyle - true: entity starts with '&' and ends with ';' otherwise starts with '%'
* @param boolean $hasHexVal - is the value of the entity given as hex or decimal (defaults w3c=decimal, MS=hex)
* @return string is the utf-8 encoded string
* @access public 
*/ 
function utf8Encode ($source, $w3cStyle=true, $hasHexVal=false) {
    $utf8Str = ''; // holds the resulting utf-8 encoded string
    if ($w3cStyle) {
        if ($hasHexVal) $delimiter = '&';
        else $delimiter = "&#";
    }
    else
        $delimiter = '%';
    $entityArray = explode ($delimiter, $source); 
    $size = count ($entityArray);

    // process each character in the source string of Unicode entities
    for ($i = 0; $i < $size; $i++) { 
        $subStr = $entityArray[$i]; 
        if ($w3cStyle)
            $nonEntity = strstr ($subStr, ';');
        else
            $nonEntity = true;
            
        if ($nonEntity !== false) {
            // find the offset of the Unicode character
            if ($w3cStyle) {
                if ($hasHexVal)
                    $unicode = hexdec (substr ($subStr, 0, (strpos ($subStr, ';') + 1))); 
                else
                    $unicode = intval (substr ($subStr, 0, (strpos ($subStr, ';') + 1))); 
            }
            else {
                // in the case of %nn entities grab the first two chars
                // and mark the remainder as a non entity (pure ASCII)
                // this may not apply for the first element of the array
                if ($i > 0) {
                    $unicode = hexdec (substr ($subStr, 0, 2));
                    $nonEntity = substr ($subStr, 2);
                }
                else if (substr ($source, 0, 1) == '%') {
                    // first element is an entity
                    $unicode = hexdec (substr ($subStr, 0, 2));
                    $nonEntity = substr ($subStr, 2);
                }
                else {
                    // first element is a non entity
                    $utf8Str .= $subStr; 
                    continue;
                }
            }
            
            // determine how many chars are needed to represent this
            // Unicode character by examining in which range the 
            // position value of the Unicode character falls.
            // see figure 3.
            if ($unicode < 128) {
                // We have an ASCII character.  Simply add it
                $utf8Substring = chr ($unicode); 
            } 
            else if ($unicode >= 128 && $unicode < 2048) {
                // This Unicode character will map to a two character
                // multi-byte sequence 
                $binVal = str_pad (decbin ($unicode), 11, "0", STR_PAD_LEFT);
                // chop the binary representation of the position value
                // into two parts which will be used to fill in the xxx
                // bits described in figure 3.
                $binPart1 = substr ($binVal, 0, 5); 
                $binPart2 = substr ($binVal, 5); 
             
                // assemble the multi-byte sequence which represents
                // the Unicode character
                $char1 = chr (192 + bindec ($binPart1)); 
                $char2 = chr (128 + bindec ($binPart2)); 
                $utf8Substring = $char1 . $char2; 
            } 
            else if ($unicode >= 2048 && $unicode < 65536) {
                // This Unicode character will map to a three character
                // multi-byte sequence
                $binVal = str_pad (decbin ($unicode), 16, "0", STR_PAD_LEFT);
                // chop the binary representation of the position value
                // into three parts which will be used to fill in the
                // xxx bits described in figure 3. 
                $binPart1 = substr ($binVal, 0, 4); 
                $binPart2 = substr ($binVal, 4, 6); 
                $binPart3 = substr ($binVal, 10); 
             
                // assemble the multi-byte sequence which represents
                // the Unicode character
                $char1 = chr (224 + bindec ($binPart1)); 
                $char2 = chr (128 + bindec ($binPart2)); 
                $char3 = chr (128 + bindec ($binPart3)); 
                $utf8Substring = $char1 . $char2 . $char3; 
            } 
            else {
                // This Unicode character will map to a four character
                // multi-byte sequence
                $binVal = str_pad (decbin ($unicode), 21, "0", STR_PAD_LEFT);
                // chop the binary representation of the position value
                // into four parts which will be used to fill in the
                // xxx bits described in figure 3.  
                $binPart1 = substr ($binVal, 0, 3); 
                $binPart2 = substr ($binVal, 3, 6); 
                $binPart3 = substr ($binVal, 9, 6); 
                $binPart4 = substr ($binVal, 15); 
         
                // assemble the multi-byte sequence which represents
                // the Unicode character
                $char1 = chr (240 + bindec ($binPart1)); 
                $char2 = chr (128 + bindec ($binPart2)); 
                $char3 = chr (128 + bindec ($binPart3)); 
                $char4 = chr (128 + bindec ($binPart4)); 
                $utf8Substring = $char1 . $char2 . $char3 . $char4; 
            } 
             
            if ($w3cStyle) {
                if (strlen ($nonEntity) > 1) {
                    // chop the first char (';')
                    $nonEntity = substr ($nonEntity, 1);
                }
                else $nonEntity = ''; 
            }
                    

            $utf8Str .= $utf8Substring . $nonEntity; 
        } 
        else { 
            $utf8Str .= $subStr; 
        } 
    } 

    return $utf8Str; 
} 
        
