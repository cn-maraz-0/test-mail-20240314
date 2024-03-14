<?php
/**
 * setup.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2008 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 *
 * Squirrelmail Plugin Functions
 */

/**
 * Plugin initialization.
 *
 * @return void
 */
function squirrelmail_plugin_init_useracl() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['useracl'] = 'useracl_menuline';
    $squirrelmail_plugin_hooks['folders_bottom']['useracl'] = 'useracl_folders_bottom';
    $squirrelmail_plugin_hooks['pagetop']['useracl'] = 'useracl_pagetop';
}

/**
 * Place Link to Squirrelmail's Menu Line
 * @return void
 */
function useracl_menuline() {
    include_once(SM_PATH . 'plugins/useracl/config.php');
    global $useracl_links;
    if(in_array('top', $useracl_links)) {
        include_once(SM_PATH . 'plugins/useracl/include/html_links.inc.php');
        useracl_link_menuline_do();
    }
}    

function useracl_folders_bottom() {
    include_once(SM_PATH . 'plugins/useracl/config.php');
    global $useracl_links;
    if(in_array('folders', $useracl_links)) {
        include_once(SM_PATH . 'plugins/useracl/include/html_links.inc.php');
        useracl_link_folders_page();
    }
}    

/**
 * Place link in Squirrelmail page-top ("Current Folder: Foo").
 * @return void
 */
function useracl_pagetop() {
    include_once(SM_PATH . 'plugins/useracl/pagetop.php');
    return useracl_pagetop_do();
}    

/**
 * Return information about plugin.
 * @return array
 */
function useracl_info() {
   return array(
       'english_name' => 'UserACL',
       'version' => '2.2.1svn',
       'summary' => 'Allows users to set ACLs for their folders and share their contents with other users.',
   );
}

/**
 * Version information
 * @return string
 */
function useracl_version() {
    $info = useracl_info();
    return $info['version'];
}
 
