<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_addressbook_ui_enhancements() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['page_header_onload']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_onload';
    $squirrelmail_plugin_hooks['addressbook_before_list']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_before_list';
    $squirrelmail_plugin_hooks['addressbook_after_row']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_after_row';
    $squirrelmail_plugin_hooks['addressbook_bottom']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_bottom';
    $squirrelmail_plugin_hooks['javascript_libs_register']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_register_jslibs';
    $squirrelmail_plugin_hooks['generic_header']['addressbook_ui_enhancements'] = 'addressbook_ui_enhancements_generic_header';
}

/**
 * In the "onload" event of the Addressbook page.
 * @see addressbook_ui_enhancements_onload_do()
 */
function addressbook_ui_enhancements_onload($onload) {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/html_components.inc.php');
    return addressbook_ui_enhancements_onload_do($onload);
}

/**
 * Before Addressbook list.
 * @see addressbook_ui_enhancements_before_list_do()
 */
function addressbook_ui_enhancements_before_list() {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/html_components.inc.php');
    addressbook_ui_enhancements_before_list_do();
}

/**
 * After each row.
 * @see addressbook_ui_enhancements_after_row_do()
 */
function addressbook_ui_enhancements_after_row(&$args) {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
    addressbook_ui_enhancements_after_row_do($args);
}

/**
 * Inside each row's <tr> element.
 * @see addressbook_ui_enhancements_row_tag_do()
 */
function addressbook_ui_enhancements_row_tag(&$row) {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
    return addressbook_ui_enhancements_row_tag_do($row);
}
   
/**
 *
 * @see addressbook_ui_enhancements_name_col_do()
 */
function addressbook_ui_enhancements_name_col(&$row, $columnName = '') {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
    return addressbook_ui_enhancements_name_col_do($row, $columnName);
}

/**
 *
 * @see addressbook_ui_enhancements_bottom_do()
 */
function addressbook_ui_enhancements_bottom() {
    include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/functions.inc.php');
    addressbook_ui_enhancements_bottom_do();
}

/**
 * Load essential files and add custom CSS style sheet, when page == src/addressbook.php
 */
function addressbook_ui_enhancements_generic_header() {
    global $PHP_SELF, $base_uri;
    $pathinfo = pathinfo($PHP_SELF);
    
    if((substr($pathinfo['basename'], 0, strlen('addressbook.php')) == 'addressbook.php') &&
      substr($pathinfo['dirname'], -3) == 'src' ) {
          
        include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/constants.inc.php');
        include_once(SM_PATH . 'plugins/addressbook_ui_enhancements/include/javascripts.inc.php');

        echo "\n".'<link rel="stylesheet" type="text/css" href="'.$base_uri.'plugins/addressbook_ui_enhancements/css_styles.php" />';
        echo "<script type=\"text/javascript\">\n" .
        addressbook_ui_javascripts_main() .
        '</script>';
    }
}

/**
 * Register the main addressbook_ui_enhancements scripts with the javascript_libs plugin.
 */
function addressbook_ui_enhancements_register_jslibs() {
    global $plugins;
    if(in_array('javascript_libs', $plugins)) {
        javascript_libs_register('src/addressbook.php', array('prototype-1.6.0.3/prototype.js', 'scriptaculous-1.8.1/effects.js'));
    }
}

/**
 * Return information about plugin.
 * @return array
 */
function addressbook_ui_enhancements_info() {
   return array(
       'english_name' => 'Addressbook UI Enhancements',
       'version' => '0.1svn',
       'summary' => 'Personal Addressbook - User Interface Enhancements and handling of additional LDAP attributes',
       'author' => 'Alexandros Vellis',
       'requirements' => 'Plugin uoa_enhancements',
   );
}

/**
 * Return plugin version.
 * @return string
 */
function addressbook_ui_enhancements_version() {
   $info = addressbook_ui_enhancements_info();
   return $info['version'];
}

