<?php
/**
 * Unit Test module: IMAP Namespace test
 *
 * @package squirrelmail
 * @subpackage devhelper_unit_tests
 */
include_once(SM_PATH . 'functions/imap_general.php');

$namespace_tests = array(
 0 => array(
    'input' => 'NAMESPACE (("INBOX." ".")) (("user." ".") ("otheruser." "/")) (("" "."))',
    'output' => array ( 'personal' => array ( 0 => array ( 'prefix' => 'INBOX.', 'delimiter' => '.', ), ), 'users' => array ( 0 => array ( 'prefix' => 'user.', 'delimiter' => '.', ), 1 => array ( 'prefix' => 'otheruser.', 'delimiter' => '/', ), ), 'shared' => array ( 0 => array ( 'prefix' => '', 'delimiter' => '.')))
    )
);

class smTestNamespaceParsing extends UnitTestCase {
    function smTestNamespaceParsing($ns, $result) {
        global $imapConnection;
        $this->imapConnection = $imapConnection;
        $this->ns = $ns;
        $this->result = $result;
    }

    function testNamespaceParsing() {
        $namespace = sqimap_parse_namespace($this->ns);
        foreach($namespace as $n => $info) {
            foreach($info as $no => $data) {
                // 0=> array('delimiter','prefix')
                $this->assertIdentical($data['delimiter'], $this->result[$n][$no]['delimiter'], "$n::$no::Delimiter");
                $this->assertIdentical($data['prefix'], $this->result[$n][$no]['prefix'], "$n::$no::Prefix");
            }
        }
    }
}

foreach($namespace_tests as $a) {
    $test = &new smTestNamespaceParsing($a['input'], $a['output']);
    $test->run(new HtmlReporter());
}

?>
