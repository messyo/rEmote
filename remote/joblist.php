<?php

define('TO_ROOT', './');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/functions/file.fun.php');

if(isset($_GET['kill']) && is_numeric($_GET['kill']))
{
	if($_SESSION['status'] > 1)
		$result = $db->query('UPDATE jobs SET status = ? WHERE jid = ? AND status = ?', 'sis', 'aborted', $_GET['kill'], 'running');
	else
		$result = $db->query('UPDATE jobs SET status = ? WHERE jid = ? AND status = ? AND uid = ?', 'sisi', 'aborted', $_GET['kill'], 'running', $_SESSION['uid']);

	if($db->affected_rows($result))
	{
		$pid = intval($db->one_result($db->query('SELECT pid FROM jobs WHERE jid = ?', 'i', $_GET['kill']), 'pid'));
		if($pid)
		{
			shell_exec('kill -TERM '.$pid);
			sleep(1);
			shell_exec('kill -KILL '.$pid);
		}
		else
			logger(LOGERROR, "Job #{$_GET['kill']} has no proper pid ($pid)", __FILE__, __LINE__);
	}
}
else if(isset($_GET['delete']) && is_numeric($_GET['delete']))
{
	if($_SESSION['status'] > 1)
		$db->query('DELETE FROM jobs WHERE jid = ? AND status != ?', 'is', $_GET['delete'], 'running');
	else
		$db->query('DELETE FROM jobs WHERE jid = ? AND status != ? AND uid = ?', 'isi', $_GET['delete'], 'running', $_SESSION['uid']);
}

$result = $db->query('SELECT jid, pid FROM jobs WHERE status = ? AND starttime < ? ORDER BY jid DESC', 'si', 'running', time()+1);
if($db->num_rows($result))
{
	$ps = explode("\n", shell_exec('ps x'));
	$c  = count($ps);
	$pidlist = array();
	for($x = 1; $x < $c; $x++)
	{
		list($pid, ) = explode(' ', $ps[$x], 2);
		$pidlist[] = $pid;
	}

	while($h = $db->fetch($result))
	{
		if(!in_array(intval($h['pid']), $pidlist))
			$db->query('UPDATE jobs SET status = ? WHERE jid = ?', 'si', 'died', $h['jid']);
	}
}


if($_SESSION['status'] > 1)
	$result = $db->query('SELECT u.name, j.jid, j.status, j.function, j.file1, j.file2, j.starttime FROM jobs j, users u WHERE u.uid = j.uid');
else
	$result = $db->query('SELECT \'\' as name, jid, status, function, file1, file2, starttime FROM jobs WHERE uid = ?', 'i', $_SESSION['uid']);

$joblist  = "<table id=\"jobtable\"><thead><tr><td class=\"tableheadline\" colspan=\"5\"><h2>{$lng['joblist']}</h2></td></tr>";
if($_SESSION['status'] > 1)
	$joblist .= "<tr><td>{$lng['user']}</td><td>{$lng['status']}</td><td>{$lng['job']}</td><td>{$lng['starttime']}</td><td>&nbsp;</td></tr></thead>";
else
	$joblist .= "<tr><td>&nbsp;</td><td>{$lng['status']}</td><td>{$lng['job']}</td><td>{$lng['starttime']}</td><td>&nbsp;</td></tr></thead>";

if($db->num_rows($result))
{
	while($h = $db->fetch($result))
	{
		$starttime = date('d.m.Y H:i:s', $h['starttime']);

		switch($h['status'])
		{
			case 'complete':
				$status = "<span class=\"success\">{$lng['job'.$h['status']]}</span>";
				break;
			case 'died':
			case 'error':
			case 'unabled':
				$status = "<span class=\"error\">{$lng['job'.$h['status']]}</span>";
				break;
			default:
				$status = $lng['job'.$h['status']];
				break;
		}

		if($h['status'] == 'running')
			$links = "<a href=\"joblist.php?kill={$h['jid']}$sid\" title=\"{$lng['killjob']}\"><img src=\"{$imagedir}stop.png\" alt=\"Kill\" /></a>";
		else
			$links = "<a href=\"joblist.php?delete={$h['jid']}$sid\" title=\"{$lng['deletejob']}\"><img src=\"{$imagedir}delete.png\" alt=\"Delete\" /></a>";


		$job = lng('job'.$h['function'], '<em>'.cutMiddle($h['file1'], 50).'</em>', '<em>'.cutMiddle($h['file2'], 50).'</em>');
		$joblist .= "<tr><td>{$h['name']}</td><td>$status</td><td>$job</td><td>$starttime</td><td>$links</td></tr>";
	}
}
else
	$joblist .= "<tr><td colspan=\"5\"><div class=\"notify\">{$lng['nojobs']}</div></td></tr>";
$joblist .= '</table>';

$out->content = "<div id=\"main\"><div id=\"content\">$joblist</div></div>";
$out->renderPage($settings['html_title'], true, true);

?>
