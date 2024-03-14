<?php
/**
 * LDAP Global Preferences. Change these to your setup -- and rename the file
 * to config.php!
 *
 * $Id: config_sample.php,v 1.2 2004/02/10 12:46:00 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */

/**
 * $ldap_host can be a space-separated list of hosts, for automatic failover
 * @var string
 */
global $ldap_host;
$ldap_host = '10.10.10.1 10.10.10.2';

/**
 * $ldap_master_host MUST be the single, master LDAP server for writes.
 * @var string
 */
global $ldap_master_host;
$ldap_master_host = '10.10.10.1';

/**
 * Which ldap attribute holds the username?
 * @var string
 */
global $ldap_user_search_filter;
$ldap_user_search_filter = 'uid=%s';

/**
 * LDAP Base DN
 * @var string
 */
global $ldap_base_dn;
$ldap_base_dn = 'o=organization,c=country';

/**
 * Object class to be required for the authentication of the user.
 * @var string
 */
global $required_objectclas;;
$required_objectclass = 'mailRecipient';

/**
 * LDAP network timeout in seconds
 * @var int
 */
global $ldap_network_timeout;
$ldap_network_timeout = 10;

/**
 * Credentials for the LDAP Squirrelmail Admin Account (Read only).
 * @var string
 */
global $ldap_bind_dn;
$ldap_bind_dn = "uid=squirrelDaemon,ou=admins,ou=Services,$ldap_base_dn";
global $ldap_bind_pw;
$ldap_bind_pw = "secret";

/**
 * Credentials for the LDAP Squirrelmail Admin Account (with write
 * permissions). Can be the manager account (although not recommended). Only
 * used when updating the objectclass
 * @var string
 */
global $ldap_manager_dn;
$ldap_manager_dn = "uid=squirreladmin,ou=admins,ou=Services,$ldap_base_dn";
global $ldap_manager_pw;
$ldap_manager_pw = "secret";

/**
 * The object class that has all the squirrelmail attributes
 * @var string
 */
global $ldap_objectclass;
$ldap_objectclass = 'SquirrelMailUser';

/**
 * Enable imapproxy mode
 * @var boolean
 */
global $imapproxymode;
$imapproxymode = false;

/** 
 * Define Imapproxy mode server mapping
 * @var array
 */
global $ldapuserdata_imapproxyserv;
$ldapuserdata_imapproxyserv = array(
	'real.imap.host' => 'localhost',
	'real.imap.host2' => 'localhost'
);

/**
 * The default Attributes' values.
 *
 * These are used if the LDAP value is null. The format is simply:
 * 'squirrelmail_variable_name' => 'Default value'.  Consider this as our
 * version of data/default_pref, for the file preferences backend equivalent.
 *
 * @var array
 */
global $prefs_default;
$prefs_default = array (
'trash_folder' => 'INBOX.Trash',
'sent_folder' => 'INBOX.Sent' ,
'draft_folder' => 'INBOX.Drafts' ,
'show_html_default' =>  0,
'chosen_theme' => '../themes/plain_blue_theme.php',
'custom_css' => 'sans-10.css',
'javascript_setting' =>  2,
'use_javascript_addr_book' => 1,
'show_num' => 20,
'alt_index_colors' => 0,
'page_selector' => 1,
'page_selector_max' => 10,
'wrap_at' => 86,
'editor_size' => 76,
'include_self_reply_all' => 0,
'show_xmailer_default' => 1,
'attachment_common_show_images' => 0,
'pf_subtle_link' => 1,
'pf_cleandisplay' => 0,
'mdn_user_support' => 1,
'compose_new_win' => 1,
'compose_width' => 620,
'compose_height' => 480,
'sig_first' => 0,
'internal_date_sort' => 0,
'sort_by_ref' => 1, /* NEW */

'language' => 'en_US',
'location_of_buttons' => 'between',
'use_signature' => 0,
'prefix_sig' => 1,
'order1' => 1,
'order2' => 2,
'order3' => 3,
'order4' => 5,
'order5' => 4,
'order6' => 6,
//'left_size' => 150,
'left_size' => 200,
'unseen_notify' => 3, /* 2 */
'unseen_type' => 2, /* 1 */
'collapse_folders' => 1, /* 0 */
'date_format' => 6,
'hour_format' => 1,
'search_memory' => 0,
'mailbox_select_style' => 0,
/* These are for enabling the special status of INBOX.Trash,Sent,Draft folders */
'move_to_trash' => 1,
'move_to_sent' => 1,
'save_as_draft' => 1,
/* Server-side sorting breaks if we do not provide a default */
'sort' => 0,
/* Mailfetch cypher always on */
'mailfetch_cypher' => 'on',
/* Some more defaults for translate plugin */
'translate_server' => 'otenet',
'translate_location' => 'center',
'translate_show_read' => 1,
'translate_show_send' => 0,
'translate_same_window' => 0,
/* variable_sent_folder */
'variable_sent_folder_on' => 3,
'variable_sent_folder_default' => 1,
/* Avelsizes */
'folder_sizes_subtotals' => 0,
'folder_sizes_left_link' => 1,
'folder_sizes_on_folder_page' => 1,
/* jsclock */
'jsclock_face_name' => "japanese",
'jsclock_position' => "above",
'jsclock_hours' => 24,
'jsclock_seconds' => 1,
'jsclock_border' => 0,
'jsclock_bgcolor' => "",
'jsclock_padding' => 1,
/* Directory Services plugin */
'directory_output_type' => 'onetable',
'directory_showattr_cn' => 1,
'directory_showattr_department' => 0,
'directory_showattr_edupersonorgunitdn' => 1,
'directory_showattr_description' => 0,
'directory_showattr_title' => 0,
'directory_showattr_telephonenumber' => 1,
'directory_showattr_facsimiletelephonenumber' => 1,
'directory_showattr_mail' => 1,
'directory_showattr_edupersonaffiliation' => 1
);

/**
 * Description of the schema - available LDAP attributes.
 *
 * Attributes are described in this array as:  'LDAP attribute names' =>
 * 'Squirrelmail variable names'
 * @var array
 */
global $ldap_attributes;
$ldap_attributes = array (
/* The first three are not actually Squirrelmail-specific attributes. */
'cn' => 'full_name',
'mail' => 'email_address',
'mailhost' => 'imapServerAddress',

/* Some other stuff, more advanced... */
/* 'mailalternateaddress' => 'mailalternateaddress', */

/* On to the squirrelmail specific values which will actually be changed and
 * written back to LDAP. */

/* display -> General */

'chosentheme' => 'chosen_theme',
'customcss' => 'custom_css',
'language' => 'language',
'javascriptsetting' => 'javascript_setting',
'saveoptionjavascriptautodetect' => 'save_option_javascript_autodetect',

/* display -> Mailbox display */
'shownum' => 'show_num',

/* display -> message Display & composition */
'usejavascriptaddrbook' => 'use_javascript_addr_book',
'includeselfreplyall' => 'include_self_reply_all',
'mdnusersupport' => 'mdn_user_support',
'composenewwin' => 'compose_new_win',

/* Folder -> special folders */
'trashfolder' => 'trash_folder',
'sentfolder' => 'sent_folder',
'draftfolder' => 'draft_folder',

/* Folder -> folderlist */
'leftsize' => 'left_size',
'locationofbar' => 'location_of_bar',
'leftrefresh' => 'left_refresh',
'unseennotify' => 'unseen_notify',
'unseentype' => 'unseen_type',

/* Order */
'order1' => 'order1',
'order2' => 'order2',
'order3' => 'order3',
'order4' => 'order4',
'order5' => 'order5',
'order6' => 'order6', 

/* Highlight settings*/
'hililist' => 'hililist',

/* Personal */
'replyto' => 'reply_to',
'signature' => 'signature',
'timezone' => 'timezone', 
'replycitationstyle' => 'reply_citation_style',
'replycitationstart' => 'reply_citation_start',
'replycitationend' => 'reply_citation_end',
'usesignature' => 'use_signature',
/* This one is for mailAlternateAddress */
'mailpreferred' => 'mailpreferred',
'cnpreferred' => 'namepreferred',

/* Plugin attrs */
/* Plugin attributes: */
/* html_mail */
'composewindowtype' => 'compose_window_type'
);

/**
 * Boolean attributes schema.
 * The attributes that have boolean values need special treatment due to LDAP
 * design. Repeat them here.
 * @var array
 */
global $boolean_attrs;
$boolean_attrs = array(
'includeselfreplyall' => 'include_self_reply_all',
'mdnusersupport' => 'mdn_user_support',
'composenewwin' => 'compose_new_win',
'usesignature' => 'use_signature'
);

/**
 * Multivalue attributes schema.
 *
 * These are special cases for multivalue attributes. Again ldapattr =>
 * squirrelmailattr.
 * @var array
 */
global $multivalue_attrs;
$multivalue_attrs = array(
/* Mailfetch settings */
'mailfetch' => 'mailfetch',
/* Newmail plugin settings */
'newmail' => 'newmail'
);

/**
 * Attributes that define allowed mail addresses.
 *
 * These are all multivalue attributes. Again ldapattr => squirrelmailattr
 * @var array
 */
global $alternatemail_attrs;
$alternatemail_attrs = array(
'mailalternateaddress' => 'alternateemails',
'mailauthorizedaddress' => 'mailauthorizedaddresses',
'mailauthorizedreplyto' => 'mailauthorizedreplyto'
);

?>
