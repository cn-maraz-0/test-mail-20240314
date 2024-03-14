<?php
/**
 * Presentation functions for quota usage display.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage ldapfolderinfo
 * @version $Id: quota_display.inc.php,v 1.2 2006/11/22 13:21:23 avel Exp $
 */

/**
 * Display Quota usage in left frame
 */
function ldapfolderinfo_display_quota_usage_do() {
    global $data_dir, $username, $key, $imapServerAddress, $imapPort,
    $imap_general, $imap_stream, $imapConnection, 
    $sqimap_capabilities, $UseSeparateImapConnection, $color;

    include( SM_PATH . 'plugins/ldapfolderinfo/config.php');
    include_once( SM_PATH . 'plugins/ldapfolderinfo/include/quota.inc.php');
    
    bindtextdomain ('ldapfolderinfo', SM_PATH . 'plugins/ldapfolderinfo/locale');
    textdomain ('ldapfolderinfo');

    // Detect if we have already connected to IMAP or not.
    // Also check if we are forced to use a separate IMAP connection
    if ((!isset($imap_stream) && !isset($imapConnection)) ||
        $UseSeparateImapConnection) {
        $stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 10);
        $previously_connected = false;
    } elseif (isset($imapConnection)) {
        $stream = $imapConnection;
        $previously_connected = true;
    } else {
        $previously_connected = true;
        $stream = $imap_stream;
    }

    if(! sqimap_capability($stream, 'QUOTA', true)) {
        // No Quota Information; don't display anything.
        sqimap_logout($stream);
        return;
    }
    
    $quota_status = sqimap_get_quota_ldapfolderinfo($stream, 'INBOX', $taken, $total);

    if($quota_status == QUOTA_NOT_SET) {
          echo _("Quota not set.");
    } elseif($quota_status = QUOTA_DEFINED_IN_PARENT) {
          echo _("Quota is defined elsewhere");
    } elseif ($quota_status = QUOTA_SET) {

        if($total != 0) {
            $percent = number_format(($taken/$total) * 100, 0);
        } else {
            $percent = 0;
        }

    $width = getPref($data_dir, $username, 'left_size')-40;
    $real_width = number_format(($taken/$total) * $width - 2*$edge_width, 0);
    $quota = number_format((($total *1024) - 1023) / 1000000, 1);

    if($percent>100) {

    echo "<script language=\"JavaScript\">\nalert(\"".
        _("Warning: You have exceeded your quota (allowable mail storage space on this server).\nFor this session, the move to trash function will not work so that you can first delete some emails or move them to local folders in your computer.") .
        "\"\n</script>\n";
        setPref($data_dir, $username, "move_to_trash", 0);
    }
         
    // if($percent >= $warn_percent) $fontcolorstring = " color=#FF0000";

    echo '<table border="0" cellpadding="2" width="'.$width.'" align="center">';
    echo '<tr bgcolor="'.$color[3].'">'.
        '<td nowrap=""><font size="-1" color="'.$color[8].'">' .
        sprintf ( _("Usage: %s%% of %s MB"), $percent, $quota) . 
        "</strong></font>\n" .
        "</tr>\n";

    echo '<tr width='.$width.' bgcolor="'.$color[5].'"><td nowrap="">';
    
    echo '<a href="../plugins/ldapfolderinfo/folder_sizes.php" target="right" title="'.
    _("More information about your quota...") . '">';
     
     if($taken == 0 && $total == 0) {
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/caution.gif" border="0" alt="'. _("Locked") .'" align="middle" /> '
            . _("Locked");

    } elseif ($taken > 0 && $total == 0) {
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/important.gif" border="0" alt="'. _("Locked") .'" align="middle" /> '.
            _("Locked") .' - '. _("Overquota");

    } elseif ($taken > $total) {
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/warning.gif" border="0" alt="'. _("Warning!") . '" align="middle" /> ' .
            '<strong>'. _("Overquota") . '</strong> ('.$percent.'%)';

    } else {
        echo '<img height="'.$height.'" src="'.$left_image.'" alt="" border="0">'.
            '<img src="'.$middle_image.'" height="'.$height.'" width="'.$real_width.'" alt="" border="0" />'.
            '<img height="'.$height.'" src="'.$right_image.'" alt="" border="0" />';
     
         if ($percent > 80) {
            echo ' <br/><img src="'.SM_PATH.'plugins/ldapfolderinfo/images/important.gif" border="0" alt="'.
                _("Critical!") . '" align="middle" /> '.
                '<strong>'. _("Critical!") .' </strong>';
        }
    }

    echo '</a>
    </td></tr></table>
    </center><p>';
    }
      
    if (!$previously_connected)
        sqimap_logout($stream);
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');

}

/**
 * Display Quota usage with given parameters
 *
 * @param $taken str Where the quota data will be stored
 * @param $total str Where the quota data will be stored
 * @return string
 */
function display_quota_usage_per_folder($taken, $total) {
    
    global $data_dir, $username, $color;
    global $use_pixmap, $height, $left_image, $right_image, $middle_image,
    $width_test;
    
    if($taken== 0 && $total == 0) {
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/caution.gif" border="0" alt="Post" align="middle" /> Locked';
        return;
    } elseif ($taken > 0 && $total == 0) {
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/important.gif" border="0" alt="Post" align="middle" /> Locked (overquota)';
        return;
    } elseif ($taken > $total) {
        $percent = number_format(($taken/$total) * 100, 0);
        echo '<img src="'.SM_PATH.'plugins/ldapfolderinfo/images/warning.gif" border="0" alt="Warning!" align="middle" /> <strong>' .
            _("Overquota!") . '</strong> ('.$percent.'%)';
        return;
    }

    $percent = number_format(($taken/$total) * 100, 0);
    $width = getPref($data_dir, $username, 'left_size') + 10;
    $real_width = number_format(($taken/$total) * $width, 0);
    $quota = number_format((($total *1024) - 1023) / 1000000, 1);
    
    // if($percent >= $warn_percent) $fontcolorstring = " color=#FF0000";
    echo '<table border="0" cellpadding="2" width='.$width.'" bgcolor="'.$color[5].'">';
    echo '<tr>';
    echo '<td bgcolor="'.$color[3].'" nowrap="">

<img height="'.$height.'" src="'.$left_image.'" alt="" border="0"><img src="'.$middle_image.'" height="'.$height.'" width="'.$real_width.'" alt="" border="0"><img height="'.$height.'" src="'.$right_image.'" alt="" border="0">
';
         echo "</td></tr><tr><td>";

     if ($percent > 80) {
        echo ' <img src="'.SM_PATH . 'plugins/ldapfolderinfo/images/important.gif" border="0" alt="'. _("Critical!") . '" align="middle" /> <strong>';
    }
     
     echo "<font color=$color[8]>" . sprintf( _("Used %s%% quota of %s MB"), $percent, $quota) . "</font>\n";
     
     if ($percent > 80) {
         echo "</strong>";
    }
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</font></center><p>\n";
}
    

?>
