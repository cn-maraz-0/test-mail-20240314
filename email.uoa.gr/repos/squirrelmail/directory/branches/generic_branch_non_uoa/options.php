<?php
/**
 * options.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: options.php,v 1.2.2.2 2006/07/26 08:16:47 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH', '../../');
    include_once (SM_PATH . 'include/validate.php');
}
    
define('DIR_PATH', SM_PATH . "plugins/directory/");

include_once (DIR_PATH . 'config.php');
include_once (DIR_PATH . 'constants.php');
include_once (DIR_PATH . 'functions.php');

if (isset($_POST['submit_directory'])) {

	
      sqgetGlobalVar('directory_output_type', $directory_output_type, SQ_POST);
      setPref($data_dir, $username, 'directory_output_type', $directory_output_type);

      foreach ($ldq_enable_attrs as $attr) {
	 $Var = 'directory_showattr_'.$attr;
	 if (isset($_POST[$Var])) {
	    $$Var = '1';
	 } else {
	    $$Var = '0';
	 }   
         setPref($data_dir, $username, $Var, $$Var);
      }
      header("Location: ../../src/options.php");
      exit;
}

displayPageHeader($color, 'None');

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

directory_LoadPrefs();



$optpage_title = _("Options") . ' - ' . _("Directory Service");

echo html_tag( 'table', '', 'center', $color[0], 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) . "\n" .
        html_tag( 'tr' ) . "\n" .
            html_tag( 'td', '', 'center' ) .
                "<b>$optpage_title</b><br>\n".
                html_tag( 'table', '', '', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) . "\n" .
                    html_tag( 'tr' ) . "\n" .
                        html_tag( 'td', '', 'left', $color[4] ) . "\n";


echo '<form action="options.php" method="post">';

echo html_tag('p',  html_tag('strong', _("Type of Output") ));

echo '<p><input type="radio" name="directory_output_type" value="onetable" id="directory_output_onetable"';
if(!isset($directory_output_type) || $directory_output_type == 'onetable') {
	echo ' checked=""';
}
echo '> ';
echo '<label for="directory_output_onetable">';
echo _("One Table");

echo '<br /><blockquote>';
echo _("Display results as one big table with one row per record found and each selected attribute as a separate column.");
echo '</blockquote></label></p>';


echo '<p><input type="radio" name="directory_output_type" value="multitable" id="directory_output_multitable"';
if($directory_output_type == 'multitable') {
	echo ' checked=""';
}
echo '> ';

echo '<label for="directory_output_multitable">';
echo _("Multiple Table");
echo '<br /><blockquote>';
echo _("One table per record found with 2 columns for each table. One colunn contains attribute names and the 2nd column shows values for each attribute.  This format is best if you have many attributes selected or if the values for some of the attributes you have selected can be very long with no spaces.");
echo '</blockquote></label></p>';

/* Checkboxes */
echo html_tag('p',  html_tag('strong', _("Fields to display") ));
echo '<blockquote>';
directory_ShowCheckboxes ($ldq_enable_attrs);
echo '</blockquote>';


echo '<p align="right"><input type="submit" value="'. _("Submit") .'" name="submit_directory"></p>';

echo '</td></tr></table></form>';

/* Close Squirrelmail table */
echo        '</td></tr>' .
        '</table>'.
        '</td></tr>'.
     '</table>' .
     '</body></html>';

?>
