<?php

define('TO_ROOT', '../');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/functions/details.fun.php');
require_once(TO_ROOT.'inc/functions/file.fun.php');
require_once(TO_ROOT.'inc/defines/details.php');

$mod_arr  = array('filetree', 'filelist', 'infos', 'tracker', 'peers');

if(isset($_GET['mod']) && isset($mod_arr[$_GET['mod']]))
	$mod = $_GET['mod'];
else
{
	session_write_close();
	exit("ERROR: {$lng['internerror']}");
}

if(isset($_GET['hash']))
	$hash = $_GET['hash'];
else
{
	logger(LOGDEBUG, "Called Details without hash", __FILE__, __LINE__);
	session_write_close();
	exit("ERROR: No Hash");
}

if($settings['real_multiuser'] && ($_SESSION['status'] == 1))
{
	$result = $db->query("SELECT COUNT(*) AS c FROM torrents WHERE hash = '$hash' AND ( uid = 0 OR uid = {$_SESSION['uid']})");
	$h = $db->fetch($result);
	if($h['c'] < 1)
	{
		logger(LOGSECURITY, "User {$_SESSION['uid']} tried to see details for a torrent that is not his own", __FILE__, __LINE__);
		session_write_close();
		exit('ERROR: Permission denied');
	}
}

$detailscontent = "<input type=\"hidden\" name=\"hash\" value=\"$hash\" />";

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

echo $detailscontent;
session_write_close();

?>
