<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Copyright (c) 2002-2003 Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * $Id: standalone_html.inc.php,v 1.2 2003/10/07 13:24:52 avel Exp $
 */

/* HTML Functions that are in use when being a standalone PHP app (that is,
 * instead of a Squirrelmail plugin).  Still not used. Just a draft.
 */

function avelsieve_standalone_print_html_header() {

global $startitems;

print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>New Rule Wizard</title>
<style type="text/css">
body {
        background: '.$color[4].';
	color: '.$color[8].';
        font-family: verdana, tahoma, arial, sans-serif;
        }
a:link {
        text-decoration: none;
        color: '.$color[7].';
        border: none 1px;
        margin: 1px;
        }
a:hover {
        background: '.$color[12].';
        text-decoration: none;
        color: '.$color[7].';
        }
a:visited {
        color: '.$color[13].';
        text-decoration: none;
        }
a:active {
        background: transparent;
        }
h1 {
        text-align: center;
        font-family: tahoma, verdana, lucida, helvetica, sans-serif;
        font-size: 2.0em;
	color: '.$color[0].';
        }
h2 {
        font-weight: bold;
        font-family: tahoma, lucida, helvetica, sans-serif;
        }


</style>
</head>
<body>';

}

function avelsieve_standalone_print_table_header() {

global $PHP_SELF, $color, $part;
print '<form action="'.$PHP_SELF.'" method="POST">
<table cellpadding="2" cellspacing="2" border="1" align="center"
valign="middle" bgcolor="'.$color[3].'" width="80%" frame="hsides">
<tr bgcolor="'.$color[5].'">
<td>';

}

function avelsieve_standalone_print_table_footer() {

	print '</td></tr></table>';

}

function avelsieve_standalone_print_html_footer() {

	print '</body></html>';

}

?>
