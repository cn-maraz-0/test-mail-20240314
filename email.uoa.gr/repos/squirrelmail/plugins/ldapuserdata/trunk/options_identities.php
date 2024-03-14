<?php
/**
 * options_identities.php
 *
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Copyright (c) 2003 Alexandros Vellis <avel@noc.uoa.gr>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Alexandros Vellis' Version for use with the ldapuserdata plugin and
 * attributes such as mailAlternateAddress, mailAuthorizedAddress.
 *
 * National & Kapodistrian University of Athens
 * http://email.uoa.gr
 *
 * Display Identities Options
 *
 */

if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    require_once(SM_PATH . 'include/validate.php');
}
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/identity.php');

$ldapidentities = $_SESSION['ldapidentities'];
$identities_map = $_SESSION['identities_map'];

/* User's current identity preferences */
$identity = getpref($data_dir, $username, 'identity');
$namepreferred = getpref($data_dir, $username, 'namepreferred');
$em_preferred = getpref($data_dir, $username, 'mailpreferred');


/* defining $selection */
if(isset($_POST['selection'])) {
    $selection = $_POST['selection'];

} elseif(!empty($em_preferred)) {
    if(in_array($em_preferred, $ldapidentities['main']['email_address'])){
        $selection = 'main';
    } else {
        $selection = $em_preferred;
    }
} else {
    $selection = 'main';
}


/* Default email identity (full_name, email_address) */
$full_name = getpref($data_dir, $username, 'full_name');
$email_address = getpref($data_dir, $username, 'email_address');
$id_default = $full_name.' &lt;'.$email_address.'&gt;';

if (isset($_POST['update'])) {

    if($selection == 'main') {
        removePref($data_dir, $username, 'identity');
        removePref($data_dir, $username, 'mailpreferred');
        removePref($data_dir, $username, 'namepreferred');
        if(isset($ldapidentities['main']['allowed_names'])){
            $_SESSION['alternatenames'] = $ldapidentities['main']['allowed_names'];
        }

    } elseif(in_array($selection, $identities_map)) {
        foreach($identities_map as $no=>$mail) {
            if($mail == $selection) {
                setPref($data_dir, $username, 'identity', $no);
                setPref($data_dir, $username, 'mailpreferred', $selection);
                /* Set a default name... */
                if(isset($ldapidentities[$selection]['allowed_names'])){
                    setPref($data_dir, $username, 'namepreferred', $ldapidentities[$selection]['allowed_names'][0]);
                }
            }
        }
        if(isset($ldapidentities[$selection]['allowed_names'])){
            $_SESSION['alternatenames'] = $ldapidentities[$selection]['allowed_names'];
        }
    
    } else {
        print "Wrong identity specified.";
        exit;
    }
    header('Location: ../../src/options.php?optpage=personal');
    exit;
}

displayPageHeader($color, 'None');

print '<br />
<table width="95%" align=center border=0 cellpadding=2 cellspacing=0>
<tr><td bgcolor="'.$color[0].'" align="center">
<b>';
print _("Options") . ' - ' .  _("Advanced Identities");

$prev = sq_bindtextdomain ('ldapudidentities', SM_PATH . 'plugins/ldapuserdata/locale');
textdomain ('ldapudidentities');

print '</b>

<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr><td bgcolor="'.$color[4].'" >

<p>'. _("This page allows you to choose between a default mail identity for this system, and one of the identities which you have been authorized to send mail as.") . '</p>

<p>'. _("Authorized mail identities are provided by the mail administrators on demand, in order to allow certain users to use third-party or non-returnable addresses.") . '</p>

<p>'. _("Note that you can always choose a specific identity <em>on-the-fly</em>, while composing a new mail.") . '</p>';


if(isset($ldapidentities)) {

    print '<form name="f" action="options_identities.php" method="post">';
    
    echo '<table width="95%" cols="1" align="center" cellpadding="2" cellspacing="0" border="0">';
    
    /* All email addresses */
    foreach($ldapidentities as $id => $info) {

        if($id == 'main') {
            /* Main (mail/mailalternate) Identities */
    
            print "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER>".
            '<strong>' . _("Main Identity") . '</strong>'.
                 "</TD></TR>";
            print "<TR><TD BGCOLOR=\"$color[0]\" >";


            print '<input type="radio" name="selection" id="identity_main" ';
            if($selection == 'main') {
                print 'checked="" ';
            }
            
            print 'value="main" />';
            
            print '<label for="identity_main">'.
            '<strong>' . _("Main Identity") . '</strong>'.
            '<p><blockquote>' . _("This is your main identity. You can choose from one of the alternate mail addresses that have been provided for your exclusive personal use:") . '</p><ul>'; 

            for($l=0; $l<sizeof($info['email_address']);$l++) {
                echo '<li>'.$info['email_address'][$l].'</li>';
            }
            echo '</ul><p>';

            if(isset($info['allowed_names'])) {
            
                echo '<p>'. _("Also, you can choose between the following names, to be displayed together with your email address:") . '</p>';

                print '<ul>';
                for($i=0;$i<sizeof($info['allowed_names']); $i++) {
                    print '<li>'.$info['allowed_names'][$i].'</li>';
                }
                print '</ul>';
                

            }

            print _("Your selection on these options can be made in the Personal Options Page.") .
            '</p></blockquote>'.
            '</label><br />';
    
            echo "</TD></TR>\n";
            echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";

        } else {

            /* Informational Text */
            if(!isset($mailauthorized_blurb)) {
                $mailauthorized_blurb = true;
    
    
                print "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>".
                    '<strong>' . _("Authorized Identities") . '</strong>'.
                     "</B></TD></TR>".

                "<TR><TD BGCOLOR=\"$color[0]\" >".

                '<p>'. _("The rest of the identities have been provided as a means for you to send mail using specific names and addresses. If other users on the Internet reply to you while you have used these email addresses, <em>it is not certain that the mail will return to the mailbox of this specific account</em>; <strong>you will have to check that specific mail account, or set up mail forwarding accordingly, by yourself</strong>. Also, it is possible that other users on this system or in the Internet are using the same email addresses as these.") . '</p>'.
                '<p>';
                // $ldapidentitiescontact = '210 727 5600 / helpdesk@noc.uoa.gr';
                $ldapidentitiescontact = 'email@edunet.gr';
                printf(_("For more information about authorized identities, please contact our support staff at: %s"), $ldapidentitiescontact);
                print '</p>';
            }


            /* mailauthorized Identities */
            print '<input type="radio" name="selection" id="identity_'.urlencode($id).'" '.
            'value="'.$id.'" ';

            if (isset($selection) && $selection == $id) {
                print 'checked="" ';
            }
            print '/> ';
            print '<label for="identity_'.urlencode($id).'">'.
            '<strong>' . _("Authorized Identity:") . ' ' . $id . '</strong>'.
            '<blockquote><p>';

            if(isset($info['allowed_names'])) {
                if(sizeof($info['allowed_names']) == 1) {
                    printf( _("Uses the name &quot;<em>%s</em>&quot;"), trim($info['allowed_names'][0]));
                } else {
                    print _("The following names have been authorized for this email address:");
                    print '<ul>';
                    for($i=0;$i<sizeof($info['allowed_names']); $i++) {
                        print '<li>'.$info['allowed_names'][$i].'</li>';
                    }
                    print '</ul>';
                }

            } elseif(isset($namepreferred)) {
                print $namepreferred;
            } else {
                print $full_name;
            }
            print '</p></blockquote>';

            // print ' &lt;'.htmlspecialchars($id).'&gt;';
            print '</label><br />';
            
            
        }
    }

    echo "</TD></TR>\n".
    "<tr><td bgcolor=\"$color[4]\" align=\"center\">".
    '<p style="text-align:center"><input type="submit" name="update" value="'._("Update Identity Selection").
    '"></p>'.
    "</td></tr>\n".
    '</form>';

}

$prev = sq_bindtextdomain ('squirrelmail', SM_PATH . 'locale');
textdomain ('squirrelmail');

?>
</table>
</td></tr></table>
</td></tr></table>
</body></html>
