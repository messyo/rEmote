<?php

define('TO_ROOT', '../');

require_once(TO_ROOT.'inc/global.php');

function error_handler($code, $text, $file, $line)
{
	exit("ERROR ($code) in $file:$line: $text");
}

$is_refresh = true;
require_once(TO_ROOT.'inc/functions/feeds.fun.php');
session_write_close();

set_error_handler('error_handler');

if(!isset($_GET['feedid']) || !is_numeric($_GET['feedid']))
	exit("ERROR: No feed-id");

$feedid = $_GET['feedid'];

if(false === ($allowhtml = $db->one_result($db->query("SELECT allowhtml FROM feeds WHERE fid = $feedid"), 'allowhtml')))
	exit("ERROR: Could not access Feed");

$allowhtml = intval($allowhtml);

if(!($items = getItems($feedid)))
	exit("ERROR: {$lng['couldntread']}");

if(!isset($_GET['descrid']) || !isset($items[$_GET['descrid']]))
	exit("ERROR: {$lng['norssitem']}");

if(!isset($items[$_GET['descrid']]['description']['value']))
	exit("ERROR: {$lng['nodescr']}");


if($allowhtml)
	$descr = '<br />'.$items[$_GET['descrid']]['description']['value'];
else
	$descr = '<br />'.str_replace("\n", '<br />', htmlspecialchars($items[$_GET['descrid']]['description']['value']));

echo $descr;

?>
