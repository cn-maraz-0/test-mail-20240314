<?php
/**
 * Autocomplete for Squirrelmail
 *
 * @package plugins
 * @subpackage javascript_autocomplete
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

/**
 * Plugin setup
 */
function squirrelmail_plugin_init_javascript_autocomplete() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['compose_bottom']['javascript_autocomplete'] = 'javascript_autocomplete_compose_bottom';
	$squirrelmail_plugin_hooks['generic_header']['javascript_autocomplete']  = 'javascript_autocomplete_generic_header';
    $squirrelmail_plugin_hooks['options_display_inside']['javascript_autocomplete']   = 'javascript_autocomplete_options_display_inside';
    $squirrelmail_plugin_hooks['options_display_save']['javascript_autocomplete']   = 'javascript_autocomplete_options_display_save';
}   
   

function javascript_autocomplete_generic_header() {
	$page = basename($_SERVER['PHP_SELF']);
	switch ($page) {
		case 'addrbook_search.php':
		case 'abook_group_interface.php':
			// abook_init_do();
			break;
		case 'compose.php':
			require_once(SM_PATH . 'plugins/javascript_autocomplete/include/compose.php');
            javascript_autocomplete_compose_header();
			break;
		default:
	}
}

function javascript_autocomplete_compose_bottom() {
	include_once(SM_PATH . 'plugins/javascript_autocomplete/include/compose.php');
    javascript_autocomplete_compose_main();
}

function javascript_autocomplete_options_display_inside($args) {
	include_once(SM_PATH . 'plugins/javascript_autocomplete/include/options.php');
    javascript_autocomplete_options_display_inside_do($args);
}

function javascript_autocomplete_options_display_save() {
	include_once(SM_PATH . 'plugins/javascript_autocomplete/include/options.php');
    javascript_autocomplete_options_display_save_do();
}

function javascript_autocomplete_info() {
	return array(
		'english_name' => 'Javascript Compose Autocomplete',
		'version' => '0.1',
		'summary' => 'Address Completion during compose',
	);
}

function javascript_autocomplete_version() {
	$info = javascript_autocomplete_info();
	return $info['version'];
}