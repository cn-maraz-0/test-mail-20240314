<?php
/**
 * Test module: IMAP MyRights test
 *
 * @package squirrelmail
 * @subpackage devhelper_tests
 */

/** Prevent this script from being called stand-alone */
if($PHP_SELF != $base_uri.'plugins/devhelper/devhelper_tests.php?t='.$dh_test) die();

/** Include required IMAP functions */
include_once(SM_PATH . 'functions/imap_general.php');
include_once(SM_PATH . 'plugins/useracl/imap_acl.php');

$myrights = array();
$boxes = sqimap_mailbox_list($imapConnection);
for ($boxnum = 0; $boxnum < count($boxes); $boxnum++) {
   $myrights[$boxes[$boxnum]['formatted']] = sqimap_myrights ($imapConnection,$boxes[$boxnum]['unformatted']);
}

/* ======== Presentation ======== */

echo '<h3>MyRights for all folders of current server</h3>';
echo '<ul>';
foreach($myrights as $f=>$m) {
    echo '<li>'.$f.' = <strong>' . $m  . '</strong></li>';
}
echo '</ul>'

?>
