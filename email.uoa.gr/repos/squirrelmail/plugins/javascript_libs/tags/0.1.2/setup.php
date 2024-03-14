<?php
/*
 * Javascript libraries management framework for Squirrelmail Plugins.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team
 * @package plugins
 * @subpackage javascript_libs
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_javascript_libs() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['generic_header']['javascript_libs'] = 'javascript_libs_generic_header';
}

/**
 * Insert the hook for javascript libraries, in the generic header of every page.
 *
 * @see javascript_libs_generic_header_do()
 * @return void
 */
function javascript_libs_generic_header() {
	include_once(SM_PATH . 'plugins/javascript_libs/functions.php');
    javascript_libs_generic_header_do();
}    

/**
 * This is the function that plugins can use, in order to register their 
 * desired libraries for a specific page.
 *
 * @see javascript_libs_register_do()
 * @param string $page
 * @param array $javascripts
 * @return void
 */
function javascript_libs_register($page, $javascripts) {
	include_once(SM_PATH . 'plugins/javascript_libs/functions.php');
    javascript_libs_register_do($page, $javascripts);
}


/**
 * Return information about plugin.
 * @return array
 */
function javascript_libs_info() {
   return array(
       'english_name' => 'Javascript Libraries Common Loader',
       'version' => '0.1.2',
       'summary' => 'Javascript Libraries Loader Library and Repository for Squirrelmail Plugins',
       'details' => 'This plugin stores the most common and popular javascript libraries, and enables plugins to load them in a fashion that can be interoperable between plugins; that is, two plugins can use a javascript library in the same page, and it will be loaded only once.'
   );
}

/**
 * Return plugin version.
 * @return string
 */
function javascript_libs_version() {
   $info = javascript_libs_info();
   return $info['version'];
}

