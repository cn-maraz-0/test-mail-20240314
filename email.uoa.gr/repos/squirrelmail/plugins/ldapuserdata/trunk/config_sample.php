<?php
/**
 * LDAP Global Preferences. Change these to your setup -- and rename the file
 * to config.php!
 *
 * $Id: config_sample.php,v 1.13 2007/06/14 10:35:05 avel Exp $
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
 * Ldap filter to use while searching for username. %s will be substituted with
 * the login username.
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
 * @var boolean Remove options that are not saved in LDAP, from the options pages?
 */
$ldapuserdata_remove_options_that_are_not_saved = true;

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
'sTemplateID' => 'default',
'icon_theme' => 'template',
'chosen_fontset' => 'vistasans',
'chosen_fontsize' => '10',
'trash_folder' => 'INBOX.Trash',
'sent_folder' => 'INBOX.Sent' ,
'draft_folder' => 'INBOX.Drafts' ,
'show_html_default' =>  1,
'chosen_theme' => '../themes/plain_blue_theme.php',
'custom_css' => 'sans-10.css',
'javascript_setting' =>  2,
'use_javascript_addr_book' => 1,
'show_num' => 20,
'alt_index_colors' => 0,
'page_selector' => 5, // Change to 1 for SM stable (1.4)
'page_selector_max' => 10,
'wrap_at' => 86,
'truncate_sender' => 50,
'truncate_subject' => 50,
'editor_size' => 76,
'editor_height' => 25,
'include_self_reply_all' => 0,
'show_xmailer_default' => 1,
'attachment_common_show_images' => 0,
'pf_subtle_link' => 1,
'pf_cleandisplay' => 0,
'mdn_user_support' => 1,
'compose_new_win' => 1,
'compose_width' => 700,
'compose_height' => 600,
'sig_first' => 0,
'reply_focus' => 'focus',
'internal_date_sort' => 1,
'sort_by_ref' => 1,

'language' => 'en_US',
'location_of_buttons' => SMPREF_LOC_BOTTOM, // 'between',
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
/* Folders */
'unseen_cum' => 0,
'show_only_subscribed_folders' => 1,
'translate_special_folders' => 1,
'search_memory' => 0,
'mailbox_select_style' => 0, /* 0: long, 1: indented, 2: delimited */
/* These are for enabling the special status of INBOX.Trash,Sent,Draft folders */
'move_to_trash' => 1,
'move_to_sent' => 1,
'save_as_draft' => 1,
/* Server-side sorting breaks if we do not provide a default */
'sort' => 0,
/* Check Me */
'thread_*' => 1,
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
/* ldapfolderinfo / foldersizes */
'folder_sizes_subtotals' => 0,
'folder_sizes_left_link' => 1,
'folder_sizes_on_folder_page' => 1,
/* jsclock */
'jsclock_face_name' => '11x17fluorescent',
'jsclock_position' => 'above',
'jsclock_hours' => 24,
'jsclock_seconds' => 0,
'jsclock_border' => 0,
'jsclock_bgcolor' => '',
'jsclock_padding' => 1,
/* Delete_move_next plugin */
'delete_move_next_t' => 'on',
'delete_move_next_formATtop' => 'on',
'delete_move_next_b' => 'on',
'delete_move_next_formATbottom' => 'on',
/* quicksave plugin */
'quicksave_frequency' => '30',
'quicksave_units' => 'seconds',
'quicksave_encryption' => 'low',
/* Directory Services plugin */
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
/* folder_synch plugin */
'folder_synch_opt_left' => 1,
'folder_synch_opt_right' => 1,
/* Preview_Pane Plugin */
'previewPane_size' => '400'
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

/* display -> Editor Size */
'editorSize' => 'editor_size',
'editorHeight' => 'editor_height',

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

/* Plugin attributes: */
/* preview_pane */
'usepreviewpane' => 'use_previewPane',
'previewpaneverticalsplit' => 'previewPane_vertical_split',
'previewpanesize' => 'previewPane_size',
'previewpanerefreshList' => 'pp_refresh_message_list',

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
'usesignature' => 'use_signature',
'usepreviewpane' => 'use_previewPane',
'previewpaneverticalsplit' => 'previewPane_vertical_split',
'previewpanerefreshList' => 'pp_refresh_message_list'
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

/**
 * @var array
 * Values to be stored under ldap multivalue attribute smOptions
 */
global $sm_options_to_save;
$sm_options_to_save = array(
    'javascript_autocomplete_options',
);

/**
 * @var array These are not prefs items but are available nonetheless in the 
 * options screens, so do not remove them.
 */
global $sm_settings_do_not_remove;
$sm_settings_do_not_remove = array(
        'js_autodetect_results',
        'identities_link'
);

global $plugins;
if(in_array('junkfolder', $plugins)) {
    include(SM_PATH . 'plugins/junkfolder/config.php');
    $prefs_default['junkprune'] = '';  // $junkfolder_days;
    $ldap_attributes['junkprune'] = 'junkprune';

}

/** @var array UoA-specific: *disabled services schema, which is used to 
 * identify disabled services for a user. These will be cached into session at 
 * login. */
global $sm_uoa_disabled_services_vars;
$sm_uoa_disabled_services_vars = array(
   'sendMailDisabled','retrieveMailDisabled','eclassDisabled','openvpnDisabled','UoApAbDisabled','ftpDisabled'
);

