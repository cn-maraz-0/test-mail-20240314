<?php
/**
 * mailnotify.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id: mailnotify.php,v 1.5 2004/08/09 13:07:26 avel Exp $
 *
 * Functions related to mail notification feature.
 */

/**
 * Prepare body of notify message to be sent.
 * @param array $info An array with the recipients information:
 * - user (username of receiver)
 * - permission (ACL granted, e.g lrsp)
 * - mailbox  (mailbox name, M-UTF7-Encoded)
 * - type (change or new)
 * - name (receiver's name, optionally)
 */
function useracl_prepare_notify_message($info) {
	global $useracl_notify_url, $username, $full_name, $acl;

	if(!isset($info['name'])) {
		$info['name'] = '';
	} else {
		$info['name'] = '('.$info['name'].') ';
	}

	if(isset($full_name)) {
		$printname = $username . ' ('.$full_name.')';
	} else {
		$printname = $username;;
	}

	if($info['type'] == 'change' && $info['permission'] == '' ) {
		/* Remove */
		$notify_msg = sprintf("User %s has removed access to his/her folder, %s, from you.",
		  $printname, imap_utf7_decode_local($info['mailbox']));
	
	} elseif($info['type'] == 'change') {
		/* Change */
		$notify_msg = sprintf("User %s has changed your permission to his/her folder, %s, to:",
		  $printname, imap_utf7_decode_local($info['mailbox']));
		foreach($acl as $a=>$in) {
			if(strstr($info['permission'], $in['acl'])) {
				$notify_msg .= "\r\n - ".ucfirst($a);
			}
		}
		$notify_msg .= "\r\n";
	} else {
		/* New */
		$notify_msg = sprintf("User %s has given you the following permissions to his/her folder, %s:",
		  $printname, imap_utf7_decode_local($info['mailbox']) );
		foreach($acl as $a=>$in) {
			if(strstr($info['permission'], $in['acl'])) {
				$notify_msg .= "\r\n - ".ucfirst($a);
			}
		}
		$notify_msg .= "\r\n";
	}
	
	if(strstr($info['permission'], $acl['read']['acl'])) {
		$notify_msg .= "\r\n";
		$notify_msg .= "You can view the contents of that folder at any time, by subscribing to it.";
	}
	
	/*
	if(strstr($acl['post']['acl']), $info['permission']) {
		$notify_msg .= "\r\n";
		$notify_msg .= "You can post to the folder using this mail address: $mbox+$username";
	}
	*/

	if(isset($useracl_notify_url)) {
		$notify_msg .= "\r\n\r\n";
		$notify_msg .= sprintf("For more information about shared folders, please see %s .",
		$useracl_notify_url);
	}

	$notify_msg .= "\r\n\r\n";
	$notify_msg .= "This message has been generated automatically, at the request of the user.";

	return $notify_msg;
}

/**
 * Copy/paste of Squirrelmail's deliverMessage that resides in compose.php
 */
function deliverMessage($composeMessage, $draft=false) {
    global $send_to, $send_to_cc, $send_to_bcc, $mailprio, $subject, $body,
           $username, $popuser, $usernamedata, $identity, $data_dir,
           $request_mdn, $request_dr, $default_charset, $color, $useSendmail,
           $domain, $action, $default_move_to_sent, $move_to_sent;
    global $imapServerAddress, $imapPort, $sent_folder, $key;

    /* some browsers replace <space> by nonbreaking spaces &nbsp;
       by replacing them back to spaces addressparsing works */
    /* FIXME: How to handle in case of other charsets where "\240"
       is not a non breaking space ??? */

    $send_to = str_replace("\240",' ',$send_to);
    $send_to_cc = str_replace("\240",' ',$send_to_cc);
    $send_to_bcc = str_replace("\240",' ',$send_to_bcc);

    $rfc822_header = $composeMessage->rfc822_header;

    $rfc822_header->to = $rfc822_header->parseAddress($send_to,true, array(), '', $domain);
    $rfc822_header->cc = $rfc822_header->parseAddress($send_to_cc,true,array(), '',$domain);
    $rfc822_header->bcc = $rfc822_header->parseAddress($send_to_bcc,true, array(), '',$domain);
    $rfc822_header->priority = $mailprio;
    $rfc822_header->subject = $subject;
    $special_encoding='';
    if (strtolower($default_charset) == 'iso-2022-jp') {
        if (mb_detect_encoding($body) == 'ASCII') {
            $special_encoding = '8bit';
        } else {
            $body = mb_convert_encoding($body, 'JIS');
            $special_encoding = '7bit';
        }
    }
    $composeMessage->setBody($body);

    if (ereg("^([^@%/]+)[@%/](.+)$", $username, $usernamedata)) {
       $popuser = $usernamedata[1];
       $domain  = $usernamedata[2];
       unset($usernamedata);
    } else {
       $popuser = $username;
    }
    $reply_to = '';
    if (isset($identity) && $identity != 'default') {
        $from_mail = getPref($data_dir, $username, 'email_address' . $identity);
        $full_name = getPref($data_dir, $username, 'full_name' . $identity);
        $reply_to = getPref($data_dir, $username, 'reply_to' . $identity);
    } else {
        $from_mail = getPref($data_dir, $username, 'email_address');
        $full_name = getPref($data_dir, $username, 'full_name');
        $reply_to = getPref($data_dir, $username,'reply_to');
    }
    if (!$from_mail) {
       $from_mail = "$popuser@$domain";
    }

    $rfc822_header->from = $rfc822_header->parseAddress($from_mail,true);
    if ($full_name) {
        $from = $rfc822_header->from[0];
        if (!$from->host) $from->host = $domain;
        $full_name_encoded = encodeHeader($full_name);
        if ($full_name_encoded != $full_name) {
            $from_addr = $full_name_encoded .' <'.$from->mailbox.'@'.$from->host.'>';
        } else {
            $from_addr = '"'.$full_name .'" <'.$from->mailbox.'@'.$from->host.'>';
        }
        $rfc822_header->from = $rfc822_header->parseAddress($from_addr,true);
    }
    if ($reply_to) {
       $rfc822_header->reply_to = $rfc822_header->parseAddress($reply_to,true);
    }
    /* Receipt: On Read */
    if (isset($request_mdn) && $request_mdn) {
       $rfc822_header->dnt = $rfc822_header->parseAddress($from_mail,true);
    }
    /* Receipt: On Delivery */
    if (isset($request_dr) && $request_dr) {
       $rfc822_header->more_headers['Return-Receipt-To'] = $from_mail;
    }
    /* multipart messages */
    if (count($composeMessage->entities)) {
        $message_body = new Message();
        $message_body->body_part = $composeMessage->body_part;
        $composeMessage->body_part = '';
        $mime_header = new MessageHeader;
        $mime_header->type0 = 'text';
        $mime_header->type1 = 'plain';
        if ($special_encoding) {
            $mime_header->encoding = $special_encoding;
        } else {
            $mime_header->encoding = '8bit';
        }
        if ($default_charset) {
            $mime_header->parameters['charset'] = $default_charset;
        }
        $message_body->mime_header = $mime_header;
        array_unshift($composeMessage->entities, $message_body);
        $content_type = new ContentType('multipart/mixed');
    } else {
        $content_type = new ContentType('text/plain');
        if ($special_encoding) {
            $rfc822_header->encoding = $special_encoding;
        } else {
            $rfc822_header->encoding = '8bit';
        }
        if ($default_charset) {
            $content_type->properties['charset']=$default_charset;
	}
    }

    $rfc822_header->content_type = $content_type;
    $composeMessage->rfc822_header = $rfc822_header;

    /* Here you can modify the message structure just before we hand
       it over to deliver */
    $hookReturn = do_hook('compose_send', $composeMessage);
    /* Get any changes made by plugins to $composeMessage. */
    if ( is_object($hookReturn[1]) ) {
        $composeMessage = $hookReturn[1];
    }

    if (!$useSendmail && !$draft) {
        require_once(SM_PATH . 'class/deliver/Deliver_SMTP.class.php');
        $deliver = new Deliver_SMTP();
        global $smtpServerAddress, $smtpPort, $pop_before_smtp, $smtp_auth_mech;

        if ($smtp_auth_mech == 'none') {
                $user = '';
                $pass = '';
        } else {
                global $key, $onetimepad;
                $user = $username;
                $pass = OneTimePadDecrypt($key, $onetimepad);
        }

        $authPop = (isset($pop_before_smtp) && $pop_before_smtp) ? true : false;
        $stream = $deliver->initStream($composeMessage,$domain,0,
                          $smtpServerAddress, $smtpPort, $user, $pass, $authPop);
    } elseif (!$draft) {
       require_once(SM_PATH . 'class/deliver/Deliver_SendMail.class.php');
       global $sendmail_path;
       $deliver = new Deliver_SendMail();
       $stream = $deliver->initStream($composeMessage,$sendmail_path);
    } elseif ($draft) {
       global $draft_folder;
       require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
       $imap_stream = sqimap_login($username, $key, $imapServerAddress,
                      $imapPort, 0);
       if (sqimap_mailbox_exists ($imap_stream, $draft_folder)) {
           require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
           $imap_deliver = new Deliver_IMAP();
           $length = $imap_deliver->mail($composeMessage);
           sqimap_append ($imap_stream, $draft_folder, $length);
           $imap_deliver->mail($composeMessage, $imap_stream);
               sqimap_append_done ($imap_stream, $draft_folder);
           sqimap_logout($imap_stream);
           unset ($imap_deliver);
           return $length;
        } else {
           $msg  = '<br>Error: '._("Draft folder")." $draft_folder" . ' does not exist.';
           plain_error_message($msg, $color);
           return false;
        }
    }
    $succes = false;
    if ($stream) {
        $length = $deliver->mail($composeMessage, $stream);
        $succes = $deliver->finalizeStream($stream);
    }
    if (!$succes) {
        $msg  = $deliver->dlv_msg . '<br>' .
                _("Server replied: ") . $deliver->dlv_ret_nr . ' '.
                $deliver->dlv_server_msg;
        plain_error_message($msg, $color);
    } else {
        unset ($deliver);
        $move_to_sent = getPref($data_dir,$username,'move_to_sent');
        $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

        /* Move to sent code */
        if (isset($default_move_to_sent) && ($default_move_to_sent != 0)) {
            $svr_allow_sent = true;
        } else {
            $svr_allow_sent = false;
        }

        if (isset($sent_folder) && (($sent_folder != '') || ($sent_folder != 'none'))
           && sqimap_mailbox_exists( $imap_stream, $sent_folder)) {
            $fld_sent = true;
        } else {
            $fld_sent = false;
        }

        if ((isset($move_to_sent) && ($move_to_sent != 0)) || (!isset($move_to_sent))) {
            $lcl_allow_sent = true;
        } else {
            $lcl_allow_sent = false;
        }

        if (($fld_sent && $svr_allow_sent && !$lcl_allow_sent) || ($fld_sent && $lcl_allow_sent)) {
            sqimap_append ($imap_stream, $sent_folder, $length);
            require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
            $imap_deliver = new Deliver_IMAP();
            $imap_deliver->mail($composeMessage, $imap_stream);
            sqimap_append_done ($imap_stream, $sent_folder);
            unset ($imap_deliver);
        }
        global $passed_id, $mailbox, $action;
        // ClearAttachments($composeMessage);
        if ($action == 'reply' || $action == 'reply_all') {
            sqimap_mailbox_select ($imap_stream, $mailbox);
            sqimap_messages_flag ($imap_stream, $passed_id, $passed_id, 'Answered', false);
        }
            sqimap_logout($imap_stream);
    }
    return $succes;
}

?>
