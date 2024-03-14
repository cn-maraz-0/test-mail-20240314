<?php
/**
 * setup.php
 *
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Copyright (c) 2007 Tomas Kuliavas <tokul@users.sourceforge.net>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Uses standard plugin format to create a couple of forms to
 * enable import/export of CSV files to/from the address book.
 * @version $Id$
 * @package sm-plugins
 * @subpackage abook_import_export
 */

/**
 * Init plugin
 */
function squirrelmail_plugin_init_abook_import_export() {
    global $squirrelmail_plugin_hooks;
    //$squirrelmail_plugin_hooks["addressbook_bottom"]["abook_import_export"] = "abook_import_export";
    $squirrelmail_plugin_hooks['abook_tools']['abook_import_export'] = 'abook_import_export';
    //$squirrelmail_plugin_hooks['optpage_register_block']['abook_import_export'] = 'abook_import_export_optpage_register_block';
}


/**
 * Register options block page
 * @return void
 */
function abook_import_export_optpage_register_block() {
	global $optpage_blocks, $abook_import_export_enable_rules;
    bindtextdomain ('abook_import_export', SM_PATH . 'locale');
	textdomain ('abook_import_export');

	$optpage_blocks[] = array(
		'name' => _("Address Book Import / Export"),
		'url'  => '../plugins/abook_import_export/table.php',
		'desc' => _("Import and Export your Address Book Contacts."),
		'js'   => false
	);

    bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain('squirrelmail');
}

/**
 * Adds import/export form to addresses page
 */
function abook_import_export() {
    include_once(SM_PATH . 'plugins/abook_import_export/functions.php');
    aie_create_form();
}

/**
 * Displays plugin's version
 * @return string version number
 */
function abook_import_export_version() {
    return '1.1uoa';
}
