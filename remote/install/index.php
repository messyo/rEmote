<?php
ini_set('error_reporting',          E_ALL);
ini_set('session.use_cookies',      false);
ini_set('session.use_only_cookies', 0);

session_name('remote-install-sid');
session_start();
if(SID != '')
{
	$sid  = '&amp;'.SID;
	$qsid = '?'.SID;
}
else
{
	$sid  = '';
	$qsid = '';
}

define('IN_INSTALL', true);
define('REMOTE_BUILD', 2);
define('REMOTE_VERSION', '2.0.0-B1');
define('MIN_RT', '0.8.4');
define('TO_ROOT', '../');


$steps = array(
	'language',
	'requirements',
	'rpc',
	'database',
	'settings',
	'user',
	'paths',
	'binaries',
	'completion'
);

if(is_file(TO_ROOT.'.lock'))
{
	$file = file(TO_ROOT.'.lock');
	if(isset($file[0]))
	{
		$l = trim($file[0]);
		if(version_compare(REMOTE_VERSION.'-'.REMOTE_BUILD, $l) < 1)
			$locked = true;
	}
}

if(isset($locked))
	$out = "<div class=\"error\">Installer locked, if you want to install the same Version again, please remove file \".lock\"</div>";
else if(!@include(TO_ROOT.'config.php'))
	$out = "<div class=\"error\">No config.php found. Please create one out of config.sample.php</div>";
else
{
	require_once('./functions.php');
	if(isset($_SESSION['database']) && $_SESSION['database'])
	{
		require_once(TO_ROOT.'inc/sql/database.php');
		require_once(TO_ROOT.'inc/sql/'.$sql['type'].'.php');
	}
	require_once(TO_ROOT.'inc/functions/base.fun.php');

	if(isset($_SESSION['language']))
		require(TO_ROOT.'languages/'.$_SESSION['language'].'/install.lng.php');
	else // if(!(@include(TO_ROOT.'languages/'.$_SESSION['language'].'/install.lng.php')))
	{
		$lng['title']     = 'Install rEmote';
		$lng['chooselng'] = 'Please choose your language';
		$lng['save']      = 'Save';
	}
	if(isset($_SESSION['step']))
	{
		if(isset($_SESSION['step'.$_SESSION['step']]['success'])
			&& ($_SESSION['step'.$_SESSION['step']]['success'] === true)
			&& isset($_GET['gonext'])
			&& $_GET['gonext'] == $_SESSION['step']
			&& isset($steps[$_SESSION['step'] + 1]))
			$_SESSION['step'] += 1;
	}
	else
		$_SESSION['step'] = 0;

	$step = $_SESSION['step'];

	$success = true;

	require_once($steps[$step].'.php');
}

$out .= '<div id="buttons"><div id="next" class="bottom">';
if(isset($retry_possible) && $retry_possible)
{
	$time = time();
	$out .= "<a href=\"index.php?re=$time$sid\" title=\"retry\">{$lng['retry']}</a>&nbsp;";
}
if(isset($success) && $success && isset($steps[$step + 1]))
{
	$out .= "<a href=\"index.php?gonext=$step$sid\" title=\"continue\">{$lng['next']}</a>";
	$_SESSION['step'.$step]['success'] = true;
}
$out .= '</div><div id="custombutton" class="bottom">';
if(isset($custombutton))
	$out .= "<a href=\"index.php$qsid&amp;{$custombutton['args']}\" title=\"continue\">{$custombutton['label']}</a>";
else
	$out .= '&nbsp;';
$out .= '</div></div>';
$head  = "<div id=\"header\">r<span style=\"color: orange; font-style: italic;\">E</span>mote<br />";
$head .= "<span style=\"font-style: italic; font-size: 8px; color: #aaa;\">Your rtorrent WebGUI</span></div>";

if(!isset($lng))
	$lng['title'] = 'rEmote';

echo "<html><head><title>{$lng['title']}</title></head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /><link href=\"style.css\" rel=\"stylesheet\" type=\"text/css\" /><body><div id=\"main\">$head<div id=\"content\"";
echo $out;
echo '</div></div></body></html>'

?>
