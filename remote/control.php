<?php

define('TO_ROOT', './');

require_once('inc/global.php');

function stoptorrent($hash)
{
	global $rpc;
	
	$rpc->request('d.stop', array($hash));
	logger(LOGDEBUG, 'Tried to stop '.$hash, __FILE__, __LINE__);
}



function starttorrent($hash)
{
	global $rpc;

	$rpc->request('d.start', array($hash));
	logger(LOGDEBUG, 'Tried to start '.$hash, __FILE__, __LINE__);
}



function deletetorrent($hash)
{
	global $settings, $db, $rpc;

	$rpc->request('d.erase', array($hash));
	logger(LOGADDDEL | LOGDEBUG, 'Deleted '.$hash, __FILE__, __LINE__);
	if($settings['real_multiuser'])
		$db->query('DELETE FROM torrents WHERE hash = ?', 's', $hash);
}



function hashtorrent($hash)
{
	global $rpc;
	
	$rpc->request('d.check_hash', array($hash));
	logger(LOGDEBUG, 'Tried to hash '.$hash, __FILE__, __LINE__);
}






if(isset($_POST['viewchange']))
{
	if(isset($view_arr[$_POST['view']]))
		$_SESSION['viewmode']  = $_POST['view'];
	if(isset($group_arr[$_POST['group']]))
		$_SESSION['groupmode'] = $_POST['group'];
	if($settings['real_multiuser'] && isset($source_arr[$_POST['source']]))
		$_SESSION['sourcemode'] = $_POST['source'];

	if($_SESSION['viewchange'])
		$db->query('UPDATE users SET viewmode = ?, groupmode = ?, sourcemode = ? WHERE uid = ?', 'iiii',
									$_SESSION['viewmode'],
									$_SESSION['groupmode'],
									$_SESSION['sourcemode'],
									$_SESSION['uid']);
}
else if(isset($_GET['sort']))                // User ELSE-if as both cases can't occure at the same time
{
	if(is_numeric($_GET['sort']) && $_GET['sort'] >= 0 && $_GET['sort'] < 20)
	{
		if($_SESSION['sortkey'] == $_GET['sort'])
		{
			if($_SESSION['sortord'][0] == 'A')
				$_SESSION['sortord'] = 'DESC';
			else
				$_SESSION['sortord'] = 'ASC';
		}
		else
		{
			$_SESSION['sortkey'] = $_GET['sort'];
			$_SESSION['sortord'] = 'ASC';
		}
	}
	else
		logger(LOGERROR, 'Invalid Sortkey: '.$_GET['sort'], __FILE__, __LINE__);
}
else if(isset($_GET['hash']) && isset($_GET['ctl']))
{
	$hash = $_GET['hash'];
	if($settings['real_multiuser'] && $_SESSION['status'] < ADMIN)
	{
		$allowed = false;
		$result = $db->query('SELECT uid FROM torrents WHERE hash = ?', 's', $hash);
		if(($h = $db->fetch($result)) && (($h['uid'] == 0) || ($h['uid'] == $_SESSION['uid'])))
			$allowed = true;
		else if($h)
			logger(LOGSECURITY, "USER {$_SESSION['uid']} tries to control torrents that are not his own", __FILE__, __LINE__);
		else
			logger(LOGINFOS, "USER {$_SESSION['uid']} tries to control torrent that not exists", __FILE__, __LINE__);
	}
	else
		$allowed = true;

	if($allowed)
	{
		switch($_GET['ctl'])
		{
			case 'stop':
				stoptorrent($hash);
				break;
			case 'start':
				starttorrent($hash);
				break;
			case 'delete':
				deletetorrent($hash);
				break;
			case 'hash':
				hashtorrent($hash);
				break;
			default:
				logger(LOGDEBUG, "Unknown Ctl-Command \"{$_GET['ctl']}\"", __FILE__, __LINE__);
				break;
		}
	}
}
else if(isset($_POST['multistart_x']) || isset($_POST['multistop_x']) || isset($_POST['multidelete_x']) || isset($_POST['multihash_x']))
{
	if(isset($_POST['multistart_x']))
		$tfunction = 'starttorrent';
	else if(isset($_POST['multistop_x']))
		$tfunction = 'stoptorrent';
	else if(isset($_POST['multidelete_x']))
		$tfunction = 'deletetorrent';
	else
		$tfunction = 'hashtorrent';

	if($settings['real_multiuser'] && $_SESSION['status'] < 2)
	{
		$in = '';
		foreach($_POST['multiselect'] as $hash)
			$in .= ", '$hash'";
		$in = substr($in, 1);
		$result = $db->query("SELECT hash FROM torrents WHERE hash IN ($in) AND (uid = 0 OR uid = {$_SESSION['uid']})");
		while($h = $db->fetch_result($result))
			$tfunction($h['hash']);
	}
	else if(isset($_POST['multiselect']))
	{
		if(is_array($_POST['multiselect']))
			foreach($_POST['multiselect'] as $hash)
				$tfunction($hash);
		else
			$tfunction($_POST['multiselect']);
	}
}
else if(isset($_POST['fileprio']))
{
	$hash = $_REQUEST['hash'];
	$num = $rpc->request('d.get_size_files', array($hash));
	$req = array();
	for($x = 0; $x < $num; $x++)
	{
		if(isset($_POST["priority$x"]))
			$prio = intval($_POST["priority$x"]);
		else
			$prio = 0;
		/* Collect Status-Changes, to send them together, to reduce xmlrpc-overhead */
		$req[] = array('methodName' => 'f.set_priority', 'params' => array($hash, $x, $prio));
	
		if(count($req) >= 10)   /* If 10 Requests are collected, commit */
		{
			$rpc->request('system.multicall', array($req));
			unset($req);
			$req = array();
		}
	}
	if(count($req))        /* If any status changes are left (e.g. because of not reaching the 10-Limit one more time), commit them */
		$rpc->request('system.multicall', array($req));
	/* Now Tell rEmote to use the new Priorities */
	$rpc->request('d.update_priorities', array($hash));
	
	logger(LOGDEBUG, "Updated Prios of $hash", __FILE__, __LINE__);

	/* NOTE: Tried to use multicall for setting priorities.
	 * It worked fine for torrents with not so many files.
	 * I think system.multicalls capacity is limited so a complete
	 * multicall (and a better performance) is not possible.
	 */
}
else if(isset($_POST['maxspeeds']))
{
	if(isset($_POST['maxup']) && is_numeric($_POST['maxup']) && isset($_POST['maxdown']) && is_numeric($_POST['maxdown']))
	{
		$maxup   = 1024 * $_POST['maxup'];
		$maxdown = 1024 * $_POST['maxdown'];

		$rpc->multicall('set_upload_rate',   array($maxup),
							'set_download_rate', array($maxdown));
		
		logger(LOGINFOS, "Changed Maximum-Speeds (by {$_SESSION['uid']})", __FILE__, __LINE__);
	}
}
else if(isset($_POST['refsubmit']))
{
	if(isset($_POST['refmode']) && is_numeric($_POST['refmode']) && $_POST['refmode'] >= 0 && $_POST['refmode'] <= 4)
		$_SESSION['refmode'] = $_POST['refmode'];
	if(isset($_POST['refinterval']) && is_numeric($_POST['refinterval']) && $_POST['refinterval'] > 0 && $_POST['refinterval'] < 2147483647) // No refinterval > Integer
		$_SESSION['refinterval'] = $_POST['refinterval'];
	if($_SESSION['refchange'])
		$db->query('UPDATE users SET refinterval = ?, refmode = ? WHERE uid = ?', 'iii',
						$_SESSION['refinterval'],
						$_SESSION['refmode'],
						$_SESSION['uid']);
}
else if(isset($_POST['priochange']))
{
	$prio_arr = array('off', 'low', 'normal', 'high');
	if(isset($_POST['prio']) && isset($prio_arr[$_POST['prio']]) && isset($_POST['hash']))
	{
		if($settings['real_multiuser'] && $_SESSION['status'] < 2)
		{
			$hash = $_GET['hash'];
			$result = $db->query('SELECT uid FROM torrents WHERE hash = ?', 's', $hash);
			if($h = $db->fetch_result($result) && ($h['uid'] == 0 || $h['uid'] == $_SESSION['uid']))
			{
				logger(LOGSECURITY, "USER {$_SESSION['uid']} tries to stop torrents that are not his own", __FILE__, __LINE__);
				$hash = '';
			}
		}
		else
			$hash = $_POST['hash'];
		if($hash != '')
			$rpc->request('d.set_priority', array($hash, $_POST['prio']));
	}
}

if(isset($_GET['return']))
{
	switch($_GET['return'])
	{
		case 'index':
			$url = "index.php$qsid";
			break;
		case 'torrent':
			$url = "index.php$qsid#{$_GET['hash']}";
			break;
		case 'details':
			$url = "details.php?hash={$_GET['hash']}&mod={$_GET['mod']}&".SID;
			break;
		default:
			$url = "index.php$qsid";
			break;
	}
}
else
	$url = "index.php$qsid";

$out->redirect($url);
?>
