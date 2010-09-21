<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$db = new Database($sql);

$out = "<h1>{$lng['settings']}</h1>";

if(($file = file('settings.txt')) === false)
	$out .= "<div class=\"error\">".lng('couldntread', 'settings.txt')."</div>";
else
{
	$first = true;

	foreach($file as $line)
	{
		$line = trim($line);
		if($line[0] == '#')
			continue; // Comment
		list(,$key,) = explode('\'', $line, 3);
		if(($count = intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM settings WHERE skey = ?', 's', $key), 'c'))) == 0)
		{
			if($first)
			{
				$first = false;
				$qry = $line;
				$details  = lng('adding', $key);
			}
			else
			{
				$qry .= ", $line";
				$details .= '<br />'.lng('adding', $key);
			}
		}
	}

	if($first == false)
	{
		$db->query('DELETE FROM cache WHERE ckey = ?', 's', 'settings');
		$qry = 'INSERT INTO `settings` (`skey`, `defaultname`, `gid`, `inputtype`, `onsave`, `value`, `sortid`, `defaultvalue`) VALUES '.$qry;
		$res = $db->query($qry);

		$out .= "<div class=\"small\">$details</div>";
		if($db->affected_rows($res))
			$out .= "<div class=\"success\">{$lng['settingsu2d']}</div>";
		else
		{
			$success = false;
			$out .= "<div class=\"error\">{$lng['errorins']}</div>";
		}
	}
	else
		$out .= "<div class=\"success\">{$lng['settingsu2d']}</div>";
	if(isset($details))
		$out .= "<div class=\"notify\">{$lng['newsettings']}</div>";
}



?>
