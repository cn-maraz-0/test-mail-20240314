<?php
/**
 * config.php
 *
 * Configuration file for ldapfolderinfo plugin.
 *
 * @author Alexandros Vellis <avel@noc.uoa.gr>
 * @package plugins
 * @subpackage ldapfolderinfo
 */

/**
 * @var int Percentage of quota, above which the warning will be shown.
 */
$warn_percent = 80;

/**
 * @var boolean Use Pixmaps or just colors for the bar?
 */
$use_pixmap = true;

/**
 * @var int Height of the bar in pixels.
 */
$height = 16;

/**
 * @var int Width of the edges of the quota bar in the left frame.
 */
$edge_width = 5;

/**
 * @var boolean Ask LDAP for folder information?
 */
$askldap = true;

/* Following parameters apply if $use_pixmap is true */

global $data_dir, $username;
$chosen_theme = getpref($data_dir, $username, 'chosen_theme');
if(strstr($chosen_theme, 'uoa')) {
    $theme = 'uoa';
    $frmt = 'png';
} else {
    $theme = 'aqua';
    $frmt = 'gif';
}

$left_image  = SM_PATH . "plugins/ldapfolderinfo/images/$theme/bar_left.$frmt";
$middle_image = SM_PATH . "plugins/ldapfolderinfo/images/$theme/bar_middle.$frmt";
$right_image = SM_PATH . "plugins/ldapfolderinfo/images/$theme/bar_right.$frmt";

