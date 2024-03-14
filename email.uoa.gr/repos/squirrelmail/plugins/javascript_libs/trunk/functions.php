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

if(file_exists(SM_PATH . 'plugins/javascript_libs/config.php')) {
    include_once(SM_PATH . 'plugins/javascript_libs/config.php');
} else {
    include_once(SM_PATH . 'plugins/javascript_libs/config_sample.php');
}

/**
 * Insert the hook for javascript libraries, in the generic header of every page.
 * @return void
 */
function javascript_libs_generic_header_do() {
    global $squirrelmail_plugin_hooks, $javascript_libs_hooks, $PHP_SELF, $javascript_on;
    do_hook('javascript_libs_register');
    if(isset($javascript_on) && !$javascript_on) {
        return;
    }

    // By now all plugins' javascripts should have been registered in global variable. 

    $script_url = strip_tags($_SERVER['REQUEST_URI']);
    $base = basename($_SERVER['SCRIPT_FILENAME']);
    $base_uri = sqm_baseuri();

    $outputJs = array();

    /*
     * $script_url MINUS $base_uri will end up with the path, starting from Squirrelmail root
     * directory, that plugins use to register their pages with javascript.
     * Examples:
     *  'src/right_main.php'
     *  'plugins/avelsieve/table.php'
     * and so on.
     */
    $current_path = substr($script_url, strlen($base_uri));
    if(strstr($current_path, '?')) {
        $current_path = substr($current_path, 0, strpos($current_path, '?'));
    }

    if(isset($javascript_libs_hooks[$current_path]) && is_array($javascript_libs_hooks[$current_path]) &&
         sizeof($javascript_libs_hooks[$current_path]) > 0){
         foreach($javascript_libs_hooks[$current_path] as $js) {
             if(true) {
                 $outputJs[] = $js;
             }
         }
    }
    /* Javascripts that are enabled for all pages: */
    if(isset($javascript_libs_hooks['*']) && !empty($javascript_libs_hooks[$current_path])) {
         foreach($javascript_libs_hooks[$current_path] as $js) {
             if(true) {
                 $outputJs[] = $js;
             }
         }
    }

    if(sizeof($outputJs) == 0) return;

    $outputJs2 = array();
    foreach($outputJs as $js) {
        $outputJs2[] = $base_uri.'plugins/javascript_libs/modules'.
            (JAVASCRIPT_LIBS_USE_MINIFY == 2 ? '-min' : '') .
            '/'.$js;
    }

    /* Do the actual output of HTML <script> elements. If minify is enabled, 
     * use it to do caching / optimizing. */

    if(JAVASCRIPT_LIBS_USE_MINIFY == 1) {
        echo '<script language="javascript" type="text/javascript" src="'.$base_uri.'plugins/javascript_libs/minify.php?files='.implode(',',$outputJs2).'"></script>';
    } else {
        foreach($outputJs2 as $j) {
            echo '<script language="javascript" type="text/javascript" src="'.$j.'"></script>'."\n";
        }
    }
     
}

/**
 * This is the function that plugins can use, in order to register their 
 * desired libraries for a specific page.
 *
 * @example javascript_libs_register('read_body.php', array('prototype', 'effects'));
 * @param string $page
 * @param array $javascripts
 * @return void
 */
function javascript_libs_register_do($page, $javascripts) {
    global $javascript_libs_hooks;
    foreach($javascripts as $js) {
        if(!isset($javascript_libs_hooks[$page]) || (
         is_array($javascript_libs_hooks[$page]) && !in_array($js, $javascript_libs_hooks[$page]))
        ){
            $javascript_libs_hooks[$page][] = $js;
        }
    }
    //print "<br/>Debug:: after this invocation: "; print_r($javascript_libs_hooks);
}

