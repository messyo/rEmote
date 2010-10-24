<?php

if(!defined('IN_MYCP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

$lid = 'new';
$url = $checked = $label = $checked = '';
$public = false;
$changed = false;

$all = false;
$urlext = '';

if(($_SESSION['status'] >= ADMIN) && isset($_GET['displayall']))
{
	$all = true;
	$urlext = '&amp;displayall=true';
}

if(isset($_GET['delete']) && is_numeric($_GET['delete']))
{
	if($all)
		$db->query('DELETE FROM extlinks WHERE lid = ?', 'i', $_GET['delete']);
	else
		$db->query('DELETE FROM extlinks WHERE lid = ? AND uid = ?', 'ii', $_GET['delete'], $_SESSION['uid']);
	$changed = true;
}

if(isset($_POST['save']))
{
	if(isset($_POST["public_$lid"]) && (true == $_POST["public_$lid"]))
		$public = true;
	else
		$public = false;

	$label = trim($_POST["label_$lid"]);
	$url   = trim($_POST["url_$lid"]);
	
	if($label == '')
		$out->addError($lng['invalidlab']);
	if($url == '')
		$out->addError($lng['invalidurl']);
	else if(preg_match('#^javascript#', strtolower($url)))
		$out->addError($lng['invalidurl']);


	if(!$out->hasError())
	{
		$db->query('INSERT INTO extlinks (uid, label, url, public) VALUES (?, ?, ?, ?)',
			'issi',
			$_SESSION['uid'],
			$label,
			$url,
			intval($public)
		);
		$out->addSuccess($lng['saved']);
		$url = $checked = $label = $checked = '';
		$public = false;
		$changed = true;
	}
}


if($changed)
{
	$db->query('DELETE FROM cache WHERE ckey = ?', 's', 'extlinks');
}

if($all)
	$result = $db->query('SELECT lid, public, label, url FROM extlinks');
else
	$result = $db->query('SELECT lid, public, label, url FROM extlinks WHERE uid = ?', 'i', $_SESSION['uid']);

$table  = "<table id=\"extlinktable\"><thead><tr><td colspan=\"4\" class=\"tableheadline\"><h2>{$lng['linklist']}</h2></td></tr>";
$table .= "<td>{$lng['label']}</td><td>{$lng['url']}</td><td>{$lng['public']}</td><td>&nbsp;</td></tr></thead>";


while($h = $db->fetch($result))
{
	$elid    = intval($h['lid']);
	$epublic = ord($h['public']);
	$elabel  = $db->out($h['label']);
	$eurl    = $db->out($h['url']);
	
	if($epublic)
		$img = "yes.png";
	else
		$img = "no.png";

	$line  =	"<tr><td>$elabel</td>";
	$line .= "<td>$eurl</td>";
	$line .= "<td><img src=\"{$imagedir}{$img}\" alt=\"$img\" /></td>";
	$line .= "<td><a href=\"controlpanel.php?mod=$mod&amp;sub=$sub&amp;delete=$elid$urlext$sid\" title=\"{$lng['delete']}\"><img alt=\"{$lng['delete']}\" src=\"{$imagedir}delete.png\" /></td></tr>";
	$table .= $line;
}



if($public)
	$checked = ' checked="checked"';

$line  =	"<tr><td><input type=\"text\" name=\"label_{$lid}\" value=\"$label\" /></td>";
$line .= "<td><input type=\"text\" name=\"url_{$lid}\" value=\"$url\" /></td>";
$line .= "<td><input type=\"checkbox\" name=\"public_{$lid}\" value=\"true\"$checked /></td>";
$line .= "<td><input type=\"submit\" name=\"save\" value=\"{$lng['add']}\" /></td></tr>";
$table .= $line;


$table .= '</table>';


if($_SESSION['status'] >= ADMIN)
{
	if($all)
		$alllink = "<a href=\"controlpanel.php?mod=$mod&amp;sub=$sub$sid\">{$lng['ownlinks']}</a>";
	else
		$alllink = "<a href=\"controlpanel.php?mod=$mod&amp;sub=$sub&amp;displayall=true$sid\">{$lng['alllinks']}</a>";
}
else
	$alllink = '';


$cpout = $out->getMessages()."$alllink<form action=\"controlpanel.php?mod=$mod&amp;sub=$sub$urlext$sid\" method=\"post\">$table</form>";

?>
