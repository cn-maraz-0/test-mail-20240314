<?php
/**
 * constants.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: constants.php,v 1.6 2005/04/13 14:48:43 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

include(DIR_PATH . 'schemas/main.php');

if($ldq_support_eduperson == true) {
	include(DIR_PATH . 'schemas/eduorg.php');
	if($ldq_custom == 'uoa') {
		include(DIR_PATH . 'schemas/uoa.php');
		$ldq_attributes = array_merge($ldq_attributes, $eduperson_schema, $eduorg_schema, $uoa_attributes);
	} else {
		$ldq_attributes = array_merge($ldq_attributes, $eduperson_schema, $eduorg_schema);
	}
		
}

/**
 * Search Objects
 * @var array
 */
$ldq_searchobjs = array (
	'person' => array(
		'text' => _("People"),
		'filter' => 'objectclass=person',
		'rdn' => 'ou=People'
	),

	'schools' => array(
		'text' => _("Educational Units"),
		'filter' => 'objectclass=UoAStructuralUnit',
		'rdn' => 'ou=Schools'
	),

	'admin' => array(
		'text' => _("Administration Units"),
		'filter' => 'objectclass=UoAStructuralUnit',
		'rdn' => 'ou=Admin'
	),
	
	'ou' => array(
		'text' => _("Organizational Units"),
		'filter' => 'objectclass=organizationalUnit'
	),

	'any' => array(
		'text' => _("Any"),
		'filter' => 'objectclass=*'
	)
);

/**
 * Compare Types. Affects LDAP filter that is created.
 * @var array
 */
$ldq_comparetypes = array(
	'contains' => array(
		'text' => _("Contains"),
		
	),
	'is' => array(
		'text' => _("Is"),
	)
);

/**
 * Operating modes for the user interface
 */
define('DIR_SIMPLE', 1);
define('DIR_ADVANCED', 2);

?>
