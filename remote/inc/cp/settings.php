<?php

if(!defined('IN_CP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

if(!(@include(TO_ROOT."languages/{$_SESSION['lng']}/settings.lng.php")))
{
	require_once(TO_ROOT."languages/{$settings['default_lng']}/settings.lng.php");
	logger(LOGERROR, "Could not load {$_SESSION['lng']}/settings.lng.php", __FILE__, __LINE__);
}
require_once(TO_ROOT."inc/functions/settings.fun.php");

$groups = array('general', 'paths', 'binaries', 'session', 'expert');

if(isset($_GET['group']) && isset($groups[$_GET['group']]))
	$group = $_GET['group'];
else
	$group = 0;

$settingsmenu = '<ul class="tabs">';
foreach($groups as $num => $g)
	$settingsmenu .= '<li'.($num == $group ? ' class="viewselon"' : '')."><a href=\"controlpanel.php?mod=$mod&amp;group=$num$sid\" title=\"{$lng['goto']} {$lng[$g]}\">{$lng[$g]}</a></li>";
$settingsmenu .= '</ul>';

if(isset($_POST['which']) || isset($_POST['info']) || isset($_POST['save']))
	$evaluate = true;
else
	$evaluate = false;


if(isset($_POST['which']))
{
	$result = $db->query('SELECT inputtype FROM settings WHERE skey = ?', 's', $_POST['which']);
	if(($h = $db->fetch($result)) && ($h['inputtype'] == 'bin'))
	{
		list(,$bin) = explode('_', $_POST['which'], 2);
		$which = exec('which '.escapeshellarg($bin));
		if($which == '')
		{
			$error = $lng['nobinary'];
			unset($which);
		}
	}
	else
		logger(LOGSECURITY, "User {$_SESSION['uid']} tried to inject \"{$_POST['which']}\"", __FILE__, __LINE__);
}

$result = $db->query('SELECT skey, defaultname, inputtype, value, onsave, sortid FROM settings WHERE gid = ? ORDER BY sortid ASC', 'i', $group);
$settingstable = "<form action=\"controlpanel.php?mod=$mod&amp;group=$group$sid\" method=\"post\"><table id=\"settingstable\">";
for($x = 0; $h = $db->fetch($result); $x++)
{
	$skey  = $h['skey'];
	if(substr($h['inputtype'], 0, 3) == 'php')
		list(,$inputtype,) = explode('|', $h['inputtype'], 3);
	else
		$inputtype = $h['inputtype'];

	if($evaluate && (isset($_POST[$skey]) || $inputtype == 'yn'))
	{
		if($h['onsave'] != '')
			eval($h['onsave']);
		else
		{
			switch($inputtype)
			{
				case 'char':
				case 'txt':
				case 'dir':
				case 'bin':
					$value = $_POST[$skey];
					break;
				case 'int':
					$value = intval($_POST[$skey]);
					break;
				case 'yn':
					if(isset($_POST[$skey]) && ($_POST[$skey] == 'yes'))
						$value = 1;
					else
						$value = 0;
					break;
				case 'float':
					$value = floatval($_POST[$skey]);
					break;
				default:
					logger(LOGDEBUG, "Invalid Settings-Datatype: {$h['inputtype']} on skey = $skey", __FILE__, __LINE__);
					break;
			}
		}
	}
	else
	{
		switch($inputtype)
		{
			case 'char':
			case 'txt':
			case 'dir':
			case 'bin':
				$value = $h['value'];
				break;
			case 'int':
			case 'yn':
				$value = intval($h['value']);
				break;
			case 'float':
				$value = floatval($h['value']);
				break;
			default:
				logger(LOGDEBUG, "Invalid Settings-Datatype: {$h['inputtype']} on skey = $skey", __FILE__, __LINE__);
				break;
		}
	}
	if(isset($_POST['save']))
	{
		$save = true;
		switch($inputtype)
		{
			case 'dir':
				$value = clean_dir($value);
			case 'char':
			case 'txt':
			case 'bin':
				$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'ss', $value, $skey);
				break;
			case 'int':
			case 'yn':
				$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'is', $value, $skey);
				break;
			case 'float':
				$db->query('UPDATE settings SET value = ? WHERE skey = ?', 'ds', $value, $skey);
				break;
			default:
				logger(LOGDEBUG, "Invalid Settings-Datatype: {$h['inputtype']} on skey = $skey", __FILE__, __LINE__);
				break;
		}
	}
	if(substr($h['inputtype'], 0, 3) == 'php')
	{
		list(,,$phpcode) = explode('|', $h['inputtype'], 3);  /* inputtype is in Format 'php|$DATATYPE|$PHPCODE' */
		eval($phpcode);
	   $for = '';
	}
	else
	{
		$for = " for=\"$skey\"";
		switch($inputtype)
		{
			case 'char':
				$input = "<input type=\"text\" class=\"text\" name=\"$skey\" id=\"$skey\" value=\"$value\" size=\"1\" maxlength=\"1\" />";
				break;
			case 'txt':
				$input = "<input type=\"text\" class=\"text\" name=\"$skey\" id=\"$skey\" value=\"$value\" />";
				break;
			case 'dir':
				$input = "<input type=\"text\" class=\"text\" name=\"$skey\" id=\"$skey\" value=\"$value\" />";
				break;
			case 'bin':
				if(isset($which) && $_POST['which'] == $skey)
					$value = $which;
				$input = "<input type=\"text\" class=\"text\" name=\"$skey\" id=\"$skey\" value=\"$value\" /><button name=\"which\" value=\"$skey\" onclick=\"return ajax_exec('$sid', 'which', '$skey', this, '{$lng['loading']}');\">{$lng['autodetect']}</button>";
				break;
			case 'int':
				$input = "<input type=\"text\" class=\"num\" name=\"$skey\" id=\"$skey\" value=\"$value\" maxlength=\"8\" />";
				break;
			case 'yn':
				$input = "<input type=\"checkbox\" class=\"checkbox\" name=\"$skey\" id=\"$skey\" value=\"yes\" ".(intval($value) == 1 ? 'checked="checked" ' : '').'/>';
				break;
			case 'float':
				$input = "<input type=\"text\" class=\"num\" name=\"$skey\" id=\"$skey\" value=\"$value\" maxlength=\"16\" />";
				break;
			default:
				logger(LOGDEBUG, "Invalid Settings-Datatype: {$h['inputtype']} on skey = $skey", __FILE__, __LINE__);
				continue;
				break;
		}
	}
	$row = $x%2;
	$settingstable .= "<tr class=\"row$row\"><td><button name=\"info\" value=\"$skey\" onclick=\"return ajax_exec('$sid', 'info', '$skey', null, '');\"><img src=\"{$imagedir}help.png\" alt=\"{$lng['help']}\" /></button></td>";
	if(isset($lng[$skey]))
		$name = $lng[$skey];
	else
		$name = $h['defaultname'];
	$settingstable .= "<td><label$for>$name</label></td><td>$input</td></tr>";
}
$settingstable .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type=\"submit\" name=\"save\" value=\"{$lng['save']}\" /></td></tr></table></form>";

if(isset($save) && $save)
{
	logger(LOGSETTINGS, "Settings updated by {$_SESSION['uid']}", __FILE__, __LINE__);
	$new = process_settings();
	if(count(array_diff_assoc($settings, $new)))
	{
		unset($settings);
		$settings = $new;
		unset($new);
		cache_put('settings', $settings);
	}
}

$cpout = '';

if(isset($error))
	$cpout .= "<div class=\"error\">$error</div>";

if(isset($save) && !isset($error))
	$cpout .= "<div class=\"success\">{$lng['saved']}</div>";

if(isset($_POST['info']) && isset($lng['help_'.$_POST['info']]))
	$cpout .= "<div class=\"notify\">{$lng['help_'.$_POST['info']]}</div>";
else
	$cpout .= "<div id=\"notifycontainer\"></div>";

$out->javascripts[] = 'js/settings.js';
$cpout .= "$settingsmenu<div class=\"tabsbody\" id=\"settingscontent\">$settingstable</div>";

?>
