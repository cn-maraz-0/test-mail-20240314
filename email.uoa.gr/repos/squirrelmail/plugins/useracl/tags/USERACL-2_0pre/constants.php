<?php
/**
 * constants.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: constants.php,v 1.4 2004/08/09 13:07:26 avel Exp $
 *
 * Constants that might be of use somewhere
 */

$acl = array(
	'read' => array(
		'acl' => 'lrs',
		'desc' => _("Read"),
	),
	'insert' => array(
		'acl' => 'wi',
		'desc' => _("Insert")
	),
	'post' => array(
		'acl' => 'p',
		'desc' => _("Post")
	),
	'delete' => array(
		'acl' => 'd',
		'desc' => _("Delete")
	),
	'admin' => array(
		'acl' => 'a',
		'desc' => _("Admin")
	)
);

?>
