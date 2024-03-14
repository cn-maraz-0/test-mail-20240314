<?php
/**
 * DevHelper Simple Helper plugin for Squirrelmail Developers.
 *
 * Toolbox for various things that might be useful.
 *
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: devhelper_toolbox.php,v 1.1.1.1 2006/11/03 17:39:55 avel Exp $
 * @package plugins
 * @subpackage devhelper
 */

/** Includes */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'include/load_prefs.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/date.php');
}

include_once(SM_PATH . 'functions/imap.php');
include_once(SM_PATH . 'plugins/devhelper/config.php');
include_once(SM_PATH . 'plugins/devhelper/include/dumpr.php');
include_once(SM_PATH . 'plugins/devhelper/include/functions.inc.php');

$SELF = $PHP_SELF;

/* ============================ SASL PLAIN ========================== */

displayPageHeader("DevHelper - Toolbox");

print '<h2>Print SASL PLAIN Login String</h2>';

print '<form name="saslplain_form" method="POST" action="'.$SELF.'">';

print '<br/> Auth: <input name="auth" size="20" />';
print '<br/> User: <input name="user" size="20" />';
print '<br/> Pass: <input name="pass" type="password" size="20" />';
print '<br/> <input name="saslplain" type="submit" value="Go!" />';

print '</form>';

if(isset($_POST['saslplain'])) {
	$auth = $_POST['auth'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];

	print "<h3>Result:</h3><strong>" . base64_encode("$user\0$auth\0$pass") . "</strong>";;
}


/* ============================ SASL DIGEST-MD5 Response ========================== */

print '<h2>Print SASL Digest-MD5 Response String</h2>';

print '<form name="sasldm5_form" method="POST" action="'.$SELF.'">';

print '<br/> Auth: <input name="dm5auth" size="20" />';
print '<br/> User: <input name="dm5user" size="20" />';
print '<br/> Pass: <input name="dm5pass" type="password" size="20" />';
print '<br/> Challenge: <input name="dm5challenge" size="20" />';
print '<br/> Service: <input name="dm5service" size="20" />';
print '<br/> Host: <input name="dm5host" size="20" />';
print '<br/> <input name="sasldm5" type="submit" value="Go!" />';

print '</form>';

if(isset($_POST['sasldm5'])) {
	$authz = $_POST['dm5auth'];
	$username = $_POST['dm5user'];
	$password = $_POST['dm5pass'];
	$challenge = $_POST['dm5challenge'];
	$service = $_POST['dm5service'];
	$host = $_POST['dm5host'];
    
    print "<h3>Result:</h3>".
            '<input name="result" value="' .
            digest_md5_response ($username,$password,$challenge,$service,$host,$authz='')
            . '"/>';
}


/* ====================== ldap password encode  ================ */

print '<h2>Encode Password for LDAP (Base64)</h2>';

print '<form name="ldappass_form" method="POST" action="'.$SELF.'">';

print '<br> Pass: <input name="password" type="password" size="20" />';
print ' <input name="ldappass" type="submit" value="Go!" />';

print '</form>';

	/**
	 * Generates the encrypted password.
	 * @return string
	 */
	function generate_crypt_password($cleartext) {
		$cset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./";
		$salt = substr($cset, time() & 63, 1) .
        		substr($cset, time()/64 & 63, 1);
		return crypt($cleartext,$salt);
	}


if(isset($_POST['ldappass'])) {
    $password = $_POST['password'];
    print '<h3>Result:</h3><strong> {CRYPT}'.generate_crypt_password($password) . '</strong>';
}


echo '</body></html>';
?>
