<?php

define('TO_ROOT', './');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/functions/details.fun.php');
require_once(TO_ROOT.'inc/functions/file.fun.php');
require_once(TO_ROOT.'inc/defines/details.php');


$mod_arr  = array('filetree', 'filelist', 'infos', 'tracker', 'peers');


if(isset($_GET['hash']))
	$hash = $_GET['hash'];
else
{
	logger(LOGDEBUG, "Called Details without hash", __FILE__, __LINE__);
	session_write_close();
	exit("No Hash");
}

if($settings['real_multiuser'] && ($_SESSION['status'] == 1))
{
	$result = $db->query('SELECT COUNT(*) AS c FROM torrents WHERE hash = ? AND ( uid = 0 OR uid = ?)', 'si', $hash, $_SESSION['uid']);
	$h = $db->fetch($result);
	if($h['c'] < 1)
	{
		logger(LOGSECURITY, "User {$_SESSION['uid']} tried to see details for a torrent that is not his own", __FILE__, __LINE__);
		session_write_close();
		exit('Permission denied');
	}
}

if(isset($_GET['mod']) && isset($mod_arr[$_GET['mod']]))
	$mod = $_GET['mod'];
else
	$mod = $settings['details_def_mode'];

$detailsmenu = '<ul class="tabs">';
foreach($mod_arr as $k => $v)
{
	if($k == $mod)
		$detailsmenu .= "<li class=\"viewselon\"><a href=\"details.php?mod=$k&amp;hash=$hash$sid\" alt=\"{$lng['goto']} {$lng[$v]}\">{$lng[$v]}</a></li>";
	else
		$detailsmenu .= "<li><a href=\"details.php?mod=$k&amp;hash=$hash$sid\" alt=\"{$lng['goto']} {$lng[$v]}\">{$lng[$v]}</a></li>";
}
$detailsmenu .= '</ul>';

$detailscontent  = "<form action=\"control.php?mod=$mod&amp;hash=$hash&amp;return=details$sid\" method=\"post\">";

switch($mod)
{
	case 0:
		$detailscontent .= page_tree($hash);
		break;
	case 1:
		$detailscontent .= page_list($hash);
		break;
	case 2:
		$detailscontent .= page_infos($hash);
		break;
	case 3:
		$detailscontent .= page_tracker($hash);
		break;
	case 4:
		$detailscontent .= page_peers($hash);
		break;
}

if(isset($bodyonload))
	$out->bodyonload = $bodyonload;
$out->content = "<div id=\"main\"><div id=\"content\">$detailsmenu<div class=\"tabsbody\">$detailscontent</form></div></div></div>";
$out->addJavascripts('js/details.js');

$out->renderPage($settings['html_title'], true, true);

?>
