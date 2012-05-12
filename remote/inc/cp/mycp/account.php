<?php

if(!defined('IN_MYCP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");


function loadOptions()
{
	global $db;

	$uinfo = $db->fetch($db->query('SELECT viewchange, sortord, refchange, viewmode, groupmode, sourcemode, sortkey, refinterval, refmode, detailsstyle, hostnames, bitfields, language, design, detailsmode FROM users WHERE uid = ?', 'i', $_SESSION['uid']));
	$opt['viewchange']   = intval($uinfo['viewchange']);
	$opt['refchange']    = intval($uinfo['refchange']);
	$opt['sortord']      = $uinfo['sortord'];
	$opt['viewmode']     = intval($uinfo['viewmode']);
	$opt['groupmode']    = intval($uinfo['groupmode']);
	$opt['sourcemode']   = intval($uinfo['sourcemode']);
	$opt['sortkey']      = intval($uinfo['sortkey']);
	$opt['refinterval']  = intval($uinfo['refinterval']);
	$opt['refmode']      = intval($uinfo['refmode']);
	$opt['detailsstyle'] = intval($uinfo['detailsstyle']);
	$opt['hostnames']    = intval($uinfo['hostnames']);
	$opt['bitfields']    = intval($uinfo['bitfields']);
	$opt['lng']          = $uinfo['language'];
	$opt['style']        = $uinfo['design'];
	$opt['detailsmode']  = intval($uinfo['detailsmode']);

	return($opt);
}

function writeOptions($opt)
{
	global $db;

	$db->query('UPDATE users SET viewchange = ?, sortord = ?, refchange = ?, viewmode = ?, groupmode = ?, sourcemode = ?, sortkey = ?, refinterval = ?, refmode = ?, detailsstyle = ?, hostnames = ?, bitfields = ?, language = ?, design = ?, detailsmode = ? WHERE uid = ?',
		'isiiiiiiiiiissii',
		$opt['viewchange']   ,
		$opt['sortord']      ,
		$opt['refchange']    ,
		$opt['viewmode']     ,
		$opt['groupmode']    ,
		$opt['sourcemode']   ,
		$opt['sortkey']      ,
		$opt['refinterval']  ,
		$opt['refmode']      ,
		$opt['detailsstyle'] ,
		$opt['hostnames']    ,
		$opt['bitfields']    ,
		$opt['lng']          ,
		$opt['style']        ,
		$opt['detailsmode']  ,
		$_SESSION['uid']);

	if($db->affected_rows())
		return true;
	else
		return false;
}

$sort_arr     = array('ASC', 'DESC');
$details_arr  = array('samepage', 'popup', 'inline');
$detailsm_arr = array('filetree', 'filelist', 'infos', 'tracker', 'peers');
$shout_arr    = array('shoutoff', 'shoutside', 'shouttop', 'shoutbottom');
$hosts_arr    = array('no', 'yes');
$sortkey_arr  = array(NAME             => 'name',
							PERCENT_COMPLETE => 'done',
							ETA              => 'eta',
							UP_RATE          => 'up_rate',
							DOWN_RATE        => 'down_rate',
							UP_TOTAL         => 'seeded',
							COMPLETED_BYTES  => 'completed',
							SIZE_BYTES       => 'size',
							PEERS_CONNECTED  => 'peers',
							RATIO            => 'ratio');


$options = loadOptions();

if(isset($_POST['save']))
{
	if(isset($_POST['viewchange']))
	{
		if($_POST['viewchange'] == 'yes')
			$options['viewchange'] = 1;
		else
			$options['viewchange'] = 0;
	}

	if(isset($_POST['refrchange']))
	{
		if($_POST['refrchange'] == 'yes')
			$options['refchange'] = 1;
		else
			$options['refchange'] = 0;
	}

	if(isset($_POST['view']) && isset($view_arr[$_POST['view']]))
		$options['viewmode'] = $_POST['view'];
	if(isset($_POST['group']) && isset($group_arr[$_POST['group']]))
		$options['groupmode'] = $_POST['group'];
	if(isset($_POST['source']) && isset($source_arr[$_POST['source']]))
		$options['sourcemode'] = $_POST['source'];
	if(isset($_POST['sortkey']) && isset($sortkey_arr[$_POST['sortkey']]))
		$options['sortkey'] = $_POST['sortkey'];
	if(isset($_POST['sortord']) && in_array($_POST['sortord'], $sort_arr))
		$options['sortord'] = $_POST['sortord'];
	if(isset($_POST['refmode']) && isset($refresh_arr[$_POST['refmode']]))
		$options['refmode'] = $_POST['refmode'];
	if(isset($_POST['refinterval']) && is_numeric($_POST['refinterval']))
		$options['refinterval'] = $_POST['refinterval'];
	$a = scandir(TO_ROOT.'languages/');
	if(isset($_POST['language']) && in_array($_POST['language'], $a))
		$options['lng'] = $_POST['language'];
	$a = scandir(TO_ROOT.'styles/');
	if(isset($_POST['style']) && in_array($_POST['style'], $a))
		$options['style'] = $_POST['style'];
	if(isset($_POST['details']) && isset($details_arr[$_POST['details']]))
		$options['detailsstyle'] = $_POST['details'];
	if(isset($_POST['detailsm']) && isset($detailsm_arr[$_POST['detailsm']]))
		$options['detailsmode'] = $_POST['detailsm'];
	if(isset($_POST['hostnames']) && isset($hosts_arr[$_POST['hostnames']]))
		$options['hostnames'] = $_POST['hostnames'];
	if(isset($_POST['bitfields']) && isset($hosts_arr[$_POST['bitfields']]))
		$options['bitfields'] = $_POST['bitfields'];


	if(writeOptions($options))
		$success = $lng['saved'];

	//Apply some Options
	$_SESSION['lng']          = $options['lng'];
	$_SESSION['detailsstyle'] = $options['detailsstyle'];
	$_SESSION['hostnames']    = $options['hostnames'];
	$_SESSION['bitfields']    = $options['bitfields'];
	$_SESSION['style']        = $options['style'];
	$_SESSION['detailsmode']  = $options['detailsmode'];


	//Password Change
	if(isset($_POST['oldpwd']) && isset($_POST['newpwd']) && isset($_POST['newpwd2']))
	{
		if($_POST['oldpwd'] != '' || $_POST['newpwd'] != '' || $_POST['newpwd2'] != '')
		{
			$result = $db->query('SELECT password, salt FROM users WHERE uid = ?', 'i', $_SESSION['uid']);
			if(($h = $db->fetch($result)) && ($h['password'] == sha1($_POST['oldpwd'].$h['salt'])))
			{
				if($_POST['newpwd'] == $_POST['newpwd2'])
				{
					$newpassword = sha1($_POST['newpwd'].$h['salt']);
					$db->query('UPDATE users SET password = ? WHERE uid = ?', 'si', $newpassword, $_SESSION['uid']);
					if($db->affected_rows())
						$success2 = $lng['pwdchanged'];
					else
						$error = $lng['internerror'];

					if(strlen($_POST['newpwd']) < 8)
						$notify = $lng['shortpwd'];
				}
				else
					$error = $lng['pwdnotident'];
			}
			else
				$error = $lng['wrongpwd'];
		}
	}
}

$view_dropdown = '<select name="view">';
foreach($view_arr as $n => $v)
{
	if($n == $options['viewmode'])
		$view_dropdown .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
	else
		$view_dropdown .= "<option value=\"$n\">{$lng[$v]}</option>";
}
$view_dropdown .= '</select>';
$group_dropdown  = '<select name="group">';
foreach($group_arr as $n => $v)
{
	if($n == $options['groupmode'])
		$group_dropdown .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
	else
		$group_dropdown .= "<option value=\"$n\">{$lng[$v]}</option>";
}
$group_dropdown .= '</select>';
if($settings['real_multiuser'])
{
	$source_dropdown  = '<select name="source">';
	foreach($source_arr as $n => $v)
	{
		if($n == $options['sourcemode'])
			$source_dropdown .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
		else
			$source_dropdown .= "<option value=\"$n\">{$lng[$v]}</option>";
	}
	$source_dropdown .= '</select>';
}
$sort_dropdown  = '<select name="sortkey">';
foreach($sortkey_arr as $n => $v)
{
	if($n == $options['sortkey'])
		$sort_dropdown .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
	else
		$sort_dropdown .= "<option value=\"$n\">{$lng[$v]}</option>";
}
$sort_dropdown .= '</select>';

$sortord_dropdown  = '<select name="sortord">';
foreach($sort_arr as $n => $v)
{
	if($n == $options['sortord'])
		$sortord_dropdown .= "<option value=\"$v\" selected=\"selected\">{$lng[strtolower($v)]}</option>";
	else
		$sortord_dropdown .= "<option value=\"$v\">{$lng[strtolower($v)]}</option>";
}
$sortord_dropdown .= '</select>';

$refr_dropdown = '<select name="refmode">';
foreach($refresh_arr as $key => $val)
{
	if($options['refmode'] == $key)
		$refr_dropdown .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
	else
		$refr_dropdown .= "<option value=\"$key\">{$lng[$val]}</option>";
}
$refr_dropdown .= "</select>";

$details_dropdown = '<select name="details">';
foreach($details_arr as $key => $val)
{
	if($options['detailsstyle'] == $key)
		$details_dropdown .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
	else
		$details_dropdown .= "<option value=\"$key\">{$lng[$val]}</option>";
}
$details_dropdown .= "</select>";

$detailsm_dropdown = '<select name="detailsm">';
foreach($detailsm_arr as $key => $val)
{
	if($options['detailsmode'] == $key)
		$detailsm_dropdown .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
	else
		$detailsm_dropdown .= "<option value=\"$key\">{$lng[$val]}</option>";
}
$detailsm_dropdown .= "</select>";

$hostnames_dropdown = '<select name="hostnames">';
foreach($hosts_arr as $key => $val)
{
	if($options['hostnames'] == $key)
		$hostnames_dropdown .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
	else
		$hostnames_dropdown .= "<option value=\"$key\">{$lng[$val]}</option>";
}
$hostnames_dropdown .= "</select>";

if($settings['showbitfields'])
{
	$bitfields_dropdown = '<select name="bitfields">';
	foreach($hosts_arr as $key => $val)
	{
		if($options['bitfields'] == $key)
			$bitfields_dropdown .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
		else
			$bitfields_dropdown .= "<option value=\"$key\">{$lng[$val]}</option>";
	}
	$bitfields_dropdown .= "</select>";
}

$style_dropdown = "<select name=\"style\">";
$a = scandir(TO_ROOT.'styles/');
foreach($a as $f)
{
	if($f == '.' || $f == '..')
		continue;
	if($options['style'] == $f)
		$checked = ' selected="selected"';
	else
		$checked = '';
	$style_dropdown .= "<option value=\"$f\"$checked>$f</option>";
}
$style_dropdown .= '</select>';

$lang_dropdown = "<select name=\"language\">";
$a = scandir(TO_ROOT.'languages/');
foreach($a as $f)
{
	if($f == '.' || $f == '..')
		continue;
	if($options['lng'] == $f)
		$checked = ' selected="selected"';
	else
		$checked = '';
	$lang_dropdown .= "<option value=\"$f\"$checked>$f</option>";
}
$lang_dropdown .= '</select>';



$viewselyes = $viewselno = $refrselyes = $refrselno = '';

if($options['viewchange'])
	$viewselyes = ' checked="checked"';
else
	$viewselno  = ' checked="checked"';
if($options['refchange'])
	$refrselyes = ' checked="checked"';
else
	$refrselno  = ' checked="checked"';


$view_options  = "<table><tr><td>{$lng['filter']}</td><td>$view_dropdown</td></tr>";
$view_options .= "<tr><td>{$lng['grouping']}</td><td>$group_dropdown</td></tr>";
if($settings['real_multiuser'])
	$view_options .= "<tr><td>{$lng['source']}</td><td>$source_dropdown</td></tr>";
$view_options .= "<tr><td>{$lng['sorting']}</td><td>$sort_dropdown</td></tr>";
$view_options .= "<tr><td>{$lng['sortord']}</td><td>$sortord_dropdown</td></tr>";
$view_options .= "</table>";


$refr_options  = "<table><tr><td>{$lng['refmode']}</td><td>$refr_dropdown</td></tr>";
$refr_options .= "<tr><td>{$lng['interval']}</td><td><input type=\"text\" class=\"text\" name=\"refinterval\" value=\"{$options['refinterval']}\" />{$lng['sec']}</td></tr>";
$refr_options .= "</table>";







$box_view  = "<fieldset class=\"box\" id=\"viewbox\"><legend>{$lng['viewoptions']}</legend><table>";
$box_view .= "<tr><td><input type=\"radio\" name=\"viewchange\" value=\"yes\" id=\"viewyes\"$viewselyes /></td><td><label for=\"viewyes\">{$lng['lastchosen']}</label></td></tr>";
$box_view .= "<tr><td><input type=\"radio\" name=\"viewchange\" value=\"no\"  id=\"viewno\"$viewselno /></td><td><label for=\"viewno\" >{$lng['followingop']}</label><br />$view_options</td></tr>";
$box_view .= "</table></fieldset>";

$box_refr  = "<fieldset class=\"box\" id=\"refrbox\"><legend>{$lng['refoptions']}</legend><table>";
$box_refr .= "<tr><td><input type=\"radio\" name=\"refrchange\" value=\"yes\" id=\"refryes\"$refrselyes /></td><td><label for=\"refryes\">{$lng['lastchosen']}</label></td></tr>";
$box_refr .= "<tr><td><input type=\"radio\" name=\"refrchange\" value=\"no\"  id=\"refrno\"$refrselno  /></td><td><label for=\"refrno\" >{$lng['followingop']}</label><br />$refr_options</td></tr>";
$box_refr .= "</table></fieldset>";

$box_settings  = "<fieldset class=\"box\" id=\"accsettingsbox\"><legend>{$lng['accsettings']}</legend><table>";
$box_settings .= "<tr><td>{$lng['language']}</td><td>$lang_dropdown</td></tr>";
$box_settings .= "<tr><td>{$lng['style']}</td><td>$style_dropdown</td></tr>";
$box_settings .= "<tr><td>{$lng['opendetails']}</td><td>$details_dropdown</td></tr>";
$box_settings .= "<tr><td>{$lng['detailsmode']}</td><td>$detailsm_dropdown</td></tr>";
$box_settings .= "<tr><td>{$lng['showhosts']}</td><td>$hostnames_dropdown</td></tr>";
if($settings['showbitfields'])
	$box_settings .= "<tr><td>{$lng['showbfields']}</td><td>$bitfields_dropdown</td></tr>";
$box_settings .= "</table></fieldset>";

$box_pwd  = "<fieldset class=\"box\" id=\"pwdbox\"><legend>{$lng['changepwd']}</legend><table>";
$box_pwd .= "<tr><td>{$lng['oldpwd']}</td><td><input type=\"password\" class=\"text\" name=\"oldpwd\" /></td></tr>";
$box_pwd .= "<tr><td>{$lng['newpwd']}</td><td><input type=\"password\" class=\"text\" name=\"newpwd\" /></td></tr>";
$box_pwd .= "<tr><td>{$lng['newpwd2']}</td><td><input type=\"password\" class=\"text\" name=\"newpwd2\" /></td></tr>";
$box_pwd .= "</table></fieldset>";


$cpout = '';
if(isset($error))
	$cpout .= "<div class=\"error\">$error</div>";
if(isset($success))
	$cpout .= "<div class=\"success\">$success</div>";
if(isset($success2))
	$cpout .= "<div class=\"success\">$success2</div>";
if(isset($notify))
	$cpout .= "<div class=\"notify\">$notify</div>";

$submit = "<div><input type=\"submit\" value=\"{$lng['apply']}\" name=\"save\" /></div>";
$cpout .= "<form action=\"controlpanel.php?mod=$mod&amp;sub=$sub$sid\" method=\"post\">$box_view$box_refr$box_settings$box_pwd$submit</form>";
