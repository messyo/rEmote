<?php

require_once(TO_ROOT."styles/{$settings['default_style']}/style.php");
require_once(TO_ROOT."languages/{$settings['default_lng']}/base.lng.php");

$out->javascripts = $stylejs;
$out->stylesheets = $stylesheets;

$loggedin = false;

/* Check if Installer is locked correctly */

if(is_file('.lock') && ($lv = file('.lock')) && ($lv[0] != ''))
	$lockv = trim($lv[0]);
else
	$lockv = '1.0.0';
unset($lv);

if(version_compare($global['versions']['remote'], $lockv, ">") && is_dir('install'))
{
	$out->addError("{$lng['doinstall']} <a href=\"install/\">{$lng['goinstall']}");
	$out->content = $out->getMessages();
	$out->renderPage('Error', false);
}

/* End of locking-Control */

if(isset($_POST['login']))
{
	// Try to take login
	$res = $db->query('SELECT uid, `password`, `salt` FROM users WHERE name = ?', 's', $_POST['username']);
	if(($h = $db->fetch($res)) && $h['password'] == sha1($_POST['password'].$h['salt']))
	{
		$uinfo = $db->fetch($db->query('SELECT uid, name, status, dir, rootdir, viewchange, sortord, refchange, viewmode, groupmode, sourcemode, sortkey, refinterval, refmode, detailsstyle, shoutbox, hostnames, bitfields, language, design, sidebar FROM users WHERE uid = ?', 'i', $h['uid']));
		$_SESSION['uid']          = intval($uinfo['uid']);
		$_SESSION['username']     = $uinfo['name'];
		$_SESSION['status']       = intval($uinfo['status']);
		$_SESSION['dir']          = $uinfo['dir'];
		$_SESSION['rootdir']      = $uinfo['rootdir'];
		$_SESSION['viewchange']   = ord($uinfo['viewchange']);
		$_SESSION['refchange']    = ord($uinfo['refchange']);
		$_SESSION['sortord']      = $uinfo['sortord'];
		$_SESSION['viewmode']     = intval($uinfo['viewmode']);
		$_SESSION['groupmode']    = intval($uinfo['groupmode']);
		$_SESSION['sourcemode']   = intval($uinfo['sourcemode']);
		$_SESSION['sortkey']      = intval($uinfo['sortkey']);
		$_SESSION['refinterval']  = intval($uinfo['refinterval']);
		$_SESSION['refmode']      = intval($uinfo['refmode']);
		$_SESSION['detailsstyle'] = intval($uinfo['detailsstyle']);
		$_SESSION['shoutbox']     = intval($uinfo['shoutbox']);
		$_SESSION['hostnames']    = ord($uinfo['hostnames']);
		$_SESSION['bitfields']    = ord($uinfo['bitfields']);
		$_SESSION['lng']          = $uinfo['language'];
		$_SESSION['style']        = $uinfo['design'];

		$result = $db->query('SELECT boxid, area FROM boxpositions WHERE uid = ? ORDER BY position ASC', 'i', $_SESSION['uid']);
		if($db->num_rows($result))
		{
			while($h = $db->fetch($result))
      		$_SESSION['boxpositions'][intval($h['area'])][] = intval($h['boxid']);
		}
		else
		{
      	$_SESSION['boxpositions'] = array(
         	array(1,2,3,4,5,6), // SIDEBAR
				array(), // TOP
				array(), // BOTTOM
				array()  // RIGHT
			);
		}
		
		
		if(isset($_POST['stay']) && $_POST['stay'] == 'true')
		{
			$permanent = 1;  /* This variable has to been set to tell session-management it is a permanent session. */
			/* So now let's send another session cookie, that will never die */
			setcookie(session_name(), session_id(), time()+(60*60*24*365), $settings['cookie_path']);  // Expiring in current-Time + a year, think this will do it ;-)
		}
		else
		{
			$permanent = 0;
		}

		if(remainingJobs() > 0)
			$_SESSION['lastjcheck'] = time();
		else
			$_SESSION['lastjcheck'] = 0;

		$url = $_SERVER['REQUEST_URI'];
		if(SID != '')
		{
			if(strpos($url, '?'))
				$url .= '&'.SID;
			else
				$url .= '?'.SID;
		}

		// Insert User-Id into Sessions-Table
		session_write_close();
		$db->query('UPDATE sessions SET uid = ? WHERE sid = ?', 'is', $_SESSION['uid'], session_id());
		logger(LOGDEBUG, "User {$_SESSION['uid']} has logged in.", __FILE__, __LINE__);
		$out->redirect($url);
	}
	else
	{
		$out->addError($lng['badlogin']);
		logger(LOGSECURITY, "Bad login with Username \"{$_POST['username']}\"' from {$_SERVER['REMOTE_ADDR']}", __FILE__, __LINE__);
	}
}
else if(isset($_GET['logout']))
	$out->addSuccess($lng['suclogout']);
else if(isset($_REQUEST[session_name()]))
	$out->addNotify($lng['sexpired']);

/* Build URL  Not take all parameters, as it could occure you have
 * the parameter "logout" or maybe do unwanted action (like truncating the log) */
$url = $_SERVER['PHP_SELF'];

if(substr($url, -11) == 'control.php')
{
	if(isset($_GET['return']))
	{
		switch($_GET['return'])
		{
			case 'index':
				$url = "index.php";
				break;
			case 'torrent':
				if(isset($_GET['hash']))
					$url = "index.php#{$_GET['hash']}";
				else
					$url = 'index.php';
				break;
			case 'details':
				if(!isset($_GET['hash']))
				{
					if(isset($_GET['mod']))
						$url = "details.php?hash={$_GET['hash']}&mod={$_GET['mod']}";
					else
						$url = "details.php?hash={$_GET['hash']}";
				}
				else
					$url = 'index.php';
				break;
			default:
				$url = "index.php";
				break;
		}
		unset($_GET);
	}
	else
		$url = 'index.php';
}
else
{
	$params = '';
	if(isset($_GET['mod']))
		$params .= '&mod='.$_GET['mod'];
	if(isset($_GET['group']))
		$params .= '&group='.$_GET['group'];
	if(isset($_GET['hash']))
		$params .= '&hash='.$_GET['hash'];
	if(isset($_GET['file']))
		$params .= '&file='.$_GET['file'];
	if(isset($_GET['folder']))
		$params .= '&folder='.$_GET['folder'];
	if($params != '')
		$params[0] = '?';
	$url .= $params;
}

$out->content  = "<div id=\"loginbox\">";
$out->content .= $out->getMessages();
$out->content .= "<h1>{$lng['logintext']}</h1><form action=\"$url\" method=\"post\"><table>";
$out->content .= "<tr><td><label for=\"iusername\">{$lng['username']}</label></td><td><input type=\"text\" name=\"username\" id=\"iusername\"/></td></tr>";
$out->content .= "<tr><td><label for=\"ipassword\">{$lng['password']}</label></td><td><input type=\"password\" name=\"password\" id=\"ipassword\" /></td></tr>";
if($settings['session_use_cookies'])
	$out->content .= "<tr><td>&nbsp;</td><td><input type=\"checkbox\" name=\"stay\" value=\"true\" id=\"istay\" />&nbsp;&nbsp;<label class=\"hint\" for=\"istay\">{$lng['stayinhint']}</label></td></tr>";
$out->content .= "<tr><td><input type=\"hidden\" name=\"login\" value=\"true\" /></td><td><input type=\"submit\" value=\"{$lng['login']}\" /></td></tr>";
$out->content .= "</table></form></div>";
$out->renderPage($lng['logintext'], false);

?>
