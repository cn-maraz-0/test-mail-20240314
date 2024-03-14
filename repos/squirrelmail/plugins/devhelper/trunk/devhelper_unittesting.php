<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * Test Modules for Unit Testing: main Script.
 *
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: devhelper_unittesting.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
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

require_once(SM_PATH .'plugins/devhelper/include/simpletest/unit_tester.php');
require_once(SM_PATH .'plugins/devhelper/include/simpletest/reporter.php');

include_once(SM_PATH . 'functions/imap.php');

sqgetGlobalVar('t', $test, SQ_GET);
sqgetGlobalVar('key', $key, SQ_COOKIE);
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0); 

$devhelper_tests = array(
    'imap_namespace' => array(
        'name' => 'Namespace Test'
    )
);

if(!array_key_exists($test, $devhelper_tests)) {
    $test = '';
}


/* Output starts here */

displayPageHeader("DevHelper - Tests");

echo '<ul>';
foreach($devhelper_tests as $t=>$info) {
    echo '<li>'. ($test==$t ? '<strong>':'') . '<a href="devhelper_unittesting.php?t='.$t.'">'.$info['name'].'</a>'. ($test==$t ? '</strong>':'') . '</li>';
}
echo '</ul>';

if(!empty($test)) {
    require(SM_PATH . 'plugins/devhelper/unit_test_modules/'.$test.'.php');
}

sqimap_logout($imapConnection); 

?>
