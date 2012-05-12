<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$db = new Database($sql);

define('EXISTS',    1);
define('READABLE',  2);
define('WRITEABLE', 4);

define('BAD',    0);
define('PASSED', 1);
define('OK'    , 2);

$oks = array(
	BAD    => 'images/ledred.png',
	PASSED => 'images/ledyellow.png',
	OK     => 'images/ledgreen.png',
);

$dirs = array(
		'tmpdir'      => array(7, 7),
		'default_dir' => array(0, 3)
);

$pathes = array();

foreach($dirs as $key => $val)
{
	$dir = $db->one_result($db->query('SELECT value FROM settings WHERE skey = ?', 's', $key), 'value');
	$pathes[$key] = $dir;
}

$dirs['user_dir'] = array(0, 7);
$pathes['user_dir'] = $db->one_result($db->query('SELECT dir FROM users LIMIT 1'), 'dir');

$out = "<h1>{$lng['dirs']}</h1>";

if(isset($_GET['change']))
{
	$time = time();
	$out .= "<form action=\"index.php$qsid&amp;save=$time\" method=\"post\">";
	$out .= "<table id=\"pathes\"><tr class=\"head\"><td>{$lng['function']}</td><td>{$lng['path']}</td></tr>";
	foreach($pathes as $k => $p)
		$out .= "<tr><td>{$lng[$k]}</td><td><input type=\"text\" name=\"$k\" value=\"$p\" /></td></tr>";
	$out .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"save\" value=\"{$lng['save']}\" /></td></tr></table></form>";
	$success = false;
}
else
{
	if(isset($_POST['save']))
	{
		$pathes['tmpdir']      = clean_dir($_POST['tmpdir']);
		$pathes['default_dir'] = clean_dir($_POST['default_dir']);
		$pathes['user_dir']    = clean_dir($_POST['user_dir']);
		$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'ss', $pathes['tmpdir'],      'tmpdir');
		$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'ss', $pathes['default_dir'], 'default_dir');
		$db->query('UPDATE users SET dir = ? WHERE uid = ?',       'si', $pathes['user_dir'],    1);
	}
	$out .= "<table><tr class=\"head\"><td>{$lng['function']}</td><td>{$lng['path']}</td><td>{$lng['privs']}</td><td>{$lng['pass']}</td></tr>";

	foreach($dirs as $key => $val)
	{
		$dir = $pathes[$key];

		$exists    = false;
		$readable  = false;
		$writeable = false;

		if(is_dir($dir))
			$exists = true;
		if($exists)
		{
			if(is_readable($dir))
				$readable = true;
			if(is_writeable($dir))
				$writeable = true;
		}

		$ok = OK;
		if((($val[1] & EXISTS)    && !$exists   ) ||
			(($val[1] & READABLE)  && !$readable ) ||
			(($val[1] & WRITEABLE) && !$writeable))
			$ok = PASSED;
		if((($val[0] & EXISTS)    && !$exists   ) ||
			(($val[0] & READABLE)  && !$readable ) ||
			(($val[0] & WRITEABLE) && !$writeable))
		{
			$ok = BAD;
			$success = false;
		}

		if($exists)
			$pass = $lng['exists'];
		else if($val[0] & EXISTS)
			$pass = "<span class=\"red\">{$lng['not']} {$lng['exists']}</span>";
		else
			$pass = "{$lng['not']} {$lng['exists']}";
		$passes = $pass;

		if($readable)
			$pass = $lng['readable'];
		else if($val[0] & READABLE)
			$pass = "<span class=\"red\">{$lng['not']} {$lng['readable']}</span>";
		else
			$pass = "{$lng['not']} {$lng['readable']}";
		$passes .= '<br />'.$pass;

		if($writeable)
			$pass = $lng['writeable'];
		else if($val[0] & WRITEABLE)
			$pass = "<span class=\"red\">{$lng['not']} {$lng['writeable']}</span>";
		else
			$pass = "{$lng['not']} {$lng['writeable']}";
		$passes .= '<br />'.$pass;

		$out .= "<tr><td>{$lng[$key]}</td><td><strong>$dir</strong></td><td>$passes</td><td style=\"text-align: center;\"><img src=\"{$oks[$ok]}\" /></td></tr>";
	}
	$out .= '</table><table id="legend">';
	$out .= "<tr><td><img src=\"{$oks[OK]}\" /></td><td>{$lng['pgood']}</td></tr>";
	$out .= "<tr><td><img src=\"{$oks[PASSED]}\" /></td><td>{$lng['ppassed']}</td></tr>";
	$out .= "<tr><td><img src=\"{$oks[BAD]}\" /></td><td>{$lng['pbad']}</td></tr>";
	$out .= '</table>';

	$retry_possible = true;
	$custombutton['args']  = 'change='.time();
	$custombutton['label'] = $lng['changedir'];
}

?>
