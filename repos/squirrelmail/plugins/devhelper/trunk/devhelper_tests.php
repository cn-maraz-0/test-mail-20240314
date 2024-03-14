<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * Test Modules Main Script.
 *
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: devhelper_tests.php,v 1.2 2006/11/16 12:55:24 avel Exp $
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

include_once(SM_PATH . 'functions/imap.php');
include_once(SM_PATH . 'plugins/devhelper/config.php');
include_once(SM_PATH . 'plugins/devhelper/include/dumpr.php');
include_once(SM_PATH . 'plugins/devhelper/include/functions.inc.php');

sqgetGlobalVar('t', $dh_test, SQ_GET);
sqgetGlobalVar('key', $key, SQ_COOKIE);
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0); 

$devhelper_tests = array(
    'imap_namespace' => array(
        'name' => 'Namespace Test'
    ),
    'imap_pipeline' => array(
        'name' => 'IMAP Command Pipeline Test'
    ),
    'imap_arbitrary_keywords' => array(
        'name' => 'IMAP Arbitrary Keywords Test'
    ),
    'imap_myrights' => array(
        'name' => 'IMAP MyRights',
        'plugins' => array('useracl')
    )
);

if(!array_key_exists($dh_test, $devhelper_tests)) {
    $dh_test = '';
}


/* Output starts here */

displayPageHeader("DevHelper - Tests");

echo '<ul>';
foreach($devhelper_tests as $t=>$info) {
    echo '<li>'. ($dh_test==$t ? '<strong>':'') . '<a href="devhelper_tests.php?t='.$t.'">'.$info['name'].'</a>'. ($dh_test==$t ? '</strong>':'') . '</li>';
}
echo '</ul>';

if(!empty($dh_test)) {
    require(SM_PATH . 'plugins/devhelper/test_modules/'.$dh_test.'.php');
}

sqimap_logout($imapConnection); 

?>
