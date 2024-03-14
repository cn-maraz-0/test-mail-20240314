<?php
/**
 * setup.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: setup.php,v 1.9 2006/08/01 08:56:50 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Squirrelmail Plugin initialization
 * @return void
 */
function squirrelmail_plugin_init_directory() {
  global $squirrelmail_plugin_hooks;

  $squirrelmail_plugin_hooks['menuline']['directory'] = 'directory_menuline';
  $squirrelmail_plugin_hooks['compose_button_row']['directory'] = 'directory_compose_button';
  $squirrelmail_plugin_hooks['optpage_register_block']['directory'] = 'directory_optpage_register_block';
  $squirrelmail_plugin_hooks['options_save']['directory'] = "directory_save";
}

/**
 * Link in top menu line
 * @return void
 */
function directory_menuline () {
    include(SM_PATH . 'plugins/directory/config.php');
    if(in_array('top', $directory_link_setup)) {

        sq_bindtextdomain('directory', SM_PATH . 'plugins/directory/locale');
        textdomain ('directory');

        displayInternalLink ("plugins/directory/directory.php", _("Directory"));
        echo "&nbsp;&nbsp;\n";

        sq_bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');
    }
}

/**
 * Data for Options Screen block.
 * @return void
 */
function directory_optpage_register_block() {
	global $optpage_blocks;

	sq_bindtextdomain('directory', SM_PATH . 'plugins/directory/locale');
	textdomain ('directory');

	include(SM_PATH . 'plugins/directory/config.php');
	if($ldq_allow_prefs_page) {
		$optpage_blocks[] = array(
			'name' => _("Directory Preferences"),
			'url'  => '../plugins/directory/options.php',
			'desc' => _("Customize the output of the Directory Services page."),
			'js'   => false
		);
	}

	$optpage_blocks[] = array(
		'name' => _("Edit Directory Profile"),
		'url'  => '../plugins/directory/editprofile.php',
		'desc' => _("Edit your personal data which other people see in the Directory Service, and set up privacy options."),
		'js'   => false
	);
    
    if(in_array('tools', $directory_link_setup)) {
        $optpage_blocks[] = array(
            'name' => _("Directory"),
            'url'  => '../plugins/directory/directory.php',
            'desc' => _("Directory Services"),
            'profile' => 'tools',
            'js'   => false
        );
    }
    
    if(in_array('options', $directory_link_setup)) {
        $optpage_blocks[] = array(
            'name' => _("Directory"),
            'url'  => '../plugins/directory/directory.php',
            'desc' => _("Directory Services"),
            'js'   => false
        );
    }
      
	sq_bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain('squirrelmail');
}

/**
 * Save preferences if form is submitted.
 */
function directory_save() {
   require_once (SM_PATH . "plugins/directory/config.php");
   global $username,$data_dir;
   global $submit_directory, $directory_directory_output_type;
   
   if ($submit_directory) {
      if (isset($directory_directory_output_type)) {
         setPref($data_dir, $username, 'directory_output_type',
                 $directory_directory_output_type);
      } else {
         setPref($data_dir, $username, 'directory_output_type', 'OneTable');
      }
  
      foreach ($ldq_attributes as $attr) {
         $Var = "directory_showattr_" . $attr;
         $Var2 = "directory_directory_showattr_" . $attr;
         global $$Var2;
         //print ("<H3>$Var - ");
         //print ($$Var2);
         //print ("</H3>");
         if (isset($$Var2)) {
            setPref($data_dir, $username, $Var, $$Var2);
         } else {
            setPref($data_dir, $username, $Var, "off");
         }
      }
  
      echo '<p align="center"><strong>';
      echo _("Directory preferences saved");
      echo '</strong></p>';
   }
}

/**
 * Add Link to Directory in Compose window, similar to the Addresses button
 * that already exists there.
 * @return void
 */
function directory_compose_button() {
    global $use_javascript_addr_book;
    
    if ($use_javascript_addr_book) {
        sq_bindtextdomain ('directory', SM_PATH . 'plugins/directory/locale');
        textdomain ('directory');
        echo " <script type=\"text/javascript\"><!--\n document.write(\"".
            "<input type=\\\"button\\\" value=\\\""._("Directory").
            "\\\" onclick=\\\"window.open('../plugins/directory/directory.php?popup=1&amp;formname=compose', ".
            "'directory', 'status=no,scrollbars=yes,width=780,height=580,resizable=yes')\\\">\");" . "\n".
            "// --></script> ";
        textdomain ('squirrelmail');
    }
}

/**
 * Version Information
 * @return string
 */
function directory_version() {
	return "1.0-uoa-cvs";
}

