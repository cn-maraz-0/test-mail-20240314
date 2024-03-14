<?php
/**
 * setup.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id: setup.php,v 1.1.1.1 2004/01/02 16:00:18 avel Exp $
 *
 * @package plugins
 * @subpackage directory
 */

/**
 * Squirrelmail Plugin initialization
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
 */
function directory_menuline () {

   bindtextdomain('directory', SM_PATH . 'plugins/directory/locale');
   textdomain ('directory');

   displayInternalLink ("plugins/directory/directory.php", _("Directory"), "right");
   echo "&nbsp;&nbsp;\n";

   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain ('squirrelmail');

}

/**
 * Data for Options Screen block.
 */
function directory_optpage_register_block() {

      global $optpage_blocks;

      bindtextdomain ('directory', SM_PATH . 'plugins/directory/locale');
      textdomain ('directory');

      $optpage_blocks[] = array(
         'name' => _("Directory Preferences"),
         'url'  => '../plugins/directory/options.php',
	 'desc' => _("Customize the output of the Directory Services page."),
         'js'   => false
      );
      
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
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
 * @todo Support non-javascript browsers.
 */
function directory_compose_button() {
        global $use_javascript_addr_book;
        bindtextdomain ('directory', SM_PATH . 'plugins/directory/locale');
        textdomain ('directory');
    
        if ($use_javascript_addr_book) {
	        echo "<br /><script type=\"text/javascript\"><!--\n document.write(\"".
		  "<input type=\\\"button\\\" value=\\\""._("Directory").
		  "\\\" onclick=\\\"window.open('../plugins/directory/directory.php?popup=1', ".
		  "'directory', 'status=no,scrollbars=yes,width=780,height=580,resizable=yes')\\\">\");" . "\n".
		  "// --></SCRIPT>";
             
		/* Non-javascript browsers not supported at the moment. */
		/*
		echo "<NOSCRIPT>\n".
		  " <input type=submit name=\"html_dir_search\" value=\""._("Directory")."\">".
		  "</NOSCRIPT>\n";
		} else {
			echo ' <input type=submit name="html_dir_search" value="'._("Directory").'">' . "\n";
		*/
        }
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain ('squirrelmail');
}

/**
 * Version Information
 * @return string
 */
function directory_version() {
	return "1.0-uoa-cvs";
}

?>
