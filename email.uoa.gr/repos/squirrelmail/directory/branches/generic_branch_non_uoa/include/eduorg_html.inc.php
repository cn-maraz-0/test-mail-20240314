<?php


/**
 * Print "select" options for Restrict to Organizational Units feature.
 * This algorithm needs a properly formatted "$orgs2" array.
 * @param array $orgs2
 * @param int $level
 * @return void
 * @deprecated
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
			echo '>'.$indent.$info['struct']. ' ' .$info['text']."</option>\n";
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
			if(isset($trim_at) && strlen($info['text']) > $trim_at && $dn != '*') {
				$text = substr($info['text'], 0, $trim_at);
				$text .= '...';
			} else {
				$text = $info['text'];
			}
			echo '<option value="'.$dn.'"';
			if(isset($restrict) && in_array($dn, $restrict)) {
				echo ' selected=""';
			}
			echo '>'.$indent.$info['struct']. ' ' .$text."</option>\n";
		}
		if(isset($info['sub'])) {
			directory_print_orgs3($info['sub'], $level+1);
		}
	}
}

function directory_print_browse_tree($orgs3, $level, $stop_at = 1, $current = '', $expand = array()) {
	global $restrict, $trim_at, $ldq_standalone;
	
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
			if(isset($trim_at) && strlen($info['text']) > $trim_at && $dn != '*') {
				$text = substr($info['text'], 0, $trim_at);
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
			
			echo $info['struct']. ' ' .$text;

			if(isset($niaou)) {
				echo '</a></strong> '.
				'<a href="directory.php?searchform=no&amp;browseorgdn='.urlencode($dn).'" '.
				'title="'._("Browse All People in this Organizational Unit").'" target="'.$target_results.'">'.
				'<img src="images/people-16.gif" alt="'. _("Browse") .'" align="center" border="0" /></a>'.

				' <a href="directory.php?searchform=yes&amp;restrict[]='.urlencode($dn).'&amp;mode=2" '.
				'title="'._("Search for People in this Organizational Unit") .'" target="'.$target_browse.'">'.
				'<img src="images/search-16.gif" alt="'._("Search").'" align="center" border="0" /></a>';
				

			} elseif(isset($gab)) {
				echo '</em> ';
				
			} else {
				echo '</a>';
			}
			echo "<br />\n";
		}

		if(isset($info['sub']) && in_array($dn, $expand) ) {
			//print "directory_print_browse_tree(".$info['sub'].", $level+1, $stop_at, $current)\n<br>";
			directory_print_browse_tree($info['sub'], $level+1, $stop_at, $current, $expand);
		} elseif(isset($info['sub'])) {
			directory_print_browse_tree($info['sub'], $level+1, $stop_at-1, $current, $expand);
		}
	}
}


function directory_find_inferior($restrict, &$inferior_final) {
	global $orgs;
	$inferior = array();
	foreach($restrict as $no=>$restrictdn) {
		foreach ($orgs as $orgdn => $data) {
			if(isset($data['superior']['dn']) && $data['superior']['dn'] == $restrictdn) {
				// print "Hit. $orgdn <BR>";
				$inferior_final[] = $orgdn;
				$inferior[] = $orgdn;
			} else {
				continue;
			}
		}
		if(!empty($inferior)) {
			// print "<BR><BR>calling directory_find_inferior("; print_r($inferior); print_r($inferior_final);
			directory_find_inferior($inferior, $inferior_final);
		}
	}
}

?>
