<?php
/**
 * schemas/descriptions.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage editprofile
 * @version $Id: descriptions.php,v 1.4 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Descriptions to be used in edit profile page (editprofile.php).
 */
$ldq_attributes['homephone']['inputdesc'] = _("Home Telephone Number is completely optional and we recommend that for privacy reasons you do not fill it in.");
$ldq_attributes['homepostaladdress']['inputdesc'] = _("Home Postal Address is completely optional and we recommend that for privacy reasons you do not fill it in.");
$ldq_attributes['labeleduri']['inputdesc'] = _("Here you can enter one or more Addresses of Internet Web Pages (Universal Resource Locators), such as your home page or your project pages.");
$ldq_attributes['description']['inputdesc'] = _("You can optionally fill in a short description of your activity.");
$ldq_attributes['edupersonnickname']['inputdesc'] = _("You can fill in a nickname you are known by, to help people who search for you.");
$ldq_attributes['uoaprivate']['inputdesc'] = _("You can define which attributes you wish to remain private. These attributes will not be shown to people who browse the White Pages.");
$ldq_attributes['uoaprivateinternal']['inputdesc'] = _("You can define which attributes you wish to remain private to University Users, such as Faculty members and students. This should be a subset of the previous private attributes. For instance, you can choose not to keep your email address private to University users, even though you requested to keep it private from the Public White Pages.");

?>
