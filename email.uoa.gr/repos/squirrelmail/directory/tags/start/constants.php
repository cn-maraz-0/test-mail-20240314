<?php
/**
 * constants.php
 *
 * Copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: constants.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

include(SM_PATH . 'plugins/directory/schemas/main.php');

if($ldq_support_eduperson == true) {
	include(SM_PATH . 'plugins/directory/schemas/eduorg.php');
	if($uoa_support == true) {
		include(SM_PATH . 'plugins/directory/schemas/uoa.php');
		$ldq_attributes = array_merge($ldq_attributes, $eduperson_schema, $eduorg_schema, $uoa_structural_unit);
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

?>
