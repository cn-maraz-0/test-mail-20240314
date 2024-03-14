<?php
/**
 * functions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage standalone
 * @version $Id: standalone.php,v 1.7 2005/04/14 14:28:37 avel Exp $
 * @copyright (c) 2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Standalone mode set up environment.
 */

define('DIR_PATH', '');
define('SM_PATH', '');

	/* Dummy variables */
	$data_dir = '';
	
	$public_mode = true;


	/* EXPERIMENTAL Session sharing with Squirrelmail */
	// $session_name = 'UOASID';
	/* set the name of the session cookie */
	//if(isset($session_name) && $session_name) {
	//	ini_set('session.name' , $session_name);
	//} else {
		ini_set('session.name' , 'DIRSID');
	//}
	sqsession_is_active();

	if(isset($_SESSION['user_is_logged_in'])) {
		$squirrelmail_session_open = true;
		$sq_base_url = $_SESSION['sq_base_url'];
		$public_mode = false;
	} else {
		$squirrelmail_session_open = false;
	}

	/**
	 * Username setup
	 */
	if(isset($_SESSION['username']) && isset($_SESSION['logged_in'])) {
		$username = $_SESSION['username'];
		$logged_in = true;
	} else {
		$username = 'anonymous';
		$logged_in = false;
	}


	/* Locale Setup */
	/* Language Selection Setup */
	if(isset($_GET['language'])) {
		$language = $lang_iso = $_GET['language'];
		if(isset($_SESSION['language']) && $_SESSION['language'] != $language) {
			$switch_lang = $_GET['language'];
		}
		$_SESSION['language'] = $language;
	} elseif(isset($_SESSION['language'])) {
		$language = $lang_iso = $_SESSION['language'];
	} else {
		$language = $lang_iso = $directory_prefs_default['language'];
	}



	if ( !ini_get('safe_mode') && getenv( 'LC_ALL' ) != $language ) {
            putenv( "LC_ALL=$language" );
            putenv( "LANG=$language" );
            putenv( "LANGUAGE=$language" );
        }

        setlocale(LC_ALL, $language);



include(DIR_PATH . 'standalone/standalone_html.php');
include(DIR_PATH . 'standalone/themes/noc_theme.php');

  if (!isset($PHP_SELF) || empty($PHP_SELF)) {
     $PHP_SELF =  $HTTP_SERVER_VARS['PHP_SELF'];
  }
$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

define('SQ_INORDER',0);
define('SQ_GET',1);
define('SQ_POST',2);
define('SQ_SESSION',3);
define('SQ_COOKIE',4);
define('SQ_SERVER',5);
define('SQ_FORM',6);

/**
 * Search for the var $name in $_SESSION, $_POST, $_GET,
 * $_COOKIE, or $_SERVER and set it in provided var. 
 *
 * If $search is not provided,  or == SQ_INORDER, it will search
 * $_SESSION, then $_POST, then $_GET. Otherwise,
 * use one of the defined constants to look for 
 * a var in one place specifically.
 *
 * Note: $search is an int value equal to one of the 
 * constants defined above.
 *
 * example:
 *    sqgetGlobalVar('username',$username,SQ_SESSION);
 *  -- no quotes around last param!
 *
 * Returns FALSE if variable is not found.
 * Returns TRUE if it is.
 */
function sqgetGlobalVar($name, &$value, $search = SQ_INORDER) {

    if ( !check_php_version(4,1) ) {
        global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS, 
               $HTTP_SERVER_VARS, $HTTP_SESSION_VARS;

        $_COOKIE  =& $HTTP_COOKIE_VARS;
        $_GET     =& $HTTP_GET_VARS;
        $_POST    =& $HTTP_POST_VARS;
        $_SERVER  =& $HTTP_SERVER_VARS;
        $_SESSION =& $HTTP_SESSION_VARS;
    }

    /* NOTE: DO NOT enclose the constants in the switch
       statement with quotes. They are constant values,
       enclosing them in quotes will cause them to evaluate
       as strings. */
    switch ($search) {
        /* we want the default case to be first here,  
	   so that if a valid value isn't specified, 
	   all three arrays will be searched. */
      default:
      case SQ_INORDER:   // check session, post, get
      case SQ_SESSION:
        if( isset($_SESSION[$name]) ) {
            $value = $_SESSION[$name];
            return TRUE;
        } elseif ( $search == SQ_SESSION ) {
            break;
        }
      case SQ_FORM:      //  check post, get
      case SQ_POST:
        if( isset($_POST[$name]) ) {
            $value = $_POST[$name];
            return TRUE;
        } elseif ( $search == SQ_POST ) {
          break;
        }
      case SQ_GET:
        if ( isset($_GET[$name]) ) {
            $value = $_GET[$name];
            return TRUE;
        } 
        /* NO IF HERE. FOR SQ_INORDER CASE, EXIT after GET */
        break;
      case SQ_COOKIE:
        if ( isset($_COOKIE[$name]) ) {
            $value = $_COOKIE[$name];
            return TRUE; 
        }
        break;
      case SQ_SERVER:
        if ( isset($_SERVER[$name]) ) {
            $value = $_SERVER[$name];
            return TRUE;
        }
        break;
    }
    return FALSE;
}

$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);


function sqsession_is_active() {

    $sessid = session_id();
    if ( empty( $sessid ) ) {
        session_start();
    }
}

/** 
 * returns true if current php version is at mimimum a.b.c 
 * 
 * Called: check_php_version(4,1)
 */
function check_php_version ($a = '0', $b = '0', $c = '0')             
{
    global $SQ_PHP_VERSION;
 
    if(!isset($SQ_PHP_VERSION))
        $SQ_PHP_VERSION = substr( str_pad( preg_replace('/\D/','', PHP_VERSION), 3, '0'), 0, 3);

    return $SQ_PHP_VERSION >= ($a.$b.$c);
}

/**
 * GetPref Replacement
 */
function getpref($data_dir, $username, $string) {
	global $directory_prefs_default;
	if(isset($directory_prefs_default[$string])) {
		return $directory_prefs_default[$string];
	} else {
		return 0;
	}
}


function makeInternalLink($path, $text, $target='') {
    sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION);
    if ($target != '') {
        $target = " target=\"$target\"";
    }
    return '<a href="'.$base_uri.$path.'"'.$target.'>'.$text.'</a>';
}

/* returns a link to the compose-page, taking in consideration
 * the compose_in_new and javascript settings. */
function makeComposeLink($url, $text = null)
{
    global $compose_new_win,$javascript_on;

    if(!$text) {
        $text = _("Compose");
    }

    if($compose_new_win != '1') {
        return makeInternalLink($url, $text, 'right');
    }

    /* if we can use JS, use the fancy window, else just open a new one HTML-style */
    if($javascript_on) {
        sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION);
        return '<a href="javascript:void(0)" onclick="comp_in_new(\''.$base_uri.$url.'\')">'. $text.'</a>';
    }

    return makeInternalLink($url, $text, '_blank');
}


/* Myvariables */

$plugins = array();



/* This array specifies the available languages. */

$languages['bg_BG']['NAME']    = 'Bulgarian';
$languages['bg_BG']['CHARSET'] = 'windows-1251';
$languages['bg']['ALIAS'] = 'bg_BG';

// The glibc locale is ca_ES.

$languages['ca_ES']['NAME']    = 'Catalan';
$languages['ca_ES']['CHARSET'] = 'iso-8859-1';
$languages['ca']['ALIAS'] = 'ca_ES';

$languages['cs_CZ']['NAME']    = 'Czech';
$languages['cs_CZ']['CHARSET'] = 'iso-8859-2';
$languages['cs']['ALIAS']      = 'cs_CZ';

// Danish locale is da_DK.

$languages['da_DK']['NAME']    = 'Danish';
$languages['da_DK']['CHARSET'] = 'iso-8859-1';
$languages['da']['ALIAS'] = 'da_DK';

$languages['de_DE']['NAME']    = 'Deutsch';
$languages['de_DE']['CHARSET'] = 'iso-8859-1';
$languages['de']['ALIAS'] = 'de_DE';

// There is no en_EN! There is en_US, en_BR, en_AU, and so forth, 
// but who cares about !US, right? Right? :)

$languages['el_GR']['NAME']    = 'Greek';
$languages['el_GR']['CHARSET'] = 'iso-8859-7';
$languages['el']['ALIAS'] = 'el_GR';

/* Alex - Edunet */
$languages['en_GR']['NAME']    = 'Greek (with English GUI)';
$languages['en_GR']['CHARSET'] = 'iso-8859-7';

$languages['en_US']['NAME']    = 'English';
$languages['en_US']['CHARSET'] = 'iso-8859-1';
$languages['en']['ALIAS'] = 'en_US';

$languages['es_ES']['NAME']    = 'Spanish';
$languages['es_ES']['CHARSET'] = 'iso-8859-1';
$languages['es']['ALIAS'] = 'es_ES';

$languages['et_EE']['NAME']    = 'Estonian';
$languages['et_EE']['CHARSET'] = 'iso-8859-15';
$languages['et']['ALIAS'] = 'et_EE';

$languages['fi_FI']['NAME']    = 'Finnish';
$languages['fi_FI']['CHARSET'] = 'iso-8859-1';
$languages['fi']['ALIAS'] = 'fi_FI';

$languages['fo_FO']['NAME']    = 'Faroese';
$languages['fo_FO']['CHARSET'] = 'iso-8859-1';
$languages['fo']['ALIAS'] = 'fo_FO';

$languages['fr_FR']['NAME']    = 'French';
$languages['fr_FR']['CHARSET'] = 'iso-8859-1';
$languages['fr']['ALIAS'] = 'fr_FR';

$languages['hr_HR']['NAME']    = 'Croatian';
$languages['hr_HR']['CHARSET'] = 'iso-8859-2';
$languages['hr']['ALIAS'] = 'hr_HR';

$languages['hu_HU']['NAME']    = 'Hungarian';
$languages['hu_HU']['CHARSET'] = 'iso-8859-2';
$languages['hu']['ALIAS'] = 'hu_HU';

$languages['id_ID']['NAME']    = 'Bahasa Indonesia';
$languages['id_ID']['CHARSET'] = 'iso-8859-1';
$languages['id']['ALIAS'] = 'id_ID';

$languages['is_IS']['NAME']    = 'Icelandic';
$languages['is_IS']['CHARSET'] = 'iso-8859-1';
$languages['is']['ALIAS'] = 'is_IS';

$languages['it_IT']['NAME']    = 'Italian';
$languages['it_IT']['CHARSET'] = 'iso-8859-1';
$languages['it']['ALIAS'] = 'it_IT';

$languages['ja_JP']['NAME']    = 'Japanese';
$languages['ja_JP']['CHARSET'] = 'iso-2022-jp';
$languages['ja_JP']['XTRA_CODE'] = 'japanese_charset_xtra';
$languages['ja']['ALIAS'] = 'ja_JP';

$languages['ko_KR']['NAME']    = 'Korean';
$languages['ko_KR']['CHARSET'] = 'euc-KR';
$languages['ko_KR']['XTRA_CODE'] = 'korean_charset_xtra';
$languages['ko']['ALIAS'] = 'ko_KR';

$languages['lt_LT']['NAME']    = 'Lithuanian';
$languages['lt_LT']['CHARSET'] = 'utf-8';
$languages['lt_LT']['LOCALE'] = 'lt_LT.UTF-8';
$languages['lt']['ALIAS'] = 'lt_LT';

$languages['ms_MY']['NAME']    = 'Bahasa Melayu';
$languages['ms_MY']['CHARSET'] = 'iso-8859-1';
$languages['my']['ALIAS'] = 'ms_MY';

$languages['nl_NL']['NAME']    = 'Dutch';
$languages['nl_NL']['CHARSET'] = 'iso-8859-1';
$languages['nl']['ALIAS'] = 'nl_NL';

$languages['no_NO']['NAME']    = 'Norwegian (Bokm&aring;l)';
$languages['no_NO']['CHARSET'] = 'iso-8859-1';
$languages['no']['ALIAS'] = 'no_NO';
$languages['nn_NO']['NAME']    = 'Norwegian (Nynorsk)';
$languages['nn_NO']['CHARSET'] = 'iso-8859-1';

$languages['pl_PL']['NAME']    = 'Polish';
$languages['pl_PL']['CHARSET'] = 'iso-8859-2';
$languages['pl']['ALIAS'] = 'pl_PL';

$languages['pt_PT']['NAME'] = 'Portuguese (Portugal)';
$languages['pt_PT']['CHARSET'] = 'iso-8859-1';
$languages['pt_BR']['NAME']    = 'Portuguese (Brazil)';
$languages['pt_BR']['CHARSET'] = 'iso-8859-1';
$languages['pt']['ALIAS'] = 'pt_PT';

$languages['ro_RO']['NAME']    = 'Romanian';
$languages['ro_RO']['CHARSET'] = 'iso-8859-2';
$languages['ro']['ALIAS'] = 'ro_RO';

$languages['ru_RU']['NAME']    = 'Russian';
$languages['ru_RU']['CHARSET'] = 'utf-8';
$languages['ru_RU']['LOCALE'] = 'ru_RU.UTF-8';
$languages['ru']['ALIAS'] = 'ru_RU';

$languages['sk_SK']['NAME']     = 'Slovak';
$languages['sk_SK']['CHARSET']  = 'iso-8859-2';
$languages['sk']['ALIAS']       = 'sk_SK';

$languages['sl_SI']['NAME']    = 'Slovenian';
$languages['sl_SI']['CHARSET'] = 'iso-8859-2';
$languages['sl']['ALIAS'] = 'sl_SI';

$languages['sr_YU']['NAME']    = 'Serbian';
$languages['sr_YU']['CHARSET'] = 'iso-8859-2';
$languages['sr']['ALIAS'] = 'sr_YU';

$languages['sv_SE']['NAME']    = 'Swedish';
$languages['sv_SE']['CHARSET'] = 'iso-8859-1';
$languages['sv']['ALIAS'] = 'sv_SE';

$languages['tr_TR']['NAME']    = 'Turkish';
$languages['tr_TR']['CHARSET'] = 'iso-8859-9';
$languages['tr']['ALIAS'] = 'tr_TR';

$languages['zh_TW']['NAME']    = 'Chinese Trad';
$languages['zh_TW']['CHARSET'] = 'big5';
$languages['tw']['ALIAS'] = 'zh_TW';

$languages['zh_CN']['NAME']    = 'Chinese Simp';
$languages['zh_CN']['CHARSET'] = 'gb2312';
$languages['cn']['ALIAS'] = 'zh_CN';

$languages['th_TH']['NAME']    = 'Thai';
$languages['th_TH']['CHARSET'] = 'tis-620';
$languages['th']['ALIAS'] = 'th_TH';
/*
$languages['uk_UA']['NAME']    = 'Ukrainian';
$languages['uk_UA']['CHARSET'] = 'koi8-u';
$languages['uk']['ALIAS'] = 'uk_UA';
*/
$languages['cy_GB']['NAME']    = 'Welsh';
$languages['cy_GB']['CHARSET'] = 'iso-8859-1';
$languages['cy']['ALIAS'] = 'cy_GB';
/*
$languages['vi_VN']['NAME']    = 'Vietnamese';
$languages['vi_VN']['CHARSET'] = 'utf-8';
$languages['vi']['ALIAS'] = 'vi_VN';
*/

// Right to left languages

$languages['ar']['NAME']    = 'Arabic';
$languages['ar']['CHARSET'] = 'windows-1256';
$languages['ar']['DIR']     = 'rtl';

$languages['he_IL']['NAME']    = 'Hebrew';
$languages['he_IL']['CHARSET'] = 'windows-1255';
$languages['he_IL']['DIR']     = 'rtl';
$languages['he']['ALIAS']      = 'he_IL';
       
       
header( 'Content-Type: text/html; charset=' . $languages[$language]['CHARSET'] );

?>
