<?php
/**
 *Junk Folder Plugin for Squirrelmail - Configuration
 */

global $junkfolder_user, $junkfolder_useimage, $junkfolder_days,
$junkfolder_autocreate;

/**
 * Folder that will be regarded as the user's special Junk Folder.
 * @var string
 */
$junkfolder_user = "INBOX.Junk";

/**
 * Use nice image for warning message?
 * @var boolean
 */
$junkfolder_useimage = true;

/**
 * Informational: Is the system configured to auto-create the Junk folder?
 * @var boolean
 */
$junkfolder_autocreate = true;

/**
 * How many days have you set ipurge to?
 * @var int
 */
$junkfolder_days = 7;

?>
