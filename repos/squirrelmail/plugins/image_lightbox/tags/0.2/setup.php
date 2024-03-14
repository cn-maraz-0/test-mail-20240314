<?php
/*
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 Alexandros Vellis
 * @package plugins
 * @subpackage image_lightbox
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_image_lightbox() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['javascript_libs_register']['image_lightbox'] = 'image_lightbox_register';
    $squirrelmail_plugin_hooks['attachment image/png']['image_lightbox'] = 'image_lightbox_handle_image_attachment';
    $squirrelmail_plugin_hooks['attachment image/jpg']['image_lightbox'] = 'image_lightbox_handle_image_attachment';
    $squirrelmail_plugin_hooks['attachment image/jpeg']['image_lightbox'] = 'image_lightbox_handle_image_attachment';
    $squirrelmail_plugin_hooks['attachment image/gif']['image_lightbox'] = 'image_lightbox_handle_image_attachment';
}

   
/**
 * Register the javascript libraries for page read_body.php.
 *
 * @return void
 */
function image_lightbox_register() {
    // This is for lightbox 2.02 (does not work well)
    //javascript_libs_register('read_body.php', array('prototype', 'effects', 'lightbox'));

    // This is for lightbox_plus:
    javascript_libs_register('src/read_body.php', array('spica/spica.js', 'lightboxplus/lightbox_plus.js'));
}    

/**
 * Attachment handling function
 *
 * @param array $args
 * @return void
 * @see image_lightbox_handle_image_attachment_do()
 */
function image_lightbox_handle_image_attachment(&$Args) {
    include_once(SM_PATH . 'plugins/image_lightbox/functions.php');
    image_lightbox_handle_image_attachment_do($Args);
}

/**
 * Return information about plugin.
 * @return array
 */
function image_lightbox_info() {
   return array(
       'english_name' => 'Lightbox Image Display',
       'version' => '0.2',
       'summary' => 'A sample plugin that makes use of javascript_libraries plugin',
       'details' => 'This is at the moment a demonstration-only plugin, that makes use of the javascript_libraries plugin. It enables showing an image attachment via the Javascript program "Lightbox"',
       'required_plugins' => array( 'javascript_libs' => 0 )
   );
}

/**
 * Return plugin version.
 * @return string
 */
function image_lightbox_version() {
   $info = image_lightbox_info();
   return $info['version'];
}

