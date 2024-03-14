<?php
/**
 * config.php
 *
 * Configuration file for Directory Services Application.
 *
 * Copyright (c) 2004-2005 Alexandros Vellis <avel@noc.uoa.gr>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: config_sample.php,v 1.15 2006/08/01 08:56:50 avel Exp $
 * @copyright (c) 2004-2006 Alexandros Vellis <avel@noc.uoa.gr>
 */


/**
 * ---------- Settings for Standalone Environment ----------
 */

/**
 * @var boolean Set to true if this is an installation in standalone mode, or
 * false if it is as a Squirrelmail Plugin.
 */
$ldq_standalone = false;

if($ldq_standalone == true) {
/* Define LDAP server(s) just like in Squirrelmail. */
$ldap_server[0] = array(
    'host' => 'ldap.example.org',
    'base' => 'dc=example,dc=org',
    'name' => 'Directory Name',
	// 'name;lang-??' => 'Directory Name in different Language',
    'port' => 389,
    'charset' => 'utf-8',
    'maxrows' => 200,
    'timeout' => 20,
    'binddn' => 'cn=foo,dc=example,dc=org',
    'bindpw' => 'secret'
    'writedn' => 'cn=bar,dc=example,dc=org',
    'writepw' => 'secret'
);

/**
 * @var array Default Preferences. Valid only for standalone mode.
 */
$directory_prefs_default = array(
'directory_output_type' => 'onetable',
'directory_showattr_cn' => 1,
'directory_showattr_department' => 0,
'directory_showattr_edupersonorgunitdn' => 1,
'directory_showattr_description' => 0,
'directory_showattr_title' => 1,
'directory_showattr_telephonenumber' => 1,
'directory_showattr_facsimiletelephonenumber' => 1,
'directory_showattr_mail' => 1,
'directory_showattr_edupersonaffiliation' => 1,
'language' => 'en_US'
);
}

/**
 * @var array Attributes available to the user interface, when public (not
 * authenticated).
 * @todo Not yet implemented
 */

/*
$ldq_enable_attrs_public = array(
	'cn', 'edupersonorgunitdn', 'description', 'title',
	'mail', 'edupersonaffiliation'
);
*/

/**
 * @var string Introduction page for lower frame. %s will be replaced by the
 * language string (e.g. 'en')
 */
$ldq_intro_page = 'frames/intro_uoa.%s.html';


/**
 * ---------- Edit Profile Page Setup -------------
 */

/**
 * @var array Attributes that are allowed to be edited freely in the Edit
 * Profile Page in Squirrelmail.
 */
$ldq_editable_attrs = array('l', 'postaladdress', 'postalcode',
'postofficebox', 'physicaldeliveryofficename', 'preferreddeliverymethod',
'description', 'edupersonnickname');

/**
 * @var string If you have an online form with an application that allows a
 * user to edit more important attributes, you can insert the URL here.
 * Otherwise leave empty.
 */
$editprofile_url = '';

/**
 * @var array Languages that are to be enabled while editing these attributes.
 */
$editprofile_langs = array('en', 'el');



/**
 * ---------- Attributes and LDAP Filters Setup -------------
 */

/**
 * @var array What attributes should be available to the user interface. User
 * can choose to display all these at maximum.
 */
$ldq_enable_attrs = array(
	'cn', 'edupersonorgunitdn', 'edupersonaffiliation', 'title', 'mail',
	'telephonenumber', 'facsimiletelephonenumber',
	'description',
	'l', 'postaladdress', 'postalcode',
	'postofficebox', 'physicaldeliveryofficename', 'preferreddeliverymethod',
	'edupersonnickname'
);

/**
 * @var boolean Enable Preferences Page in Squirrelmail?
 */
$ldq_allow_prefs_page = true;

/**
 * @var boolean Special Support for eduPerson and eduOrg schemas?
 */
$ldq_support_eduperson = true;

/**
 * @var array Which organizational unit Attributes to show in org. unit page?
 * Must be one of the attributes defined already in the schema. (See
 * constants.php and eduorg.php).
 */
$ldq_enable_ou_attrs = array(
	'cn',
	'telephonenumber',
	'facsimiletelephonenumber',
	'postaladdress',
	'l',
	'postalcode',
	'description',
	'eduorghomepageuri',
	'eduorgwhitepagesuri',
	'eduorglegalname',
	'eduorgsuperioruri'
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
 * These addresses will be grabbed from the LDAP attribute
 * 'mailAlternateAddress'
 */
$ldq_enablemailalternate = true;

/**
 * @var array What attributes do to permit searching for/by. Must be a subset of
 * $ldq_enable_attrs.
 */
$ldq_searchattrs = array ('cn', 'sn', 'mail');

/**
 * @var array What objects to permit searching for?
 */
$ldq_enable_searchfor = array('person');

/**
 * @var array What attributes to permit sorting by?
 * @todo Not yet implemented
 */
$ldq_sortby_attrs = array(
	'cn', 'edupersonorgunitdn', 'mail', 'edupersonaffiliation'
);




/**
 * ---------- Edit EduOrg Attributes Setup ----------
 * These affect how the editing of eduorg (organizational unit object)
 * attributes are done, and by whom they are allowed to be edited.
 */

/**
 * @var array The LDAP Attributes that are editable.
 */
$ldq_eduorg_editable_attrs = $ldq_enable_ou_attrs;

/**
 * @var array For a new eduOrgUnit, specify which objectclasses it should use.
 */
$ldq_eduorg_new_objectclass = array('organizationalUnit', 'eduOrg', 'UoAStructuralUnit');

/**
 * @var array This will allow you to restrict editing to only a couple of IP
 * addresses, used by administrators. The IP addresses or networks listed here
 * will have access to the pages: editeduorginfo.php and policy.php.
 */
$ldq_trusted_networks = array(
	array('network' => '10.0.0.1', 'mask' => '255.255.255.255')
);


/**
 * ---------- Titles and eduPersonAffiliation Setup ----------
 */

/**
 * @var array This array can contain pointers to different Personal Titles per
 * affiliation. (Note, affiliation is defined per eduPersonAffiliation and
 * eduPersonPrimaryAffiliation LDAP Attributes)
 */
$affiliate_titles=array(
	'staff' => array('445','446','447'),
	'faculty'=>array('448','449','450','451','452'),
	'student'=>array('453','454'),
	'employee'=>array('445','455','456'),
	'affiliate'=>array('457','458','459','460')
);

/**
 * @var array Personal titles descriptions in various languages. You are
 * expected to fill this in as you see fit.
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
$ldq_privacy_attribute = '';

/**
 * @var string Privacy attribute for internal users. This can be a multivalue
 * LDAP attribute, that describes which attributes are to remain private to a
 * user.
 */
$ldq_privacy_attribute_internal = '';

/**
 * @var string Optional URL to a Privacy Help / Policy web page
 */
$ldq_privacy_url = '';


/**
 * ---------- User Default Preferences / Interface -------------
 */

/**
 * @var boolean Enable "browse" features
 */
$directory_enable_browse = false;

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

/** @var array Where to have links to directory services. An array that can
 * contain any or all of these values:
 *  - 'top' -> for squirrelmail's top navigation bar
 *  - 'options' -> an entry in Options screen
 *  - 'tools' -> an entry in Tools screen
 */
$directory_link_setup = array('options');


/**
 * ----------------- Misc. Configuration ----------------- 
 */


/**
 * @var string Load in custom LDAP code from file
 * "include/custom/$ldq_custom.php".  This will be loaded in every script
 * intialization, after all other includes.
 */
$ldq_custom = '';

