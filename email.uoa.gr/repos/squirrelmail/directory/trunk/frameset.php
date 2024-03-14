<?php
/**
 * frameset.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage standalone
 * @version $Id: frameset.php,v 1.6 2004/07/07 16:37:38 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Main routine for directory window
 */

/**
 * Define's and include's
 */
include_once ('config.php');

if(!$ldq_standalone) {
	header("HTTP/1.0 401 Unauthorized");
	exit();
}


include_once ('standalone/standalone.php');

/* Reload $orgs into cache if switching languages */
if(isset($switch_lang)) {
	unset($_SESSION['orgs']);
	unset($_SESSION['orgs3']);
}


print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>'.  _("Directory Service") .'</title>
<script type="text/javascript">

/***********************************************
* Collapsible Frames script- © Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for use
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/

var columntype=""
var defaultsetting=""

function getCurrentSetting(){
if (document.body)
return (document.body.cols)? document.body.cols : document.body.rows
}

function setframevalue(coltype, settingvalue){
if (coltype=="rows")
document.body.rows=settingvalue
else if (coltype=="cols")
document.body.cols=settingvalue
}

function resizeFrame(contractsetting){
if (getCurrentSetting()!=defaultsetting)
setframevalue(columntype, defaultsetting)
else
setframevalue(columntype, contractsetting)
}

function init(){
if (!document.all && !document.getElementById) return
if (document.body!=null){
columntype=(document.body.cols)? "cols" : "rows"
defaultsetting=(document.body.cols)? document.body.cols : document.body.rows
}
else
setTimeout("init()",100)
}

setTimeout("init()",100)

</script>
</head>

<frameset rows="45%, 55%">
  <frame name="dirbrowse" src="directory.php'. ( (isset($language)) ? '?language='.$language : '' ) .'">
  <frame name="dirresults" src="'.sprintf($ldq_intro_page, substr($language, 0, 2)).'">
</frameset>
  <noframes>
      <P>This frameset document contains:
      <ul>
      <li><a href="directory.php'. ( (isset($language)) ? '?language='.$language : '' ) .'">
          Directory Service Search Page
      </a></li>
      <li><a href="'.sprintf($ldq_intro_page, substr($language, 0, 2)).'">
          Introduction to Directory Services
      </a></li>
      </ul>
  </noframes>
</frameset>
</html>';

?>
