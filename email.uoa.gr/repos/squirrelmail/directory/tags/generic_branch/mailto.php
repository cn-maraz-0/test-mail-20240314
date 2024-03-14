<?php
/**
 * mailto.php version 1.2.0 Gavin Brown (gavin.brown@uk.com)
 * Future releases will be at http://jodrell.net/
 *
 *	//	This program is free software; you can redistribute it and/or modify
 *	//	it under the terms of the GNU General Public License as published by
 *	//	the Free Software Foundation; either version 2 of the License, or
 *	//	(at your option) any later version.
 *	//
 *	//	This program is distributed in the hope that it will be useful,
 *	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	//	GNU General Public License for more details.
 *
 *	//	this  library provides  a way of putting mailto:  links in  HTML
 *	//	pages  without  exposing your e-mail  address to spammers.  When
 *	//	called,  it  prints  some  JavaScript  which  prints  some  more
 *	//	JavaScript  that prints some HTML containing the  mailto:  link.
 *	//	This  means that e-mail  harvesters won't be able to pick it up,
 *	//	unless they were actually looking for code of this kind.
 *	//
 *	//	This uses some ideas from Paul Gregg's website, but I had been
 *	//	working on it before I knew Paul had actually implemented it.
 *	//	Visit www.pgregg.com for more information.
 *	//
 *
 * @package directory
 * @subpackage thirdparty
 * @version $Id: mailto.php,v 1.2 2004/07/20 16:05:20 avel Exp $
 * @see http://jodrell.net/
 * @copyright Gavin Brown (gavin.brown@uk.com)
 */
	/**
	 * URI encode a string of ASCII text
	 */
	function uri_escape($str) {
		$escaped = '';
		for ($i = 0; $i < strlen($str); $i++) {
			// you could use unpack() here as well:
			$escaped .= '%' . dechex(ord(substr($str, $i, 1)));
		}
		return $escaped;
	}

	function mailto() {
		if (func_num_args() == 3) {
			list($email, $string, $class) = func_get_args();
			print smailto($email, $string, $class);
			return true;
		} elseif (func_num_args() == 2) {
			list($email, $string) = func_get_args();
		} elseif (func_num_args() == 1) {
			list($email, $string) = array(func_get_arg(0), '');
		} else {
			return false;
		}
		print smailto($email, $string);
	}

	function smailto() {
		if (func_num_args() == 3) {
			list($email, $string, $class) = func_get_args();
		} elseif (func_num_args() == 2) {
			list($email, $string) = func_get_args();
		} elseif (func_num_args() == 1) {
			list($email, $string) = array(func_get_arg(0), '');
		} else {
			return false;
		}

		// if the string is empty, use the e-mail address:
		if ($string == '') {
			$string = $email;
		}

		// the JavaScript code to print the HTML:
		// (putting newlines into $code seems to mess up the encoding, so don't)
		$class_string = (empty($class) ? '' : "class=\"$class\"");
		$enc_email = uri_escape($email);
		$enc_str   = uri_escape($string);
		$code =	"var addr = '$enc_email';" .
			"var string = '$enc_str';" .
			"document.write('<a $class_string href=\"mailto:' + unescape(addr) + '\">' + unescape(string) + '</a>');";

		// encode the JavaScript code:
		$encoded = uri_escape($code);

		// generate stuff to go inside <noscript></noscript> tags:
		if ($string == $email) {
			$noscript = "(e-mail address hidden)";
		} else {
			$noscript = $string;
		}

		// print the JavaScript which prints the JavaScript which prints the HTML:
		return	"<script language=\"JavaScript\" type=\"text/javascript\">" .
			"eval(unescape('$encoded'));" .
			"</script>" .
			"<noscript>$noscript</noscript>";

		return true;
	}

	/**
	 * this scans a chunk of text and replaces all e-mail addresses with smailto() calls
	 */
	function mailto_text($text) {
		preg_match_all("/([A-Za-z0-9\.\_\-]+\@{1}[A-Za-z0-9\.\_\-]+)/", $text, $addresses);
		foreach ($addresses[0] as $address) {
			$text = str_replace($address, smailto($address), $text);
		}
		return $text;
	}

	/**
	 * this scans a chunk of HTML and replaces all e-mail addresses with smailto() calls
	 */
	function mailto_html($html) {
		preg_match_all("/<a[\s\r\n\t]+href=\"mailto:([A-Za-z0-9\.\_\-]+\@{1}[A-Za-z0-9\.\_\-]+)\"[\s\r\n\t]*>[\s\r\n\t]*(.+?)[\s\r\n\t]*<\/a>/si", $html, $addresses);
		if (count($addresses[0]) > 0) {
			for ($i = 0 ; $i < count($addresses[0]) ; $i++) {
				$addresses[2][$i] = preg_replace('/[\r\n]+/', ' ', $addresses[2][$i]);
				$html = str_replace($addresses[0][$i], smailto($addresses[1][$i], $addresses[2][$i]), $html);
			}
		}
		return $html;
	}

	/**
	 * an output buffering handler, remember, $buffer is read-only:
	 */
	function ob_mailto($buffer) {
		return mailto_html($buffer);
	}
?>
