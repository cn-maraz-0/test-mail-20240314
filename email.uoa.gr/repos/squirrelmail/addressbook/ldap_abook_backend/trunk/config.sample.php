<?php

global $ldapAbookDebug, $ldapAbookReplaceFileAbook,
       $ldapAbookHost, $ldapAbookOptions, $ldapAbookUseTls,
       $ldapAbookUseSelfAuth, $ldapAbookBindDn, $ldapAbookBindPassword,
       $ldapAbookBase, $ldapAbookFilter,
       $ldapAbookObjectClass,
       // attribute names
       $ldapAbookAttrRDN, $ldapAbookAttrNickname, $ldapAbookAttrFirstname, $ldapAbookAttrLastname, 
       $ldapAbookAttrEmail,$ldapAbookAttrLabel,
       // required attributes
       $ldapAbookRequiredNickname, $ldapAboobRequiredFirstname, $ldapAbookRequiredLastname,
       $ldapAbookRequiredEmail, $ldapAbookRequiredLabel,
       // full name template
       $ldapAbookFullnameTemplate,
       $ldapAbookValidateEmail,
       // container object
       $ldapAbookContainerObjectClass, $ldapAbookContainerDnAttr,
       // Other options
       $ldapAbookAlwaysUseConfiguredRdn;

// Enable/disable debug log. The log file should be specified in the
// LDAP Common plugin's config.php file.
$ldapAbookDebug = false;

// If set to true, it will replace the built in file-based or database
// address book.
// If set to false, it will be added as an additionaly address book.
$ldapAbookReplaceFileAbook = true;

// LDAP hostname
$ldapAbookHost = "localhost";

// Use TLS when connecting. You must choose LDAP Protocol V3 for
// TLS. It should be enabled in $ldapAbookOptions
$ldapAbookUseTls = false;

// Array of key-values. ldap_set_option will be called for
// key every with the corresponding value.
// $ldapAbookOptions = array();
$ldapAbookOptions = array("LDAP_OPT_PROTOCOL_VERSION" => 3);

// Bind as a manager account, or bind as the virtual mail user itself
//
// If set to true, user's DN will be searched using $ldapAbookBindFilter
// under $ldapAbookBindBase and will bind to LDAP using the found object's
// DN and the password that the user used to log in into SquirrelMail.
// This is actually the same approach as many IMAP servers do for authentication.
//
// If set to false, $ldapAbookBindDn and $ldapAbookBindPassword will be used for
// binding. It can be useful if your users can access their mail account
// objects directly in LDAP and you don't want them to modify these
// attributes.
$ldapAbookUseSelfAuth = true;

// Manager user
$ldapAbookBindDn = "cn=squirrel,dc=dev-labs,dc=com";

// Password
$ldapAbookBindPassword = "secret";

// Base DN for searching users
$ldapAbookBase = "ou=virtMail,dc=dev-labs,dc=com";

// Filter used to search the mail account object.
// Address book entries will be added under this object.
$ldapAbookFilter = "mail=%username";

// These values are used to create new address book entries.
// Every value in "objectClass" attribute should be specified here.
$ldapAbookObjectClass = array("top", "inetOrgPerson");

// SquirreMail uses five attributes:
// nickname (which should be unique), firstname, lastname,
// email and label (description).
// Their corresponding LDAP attribute names should be specified
// in the appropriate variable. Additionally, LDAP attributes
// that are required (MUST in schema), should be set to true
// in the corresponding $ldapAbookRequiredXXX variable.

$ldapAbookAttrRDN = 'uid';
$ldapAbookAttrNickname = "displayName";
$ldapAbookRequiredNickname = true;

$ldapAbookAttrFirstname = "givenName";
$ldapAboobRequiredFirstname = false;

$ldapAbookAttrLastname = "sn";
$ldapAbookRequiredLastname = true;

$ldapAbookAttrEmail = "mail";
$ldapAbookRequiredEmail = false;

$ldapAbookAttrLabel = "description";
$ldapAbookRequiredLabel = false;

// The full name, which can be seen in the address list is not
// stored. LDAP AddressBook Plugin generates it using the value
// of the lastname and firstname attributes.
// Names in Hungary, China, Japan, Vietnam are in format 
// %lastname %firstname
$ldapAbookFullnameTemplate = "%firstname %lastname";
//$ldapAbookFullnameTemplate = "%lastname %firstname";

// LDAP mail attribute allows only 7 bit characters. The plugin checks this
// by default, but it may be disabled if a custom attribute is used that allows
// 8 bit characters.
$ldapAbookValidateEmail = true;

// If addresses are stored under a dedicated object, then address book
// operations will be performed under this object, not under the user's object.
// This value is simply prepended to the user's DN.
$ldapAbookContainerDnAttr = "";
//$ldapAbookContainerDnAttr = "ou=addresses";

// If addresses are stored under a dedicated object, under the user's object,
// then the plugin can automatically create this object under the user's
// object. To do this, objectClass for this dedicated object should be
// specified here. Use it only if $ldapAbookContainerDnAttr is not empty.
$ldapAbookContainerObjectClass = array();
//$ldapAbookContainerObjectClass = array("top", "organizationalUnit");


$ldapAbookAlwaysUseConfiguredRdn = true;
