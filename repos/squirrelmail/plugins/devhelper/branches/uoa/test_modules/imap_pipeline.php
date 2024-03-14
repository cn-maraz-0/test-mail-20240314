<?php
/**
 * Test module: IMAP Pipelining
 *
 * @package squirrelmail
 * @subpackage devhelper_tests
 */

/** Prevent this script from being called stand-alone */
if($PHP_SELF != $base_uri.'plugins/devhelper/devhelper_tests.php?t='.$dh_test) die();

/** Include required IMAP functions */
include_once(SM_PATH . 'functions/imap_general.php');
include_once(SM_PATH . 'functions/imap_mailbox.php');

$boxes = sqimap_mailbox_list($imapConnection);

$tags = array();
$aQuery = array();

/* Test: Multiple STATUSes */
$query = 'STATUS "%s" (MESSAGES UNSEEN RECENT)';
foreach($boxes as $no=>$info) {
	$box_ary[] = $info['unformatted'];
	sqimap_prepare_pipelined_query(sprintf($query, $info['unformatted']),$tag[0],$aQuery,false);
}

/* ========================== */

/* Alternative pipelined queries */

/* ========================== */

/* Test: SELECT */
/*
$query = 'SELECT "%s"';
foreach($boxes as $no=>$info) {
	sqimap_prepare_pipelined_query(sprintf($query, $info['unformatted']),$tag[0],$aQuery,false);
}
 */

/* ========================== */

/* Test: Multiple CREATEs and SUBSCRIBEs */
/*
sqimap_prepare_pipelined_query('CREATE "Stuff'.$i.'"',$tag[0],$aQuery,false);
for($i=1; $i<100; $i++) {
	sqimap_prepare_pipelined_query('CREATE "Stuff/Mailbox_'.$i.'"',$tag[0],$aQuery,false);
}
for($i=1; $i<100; $i++) {
	sqimap_prepare_pipelined_query('SUBSCRIBE "Stuff/Mailbox_'.$i.'"',$tag[0],$aQuery,false);
}
*/

/* ========================== */

/* Test: Execute pipelined queries */
$aResponse = sqimap_run_pipelined_command ($imapConnection, $aQuery, false, $aServerResponse, $aServerMessage);

print '<h2>Queries issued:</h2>';
dumpr($aQuery);
print '<h2>Responses:</h2>';
dumpr($aResponse);

?>
