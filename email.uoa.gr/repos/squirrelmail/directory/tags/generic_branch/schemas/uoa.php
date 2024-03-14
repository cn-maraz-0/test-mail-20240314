<?php
/**
 * uoa.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage custom
 * @version $Id: uoa.php,v 1.10 2004/07/16 16:28:22 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Custom schemas for University of Athens - not applicable elsewhere...
 */

/**
 * Attributes available to Privacy Options
 * @var array
 */
$privacy_attrs = array('mail', 'telephonenumber', 'facsimiletelephonenumber',
'postaladdress', 'edupersonaffiliation', 'description', 'uoaprojectinvolved');

/**
 * UoA Attributes.
 * @var array
 */
$uoa_attributes = array(
	'uoastructuraltype' => array(
		'text' => _("Structural Type")
		// 'hide' => true
	),

	'uoaknowledgesubject' => array(
		'text' => _("Knowledge Subject")
	),

	'uoaprojectinvolved' => array(
		'text' => _("Project Involved In")
	),

	'uoaprivate' => array(
		'text' => _("Attributes to remain private from the public"),
		'input' => 'checkboxes',
		'posvals' => $privacy_attrs
	),
	'uoaprivateinternal' => array(
		'text' => _("Attributes to remain private for University Users"),
		'input' => 'checkboxes',
		'posvals' => $privacy_attrs
	)
);


?>
