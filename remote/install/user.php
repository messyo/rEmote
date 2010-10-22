<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$out = "<h1>{$lng['firstuser']}</h1>";

$db = new Database($sql);

$v_name = $v_pass = $v_dir = '';

if(isset($_POST['save']))
{
	$error = '';
	if(!postNotEmpty('name'))
		$error .= "<div class=\"error\">{$lng['invname']}</div>";
	else
		$v_name = trim($_POST['name']);
	if(!postNotEmpty('password'))
		$error .= "<div class=\"error\">{$lng['invpass']}</div>";
	else
		$v_pass = trim($_POST['password']);
	if(!postNotEmpty('dir'))
		$error .= "<div class=\"error\">{$lng['invdir']}</div>";
	else
	{
		$v_dir = clean_dir(trim($_POST['dir']));
		if(!is_dir($v_dir))
			$error .= "<div class=\"error\">{$lng['nodir']}</div>";
	}

	if($error != '')
		$out .= $error;
	else
	{
		$salt = makeRandomStr(10, false);
		$count = intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM users'), 'c'));
		if($count == 0)
			$db->query('INSERT INTO users (name, password, salt, status, dir, rootdir, viewchange, sortord, refchange, viewmode, groupmode, sourcemode, sortkey, refinterval, refmode, detailsstyle, language, design, sidebar, detailsmode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				'sssissisiiiiiiiissi',
				$v_name,
				sha1($v_pass.$salt),
				$salt,
				3,
				$v_dir,
				$v_dir,
				0,
				'ASC',
				0,
				0,
				0,
				0,
				0,
				60,
				0,
				2,
				$_SESSION['language'],
				$db->one_result($db->query('SELECT value FROM settings WHERE skey = ?', 's', 'default_style'), 'value'),
				0,
				0);
	}
}

$count = intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM users'), 'c'));
if($count == 0)
{
	$success = false;
	$out .= "<form action=\"index.php$qsid\" method=\"post\"><table>";
	$out .= "<tr><td><label for=\"username\">{$lng['username']}:</label></td><td><input type=\"text\" class=\"text\" value=\"$v_name\" name=\"name\" id=\"username\" /></td></tr>";
	$out .= "<tr><td><label for=\"password\">{$lng['password']}:</label></td><td><input type=\"text\" class=\"text\" value=\"$v_pass\" name=\"password\" id=\"password\" /></td></tr>";
	$out .= "<tr><td><label for=\"userdir\">{$lng['dir']}:</label></td><td><input type=\"text\" class=\"text\" value=\"$v_dir\" name=\"dir\" id=\"userdir\" /></td></tr>";
	$out .= "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"{$lng['save']}\" name=\"save\" /></td></tr>";
	$out .= '</table></form>';
}
else
{
	// UPGRADE FOR ALPHA-USERS
	$db->query('UPDATE users SET rootdir = dir');

	$out .= "<div class=\"success\">{$lng['userfound']}</div>";
}


?>
