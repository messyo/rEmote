<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");


if(($lngs = makeLanguageChoice()) !== false)
{
	if(isset($_SESSION['language']))
		$lang = $_SESSION['language'];
	else
	{
		$lang = 'English';
		$success = false;
	}

	if(isset($_POST['save']))
	{
		if(in_array($_POST['lng'], $lngs))
		{
			$lang = $_POST['lng'];
			$_SESSION['language'] = $lang;
			require(TO_ROOT.'languages/'.$_SESSION['language'].'/install.lng.php');
			$success = true;
		}
		else
			$success = false;
	}

	$out = "<h1>{$lng['chooselng']}</h1>";
	$out .= "<form action=\"index.php$qsid\" method=\"post\"><div><select name=\"lng\">";
	foreach($lngs as $l)
	{
		if($l == $lang)
			$s = ' selected="selected"';
		else
			$s = '';

		$out .= "<option$s>$l</option>";
	}
	$out .= "</select><br /><input type=\"submit\" name=\"save\" value=\"{$lng['save']}\" /></div></form>";
}
else
{
	$out = "<div class=\"error\">Could not load languages</div>";
	$success = false;
}

?>
