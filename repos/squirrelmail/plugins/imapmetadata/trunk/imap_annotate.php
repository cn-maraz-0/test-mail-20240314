<?php
/**
 * Implementation of IMAP METADATA (former ANNOTATEMORE), useful for setting / 
 * getting metadata in supported IMAP server.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * @version $Id: imap_annotate.php,v 1.2 2006/11/01 11:57:35 avel Exp $
 * @author Alexandros Vellis <avel+devel@noc.uoa.gr>
 * @copyright 2005-2007 Alexandros Vellis
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package plugins
 * @subpackage imapmetadata
 */

/**
 * Get Annotation from server.
 *
 * @param object $imap_stream
 * @param string $mailbox Name of mailbox, encoded in UTF-7-IMAP, or empty if
 *   the annotation(s) for the server as a whole is desired.
 * @param mixed $entry A string or an array of strings that describe the entry
 *   specifiers.
 * @param mixed $attribs A string or an array of strings that describe the
 *   attribute specifiers.
 * @param boolean $tree An optional argument, that makes this function return
 *   the annotation array with the entry name as a tree. For instance:
 *   array('INBOX' => array('vendor' => array('cmu') => .... Otherwise, the array
 *   has a simple key: array('INBOX' => array('/vendor/cmu/...') => ...
 *
 * @return array An array of the annotations returned by the server, as:
 * <pre>
 * array('/comment' => array('value.priv' => 'My Comment'))
 * </pre>
 */
function sqimap_get_annotation($imap_stream, $mailbox = '', $entry, $attribs = '', $tree = false) {
    
    if(is_array($entry)) {
        $entry_specifier = '(' . explode($entry, ' ') . ')';
    } else {
        $entry_specifier = $entry;
    }

    $attribute_specifier = '';
    if(is_array($attribs)) {
        if(sizeof($attribs) > 1) {
            $attribute_specifier .= '(';
        }
        for($i=0; $i<sizeof($attribs); $i++) {
            $attribute_specifier .= '"'.$attribs[$i].'"';
            if($i+1 < sizeof($attribs)) {
                $attribute_specifier .= ' ';
            }
        }
        if(sizeof($attribs) > 1) {
            $attribute_specifier .= ')';
        }
    } else {
        $attribute_specifier .= '"*"';
    }

    $query = 'GETANNOTATION "'.$mailbox.'" "'.$entry_specifier.'" '.$attribute_specifier.'';
    $ann = sqimap_run_command($imap_stream, $query, true, $response, $message);
    $annotations = sqimap_parse_annotation_response($ann, $tree);
    return $annotations;
}

/**
 * A really simple interface to sqimap_get_annotation(); should only be used
 * when a single attribute.scope within a single entry is needed to be read.
 * 
 * @param object $imap_stream
 * @param string $mailbox Name of mailbox, encoded in UTF-7-IMAP, or empty if
 *   the annotation for the server as a whole is desired.
 * @param string $entry
 * @param string $attribute
 * @param $scope The scope, one of 'priv' 'shared' or 'both'. If left empty,
 *  'both' is implied.
 * @return array
 * @see sqimap_get_annotation();
 */
function sqimap_get_annotation_simple($imap_stream, $mailbox = '', $entry, $attribute, $scope) {
    return sqimap_get_annotation($imap_stream, $mailbox, $entry, array($attribute.'.'.$scope));
}

/**
 * Set Annotation to server.
 *
 * @param string $mailbox Name of mailbox, encoded in UTF-7-IMAP, or empty if
 *  the annotation(s) for the server as a whole is desired.
 * @param mixed $entry A string or an array of strings that describe the entry
 *  specifiers.
 * @param array $attribs An array of attribute names and its values that are to
 * be added or replaced.  Example:
 *     array('value.priv' => 'Foo', 'value.shared' => 'Bar').
 * @param string $response
 * @param string $message
 * @param boolean $handle_errors
 * @return boolean
 */
function sqimap_set_annotation($imap_stream, $mailbox = '', $entry, $attribs,
  &$response, &$message, $handle_errors) {

    if(is_array($entry)) {
        $entry_specifier = '(' . explode($entry, ' ') . ')';
    } else {
        $entry_specifier = '"'.$entry.'"';
    }

    if(is_array($attribs)) {
        $attribute_specifier = '(';
        foreach($attribs as $a=>$v) {
            $attribute_specifier .= '"'.$a.'" "'.$v.'"';
        }
        $attribute_specifier .= ')';
    }

    $query = 'SETANNOTATION "'.$mailbox.'" '.$entry_specifier.' '.$attribute_specifier;
    sqimap_run_command($imap_stream, $query, false, $response, $message);

    if($response != 'OK') {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $errormsg = "<b><font color=\"$color[2]\">\n"; 

        $errormsg .= _("Server responded with:"). ' ' . $message . ' ';
        if($error == 'TOOBIG') {
            $errormsg .= _("Annotation value is too big.");
        } elseif($error == 'TOOMANY') {
            $errormsg .= _("The maximum number of annotations has been reached.");
        } else {
            $errormsg .= $message;
        }
        $errormsg .= "</b></font>\n";
        error_box($errormsg,$color);
        return false;
    }
    return true;
}

/**
 * Delete an annotation attribute.
 *
 * @param string $mailbox Name of mailbox, encoded in UTF-7-IMAP, or empty if
 *  the annotation(s) for the server as a whole is desired.
 * @param mixed $entry A string or an array of strings that describe the entry
 *  specifiers.
 * @param mixed $attributes A string or an array of strings that describe the
 *  attribute names (e.g. "value.priv").
 * @param string $response
 * @param string $message
 * @param boolean $handle_errors
 * @return boolean
 */
function sqimap_delete_annotation($imap_stream, $mailbox = '', $entry, $attributes, &$response, &$message, $handle_errors) {;
    if(is_array($entry)) {
        $entry_specifier = '(' . explode($entry, ' ') . ')';
    } else {
        $entry_specifier = '"'.$entry.'"';
    }

    if(is_array($attributes)) {
        $attribute_specifier = '(';
        foreach($attributes as $a) {
            $attribute_specifier .= '"'.$a.'" NIL';
        }
        $attribute_specifier .= ')';
    }

    $query = 'SETANNOTATION "'.$mailbox.'" '.$entry_specifier.' '.$attribute_specifier;
    sqimap_run_command($imap_stream, $query, false, $response, $message);

    if($response != 'OK') {
        global $squirrelmail_language, $color;
        set_up_language($squirrelmail_language);
        require_once(SM_PATH . 'functions/display_messages.php');
        $errormsg = "<b><font color=\"$color[2]\">\n"; 
        $errormsg .= _("Server responded with:"). ' ' . $message . ' ';
        $errormsg .= "</b></font>\n";
        error_box($errormsg,$color);
        return false;
    }
    return true;
}


/*
 * Parse annotation response.
 *
 * @param array $data Output from IMAP server 
 * @param boolean $tree Return entry in a tree-like structure?
 * @return array An array of the annotations returned by the server
 */
function sqimap_parse_annotation_response($data, $tree = false) {
    $annotations = array();
    for($i=0; $i<sizeof($data); $i++) {
        if (preg_match('/\* ANNOTATION \"([^\"]*)\" \"([^\"]*)\" \((.*)?\)/', $data[$i], $regs)) {
            $mbox = $regs[1];
            if($mbox == '') {
                $mbox = 'SERVER';
            }
            $entry = $regs[2];
            if(!isset($annotations[$mbox])) {
                $annotations[$mbox] = array();
            }
            /* The following code constructs the entry tree */
            if($tree) {
                /* Tree-like structure requested */
                $en = split('/', $entry);
                $array_pointer = &$annotations[$mbox];
                for($k=1; $k<sizeof($en); $k++) {
                    if(!isset($array_pointer[$en[$k]])) {
                        $array_pointer[$en[$k]] = array();
                    }
                    $array_pointer = &$array_pointer[$en[$k]];
                }
            } else {
                /* Flat-tier (nice word :) ) structure requested */
                if(!isset($annotations[$mbox][$entry])) {
                    $annotations[$mbox][$entry] = array();
                }
                $array_pointer = &$annotations[$mbox][$entry];
            }
            /* Now array_pointer points to the place of the entry tree where
             * the actual values will be stored. */

            $tmp = split('" "',$regs[3]);
            $tmp[0] = str_replace('"', '',$tmp[0]);
            $tmp[sizeof($tmp)-1] = str_replace('"', '', $tmp[sizeof($tmp)-1]);
            for($j=0; $j<sizeof($tmp)-1; $j=$j+2) {
                $tmp2 = split('\.', $tmp[$j]);
                $array_pointer[$tmp2[0]][$tmp2[1]] = $tmp[$j+1];
            }
        } else {
            // Got nothing
        }
    }
    return $annotations;
}

