<?php
/**
 * config.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: config_sample.php,v 1.10 2005/04/15 13:53:52 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Configuration file for directory plugin for Squirrelmail.
 */

/**
 * ---------- Settings for Standalone Environment ----------
 */

/** Set to true if this is an installation in standalone mode. */
$ldq_standalone = false;

if($ldq_standalone == true) {
/** Define LDAP server(s) just like in Squirrelmail. */
$ldap_server[0] = array(
    'host' => 'ldap.example.org',
    'base' => 'dc=example,dc=org',
    'name' => 'Directory Name',
    'port' => 389,
    'charset' => 'utf-8',
    'maxrows' => 200,
    'timeout' => 20,
    'binddn' => 'cn=foo,dc=example,dc=org',
    'bindpw' => 'secret'
    'writedn' => 'cn=bar,dc=example,dc=org',
    'writepw' => 'secret'
);

/** Default Preferences */
$directory_prefs_default = array(
'directory_output_type' => 'onetable',
'directory_showattr_cn' => 1,
'directory_showattr_department' => 0,
'directory_showattr_edupersonorgunitdn' => 1,
'directory_showattr_description' => 0,
'directory_showattr_title' => 0,
'directory_showattr_telephonenumber' => 1,
'directory_showattr_facsimiletelephonenumber' => 1,
'directory_showattr_mail' => 1,
'directory_showattr_edupersonaffiliation' => 1,
'language' => 'en_US'
);
}

/** Attributes available to the user interface, when public (not authenticated) */

/*
$ldq_enable_attrs_public = array(
	'cn', 'edupersonorgunitdn', 'description', 'title',
	'mail', 'edupersonaffiliation'
);
*/

/**
 * Introduction page for lower frame
 * @var string
 */
$ldq_intro_page = 'frames/intro.html';


/**
 * ---------- Edit Profile Page Setup -------------
 */

$ldq_editable_attrs = array('telephonenumber', 'edupersonorgunitdn',
'edupersonaffiliation');

$ldq_freely_editable_attrs = array('homephone', 'homepostaladdress',
'labeledurl', 'description', 'edupersonnickname', 'uoaprivate', 'uoaprivateinternal');



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

/**
 * Custom support for University of Athens setup
 * @var boolean
 */
$uoa_support = false;

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
	'eduorgwhitepagesuri',
	'eduorglegalname',
	'eduorgsuperioruri'
);

/**
 * Enable restrict to certain ou's option?
 * @var boolean
 */
$ldq_restrict_ou = true;

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
$ldq_searchattrs = array ('cn', 'sn', 'mail', 'department');

/**
 * What objects to permit searching for?
 * @var array
 */
$ldq_enable_searchfor = array('person');

/**
 * What attributes to permit sorting by?
 * @var array
 * @todo
 */
$ldq_sortby_attrs = array(
	'cn', 'edupersonorgunitdn', 'mail', 'edupersonaffiliation'
);

/**
 * Personal Titles per affiliation.
 * @var array
 */
$affiliate_titles=array(
	'staff' => array('445','446','447'),
	'faculty'=>array('448','449','450','451','452'),
	'student'=>array('453','454'),
	'employee'=>array('445','455','456'),
	'affiliate'=>array('457','458','459','460')
);

/**
 * Personal titles descriptions in various languages.
 * @var array
 */
$titles['445']=array(
	'en' => 'Teaching Assistant',
	'el' => 'Ειδικό Διδακτικό Προσωπικό'
);
$titles['446']=array(
	'en' => 'Administrative Staff',
	'el' => 'Διοικητικό Προσωπικό'
);
$titles['447']=array(
	'en' => 'Technical Staff',
	'el' => 'Τεχνικό Προσωπικό'
);
$titles['448']=array(
	'en' => 'Professor',
	'el' => 'Καθηγητής'
);
$titles['449']=array(
	'en' => 'Associate Professor',
	'el' => 'Αναπληρωτής Καθηγητής'
);
$titles['450']=array(
	'en' => 'Assistant Professor',
	'el' => 'Επίκουρος Καθηγητής'
);
$titles['451']=array(
	'en' => 'Professor Emeritus',
	'el' => 'Ομότιμος Καθηγητής'
);
$titles['452']=array(
	'en' => 'Lecturer',
	'el' => 'Λέκτορας'
);
$titles['453']=array(
	'en' => 'Postgraduate Student',
	'el' => 'Μεταπτυχιακός Φοιτητής'
);
$titles['454']=array(
	'en' => 'PhD Candidate',
	'el' => 'Υποψήφιος Διδάκτωρ'
);
$titles['455']=array(
	'en' => 'Research Associate',
	'el' => 'Επιστημονικός Συνεργάτης'
);
$titles['456']=array(
	'en' => 'Associate',
	'el' => 'Συνεργάτης'
);
$titles['457']=array(
	'en' => 'Honorary Doctorate',
	'el' => 'Επίτιμος Διδάκτωρ'
);
$titles['458']=array(
	'en' => 'Affiliate',
	'el' => 'Μέλος'
);
$titles['459']=array(
	'en' => 'Visitor',
	'el' => 'Επισκέπτης'
);
$titles['460']=array(
	'en' => 'Visiting Professor',
	'el' => 'Επισκέπτης Καθηγητής'
);
$titles['461']=array(
	'en' => 'Undergraduate Student',
	'el' => 'Προπτυχιακός Φοιτητής'
);


/**
 * Privacy attribute for public users
 * @var string
 */
$ldq_privacy_attribute = 'uoaprivate';

/**
 * Privacy attribute for internal users
 * @var string
 */
$ldq_privacy_attribute_internal = 'uoaprivateinternal';

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

/**
 * Trim strings at this length
 * @var integer
 */
$trim_at = 55;

/**
 * @var int Default Operational mode. Eiter 1 (simple) or 2 (advanced).
 */
$ldq_default_mode = 1;

/**
 * @var boolean Skip attributes with empty values? If false, will just print an
 * empty row, that will remind the user / administrator that this can be filled
 * in.
 */
$ldq_skip_empty_attributes = false;

/**
 * ----------------- Misc. Configuration ----------------- 
 */

/**
 * Load in custom LDAP code from file "custom/$ldq_custom.php". This will be
 * loaded in every script intialization, after all other includes.
 * @var string
 */
$ldq_custom = 'uoa';


?>
