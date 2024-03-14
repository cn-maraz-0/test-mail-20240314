<?php

/**
 * Utility to convert SquirrelMail *.abook files to LDIF.
 * Version: 0.1.0
 *
 * Usage: php convert_abook.php >abookss.ldif
 * Modify parameters according to your needs.
 *
 * By Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Copyright (c) 1999-2005 Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 */

    $file_encoding = "iso-8859-2";
    $data_dir = "/var/lib/squirrelmail/data";
    $pattern = "/^([^@]+)@([^@]+)\\.abook$/";
    $dn_template = "ou=addresses, mail=%1@%2, domain=%2, ou=virtualMail, dc=dev-labs, dc=com";

    $objectclass = array("top", "inetOrgPerson");
    $attr[0] = "cn";			// nickname
    $attr[1] = "firstname";		// firstname
    $attr[2] = "sn";			// lastname
    $attr[3] = "mail";			// email
    $attr[4] = "description";		// description

    $container_add = true;
    $container_dn_template = "mail=%1@%2, domain=%2, ou=virtualMail, dc=dev-labs, dc=com";
    $container_objectclass = array("top", "organizationalUnit");
    $container_attr = "ou: addresses";

    function fillTemplate($template, $values)
    {
	for ($i = 1; $i < count($values); $i++) {
	    $template = str_replace("%$i", $values[$i], $template);
	}
	return $template;
    }
    
    function printObjectClass($array)
    {
	for ($i = 0; $i < count($array); $i++) {
	    echo "objectClass: " . $array[$i] . "\n";
	}
    }
    
    function printAttribute($attr, $s)
    {
	$s = trim($s);
	if ($s != "") {
	    $s = iconv($file_encoding, "utf-8", $s);
	    for ($i = 0; $i < strlen($s); $i++) {
		if (ord($s[$i]) > 127) {
		    echo $attr . ":: " . base64_encode($s) . "\n";
		    return;
		}
	    }
	    echo $attr . ": " . $s . "\n";
	}
    }
    
    $h = opendir($data_dir);
    while ($filename = readdir($h)) {
	$filename = strtolower($filename);
	if (preg_match($pattern, $filename, $matches)) {
	    $dn = fillTemplate($dn_template, $matches);
	    $f = fopen($data_dir . "/" . $filename, "r");
	    $has_container = false;
	    while ($s = fgets($f)) {
		if ($container_add && !$has_container) {
		    $has_container = true;
		    echo fillTemplate($container_dn_template, $matches) . "\n";
		    printObjectClass($container_objectclass);
		    echo $container_attr . "\n\n";
		}
		$tmp = explode("|", $s);
		printAttribute("dn", $attr[0] . "=" . $tmp[0] . ", " . $dn);
		printObjectClass($objectclass);
		for ($i = 0; $i < 5; $i++) {
		    printAttribute($attr[$i], $tmp[$i]);
		}
		echo "\n";
	    }
	    fclose($f);
	}
    }
    closedir($h);
?>