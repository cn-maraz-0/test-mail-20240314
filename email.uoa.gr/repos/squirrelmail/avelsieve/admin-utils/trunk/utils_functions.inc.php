<?php
/**
 * Since we do not include many Squirrelmail files, we redefine
 * some functions in here as minimally as possible.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package avelsieve
 * @subpackage admin-utils
 */

/**
 * Variables replacements
 */
global $plugins;
$plugins = array();

/**
 * Character set - replacement
 */
function set_my_charset() {
        global $default_charset;
        $default_charset = 'UTF-8';
}

/**
 * Hook function - replacement
 */
function do_hook($name, &$args) {
    return;
}

/**
 * getpref function - replacement
 */
function getPref($data_dir, $username, $p) {
    switch($p) {
        case 'trash_folder':
            return 'INBOX.Trash';
            break;

        default:
            echo " NOTICE - getPref returning empty for $p\n";
            return '';
    }
}

