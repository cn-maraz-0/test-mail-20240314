<?php
/**
 * Statistics for Sieve scripts in a ManageSieve server, and migration
 * for new SPAM rule: converts spam rule #10 to #11.
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
        'rule_10_exists' => 0,
        'rule_11_exists' => 0,
        'spam_rule_advanced_exists' => 0,
        'no_avelsieve_script_from_those_who_have_scripts'=>0,
        'versions' => array(),
        'users_with_advanced_spam_rule' => array()
);

$ldap = ldap_connect($ldap_server);
$bind_result = ldap_bind( $ldap, $ldap_bind, $ldap_secret);
if(!$bind_result) {
   print "Error: Could not bind to ldap!";
   exit;
}


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
    
    $user_whitelist = array(); // Gather all whitelist data here. (e.g. from multiple spam rules)

    for($i=0; $i<sizeof($rules); $i++) {

        // 1) 10->11 ..........
        if($rules[$i]['type'] == '10') {
            $stats['rule_10_exists']++;
            print "Previous spam rule:"; print_r($rules[$i]);
            uoa_migrate_spamrule_to_junkmail($rules[$i], $username, $new_whitelist);
            $write_needed = true;

            if(!empty($new_whitelist)) {
                $user_whitelist = array_merge($user_whitelist, $new_whitelist); 
            }
            $new_whitelist = array();
        }
        

        // 2) 11 flat tests -> 11 with array tests
        if($rules[$i]['type'] == '11') {
            print "Previous junk rule:"; print_r($rules[$i]);
            $stats['rule_11_exists']++;
            $spam_rule_exists = true;
            uoa_fix_tests($rules[$i], $username);
            $write_needed = true;
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
    if(isset($spam_rule_advanced_exists)) {
        $stats['spam_rule_advanced_exists']++;
        unset($spam_rule_advanced_exists);
    }

    echo "\n";
    $sieve->sieve_logout();
}


ldap_close($ldap);

print "\n\nFinal statistics for $global_count users:";
print_r($stats);


/* ==================================================================== */

/**
 * fix tests
 */
function uoa_fix_tests(&$rule, $username) {
    global $avelsieve_rules_settings, $domain, $ldap;

    if(!isset($rule['advanced']) || (isset($rule['advanced']) && !$rule['advanced'])) {
        $advanced = false;
    } else {
        $advanced = true;
    }
    if(isset($rule['junkmail_advanced']) && $rule['junkmail_advanced'] == 1) {
        $advanced = true;
    }

    if(isset($rule['advanced'])) unset($rule['advanced']);
            
    if(!$advanced) {
        // just put the default thingies
        unset($rule['tests']);
        $rule['tests'] = $avelsieve_rules_settings[11]['default_rule']['tests'];

    } else {
        // just convert strings to arrays
        $newtests = array();
        $imok = false;
        foreach($rule['tests'] as $t => $v ) {
            if(is_string($v)) {
                $newtests[$t] = array($v);
            } else {
                // array already -- no conversion needed;
                $imok = true;
            }

        }
        if(!$imok) {
            unset($rule['tests']);
            $rule['tests'] = $newtests;
        }
    }

    if(!$advanced) {
        $rule['junkmail_advanced'] = 0;
    } else {
        $rule['junkmail_advanced'] = 1;
    }

    print "New rule:";
    print_r($rule);
}


/**
 * SPAM-Rule migrate: 10->11
 *
 * <pre>
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
 *  array(
 *       'type' => 11,
 *       'advanced' => 0,
 *       'action' => 7,
 *       'stop' => true
 *   )
 *
 * New:
 *
 *  array(
 *    'type' => '11' (length=2)
 *    'enable' => 1
 *    'junkmail_prune' => 1
 *    'enable_whitelist' => 1
 *    'whitelist_abook' => 1
 *    'junkmail_advanced' => 0
 *    'junkmail_days' => 29
 *    'action' => 7
 *    'tests' => 
 *        array
 *            'Spamhaus.Block.List' => array('SPAM')
 *            'SpamCop' => array('SPAM')
 *            'Composite.Blocking.List' => array('SPAM')
 *            'Sender.Address.Verification' => array('NO_MAILBOX')
 *            'FORGED' => array('SPAM')
 *            'Policy.Block.List' => array('SPAM')
 *            'SORBS.Safe.Aggregate' => array('SPAM')
 *            'Exploits.Block.List' => array('SPAM')
 *   )
 * </pre>
 */
function uoa_migrate_spamrule_to_junkmail(&$rule, $username, &$new_whitelist) {
    global $avelsieve_rules_settings, $domain, $ldap;

    print "previous rule:";
    print_r($rule);
    
    $rule['action'] = 7;
    $rule['enable'] = 1;
    $rule['stop'] = true;
    $rule['type'] = '11';
    $rule['enable_whitelist'] = 1;
    $rule['whitelist_abook'] = 1;

    $rule['junkmail_prune'] = 1;

    if(isset($rule['score'])) unset($rule['score']);

    /* =========== */
    // GET junkmail_days FROM LDAP
    $sr = ldap_search($ldap, $ldap_base, "(uid=$username)", array('junkprune'));
	$info = ldap_get_entries($ldap, $sr);
    print "LDAP lookup result:\n";
    print_r($info);

    if(isset($info[0]['junkprune']) && $info[0]['junkprune']['count'] == 1) {
        $rule['junkmail_days'] = $info[0]['junkprune'][0];
    } else {
        $rule['junkmail_days'] = 7;
    }

    if($rule['junkmail_days'] == 0) {
        $rule['junkmail_prune'] = 0;
    }

    /* =========== */

    if(!isset($rule['advanced']) || (isset($rule['advanced']) && !$rule['advanced'])) {
        $advanced = false;
    } else {
        $advanced = true;
    }

    if(isset($rule['advanced'])) unset($rule['advanced']);
            
    unset($rule['tests']);
    $rule['tests'] = $avelsieve_rules_settings[11]['default_rule']['tests'];

    if(!$advanced) {
        $rule['junkmail_advanced'] = 0;
    } else {
        $rule['junkmail_advanced'] = 1;
    }

    /* ===== Whitelist ======== */
    if(isset($rule['whitelist']) && sizeof($rule['whitelist']) > 0 && !empty($rule['whitelist'][0]['headermatch']) ) {
        $new_whitelist_entries = array();
        foreach($rule['whitelist'] as $idx=>$wl) {
            $new_whitelist_entries[] = $wl['headermatch'];
        }
        unset($rule['whitelist']);
    }
    /* Pass along the new whitelist entries. */

    if(isset($new_whitelist_entries)) {
        $new_whitelist = $new_whitelist_entries;
    }


    print "New rule:";
    print_r($rule);
}
