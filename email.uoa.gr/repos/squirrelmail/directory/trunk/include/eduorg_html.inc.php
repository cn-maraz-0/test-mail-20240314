<?php
/**
 * eduorg_html.inc.php
 *
 * Functions specific to eduOrg LDAP Schema and HTML output.
 *
 * Copyright (c) 2003-2005 Alexandros Vellis <avel@noc.uoa.gr>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage main
 * @version $Id: eduorg_html.inc.php,v 1.4 2007/07/05 08:37:26 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */


/**
 * Print "select" options for Restrict to Organizational Units feature.
 * This algorithm needs a properly formatted "$orgs2" array.
 * @param array $orgs2
 * @param int $level
 * @return void
 */
function directory_print_orgs2($orgs2, $level) {
	global $restrict;

	foreach($orgs2 as $id=>$info) {
		$ind = '&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}

		if(isset($info['text'])) {
			echo '<option value="'.$info['dn'].'"';
			if(isset($restrict) && in_array($info['dn'], $restrict)) {
				echo ' selected=""';
			}
			echo '>'.$indent.
			( isset($info['struct']) ? $info['struct'] . ' ' : '' )
			.$info['text']."</option>\n";
		}
		if(isset($info['sub'])) {
			directory_print_orgs2($info['sub'], $level+1);
		}
	}
}

/**
 * Print "select" options for Restrict to Organizational Units feature.
 * This algorithm needs a properly formatted "$orgs3" array.
 * @param array $orgs3
 * @param int $level
 * @global array $restrict
 * @global int $trim_at
 * @return void
 */
function directory_print_orgs3($orgs3, $level) {
	global $restrict, $trim_at;
	foreach($orgs3 as $dn=>$info) {
				print $dn;
		$ind = '&nbsp;&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}
		if(isset($info['text'])) {
			if(isset($trim_at) && mb_strlen($info['text']) > $trim_at && $dn != '*') {
				$text = mb_substr($info['text'], 0, $trim_at);
				$text .= '...';
			} else {
				$text = $info['text'];
			}
			echo '<option value="'.$dn.'"';
			if(isset($restrict) && in_array($dn, $restrict)) {
				echo ' selected=""';
			}
			echo '>'.$indent.
			( isset($info['struct']) ? $info['struct']. ' ' : '' )
			.$text."</option>\n";
		}
		if(isset($info['sub'])) {
			directory_print_orgs3($info['sub'], $level+1);
		}
	}
}

/**
 * Output "Browse" Tree for eduOrg ou's.
 *
 * @param array $orgs3
 * @param int $level
 * @param int $stop_at
 * @param string $current
 * @param array $expand
 * @param int $editlink 0 = do not display edit links, 1 = display edit link
 *  only for currently selected entry, 2 = display edit link for all entries in
 *  the tree, 3 = also show link to new entry (this is actually done in
 *  browse.php or elsewhere).
 * @return void
 */
function directory_print_browse_tree($orgs3, $level, $stop_at = 1, $current = '', $expand = array(), $editlink = 0) {
	global $restrict, $trim_at, $ldq_standalone, $directory_enable_browse;
	
	if($stop_at == 0) {
		return;
	}

	if($ldq_standalone) {
		$target_browse = 'dirbrowse';
		$target_results = 'dirresults';
	} else {
		$target_browse = '_self';
		$target_results = '_self';
	}

	foreach($orgs3 as $dn=>$info) {

		if(isset($niaou)) unset($niaou);
		if(isset($gab)) unset($gab);

		$ind = '&nbsp;&nbsp;&nbsp; ';
		$indent = '';
		if($level > 0 ) {
			for($i=0; $i<$level; $i++) {
				$indent .= $ind;
			}
		}
		if(isset($info['text'])) {
			if(isset($trim_at) && mb_strlen($info['text']) > $trim_at && $dn != '*') {
				$text = mb_substr($info['text'], 0, $trim_at);
				$text .= '...';
			} else {
				$text = $info['text'];
			}
			
			echo $indent;
				
				/* Set up "Rest of URL", for all links below! */
				$restofurl = '';
				if(isset($level)) {
					$restofurl .= '&amp;level='.$level;
				}
				if(isset($expand)) {
					foreach($expand as $exp) {
						$restofurl .= '&amp;expand[]='.urlencode($exp);
					}
				}

			global $formname, $inputname, $popup;
			if(!empty($formname)) {
				$restofurl .= '&amp;formname='.urlencode($formname);
			}
			if(!empty($inputname)) {
				$restofurl .= '&amp;inputname='.urlencode($inputname);
			}
			if(isset($popup) && $popup == 1) {
				$restofurl .= '&amp;popup=1';
			}

			/* Plus/Minus gif, expand/collapse functionality */
			if(array_key_exists('sub', $info)) {
				if(in_array($dn, $expand) ) {
					echo '<a href="browse.php?collapse='.urlencode($dn).$restofurl.'">'.
						'<img src="images/toc-minus.gif" alt="[-]" border="0" /></a>';
				} else {
					echo '<a href="browse.php?expand[]='.urlencode($dn).$restofurl.'#'.urlencode($dn).'">'.
						'<img src="images/toc-plus.gif" alt="[+]" border="0" /></a>';
				}
					
			} else {
					echo '<img src="images/toc-blank.gif" alt="[ ]"border="0" />';
			}

			/* Text & Link Print */
			if($dn == $current) {
				echo '<strong><a name="'.urlencode($dn).'">';
				$niaou = true;

			} elseif($dn == '*') {
				echo '<em>';
				$gab = true;
			} else {
				// echo '<a href="browse.php?dn='.urlencode($dn).$restofurl;
				// echo '&amp;expand[]='.urlencode($dn).'" target="dirresults">';
				
				$selfuri = 'browse.php?dn='.urlencode($dn).$restofurl.
					'&amp;expand[]='.urlencode($dn);
				$selftarget = urlencode($dn);

				echo '<a name="'.urlencode($dn).'" href="showeduorginfo.php?dn='.urlencode($dn).$restofurl.'"';
				echo ' target="'.$target_results.'" onClick="window.location.href=\''.$selfuri.'#'.$selftarget.'\';'.
					// 'window.location.hash=\''.$selftarget.'\';'.
					'return true;">';

			}
			
			echo ( isset($info['struct']) ? $info['struct'] . ' ' : '' ) . $text;

			if(isset($niaou)) {
                echo '</a></strong> ';

                if($directory_enable_browse) {
                    echo '<a href="directory.php?searchform=no&amp;browseorgdn='.urlencode($dn).'" '.
                        'title="'._("Browse All People in this Organizational Unit").'" target="'.$target_results.'">'.
                        '<img src="images/people-16.gif" alt="'. _("Browse") .'" align="center" border="0" /></a>';
                }

				echo ' <a href="directory.php?searchform=yes&amp;restrict[]='.urlencode($dn).'&amp;mode=2" '.
				'title="'._("Search for People in this Organizational Unit") .'" target="'.$target_browse.'">'.
				'<img src="images/search-16.gif" alt="'._("Search").'" align="center" border="0" /></a>';

				if($editlink != 0) {
					echo ' <a href="editeduorginfo.php?dn='.urlencode($dn).'" '.
					'title="'._("Edit Information about this Organizational Unit") .'" target="'.$target_results.'">'.
					'<img src="images/edit.png" alt="'._("Edit").'" align="center" border="0" /></a>';
				}

			} elseif(isset($gab)) {
				echo '</em> ';
				
			} else {
				echo '</a>';
				
				if($editlink >= 2) {
					echo ' <a href="editeduorginfo.php?dn='.urlencode($dn).'" '.
					'title="'._("Edit Information about this Organizational Unit") .'" target="'.$target_results.'">'.
					'<img src="images/edit.png" alt="'._("Edit").'" align="center" border="0" /></a>';
				}
			}
			echo "<br />\n";
		}

		if(isset($info['sub']) && in_array($dn, $expand) ) {
			//print "directory_print_browse_tree(".$info['sub'].", $level+1, $stop_at, $current)\n<br>";
			directory_print_browse_tree($info['sub'], $level+1, $stop_at, $current, $expand, $editlink);
		} elseif(isset($info['sub'])) {
			directory_print_browse_tree($info['sub'], $level+1, $stop_at-1, $current, $expand, $editlink);
		}
	}

	if($editlink > 2 && $level == 0) {
		echo '<div align="center">' .
			' <a href="editeduorginfo.php?new=1" target="'.$target_results.'">'.
			_("Add a new Organizational Unit") . 
			' <img src="images/edit.png" alt="'._("Edit").'" align="center" border="0" /></a></div>';
	}
}

/**
 * Return a nice link using javascript & HTML to the information page of the
 * EduOrgUnitDN specified.
 *
 * @param string $dn EduOrgUnitDN
 * @return string The hyperlink (<a .....>Text</a>)
 * @author avel
 */
function eduorgunit_link($dn) {
    global $orgs, $baseuri, $javascript_on, $lang;
	if($ldq_standalone) {
		$target_browse = 'dirbrowse';
		$target_results = 'dirresults';
	} else {
		$target_browse = '_self';
		$target_results = '_self';
	}

    $dn = strtolower($dn);
    $uri = DIR_PATH .'showeduorginfo.php?dn='.urlencode($dn).'';
    
    if($javascript_on) {
        $out = '<a href="javascript:open_in_new(\''.$uri.'\')"';
    } else {
        $out = '<a href="'.$uri.'" target="_blank"';
    }
    $out .= ' title="'._("Information about this Organizational Unit") .'" target="'.$target_results.'">'.
        '<img src="'.$baseuri.'images/icons/information.png" border="0" valign="middle" />' .
        '<small>('.$orgs[$dn]['struct'].')</small> '.$orgs[$dn]['text'] . '</a>';
    return $out;
}


