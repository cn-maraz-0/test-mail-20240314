<?php
/**
 * This avelsieve-utils admin batch script can update users' sieve scripts, by using 
 *
 * NOTE:
 * To run this, edit avelsieve file:
 * ../include/support.inc.php
 * and comment out the line:
 * //include_once(SM_PATH . 'functions/identity.php');
 *
 * When you are done, put it back for normal avelsieve operation :-p
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package avelsieve
 * @subpackage admin-utils
 */

define('SM_PATH','../../../');
//include_once(SM_PATH . 'config/config.php');
include_once(SM_PATH . 'plugins/avelsieve/config/config.php');
include_once(SM_PATH . 'functions/imap_utf7_local.php');
include_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'plugins/avelsieve/utils/utils_global_config.php');
include_once(SM_PATH . 'plugins/avelsieve/include/support.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/html_rulestable.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/sieve.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/spamrule.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/managesieve.lib.php');
include_once(SM_PATH . 'plugins/avelsieve/utils/utils_functions.inc.php');
include_once(SM_PATH . 'plugins/avelsieve/include/constants.inc.php');

/* =============================================================== */
/* Get usernames */
$f = fopen($filename, 'rb');
$contents = fread($f, filesize($filename));
fclose($f);
$users = explode("\n", trim($contents));

$global_count = 0;

global $sieve_capabilities;
$sieve_capabilities = array();

/* Start the batch job! */
$stats = array(
        'no_script' => 0,
        'script_exists' => 0,
        'spam_rule_advanced_exists' => 0,
        'no_avelsieve_script_from_those_who_have_scripts'=>0,
        'added_new_tests' => 0,
        'versions' => array(),
        'users_with_advanced_spam_rule' => array()
);

foreach($users as $username) {
    $global_count++;
    /* Test run for 30 users only */
    /*
    if($global_count > 30) {
            break;
    }
     */

    echo '===== User: '. $username . " =====\n";
    $sieve = new sieve($imapServerAddress, $sieveport, $username, $secret, $proxy_username, $sieve_preferred_sasl_mech, $avelsieve_disabletls);
    if(! $sieve->sieve_login()) {
        print "  Login failed: ".$sieve->error . "\n";
        continue;
    }
    
    $sieve_capabilities = $sieve->capabilities['modules'];

    if(! $sieve->sieve_listscripts()) {
            $stats['no_script']++;
            print " (Note: No script here)\n";
            continue;
    }

    $stats['script_exists']++;
    
    $scripts = $sieve->response;
    if(!in_array($scriptname, $scripts)) {
        $stats['no_avelsieve_script_from_those_who_have_scripts']++;
    }

    /* Now actually we get the script and examine it: */
    $script = $sieve->sieve_getscript($scriptname);
    if($script === false) {
            print ' Error while getting script: '. $sieve->error . "\n";
            continue;
    }
    foreach($sieve->response as $line)  $script .= $line; 


    $rules = avelsieve_extract_rules($script, $scriptinfo);

    /* First some stats, version etc. */
    if(!isset($stats['versions'][$scriptinfo['version']['string']])) {
         $stats['versions'][$scriptinfo['version']['string']] = 0;
    }
    $stats['versions'][$scriptinfo['version']['string']]++;

    /* Now closely examining the rules */

    $write_needed = false;

    /* Massive migration plan policies are described here. */
    /* --------------------------------------------------- */
    for($i=0; $i<sizeof($rules); $i++) {
        if($rules[$i]['type'] == '11') {
            $spam_rule_exists = true;
            if(isset($rules[$i]['advanced']) && $rules[$i]['advanced']) {
                $stats['spam_rule_advanced_exists']++;
                if(!isset($stats['users_with_advanced_spam_rule'][$username])) {
                    $stats['users_with_advanced_spam_rule'][$username] = array();
                }
                $stats['users_with_advanced_spam_rule'][$username][] = $rules[$i];
            }
            
            uoa_migrate_rule_11_new_tests($rules[$i], $username);
            $stats['added_new_tests']++;

            $write_needed = true;
        }
    }
    
    /* --------------------------------------------------- */

    if($write_needed) {
            /* Flag that migration functions have changed rules and we
             * have to rewrite the whole script to the server */
           echo "\n** SCRIPT REWRITE IN PROGRESS **\n"; 
           
           /* Build script */
	       $newscript = makesieverule($rules);
           print "new script created:\n" . stripslashes($newscript) . "\n";

           /* Uncomment this for the production run (to write back scripts */
           /*
           if($sieve->sieve_sendscript($scriptname, stripslashes($newscript))) {
              print "OK SAVING $username : $scriptname \n";
              if(!($sieve->sieve_setactivescript($scriptname))){
                print "Could not set active script.";
              }
           } else {
                print "ERROR - Unable to load script to server.";
           }
            */
    }

    // in the stats, calculate once per set of rules.
    if(isset($spam_rule_exists)) {
        $stats['spam_rule_exists']++;
        unset($spam_rule_exists);
    }
    if(isset($spam_rule_advanced_exists)) {
        $stats['spam_rule_advanced_exists']++;
        unset($spam_rule_advanced_exists);
    }

    echo "\n";
    $sieve->sieve_logout();
}


print "\n\nFinal statistics for $global_count users:";
print_r($stats);


/**
 * SPAM-Rule migrate: 11=>11 with 2 new tests
 *
 *  array(
 *    'type' => '11' (length=2)
 *    'enable' => 1
 *    'junkmail_prune' => 1
 *    'enable_whitelist' => 1
 *    'whitelist_abook' => 1
 *    'junkmail_advanced' => 0
 *    'junkmail_days' => '29' (length=2)
 *    'action' => 7
 *    'tests' => 
 *        array
 *            'Spamhaus.Block.List' => 'SPAM' (length=4)
 *            'SpamCop' => 'SPAM' (length=4)
 *            'Composite.Blocking.List' => 'SPAM' (length=4)
 *            'Sender.Address.Verification' => 'NO_MAILBOX' (length=10)
 *            'FORGED' => 'SPAM' (length=4)
 *            'Policy.Block.List' => 'SPAM' (length=4)
 *            'SORBS.Safe.Aggregate' => 'SPAM' (length=4)
 *            'Exploits.Block.List' => 'SPAM' (length=4)
 *   )
 */
function uoa_migrate_rule_11_new_tests(&$rule, $username) {
    global $avelsieve_rules_settings, $domain;

    //print "previous rule:";
    //print_r($rule);

    $rule['tests']['multi.surbl.org'] = array('SPAM');
    $rule['tests']['multi.uribl.com'] = array('SPAM');

    //print "New rule:";
    //print_r($rule);
}
