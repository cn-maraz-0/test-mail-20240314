<?php
/**
 * Autocomplete for Squirrelmail
 *
 * @package plugins
 * @subpackage javascript_autocomplete
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

/**
 * Options under display
 */
function javascript_autocomplete_options_display_inside_do($hookname) {
    global $data_dir, $username;
    sq_bindtextdomain('javascript_autocomplete', SM_PATH . '/plugins/javascript_autocomplete/locale');
    textdomain('javascript_autocomplete');

    $javascript_autocomplete_options = getPref($data_dir, $username, 'javascript_autocomplete_options', 2);

    echo html_tag( 'tr', "\n".
            html_tag( 'td',
                '<b>' . _("Automatic Completion of Addresses") . '</b>' ,
                'center' ,'', 'valign="middle" colspan="2" nowrap' )
        ) ."\n";

    echo '<tr><td align=right valign=top>' .
      _("Auto complete addresses during compose:") . "</td>"  .
      '<td>' .

      '<input type="radio" value="0" name="javascript_autocomplete_options" id="javascript_autocomplete_options_0" '.
        ($javascript_autocomplete_options == 0 ? ' checked=""' : '').' /> '.
       '<label for="javascript_autocomplete_options_0">&nbsp;' . _("Disable autocomplete") . '</label><br/>' .

      '<input type="radio" value="1" name="javascript_autocomplete_options" id="javascript_autocomplete_options_1" '.
        ($javascript_autocomplete_options == 1 ? ' checked=""' : '').' /> '.
       '<label for="javascript_autocomplete_options_1">&nbsp;' . _("Only for my contacts") . '</label><br/>' .

      '<input type="radio" value="2" name="javascript_autocomplete_options" id="javascript_autocomplete_options_2" '.
        ($javascript_autocomplete_options == 2 ? ' checked=""' : '').' /> '.
       '<label for="javascript_autocomplete_options_2">&nbsp;' . _("For my contacts and University directory") . '</label><br/>' .
      '</td></tr>';
    
    textdomain ('squirrelmail');
}

function javascript_autocomplete_options_display_save_do() {
    global $username, $data_dir;
    sqgetGlobalVar('javascript_autocomplete_options', $javascript_autocomplete_options, SQ_FORM);
    if(in_array($javascript_autocomplete_options, array(0, 1, 2))) {
        setPref($data_dir, $username, 'javascript_autocomplete_options', $javascript_autocomplete_options);
    }
}

