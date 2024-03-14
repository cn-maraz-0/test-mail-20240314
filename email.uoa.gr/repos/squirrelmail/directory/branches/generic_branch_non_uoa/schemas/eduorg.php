<?php
/**
 * eduorg.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: eduorg.php,v 1.4 2004/07/20 15:53:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @see http://www.educause.edu/netatedu/groups/pki/eduperson/internet2-mace-dir-eduOrg-200210.htm
 * @see http://www.educause.edu/netatedu/groups/pki/eduperson
 */

/**
 * This file contains schemas and various useful structures for the eduPerson
 * schema, as defined by the Internet2 Middleware, Architecture Committee,
 * Directory Working Group (MACE-Dir).
 */

/**
 * Map permissible affiliation attribute values to their friendly names.
 * @var array
 */
$affiliations_map = array(
'faculty' => _("Faculty"),
'student'=> _("Student"),
'staff'=> _("Staff"),
'alum'=> _("Alum"),
'member'=> _("Member"),
'affiliate'=> _("Affiliate"),
'employee'=> _("Employee")
);

/**
 * Support for the eduPerson Schema.
 * @var array
 */
$eduperson_schema = array(
	'edupersonorgdn' => array(
		'text' => _("Organization"),
		'followme' => true,
		'followme_show' => 'cn',
		'url' => 'eduorg'
		),
	
	'edupersonorgunitdn' => array(
		'text' => _("Organizational Unit"),
		'followme' => true,
		'followme_show' => 'cn',
		'url' => 'eduorg',
		'additional_attrs' => array('edupersonprimaryorgunitdn')
		),

	'edupersonaffiliation' => array(
		'text' => _("Affiliation"),
		'additional_attrs' => array('edupersonprimaryaffiliation'),
		'map' => $affiliations_map
		),

	'edupersonnickname' => array(
		'text' => _("Nickname")
		),

	'edupersonprincipalname' => array(
		'text' => _("Principal Name")
		),

	'edupersonentitlement' => array(
		'text' => _("Entitlement")
		)
);

/**
 * Support for the eduOrg Schema.
 * Note: 'cn' is already defined elsewhere.
 *
 * @var array
 */
$eduorg_schema = array(
	'eduorghomepageuri' => array(
		'text' => _("Home Page"),
		'url' => 'http'
		),
	'eduorgidentityauthnpolicyuri' => array(
		'text' => _("Identification Policy"),
		'url' => 'http'
		),
	'eduorglegalname' => array(
		'text' => _("Legal Name")
		),
	'eduorgsuperioruri' => array(
		'text' => _("Superior Unit"),
		'url' => 'ldap',
		'followme' => true
		),
	'eduorgwhitepagesuri' => array(
		'text' => _("White Pages"),
		'url' => 'http'
		)
);

?>
