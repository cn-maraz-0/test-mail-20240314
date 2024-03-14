<?php
/*
 * User-friendly interface to SIEVE server-side mail filtering.
 * Plugin for Squirrelmail 1.4+
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2005-2007 The SquirrelMail Project Team, Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage imapmetadata
 */
   
/**
 * Register Plugin
 * @return void
 */
function squirrelmail_plugin_init_imapmetadata() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['imapmetadata'] = 'imapmetadata_menuline';
}

/**
 * Display menuline link
 * @return void
 */
function imapmetadata_menuline() {
    /*
    bindtextdomain('imapmetadata', SM_PATH . 'plugins/imapmetadata/locale');
    textdomain ('imapmetadata');
    */
        
    displayInternalLink('plugins/imapmetadata/annotate_tests.php',_("Metadata"));
    echo "&nbsp;&nbsp\n";

    /* 
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain ('squirrelmail');
    */
}

/**
 * Squirrelmail plugin information
 * @return array
 */
function imapmetadata_info() {
    return array(
        'version' => '0.3svn',
        'requirements' => 'An IMAP server that supports imapmetadata / METADATA IMAP Extension.'
    );
}

/**
 * Versioning information
 * @return string
 */
function imapmetadata_version() {
    $info = imapmetadata_info();
    return $info['version'];
}

