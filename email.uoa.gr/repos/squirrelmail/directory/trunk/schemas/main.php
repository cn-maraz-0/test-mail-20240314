<?php
/**
 * main.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: main.php,v 1.10 2007/06/25 07:26:45 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Main attributes schema.
 * @var array
 */
$ldq_attributes = array (
	'uid' => array(
		'text' => _("UserID"),
        'image' => 'textfield_key.png',
		),
		
	'cn' => array(
		'text' => _("Name"),
		'important' => true
		),
	
	'sn' => array(
		'text' => _("Surname"),
		),

	'displayname' => array(
		'text' => _("Nickname"),
		),
		
	'givenname' => array(
		'text' => _("First Name"),
		),

	'description' => array(
        'text' => _("Description"),
        'input' => 'textarea'
		),
    'o' => array(
        'text' => _("Organisation"),
        'image' => 'building.png',
    ),

    'ou' => array(
        'text' => _("Organisational Unit"),
        'image' => 'building.png',
    ),
	'department' => array(
		'text' => _("Department"),
        'image' => 'building.png',
		),


	'title' => array(
		'text' => _("Title"),
        'image' => 'user.png',
		),

	'mail' => array(
		'text' => _("Email Address"),
		'url' => 'mailto',
        'additional_attrs' => array('mailalternateaddress'),
        'image' => 'email.png',
		),

	'telephonenumber' => array(
		'text' => _("Phone"),
        'url' => 'callto',
        'image' => 'telephone.png',
		),

	'homephone' => array(
		'text' => _("Home Phone"),
		'url' => 'callto',
        'image' => 'telephone.png',
		),

	'mobile' => array(
		'text' => _("Mobile Phone"),
		'url' => 'callto',
        'image' => 'phone.png',
		),

	'facsimiletelephonenumber' => array(
		'text' => _("Fax"),
		'url' => 'callto',
        'image' => 'printer.png',
		),

	'st' => array(
		'text' => _("State")
		),

	'roomnumber' => array(
		'text' => _("Room Number")
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
		'text' => _("Web Address"),
		'url' => 'labeled',
		'multi' => true
		)
);

?>
