<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");


$db = new Database($sql);

define('EXISTS',      1);
define('EXECUTABLE',  2);

define('BAD',    0);
define('PASSED', 1);
define('OK'    , 2);

$oks = array(
	BAD    => 'images/ledred.png',
	PASSED => 'images/ledyellow.png',
	OK     => 'images/ledgreen.png',
);

$musts = array('binary_php', 'binary_wget', 'binary_nohup');

require_once(TO_ROOT.'languages/'.$_SESSION['language'].'/settings.lng.php');

$out = "<h1>{$lng['binaries']}</h1>";

if(isset($_GET['change']))
{
	$result = $db->query('SELECT skey, value FROM settings WHERE inputtype = ?', 's', 'bin');
	$time = time();
	$out .= "<form action=\"index.php$qsid&amp;save=$time\" method=\"post\">";
	$out .= "<table id=\"binaries\"><tr class=\"head\"><td>{$lng['binary']}</td><td>{$lng['path']}</td></tr>";
	while($h = $db->fetch($result))
	{
		$value  = $h['value'];
		$key    = $h['skey'];
		list(, $binary) = explode('_', $h['skey'], 2);
		if(!isset($_SESSION['manbin']))
		{
			$which = exec('which '.escapeshellarg($binary));
			if($which == '')
				$which = $lng['bnotfound'];
			$value = $which;
		}
		$out .= "<tr><td>{$lng[$key]}</td><td><input type=\"text\" name=\"$key\" value=\"$value\" /></td></tr>";

	}
	$out .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"save\" value=\"{$lng['save']}\" /></td></tr></table></form>";
	$success = false;
}
else
{
	if(isset($_POST['save']))
	{
		$_SESSION['manbin'] = true;
		$result = $db->query('SELECT skey FROM settings WHERE inputtype = ?', 's', 'bin');
		while($h = $db->fetch($result))
		{
			$key = $h['skey'];
			if(isset($_POST[$key]) && (trim($_POST[$key]) != ''))
				$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'ss', trim($_POST[$key]), $key);
		}
	}
	$out .= "<table><tr class=\"head\"><td>{$lng['binary']}</td><td>{$lng['path']}</td><td>{$lng['privs']}</td><td>{$lng['pass']}</td></tr>";

	$result = $db->query('SELECT skey, value FROM settings WHERE inputtype = ?', 's', 'bin');
	while($h = $db->fetch($result))
	{
		$value  = $h['value'];
		$key    = $h['skey'];

		if($value[0] != '/')
			$cvalue = TO_ROOT.$value;
		else
			$cvalue = $value;

		if(in_array($key, $musts))
			$ok = BAD;
		else
			$ok = PASSED;
		$exists     = false;
		$executable = false;
		if($value != '' && is_file($cvalue))
			$exists = true;
		if($exists)
		{
			if(is_executable($cvalue))
			{
				$executable = true;
				$ok = OK;
			}
		}


		if($exists)
			$pass = $lng['exists'];
		else
			$pass = "<span class=\"red\">{$lng['not']} {$lng['exists']}</span>";
		$passes = $pass;

		if($executable)
			$pass = $lng['executable'];
		else
			$pass = "<span class=\"red\">{$lng['not']} {$lng['executable']}</span>";
		$passes .= '<br />'.$pass;

		if($ok == BAD)
			$success = false;

		$out .= "<tr><td>{$lng[$key]}</td><td><strong>$value</strong></td><td>$passes</td><td style=\"text-align: center;\"><img src=\"{$oks[$ok]}\" /></td></tr>";
	}
	$out .= '</table><table id="legend">';
	$out .= "<tr><td><img src=\"{$oks[OK]}\" /></td><td>{$lng['bgood']}</td></tr>";
	$out .= "<tr><td><img src=\"{$oks[PASSED]}\" /></td><td>{$lng['bpassed']}</td></tr>";
	$out .= "<tr><td><img src=\"{$oks[BAD]}\" /></td><td>{$lng['bbad']}</td></tr>";
	$out .= '</table>';

	$retry_possible = true;
	$custombutton['args']  = 'change='.time();
	$custombutton['label'] = $lng['changebin'];
}

?>
