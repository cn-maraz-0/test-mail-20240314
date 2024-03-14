<?php
/**
 * main.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: main.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Main attributes schema.
 * @var array
 */
$ldq_attributes = array (
	'cn' => array(
		'text' => _("Name"),
		'important' => true
		),
		
	'department' => array(
		'text' => _("Department")
		),

	'description' => array(
		'text' => _("Description")
		),

	'title' => array(
		'text' => _("Title")
		),

	'mail' => array(
		'text' => _("Email Address"),
		'url' => 'mailto',
		'additional_attrs' => array('mailalternateaddress')
		),

	'telephonenumber' => array(
		'text' => _("Phone"),
		'url' => 'callto'
		),

	'homephone' => array(
		'text' => _("Home Phone"),
		'url' => 'callto'
		),

	'facsimiletelephonenumber' => array(
		'text' => _("Fax")
		),

	'st' => array(
		'text' => _("State")
		),

	'postaladdress' => array(
		'text' => _("Address")
		),

	'homepostaladdress' => array(
		'text' => _("Home Address")
		),

	'labeledurl' => array(
		'text' => _("URL"),
		'url' => 'labeled'
		)
);

?>
