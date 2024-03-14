<?php
/**
 * Authenticate for Squirrelmail.
 *
 * This is required by the file tiny_mce/plugins/spellchecker/rpc.php, so that
 * the spellchecker mechanism cannot be used by anyone on the Internet.
 *
 * @package plugins
 * @subpackage html_mail
 */

/** Path is relative to file rpc.php */
define("SM_PATH",'../../../../../');

/** Include Squirrelmail validation */
include_once(SM_PATH . 'include/validate.php');
