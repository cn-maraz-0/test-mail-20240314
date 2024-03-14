<?php
/**
 * policy.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package directory
 * @subpackage addons
 * @version $Id: policy.php,v 1.3 2004/07/20 15:53:12 avel Exp $
 * @copyright (c) 1999-2004 Alexandros Vellis <avel@noc.uoa.gr>
 */

/**
 * Help table for trying to decide on attribute policy stuff.
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

$prev = bindtextdomain ('directory', DIR_PATH . 'locale');
textdomain ('directory');

include_once (DIR_PATH . "html.php");
include_once (DIR_PATH . "constants.php");


/**
 *
 */

displayPageHeader($color, "None", '');

print '<table width="100%" border="2" class="myschema" style="page-break-after: always">'.
	'<tr><td>LDAP Attribute Name</td><td>Description</td><td>Flags</td>'.
	'<td>Person</td><td>Person (Default)</td><td>Privacy</td><td>Editable</td><td>Search By</td><td>Sort By</td>'.
	'<td>Organization</td></tr>';
$toggle = false;
foreach($ldq_attributes as $attr=>$i){
		print '<tr';
		if ($toggle) {
			print ' bgcolor="'.$color[12].'"';
		} else {
			print ' bgcolor="'.$color[4].'"';
		}
		print '>';

	print '<td>'.$attr.'</td>';

	print '<td>'.$i['text'].'</td><td>';
	if(isset($i['important'])) {
		print 'important';
	}
	if(isset($i['url'])) {
		print '<br/>url:'.$i['url'];
	}
	print '</td>';
	
	/* PERSON */
	print '<td align="center">';
	if(in_array($attr, $ldq_enable_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';
	
	/* PERSON (Default) */
	print '<td>';
	if(isset($directory_prefs_default['directory_showattr_'.$attr])
	  && $directory_prefs_default['directory_showattr_'.$attr] == 1 ) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';
	
	
	/* PRIVACY */
	print '<td align="center">';
	if(in_array($attr, $privacy_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';
	/* EDITABLE */
	print '<td align="center">';
	if(in_array($attr, $ldq_editable_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';


	/* SEARCH BY */
	print '<td align="center">';
	if(in_array($attr, $ldq_searchattrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';

	/* SORT BY */
	print '<td align="center">';
	if(in_array($attr, $ldq_sortby_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';

	/* ORGANIZATION */
	print '<td align="center">';
	if(in_array($attr, $ldq_enable_ou_attrs)) {
		print '<img src="images/yes.gif" alt="Yes"/>';
	}
	print '</td>';



	print '</tr>';
}

echo '
</table>
<br/><br/>
<h2>���������</h2>

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
