<?php
/*
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 Alexandros Vellis
 * @package plugins
 * @subpackage image_lightbox
 */
   
/**
 * Attachment handling function
 *
 * @param array $args
 * @return void
 */
function image_lightbox_handle_image_attachment_do(&$Args) {
    $Args[1]['image_lightbox']['href'] = SM_PATH . 'src/download.php?'
         . 'passed_id=' . $Args[3] . '&mailbox=' . $Args[4]
         . '&ent_id=' . $Args[5];
    $Args[1]['image_lightbox']['text'] = _("Show");
    $Args[1]['image_lightbox']['rel'] = 'lightbox';
}

