<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * @copyright &copy; 2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: devhelper_toolbox.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
 * @package plugins
 * @subpackage devhelper
 */

/** Includes */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'include/load_prefs.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/date.php');
}

include_once(SM_PATH . 'plugins/devhelper/config.php');
include_once(SM_PATH . 'plugins/devhelper/include/dumpr.php');
include_once(SM_PATH . 'plugins/devhelper/include/functions.inc.php');

$SELF = $PHP_SELF;

/* ============================ ========================== */

displayPageHeader($color, '');

print '<h2>All Prefs follow:</h2>';

echo '<ul>';
foreach($ldap_attributes as $ldapAttr => $key) {
    echo '<li>'.$key.': '. getPref($data_dir, $username, $key, ' <small>unset</small> ')  .'</li>';
}


echo '</ul>';
