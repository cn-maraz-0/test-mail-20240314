<?php
/**
 * main.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: main.php,v 1.7 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Main attributes schema.
 * @var array
 */
$ldq_attributes = array (
	'uid' => array(
		'text' => _("UserID")
		),
		
	'cn' => array(
		'text' => _("Name"),
		'important' => true
		),
	
	'sn' => array(
		'text' => _("Surname"),
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

	'mobile' => array(
		'text' => _("Mobile Phone"),
		'url' => 'callto'
		),

	'facsimiletelephonenumber' => array(
		'text' => _("Fax")
		),

	'st' => array(
		'text' => _("State")
		),

	'preferreddeliverymethod' => array(
		'text' => _("Preferred Delivery Method")
		),

	'l' => array(
		'text' => _("Locality (City)")
		),

	'postaladdress' => array(
		'text' => _("Postal Address")
		),

	'postalcode' => array(
		'text' => _("Postal Code")
		),

	'postofficebox' => array(
		'text' => _("Post Office Box")
		),

	'physicaldeliveryofficename' => array(
		'text' => _("Physical Delivery Office Name")
		),

	'homepostaladdress' => array(
		'text' => _("Home Address")
		),

	'labeleduri' => array(
		'text' => _("URL"),
		'url' => 'labeled',
		'multi' => true
		)
);

?>
