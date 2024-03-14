<?php

/**
 * SquirrelMail LDAP Personal Address Book Backend Plugin (ldap_abook_backend)
 * By Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Based on Address book backend template by Tomas Kuliavas
 *
 * Copyright (c) 2005-2006 Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Version: 0.2.2
 */

if (!defined("SM_PATH")) {
    define("SM_PATH", "../../");
}

include_once(SM_PATH . "functions/addressbook.php");
include_once(SM_PATH . "functions/i18n.php");
include_once(SM_PATH . "functions/display_messages.php");

if (file_exists(SM_PATH . "plugins/ldap_abook_backend/config.php")) {
    require_once(SM_PATH . "plugins/ldap_abook_backend/config.php");
} else {
    error_box("Configuration file for LDAP Personal Address Book Plugin is missing!");
}

if (file_exists(SM_PATH . "plugins/ldap_common/ldap_common.php")) {
    include_once(SM_PATH . "plugins/ldap_common/ldap_common.php");
} else {
    error_box("LDAP Common Plugin is missing! Please download and install it!");
}

class abook_LdapBackend extends addressbook_backend {
    var $btype = 'local';
    var $bname = 'LdapBackend';
    
    var $writeable = true;

    var $ldap;

    var $host;
    var $options;
    var $useTls;
    var $useSelfAuth;

    var $bindDn;
    var $bindPassword;

    var $base;
    var $filter;

    // attribute names
    var $squirrelAttrs;
    var $ldapAttrs;
    var $requiredAttrs;
    var $requiredMsgs;

    var $attrNickname;
    var $attrRDN;
    var $objectClass;

    var $containerObjectClass;
    var $containerDnAttr;

    var $missingField;

    function abook_LdapBackend($param) {
        global $ldapAbookHost, $ldapAbookOptions, $ldapAbookUseTls, $ldapAbookUseSelfAuth, $ldapAbookBindDn, $ldapAbookBindPassword,
            $ldapAbookBase, $ldapAbookFilter, $ldapAbookObjectClass, $ldapAbookRequiredNickname, $ldapAboobRequiredFirstname,
            $ldapAbookRequiredLastname, $ldapAbookRequiredEmail, $ldapAbookRequiredLabel, $ldapAbookAttrNickname, $ldapAbookAttrRDN,
            $ldapAbookAttrFirstname, $ldapAbookAttrLastname, $ldapAbookAttrEmail,$ldapAbookAttrLabel, $ldapAbookContainerObjectClass,
            $ldapAbookContainerDnAttr, $username, $default_charset, $languages, $squirrelmail_language;
        
        if (!is_array($param)) {
            return $this->set_error('Invalid argument to constructor');
        }

        if (!empty($param['name'])) {
           $this->sname = $param['name'];
        }

        $charset = $default_charset;
        if (isset($languages[$squirrelmail_language])) {
                $charset = $languages[$squirrelmail_language]["CHARSET"];
        }
        $this->ldap = new LdapClient($charset);

        $this->host = $ldapAbookHost;
        $this->options = $ldapAbookOptions;
        $this->useTls = $ldapAbookUseTls;

        $this->base = $ldapAbookBase;
        $this->filter = $ldapAbookFilter;

        $this->useSelfAuth = $ldapAbookUseSelfAuth;
        if (!$this->useSelfAuth) {
            // Avel
            $this->bindDn = $dn = "uid=".$username.",".$this->base;
            $this->bindPassword = sqauth_read_password();
    
            // $this->bindDn = $ldapAbookBindDn;
            // $this->bindPassword = $ldapAbookBindPassword;
            // Bind only if not in self auth mode
         }
         if (!$this->connect(!$this->useSelfAuth)) {
             $this->disconnect($this->_("Can't connect to LDAP server!"));
         }

         $query = str_replace("%username", $this->ldap->escapeQuery($username), $this->filter);

         /*
            $this->debug("abook_LdapBackend::abook_LdapBackend - search user DN: $this->base QUERY: $query");
        $dn = $this->ldap->searchDn($this->base, $query, true, true);
        if (!$dn) {
            $this->disconnect($this->_("Can't search in LDAP server!"));
        }
        $this->ldap->disconnect();
            $this->debug("abook_LdapBackend::abook_LdapBackend - found user DN: $dn");
          */

        // In self auth mode, the found DN will be used both for binding and as the base DN for the
        // personal address book.
        if ($this->useSelfAuth) {
            // Fix by Patrick Scharrenberg
            sqgetGlobalVar("key", $key, SQ_COOKIE);
            sqgetGlobalVar("onetimepad", $onetimepad, SQ_SESSION);

                $this->bindDn = $dn;
            $this->bindPassword = OneTimePadDecrypt($key, $onetimepad);
        }

        // $this->base = $dn;
        $this->objectClass = $ldapAbookObjectClass;
        $this->containerDnAttr = $ldapAbookContainerDnAttr;
        $this->containerObjectClass = $ldapAbookContainerObjectClass;

        $this->attrNickname = $ldapAbookAttrNickname;
        $this->attrRDN = $ldapAbookAttrRDN;
        $this->ldapAttrs = array(
            $ldapAbookAttrNickname,
            $ldapAbookAttrFirstname,
            $ldapAbookAttrLastname,
            $ldapAbookAttrEmail,
            $ldapAbookAttrLabel);
        $this->requiredAttrs = array(
                $ldapAbookRequiredNickname, 
            $ldapAboobRequiredFirstname, 
            $ldapAbookRequiredLastname,
            $ldapAbookRequiredEmail,
            $ldapAbookRequiredLabel);
        $this->squirrelAttrs = array(
            "nickname", 
            "firstname", 
            "lastname", 
            "email", 
            "label");
        $this->requiredMsgs = array(
            $this->_("Nickname is missing"),
            $this->_("First name is missing"),
            $this->_("Last name is missing"),
            $this->_("Email is missing"),
            $this->_("Additional info is missing"));

            $this->debug("abook_LdapBackend::abook_LdapBackend - address book base: $this->base");
    }

    function _($s) {
        sq_bindtextdomain("ldap_abook_backend", SM_PATH . "plugins/ldap_abook_backend/locale");
        textdomain("ldap_abook_backend");
        $s = _($s);
        sq_bindtextdomain("squirrelmail", SM_PATH . "locale");
        textdomain("squirrelmail");
        return $s;
    }

    function debug($s) {
        global $ldapAbookDebug;
        if ($ldapAbookDebug) $this->ldap->writeLog($s);
    }

    function _generateUUID() {
        return 'sm' . md5(uniqid(rand(), true));
    }

    function connect($doBind = true) {
        $this->debug("abook_LdapBackend::connect - HOST: $this->host");
        if (!$this->ldap->connect($this->host, $this->options, $this->useTls)) {
            $this->disconnect($this->_("Can't connect to LDAP server!"));
        }

        if ($doBind) {
            $this->debug("abook_LdapBackend::connect - bind as USER: $this->bindDn PASSWORD: ********* SELFAUTH: $this->useSelfAuth");
            if (!$this->ldap->bind($this->bindDn, $this->bindPassword)) {
                $this->disconnect($this->_("Can't login to LDAP server!"));
            }
        }

        return true;
    }

    function disconnect($errorText = "") {
        $this->ldap->disconnect();
        if ($errorText != "" || $this->ldap->failed) {
            return $this->set_error($errorText);
        }
        return true;
    }

    function getBase() {
        $base = $this->base;
        if (!empty($this->containerDnAttr)) {
            $base = $this->containerDnAttr . "," . $base;
        }
        return $base;
    }

    /**
     * private
     *
     * @return string Full name string
     */
    function _calculateFullName_object(&$object) {
        $fname = (!empty($object['givenname'][0]) ? $object['givenname'][0] : '');
        $lname = (!empty($object['sn'][0]) ? $object['sn'][0] : '');
        return $this->_calculateFullName($fname, $lname);
    }

    /**
     * private
     *
     * @return string Full name string
     */
    function _calculateFullName_userdata(&$userdata) {
        $fname = (!empty($userdata['firstname']) ? $userdata['firstname'] : '');
        $lname = (!empty($userdata['lastname']) ? $userdata['lastname'] : '');
        return $this->_calculateFullName($fname, $lname);
    }
    
    /**
     * This plugin was configured to have as an "$ldapAbbokAttrNickname" 
     * something other than 'cn'. In the end, 'cn' was not calculated.
     * We *have* to provide a 'cn' out of the first name & last name,
     * because it is a MUST in the relevant objectclasses, and also
     * because it is used as the RDN.
     * private
     */
    function _calculateFullName($fname, $lname) {
        global $ldapAbookFullnameTemplate;
        $cn = $ldapAbookFullnameTemplate;
        $cn = str_replace("%firstname", $fname, $cn );
        $cn = str_replace("%lastname", $lname, $cn );
        $cn = trim($cn); // In case e.g. firstname was empty.
        return $cn;
        /* TODO / FIXME - if both are empty, provide a sane & unique RDN */
    }

    function convertToLdap($userdata, $modification) {
        global $ldapAbookValidateEmail, $ldapAbookFullnameTemplate;

        if ($ldapAbookValidateEmail) {
            $s = $userdata["email"];
            $len = strlen($s);
            for ($i = 0; $i < $len; $i++) {
                if (ord($s[$i]) > 127) {
                    $this->missingField = $this->_("Invalid character in email!");
                    return false;
                }
            }
        }

        $object = array();
        for ($i = 0; $i < count($this->ldapAttrs); $i++) {
            $s = $userdata[$this->squirrelAttrs[$i]];
            if ($s != "") {
                $object[$this->ldapAttrs[$i]] = $s;
            }
            else if ($this->requiredAttrs[$i]) {
                $this->missingField = $this->requiredMsgs[$i];
                return false;
            }
            else if ($modification) {
                $object[$this->ldapAttrs[$i]] = array();
            }
        }
        if(!isset($object['cn'])) {
            $object['cn'] = $this->_calculateFullName_userdata($userdata);
        }
        return $object;
    }

    /**
     * @return void
     */
    function validateObject(&$object) {
        if(!isset($object['cn'])) {
            $object['cn'] = $this->_calculateFullName_object($object);
        }
    }

    /**
     * Search address function
     * @param expr string search expression
     * @param boolean $exactSearch
     * @param boolean $narrowmatch If this is set to true, then the search
     *   filter will only be related to the "attrNickname" attribute. This
     *   will make sure that e.g. the "Edit Addressbook entry" function
     *   in Squirrelmail will have only one entry to edit and will not
     *   match any other entries by accident.
     * @return mixed
     */
    function search($expr, $exactSearch = false, $narrowmatch = false) {
        global $ldapAbookFullnameTemplate, $default_charset;

        /* To be replaced by advanded search expression parsing */
        if(is_array($expr)) {
            return false;
        }

        if ($expr != "*") {
            $expr = $this->ldap->escapeQuery($expr);
        }

        /* Encode the expression */
        if (!$exactSearch) {
            if(strstr($expr, '*') === false) {
                $expr = "*$expr*";
            }
        }

        if(strtolower($this->attrNickname) == 'cn' || $narrowmatch) {
            $query = $this->attrNickname . '=' . $expr;
        } else {
            $query = '(|('.$this->attrNickname.'='.$expr.')(cn='.$expr.'))';
        }

        // Make sure connection is there
        if(!$this->connect()) {
            return false;
        }

        $base = $this->getBase();
        $this->debug("abook_LdapBackend::search - search entries in DN: $base QUERY: $query");
        /* Here we add the cn in the attributes to retrieve, in case a third
         * party client uses only that to put the full name, instead of putting 
         * it in sn + givenName.
         */
        if(!in_array('cn', $this->ldapAttrs)) {
            $searchAttrs = array_merge($this->ldapAttrs, array('cn'));
        } else {
            $searchAttrs = $this->ldapAttrs;
        }
        $objects = $this->ldap->searchObjects($base, $query, $searchAttrs, 'ONELEVEL');

        $cnt = $objects["count"];
        $ret = array();
        if ($cnt == 0) {
            $this->disconnect();
            return $ret;
        }

        // Convert LDAP result objects into SquirrelMail Address Book objects
        for($i = 0 ; $i < $cnt ; $i++) {
            $object = array();
            $object['dn'] = $objects[$i]['dn'];
            for ($j = 0; $j < count($this->ldapAttrs); $j++) {
                $key = $this->ldapAttrs[$j];
                if (isset($objects[$i][$key][0])) {
                    $s = $objects[$i][$key][0];
                } else {
                    $s =  '';
                }
                $object[$this->squirrelAttrs[$j]] = (!empty($s) ? $s : "");
            }
            $s = $ldapAbookFullnameTemplate;
            $s = str_replace("%firstname", $object["firstname"], $s);
            $s = str_replace("%lastname", $object["lastname"], $s);

            /* This check is a special hack for users of third party addressbok
             * UAs. Some UAs (example: Evolution v2.8) might write an addressbook
             * entry that consists of 'cn' and 'sn', but not 'givenName'. As a
             * result, the full name can only be extracted from the 'cn' attribute.
             * So, we use that in here in order to display it properly in
             * Squirrelmail contacts list.
             */
            if(!empty($objects[$i]['cn'][0]) && strlen($objects[$i]['cn'][0]) > strlen($s)) {
                $object["name"] = $objects[$i]['cn'][0];
                /* the firstname must also be deducted from cn, in case it
                 * is missing, in order to enable sane editing.
                 */
                if(empty($object['firstname'])) {
                    switch($ldapAbookFullnameTemplate) {
                    case "%lastname %firstname":
                        // cn: "firstname lastname"
                        // sn: "lastname"
                        // givenName: "firstname" <- deduct
                        $object['firstname'] = trim(substr($object['name'], 0, strpos($object['name'], $object['lastname'])));
                        break;

                    case "%firstname %lastname":
                    default: 
                        // cn: "firstname lastname"
                        // sn: "lastname"
                        // givenName: "firstname" <- deduct
                        $object['firstname'] = trim(substr($object['name'], 0, strpos($object['name'], $object['lastname'])));
                        break;
                    }
                }
            } else {
                $object["name"] = $s;
            }
            $object["backend"] = $this->bnum;
            $object["source"] = &$this->sname;

            /* Special case for entries created by other (third-party) addressbook
             * UAs, which have not provided a nickname.
             * When $this->attrNickname != 'cn' and the $this->attrNickname value
             * does not exist in an entry, we must create one or else Squirrelmail
             * will not be able to handle this entry.
             *
             * We also use this hack later in order to refer to this entry.
             */
            // !!!!!!!!! XXX XXX XXX 
            // if(empty($object['nickname'])) {
                $object['nickname'] = 'dn:::'.$object['dn'];
            // }
            // $object['friendly_nickname'] = $this->_human_readable_nickname($object['nickname']);
            //
            //
            if(!empty($object['nickname'])) {
                $object['friendly_nickname'] = $object['nickname'];
            } else {
                $object['friendly_nickname'] = $this->_human_readable_nickname($object['nickname']);
            }
            array_push($ret, $object);
        }
        /*
        print '<pre>';
        print_r($ret);
        print '</pre>';
        exit;
         */
        $this->disconnect();
        return $ret;
    }
    
    /**
     * Decode our special "alias" to LDAP dn.
     */ 
    function _encoded_alias_to_dn($enc_alias) {
        return substr($enc_alias, 5);
    }

    /**
     * Decode our special "alias" to something usable to lookup that entry.
     * e.g.
     *    dn:::cn=Firstname Lastname,ou=contacts,dc=example,dc=org 
     *    will be transformed to
     *    array('cn' => 'Firstname Lastname')
     */
    function _encoded_alias_to_rdn_part($enc_alias) {
        $dn = $this->_encoded_alias_to_dn($enc_alias);
        return $this->_dn_to_rdn($dn);
    }

    function _dn_to_rdn($dn, $type = 'array') {
        $parts = split(',', $dn);
        $parts2 = split('=', $parts[0], 2);
        if($type == 'array') {
            return array($parts2[0] => $parts2[1]);
        } else {
            return $parts2[0] . '=' .$parts2[1];
        }
        return $ret;
    }
    
    function _rdn_part_to_human_readable($rdn_part) {
        $tmp = array_values($rdn_part);
        return $tmp[0];
    }

    function _human_readable_nickname($nickname) {
        if(empty($nickname)) {
            return $this->_("None specified");
        }

        if(strpos($nickname, 'dn:::') === 0) {
            $rdn_part = $this->_encoded_alias_to_rdn_part($nickname);
            $rdn_part_keys = array_keys($rdn_part);
            if($rdn_part_keys[0] == 'uid') {
                // Not exactly human readable
                return '** <small><i>'. $this->_("No nickname specified") . '</i></small> **';
            }

            // For other RDNs such as 'cn', 'displayname' or whatever:
            return htmlspecialchars($this->_rdn_part_to_human_readable($rdn_part));
            return '<i>' . htmlspecialchars($this->_rdn_part_to_human_readable($rdn_part)) . '</i>';
        }
        return $nickname;
    }

    /**
     * Convert expression array('cn' => 'Firstname Lastname') to 
     * search query 'cn:dn:=Firstname Lastname
     */
    function _rdn_part_to_search_query($rdn_part) {
        foreach($rdn_part as $attr=>$val) {
            return '('.$attr.':dn:='.$this->ldap->escapeQuery($val).')';
        }
    }
     
    /**
     * Lookup alias
     * @param alias string
     */
    function lookup($alias) {
        if (empty($alias)) {
            return array();
        }
        $narrowmatch = true;
        
        if(strpos($alias, 'dn:::') === 0) {
            $narrowmatch = false;
            $rdn_part = $this->_encoded_alias_to_rdn_part($alias);
        }
        $objects = $this->search($alias, true, $narrowmatch);
        return !empty($objects) ? $objects[0] : array();
    }
    
    /**
     * This will do a lookup as per function lookup(), but will return an LDAP object 
     * rather than squirrelmail-formatted data.
     *
     * @param string $alias
     * @return $array
     * @author Alexandros Vellis <avel@noc.uoa.gr>
     */
    function lookup_extended($alias) {
        if (empty($alias)) {
            return array();
        }
        if(strpos($alias, 'dn:::') === 0) {
            $rdn_part = $this->_encoded_alias_to_rdn_part($alias);
        }

        if(isset($rdn_part)) {
            $query = $this->_rdn_part_to_search_query($rdn_part);

        } elseif(strtolower($this->attrNickname) == 'cn') {
            // ???
            $query = $this->attrNickname.'='.$this->ldap->escapeQuery($alias);
        } else {
            $query = '(|('.$this->attrNickname.'='.$this->ldap->escapeQuery($alias).')(cn='.$this->ldap->escapeQuery($alias).'))';
        }

        // Make sure connection is there
        if(!$this->connect()) {
            return false;
        }

        $base = $this->getBase();
        $this->debug("abook_LdapBackend::lookup_extended - extended lookup of $alias in DN: $base QUERY: $query");
        $entries = $this->ldap->searchObjects($base, $query, array(),'ONELEVEL');
        if($entries['count'] == 1) {
            $ret = $entries[0];
        } else {
            $ret = array();
        }
        $this->disconnect();
        return $ret;
    }

    /**
     * List all addresses
     * @return array
     */
    function list_addr() {
        return $this->search("*");
    }

    /**
     * Add address
     * @param userdata
     * @return boolean
     */
    function add($userdata) {
        global $ldapAbookObjectClass;

        // See if user exist already
        $this->connect();

        $base = $this->getBase();
        $this->debug("abook_LdapBackend::add - check if alias exists: " . $userdata["nickname"]);
        $dn = $this->ldap->searchDn($base, 
                                    $this->attrNickname . "=" . $this->ldap->escapeQuery($userdata["nickname"]), 
                                    false, false);
        if (!empty($dn)) {
            $this->debug("abook_LdapBackend::add - already exists");
            return $this->set_error(sprintf($this->_("User '%s' already exist"), $userdata["nickname"]));
        }

        // If container object is used, check if it should be created first
        if (!empty($this->containerDnAttr)) {
            if (!$this->ldap->existsDn($base)) {
                $containerAttr = explode("=", $this->containerDnAttr);
                $object = array();
                $object["objectClass"] = $this->containerObjectClass;
                $object[$containerAttr[0]] = $containerAttr[1];
                if (!$this->ldap->addObject($base, $object)) {
                    return $this->disconnect($this->_("Address add operation failed"));
                }
            }
        }

        $this->debug("convert");
        $object = $this->convertToLdap($userdata, false);
        if (!$object) {
            return $this->disconnect($this->missingField);
        }
        $object["objectClass"] = $this->objectClass;
        $dn = $this->attrRDN . "=" . $this->ldap->escapeDn( $this->_generateUUID() ) . "," . $base;
        $this->debug("abook_LdapBackend::add - DN: $dn");
    
        if (!$this->ldap->addObject($dn, $object)) {
            return $this->disconnect($this->_("Address add operation failed"));
        }

        return $this->disconnect();
    }

    /**
     * Modify address
     * @param alias
     * @param userdata
     * @return boolean
     */
    function modify($alias, $userdata) {
        global $ldapAbookObjectClass;
        $this->connect();
        $base = $this->getBase();
        $olddn = $dn = $this->get_entry_dn($alias);

        $rename = false;

        if ($alias != $userdata["nickname"]) {
            // If nickname has changed, the object must always be recreated.
            // FIXME
            //$dn = $this->attrNickname . "=" . $this->ldap->escapeDn($userdata["nickname"]) . "," . $base;
            //$rename = true;
        }

        if( ($newdn = $this->_rename_to_new_rdn_if_needed($olddn) ) != false) {
            $dn = $newdn;
        }

        // Afterwards we will do the actual modification of attributes on the new dn, if 
        // it was renamed.
        $this->debug("abook_LdapBackend::modify - DN: $dn");
        if ($rename == false) {
            $this->debug("abook_LdapBackend::modify - simple modification, ldap_modify()");
            $object = $this->convertToLdap($userdata, true);
            if (!$object) {
                return $this->disconnect($this->missingField);
            }
            // Remove the nickname attribute, it should not be modified.
            unset($object[$this->attrNickname]);
            if (!$this->ldap->modifyObject($dn, $object)) {
                return $this->disconnect($this->_("Address modify operation failed"));
            }
        } else {
            /* -- obsolete -- */
            $this->debug("abook_LdapBackend::modify - renaming of entry");
            $object = $this->convertToLdap($userdata, true);
            if (!$object) {
                return $this->disconnect($this->missingField);
            }

            $current = $this->ldap->readObject($olddn);
            $current = $this->ldap->convertOutputObjectToInputObject($current);
            foreach ($object as $key => $value) {
                if (is_array($value) && count($value) == 0) {
                    unset($current[$key]);
                } else {
                    $current[$key] = $value;
                }
            }

            // Create the new one
            if (!$this->ldap->addObject($dn, $current)) {
                return $this->disconnect($this->_("Address modify operation failed"));
            }

            // Delete the old one
            if (!$this->ldap->deleteObject($olddn)) {
                return $this->disconnect($this->_("Address modify operation failed"));
            }
        }
        return $this->disconnect();
    }

    /**
     * Add a new address book entry: Extended version, which takes as an argument 
     * an LDAP $info array, rather than Squirrelmail's constructed data.
     *
     * @param array $info
     * @return boolean
     */
    function add_extended($info) {
        global $ldapAbookObjectClass, $ldapAbookAttrNickname;
        $this->connect();
        $base = $this->getBase();
        
        // Set up object to add: objectclass, dn
        $info['objectClass'] = $this->objectClass;
        $rdn = $this->attrRDN . "=" . $this->ldap->escapeDn( $this->_generateUUID() );
        $dn = $rdn . ',' . $this->base;

        $this->validateObject($info);

        $this->debug("abook_LdapBackend::addExtended - DN: $dn");
        
        // Check for uniqueness of nickname
        if(isset($info[ strtolower($ldapAbookAttrNickname) ] )) {
            $newnickname = $info[ strtolower($ldapAbookAttrNickname) ][0];
            if($this->_nickname_exists($newnickname) === true) {
                $this->set_error(sprintf($this->_("User '%s' already exists"), $newnickname));
                return false;
            }
        }

        if (!$this->ldap->addObject($dn, $info)) {
            return $this->disconnect($this->_("Address add operation failed"));
        }
        return true;
    }

    /**
     * Modify address book entry: Extended version, which takes as an argument 
     * an LDAP $info array, rather than Squirrelmail's constructed data.
     *
     * @param alias
     * @param array $info
     * @return boolean
     * @todo honor $ldapAbookAlwaysUseConfiguredRdn
     */
    function modify_extended($alias, $info) {
        global $ldapAbookObjectClass, $ldapAbookAlwaysUseConfiguredRdn, $ldapAbookAttrNickname;
        $this->connect();
        $base = $this->getBase();
        $olddn = $dn = $this->get_entry_dn($alias);

        if( ($newdn = $this->_rename_to_new_rdn_if_needed($olddn) ) != false) {
            $dn = $newdn;
        }

        $this->debug("abook_LdapBackend::modifyExtended - DN: $dn");
        
        if(isset($info[ strtolower($ldapAbookAttrNickname) ] )) {
            // user wants to rename the nickname, so we need to do some checking
            $newnickname = $info[ strtolower($ldapAbookAttrNickname) ][0];
            if($this->_nickname_exists($newnickname) === true) {
                $this->set_error(sprintf($this->_("User '%s' already exists"), $newnickname));
                return false;
            }
        }

        if (!$this->ldap->modifyObject($dn, $info)) {
            return $this->disconnect($this->_("Address modify operation failed"));
        }
        return true;
    }
    
    /**
     * Check if a nickname already exists
     *
     * @param string $newnickname
     * @return boolean true if $newnickname alias already exists
     */
    function _nickname_exists($newnickname) {
        $dn = $this->ldap->searchDn($this->base, 
                                    $this->attrNickname . "=" . $this->ldap->escapeQuery($newnickname), 
                                    false, false);
        if (!empty($dn)) {
            return true;
        }
        return false;
    }


    /**
     * Renames an ldap entry to the RDN schema that we use
     *
     * @param string $olddn
     * @return mixed false if no rename was needed, or a string with the new entry
     *         dn if the rename was needed and was successful.
     */
    function _rename_to_new_rdn_if_needed($olddn) {
        global $ldapAbookAlwaysUseConfiguredRdn;

        $ldap_rename = false; // This is the flag for enabling the rename function

        if($ldapAbookAlwaysUseConfiguredRdn) { 
            $oldrdn_array = $this->_dn_to_rdn($olddn);
            $oldrdn_array_keys = array_keys($oldrdn_array);
            if($oldrdn_array_keys[0] != $this->attrRDN) {
                $ldap_rename = true;
            }
        }
        
        if($ldap_rename === false) return false;

        $new_rdn = $this->attrRDN . "=" . $this->ldap->escapeDn( $this->_generateUUID() );
        $new_dn = $new_rdn . ',' . $this->base;

        if(ldap_rename($this->ldap->conn, $olddn, $new_rdn, null, false) == true) {
            $this->debug("abook_LdapBackend::rename - Old DN: $olddn, New DN: $new_dn");
            return $new_dn;
        } else {
            // Rename failed.. How to handle this?
            // ldap_error($this->ldap->conn);
        }
    }
    
    /**
     * Delete addressbook entries
     *
     * @param string alias
     * @return boolean
     */
    function delete_extended($alias) {
        global $ldapAbookObjectClass;
        $this->connect();
        $base = $this->getBase();
        $olddn = $this->get_entry_dn($alias);
        
        $this->debug("abook_LdapBackend::deleteExtended - DN: $olddn");

        if (!$this->ldap->deleteObject($olddn)) {
            return $this->disconnect($this->_("Address modify operation failed"));
        }
        return true;
    }

    /**
     * Return the dn for a specific Squirrelmail "nickname".
     * @param string $alias
     * @return string
     */
    function get_entry_dn($alias) {
        $this->connect();
        $base = $this->getBase();

        /* See if user exists */
        if(strpos($alias, 'dn:::') === 0) {
            $dn = $this->_encoded_alias_to_dn($alias);
            return $dn;
            // ---
            // We could also continue to see if the user exists!
            $rdn_part = $this->_encoded_alias_to_rdn_part($alias);
            $query = $this->_rdn_part_to_search_query($rdn_part);
        } else {
            $query = $this->attrNickname.'='.$this->ldap->escapeQuery($alias);
        }
        $dn = $this->ldap->searchDn($base, $query, false, true);
        if(empty($dn)) {
           return $this->set_error(sprintf($this->_("User '%s' does not exist"), $alias));
        }
        return $dn;
    }

    /**
     * Delete address
     * @param array aliases
     * @return boolean
     */
    function remove($aliases) {
        $this->connect();

        $base = $this->getBase();
        for ($i = 0; $i < count($aliases); $i++) {
            $dn = $this->get_entry_dn($aliases[$i]);
            $this->debug("abook_LdapBackend::remove - alias: $dn");

            if ($dn == "" || !$this->ldap->deleteObject($dn)) {
                $this->debug("abook_LdapBackend::remove - failure");
                return $this->disconnect($this->_("Address delete operation failed"));
            }
        }
        return $this->disconnect();
    }
}
