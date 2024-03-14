<?php

/**
 * junkfolder_create.php
 *
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@users.sourceforge.net>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Creates Junk folder on the IMAP server.
 * Called from folders.php
 *
 */

/* Path for SquirrelMail required files. */
define('SM_PATH','../../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'plugins/junkfolder/config.php');

/* get globals we may need */
sqgetGlobalVar('key',          $key,           SQ_COOKIE);
sqgetGlobalVar('username',     $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',   $onetimepad,    SQ_SESSION);
sqgetGlobalVar('delimiter',    $delimiter,     SQ_SESSION);
sqgetGlobalVar('subfolder',    $subfolder,     SQ_POST);
sqgetGlobalVar('contain_subs', $contain_subs,  SQ_POST);
/* end of get globals */

$folder_name = $junkfolder_user;

$location = get_location();

/* Rest is copy & paste from src/folder_create.php */

/*
if (substr_count($folder_name, '"') || substr_count($folder_name, "\\") ||
    substr_count($folder_name, $delimiter) || ($folder_name == '')) {
    displayPageHeader($color, 'None');

    plain_error_message(_("Illegal folder name.  Please select a different name.").
        '<BR><A HREF="'.$location.'/src/folders.php">'._("Click here to go back").'</A>.', $color);

    exit;
}
*/

$folder_name = imap_utf7_encode_local($folder_name);

if (isset($contain_subs) && $contain_subs ) {
    $folder_name = $folder_name . $delimiter;
}

if ($folder_prefix && (substr($folder_prefix, -1) != $delimiter)) {
    $folder_prefix = $folder_prefix . $delimiter;
}
if ($folder_prefix && (substr($subfolder, 0, strlen($folder_prefix)) != $folder_prefix)){
    $subfolder_orig = $subfolder;
    $subfolder = $folder_prefix . $subfolder;
} else {
    $subfolder_orig = $subfolder;
}

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

if (trim($subfolder_orig) == '') {
    sqimap_mailbox_create ($imapConnection, $folder_prefix.$folder_name, '');
} else {
    sqimap_mailbox_create ($imapConnection, $subfolder.$delimiter.$folder_name, '');
}

sqimap_logout($imapConnection);

$location = get_location();
header ("Location: ../../src/folders.php?success=create");

?>
