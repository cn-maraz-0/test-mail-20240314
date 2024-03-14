<?php
/**
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 * This file is to be loaded directly to the browser, and serves the CSS styles used by the 
 * addressbook_ui_enhancements plugin.
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team, Alexandros Vellis
 * @package plugins
 * @subpackage addressbook_ui_enhancements
 */

/**
 * Squirrelmail path
 * @ignore
 */
define('SM_PATH','../../');

/** SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');

global $color;

/* ============ Generic ============ */
echo '
img {
    border: none;
    vertical-align: baseline;
}';

/* ============ Informational Messages ============ */
echo '
#abook_message_box {
    border: solid 2px '.$color[0].';
    background: '.$color[4].';
    margin-left: 10px;
    margin-top: 10px;
    padding: 1em 1em 1em 1em;
    text-align: center;
    display: none;
    font-size: 1.3em;
    left: 25%;
    right: 25%;
    top: 2.5em;
    width: 50%;
    position: fixed;
}
#abook_message_refresh {
    margin: 0 auto;
    margin-bottom: 1em;
    text-align: center;
    font-size: 1.3em;
    font-weight: bold;
    display: none;
}
.abook_paragraph {
    width: 90%;
    margin: 0 auto;
}
.abook_ui_button_rows {
    margin-left: 40%;
    margin-right: auto;
    text-align: center;
}

#abook_add_new {
    width: 90%;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}
#abook_add_new_start {
    text-align: center;
    margin-left: auto;
    margin-right: auto;
}
';

/* ============ Addressbook Table Elements ============ */
echo '
.abook_ui_expand_link {
    color: '.$color[7].' !important;
    cursor: pointer;
}
.abook_ui_col_name {
    text-decoration: underline !important;
    font-weight: bold;
}
.abook_info_row {
    margin: 10px 10px 10px 10px;
    display: none;
}
.abook_ui_extended_info {
    background: '.$color[3].';
    border-style: dotted;
    border-color: #dddddd;
    border-width: 0 2px 2px 2px;
    margin: 0 2em 1em 2em;
    padding: 0 1em 1em 1em;
    width: 92%;
    text-align: center;
    float: left;
}

.abook_ui_profile_msgs {
    width: 90%
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    background-color: '.$color[12].';
    border: none;
    display: none;
}
';

/* ============ Addressbook Forms Elements ============ */
echo '
.abook_form_element {
    font-size: 0.9em;
    border: solid 2px '.$color[15].';
    padding: 3px;
    background: '.$color[4].';
}
.abook_form_element_active {
    font-size: 0.9em;
    padding: 3px;
    border: solid 2px '.$color[15].';
    background: '.$color[16].';
}
label.abook_ui_label {  
    display: block; 
    float: left;  
    width: 40%;  
    margin-right: 0.2em;
    text-align: right;
    font-size: 0.8em;
}
.abook_ui_input {
    width: 100%;
    margin: 0 0 0 0;
    padding: 0 0 0 0;
}

legend {
    margin-left: 1em;  
    padding: 0;  
    color: '.$color[6].';  
    font-weight: bold;
}
fieldset ol {  
    margin: 2px 1em 2px 2px;
    padding: 0.9em 3px 0 2px;  
    list-style: none; 
}
fieldset li.abook_ui_item {  
    float: left;  
    clear: left;  
    width: 99%;  
    padding-bottom: 0.2em; 
}
fieldset.abook_ui_fieldset {  
    float: left;  
    clear: left;  
    width: 100%;  
    margin: 0 0 1.5em 0;  
    padding: 0;
    border: 1px dotted #dddddd;
    background-color: transparent;
}
fieldset.submit {  
    float: none;  
    width: auto;  
    border: 0 none #FFF;  
    padding-left: 12em; 
}
';


/* =========== BUTTONS =========== */
echo '
.buttonswrapper {
    padding: 0 30% 0 30%;
}
.buttons a, .buttons button{
    display:block;
    float:left;
    margin:0 7px 0 0;
    background-color: '.$color[4].';
    border:1px solid #dedede;
    border-top:1px solid #eee;
    border-left:1px solid #eee;

    font-size:100%;
    line-height:130%;
    text-decoration:none;
    font-weight:bold;
    color:#565656;
    cursor:pointer;
    padding:5px 10px 6px 7px; /* Links */
}
.buttons button{
    width:auto;
    overflow:visible;
    padding:4px 10px 3px 7px; /* IE6 */
}
.buttons button[type]{
    padding:5px 10px 5px 7px; /* Firefox */
    line-height:17px; /* Safari */
}
.buttons button img, .buttons a img{
    margin:0 3px -3px 0 !important;
    padding:0;
    border:none;
    width:16px;
    height:16px;
}


/* STANDARD */

button:hover, .buttons a:hover{
    background-color:#dff4ff;
    border:1px solid #c2e1ef;
    color:#336699;
}
.buttons a:active{
    background-color:#6299c5;
    border:1px solid #6299c5;
    color:#fff;
}

/* POSITIVE */

button.positive, .buttons a.positive{
    color:#529214;
}
.buttons a.positive:hover, button.positive:hover{
    background-color:#E6EFC2;
    border:1px solid #C6D880;
    color:#529214;
}
.buttons a.positive:active{
    background-color:#529214;
    border:1px solid #529214;
    color:#fff;
}

/* NEGATIVE */

.buttons a.negative, button.negative{
    color:#d12f19;
}
.buttons a.negative:hover, button.negative:hover{
    background:#fbe3e4;
    border:1px solid #fbc2c4;
    color:#d12f19;
}
.buttons a.negative:active{
    background-color:#d12f19;
    border:1px solid #d12f19;
    color:#fff;
}
';

/* ======= ModalBox ======== */
echo file_get_contents(SM_PATH . 'plugins/javascript_libs/modules/modalbox/modalbox.css');

