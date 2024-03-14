<?php
/**
 * Statistics for Sieve scripts in a ManageSieve server, and migration
 * for new SPAM rule: converts spam rule #10 to #11.
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
        'versions' => array(),
        'users_with_advanced_spam_rule' => array()
);

foreach($users as $username) {
    $global_count++;
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

    $user_whitelist = array(); // Gather all whitelist data here. (e.g. from multiple spam rules)

    for($i=0; $i<sizeof($rules); $i++) {
        if($rules[$i]['type'] == '10' || $rules[$i]['type'] == 'spamrule') {
            $spam_rule_exists = true;
            if(isset($rules[$i]['advanced']) && $rules[$i]['advanced']) {
                $stats['spam_rule_advanced_exists']++;
                if(!isset($stats['users_with_advanced_spam_rule'][$username])) {
                    $stats['users_with_advanced_spam_rule'][$username] = array();
                }
                $stats['users_with_advanced_spam_rule'][$username][] = $rules[$i];
            }
            uoa_migrate_spamrule($rules[$i], $new_whitelist);
            $write_needed = true;
            if(!empty($new_whitelist)) {
                $user_whitelist = array_merge($user_whitelist, $new_whitelist); 
            }
            $new_whitelist = array();
        }
    }
    
    /* --------------------------------------------------- */

    if($write_needed) {
            /* Flag that migration functions have changed rules and we
             * have to rewrite the whole script to the server */
           echo "\n** SCRIPT REWRITE IN PROGRESS **\n"; 
           
           /* New whitelist? */
           if(!empty($user_whitelist)) {
                /** WRITE NEW WHITELIST */
                $wl_rule = array();
                $wl_rule['type'] = 12;
                $wl_rule['whitelist'] = $user_whitelist;

                echo "\n** NEW WHITELIST CREATED **\n"; 
                print_r($wl_rule);

                array_unshift($rules, $wl_rule);
           }
           print_r($rules);

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
 * SPAM-Rule migrate: 10->11
 *
 * Old:
 *
 * (
 *   [type] => 10
 *   [tests] => Array
 *       (
 *            [0] => Open.Relay.DataBase
 *            [1] => Spamhaus.Block.List
 *            [2] => SpamCop
 *            [3] => Composite.Blocking.List
 *            [4] => FORGED
 *       )
 *
 *   [score] => 10
 *   [action] => junk
 *   [stop] => 1
 * )
 *
 * New:
 *
 *  array(
 *       'type' => 11,
 *       'advanced' => 0,
 *       'tests' => 
 *           array(
 *               'Spamhaus.Block.List' => 'SPAM',
 *               'SpamCop' => 'SPAM',
 *               'Composite.Blocking.List' => 'SPAM',
 *               'Sender.Address.Verification' => 'NO_MAILBOX',
 *               'FORGED' => 'SPAM'
 *           ),
 *       'action' => 7,
 *       'stop' => true
 *   )
 */
function uoa_migrate_spamrule(&$rule, &$new_whitelist) {
    global $avelsieve_rules_settings;

    print "previous rule:";
    print_r($rule);

    $rule['type'] = '11';
    if(!isset($rule['advanced'])) {
        // Simple case...
        $rule['tests'] = $avelsieve_rules_settings[11]['default_rule']['tests'];
        $rule['action'] = 7;
        $rule['advanced'] = 0;
        $rule['stop'] = true;

    } else {
        // Advanced - a bit more complicated.
        $newtests = array();
        foreach($rule['tests'] as $tkey=>$t) {
            $newtests[$t] = 'SPAM';
        }
        // add this explicitly
        $newtests['Sender.Address.Verification'] = 'NO_MAILBOX';

        // Now put the tests...
        unset($rule['tests']);
        $rule['tests'] = $newtests;
        unset($newtests);


        if(isset($rule['whitelist']) && sizeof($rule['whitelist']) > 0 && !empty($rule['whitelist'][0]['headermatch']) ) {
            $new_whitelist_entries = array();
            foreach($rule['whitelist'] as $idx=>$wl) {
                $new_whitelist_entries[] = $wl['headermatch'];
            }
            unset($rule['whitelist']);
        }

        switch($rule['action']) {
            case 'trash':
                $rule['action'] = 8;
                break;

            case 'discard':
                $rule['action'] = 2;
                break;

            case 'junk':
            default:
                $rule['action'] = 7;
                break;
        }

        // 'stop' remains the same.
        /*
        if(isset($rule['stop']) && $rule['stop']) {
        }
         */

        /* Pass along the new whitelist entries. */

        if(isset($new_whitelist_entries)) {
            $new_whitelist = $new_whitelist_entries;
        }

    }

    unset($rule['score']);

    print "New rule:";
    print_r($rule);

}
