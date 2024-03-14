<?php
/**
 * policy.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Helper table for trying to decide on attribute policy stuff.
 *
 * @package directory
 * @subpackage addons
 * @version $Id: policy.php,v 1.5 2006/08/01 08:02:55 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 * @todo Translate to English
 */

/**
 * Define's and include's
 */
include_once ('config.php');

if($ldq_standalone) {
	include_once ('standalone/standalone.php');
} else {
	exit;
}

$prev = sq_bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "include/html.php");
include_once (DIR_PATH . "include/constants.php");
include_once (DIR_PATH . "include/edit.php");

$editlink = directory_access_level();
if($editlink == 0 ) {
	print "Access Denied";
	exit;
}

/**
 * Presentation
 */

displayPageHeader($color, "None", '');

foreach($ldq_editable_attrs as $a) {
        echo '<li>'.$ldq_attributes[$a]['text']."</li>\n";
}
print '<table width="100%" border="2" class="myschema" style="page-break-after: always">'.
	'<thead><tr><td>LDAP Attribute Name</td><td>Description</td><td>Flags</td>'.
	'<td>Person</td><td>Person<br/>(Def.)</td><td>Privacy</td><td>Editable</td><td>Search By</td><td>Sort By</td>'.
	'<td>Organization</td></tr></thead>';
$toggle = false;
foreach($ldq_attributes as $attr=>$i){
		print '<tr';
		if ($toggle) {
			print ' bgcolor="'.$color[12].'"';
		} else {
			print ' bgcolor="'.$color[4].'"';
		}
		print '>';

	print '<td><small>'.$attr.'</small></td>';

	print '<td>'.$i['text'].'</td><td>';

	if(isset($i['important'])) {
		print '<small>important</small>';
	}
	if(isset($i['url'])) {
		print '<br/><small>url:'.$i['url'].'</small>';
	}
	print '&nbsp;</td>';
	
	/* PERSON */
	print '<td align="center">';
	if(in_array($attr, $ldq_enable_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';
	
	/* PERSON (Default) */
	print '<td>';
	if(isset($directory_prefs_default['directory_showattr_'.$attr])
	  && $directory_prefs_default['directory_showattr_'.$attr] == 1 ) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';
	
	
	/* PRIVACY */
	print '<td align="center">';
	if(in_array($attr, $privacy_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';
	/* EDITABLE */
	print '<td align="center">';
	if(in_array($attr, $ldq_editable_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';


	/* SEARCH BY */
	print '<td align="center">';
	if(in_array($attr, $ldq_searchattrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';

	/* SORT BY */
	print '<td align="center">';
	if(in_array($attr, $ldq_sortby_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';

	/* ORGANIZATION */
	print '<td align="center">';
	if(in_array($attr, $ldq_enable_ou_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '&nbsp;</td>';

	print '</tr>';
}

echo '
</table>
<br/><br/>
<h2>��������</h2>

<p>�� <b>������</b> �������� ��������� ���� ��� �� ����� ����� ��������� ��
���������� ��������.</p>


<ul>
	<li><b>Person: �������� ��� ����� ��������� �� �����, ���� ��� ��������� ���������, ��� ��� ��������� ���� ������.</b></li>
	<li>Person (Default): ��������� ��� ������������ ���������. ��������� ��� �� �������� ��� �� ��������� by default ��� ������������ ���� ����������.</li>
	<li><b>Privacy: �������� �� ����� ��� ����� ������ �� �������� ��� ����� �������� (Private), ���� ��� �� ����� (������� Internet) ���� ��� ������� ��� �������������.</b></li>
	<li>Editable: ��� ��� ���� ��������� (critical) ��������, �� ����� �� ����� ������ �� ������� ��������. ��� �� ��������� ��������, ���������� ������ ���� �����.</li>
	<li>Search By: �������� �� �� ����� ����������� �� ����� �������, ���� �������� ���������.</li>
	<li>Sort By: �������� �� �� ����� ������ ������� �� ����� ����������, ��� ������������ ���� ����������.</li>

	<li>Organization: �� ���������� �� �� Person, ���� ��� �������� ��� �� ��������� ���� ��������� ���� ����������� ������� (�����, ������ �.��.)</li>
</ul>

</body></html>';
?>
