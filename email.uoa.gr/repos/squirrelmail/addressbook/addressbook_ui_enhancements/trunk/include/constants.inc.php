<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 Alexandros Vellis
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */

/** AJAX Return code for: Entry saved successfully */
define('ABOOK_UI_SAVED_SUCCESSFULLY', 1);
/** AJAX Return code for: Entry saved successfully, with page refresh needed */
define('ABOOK_UI_SAVED_SUCCESSFULLY_UPDATE_NEEDED', 2);
/** AJAX Return code for: Error during Save */
define('ABOOK_UI_ERROR_DURING_SAVE', 3);

/** AJAX Return code for: Entry deleted successfully */
define('ABOOK_UI_DELETED_SUCCESSFULLY', 1);
/** AJAX Return code for: Error during Delete */
define('ABOOK_UI_ERROR_DURING_DELETE', 3);

