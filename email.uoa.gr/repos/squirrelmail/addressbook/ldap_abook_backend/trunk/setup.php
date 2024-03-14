<?php
/**
 * SquirrelMail LDAP Personal Address Book Backend Plugin (ldap_abook_backend)
 * By Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Based on Address book backend template by Tomas Kuliavas
 *
 * Copyright (c) 1999-2005 Daniel Marczisovszky <marczi@dev-labs.com>
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 */

require_once(SM_PATH . "plugins/ldap_abook_backend/config.php");

function squirrelmail_plugin_init_ldap_abook_backend() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['abook_init']['ldap_abook_backend'] = 'ldap_abook_backend_init';
    $squirrelmail_plugin_hooks['abook_add_class']['ldap_abook_backend'] = 'ldap_abook_backend_register';
}

/**
 * Initialize address book backend
 */
function ldap_abook_backend_init(&$argv) {
    if(!in_array('uoapabuser', $_SESSION['ludObjectClasses'])) {
         return;
    }

    global $ldapAbookReplaceFileAbook;

    // Get the arguments
    $hookName = &$argv[0];
    $abook = &$argv[1];
    $r = &$argv[2];

    sq_bindtextdomain("ldap_abook_backend", SM_PATH . "plugins/ldap_abook_backend/locale");
    textdomain("ldap_abook_backend");
    $param = array('name' => _("Personal Address Book (LDAP)"));
    sq_bindtextdomain("squirrelmail", SM_PATH . "locale");
    textdomain("squirrelmail");

    if ($ldapAbookReplaceFileAbook) {
        $abook->backends[1]  = new abook_LdapBackend($param);
        $abook->backends[1]->bnum = 1;
        $abook->localbackendname = $abook->backends[1]->sname;
    } else {
        $r = $abook->add_backend('LdapBackend', $param);
    }
}

function ldap_abook_backend_register() {
    if(!in_array('uoapabuser', $_SESSION['ludObjectClasses'])) {
         return;
    }
    require_once(SM_PATH . 'plugins/ldap_abook_backend/ldap_abook_backend.php');
}

function ldap_abook_backend_version() {
    return '0.2.2-uoa-0.1';
}
