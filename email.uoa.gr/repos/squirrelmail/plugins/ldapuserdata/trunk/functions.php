<?php
/**
 *  SquirrelMail LDAP Prefs Backend Plugin (ldapuserdata)
 *  By Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Copyright (c) 1999-2009 The SquirrelMail Project Team
 * and Alexandros Vellis <avel@users.sourceforge.net>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * $Id: functions.php,v 1.18 2007/06/14 12:04:56 avel Exp $
 *
 * @package plugins
 * @subpackage ldapuserdata
 */
   
/**
 * This file contains the main plugin functions that connect to, read from and
 * write to LDAP.
 */
require (SM_PATH . "plugins/ldapuserdata/config.php");

/**
 * Convenience function for connecting to LDAP.
 *
 * This is my convenience function for connecting to the LDAP database.  
 *
 * @param str $bind Who to connect as to LDAP. One of squirrel, or manager, or
 * anonymous, or uid to search for.  Note: 'user' is currently broken.
 * @param bool $master If I need to connect to Master LDAP database for writes.
 * @return object LDAP handle.
 * @todo Fix 'bind as user'; An ldap search on the user DN must first be done.
 */
function ldapuserdata_ldap_connect ($bind = 'squirrel', $master = false) {
    
    global $ldap_host, $ldap_master_host, $username, $ldap_base_dn,
    $ldap_network_timeout, $domain;

    if($master) {
        $ldap_host = $ldap_master_host;
    }
    $ldap_error = 0;

    if (!($ldap = ldap_connect($ldap_host))) {
        $errormsg = "Could not connect to LDAP!";
        exit;
    }

    if (!ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3)) { 
        $errormsg = "Failed to set protocol version to 3";
        exit;
    }

    /* if (!$master) {
        if (!ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, $ldap_network_timeout)) {
            $errormsg = "Failed to set network timeout.\n";
        }
    }*/
    
    switch ($bind) {
        /* Broken - TODO.  This will be the 'default' in the future. */
        case 'user':
            global $login_password; /* FIXME, what's the name again? */
            if (!($search_result = ldap_search($ldap, $ldap_base_dn, 'UID='. ldapspecialchars($uid), array('uid')))) {
                $errormsg = 'Could not do anonymous search in LDAP Directory.<br/>'.
                    'So, binding as user cannot be done here.<br/>'.
                    'Please contact your administrator.';
            }
            $entries = ldap_get_entries($ldap, $search_result);
            if (!is_array($entries[0])) {
                $errormsg = "Error while searching for $uid:<br /> " .
                "The username and/or password you have provided is incorrect.";
                session_destroy();
                exit;
            }

            $binddn =  $entries[0]['dn'];
            $bind_result = ldap_bind( $ldap, $binddn, $login_password);
            break;

        case 'manager':
              global $ldap_manager_dn, $ldap_manager_pw;
            $bind_result = ldap_bind( $ldap, $ldap_manager_dn, $ldap_manager_pw);
            break;

        case 'squirrel':
        default:
              global $ldap_bind_dn, $ldap_bind_pw;
            $bind_result = ldap_bind( $ldap, $ldap_bind_dn, $ldap_bind_pw);
            break;
    }

    if (!$bind_result) {
        $errormsg = "Error while binding to LDAP server as $bind.<br>" .
            "Please contact your administrator.<br>".
            "If you are the administrator, please check the " .
            "credentials provided in the ldapuserdata plugin configuration.";
        exit;
    }
    return $ldap;
}

/**
 * Read user data from LDAP database. 
 *
 * Data will be written to the session, in $_SESSION['ldap_prefs_cache'],
 * together with some additional data (such as $_SESSION['prefs_before'] for
 * doing compares when writing back, $_SESSION['dn'] for the user's
 * distinguished name etc.
 *
 * @return void
 */
function ldapuserdata_read_do() {
    global  $login_username, $imapServerAddress, $full_name, $email_address,
        $ldap_bind_dn, $ldap_host, $authz, $domain;

    /* --- fill $ldap_prefs_cache with data from LDAP. --- */
    $ret = ldapuserdata_retrieve_data($login_username, $ldap_prefs_cache, $extra_data);

    /* $prefs_before will remain, for checking if there are any changes to
     * write in the end. */ 
    $prefs_before = $ldap_prefs_cache;

    /* Save sensitive information to session variables outside ldap prefs
     * cache, so that they cannot be tampered by the user. These are
     * located in $extra_data array. */

    $full_name = $ldap_prefs_cache['full_name'];
    $email_address = $ldap_prefs_cache['email_address'];
    $imapServerAddress = $ldap_prefs_cache['imapServerAddress'];
    
    $vars = array('dn', 'ldapidentities', 'identities_map', 'alternatenames', 'alternateemails',
            'authorizedreplyto', 'ludObjectClasses', 'ludDisabledServices', 'ldapuserinfo');

    foreach($vars as $var) {
        if(isset($extra_data[$var])) {
            $$var = $extra_data[$var];
            sqsession_register($$var, $var);
        }
    }

    $prefs_are_cached = true;

    sqsession_register($prefs_are_cached, 'prefs_are_cached');
    sqsession_register($ldap_prefs_cache, 'ldap_prefs_cache');
    sqsession_register($prefs_before, 'prefs_before');
    sqsession_register($imapServerAddress, 'imapServerAddress');
    sqsession_register($full_name, 'full_name');
    sqsession_register($email_address, 'email_address');
}

/**
 * Retrieve user data from an LDAP database.
 *
 * This function logs on to the LDAP server, returns the user preferences of
 * the specified user and logs out.
 *
 * @param string $uid UserID of the authorized user for whom to get preferences
 * @param array &$user_prefs Reference to the array where preferences will be stored.
 * @param array &$extra_info Reference to the array where extra data will be stored.
 * @return boolean
 */
function ldapuserdata_retrieve_data ($uid, &$user_prefs, &$extra_info) {
    global  $ldap_host, $ldap_base_dn, $ldap_bind_dn, $ldap_bind_pw,
        $ldap_manager_dn, $ldap_manager_pw, $required_objectclass,
        $ldap_objectclass, $prefs_default, $ldap_attributes,
        $boolean_attrs, $multivalue_attrs, $alternatemail_attrs,
        $ldap_user_search_filter, $languages, $default_charset,
        $sm_uoa_disabled_services_vars, $limit_languages, $plugins,
        $squirrelmail_default_language, $sm_options_to_save;

    $ldap = ldapuserdata_ldap_connect('squirrel', false);

    if (!($search_result = ldap_search($ldap, $ldap_base_dn,
      sprintf($ldap_user_search_filter, ldapspecialchars($uid)),
      array_merge( array_keys($ldap_attributes),
                   array_keys($multivalue_attrs),
               array_keys($alternatemail_attrs),
               array('displayname', 'objectclass','smoptions'),
               array('edupersonprimaryaffiliation', 'edupersonprimaryorgunitdn'),
               ( !empty($sm_uoa_disabled_services_vars) ? $sm_uoa_disabled_services_vars : array() )
       ) ))) {

        print "Error while searching for $uid.";
        exit;
    }

    $info = ldap_get_entries($ldap, $search_result);
    ldap_close($ldap);

    if (!isset($info[0])) {
        logout_error( _("Unknown user or password incorrect.") );
        session_destroy();
        exit;
    }

    if(isset($required_objectclass)) {
        if(!in_array($required_objectclass, $info[0]['objectclass'])) {
            logout_error( _("User $uid does not have access to the mail service."));
            session_destroy();
            exit;
        }
    }
        
    /* Check if we have objectClass $ldap_objectclass (default: SquirrelMailUser) */
    if (in_array($ldap_objectclass, $info[0]['objectclass'])) {
        $already_member = true;
    } else {
        $already_member = false;
    }
    
    if(!empty($info[0]['language'][0])) {
        $language = $info[0]['language'][0];

        if($language == 'en_GR') {
            // MIGRATE - UOA
            $language = 'en_US';
        }
        
        if(in_array('limit_languages', $plugins)) {
            include_once(SM_PATH . 'plugins/limit_languages/config.php');
            if($language == null || !isset($language, $limit_languages)) {
                $language = $squirrelmail_default_language;
            } 
        }

    } else {
        $language = $squirrelmail_default_language;
    }

    if(isset($languages[$language])) {
        $mycharset = $languages[$language]['CHARSET'];
    } else {
        $mycharset = $default_charset;
    }
    
    /* Read in LDAP Preferences */
    if ($info["count"] == 1) {
        reset($ldap_attributes);
        while(list($ldapattr, $squirrelattr) = each($ldap_attributes)) {
            if (isset($info[0][$ldapattr][0])) {
                $prefs[$squirrelattr] = mb_convert_encoding( $info[0][$ldapattr][0], $mycharset, "UTF-8");
                //print "DBG: $ldapattr aka $squirrelattr " .$info[0][$ldapattr][0] ."<br/>"; 
            } elseif($ldapattr == 'cn') {
                /* Special hack, for when only one or more of cn;lang-??
                 * exists, but not cn itself. */
                foreach($languages as $lang => $attrs) {
                    if (isset($info[0]['cn;lang-'.$lang]['count'])) {
                        $prefs[$squirrelattr] = mb_convert_encoding( $info[0]['cn;lang-'.$lang][0], $mycharset, "UTF-8");
                    }
                }
            }
        }
    } else {
        print "Error, more than one $uid found.";
        exit;
    }

    // Overwrite $language in case it was changed.
    $prefs['language'] = $language;

    /* Special handling of some multivalue attributes: */
    
    /* All options under smoptions multivalue attribute */
    if(isset($info[0]['smoptions'])) {
        for($i=0; $i<$info[0]['smoptions']['count']; $i++) {
            $tmp = unserialize($info[0]['smoptions'][$i]);
            // now we''ll have something like array('javascript_autocomplete_options' => 2);
            $tmpkeys = array_keys($tmp);
            $mykey = $tmpkeys[0];
            if(in_array($mykey, $sm_options_to_save)) {
                $prefs[$mykey] = $tmp[$mykey];
            }
        }
    }

    /* Special treatment for POP mail-fetch.  */
    if (isset($info[0]['mailfetch']['count'])) {
        $prefs['mailfetch'] = $info[0]['mailfetch'];
    }

    if(isset($info[0]['mailfetch']['count']) &&
      $info[0]['mailfetch']['count'] > 0) {
        $prefs['mailfetch_server_number'] = $info[0]['mailfetch']['count'];

        for ($i=0; $i < $info[0]['mailfetch']['count']; $i++) {
            $fetcharray = unserialize ($prefs['mailfetch'][$i]);
            $prefs['mailfetch_server_'.$i] = $fetcharray[0];
            $prefs['mailfetch_alias_'.$i] = $fetcharray[1];
            $prefs['mailfetch_user_'.$i] = $fetcharray[2];
            $prefs['mailfetch_pass_'.$i] = $fetcharray[3];
            $prefs['mailfetch_cypher_'.$i] = $fetcharray[4];
            $prefs['mailfetch_lmos_'.$i] = $fetcharray[5];
            $prefs['mailfetch_login_'.$i] = $fetcharray[6];
            $prefs['mailfetch_fref_'.$i] = $fetcharray[7];
            $prefs['mailfetch_subfolder_'.$i] = $fetcharray[8];
            $prefs['mailfetch_uidl_'.$i] = $fetcharray[9];
        }
    }
    
    /* Special treatment for newmail plugin. */

    if(isset($info[0]['newmail'][0])) {
        
        $prefs['newmail'] = $info[0]['newmail'][0];
        $newarray = unserialize($prefs['newmail']);
        
        $prefs['newmail_enable'] = $newarray[0];
        $prefs['newmail_popup'] = $newarray[1]; 
        $prefs['newmail_allbox'] = $newarray[2]; 
        $prefs['newmail_recent'] = $newarray[3]; 
        $prefs['newmail_changetitle'] = $newarray[4];
        $prefs['newmail_media'] = $newarray[5];

    }

    /* Some additional thingies for the options: move_to_trash,
     * move_to_sent, save_as_draft, which are NOT included in the ldap
     * schema. */

    if( (isset($prefs['trash_folder']) && $prefs['trash_folder'] != 'none') ||
       !isset($prefs['trash_folder']) ) {
        $prefs['move_to_trash'] = 1;
    } else { 
        $prefs['move_to_trash'] = 0;
    }

    if( (isset($prefs['sent_folder']) && $prefs['sent_folder'] != 'none') ||
        !isset($prefs['sent_folder']) ) {
        $prefs['move_to_sent'] = 1;
    } else { 
        $prefs['move_to_sent'] = 0;
    }
    
    if( (isset($prefs['draft_folder']) && $prefs['draft_folder'] != 'none') ||
        !isset($prefs['draft_folder']) ) {
        $prefs['save_as_draft'] = 1;
    } else { 
        $prefs['save_as_draft'] = 0;
    }
    
    
    /* Handle boolean attributes. Must change the upper case string TRUE |
     * FALSE to 1 or 0 so that Squirrelmail can handle it sanely. */

    while(list($ldapattr, $squirrelattr) = each($boolean_attrs)) {
        if( array_key_exists($squirrelattr, $prefs) ) {
            if($prefs[$squirrelattr] == "TRUE") {
                $prefs[$squirrelattr] = 1;
            } elseif ($prefs[$squirrelattr] == "FALSE") {
                $prefs[$squirrelattr] = 0;
            }
        }
    }
    

    /* ------------- End of preferences ($prefs) ----------------- */

    /* ------------- user information stuff ($_SESSION['ldapuserinfo']) ----------------- */

    if(isset($info[0]['edupersonprimaryaffiliation'])) {
        $extra['ldapuserinfo'] = array(
            'edupersonprimaryaffiliation' => $info[0]['edupersonprimaryaffiliation'][0],
            'edupersonprimaryorgunitdn' => strtolower($info[0]['edupersonprimaryorgunitdn'][0]),
        );
    }

    /* ------------- ldapIdentities Stuff ----------------- */
    
    $ldapidentities = array();
    $ldapidentities['main'] = array();
    $identities_map = array();

    /* mail - default identity */
    
    $ldapidentities['main']['email_address'][] = $info[0]['mail'][0];
    // $identities_map[1] = $info[0]['mail'][0];
        

    /* mail alternate address - identities */
    if (isset($info[0]['mailalternateaddress']['count'])) {

        for($i = 0; $i < $info[0]['mailalternateaddress']['count']; $i++) {
            $extra['alternateemails'][$i] = $info[0]['mailalternateaddress'][$i];
            
            $ldapidentities['main']['email_address'][] = $info[0]['mailalternateaddress'][$i];
            
            $identities_map[$i+1] = $info[0]['mailalternateaddress'][$i];
            $m = $i+2;
        }
    }


    /* Gather together alternate names for 'main' identity */
    $tmp = array();

    /* 'displayName' (single-value). Should be the first one in the array,
     * i.e. the default. */
    if(isset($info[0]['displayname'][0])) {
        $tmp[] = $info[0]['displayname'][0];
    
        foreach($languages as $lang => $attrs) {
            if (isset($info[0]['displayname;lang-'.$lang]['count'])) {
                $tmp[] = mb_convert_encoding($info[0]['displayname;lang-'.$lang][0],
                  $mycharset, "UTF-8");
            }
        }
    }

    /* 'cn' */
    for($i=0; $i<$info[0]['cn']['count']; $i++) {
        if(!in_array($info[0]['cn'][$i], $tmp)){
            $tmp[] = $info[0]['cn'][$i];
        }
    }
    foreach($languages as $lang => $attrs) {
        if (isset($info[0]['cn;lang-'.$lang]['count'])) {
            for($i=0; $i<$info[0]['cn;lang-'.$lang]['count']; $i++) {
                $lala = mb_convert_encoding($info[0]['cn;lang-'.$lang][$i], $mycharset, "UTF-8");
                if(!in_array($lala, $tmp)){
                    $tmp[] = $lala;
                }
            }
        }
    }
    /*
    $tmp = array_unique($tmp);
    $tmp = array_values($tmp);
    */

    $ldapidentities['main']['allowed_names'] = $tmp;

    /* Done with -main- identity. Move on to mailauthorizedaddress
     * identities. */

    if (isset($info[0]['mailauthorizedaddress']['count'])) {
        
        for($i=0; $i<$info[0]['mailauthorizedaddress']['count'] ; $i++) {
            
            $info[0]['mailauthorizedaddress'][$i] =  mb_convert_encoding($info[0]['mailauthorizedaddress'][$i],
              $mycharset, "UTF-8");

            $ident = array();
        
            /* Fill $ldapidentities array with data from mailauthorized
             * address */

            /* My mailAuthorized address always has a special format:
             * Comment <email@address> */

            if(strstr($info[0]['mailauthorizedaddress'][$i], "<")) {

                $rx = "/<.+>/sU";

                $parts = explode("<", $info[0]['mailauthorizedaddress'][$i], 2);

                if( ($r = preg_match($rx, $info[0]['mailauthorizedaddress'][$i], $results)) == 1) {
                    $tmp1 = str_replace('<', '', $results[0]);
                    $ml = str_replace('>', '', $tmp1);
                
                
                    if(!isset($m)) {
                        $m = 1;
                    }
                    
                    if(!in_array($ml, $identities_map)) {
                        $identities_map[$m] = $ml;
                        $m++;
                    }

                    $ldapidentities[$ml]['allowed_names'][] = trim($parts[0]);
                } else {
                    logout_error("Malformed mailAuthorized Address on the LDAP server.");
                    session_destroy();
                    exit;
                }
            }

        }

    }

    /* Special feature for 'identity' */
    if (isset($info[0]['mailpreferred'][0])) {
        if(array_key_exists($info[0]['mailpreferred'][0], $ldapidentities) &&
           isset($identities_map) ) {

            foreach($identities_map as $no => $ma) {
                if($ma == $info[0]['mailpreferred'][0]) {
                    $prefs['identity'] = $no;
                    break;
                }
            }
        }
    }

    /* Save current selection of alternatenames to session.! */

    if(isset($ldapidentities['main']['allowed_names'])){
        /* No identity selected or main identity selected */
        $extra['alternatenames'] = $ldapidentities['main']['allowed_names'];
    }
    if(isset($prefs['mailpreferred'])) {
        if(isset($ldapidentities[$prefs['mailpreferred']]['allowed_names'])){
            $extra['alternatenames'] = $ldapidentities[$prefs['mailpreferred']]['allowed_names'];
        }
    }


    if (isset($ldapidentities)) {
        $extra['ldapidentities'] = $ldapidentities;
    }
    
    if (isset($identities_map)) {
        $extra['identities_map'] = $identities_map;
    }


    if (isset($info[0]['mailauthorizedreplyto']['count'])) {
        for($i=0; $i<$info[0]['mailauthorizedreplyto']['count'] ; $i++) {
            $extra['authorizedreplyto'][] = $info[0]['mailauthorizedreplyto'][$i];
        }
    }
    
    /* Add Objectclasses to make them available to anyone who possibly
     * wants them for some reason. */
    if(isset($info[0]['objectclass'])) {
        for($i=0; $i<$info[0]['objectclass']['count'] ; $i++) {
            $extra['ludObjectClasses'][] = strtolower($info[0]['objectclass'][$i]);
        }
    }

    /* UoA-specific, *disabled services schema */
    if(!empty($sm_uoa_disabled_services_vars)) {
        $extra['ludDisabledServices'] = array();
        foreach($sm_uoa_disabled_services_vars as $dvar) {
            if(isset($info[0][strtolower($dvar)])) {
                $extra['ludDisabledServices'][strtolower($dvar)] = $info[0][strtolower($dvar)][0];
            }
        }
    }
    
    /* Use the dn that was returned from previous search */
    if(isset($info[0]['dn'])) {
        $user_dn = $info[0]['dn'];
        $extra['dn'] = $info[0]['dn'];
    } else {
        /* This should not be reached at any time... */
        print "Warning: Could not get dn for user $uid";
    }
     
    /* If first time logged in, add me to the SquirrelMailUser objectclass.
     * */

    if(!$already_member) {
        $newinfo = array();
        $newinfo['objectclass'] = array();
        for($i = 0; $i < $info[0]['objectclass']['count']; $i++) {
            $newinfo['objectclass'][$i] =  $info[0]['objectclass'][$i];
        }
        $newinfo['objectclass'][$i] = $ldap_objectclass;
        
        $ldap2 = ldapuserdata_ldap_connect('manager', true);
        if(!ldap_modify($ldap2, $user_dn , $newinfo)) {
            $errormsg = "Error while updating your objectclass! Could not modify LDAP data.";
            ldap_close($ldap2);
            exit;
        } 
        ldap_close($ldap2);

        /* Plugins or other scripts, e.g. an enhanced motd plugin, can
         * use the first_time_user variable; this way they know that
         * this is the first time this user has logged on to the
         * webmail service. */
        $first_time_user = true;
        sqsession_register($first_time_user, 'first_time_user');
    }

    
    /* Store $prefs to $user_prefs */
    $user_prefs = $prefs;

    /* Store rest of info to $extra_info */
    $extra_info = $extra;

    return true;
}

/**
 * Write user data to LDAP Directory.
 *
 * @return void
 */
function ldapuserdata_write_do() {
    global $ldap_host, $ldap_objectclass, $prefs_default, $ldap_attributes,
      $sm_options_to_save, $boolean_attrs, $languages, $default_charset, $logoutmsg;

    if(isset($_SESSION['authz']) ) {
        $authz = $_SESSION['authz'];
    }

    if(isset($_SESSION['ldap_prefs_cache'])) {
        $ldap_prefs_cache = $_SESSION['ldap_prefs_cache'];
    }
    
    if(isset($_SESSION['prefs_before'])) {
        $prefs_before = $_SESSION['prefs_before'];
    }

    /* prepare data */
    $attr_to_write = array();
    $attr_to_del = array();
    
    /* Know which charset we convert from, to UTF-8 */
    if(isset($prefs['language'])) {
        $mycharset = $languages[$prefs['language']]['CHARSET'];
    } else {
        $mycharset = $default_charset;
    }

    while(list($ldapattr, $squirrelattr) = each($ldap_attributes)) {
        if( !( ($ldapattr == "cn") || ($ldapattr == "mail") || ($ldapattr == "mailhost")) ) {
    
            if(( isset($ldap_prefs_cache[$squirrelattr]) && isset($prefs_before[$squirrelattr]) &&
                 $ldap_prefs_cache[$squirrelattr] != $prefs_before[$squirrelattr] )|| 
               ( isset($ldap_prefs_cache[$squirrelattr]) && !isset($prefs_before[$squirrelattr]) &&
                 $ldap_prefs_cache[$squirrelattr] != "" ) || 
               ( isset($ldap_prefs_cache[$squirrelattr]) && isset($boolean_attrs[$ldapattr])
                 )
              )
                {

                if ($ldap_prefs_cache[$squirrelattr] != "") {
                    $attr_to_write[$ldapattr] = mb_convert_encoding($ldap_prefs_cache[$squirrelattr],
                      "UTF-8", $mycharset);
                } elseif(!isset($boolean_attrs[$ldapattr])) {
                    $attr_to_del[$ldapattr] = array();
                } elseif(isset($boolean_attrs[$ldapattr])) {
                    // This makes sure that we always write boolean attributes.
                    // For very-slightly-better performance there should be a check against default values.
                    // But even then it might not work 100% of the time.
                    $attr_to_write[$ldapattr] = $ldap_prefs_cache[$squirrelattr];
                }
            }
        }
    }

    /* Handle boolean attributes */
    while(list($ldapattr, $squirrelattr) = each($boolean_attrs)) {
        if( array_key_exists($ldapattr, $attr_to_write) ) {
            if($attr_to_write[$ldapattr]) {
                $attr_to_write[$ldapattr] = 'TRUE';
            } else {
                $attr_to_write[$ldapattr] = 'FALSE';
            }
        }
    }

    /* Handle multivalue attributes */

    /* All options under smoptions multivalue attribute */
    $prefs2 = array();
    $countChanged = 0;
    foreach($sm_options_to_save as $mykey) {
        if (isset($ldap_prefs_cache[$mykey]) &&
            (!isset($prefs_before[$mykey])  || serialize($ldap_prefs_cache[$mykey]) != serialize($prefs_before[$mykey])) ) {
                // will write $mykey
                $tmp = array($mykey => $ldap_prefs_cache[$mykey]);
                $prefs2[] = serialize($tmp);
                $countChanged++;
        } else {
            // $mykey hasn't changed. we won't bother writing smOptions attribute,
            // unless there was a change in the previous block
            $tmp = array($mykey => (!empty($ldap_prefs_cache[$mykey]) ? $ldap_prefs_cache[$mykey] : $prefs_default[$mykey]));
            $prefs2[] = serialize($tmp);
        }
    }
    if($countChanged) {
        $attr_to_write = array('smOptions' => $prefs2);
    }

    /* Mailfetch */
    if(isset($ldap_prefs_cache['mailfetch_server_number'])) {
    
    if($ldap_prefs_cache['mailfetch_server_number'] == 0) {

        if($prefs_before['mailfetch_server_number'] > 0) {
            $attr_to_del['mailfetch'] = array();
        }
    
    } elseif($ldap_prefs_cache['mailfetch_server_number'] > 0) {
        $delmode = false;
        $different = false;
        if($prefs_before['mailfetch_server_number'] != $ldap_prefs_cache['mailfetch_server_number']
            && $prefs_before['mailfetch_server_number'] != 0 ) {

            $attr_to_del['mailfetch'] = $prefs_before['mailfetch'];
            unset($attr_to_del['mailfetch']['count']);
            $delmode = true;
        }
        for ($i=0; $i < $ldap_prefs_cache['mailfetch_server_number']; $i++) {
            $newarray[0] = $ldap_prefs_cache['mailfetch_server_'.$i];
            $newarray[1] = $ldap_prefs_cache['mailfetch_alias_'.$i]; 
            $newarray[2] = $ldap_prefs_cache['mailfetch_user_'.$i]; 
            $newarray[3] = $ldap_prefs_cache['mailfetch_pass_'.$i];
            $newarray[4] = $ldap_prefs_cache['mailfetch_cypher_'.$i];
            $newarray[5] = $ldap_prefs_cache['mailfetch_lmos_'.$i]; 
            $newarray[6] = $ldap_prefs_cache['mailfetch_login_'.$i];
            $newarray[7] = $ldap_prefs_cache['mailfetch_fref_'.$i]; 
            $newarray[8] = $ldap_prefs_cache['mailfetch_subfolder_'.$i];
            $newarray[9] = $ldap_prefs_cache['mailfetch_uidl_'.$i]; 
            $mailfetchpref[$i] = serialize ($newarray);
        }
        for($i=0; $i<sizeof($mailfetchpref) ;$i++) {
            if($mailfetchpref[$i] != $prefs_before['mailfetch'][$i]) {
                $different = true;
            }
        }
        if($different == true || $delmode == true ) {
            for($i=0; $i<sizeof($mailfetchpref) ;$i++) {
                $attr_to_write['mailfetch'][$i] = $mailfetchpref[$i];
            }
        }
        unset($newarray);
        unset($different);
        unset($delmode);
    }
    }
    
    /* Newmail */
    if(isset($ldap_prefs_cache['newmail_enable'])) {
        $newarray[0] = $ldap_prefs_cache['newmail_enable'];
        $newarray[1] = $ldap_prefs_cache['newmail_popup'];
        $newarray[2] = $ldap_prefs_cache['newmail_allbox'];
        $newarray[3] = $ldap_prefs_cache['newmail_recent'];
        $newarray[4] = $ldap_prefs_cache['newmail_changetitle'];
        $newarray[5] = $ldap_prefs_cache['newmail_media'];
        $newmailpref = serialize ($newarray);
        if(!isset($prefs_before['newmail']) ||
           (isset($prefs_before['newmail']) &&  $newmailpref != $prefs_before['newmail'])) {
            $attr_to_write['newmail'] = $newmailpref;
        }

    }

    /*while(list($ldapattr, $squirrelattr) = each($multivalue_attrs)) {
    }*/
    
    /* Now our $attr_to_write and $attr_to_del arrays are ready. */

    /* Starting Write Process.  Of course, perform a write on our poor
     * LDAP, only if there _is_ something different to write. 
     */
        
        
    if(isset($_SESSION['dn'])) {
        $user_dn = $_SESSION['dn'];
    } else {
        /* This is reached when the session has expired, and user hits
         * 'logout'. The preferences are not saved then. */
        // Notice: Session expired - Could not get dn for user!
    }
     
    if(!(sizeof($attr_to_del) == 0)) {
        $ldap = ldapuserdata_ldap_connect('manager', true);
        if(!ldap_mod_del($ldap, $user_dn, $attr_to_del)) {
            print "<STRONG>ERROR: While clearing your preferences, an error had
            occured on the LDAP server.</strong><br />
            LDAP Replied:<br />
            <em>".ldap_error($ldap)."</em><br/>
            Please contact your administrator.";
            print_r($attr_to_del);
        }
        if(sizeof($attr_to_del) == 0) {
            ldap_close($ldap);
        }
    }

    if(!(sizeof($attr_to_write) == 0)) {
        if(sizeof($attr_to_del) == 0) {
            $ldap = ldapuserdata_ldap_connect('manager', true);
        }
        //print "DEBUG: I will now execute ldap_modify (ldap, $user_dn, attr_to_write)";
        if(!ldap_modify($ldap, $user_dn, $attr_to_write)) {
            print "<STRONG>ERROR: While writing your preferences, an error had
            occured on the LDAP server.</strong><br />
            LDAP Replied:<br />
            <em>".ldap_error($ldap)."</em><br/>
            Please contact your administrator.";
            print_r($attr_to_del);
        } 
        ldap_close($ldap);
    }

    $_SESSION['prefs_before'] = $ldap_prefs_cache;
}

/**
 * Display which preferences are to be saved to LDAP.
 *
 * @return string Message with the changed preference items, to be displayed
 *
 * @todo Does not work yet; fix it; and use logout facility of Squirrel
 */
function ldapuserdata_displaychangedprefs() {
    global $color, $logoutmsg;

    $out =
    html_tag( 'table',
        html_tag( 'tr',
             html_tag( 'th', _("Sign Out"), 'center' ) ,
        '', $color[0], 'width="100%"' ) .
        html_tag( 'tr',
             html_tag( 'td', 
            $logoutmsg ,
         
             'center' ) ,
        '', $color[4], 'width="100%"' ) .
        html_tag( 'tr',
             html_tag( 'td', '<br>', 'center' ) ,
        '', $color[0], 'width="100%"' ) ,
    'center', $color[4], 'width="50%" cols="1" cellpadding="2" cellspacing="0" border="0"' );

    return $logoutmsg;

}


/**
 * Things to do before sending out an email.
 *
 * Tasks that are performed:
 * 1) Add a Sender: Header that reflects the data of the first identity
 * ($identities[0]).
 * 2) Check if sendmailDisabled attribute exists. If it exists, it will not
 * allow someone to send mail. The format currently is: DATE@@TIME##EXCUSE
 *
 * Note: for (1) functionality class/deliver/Deliver.class.php needs to be patched:
 * ( Line 468 should become
 *     if (count($rfc822_header->from) > 1 || count($rfc822_header->sender))
 * )
 *
 * @return void
 */
function ldapuserdata_compose_check_do(&$argv) {
    global  $ldap_host, $ldap_base_dn, $ldap_bind_dn, $ldap_bind_pw,
        $ldap_manager_dn, $ldap_manager_pw, $required_objectclass,
        $ldap_objectclass, $ldap_user_search_filter, $languages,
        $default_charset, $username, $authz;

    $message = &$argv[1];
    $header = &$message->rfc822_header;
    $header->sender[0] = new AddressStructure();
    $identities = get_identities();
    $header->sender[0]->personal = $identities[0]['full_name'];
    $sender_address_array = explode('@', $identities[0]['email_address']);
    $header->sender[0]->mailbox = $sender_address_array[0];
    $header->sender[0]->host = $sender_address_array[1];

    $uid = $username;
    
    $ldap = ldapuserdata_ldap_connect('squirrel', false);

    if (!($search_result = ldap_search($ldap, $ldap_base_dn,
      sprintf($ldap_user_search_filter, ldapspecialchars($uid)), array('sendmaildisabled')
      ))) {
        print "Error while searching for $uid.";
        exit;
    }
    $info = ldap_get_entries($ldap, $search_result);
    ldap_close($ldap);

    if(!isset($info[0]['sendmaildisabled'])) {
        return;
    } else {
        print '<p style="font-size: 1.2em; font-weight: bold;">';
        if(!empty($authz)) {
            $excuseparts = explode('##', $info[0]['sendmaildisabled'][0]);
            $excuseparts2 = explode('@@', $excuseparts[0]);
            $date = $excuseparts2[0];
            $time = $excuseparts2[1];
            $reason = $excuseparts[1];
            printf(_("Sending mail from this account was disabled on %s, at %s.<br/>The reason was: %s.<br/>Please contact the helpdesk to resolve this issue."), $date, $time, $reason);
        } else {
            print _("Sending mail from this account has been forbidden.<br/>Please contact the helpdesk to resolve this issue.");
        }
        print '</p>';
        exit;
    }
    return $message;
}


/**
 * Remove Preferences that are not to be displayed.
 * @return void
 */
function ldapuserdata_display_options_do() {
   
    global $ldap_attributes, $ldapuserdata_remove_options_that_are_not_saved, $optpage_data, 
            $sm_settings_do_not_remove;

    if($ldapuserdata_remove_options_that_are_not_saved) {

        $disable_options = array(
            /* Display */
            'alt_index_colors', 'page_selector', 'page_selector_max',
            'show_full_date', 'wrap_at', 'location_of_buttons', 'show_html_default',
            'enable_forward_as_attachment', 'forward_cc', 'show_xmailer_default',
            'attachment_common_show_images', 'pf_cleandisplay', 'compose_width',
            'compose_height', 'sig_first', 'body_quote', 'reply_focus', 'internal_date_sort',
            'sort_by_ref',
    
            /* Folder */
            'collapse_folders', 'unseen_cum', 'date_format', 'hour_format',
            'search_memory', 'mailbox_select_style',
    
            /* Personal */
            'prefix_sig'
        );

        $squirrelnames = array_values($ldap_attributes);

        foreach ($optpage_data['vals'] as $groupNumber => $optionGroup) {
            foreach ($optionGroup as $optionNumber => $optionItem) {
               if (isset($optionItem['name']) && isset($optionItem['caption']) && 
                       (!in_array($optionItem['name'],$squirrelnames) && !in_array($optionItem['name'], $sm_settings_do_not_remove))
               ) {
                    unset($optpage_data['vals'][$groupNumber][$optionNumber]);
                }

            }
        }

    }
}

/**
 * Return the allowed Reply-To: Email addresses, in a format suitable for 
 * Squirrelmail Options Page.
 *
 * @return array
 */
function ldapuserdata_identities_allowed_replyto() {
    global $email_address;
    sqgetGlobalVar('alternateemails', $alternateemails, SQ_SESSION);
    sqgetGlobalVar('authorizedemails', $authorizedemails, SQ_SESSION);
    sqgetGlobalVar('authorizedreplyto', $authorizedreplyto, SQ_SESSION);
    
    $email_address  = getPref($data_dir, $username, 'email_address');
    $replytomails = array();

    /* Default mail address */
    $replytomails[''] = _("None");
    $replytomails[$email_address] = $email_address;
    
    /* Alternate mail addresses */
    if(isset($alternateemails)) {
        for($i=0; $i< sizeof($alternateemails); $i++) {
            $replytomails[$alternateemails[$i]] = $alternateemails[$i]; 
        }
    }

    /* Authorized mail addresses */
    if(isset($authorizedemails)) {
        for($i=0; $i< sizeof($authorizedemails); $i++) {
            $replytomails[$authorizedemails[$i]] = $authorizedemails[$i]; 
        }
    }

    /* Specific mail addresses allowed for reply-to: */
    if(isset($authorizedreplyto)) {
        for($i=0; $i< sizeof($authorizedreplyto); $i++) {
            $replytomails[$authorizedreplyto[$i]] = $authorizedreplyto[$i]; 
        }
    }

    return $replytomails;
}

/**
 * Sanitizes ldap search strings.
 * See rfc2254
 * @link http://www.faqs.org/rfcs/rfc2254.html
 * @since 1.5.1 and 1.4.5
 * @param string $string
 * @return string sanitized string
 * @author Squirrelmail Team
 */
function ldapspecialchars($string) {
    $sanitized=array('\\' => '\5c',
                     '*' => '\2a',
                     '(' => '\28',
                     ')' => '\29',
                     "\x00" => '\00');

    return str_replace(array_keys($sanitized),array_values($sanitized),$string);
}

