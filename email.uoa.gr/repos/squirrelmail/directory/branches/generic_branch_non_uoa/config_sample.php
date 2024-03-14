<?php
/**
 * config.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: config_sample.php,v 1.10.2.1 2005/04/21 11:37:34 avel Exp $
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
	    'host' => 'db.debian.org',
	    'base' => 'dc=debian,dc=org',
	    'name' => 'Debian Developers Directory',
	    'port' => 389,
	    'charset' => 'utf-8',
	    'maxrows' => 200,
	    'timeout' => 20
	//    'binddn' => 'cn=foo,dc=example,dc=org',
	//    'bindpw' => 'secret'
	//    'writedn' => 'cn=bar,dc=example,dc=org',
	//    'writepw' => 'secret'
	);
	
	/** Default Preferences */
	$directory_prefs_default = array(
		'directory_output_type' => 'onetable',
		'directory_showattr_cn' => 1,
		'directory_showattr_department' => 0,
		'directory_showattr_description' => 0,
		'directory_showattr_title' => 0,
		'directory_showattr_telephonenumber' => 1,
		'directory_showattr_facsimiletelephonenumber' => 1,
		'directory_showattr_mail' => 1,
		'language' => 'en_US'
	);
}

/**
 * @var array Attributes available to the user interface, when public (not
 * authenticated)
 * @todo Not implemented yet
 */

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

/**
 * @var array LDAP  attributes that are allowed to be edited freely by the
 * user.
 */
$ldq_editable_attrs = array('telephonenumber', 'postaladdress', 'postalcode',
	'postofficebox', 'physicaldeliveryofficename', 'preferreddeliverymethod');

/**
 * @var string An optional URL that links to a page, that has a form allowing
 * more attributes to be edited.
 */
$editprofile_url = 'https://webadm.uoa.gr/users/index.html';

/**
 * @var array Languages to be enabled when editing variables.
 */
$editprofile_langs = array('en');





/**
 * ---------- Attributes and LDAP Filters Setup -------------
 */

/**
 * @var boolean Allow user to select which attributes are displayed?
 */
$ldq_allow_prefs_page = true;

/**
 * @var array What attributes should be available to the user interface. User
 * can choose to display all these at maximum.
 */
$ldq_enable_attrs = array(
	'cn', 'description', 'title', 'telephonenumber',
	'facsimiletelephonenumber', 'mail', 'l', 'postaladdress', 'postalcode',
	'postofficebox', 'physicaldeliveryofficename', 'preferreddeliverymethod'
);

/**
 * @var boolean Support eduPerson and eduOrg schemas?
 */
$ldq_support_eduperson = false;

/**
 * @var array Which organizational unit Attributes to show in popup window?
 * Must be one of the attributes defined already in the schema. (See
 * constants.php and eduorg.php).
 */
$ldq_enable_ou_attrs = array(
	'uoastructuraltype',
	'cn',
	'telephonenumber',
	'facsimiletelephonenumber',
	'postaladdress',
	'postalcode',
	'description'
	/*
	'eduorghomepageuri',
	'eduorgwhitepagesuri',
	'eduorglegalname',
	'eduorgsuperioruri'
	*/
);

/**
 * @var boolean Enable restrict to certain ou's option? EduPerson / EduOrg
 * support must be enabled for this option to work.
 */
$ldq_restrict_ou = true;

/**
 * @var string Filter which will be ANDed when searching for OUs.
 */
$ldq_ou_filter = '(objectclass=organizationalunit)';

/**
 * @var boolean Show Alternate mail addresses for attribute 'mail'?
 */
$ldq_enablemailalternate = true;

/**
 * @var array What attributes do to permit searching for/by. Must be a subset
 * of $ldq_enable_attrs.
 */
$ldq_searchattrs = array ('cn', 'sn', 'mail');

/**
 * @var array What objects to permit searching for?
 *
 */
$ldq_enable_searchfor = array('person');

/**
 * @var array What attributes to permit sorting by?
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
 * @var array Personal titles descriptions in various languages.
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
 * @var string Privacy attribute for public users
 */
$ldq_privacy_attribute = 'uoaprivate';

/**
 * @var string Privacy attribute for internal users
 */
$ldq_privacy_attribute_internal = 'uoaprivateinternal';

/**
 * @var string URL that links to the Privacy Policy description
 */
$ldq_privacy_url = 'http://email.uoa.gr/help/uoa/privacy.php';


/**
 * ---------- User Default Preferences / Interface -------------
 */

/**
 * @var string Default output style. (One of 'onetable' or 'multitable')
 */
$directory_default_output_type = 'onetable';


/**
 * @var array Default attributes to show, if user has not set the preferences.
 * Must be a subset of $ldq_enable_attrs
 */
$ldq_default_attrs = array(
	'cn', 'department', 'edupersonaffiliation', 'telephonenumber',
	'facsimiletelephonenumber', 'mail'
);

/**
 * @var integer Trim strings at this length
 */
$trim_at = 65;

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
$ldq_custom = '';


?>
