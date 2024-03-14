<?php
/**
 * folder_sizes.inc.php
 *
 * Functions for Folder Sizes script (folder_sizes.php).
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @author Robin Rainton <robin@rainton.com>
 * @package plugins
 * @subpackage ldapfolderinfo
 */

/**
 * Main function for calculating and outputting folder list.
 *
 * @param $imapConnection obj the available imap stream
 * @param $ldap obj the available ldap stream, or boolean false if LDAP information is not desired.
 *
 * @author Robin Rainton <robin@rainton.com>
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 */
function ldapfolderinfo_folder_sizes_list($imapConnection, $ldap) {
  
    /* Global variables */
    global $username, $data_dir;
  
    $boxes = sqimap_mailbox_list($imapConnection);
    $delimiter = sqimap_get_delimiter($imapConnection);
    $compose_new_win = getPref($data_dir, $username, 'compose_new_win');
    $language = getPref($data_dir, $username, 'language');
    $lang = substr($language, 0, 2);

    if(isset($_SESSION['ldapfolderinfo'])) {
        $folderinfo = $_SESSION['ldapfolderinfo'];
    }

    if(isset($ldap) && $ldap !== false) {
        /* Ask LDAP for folder information */
        global $ldap_base_dn;
        $folderattributes = array('cn', 'mail', 'description');  // FIXME: 'owner' ????
    }

    /*
     * ---------- Business Logic ------------
     *
     * Work through the folder list and count messages in each.
     *
     * Fill all the information inside the $boxes structure itself.
     *
     * We will add the message count for each folder to the original boxes
     * array. This is so we can determine how deep the directories are
     * before we start. This is necessary because we need to know how many
     * columns are going to be presented to begin with.
     */
    
    $max_depth = 0;
    for ($boxnum = 0; $boxnum < count($boxes); $boxnum++) {
        $real_box = $boxes[$boxnum]['unformatted'];

        /* Record new depth if it's greatest so far */
        $parts = explode($delimiter, $boxes[$boxnum]['unformatted']);
        $boxes[$boxnum]['display'] = imap_utf7_decode_local(array_pop($parts));
        $boxes[$boxnum]['depth'] = count($parts);
    
        if ($boxes[$boxnum]['depth']  > $max_depth) {
            $max_depth = $boxes[$boxnum]['depth'];
        }

        /* Get sizes for this folder if it's selectable */
        if (!in_array('noselect', $boxes[$boxnum]['flags'])) {
            list($count, $unread, $size) = get_folder_size($imapConnection, $boxes[$boxnum]['unformatted']);
            // print "DEBUG: ".$boxes[$boxnum]['unformatted'].": $count - $unread - $size <br>";

            if($count == -1) {    /* FIXME */
                $boxes[$boxnum]['flags'][] = 'noselect';
            }
            $boxes[$boxnum]['count'] = $count;
            $boxes[$boxnum]['unread'] = $unread;
            $boxes[$boxnum]['size'] = $size;
        
            if (strstr($boxes[$boxnum]['unformatted'], "INBOX")) {
        
                $boxes[$boxnum]['quotastatus'] = sqimap_get_quota_ldapfolderinfo($imapConnection, $real_box, $tak, $tot);
                $boxes[$boxnum]['quota']['taken'] = $tak;
                unset ($tak);
                $boxes[$boxnum]['quota']['total'] = $tot;
                unset ($tot);
            }
            
            
        /* Following stuff applies only for folders outside
         * INBOX (Shared folders or other users' folders) */

        if (!strstr($real_box, "INBOX") 
          // && !preg_match('/^user./', $real_box )
          && $ldap !== false ) {

            /* Ask LDAP for user permissions on the current folder */
            // Old Code:
            // get_user_perms($username, $boxes[$boxnum]['unformatted'], $permarray[$boxes[$boxnum]['unformatted']]);
            // get_user_perms($username, $boxes[$boxnum]['unformatted'], $temp_perm_array);
        
            if( $acl = sqimap_myrights ($imapConnection, $real_box) ) {
                $boxes[$boxnum]['acl'] = trim($acl);
            }

            /* Also, if permission is admin or delete, get
             * the quota as well. */

            if (strstr($acl, 'd')) {
                $boxes[$boxnum]['quotastatus'] = sqimap_get_quota_ldapfolderinfo($imapConnection, $real_box, $tak, $tot);
                $boxes[$boxnum]['quota']['taken'] = $tak;
                unset ($tak);
                $boxes[$boxnum]['quota']['total'] = $tot;
                unset ($tot);
            }
            unset($acl);
            
            /* Ask LDAP for information about the owner of this folder. */

            $folderownerdn = ldapfolderinfo_get_folder_attribute($real_box, 'owner');
            $uidfilter = strtok($folderownerdn, ',');
            if ($search_result = ldap_search($ldap, $ldap_base_dn, $uidfilter, $folderattributes )) {
                $userinfo = ldap_get_entries($ldap, $search_result);
                if($userinfo['count'] > 0) {
                    $boxes[$boxnum]['owner']['name'] = $userinfo[0]['cn'][0];
                    if(isset($userinfo[0]['cn;lang-'.$lang][0])) {
                        $boxes[$boxnum]['owner']['name_'.$lang] = directory_string_convert($userinfo[0]['cn;lang-'.$lang][0], "UTF-8", 'iso-8859-7');
                    }
                    $boxes[$boxnum]['owner']['email'] = $userinfo[0]['mail'][0];
                }
            }
        }
        }
    }

    unset($real_box);

    /*
    print "ended asking stuff. the results:";
    print "<pre>"; print_r($boxes); print "</pre>";
    */


    /* ------------- Presentation Logic --------------- */
    global $tab_cols, $warn_percent, $use_pixmap, $height, $edge_width,
        $left_image, $middle_image, $right_image, $color, $location;

    $tab_cols = $max_depth + 4;

    $indent_width = 20;

    echo '<table bgcolor="'.$color[0].'" width="70%" cols="1" align="center" cellpadding="1" cellspacing="0" border="0">
    <TR><TD><TABLE BGCOLOR="'.$color[0].'" WIDTH="100%" ALIGN=CENTER cellpadding=3 cellspacing=1 border=0>';

    echo "<colgroup>" . ($tab_cols > 4 ? "<col span=" . ($tab_cols - 4) . " width=$indent_width>" : "") ;

    echo '<col width="*"><col span="3" width="5%" align="right"></colgroup>' .
        
        // '<tr><td align=center colspan="'.$tab_cols.'">'.
        '<tr><td align=center colspan="8">'.
        '<strong>'. _("Information about Folders and Bulletin Boards") . '</strong>'.
        '</td></tr>'.

        '<th><tr bgcolor="'.$color[5].'" align="center"><td colspan="'.($tab_cols - 3).'">'.
        html_tag('strong' , _("Folder")) . ' </td><td>'.
        html_tag('strong' , _("Count")) . ' </td><td>'.
        html_tag('strong' , _("Unread")) . ' </td><td>'.
        html_tag('strong' , _("Permission")) . ' </td><td>'.
        html_tag('strong' , _("Owner")) . ' </td><td>'.
        html_tag('strong' , _("Post")) . ' </td><td>'.
        html_tag('strong' , _("Size")) . ' </td><td>'.
        html_tag('strong' , _("Quota")) . ' </td>';
                
    global $plugins;
    if(in_array('useracl', $plugins)) {
        echo '<td>'. html_tag('strong' , _("Shares")) . ' </td>';
    }
    
    echo '</tr></th>';
        

    global $use_special_folder_color, $folder_sizes_subtotals;

    $folder_sizes_subtotals = 1;
    // $folder_sizes_subtotals = 0;
    global $subfolders, $subcount, $subunread, $subsize;

    /* Start Loop! */
    $last_depth = 0;
    foreach ($boxes as $boxnum => $box) {
        
        $real_box = $box['unformatted'];

        /* If we are keeping subtotals show last one(s) */
        if ($folder_sizes_subtotals && $last_depth > $box['depth']) {
            folder_sizes_add_totals($last_depth,  $box['depth']);
            $last_depth = $box['depth'];
        }
    
        /* Indent sub-folders  */
    
        $indent = $box['depth'] > 0
        ? "<td colspan=" . $box['depth'] . ">&nbsp;</TD>"
        : "";

        /* How many columns will this folder name cross? For non-selectable is
         * number of cols - depth For other is number of cols - depth - 3  */
    
        $use_cols = in_array('noselect', $box['flags'])
        ? $tab_cols - $box['depth']
        : $tab_cols - $box['depth'] - 3;
    
        /* Make a link to each folder */
    
        $special_color = ($use_special_folder_color && isSpecialMailbox($box['unformatted']));
        
        echo '<tr bgcolor="'.$color[4].'">' . $indent . 
            '<td' . ($use_cols > 1 ? ' colspan="'.$use_cols.'"' : '') . ">" .

            (in_array('noselect', $box['flags']) ? '' :
            '<a href="../../src/right_main.php?sort=0&amp;startMessage=1&amp;mailbox=' . urlencode($box['unformatted']) .'" TARGET="right" ' .
            'style="text-decoration:none">') .

            ($special_color ? '<font color="'.$color[11].'">' : '') . $box['display'] .
            
            (in_array('noselect', $box['flags']) ? '' : '</a>') .
            '</TD>';
    
        /* If we are moving down a level, reset the counters for when the
         * subtotal is (perhaps) displayed. */
    
        if ($last_depth < $box['depth'] || !isset($last_depth)) {
            $last_depth = $box['depth'];
            $subfolders[$last_depth] = 0;
            $subcount[$last_depth] = 0;
            $subunread[$last_depth] = 0;
            $subsize[$last_depth] = 0;
        }

        /* If ! \Noselect, display the rest. */
    
        if (!in_array('noselect', $box['flags'])) {
        
            /* Counts */
            echo "<TD>" . $box['count'] . "</TD>" .
            "<TD>" . $box['unread'] . "</TD>";
    
            /* Permission */
            echo '<td><small>';
            
            if(isset ($box['permissions']['permFlag'])) {
            switch ($box['permissions']['permFlag']) {
                case 'read':
                    print _("Read Only");
                    break;
                case 'append':
                case 'rap':
                    print html_tag ('em', _("Read &amp; Post"));
                    break;
                case 'delete':
                    print html_tag ('em', _("Read &amp; Post &amp;") . ' ' . html_tag('strong',_("Delete") ) ) ;
                    break;
                case 'admin':
                    print html_tag ('em', _("Administer"));
                    break;
                default:
                    print _("Personal Folder");
                    break;
            }
            } elseif (isset($box['acl'])) {
            switch ($box['acl']){
                case 'lrs':
                case 'lrsw':
                    print _("Read Only");
                    break;
                case 'lrswi':
                    print html_tag ('em', _("Read &amp; Append"));
                case 'lrsp':
                case 'lrswp':
                case 'lrswip':
                    print html_tag ('em', _("Read &amp; Post"));
                    break;
                case 'lrswipd':
                    print html_tag ('em', _("Read &amp; Post &amp;") . ' ' . html_tag('strong',_("Delete") ) ) ;
                    break;
                case 'lrswd':
                    print html_tag ('em', _("Read") . ' &amp; ' . html_tag('strong',_("Delete") ) ) ;
                    break;
                case 'lrswipda':
                case 'lrswipcda':
                    print html_tag ('strong', _("Administer"));
                    break;
                default:
                    break;
                
            }
            }
    
    
            echo "</small></td>";
    
    
            /* Owner */
            echo '<td>';
    
            $folderattributes = array("cn", "owner", "mail", "description"); 
    
            if (!strstr($real_box, "INBOX")) {

            if (isset($box['owner']) && isset($box['acl'])) {
            // if (isset($box['owner']) && strlen(trim($box['owner']))>0 ) 

                if (isset($box['owner']['name_'.$lang])) {
                    $uname = $box['owner']['name_'.$lang];
                } else {
                    $uname = $box['owner']['name'];
                }
                $uemail = $box['owner']['email'];
            

                $comp_uri = 'src/compose.php?mailbox='.$real_box.'&amp;send_to='.urlencode($uemail);
                    print makeComposeLink($comp_uri, $uname);

                // Was used for title=""
                $dummy =  _("Send an email to this user");

            }
        
            } else {
                //print "Moi. :-)";
            }
        
            echo '</td>';
    
            /* Post button */
        
            echo '<td align="center">';
            if ( !strstr($real_box, "INBOX") &&
                !preg_match('/^user./', $real_box ) && (
                isset($box['acl']) && strstr($box['acl'], 'p')
               ) && (
               isset($folderinfo[$real_box]['mail'])
               )) {
            
                $comp_uri = 'src/compose.php?mailbox='.$real_box.'&amp;send_to='.urlencode($folderinfo[$real_box]['mail']);

                $img =  '<img src="'.SM_PATH.'templates/uoa/images/icons/email_edit.png" border="0" alt="'. _("Post") .'"' .
                    ' align="middle" title="' . _("Post a message to this folder") .'" />';

                    print makeComposeLink($comp_uri, $img);

            }
            echo "</td>";
    
    
    
            /* Show counts (and add to subtotals) */

            /* Size */
        
            echo "<td>";
            if (strstr($box['unformatted'], "INBOX") ||
                preg_match('/^user./', $real_box ) ||
                (isset($box['acl']) && strstr($box['acl'], 'd'))
                ) {
                    echo show_readable_size($box['size']*1024);
            }
            print '</td>';
        
        
            /* Quota */
            
            echo '<td nowrap="">';
                        
            if(isset($box['quotastatus'])) {    
                if ($box['quotastatus'] > 0) {
                    display_quota_usage_per_folder($box['quota']['taken'], $box['quota']['total']);
                
                } elseif ($box['quotastatus'] == -2) {
                    print _("See Parent Folder");
                
                } elseif ($box['quotastatus'] == -1) {
                    echo _("Unlimited");
                }
            }

              echo "</td>";
            
            /* Shares */
            if(in_array('useracl', $plugins)) {
                echo '<td nowrap="">';
                if (strstr($box['unformatted'], "INBOX") && $box['unformatted'] != 'INBOX') {
                    $acl_uri = 'plugins/useracl/useracl.php?mailbox='.urlencode($box['unformatted']).'&amp;addacl=1';
                    $disp = '<img src="'.$location.'/../useracl/images/public-folder-mini.png" border="0" alt ="' . _("Add Share") .'" title="'. _("Add Share") .'" />';
                    print makeComposeLink($acl_uri, $disp);
                }
                print '</td>';
            }
    
            /* Totals */
    
            if(isset($subfolders[$last_depth]))
            $subfolders[$last_depth]++;
            else
            $subfolders[$last_depth] = 0;

            if(isset($subcount[$last_depth]))
            $subcount[$last_depth] += $box['count'];
            else
            $subcount[$last_depth] = $box['count'];

            if(isset($subunread[$last_depth]))
            $subunread[$last_depth] += $box['unread'];
            else
            $subunread[$last_depth] = $box['unread'];

            if(isset($subsize[$last_depth]))
            $subsize[$last_depth] += $box['size']*1024;
            
        } else {

            /* Display empty columns for \NoSelect    */

            echo '<td></td><td></td><td></td><td></td>';
            if(in_array('useracl', $plugins)) {
                echo '<td></td>';
            }
        }
        echo "</tr>\n";
    }

    /* Grand totals  */
    folder_sizes_add_totals($last_depth,  -1);

    /* We're done - close table, connection and return */
    echo "</table></table>\n";
}

/**
 * Counts the messages and size in $mailbox.
 *
 * @param object $imapConnection
 * @param string $mailbox
 * @return arr [0] # of messages, [1] # of unseen messages, [2] size of
 * messages
 */
function get_folder_size($imapConnection, $mailbox) {
    
    $num_messages = sqimap_get_num_messages($imapConnection, $mailbox);
    if ($num_messages > 0) {
        $size = sqimap_get_used($imapConnection, $mailbox);    
        $unseen = sqimap_unseen_messages($imapConnection, $mailbox);
    } else {
        $size = 0;
        $unseen = 0;
    }
    return array($num_messages, $unseen, $size);
}

/**
 * Sub to add the totals and subtotals for the passed depth.
 * If Depth is zero, this is the grand total and should be in bold.
 *
 * @param int $from_depth
 * @param int $to_depth
 */
function folder_sizes_add_totals($from_depth, $to_depth) {

  global $color;
  global $subfolders, $subcount, $subunread, $subsize, $tab_cols;

  for ($lp = $from_depth; $lp > $to_depth; $lp--) {
    $indent = $lp > 0 ? "<TD BGCOLOR=\"$color[4]\" COLSPAN=$lp>&nbsp;</TD>"
                     : "";
    echo '<tr bgcolor="'.$color[0].'">'.$indent.'<td' .

         ($tab_cols - $lp > 3 ? " colspan=" . ($tab_cols - $lp - 3)
                              : "") . ">" .
         ($lp > 0 ? "" : "<B>");
     
     if ($subfolders[$lp] == 1 )
         printf ( _("%s Folder") , $subfolders[$lp]);
         else
         printf ( _("%s Folders") , $subfolders[$lp]);
     
         echo ($lp > 0 ? "" : "</B>") . "</TD>" .
         "<TD>" . ($lp > 0 ? "" : "<B>") .
         $subcount[$lp] .
         ($lp > 0 ? "" : "</B>") . "</TD>" .
         "<TD>" . ($lp > 0 ? "" : "<B>") .
         $subunread[$lp] .
         ($lp > 0 ? "" : "</B>") . "</TD>" .
         '<td colspan="3"></td>' .
         "<TD>" . ($lp > 0 ? "" : "<B>");
     if(isset($subsize[$lp])) 
             echo show_readable_size($subsize[$lp]);
         echo ($lp > 0 ? "" : "</B>") . "</td></tr>\n";

    $next_depth = $lp - 1;
    
    if(isset($subfolders[$next_depth]))
      $subfolders[$next_depth] = 1 + $subfolders[$lp];
    
    if(isset($subcount[$next_depth]))
      $subcount[$next_depth] += $subcount[$lp];
    
    if(isset($subunread[$next_depth]))
      $subunread[$next_depth] += $subunread[$lp];
    
    if(isset($subsize[$next_depth]))
      $subsize[$next_depth] += $subsize[$lp];
  }
}

