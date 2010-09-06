<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$lockfile = TO_ROOT.'.lock';


$success = false;

$out = "<h1>{$lng['completion']}</h1>";

if(is_writeable(TO_ROOT) || (is_file($lockfile) && is_writeable($lockfile)))
{
	if($h = fopen($lockfile, "w"))
	{
		fwrite($h, VERSION);
		fclose($h);
		$success = true;
	}
}

if($success)
{
	$out .= "<div class=\"success\">{$lng['comsuccess']}</div>";
	$out .= '<div style="margin-top: 50px;"><a href="'.TO_ROOT.'" title="GoTo rEmote">'.$lng['goremote'].'</a></div>';
}
else
{
	$out .= "<div class=\"error\">{$lng['notlocked']}</div>";
	$out .= "<div class=\"notify\">{$lng['notlockedi']}</div>";
	$retry_possible = true;
}

?>
