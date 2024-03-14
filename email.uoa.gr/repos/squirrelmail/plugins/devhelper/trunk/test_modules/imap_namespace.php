<?php
/**
 * Test module: IMAP Namespace test
 *
 * @package squirrelmail
 * @subpackage devhelper_tests
 */

/** Prevent this script from being called stand-alone */
if($PHP_SELF != $base_uri.'plugins/devhelper/devhelper_tests.php?t='.$dh_test) die();

/** Include required IMAP functions */
include_once(SM_PATH . 'functions/imap_general.php');

/** Tests definition */
$tests = array(
    'NAMESPACE (("INBOX." ".")) (("user." ".") ("otheruser." "/")) (("" "."))',
    'NAMESPACE NIL (("user." ".") ("otheruser." "/")) (("" "."))',
    'NAMESPACE NIL (("user." ".") ("otheruser." "/") ("niaou." ".")) NIL',
    'NAMESPACE (("Special \" \\\(characters" ".")) (("user." ".") ("Other\\Users" \\\"\"" "/") ("more users." ".")) (("" "."))',
    'NAMESPACE (("" "/")("#mhinbox" NIL)("#mh/" "/")) (("/" "/")) (("#shared/" "/")("#ftp/" "/")("#news." ".")("#public/" "/")))', // UWash
    'NAMESPACE (("" "/")) NIL (("Shared Folders/" "/")))' // Isode M-Box
);

/** Tests output */

$namespace = sqimap_get_namespace($imapConnection);
echo '<h3>Namespace for current server</h3>'; dumpr($namespace);

foreach($tests as $t) {
        echo '<h3>Test</h3> <p>Input: <tt>'.$t.'</tt>'; dumpr(sqimap_parse_namespace($t));
}

?>
