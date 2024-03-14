<?php
/**
 * config.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Configuration file for directory plugin for Squirrelmail.
 */


/**
 * ---------- Attributes and LDAP Filters Setup -------------
 */

/**
 * What attributes should be available to the user interface. User can choose
 * to display all these at maximum.
 * @ var array
 */
$ldq_enable_attrs = array(
	'cn', 'edupersonorgunitdn', 'description', 'title', 'telephonenumber',
	'facsimiletelephonenumber', 'mail', 'edupersonaffiliation'
);

/**
 * Support eduPerson and eduOrg schemas?
 * @var boolean
 */
$ldq_support_eduperson = true;

$uoa_support = true;

/**
 * Which organizational unit Attributes to show in popup window? Must be one of
 * the attributes defined already in the schema. (See constants.php and
 * eduorg.php).
 * @var array
 */
$ldq_enable_ou_attrs = array(
	'uoastructuraltype',
	'cn',
	'telephonenumber',
	'facsimiletelephonenumber',
	'postaladdress',
	'description',
	'eduorghomepageuri',
	'eduorglegalname',
	'eduorgsuperioruri'
);

/**
 * Filter which will be ANDed when searching for OUs.
 * @var string
 */
$ldq_ou_filter = '(objectclass=organizationalunit)';
//$ldq_ou_filter = '(&(objectclass=organizationalunit)(objectclass=eduorg))'

/**
 * Show Alternate mail addresses for attribute 'mail'?
 * @var boolean
 */
$ldq_enablemailalternate = true;

/**
 * What attributes do to permit searching for/by. Must be a subset of
 * $ldq_enable_attrs.
 * @var array
 */
$ldq_searchattrs = array ('cn', 'mail', 'department');

/**
 * What objects to permit searching for?
 * @var array
 */
$ldq_enable_searchfor = array('person');

/**
 * ---------- User Default Preferences / Interface -------------
 */

/**
 * Default output style. (One of 'onetable' or 'multitable')
 * @var string
 */
$directory_default_output_type = 'onetable';


/**
 * Default attributes to show, if user has not set the preferences. Must be a
 * subset of $ldq_enable_attrs
 * @var array
 */
$ldq_default_attrs = array(
	'cn', 'department', 'edupersonaffiliation', 'telephonenumber', 'facsimiletelephonenumber', 'mail'
);


$trim_at = 55;

/**
 * -------------- LDAP Server specific Configuration --------------
 */

/**
 * Maximum number of results for the LDAP server to return.
 * @var integer
 */
$ldq_maxres = 10;

/**
 * LDAP Base DN
 * @var string
 */
$ldap_base_dn = 'dc=domain,dc=org';

/**
 * Does the LDAP server require authentication?
 * @var boolean
 */
$ldq_authreqd = false;

/**
 * Bind dn to bind as, if required
 * @var string
 */
$ldq_bind_dn = "uid=squirrel,ou=Services,$ldap_base_dn";

/**
 * Bind password to use, if required
 * @var string
 */
$ldq_pass = 'secret';

?>
