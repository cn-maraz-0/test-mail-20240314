<?php
/**
 * Autocomplete for Squirrelmail
 *
 * @package plugins
 * @subpackage javascript_autocomplete
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

/** Squirrelmail path */
define("SM_PATH",'../../');
require_once(SM_PATH . 'include/validate.php');
include_once (SM_PATH . 'functions/addressbook.php');
include_once (SM_PATH . 'functions/i18n.php');
include_once (SM_PATH . 'functions/abook_ldap_server.php');
include_once (SM_PATH . 'plugins/directory/include/eduorg.inc.php');

if(!isset($_GET['q'])) {
    exit;
}
$q = $_GET['q'];

set_my_charset();

global $language, $languages, $data_dir, $username;
$language = getPref($data_dir, $username, 'language');
if(!isset($language)) {
	$language = getPref($data_dir, $username, 'language');
}
$ldq_lang = substr($language, 0, 2);
$charset = $languages[$language]['CHARSET'];

global $plugins, $orgs;
if(in_array('directory', $plugins)) {
    sq_bindtextdomain('directory', SM_PATH . 'plugins/directory/locale');
    textdomain ('directory');
    include_once(SM_PATH . 'plugins/directory/schemas/main.php');
    include_once(SM_PATH . 'plugins/directory/schemas/eduorg.php');
    include_once(SM_PATH . 'plugins/directory/schemas/uoa.php');
    include_once(SM_PATH . 'plugins/directory/config.php');
    include_once(SM_PATH . 'plugins/directory/include/eduorg.inc.php');
    include_once(SM_PATH . 'plugins/directory/include/functions.php');
    textdomain ('squirrelmail');
    cache_orgunitdns();
    $orgs = $_SESSION['orgs'];
}



header('Cache-Control: no-cache');
header('Pragma: no-cache');

$json=array();
$maxresults = 12;

// ============== 1) Local addressbook =======================

$abook = addressbook_init(true, true);
$abookentries = $abook->backends[1]->search($q, 2, false);
foreach($abookentries as $entry) {
    if(!isset($entry['email'])) continue;

    if(strpos($entry['email'], ',')) {
        // many email addresses; split them.
        // suboptimal atm ,but gets the job somewhat done.
        $splits = preg_split('/,/', $entry['email']);
        $json[] = array(
            'name'=> $entry['firstname']." ".$entry['lastname'] . ' ' . sprintf( _("(%s recipient addresses)"), count($splits)),
            'value' => $entry['email'],
            'src' => 'pab'
        );
    } else {

        $json[] = array(
            'name'=> $entry['firstname']." ".$entry['lastname'] . " &lt;".$entry['email']."&gt;",
            'value' => $entry['firstname']." ".$entry['lastname'] . " &lt;".$entry['email']."&gt;",
            'src' => 'pab'
        );
    }
}

// ============== 2) Directories =======================

global $abook, $lattributes;
$lattributes = array('givenname', 'sn', 'mail', 'mailalternateaddress', 'cn', 'edupersonprimaryorgunitdn');

// Figure out my relevant department / faculty dn's. This is done only in the 
// first time and is saved in the session.
if(false &&  isset($_SESSION['ldapuserinfo']['relevant_org_unit_dns'])) {
    $relevant_org_unit_dns = $_SESSION['ldapuserinfo']['relevant_org_unit_dns'];
} else {
    $relevant_org_unit_dns = array();

    if(isset($_SESSION['ldapuserinfo']['edupersonprimaryorgunitdn'])) {
        $edupersonprimaryorgunitdn = $_SESSION['ldapuserinfo']['edupersonprimaryorgunitdn'];
        $edupersonprimaryaffiliation = $_SESSION['ldapuserinfo']['edupersonprimaryaffiliation'];

        if($edupersonprimaryaffiliation == 'faculty') {
            $faculty_found = false;

            $tmpdn = $edupersonprimaryorgunitdn;
            $max_iterations = 5; // failsafe

            while($faculty_found === false) {
                if($max_iterations-- == 0) break;

                if(!isset($orgs[$tmpdn]['superior']) || empty($orgs[$tmpdn]['superior'])) {
                    break;
                }

               $tmpdn = $orgs[$edupersonprimaryorgunitdn]['superior']['dn'];

               if(in_array($orgs[$tmpdn]['struct'], array('Τμήμα', 'Faculty'))) {
                   $faculty_found = true;
                   $faculty_dn = $tmpdn;
               }
            }

            if($faculty_found) {
                directory_find_inferior(array($faculty_dn), $relevant_org_unit_dns);
            }

        } else {
            $relevant_org_unit_dns[] = $edupersonprimaryorgunitdn; 
        }
    }
    $_SESSION['ldapuserinfo']['relevant_org_unit_dns'] = $relevant_org_unit_dns;
}

if(!empty($relevant_org_unit_dns)) {
    if(sizeof($relevant_org_unit_dns) > 1) {
        $relevant_orgs_filter = '(|(edupersonprimaryorgunitdn='.implode(')(edupersonprimaryorgunitdn=',$relevant_org_unit_dns).'))';
    } else {
        $relevant_orgs_filter = '(edupersonprimaryorgunitdn='.$relevant_org_unit_dns[0].')';
    }
    $negative_relevant_orgs_filter = '(!'.$relevant_orgs_filter.')'; 
} else {
    $relevant_orgs_filter = '';
    $negative_relevant_orgs_filter = '';
}

$q2 = str_replace(array('\\','*','(',')','\x00'), array('\5c','\2a','\28','\29','\00'), $q);

// The standard declarations to be ANDed in the filter
$filter_and = '(mail=*)(!(uoaprivateinternal=mail))';
//$filter_and = '(mail=*)';

$filters = array(
    // 1) non-students from my department
    "(&(|(cn=$q2*)(sn=$q2*)(mail=$q2*)(mailalternateaddress=$q2*))$filter_and(!(edupersonprimaryaffiliation=student))".
        $relevant_orgs_filter . ")",

    // 2) non-students from the rest.
    $filter = "(&(|(cn=$q2*)(sn=$q2*)(mail=$q2*)(mailalternateaddress=$q2*))$filter_and(!(edupersonprimaryaffiliation=student))".
        $negative_relevant_orgs_filter . ")",

    // 3) students from my department.
    $filter = "(&(|(cn=$q2*)(sn=$q2*)(mail=$q2*)(mailalternateaddress=$q2*))$filter_and(edupersonprimaryaffiliation=student)".
        $relevant_orgs_filter . ")",

    // 4) students from the rest.
    $filter = "(&(|(cn=$q2*)(sn=$q2*)(mail=$q2*)(mailalternateaddress=$q2*))$filter_and(edupersonprimaryaffiliation=student)".
        $negative_relevant_orgs_filter . ")",
);

// Actual search performed now:

$maxrows = $maxresults - sizeof($json);

foreach($ldap_server as $server=>$parms) {
	$abook = new abook_ldap_server($parms);
    if(!$abook->open()) continue;
    
    foreach($filters as $filter) {
        $json2 = abook_search_with_filter($q, $filter, $maxrows);
        foreach($json2 as $res) {
            $json[] = $res;
        }
        $maxrows = $maxrows - sizeof($json);
        if($maxrows <= 0 ) break 2;
    }
    ldap_close($abook->linkid);
}
echo json_encode($json);
exit;



/* Function definitions follow */

function abook_search_with_filter(&$q, $filter, $maxrows) {

    global $abook, $lattributes, $orgs;
    $json = array();
    
    $timeout = 5; //Do not allow on the fly queries to last longer than 30 seconds
	@set_time_limit($timeout+5);

    if(!($lsearch = @ldap_search($abook->linkid, $abook->basedn, $filter, $lattributes, 0, $maxrows, $timeout))) {
        continue;
    }
    mb_internal_encoding("UTF-8");
	$entries = @ldap_get_entries($abook->linkid, $lsearch);
	for($i = 0, $count = 0; $i < $entries['count'] && $count < $maxrows; $i++) {
        // Matched: cn or cn;lang-el ?
        if(isset($entries[$i]['cn']) && mb_stripos($entries[$i]['cn'][0], $q) !== false) {
            $matched_name = 'cn';
        }
        if(isset($entries[$i]['cn;lang-el']) && mb_stripos($entries[$i]['cn;lang-el'][0], $q) !== false) {
            $matched_name = 'cn;lang-el';
        }
        
        // Matched: mail or mailalternateaddress?
        if(isset($entries[$i]['mail']) && stripos($entries[$i]['mail'][0], $q) !== false) {
            $matched_email = $entries[$i]['mail'][0];

        } elseif(isset($entries[$i]['mailalternateaddress'])) {
            for($j=0; $j<$entries[$i]['mailalternateaddress']['count']; $j++) {
                if(stripos($entries[$i]['mailalternateaddress'][$j], $q) !== false) {
                    $matched_email = $entries[$i]['mailalternateaddress'][$j];
                    break;
                }
            }
        }

        if(isset($matched_name) || !isset($matched_email)) {
            $matched_email = $entries[$i]['mail'][0];
        }

        if(!isset($matched_name)) {
            $matched_name = 'cn';
        }
        
        // Org
        $org = '';
        if(isset($orgs) && isset($entries[$i]['edupersonprimaryorgunitdn']) &&
           isset($orgs[strtolower($entries[$i]['edupersonprimaryorgunitdn'][0])])) {
               $myorgdn = strtolower($entries[$i]['edupersonprimaryorgunitdn'][0]);
                $org = $orgs[$myorgdn]['struct'] .  ' ' . $orgs[$myorgdn]['text'];  
        }

        $json[] = array(
            'name'=> htmlspecialchars($entries[$i][$matched_name][0] . " <".$matched_email.">"),
            'value' => htmlspecialchars($entries[$i][$matched_name][0] . " <".$matched_email.">"),
            'src' => 'directory',
            'org' => $org 
        );
        unset($matched_attribute);
        unset($matched_email);
    }
    return $json;
}

