<?php
/**
 * annotate_tests.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Test script that showcases features of imapmetadata.
 *
 * @author Alexandros Vellis <avel+devel@noc.uoa.gr>
 * @copyright 2005-2006 The SquirrelMail Project Team, Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: annotate_tests.php,v 1.2 2006/11/01 11:56:45 avel Exp $
 * @package plugins
 * @subpackage imapmetadata
 */

if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    define('SM_PATH','../../');
    require_once(SM_PATH . 'include/validate.php');
    require_once(SM_PATH . 'functions/global.php');
}

require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'plugins/imapmetadata/imap_annotate.php');
require_once(SM_PATH . 'functions/date.php');
require_once(SM_PATH . 'functions/mime.php');
require_once(SM_PATH . 'functions/mailbox_display.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/plugin.php');


/* lets get the global vars we may need */
sqgetGlobalVar('key',       $key,           SQ_COOKIE);
sqgetGlobalVar('username',  $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',$onetimepad,    SQ_SESSION);
sqgetGlobalVar('delimiter', $delimiter,     SQ_SESSION);
sqgetGlobalVar('base_uri',  $base_uri,      SQ_SESSION);
sqgetGlobalVar('imapmetadatatest', $imapmetadatatest, SQ_GET);

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

// Sample ways to call sqimap_get_annotation():
//
// $annotations = sqimap_get_annotation($imapConnection, '*', '/*', array('value'));
// $annotations = sqimap_get_annotation($imapConnection, '*', '/*', array('value.shared'));
// $annotations = sqimap_get_annotation($imapConnection, 'INBOX', '/comment', array('value.shared'));

/* Presentation */

// displayPageHeader($color, 'None');
echo '
<html>
<head>
	<title></title>
	<link rel="StyleSheet" href="dtree.css" type="text/css" />
	<script type="text/javascript" src="dtree.js"></script>

</head>';

echo '<h1>Annotation Tests</h1>

<a href="annotate_tests.php?imapmetadatatest=showall_internal">
Show Annotations (Internal Flat Structure)
</a>
|
<a href="annotate_tests.php?imapmetadatatest=showall_internal_tree">
Show All Annotations (Internal Entry Tree Structure)
</a>
|
<a href="annotate_tests.php?imapmetadatatest=showall">
Show All Annotations (Flat Pretty Print)
</a>
|
<a href="annotate_tests.php?imapmetadatatest=showall_tree">
Show All Annotations (Entry Tree Pretty Print)
</a>
|
<a href="annotate_tests.php?imapmetadatatest=set">
Set an Annotation
</a>
';


if(strstr($imapmetadatatest, 'tree')) {
   $tree = true;
} else {
   $tree = false;
}

switch($imapmetadatatest) {
    case 'showall_internal':
    case 'showall_internal_tree':
        $annotations = sqimap_get_annotation($imapConnection, '*', '/*', '*', $tree);
        sm_print_r($annotations);
        break;

    case 'showall':
    case 'showall_tree':
        $annotations = sqimap_get_annotation($imapConnection, '*', '/*', '*', $tree);

        if(!$tree) {
            foreach($annotations as $mbox => $anns) {
                echo '<h2>'.imap_utf7_decode_local($mbox).'</h2>';
                
                echo '<table border="1">';
                foreach($anns as $entry => $attributes) {
                    echo '<tr><td rowspan="'.sizeof($attributes).'">'.$entry.'</td>';
                    foreach($attributes as $attr => $values) {
                        echo '<td><em>'.$attr.'</em> is: ';
                        foreach($values as $scope => $val) {
                            echo '<strong>'.htmlspecialchars($val).'</strong> ('.$scope.') ';
                        }
                        echo '</td></tr>';
                    }
                }
                echo '</table>&nbsp;';
            }
        } else {
            echo '<div class="dtree">
    	    <p><a href="javascript: d.openAll();">open all</a> | <a href="javascript: d.closeAll();">close all</a></p>
	        <script type="text/javascript">';;
            echo "d = new dTree('d');
		    d.add(0,-1,'Server Annotations Tree');";
            dtree_recursive_array($annotations);
            echo '
		    document.write(d);
	        </script>
            </div>
            <p><small><a href="http://www.destroydrop.com/javascripts/tree/" target="_blank">dtree</a> is <a href="mailto&#58;drop&#64;destroydrop&#46;com">&copy;2002-2003 Geir Landr&ouml;</a></small></p>
            ';
        }
        break;

    case 'set':
    case 'delete':

        $boxes = sqimap_mailbox_list_all($imapConnection);

        $mailbox = "INBOX";
        $entry = "/comment";
        $attribute = "value";
        $scope = 'priv';
        $value = 'This is a test.';
        
        sqgetGlobalVar('mailbox', $mailbox, SQ_POST);
        sqgetGlobalVar('entry', $entry, SQ_POST);
        sqgetGlobalVar('attribute', $attribute, SQ_POST);
        sqgetGlobalVar('scope', $scope, SQ_POST);
        sqgetGlobalVar('value', $value, SQ_POST);
        sqgetGlobalVar('addannotation', $addannotation, SQ_POST);
        sqgetGlobalVar('deleteannotation', $deleteannotation, SQ_POST);
        
        echo '<form action="annotate_tests.php?imapmetadatatest=set" method="POST">

        Mailbox: '.imapmetadata_mailboxlist('mailbox', $mailbox) .'
        <br/>

        Entry: <input type="text" size="30" name="entry" value="'.$entry.'" />
        <br/>

        Attribute: <input type="text" size="30" name="attribute" value="'.$attribute.'" />
        <br/>

        Scope: <select name="scope" size="1">
        <option>priv</option>
        <option>shared</option>
        </select>
        <br/>


        Value: <input type="text" size="30" name="value" value="'.$value.'" /> 
         <small>(Not used when deleting)</small>
        <br/>

        <input type="submit" value="Add Annotation" name="addannotation">
        <input type="submit" value="Delete Annotation Altogether" name="deleteannotation">
        </form>';


        if(isset($addannotation)) {
            if(sqimap_set_annotation($imapConnection, $mailbox, $entry,
                array($attribute.'.'.$scope => $value), $response, $message, true)) {
                    echo '<p>SETANNOTATION succeeded with adding/replacing annotation! :-)</p>';
            } else {
                    echo '<p>SETANNOTATION failed! Could not add/replace annotation. :-(</p>';
            }
        } elseif(isset($deleteannotation)) {
            if(sqimap_delete_annotation($imapConnection, $mailbox, $entry,
                array($attribute.'.'.$scope), $response, $message, true)) {
                    echo '<p>SETANNOTATION succeeded with deleting annotation! :-)</p>';
            } else {
                    echo '<p>SETANNOTATION failed! Could not delete annoation! :-(</p>';
            }
        }
        
    break;
}



function dtree_recursive_array($array, $parent=0){
   $space="";
   global $counter;
   if(!isset($counter)) {
       $counter = 1;
   }
   if(is_array($array)){
       while (list ($x, $tmp) = each ($array)){
           echo "d.add($counter, $parent, '".$x."', '', '', ''";
           if($x == 'shared') {
               echo ",'img/globe.gif', 'img/globe.gif'";
           } elseif($x == 'priv') {
               echo ",'img/imgfolder.gif', 'img/imgfolder.gif'";
           }
           echo ");\n";
           $counter++;
           echo dtree_recursive_array($tmp, $counter-1);
       }
    } else {
        echo "d.add($counter, $parent, '$array');\n";
        $counter++;
    }
}

/**
 * Print mailbox select widget.
 * 
 * @param string $selectname name for the select HTML variable
 * @param string $selectedmbox which mailbox to be selected in the form
 * @param boolean $sub 
 * @return string
 */
function imapmetadata_mailboxlist($selectname, $selectedmbox, $sub = false) {
	
	global $boxes_append, $boxes_admin, $imap_server_type,
	$default_sub_of_inbox;
	
		if(isset($boxes_admin) && $sub) {
			$boxes = $boxes_admin;
		} elseif(isset($boxes_append)) {
			$boxes = $boxes_append;
		} else {
			global $boxes;
		}
		
		if (count($boxes)) {
	    	$mailboxlist = '<select name="'.$selectname.'">'.
                '<option value="">None, Server as a whole</option>';
	
	    	for ($i = 0; $i < count($boxes); $i++) {
	            	$box = $boxes[$i]['unformatted-dm'];
	            	$box2 = str_replace(' ', '&nbsp;', $boxes[$i]['formatted']);
	            	//$box2 = str_replace(' ', '&nbsp;', $boxes[$i]['formatted']);
	
	            	if (strtolower($imap_server_type) != 'courier' || strtolower($box) != 'inbox.trash') {
	                	$mailboxlist .= "<option value=\"$box\"";
				if($selectedmbox == $box) {
					$mailboxlist .= ' selected=""';
				}
				$mailboxlist .= ">$box2</option>\n";
	            	}
	    	}
	    	$mailboxlist .= "</select>\n";
	
		} else {
	    	$mailboxlist = "No folders found.";
		}
		return $mailboxlist;
}


