<?php

if(!defined('IN_CP') || $_SESSION['status'] < 2)
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

function checkName($edit = 0)
{
	global $db, $lng;

	if(!isset($_POST['name']) || trim($_POST['name']) == '')
		return $lng['invalidname'];

	$result = $db->query('SELECT COUNT(*) AS anz FROM users WHERE name = ? AND uid != ?', 'si', $_POST['name'], $edit);
	$h = $db->fetch($result);
	if(intval($h['anz']) > 0)
		return $lng['usernameexi'];

	return '';
}

function checkPermissions($uid)
{
	global $db;


	if($_SESSION['status'] == SUADMIN)
		return true;

	$result = $db->query('SELECT status FROM users WHERE uid = ?', 'i', $uid);
	if(($h = $db->fetch($result)) && (intval($h['status']) > 0) && (intval($h['status'])) < $_SESSION['status'])
		return true;
	else
		return false;
}

function makeRandomStr($len, $simple)
{
	$ressource = 'abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	if(!$simple)
		$ressource .= '_!"§$()[]+-.';

	$rlen = strlen($ressource) - 1;
	$str = '';
	for($x = 0; $x < $len; $x++)
		$str .= $ressource[mt_rand(0, $rlen)];

	return $str;
}

function checkDir($edit = 0)
{
	global $lng, $db;


	if(($_SESSION['status'] == SUADMIN) || ($edit && (intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM users WHERE uid = ? AND rootdir = ?', 'is', $edit, $_POST['dir']))) > 0)))
	{
		if(is_dir($_POST['dir']))
			return '';
		else
			return $lng['invaliddir'];
	}
	else
	{
		if(is_valid_dir($_POST['dir']))
			return '';
		else
			return $lng['invaliddir'];
	}
}

function checkValid($edit = 0)
{
	$error = checkName($edit);
	if($error != '')
		return $error;
	if($edit)
	$error = checkDir($edit);
	return $error;
}


if(!IN_CP || $_SESSION['status'] < 2)
	$out->redirect("controlpanel.php$qsid");

if(isset($_GET['edit']) && is_numeric($_GET['edit']))
	$edit = $_GET['edit'];
else
	$edit = 0;

if(isset($_GET['killsession']))
{
	$result = $db->query('DELETE FROM sessions WHERE sid = ?', 's', $_GET['killsession']);
	if($db->affected_rows())
		$success = $lng['sesskilled'];
	else
		$error   = $lng['notdeleted'];
}
else if(isset($_GET['kill']) && is_numeric($_GET['kill']))
{
	$result = $db->query("SELECT name FROM users WHERE uid = {$_GET['kill']}");
	if($h = $db->fetch($result))
	{
		if(checkPermissions($_GET['kill']))
			$notify = makeSecQuery(lng('qkilluser', $h['name']), $mod, array('delete' => $_GET['kill']));
		else
			$error = $lng['npermission'];
	}
	else
		$error = $lng['nosuchuser'];
}
else if(isset($_GET['pass']) && is_numeric($_GET['pass']))
{
	$result = $db->query("SELECT name FROM users WHERE uid = {$_GET['pass']}");
	if($h = $db->fetch($result))
		$notify  = makeSecQuery(lng('qnewpass', $h['name']), $mod, array('pass' => $_GET['pass']));
	else
		$error = $lng['nosuchuser'];
}
else if(isset($_POST['confirm']))
{
	if(isset($_POST['delete']) && is_numeric($_POST['delete']))
	{
		if(checkPermissions($_POST['delete']))
		{
			$name = getUsername($_POST['delete']);
			$result = $db->query("DELETE FROM users WHERE uid = {$_POST['delete']}");
			if($db->affected_rows())
			{
				$success = $lng['userdeleted'];
				// Now, if real multiuser is activated, delete all his torrents

				if($settings['real_multiuser'])
				{
					$result = $db->query("SELECT hash FROM torrents WHERE uid = {$_POST['delete']}");
					while($h = $db->fetch($result))
						$rpc->request('d.erase', array($hash));

					// Kick the torrents out of the table
					$db->query("DELETE FROM torrents WHERE uid = {$_POST['delete']}");
				}
				$db->query("DELETE FROM boxpositions WHERE uid = {$_POST['delete']}");
				logger(LOGUSERS, "User {$_POST['delete']} ($name) was deleted by {$_SESSION['uid']} ({$_SESSION['username']})", __FILE__, __LINE__);
			}
			else
				$error   = $lng['notdeleted'];
		}
		else
			$error = $lng['npermission'];
	}
	else if(isset($_POST['pass']) && is_numeric($_POST['pass']))
	{
		if(checkPermissions($_POST['pass']))
		{
			$password = makeRandomStr(8,  true);
			$salt     = makeRandomStr(10, false);
			$result = $db->query('UPDATE users SET password = ?, salt = ? WHERE uid = ?', 'ssd',
									sha1($password.$salt),
									$salt,
									$_POST['pass']);

			if($db->affected_rows())
			{
				$name  = getUsername($_POST['pass']);
				logger(LOGUSERS, "Password of {$_POST['pass']} ($name) reseted by {$_SESSION['uid']} ({$_SESSION['username']})", __FILE__, __LINE__);

				$success = lng('newpassword', $password);
			}
		}
		else
			$error = $lng['npermission'];
	}
}
else if(isset($_POST['usersave']))
{
	$v_name  = $_POST['name'];
	$v_dir   = $_POST['dir'];
	$v_admin = (isset($_POST['admin']) && $_POST['admin'] == 'yes');
	if($edit)
	{
		$result = $db->query("SELECT uid, name, status, rootdir FROM users WHERE uid = $edit");
		if($h = $db->fetch($result))
		{
			$error = '';
			if(($_SESSION['status'] < SUADMIN) && (intval($h['status']) >= $_SESSION['status']))
			{
				$error = $lng['npermission'];
				logger(LOGSECURITY, "User {$_SESSION['uid']} ({$_SESSION['username']}) is trieing to edit an SuperAdmins Profile", __FILE__, __LINE__);
			}
			if($error == '')
				$error = checkValid($edit);
			if($error == '')
			{
				if($_SESSION['status'] == SUADMIN)
					$status = 3;
				else if($v_admin)
					$status = 2;
				else
					$status = 1;
				$oname = getUsername($edit);
				$result = $db->query('UPDATE users SET name = ?, rootdir = ?, status = ? WHERE uid = ?', 'ssii',
											$_POST['name'],
											$_POST['dir'],
											$status,
											$edit);
				unset($error);
				$v_name  = '';
				$v_dir   = $settings['default_dir'];
				$v_admin = false;
				logger(LOGUSERS, "User $edit edited by {$_SESSION['uid']} ($oname/{$_POST['name']})", __FILE__, __LINE__);
			}
		}
		else
			$error = $lng['nosuchuser'];
	}
	else
	{
		$error = checkValid($edit);
		if($error == '')
		{
			if($v_admin)
				$status = 2;
			else
				$status = 1;

			$password = makeRandomStr(8,  true);
			$salt     = makeRandomStr(10, false);
			$result = $db->query('INSERT INTO users (name, dir, rootdir, status, password, salt, language, design) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
										'sssissss',
										$_POST['name'],
										$_POST['dir'],
										$_POST['dir'],
										$status,
										sha1($password.$salt),
										$salt,
										$settings['default_lng'],
										$settings['default_style']);
			unset($error);
			$v_name  = '';
			$v_dir   = $settings['default_dir'];
			$v_admin = false;
			logger(LOGUSERS, 'User '.$db->insert_id()." created by {$_SESSION['uid']}", __FILE__, __LINE__);

			$success = lng('useradded', $_POST['name'], $password);
		}
	}
}

if($edit)
{
	$result = $db->query("SELECT uid, name, status, rootdir FROM users WHERE uid = $edit");
	if($h = $db->fetch($result))
	{
		$v_name  = htmlspecialchars($h['name'],     ENT_QUOTES);
		$v_dir   = htmlspecialchars($h['rootdir'],  ENT_QUOTES);
		$v_admin = ($h['status'] > 1);
	}
	else
		$error = $lng['nosuchuser'];
}
else if(!isset($_POST['usersave']))
{
	$v_name  = '';
	$v_dir   = $settings['default_dir'];
	$v_admin = false;
}

$cpout = '';
if(isset($error))
	$cpout .= "<div class=\"error\">$error</div>";
if(isset($success))
	$cpout .= "<div class=\"success\">$success</div>";
if(isset($notify))
	$cpout .= "<div class=\"notify\">$notify</div>";
if($edit)
{
	$cpout .= "<fieldset class=\"box\" id=\"adduserbox\"><legend>{$lng['edituser']}</legend>";
	$cpout  .= "<form action=\"controlpanel.php?mod=$mod&amp;edit=$edit$sid\" method=\"post\"><table>";
}
else
{
	$cpout .= "<fieldset class=\"box\" id=\"adduserbox\"><legend>{$lng['newuser']}</legend>";
	$cpout  .= "<form action=\"controlpanel.php?mod=$mod$sid\" method=\"post\"><table>";
}
$cpout  .= "<tr><td><label for=\"username\">{$lng['username']}</label></td><td><input type=\"text\" class=\"text\" value=\"$v_name\" name=\"name\" id=\"username\" /></td></tr>";
$cpout  .= "<tr><td><label for=\"userdir\">{$lng['dir']}</label></td><td><input type=\"text\" class=\"text\" value=\"$v_dir\" name=\"dir\" id=\"userdir\" /></tr>";
if($v_admin)
	$checked = 'checked="checked" ';
else
	$checked = '';
$cpout  .= "<tr><td><label for=\"useradmin\">{$lng['admin']}</label></td><td><input type=\"checkbox\" name=\"admin\" value=\"yes\" id=\"useradmin\" $checked/></td></tr>";
if($edit)
	$cpout  .= "<tr><td>&nbsp;</td><td><input type=\"submit\" class=\"submit\" name=\"usersave\" value=\"{$lng['save']}\" /></td></tr>";
else
	$cpout  .= "<tr><td>&nbsp;</td><td><input type=\"submit\" class=\"submit\" name=\"usersave\" value=\"{$lng['add']}\" /></td></tr>";
$cpout  .= "</table></form></fieldset>";

$cpout .= "<table id=\"usertable\"><thead><tr><td class=\"tableheadline\" colspan=\"4\"><h2>{$lng['users']}</h2></td></tr>";
$cpout .= "<tr><td>{$lng['name']}</td><td>{$lng['homedir']}</td><td>{$lng['admin']}</td><td>&nbsp;</td></tr></thead>";

$result = $db->query("SELECT uid, name, status, rootdir FROM users ORDER BY name");
if($db->num_rows($result))
{
	while($h = $db->fetch($result))
	{
		$name = $h['name'];
		$dir  = $h['rootdir'];
		if($h['status'] >= 2)
			$admin = $lng['yes'];
		else
			$admin = $lng['no'];
		$links  = "<a href=\"controlpanel.php?mod=$mod&amp;edit={$h['uid']}$sid\" title=\"{$lng['edituser']}\"><img src=\"{$imagedir}edit.png\" alt=\"Edit\"   /></a>&nbsp;";
		$links .= "<a href=\"controlpanel.php?mod=$mod&amp;kill={$h['uid']}$sid\" title=\"{$lng['killuser']}\"><img src=\"{$imagedir}delete.png\"  alt=\"Delete\" /></a>&nbsp;";
		$links .= "<a href=\"controlpanel.php?mod=$mod&amp;pass={$h['uid']}$sid\" title=\"{$lng['passuser']}\"><img src=\"{$imagedir}newpass.png\"  alt=\"Password\" /></a>";
		$cpout .= "<tr><td>$name</td><td>$dir</td><td>$admin</td><td>$links</td></tr>";
	}
}
$cpout .= "</table></fieldset>";




$cpout .= "<table id=\"sessiontable\"><thead><tr><td class=\"tableheadline\" colspan=\"4\"><h2>{$lng['sessions']}</h2></td></tr>";
$cpout .= "<tr><td>{$lng['user']}</td><td>{$lng['lastactive']}</td><td>{$lng['expires']}</td><td>&nbsp;</td></tr></thead>";

$result = $db->query('SELECT s.sid, u.name, s.time, s.permanent, s.data FROM sessions s, users u WHERE s.uid != 0 AND s.uid = u.uid AND (s.permanent = 1 OR s.time > ?)', 'i', time() - $settings['session_lifetime']);
if($db->num_rows($result))
{
	while($h = $db->fetch($result))
	{
		$time = date("d.m.Y H:i:s", $h['time']);
		if(!$h['permanent'])
			$expires = date("d.m.Y H:i:s", $h['time'] + $settings['session_lifetime']);
		else
			$expires = $lng['never'];
		$name = $h['name'];
		$links  = "<a href=\"controlpanel.php?mod=$mod&amp;killsession={$h['sid']}$sid\" title=\"{$lng['killsession']}\"><img src=\"{$imagedir}delete.png\"  alt=\"Kill\" /></a>";
		$cpout .= "<tr><td>$name</td><td>$time</td><td>$expires</td><td>$links</td></tr>";
	}
}


$cpout .= "</table></fieldset>"

?>
