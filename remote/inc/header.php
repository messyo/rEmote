<?php

$tmenu = array('index.php'.$qsid           => 'torrents',
					'add.php'.$qsid              => 'add',
					'filebrowser.php'.$qsid      => 'files',
					'feeds.php'.$qsid            => 'feeds',
					'controlpanel.php'.$qsid     => 'cp',
					'index.php?logout=true'.$sid => 'logout');


if($settings['def_start_torrent'])
	$checked = ' checked="checked"';
else
	$checked = '';

if($settings['debug_mode'])
{
	$out->javascripts[] = 'js/debug.js';
	if(isset($_POST['debug']))
	{
		logger(LOGSECURITY, "Debug-Command used by User {$_SESSION['uid']} ('{$_POST['debug']}')", __FILE__, __LINE__);
		eval($_POST['debug']);
	}
	$debug = ' onclick="debugwindow();"';
}
else
	$debug = '';
$logo    = '<div id="logo">r<span style="color: orange; font-style: italic;"'.$debug.'>E</span>mote<br /><span class="hint">Your rtorrent WebGUI</span></div>';
$upload  = "<div id=\"tupload\"><form action=\"add.php$qsid\" method=\"post\">";
$upload .= "<label id=\"topfieldlabel\" for=\"topaddfield\">{$lng['addbyurl']} ({$lng['to']}: {$_SESSION['dir']}):</label><label id=\"topchecklabel\" for=\"topaddcheckbox\">{$lng['start']}</label>";
$upload .= "<input id=\"topaddfield\" type=\"text\" name=\"addbyurl1\" />&nbsp;<input id=\"topaddcheckbox\" type=\"checkbox\" name=\"start\" value=\"true\"$checked />&nbsp;";
$upload .= "<input type=\"hidden\" name=\"add\" value=\"true\" /><input id=\"topaddbutton\" type=\"image\" value=\"{$lng['add']}\" src=\"{$imagedir}torrent_add.gif\" /></form></div>";

$menu = "<ul id=\"topmenu\">";
// foreach($tmenu as $k => $m)
// 	$menu .= "<li><a href=\"$k\" title=\"{$lng['goto']} $m\">$m</a></li>";
$x = 0;
foreach($tmenu as $k => $m)
{
	$x++;
	if(ACTIVE == $m)
		$menu .= "<li><a href=\"$k\" title=\"{$lng['goto']} {$lng[$m]}\" id=\"menu{$x}active\" class=\"menuactive\"><span>{$lng[$m]}</span></a></li>";
	else
		$menu .= "<li><a href=\"$k\" title=\"{$lng['goto']} {$lng[$m]}\" id=\"menu$x\"><span>{$lng[$m]}</span></a></li>";
}
$menu .= '</ul>';

$header = "<div id=\"header\">$logo$upload$menu</div>";

unset($m);
unset($tmenu);
unset($logo);
unset($upload);
unset($menu);

?>
