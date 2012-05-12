<?php

if(!defined('IN_CP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

$cpout = '';
$cpout .= "<fieldset class=\"box\" id=\"bcredits\"><legend>{$lng['credits']}</legend>";
$cpout .= '<a href="http://libtorrent.rakshasa.no/" target="_blank" title="rTorrent &amp; libtorrent">rTorrent &amp; libtorrent</a> - by Jari "Rakshasa" Sundell<br />';
$cpout .= '<a href="http://rtgui.googlecode.com" target="_blank" title="rtGui">rtGui v0.2.2</a> - by Simon Hall<br />';
$cpout .= '<a href="http://icon-king.com/?p=15" target="_blank">Nuvola Icon-Package</a> - by David Vignoni<br />';
$cpout .= '<a href="http://nfo.corephp.co.uk" target="_blank">NFO Viewer</a> - by Richard Davey<br />';
$cpout .= '<a href="http://renegat.org/smilies/index.php?color=2" target="_blank">Smileys</a> - by Aurelian Hermand<br />';

$cpout .= '</fieldset>';

if($settings['user_see_serverinfo'] == '1' || $_SESSION['status'] > 1)
{
	$infos    = $rpc->simple_multicall('get_memory_usage', 'system.pid', 'system.hostname');
	$rtstats  = "<table id=\"rtstats\">";
	$rtstats .= "<tr><td>{$lng['memusage']}:</td><td>".format_bytes($infos[0][0])."</td></tr>";
	$rtstats .= "<tr><td>{$lng['versioncli']}:</td><td>{$global['versions']['rtorrent']}</td></tr>";
	$rtstats .= "<tr><td>{$lng['versionlib']}:</td><td>{$global['versions']['libtorrent']}</td></tr>";
	$rtstats .= "<tr><td>{$lng['versionrem']}:</td><td>{$global['versions']['remote']}</td></tr>";
	$rtstats .= "<tr><td>{$lng['pid']}:</td><td>{$infos[1][0]}</td></tr>";
	$rtstats .= "<tr><td>{$lng['hostname']}:</td><td>{$infos[2][0]}</td></tr>";
	$rtstats .= '</table>';

	$cpout .= "<fieldset class=\"box\" id=\"bserverinformation\"><legend>{$lng['serverinfo']}</legend>";

	$cpout .= "<fieldset class=\"box\" id=\"bwho\"><legend>{$lng['who']}</legend>";
	$cpout .= '<pre>'.shell_exec('w').'</pre></fieldset>';

	$cpout .= "<fieldset class=\"box\" id=\"bmemory\"><legend>{$lng['memory']}</legend>";
	$cpout .= '<pre>'.shell_exec('free -mo').'</pre></fieldset>';

	$cpout .= "<fieldset class=\"box\" id=\"buptime\"><legend>{$lng['uptime']}</legend>";
	$cpout .= '<pre>'.shell_exec('uptime').'</pre></fieldset>';

	$cpout .= "<fieldset class=\"box\" id=\"brtorrent\"><legend>{$lng['rtstats']}</legend>";
	$cpout .= $rtstats.'</fieldset>';

	$cpout .= '</fieldset>';
}


?>
