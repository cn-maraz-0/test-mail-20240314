<?php
/**
  * SquirrelMail HTML Mail Plugin
  * Copyright (c) 2004-2005 Paul Lesneiwski <pdontthink@angrynerds.com>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage html_mail
  *
  */

/**
 * Load configuration file
 */
function hm_get_config() {
   $one = @include_once(SM_PATH . 'plugins/html_mail/config.php.sample');
   $two = @include_once(SM_PATH . 'plugins/html_mail/config.php');
   if (!$one && !$two) {
         echo sprintf(_("ERROR (%s): can't load config file"), 'html_mail');
         exit(1);
   }
}


/** 
 * Inserts controls on the compose page that let the user
 * switch between HTML and text on the fly.
 */
function html_mail_choose_type_on_the_fly() {
   sq_bindtextdomain('html_mail', SM_PATH . 'locale');
   textdomain('html_mail');

   if (html_area_is_on_and_is_supported_by_users_browser()) {
      echo '<br/><input type="radio" name="strip_html_send_plain" id="strip_html_send_plain_html" CHECKED value="0" /><label for="strip_html_send_plain_html">' . _("HTML")
         . '</label><input type="radio" onClick="if (!confirm(\'' . _("Warning: all special formatting will be lost.  Are you sure you want to send your message in plain text format?") . '\')) document.compose.strip_html_send_plain[0].checked = true;" name="strip_html_send_plain" id="strip_html_send_plain_plain" value="1" /><label for="strip_html_send_plain_plain">' . _("Plain Text") . '</label> &nbsp;';

      global $comp_in_html;
      sqgetGlobalVar('comp_in_html', $comp_in_html, SQ_FORM);

      if ($comp_in_html) {
         echo '<input type="hidden" name="comp_in_html" value="1" />';
      }
   }

   textdomain('squirrelmail');
}

function html_mail_choose_type_on_the_fly_important() {
    global $javascript_on, $PHP_SELF, $comp_in_html;
    
    if(!$javascript_on || !html_area_is_supported_by_users_browser()) return;

    sq_bindtextdomain('html_mail', SM_PATH . 'locale');
    textdomain('html_mail');

    sqgetGlobalVar('comp_in_html', $comp_in_html, SQ_FORM);

    if(!html_compose_is_on()) {
        echo '<input type="button" style="font-weight: normal;" onclick="window.location=\'' . $PHP_SELF . (strpos($PHP_SELF, '?') === FALSE ? '?' : '&amp;') . 'comp_in_html=1\'" value="'. _("Compose in HTML") . '" />';
    }
    textdomain ('squirrelmail');
}

/**
 * "Turns on" this plugin if the compose page is currently
 * being shown
 */
function html_mail_header_do() {
   global $PHP_SELF;
   if (stristr($PHP_SELF, 'compose.php')) {
      html_mail_turn_on_htmlarea();
   }
}


/** 
 * Do the actual insertion of the enhanced text editor
 *
 * Also check that this plugin is in the correct order in $plugins array
 */
function html_mail_turn_on_htmlarea()  {
   global $plugins, $color, $customStyle, $use_spell_checker, $fully_loaded, 
          $username, $data_dir;


   // list of plugins that should come BEFORE this plugin (any that will
   // modify outgoing messages on the compose_send hook)
   //
   $check_for_previous_plugins = array(
      'gpg',    // is this one necessary?  oh well, no reason we can't play it safe
      'hancock', 
      'taglines',
      'quote_tools',
      'email_footer',
   );

   // now just make sure html_mail comes after all those plugins listed above
   $my_plugin_index = array_search('html_mail', $plugins);
   foreach ($check_for_previous_plugins as $plug) {
      $i = array_search($plug, $plugins);
      if (is_numeric($i) && $i > $my_plugin_index) { // array_search returns NULL before PHP 4.2.0, FALSE after that
         sq_bindtextdomain('html_mail', SM_PATH . 'locale');
         textdomain('html_mail');

         echo "\n\n<html><body><h2><font color='red'>" 
            . sprintf(_("FATAL: HTML_Mail plugin must come AFTER %s in plugins array.  Please modify plugin order using conf.pl or by editing config/config.php"), $plug)
            . '</font></h2></body></html>';
         exit;
      }
   }

   hm_get_config();
   if (html_area_is_on_and_is_supported_by_users_browser()) {
      echo '<script type="text/javascript" src="' . SM_PATH . 'plugins/html_mail/tiny_mce/tiny_mce.js"></script>';
   }
}

/**
 * Inserts extra JavaScript at bottom of compose page
 * that is needed by the enhanced editor
 *
 * @todo Enable spellchecker tinyMCE plugin for IE.
 */
function html_mail_footer() {
   global $username, $data_dir;
   hm_get_config();

   if (html_area_is_on_and_is_supported_by_users_browser()) {
      // replace newlines with <br>'s in body
      // (comment out these three lines if you 
      // want to do this in html_mail_compose_form_do()
      // and miss automated signatures)
      echo '<script language="javascript" type="text/javascript">' . "\n<!--\n"
         . 'document.compose.body.value = document.compose.body.value.replace(/<br *\/?>(\r\n|\r|\n)/g, "\n");' . "\n"
         . 'document.compose.body.value = document.compose.body.value.replace(/<\/p>(\r\n|\r|\n)/g, "</p>");' . "\n"
         . 'document.compose.body.value = document.compose.body.value.replace(/\n/g, "<br />");'
         . "\n// -->\n</script>";

      global $squirrelmail_language, $editor_height, $editor_size, $default_html_editor_height, $reply_focus;
      if (!$editor_height) $editor_height = $default_html_editor_height;

      $lang = substr($squirrelmail_language, 0, strpos($squirrelmail_language, '_'));
      if (empty($lang) || !file_exists(SM_PATH . 'plugins/html_mail/tiny_mce/langs/' . $lang . '.js'))
          $lang = 'en';

      $ua = html_mail_browser_info();
      if(isset($ua['msie'])) {
          $msie = true;
      } else {
          $msie = false;
      }

      echo '
    <script defer type="text/javascript">
    tinyMCE.init({
        entity_encoding: "raw",
        theme: "advanced",
        skin: "o2k7",
        language: "'.$lang.'",
        mode: "specific_textareas",
        textearea_trigger: "body",
        plugins: "safari,fullpage,paste,autosave'. ($msie ? '' : ',spellchecker') . '",
        spellchecker_languages: "+English=en,Greek=el,French=fr,German=de,Italian=it",
        gecko_spellcheck : true,
        fullpage_encodings: "Unicode (UTF-8)=utf-8,ASCII (us-ascii)=us-ascii",
        fullpage_default_encoding: "utf-8",
        fullpage_default_title: "",'
        . ( ($reply_focus == 'select' || $reply_focus == 'focus') ? 'auto_focus: "body",' : '' ) .'
        theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,image,|,forecolor,backcolor,|,charmap'. ($msie ? ',|,spellchecker' : ',|,spellchecker') .'",
        theme_advanced_buttons3: "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_fonts : "Andale Mono=andale mono,times;"+
            "Arial=arial,helvetica,sans-serif;"+
            "Arial Black=arial black,avant garde;"+
            "Book Antiqua=book antiqua,palatino;"+
            "Consolas=consolas,monospace;"+
            "Corbel=corbel,sans-serif;"+
            "Courier New=courier new,courier;"+
            "Georgia=georgia,palatino;"+
            "Helvetica=helvetica;"+
            "Impact=impact,chicago;"+
            "Symbol=symbol;"+
            "Tahoma=tahoma,arial,helvetica,sans-serif;"+
            "Terminal=terminal,monaco;"+
            "Times New Roman=times new roman,times;"+
            "Trebuchet MS=trebuchet ms,geneva;"+
            "Ubuntu=ubuntu,arial,sans-serif;"+
            "Verdana=verdana,geneva;"+
            "Webdings=webdings;"+
            "Wingdings=wingdings,zapf dingbats",
            '.($msie ? '' : '
            setup: function(ed) {
              ed.onSetContent.add(function(ed, o) {
                   // ed.plugins.spellchecker._removeWords();
                   // tinyMCE.get("body").plugins.spellchecker._removeWords();
                   // setTimeout("tinyMCE.get(\"body\").plugins.spellchecker._removeWords();", 1000);
              });
            },
            ') . '
            cleanup_on_startup : true,
            // Cleanup / output
            apply_source_formatting : true,
            // convert_newlines_to_brs
            element_format : "html"
            // theme_advanced_buttons3_add: "fullpage"
    });
    </script>
    ';

    global $html_mail_display_hint;
    if(isset($html_mail_display_hint)) {
        html_mail_display_attachment_hint();
    }

    /*
         if(tinyMCE != undefined) {
            tinyMCE.execCommand('mceFocus',false,'body');
        } else {
            $("#body")[0].focus();
        }
     */

   }
}

function html_mail_display_attachment_hint() {
    global $compose_new_win, $color;
    if ($compose_new_win == '1') {
        echo '<table align="center" bgcolor="'.$color[0].'" width="100%" border="0">'."\n" ;
    } else {
        echo '<table align="center" cellspacing="0" border="0" width="80%">' . "\n";
    }
    echo '<tr><td align="left" bgcolor="'.$color[9].'" style="text-align: center;">';

    echo _("Note: If you are resuming a draft, forwarding a message or editing a message as new, it might be advisable to delete unwanted attachments above."); 

    echo '</td></tr>';
    echo '</table><br/>';
}

/**
  * Turns off squirrelspell when the user is composing 
  * HTML-formatted email, since squirrelspell will
  * choke on the HTML.  This function also reformats the
  * message body as needed (such as getting the HTML 
  * part to edit if user settings demand it).
  *
  */
function html_mail_compose_form_do() {
   global $squirrelmail_plugin_hooks;

   if (html_area_is_on_and_is_supported_by_users_browser()) {
      if (!empty($squirrelmail_plugin_hooks['compose_button_row']['squirrelspell']))
         unset($squirrelmail_plugin_hooks['compose_button_row']['squirrelspell']);

      // need to encode body text so > signs and other stuff don't 
      // get interpreted incorrectly as HTML entities
      //
      // but only need to do this once; don't repeat if user just
      // clicked to add a signature or upload a file or add addresses, etc
      //
      global $sigappend, $from_htmladdr_search, $restrict_senders_error_no_to_recipients,
             $restrict_senders_error_too_many_recipients;
      sqgetGlobalVar('sigappend', $sigappend, SQ_FORM);
      sqgetGlobalVar('from_htmladdr_search', $from_htmladdr_search, SQ_FORM);
      sqgetGlobalVar('restrict_senders_error_too_many_recipients', 
                     $restrict_senders_error_too_many_recipients, SQ_FORM);
      sqgetGlobalVar('restrict_senders_error_no_to_recipients', 
                     $restrict_senders_error_no_to_recipients, SQ_FORM);
      if ($sigappend != 'Signature'
       && $from_htmladdr_search != 'true'
       && $restrict_senders_error_no_to_recipients != 1
       && $restrict_senders_error_too_many_recipients != 1
       && empty($_FILES['attachfile'])) {

         global $username, $key, $imapServerAddress, $imapPort, $imapConnection,
                $mailbox, $uid_support, $messages, $passed_id, $data_dir,
                $passed_ent_id, $smaction, $color, $wrap_at, $body;

         $aggressive_reply = getPref($data_dir, $username, 'html_mail_aggressive_reply', 0);
         $aggressive_reply_with_unsafe_images = getPref($data_dir, $username, 'html_mail_aggressive_reply_with_unsafe_images', 0);

         sqgetGlobalVar('messages',     $messages, SQ_SESSION);
         sqgetGlobalVar('smaction',     $smaction, SQ_FORM);
         sqgetGlobalVar('HTTP_REFERER', $referer,  SQ_SERVER);
         sqgetGlobalVar('key',          $key,      SQ_COOKIE);

         if ($smaction == 'reply' || $smaction == 'reply_all' || $smaction == 'forward'
          || $smaction == 'draft' || $smaction == 'edit_as_new') {

            // we can skip all this code that tries to get a HTML part
            // if user doesn't want it anyway
            //
            $treatAsPlainText = TRUE;
            if ($aggressive_reply 
             || (!empty($referer) && strpos($referer, 'view_as_html=1') !== FALSE)
             || getPref($data_dir, $username, 'show_html_default') ) {

               $treatAsPlainText = FALSE;

               $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
               $mbx_response   = sqimap_mailbox_select($imapConnection, $mailbox, false, false, true);
               $uidvalidity    = $mbx_response['UIDVALIDITY'];
   
               if (!isset($messages[$uidvalidity])) 
                  $messages[$uidvalidity] = array();

               // grab message from session cache or from IMAP server
               if (!isset($messages[$uidvalidity][$passed_id]) || !$uid_support) {
                  $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);
                  $message->is_seen = true;
                  $messages[$uidvalidity][$passed_id] = $message;
               } else {
                  $message = $messages[$uidvalidity][$passed_id];
               }

               if($smaction == 'forward' || $smaction == 'draft' || $smaction == 'edit_as_new') {
                   for($i=0; $i < sizeof($message->entities); $i++) {
                       if($message->entities[$i]->type0 == 'text' && $message->entities[$i]->type1 =='html') {
                           global $html_mail_display_hint;
                           $html_mail_display_hint = true;
                           // TODO: this should be handled in compose.php logic probably; remove the unwanted
                           // message entity altogether.
                       }
                   }
               }

               $orig_header = $message->rfc822_header;

               // is there an html part?  if so, use it to redefine $body
               //
               $ent_ar = $message->findDisplayEntity(array(), array('text/html'), TRUE);
               if (!empty($ent_ar)) {

                  // from compose.php (mutilated and modified...)

                  global $languages, $squirrelmail_language, $default_charset;
                  set_my_charset();

                  $unencoded_bodypart = mime_fetch_body($imapConnection, $passed_id, $ent_ar[0]);
                  $body_part_entity = $message->getEntity($ent_ar[0]);
                  $bodypart = decodeBody($unencoded_bodypart,
                                         $body_part_entity->header->encoding);

                  // handle HTML 
                  //
                  // do this after we call magicHTML()...   $bodypart = str_replace("\n", ' ', $bodypart);


                  // TODO: next line won't make a difference in ultimate result,
                  //       although we should keep an eye on it if problems arise,
                  $bodypart = str_replace(array('&nbsp;','&gt;','&lt;'),array(' ','>','<'),$bodypart);

                  // TODO: we can't strip out tags, cuz we want the tags!
                  //       but we don't want to be indescriminate, so we
                  //       use magicHTML() below... it is possible that
                  //       some people with extensive HTML mails will complain
                  //       about what magicHTML() does to their mail...
                  //$bodypart = strip_tags($bodypart);

                  // trick magicHTML() if needed by injecting info into $_GET
                  //
                  if ($aggressive_reply_with_unsafe_images 
                   || (!empty($referer) && strpos($referer, 'view_unsafe_images=1') !== FALSE)) {
                     global $_GET;
                     if (!check_php_version(4,1)) 
                     {
                        global $HTTP_GET_VARS;
                        $_GET = $HTTP_GET_VARS;
                     }
                     $_GET['view_unsafe_images'] = 1;
                  }

                  $bodypart = magicHTML($bodypart, $passed_id, $message, $mailbox, FALSE); // last param added in 1.5.1
                  $bodypart = str_replace("\n", ' ', $bodypart);


                  if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
                      function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
                     if (mb_detect_encoding($bodypart) != 'ASCII') 
                        $bodypart = $languages[$squirrelmail_language]['XTRA_CODE']('decode', $bodypart);
                  }
      
                  // charset encoding in compose form stuff
                  if (isset($body_part_entity->header->parameters['charset'])) 
                     $actual = $body_part_entity->header->parameters['charset'];
                  else
                     $actual = 'us-ascii';
         
                  if ( $actual && is_conversion_safe($actual) && $actual != $default_charset) {
                     $bodypart = charset_convert($actual,$bodypart,$default_charset,false);
                  }
                  // end of charset encoding in compose

                  $body = $bodypart;

                  // NOTE: here is the alternative code we still need if we don't use the above
                  //
                  if ($smaction == 'forward')
                  {
                     $body = getforwardHeader($orig_header) . $body;
                     $body = '<br />' . $body;
                  }
                  if ($smaction == 'reply' || $smaction == 'reply_all')
                  {
                     $from =  (is_array($orig_header->from)) ? $orig_header->from[0] : $orig_header->from;
                     $body = getReplyCitation($from , $orig_header->date) . $body;
                  }
               }
               else $treatAsPlainText = TRUE;
   
            }

            // plain text messages: need to make sure HTML entities
            // don't get interpreted incorrectly...
            if ($treatAsPlainText) {
               // email addresses in the form "name" <address> in 
               // the original message lose the address since it
               // is mistaken for a HTML tag
               $body = htmlspecialchars($body);
            }
         }

         // for some strange reason, the subject line
         // doesn't get a <br> before it in the forward header
         // unless there is a space after the newline.  
         // argh! From: suffers from the same problem
         $body = preg_replace(array('/-----\s' . _("Subject") . '/', '/\s' . _("From") . '/'), 
                              array("-----\n" . _("Subject"), "\n" . _("From")), $body);

      }
   }
}

/**
 * @return boolean
 */
function html_compose_is_on() {
    global $username, $data_dir, $javascript_on, $comp_in_html;
    sqgetGlobalVar('comp_in_html', $comp_in_html, SQ_FORM);
    $type = getPref($data_dir, $username, 'compose_window_type', '');
    return ($javascript_on && ($type == 'html' || $comp_in_html));
}

/**
 * @return boolean
 */
function html_area_is_supported_by_users_browser() {
    static $html_area_is_supported_by_users_browser = -1;
    if($html_area_is_supported_by_users_browser == 1) {
        return true;
    } elseif($html_area_is_supported_by_users_browser == 0) {
        return false;
    }

    $ua = html_mail_browser_info();
    $ret = (
           (isset($ua['msie']) && $ua['msie'] >= 6.0)
        || (isset($ua['firefox']))
        || (isset($ua['safari']))
        || (isset($ua['webkit']))
        || (isset($ua['opera']) && $ua['opera'] >= 9.5)
        || (isset($ua['netscape']))
        || (isset($ua['konqueror']))
        || (isset($ua['gecko']) && $ua['gecko'] >= 20030624)
    );
    return $ret;
}


/**
 * @return boolean
 */
function html_area_is_on_and_is_supported_by_users_browser() {
   return (html_compose_is_on() && html_area_is_supported_by_users_browser() );
}

/**
  * Show user configuration items
  *
  */
function html_mail_display($hookName) {
   // 1.4.x - 1.5.0:  options go on display options page
   // 1.5.1  and up:  options go on compose options page
   //
   if (check_sm_version(1, 5, 1) && $hookName[0] != 'options_compose_inside')
      return;
   if (!check_sm_version(1, 5, 1) && $hookName[0] != 'options_display_inside')
      return;

   global $username, $data_dir, $email_type,
          $html_mail_aggressive_reply, $default_aggressive_html_reply,
          $html_mail_aggressive_reply_with_unsafe_images,
          $default_aggressive_reply_with_unsafe_images;

   hm_get_config();

   $email_type = getPref($data_dir, $username, 'compose_window_type', '');
   $html_mail_aggressive_reply = getPref($data_dir, $username, 'html_mail_aggressive_reply', $default_aggressive_html_reply);
   $html_mail_aggressive_reply_with_unsafe_images = getPref($data_dir, $username, 'html_mail_aggressive_reply_with_unsafe_images', $default_aggressive_reply_with_unsafe_images);

   sq_bindtextdomain('html_mail', SM_PATH . 'locale');
   textdomain('html_mail');

    echo html_tag( 'tr', "\n".
            html_tag( 'td',
                '<b>' . _("Compose in Rich Text (HTML)") . '</b>' ,
                'center' ,'', 'valign="middle" colspan="2" nowrap' )
        ) ."\n";

   // email_type
   echo '<tr><td align=right valign="top"><br />'
      . _("Default Email Composition Format:") . "</td>\n"
      . '<td><br /><input type="radio" value="plain" name="email_type" id="compInPlain" ';

   if ($email_type == 'plain' || $email_type == '') echo 'CHECKED';

   echo '><label for="compInPlain">&nbsp;' . _("Plain Text") . "</label>\n"
      . '&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" value="html" id="compInHTML" name="email_type" ';

   if ($email_type == 'html') echo 'CHECKED';

   echo '><label for="compInHTML">&nbsp;' . _("HTML") . "</label>\n".
      '</td></tr>' . "\n";

   // html_mail_aggressive_reply
   echo '<tr><td align=right valign=top>'
      . _("Only Reply In HTML When Viewing HTML Format:") . "</td>\n"
      . '<td><table border="0"><tr><td>'
      . '<input type="radio" value="0" name="html_mail_aggressive_reply" id="aggressivehtmlreplyno" ';

   if (!$html_mail_aggressive_reply) echo 'CHECKED';

   echo '><label for="aggressivehtmlreplyno">&nbsp;' . _("Yes") . "</label>\n"
      . '</td><td><table border="0"><tr><td><input type="radio" value="1" id="aggressivehtmlreplyyes" name="html_mail_aggressive_reply" ';

   if ($html_mail_aggressive_reply) echo 'CHECKED';

   echo '></td><td><label for="aggressivehtmlreplyyes">&nbsp;' . _("No") . ' ' . _("(Always Attempt To Reply In HTML)") . "</label></td></tr></table></td></tr></table>\n".
      '</td></tr>' . "\n";

   // html_mail_aggressive_reply_with_unsafe_images
   echo '<tr><td align=right valign=top>'
      . _("Only Allow Unsafe Images In HTML Replies When Viewing Unsafe Images:") . "</td>\n"
      . '<td><table border="0"><tr><td>'
      . '<input type="radio" value="0" name="html_mail_aggressive_reply_with_unsafe_images" id="aggressivereplyunsafeno" ';

   if (!$html_mail_aggressive_reply_with_unsafe_images) echo 'CHECKED';

   echo '><label for="aggressivereplyunsafeno">&nbsp;' . _("Yes") . "</label>\n"
      . '</td><td><table border="0"><tr><td><input type="radio" value="1" id="aggressivereplyunsafeyes" name="html_mail_aggressive_reply_with_unsafe_images" ';

   if ($html_mail_aggressive_reply_with_unsafe_images) echo 'CHECKED';

   echo '></td><td><label for="aggressivereplyunsafeyes">&nbsp;' . _("No") . ' ' . _("(Always Include Unsafe Images)") . "</label></td></tr></table></td></tr></table>\n".
      '</td></tr>' . "\n";

   textdomain ('squirrelmail');
}



/**
 * Save user configuration items
 */
function html_mail_save($hookName) {
   if (check_sm_version(1, 5, 1) && $hookName[0] != 'options_compose_save')
      return;
   if (!check_sm_version(1, 5, 1) && $hookName[0] != 'options_display_save')
      return;

   global $username, $data_dir, $email_type,
          $html_mail_aggressive_reply, $html_mail_aggressive_reply_with_unsafe_images;

   hm_get_config();

   sqgetGlobalVar('email_type', $email_type, SQ_FORM);
   sqgetGlobalVar('html_mail_aggressive_reply', $html_mail_aggressive_reply, SQ_FORM);
   sqgetGlobalVar('html_mail_aggressive_reply_with_unsafe_images', 
                  $html_mail_aggressive_reply_with_unsafe_images, SQ_FORM);

   setPref($data_dir, $username, 'html_mail_aggressive_reply_with_unsafe_images', $html_mail_aggressive_reply_with_unsafe_images);
   setPref($data_dir, $username, 'html_mail_aggressive_reply', $html_mail_aggressive_reply);
   setPref($data_dir, $username, 'compose_window_type', $email_type);
}

/**
 * Changes outgoing message format to include multipart html and text parts if 
 * needed
 */
function html_mail_alter_type_do(&$argv) {
   // change outgoing encoding if supported/turned on
   if (html_area_is_on_and_is_supported_by_users_browser()) {

      $message = &$argv[1];

      global $strip_html_send_plain, $base_uri;
      sqgetGlobalVar('strip_html_send_plain', $strip_html_send_plain, SQ_FORM);
      $serverAddress = get_location(); 
      if (strpos($serverAddress, '/') !== FALSE) 
         $serverAddress = substr($serverAddress, strpos($serverAddress, '/') + 2);
      if (strpos($serverAddress, '/') !== FALSE) 
         $serverAddress = substr($serverAddress, 0, strpos($serverAddress, '/'));


      // user wants to send this one in plain text,
      // so we have to:
      // 1) convert <p> and <br> into newlines
      // 2) strip the HTML out
      // 3) drop comments generated by html-stripping mechanism
      //
      if ($strip_html_send_plain) {
         if (is_array($message->entities) && sizeof($message->entities) > 0)  {
            $msg = str_replace(array('<!-- begin sanitized html -->', '<!-- end sanitized html -->'), '', sq_sanitize( preg_replace('/(<br\s*\/?\s*>|<p\s*>)/i', "\n", $message->entities[0]->body_part), array(TRUE), array(), array(), array(), array(), array(), array(), array(), array(), array()));
            // decode special chars..
            $message->entities[0]->body_part = my_html_entity_decode($msg);
         } else {
            $msg = str_replace(array('<!-- begin sanitized html -->', '<!-- end sanitized html -->'), '', sq_sanitize( preg_replace('/(<br\s*\/?\s*>|<p\s*>)/i', "\n", $message->body_part), array(TRUE), array(), array(), array(), array(), array(), array(), array(), array(), array()));
            // decode special chars..
            $message->body_part = my_html_entity_decode($msg);
         }
      } else {
          // otherwise, set the outgoing content type correctly and add a 
          // text/plain mime part, which means non-multipart messages 
          // need to be converted to multipart...
         // figure out how images should be linked (HTTP/HTTPS)
         global $outgoing_image_uri_https;
         hm_get_config();
         if ($outgoing_image_uri_https == 1) {
            $http = 'http';
         } else if ($outgoing_image_uri_https == 2) {
            $http = 'https';
         } else {
            if (isset($_SERVER['SERVER_PORT']))
               $serverPort = $_SERVER['SERVER_PORT'];
            else
               $serverPort = 0;
            $http = ($serverPort == $outgoing_image_uri_https ? 'https' : 'http');
         }

         // already multipart; change original message part to 
         // multipart/alternative and add a plain text and html
         // part therein 
         //
         if (is_array($message->entities) && sizeof($message->entities) > 0) {
            $plainText = str_replace(array('<!-- begin sanitized html -->', '<!-- end sanitized html -->'), '', sq_sanitize( preg_replace('/(<br\s*\/?\s*>|<p\s*>)/i', "\n", $message->entities[0]->body_part), array(TRUE), array(), array(), array(), array(), array(), array(), array(), array(), array()));
            $plainText = my_html_entity_decode($plainText);

            // convert relative URIs to absolute; also remove URIs
            // to download.php (embedded images, etc) until we 
            // find the time to code a way to forward on those images
            //
            $message->entities[0]->body_part 
              = preg_replace(array('|src=(["\'])' . $base_uri . '|si', 
                                   '|<img.*src=.*/src/download\.php.*?>|si'), 
                             array('src=\1' . $http . '://' . $serverAddress . $base_uri,
                                   '[IMAGE REMOVED]'), 
                             $message->entities[0]->body_part);

            $message->entities[0]->mime_header->type1 = 'html';
            $htmlTextPart = $message->entities[0];

            // break connection between $htmlTextPart and $message
            unset($message->entities[0]);
            // create new message part in place of removed one
            $message->entities[0] = new Message();
            $message->entities[0]->mime_header = new MessageHeader();

            // tag that message part as multipart alternative
            $message->entities[0]->mime_header->type0 = 'multipart';
            $message->entities[0]->mime_header->type1 = 'alternative';
            $message->entities[0]->mime_header->encoding = '';
            $message->entities[0]->mime_header->parameters = array();
            $message->entities[0]->body_part = '';

            // gets us a different message boundary 
            $message->entities[0]->entity_id = 'usf' . mt_rand(1000, 9999);

            // create new plaintext message
            $plainTextPart = new Message();
            $plainTextPart->body_part = $plainText;
            $mime_header = new MessageHeader;
            $mime_header->type0 = 'text';
            $mime_header->type1 = 'plain';
            $mime_header->encoding = $message->entities[0]->mime_header->encoding;
            $mime_header->parameters = $message->entities[0]->mime_header->parameters;
            $plainTextPart->mime_header = $mime_header;

            // add plain text and html entities to multipart/alternative message
            $message->entities[0]->addEntity($plainTextPart);
            $message->entities[0]->addEntity($htmlTextPart);

         } else {

             // not multipart; convert to multipart, change original message
             // to html and add text/plain part
            $plainText = str_replace(array('<!-- begin sanitized html -->', '<!-- end sanitized html -->'), '', sq_sanitize( preg_replace('/(<br\s*\/?\s*>|<p\s*>)/i', "\n", $message->body_part), array(TRUE), array(), array(), array(), array(), array(), array(), array(), array(), array()));
            $plainText = my_html_entity_decode($plainText);

            // convert relative URIs to absolute; also remove URIs
            // to download.php (embedded images, etc) until we 
            // find the time to code a way to forward on those images
            $message->body_part 
              = preg_replace(array('|src=(["\'])' . $base_uri . '|si', 
                                   '|<img.*src=.*/src/download\.php.*?>|si'), 
                             array('src=\1' . $http . '://' . $serverAddress . $base_uri,
                                   '[IMAGE REMOVED]'), 
                             $message->body_part);

            $htmlTextPart = new Message();
            $htmlTextPart->body_part = $message->body_part;
            $htmlPartMime_header = new MessageHeader;
            $htmlPartMime_header->type0 = 'text';
            $htmlPartMime_header->type1 = 'html';
            $htmlPartMime_header->encoding = $message->rfc822_header->encoding;
            $htmlPartMime_header->parameters = $message->rfc822_header->content_type->properties;
            $htmlTextPart->mime_header = $htmlPartMime_header;

            $plainTextPart = new Message();
            $plainTextPart->body_part = $plainText;
            $plainPartMime_header = new MessageHeader;
            $plainPartMime_header->type0 = 'text';
            $plainPartMime_header->type1 = 'plain';
            $plainPartMime_header->encoding = $message->rfc822_header->encoding;
            $plainPartMime_header->parameters = $message->rfc822_header->content_type->properties;
            $plainTextPart->mime_header = $plainPartMime_header;

            // clear out some parts of the original non-multipart message
            //
            $message->rfc822_header->encoding = '';
            $message->rfc822_header->content_type->type0 = 'multipart';
            $message->rfc822_header->content_type->type1 = 'alternative';
            $message->rfc822_header->content_type->properties = array();
            $message->body_part = '';

            $message->entities = array($plainTextPart, $htmlTextPart);
         }
      }

      return $message;
   }
}

function my_html_entity_decode($text) {
   if (function_exists('html_entity_decode'))
      return html_entity_decode($text);

   // copied from http://us3.php.net/preg-replace
   $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                    "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                    "'([\r\n])[\s]+'",                 // Strip out white space
                    "'&(quot|#34);'i",                 // Replace html entities
                    "'&(amp|#38);'i",
                    "'&(lt|#60);'i",
                    "'&(gt|#62);'i",
                    "'&(nbsp|#160);'i",
                    "'&(iexcl|#161);'i",
                    "'&(cent|#162);'i",
                    "'&(pound|#163);'i",
                    "'&(copy|#169);'i",
                    "'&#(\d+);'e");                    // evaluate as php

   $replace = array ("",
                     "",
                     "\\1",
                     "\"",
                     "&",
                     "<",
                     ">",
                     " ",
                     chr(161),
                     chr(162),
                     chr(163),
                     chr(169),
                     "chr(\\1)");

   return preg_replace ($search, $replace, $text);
}

/**
 * Wraps text at $wrap characters while preserving HTML tags
 *
 * Has a problem with special HTML characters, so call this before
 * you do character translation.
 *
 * Specifically, &#039 comes up as 5 characters instead of 1.
 * This should not add newlines to the end of lines.
 */
function sqHTMLWordWrap(&$line, $wrap) {
    global $languages, $squirrelmail_language;

    if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
        function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
        if (mb_detect_encoding($line) != 'ASCII') {
            $line = $languages[$squirrelmail_language]['XTRA_CODE']('wordwrap', $line, $wrap);
            return;
        }
    }

    ereg("^([\t >]*)([^\t >].*)?$", $line, $regs);
    $beginning_spaces = $regs[1];
    if (isset($regs[2])) {
        $words = explode(' ', $regs[2]);
    } else {
        $words = '';
    }

    // pull words back together if they have split up a tag
    //
    $newWords = array();
    $newWord = '';
    foreach ($words as $word)
    {
       $newWord .= ' ' . $word;
       
       // this is a bit simplistic; a message might have 
       // a less than sign without it being an opening
       // tag marker, but we'll go with this since grepping
       // for all possible tags is much more trouble and 
       // there are probably not many messages where this 
       // will be a problem
       //
       // this won't work:  if tag doesn't end in next word segment...
       //if (strpos($word, '<') === FALSE)
       //
       $LTcount = preg_match_all('/</', $newWord, $junk);
       $GTcount = preg_match_all('/>/', $newWord, $junk);
       if ($LTcount == $GTcount)
       {
          $newWords[] = $newWord;
          $newWord = '';
       }
       
    }
    $words = $newWords;

    $i = 0;
    $line = $beginning_spaces;

    while ($i < count($words)) {
        /* Force one word to be on a line (minimum) */
        $line .= $words[$i];
        $line_len = strlen($beginning_spaces) + strlen(strip_tags($words[$i])) + 2;
        if (isset($words[$i + 1]))
            $line_len += strlen(strip_tags($words[$i + 1]));
        $i ++;

        /* Add more words (as long as they fit) */
        while ($line_len < $wrap && $i < count($words)) {
            $line .= ' ' . $words[$i];
            $i++;
            if (isset($words[$i]))
                $line_len += strlen(strip_tags($words[$i])) + 1;
            else
                $line_len += 1;
        }

        /* Skip spaces if they are the first thing on a continued line */
        while (!isset($words[$i]) && $i < count($words)) {
            $i ++;
        }

        /* Go to the next line if we have more to process */
        if ($i < count($words)) {
            $line .= "<br />";
        }
    }
}


/**
 * User-Agent parsing; mainly used for detection of IE (what else?)
 *
 * @return array Something like Array( [safari] => 532.8) or 
 *               Array( [msie] => 8.0)
 * @see http://www.php.net/manual/en/function.get-browser.php#92310
 */
function html_mail_browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?P<browser>' . join('|', $known) .
    ')[/ ]+(?P<version>[0-9]+(?:\.[0-9]+)?)#';

  // Find all phrases (or return empty array if none found)
  if (!preg_match_all($pattern, $agent, $matches)) return array();

  // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
  // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
  // in the UA).  That's usually the most correct.
  $i = count($matches['browser'])-1;
  return array($matches['browser'][$i] => $matches['version'][$i]);
}

