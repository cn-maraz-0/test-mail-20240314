<?php
/**
 * Quota functions: Communication in IMAP level and business logic.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @author Robin Rainton <robin@rainton.com>
 * @package plugins
 * @subpackage ldapfolderinfo
 * @version $Id: quota.inc.php,v 1.3 2007/08/23 13:54:56 avel Exp $
 */

/**
 * Parse output from quotaroot command.
 *
 * @param $response
 * @param $output
 * @return array
 */
function parse_quotaroot_output($response, $output) {
    $ret = array();
    if (strstr($response, ". NO Mailbox")) {
        $ret["quotaroot"] = "NOT-SET";
        $ret["used"] = "NOT-SET";
        $ret["qmax"] = "NOT-SET";
        return 0;
    } else {
        if(strstr($output[0],'"')){
        // The name of the folder contains blank space
            $first_token_list = split('"', $output[0]);
            $token_list=$token_list = split(" ", $first_token_list[0]);
            $pos=count($token_list)-1;
            for($i=1;$i<count($first_token_list);$i++){
                if(strlen(trim($first_token_list[$i]))){
                    $token_list[$pos]=$first_token_list[$i];
                    $pos++;
                }
            }
        }
        else
            $token_list = split(" ", $output[0]);
        if(count($token_list)!=3){
            $realoutput = str_replace(")", "", $output[1]);
            $mbquotaroot=$token_list[sizeof($token_list)-1];
            $ret["quotaroot"] = trim($mbquotaroot);
            $tok_list = split(" ",$realoutput);
            if(trim($tok_list[sizeof($tok_list)-1])=='('){
                $ret["qmax"]="UNLIMITED";
                $ret["used"]="NOT-SET";
            }
            else{
                $si_used=sizeof($tok_list)-2;
                $si_max=sizeof($tok_list)-1;
                $ret["used"] = str_replace(")","",$tok_list[$si_used]);
                $ret["qmax"] = $tok_list[$si_max];
            }
        }
        else{
            $ret["qmax"]="NOT-SET";
            $ret["quotaroot"]=$mb_name;
        }
    }
    return $ret;
}

/**
 * Get quota root for folder $mailbox
 *
 * @param object $stream imap stream
 * @param string $mailbox Mailbox to check quota for
 * @return int
 * @see constants.inc.php
 */
function sqimap_get_quota_ldapfolderinfo($imap_stream, $mailbox, &$taken, &$total) {

    global $username;
    if(isset($_SESSION['authz']) ){
        $uid = $_SESSION['authz'];
    } else {
        $uid = $username;
    }

    $query = 'GETQUOTAROOT "'.$mailbox.'"';
    $output = sqimap_run_command($imap_stream, $query, true, $response, $message, false, false, false, false);
    $ret = parse_quotaroot_output($response, $output);
            
    /* Compare name by quotaroot output, with name from $mailbox */
    if(strstr($mailbox, 'INBOX')) {
        $mailbox_unformatted = str_replace("INBOX", "", $mailbox);
        if ($ret['quotaroot'] == 'user.'.$uid.$mailbox_unformatted) {
            $return = QUOTA_SET;
        } else {
            $return = QUOTA_DEFINED_IN_PARENT;
        }
    } else { // A shared folder or BB
        if (strtolower($ret['quotaroot']) == strtolower($mailbox)) {
            $return = QUOTA_SET;
        } else {
            $return = QUOTA_DEFINED_IN_PARENT;
        }
    }
    if ($ret['qmax'] == 'NOT-SET') {
        $return = QUOTA_NOT_SET;
    }
                
    $taken = $ret['used'];
    $total = trim($ret['qmax']);
    return $return;
}

