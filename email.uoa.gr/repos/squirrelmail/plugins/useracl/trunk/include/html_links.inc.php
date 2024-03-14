<?php
/**
 * html_links.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2009 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 */

function useracl_link_folders_page() {
    global $color, $useracl_notify_url, $base_uri;

    sq_bindtextdomain('useracl', SM_PATH . 'locale');
    textdomain ('useracl');

	echo html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
	html_tag( 'tr' ) .
	html_tag( 'td', '', 'center', $color[4] ) .

	html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
		html_tag( 'tr',
		html_tag( 'td', '<b>' . _("Folder Sharing") . '</b>', 'center', $color[9] )
		) .
		html_tag( 'tr' ) .
		html_tag( 'td', '', 'left', $color[0] ) ;

    echo '<p>'.
        '<a href="'.$base_uri.'plugins/useracl/useracl.php">'.
        '<img src="'.$base_uri.'plugins/useracl/images/shares.png" border="0" alt="" style="float: left; padding: 3px;" /></a> '.
		_("You can share your folders with other users on this mail system, allowing them to read, write or delete messages."). ' ' .
        
        sprintf( _("<strong><a href=\"%s\">Go to the folder shares page</a></strong> to customize access by other people to your folders."),
            $base_uri.'plugins/useracl/useracl.php').
            '</p>';
    
    if(!empty($useracl_notify_url)) {
        echo '<p><img src="'.$base_uri.'plugins/useracl/images/help.png" width="16" height="16" border="0" alt="[Help]" " align="middle" /> '.
            sprintf( _("If you need any help with regard to the shared folders, please consult our <a href=\"%s\">help / support pages</a>."),
            $useracl_notify_url ).
            '</p>';
    }
	
	sq_bindtextdomain('squirrelmail', SM_PATH . 'locale');
	textdomain('squirrelmail');


	echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."</table>\n";

	echo '</td></tr>
	</table>';
}

function useracl_link_menuline_do() {
    sq_bindtextdomain('useracl', SM_PATH . 'locale');
    textdomain ('useracl');

    displayInternalLink('plugins/useracl/useracl.php', _("Shares"));
    echo "&nbsp;&nbsp;\n";

    textdomain ('squirrelmail');
}

