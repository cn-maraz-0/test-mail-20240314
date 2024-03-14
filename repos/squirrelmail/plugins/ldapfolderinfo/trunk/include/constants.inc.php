<?php
/**
 * constants.inc.php
 *
 * Constants for ldapfolderinfo plugin.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage ldapfolderinfo
 * @version $Id: constants.inc.php,v 1.3 2007/08/23 13:54:56 avel Exp $
 */

/**
 * @var int Quota is not set for the folder.
 */
define('QUOTA_NOT_SET', -1);

/**
 * @var int Quota is not set for the folder, but it is determined by a parent folder.
 */
define('QUOTA_DEFINED_IN_PARENT', 0);

/**
 * @var int Quota is set explicitly for this folder.
 */
define('QUOTA_SET', 1);

