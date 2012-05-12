<?php

if(!defined('IN_CP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");


$cpout = "<fieldset class=\"box\" id=\"blogdelete\"><legend>{$lng['delete']}</legend>";
if(isset($_GET['at']))
	$cpout .= makeSecQuery($lng['logdelconf'], $mod, array());
else
{
	if(isset($_POST['confirm']))
		$db->query('TRUNCATE TABLE log');
	$cpout .= "<a href=\"controlpanel.php?mod=$mod&amp;at=at$sid\" title=\"{$lng['dellog']}\">{$lng['dellog']}</a>";
}

$cpout .= '</fieldset>';

$result = $db->query('SELECT time, script, message FROM log ORDER BY lid DESC');
$tcontent = '';
if($result && $db->num_rows($result))
{
	for($x = 0; $h = $db->fetch($result); $x++)
		$tcontent .= '<tr class="row'. $x%2 .'"><td class="logtimestamp">'.date("d.m.Y - H:i:s", $h['time'])."</td><td class=\"logscript\">{$h['script']}</td><td>". htmlspecialchars($h['message'], ENT_QUOTES).'</td></tr>';
}
else
	$tcontent .= "<tr><td colspan=\"3\"><div class=\"notify\">{$lng['emptylog']}</div></td></tr>";

$cpout .= "<table id=\"logtable\">$tcontent</table>";

?>
