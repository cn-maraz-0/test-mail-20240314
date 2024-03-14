<?php
/**
 * Test module: Arbitrary Keywords for messages in IMAP folders
 *
 * @package squirrelmail
 * @subpackage devhelper_tests
 */

/** Prevent this script from being called stand-alone */
if($PHP_SELF != $base_uri.'plugins/devhelper/devhelper_tests.php?t='.$dh_test) die();

/** Include General IMAP functions */
include_once(SM_PATH . 'functions/imap_general.php');

/* Parameters */
$mailbox = 'INBOX';
$myflags = array(
    'set' => array('\\Answered', '$Forwarded', '$Tested', '$Fooed'),
    //'clear' => array('\\Answered', '$Forwarded', '$Tested', '$Fooed', '$Bared')
    'clear' => array()
);
$mymessage_index = 1;

/* Test run */
$mbox_select = sqimap_mailbox_select ($imapConnection, $mailbox);
$msgs_hdr_list = sqimap_get_small_header_list($imapConnection, '1:100', array('From','To','Subject'), array('FLAGS'));

// manually pick a uid
$msg_uid = $msgs_hdr_list[$mymessage_index]['UID'];

echo '<h3>FLAGS</h3>';
dumpr($mbox_select['FLAGS']);

echo '<h3>PERMANENTFLAGS</h3>';
dumpr($mbox_select['PERMANENTFLAGS']);

echo '<h3>Current messages</h3>';
dumpr($msgs_hdr_list);

echo '<h3>Message we picked up:</h3>';
dumpr($msgs_hdr_list[$msg_uid]);


/* Actual test -- Perform changes */

foreach($myflags['set'] as $f) {
    echo "Setting Flag: $f <br/>";
    $result[$f] = sqimap_toggle_flag($imapConnection, array($msg_uid), $f, true, false);
    dumpr($result[$f]);
}
foreach($myflags['clear'] as $f) {
    echo "Clearing Flag: $f <br/>";
    $result[$f] = sqimap_toggle_flag($imapConnection, array($msg_uid), $f, false, false);
    dumpr($result[$f]);
}

// Re-get the resulting message
$msgs_hdr_list_2 = sqimap_get_small_header_list($imapConnection, "$msg_uid", array('From','To','Subject'), array('FLAGS'));

echo '<h3>Message after set of all these flags</h3>';
dumpr($msgs_hdr_list_2[$msg_uid]);

?>
