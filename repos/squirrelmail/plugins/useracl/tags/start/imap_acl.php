<?php
/**
 * useracl.php
 *
 * Copyright (c) 1999-2003 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 */

/**
 * Backend Functions that provide Squirrelmail with the ACL functionality
 */

/**
 * Set ACL to a mailbox.
 *
 * If the acl contains no permissions, then delete acl is performed.
 *
 * @param string $mb_name the mailbox name.
 * @param string $user the user for whom the acl will be set.
 * @param string $acl the acl for the user.
 * @return int
 */

function sqimap_setacl($imap_stream, $mb_name, $user, $acl) {
	
	if(strlen($acl)) {
		$query = "setacl \"$mb_name\" \"$user\" $acl";
		sqimap_run_command($imap_stream, $query, true, $response,
		$message, false, false, false, false);
		return 1;
	} else {
		sqimap_deleteacl($imap_stream, $mb_name, $user);
		return 1;
	}
}
	
/**
 * Delete a user from the mailbox's acl.
 *
 * @param string $mb_name the mailbox name.
 * @param string $user the user for whom the acl will be set.
 */

function sqimap_deleteacl($imap_stream, $mb_name, $user) {
	$query = "deleteacl \"$mb_name\" \"$user\"";

	return sqimap_run_command($imap_stream, $query, true,
	$response, $message, false, false, false, false);

}
	
/**
 * Get a mailbox's ACL.
 *
 * @param string $mb_name the mailbox name.
 * @param array &$out Output in the form ["$user"]["acl_string"]
 */
function sqimap_getacl($imap_stream, $mb_name, &$out) {

	$aclflag=1; $tmp_pos=0;
	$query = "getacl \"$mb_name\"";
	/*
	$ret = sqimap_run_command($imap_stream, $query, true,
	$output, $message, false, false, false, false);
	*/
	
	$re = sqimap_run_command_list ($imap_stream, $query, true, $response, $message, false);

	$output = explode(" ", trim($re[0][0]));
	
	$i=count($output)-1;
	while ($i>3) {
		if (strstr($output[$i],'"')) {
			$i++;
		}
		if (strstr($output[$i-1],'"')) {
			$aclflag=1;
			$lauf=$i-1;
			$spacestring=$output[$lauf];
			$tmp_pos=$i;
			$i=$i-2;
			
			while ($aclflag!=0){
				$spacestring=$output[$i]." ".$spacestring;
				if (strstr($output[$i],'"')) {
					$aclflag=0;
				}
				$i--;
			}
			$spacestring=str_replace("\"","",$spacestring);
			if ($i>2) {
				$ret[$spacestring] = $output[$tmp_pos];
			}
		} else {
			$ret[$output[$i-1]] = $output[$i];
			$i = $i - 2;
		}
	}
	if(!isset($ret))
		$out=array();
	else
		$out = $ret;
	return 0;
}

/**
 * Get "My Rights" for defined mailbox
 * 
 * @param $imap_stream obj The IMAP stream
 * @param $mailbox str Mailbox to get the ACL for
 *
 * FIXME:
 * @ret arr with the structure ["$foldername"]["acl_string"]
 */
function sqimap_myrights($imap_stream, $mailbox) {

	$aclflag=1; $tmp_pos=0;
	
	
	$query = "MYRIGHTS \"$mailbox\"";

	//sqimap_run_command($imap_stream, $query, true, $response,
	//  $message, false, false, false, false);
	  
	fputs ($imap_stream, "a001 MYRIGHTS \"$mailbox\"\r\n");
	$output = sqimap_read_data ($imap_stream, 'a001', true, $result, $message);
	$output = explode(" ", $output[0]);
	
	// print "<BR><PRE>"; print_r($output); print "</PRE>";

	$i = sizeof($output)-2;
	if($i>1) {
		return $output[sizeof($output)-1];
	} else {
		return false;
	}

	print " its $i";
	while ($i>2) {
		print "EP!";
		if (strstr($output[$i],'"')) {
			$i++;
		}
		if (strstr($output[$i-1],'"')) {
			$aclflag=1;
			$lauf=$i-1;
			$spacestring=$output[$lauf];
			$tmp_pos=$i;
			$i=$i-2;
	
			while ($aclflag!=0)     {
				$spacestring=$output[$i]." ".$spacestring;
				if (strstr($output[$i],'"')) {
					$aclflag=0;
				}
				$i--;
			}
	
			$spacestring=str_replace("\"","",$spacestring);
			if ($i>2) {
				$ret[$spacestring] = $output[$tmp_pos];
			}
		} else {
			$ret[$output[$i-1]] = $output[$i];
			$i = $i - 2;
		}
	}
	
	//print "<pre>"; print_r($output); print "</pre>";
	//print_r($ret);
	//return $ret;
	
	if(sizeof($output) > 1) {
		return $output[3];
	} else {
		return false;
	}
	
	/* How to return an array */
	$aclarray[$mailbox] = $output[3]; 
	return $aclarray;
}
	
?>
