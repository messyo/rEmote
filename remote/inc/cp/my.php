<?php

if(!defined('IN_CP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

require_once(TO_ROOT.'inc/defines/torrent.php');

define('IN_MYCP', true);

$sub_array = array(
	'account',
	'boxarea',
	'extlinks'
);

if(isset($_GET['sub']) && isset($sub_array[$_GET['sub']]))
	$sub = $_GET['sub'];
else
	$sub = 0;

$cpout = '';

require_once(TO_ROOT."inc/cp/mycp/{$sub_array[$sub]}.php");


$mycpmenu = '<ul class="tabs">';
foreach($sub_array as $key => $m)
	$mycpmenu .= '<li class="'.($sub == $key ? 'viewselon' : 'viewseloff')."\"><a href=\"controlpanel.php?mod=$mod&amp;sub=$key$sid\">{$lng['mycp'.$m]}</a></li>";
$mycpmenu .= '</ul>';

$cpout = "$mycpmenu<div class=\"tabsbody\" id=\"mycpcontent\">$cpout</div>";


?>
