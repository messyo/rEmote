<?php

if(!IN_CP)
	$out->redirect("controlpanel.php$qsid");

$cdata = $chost = '';

if(isset($_GET['kill']) && is_numeric($_GET['kill']))
{
	$db->query('DELETE FROM cookies WHERE cid = ? AND uid = ?', 'ii', $_GET['kill'], $_SESSION['uid']);
	if($db->affected_rows())
		$success = $lng['cookiedel'];
	else
		$error   = $lng['cookienodel'];
}
else if(isset($_POST['cookiesubmit']))
{
	if(!isset($_POST['cookiehost']) || trim($_POST['cookiehost']) == '')
		$error = $lng['nochost'];
	else
		$chost = trim($_POST['cookiehost']);
	if(!isset($_POST['cookiedata']) || trim($_POST['cookiedata']) == '')
		$error = $lng['nocdata'];
	else
		$cdata = trim($_POST['cookiedata']);
	$result = $db->query('SELECT COUNT(*) AS anz FROM cookies WHERE uid = ? AND host = ?', 'is',
											$_SESSION['uid'],
											$_POST['cookiehost']);
	if(!(($h = $db->fetch($result)) && ($h['anz'] == 0)))
		$error = $lng['hostexists'];

	if(!isset($error))
		$db->query('INSERT INTO cookies (uid, host, cookies) VALUES (?, ?, ?)', 'dss',
						$_SESSION['uid'],
						$chost,
						$cdata);
	if($db->affected_rows())
		$success = $lng['cookieadded'];
}

$result = $db->query('SELECT cid, host, cookies FROM cookies WHERE uid = ?', 'i', $_SESSION['uid']);

if(!$db->num_rows($result))
	$ctable = "<div class=\"notify\">{$lng['nocookies']}</div>";
else
{
	$ctable  = "<table id=\"cookietable\"><thead><tr><td class=\"tableheadline\" colspan=\"3\"><h2>{$lng['cookies']}</h2></td></tr>";
	$ctable .= "<tr><td>{$lng['cookiehost']}</td><td>{$lng['cookiedata']}</td><td><img src=\"{$imagedir}delete.png\" alt=\"{$lng['delete']}\" /></td></tr></thead>";
	while($h = $db->fetch($result))
		$ctable .= sprintf('<tr><td class="cookiehost">%s</td><td class="cookiedata">%s</td><td><a href="controlpanel.php?mod=%d&amp;kill=%d%s" title="%s"><img src="%sdelete.png" alt="%s" /></a></td></tr>',
								 htmlspecialchars($h['host'],    ENT_QUOTES),
								 htmlspecialchars($h['cookies'], ENT_QUOTES),
								 $mod,
								 $h['cid'],
								 $sid,
								 $lng['rmcookie'],
								 $imagedir,
								 $lng['delete']);
	$ctable .= '</table>';
}

$cnew  = "<fieldset class=\"box\" id=\"cookienew\"><legend>{$lng['newcookie']}</legend>";
$cnew .= "<form action=\"controlpanel.php?mod=$mod$sid\" method=\"post\"><table>";
$cnew .= "<tr><td><label for=\"cookiehost\">{$lng['cookiehost']}</label></td><td><input type=\"text\" class=\"longtext\" value=\"$chost\" name=\"cookiehost\" id=\"cookiehost\" /><br /><span class=\"hint\">{$lng['chostexmpl']}</td></tr>";
$cnew .= "<tr><td><label for=\"cookiedata\">{$lng['cookiedata']}</label></td><td><input type=\"text\" class=\"longtext\" value=\"$cdata\" name=\"cookiedata\" id=\"cookiedata\" /><br /><span class=\"hint\">{$lng['cdataexmpl']}</td></tr>";
$cnew .= "<tr><td>&nbsp;</td><td><input type=\"submit\" class=\"submit\" name=\"cookiesubmit\" value=\"{$lng['add']}\" /></td></tr>";
$cnew .= "</table></form></fieldset>";

if(isset($error))
	$cpout = "<div class=\"error\">$error</div>$cnew$ctable";
else if(isset($success))
	$cpout = "<div class=\"success\">$success</div>$cnew$ctable";
else
	$cpout = $cnew.$ctable;

