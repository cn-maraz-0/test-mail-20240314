<?php
/**
 * This is a sample config file that can be read by scripts
 * from the utils directory of avelsieve.
 *
 * The 'utils' scripts are PHP-CLI scripts intended to be used
 * by administrators or power users, and are used for migration of
 * users' rules, statistics extraction and other batch jobs.
 *
 * THESE SCRIPTS ARE PROVIDED WITH NO SUPPORT.
 *
 * You might need to edit many of these scripts to make them
 * useful to you. They are only provided here only for guidance
 * and in case they might be useful to someone.
 *
 * To enable these scripts, use .htaccess (or equivalent ) to
 * protect the 'utils' directory, and then remove the exit;
 * statement from this file.
 *
 * Licensing Blurb:
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * THIS PROGRAM IS DISTRIBUTED IN THE HOPE THAT IT WILL BE USEFUL,
 * BUT WITHOUT ANY WARRANTY; WITHOUT EVEN THE IMPLIED WARRANTY OF
 * MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE.  SEE THE
 * GNU GENERAL pUBLIC lICENSE FOR MORE DETAILS.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program (in the file COPYING); if not, write to the
 * Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA  02110-1301, USA
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007 Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package avelsieve
 * @subpackage admin-utils
 */

// Remove me after precautions have been taken.
exit(1);

global $authz;

$domain            = '';
$filename          = 'usernames/imap.'.$domain.'.usernames';
$imapServerAddress = 'imap.'.$domain;
$secret            = 'secret';
$proxy_username    = 'cyrusadmin';
$scriptname        = 'phpscript';

// LDAP, if applicable
$ldap_server       = 'ldap'.$domain;
$ldap_bind         = "";
$ldap_secret       = $secret;

