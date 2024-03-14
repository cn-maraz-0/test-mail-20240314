<?php
/**
 * config.php
 *
 * Configuration for Addressbook UI Enhancements
 *
 * @copyright &copy; 2007-2008 The SquirrelMail Project Team, Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */

/**
 * @var array
 * Which attributes to allow viewing (when clicking on name / nickname) and editing (when clicking on "Edit"). 
 * These should be organized in groups.
 */
global $abookAttrs;
$abookAttrs = array(
    'main' => array(
        'givenname', 'sn',
    ),
    'work' => array(
        // department
        'o', 'ou', 'title',
    ),
    'contact' => array(
	    'mail','telephonenumber', 'mobile', 'facsimiletelephonenumber', 'homephone',
    ),
    'address' => array(
    	'l', 'postaladdress', 'postalcode', 'postofficebox','homepostaladdress',
    ),
    'other' => array(
    	'description', 'labeleduri', 
    ),
);

/**
 * @var array
 * How to organize the layout of the groups
 */
global $addressbook_ui_enhancements_editable_attrs_layout;
$addressbook_ui_enhancements_editable_attrs_layout = array(
    'left' => array('main', 'work', 'other'),
    'right' => array('contact', 'address'),
);

/**
 * @var string
 * Link to Help Web Page.
 * Can be left empty.
 */
global $abook_help_url;
$abook_help_url = '';

/**
 * @var string
 * Link to Help Web Page specific to Addressbook Import functions.
 * Can be left empty.
 */
global $abook_help_import_url;
$abook_help_import_url = '';


