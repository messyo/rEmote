<?php
//
// ===========================================
// ======== Part of the rEmote-WebUI =========
// ===========================================
//
// Contains base functions
//


function logger($l, $message, $script, $line)
{
	global $settings, $db;

	if($settings['loglevel'] & $l)
		$db->query('INSERT INTO log (time, script, message) VALUES (?, ?, ?)', 'iss',
						time(),
						basename($script).':'.$line,
						$message);
}

function fatal($message, $script, $line)
{
	global $db, $out;

	$db->query('INSERT INTO log (time, script, message) VALUES (?, ?, ?)', 'iss',
								time(),
								basename($script).':'.$line,
								"FATAL: $message");
	$out->fatal('FATAL ERROR', $message);
}

function simple_cache_get($key)
{
	global $db;

	$res = $db->query('SELECT content FROM cache WHERE ckey = ?', 's', $key);
	if($db->num_rows($res))
		return(unserialize($db->one_result($res, 'content')));
	else
		return(false);
}

function cache_get($key)
{
	global $db;

	$res = $db->query('SELECT content FROM cache WHERE ckey = ? AND uid = ? AND (expires > ? OR expires = 0 )', 'sii', $key, $_SESSION['uid'], time());
	if($db->num_rows($res))
		return(unserialize($db->one_result($res, 'content')));
	else
		return(false);
}

function cache_put($key, $content, $uid = 0, $expires = 0)
{
	global $db;
	$result = $db->query('SELECT COUNT(*) AS c FROM cache WHERE ckey = ? AND uid = ?', 'si',
								$key,
								$uid);
	if($result && (intval($db->one_result($result, 'c')) > 0))
		$db->query('UPDATE cache SET lastmodified = ?, expires = ?, content = ? WHERE ckey = ? AND uid = ?', 'iissi',
						time(),
						$expires,
						serialize($content),
						$key,
						$uid);
	else
		$db->query('INSERT INTO cache (ckey, uid, lastmodified, expires, content) VALUES (?, ?, ?, ?, ?)', 'siiis',
						$key,
						$uid,
						time(),
						$expires,
						serialize($content));
}

function do_xmlrpc($method, $params, $dieonfault = true) {	exit('USE OF DEPRECATED do_xmlrpc!'); }
function simple_system_multicall($f1)                    {	exit('USE OF DEPRECATED simple_system_multicall!'); }
function system_multicall($f1, $a1)                      {	exit('USE OF DEPRECATED system_multicall!'); }

function lng($l)
{
	global $lng;

	$argc = func_num_args();
	$ret = $lng[$l];

	for($i = 1; $i < $argc; $i++)
	{
		$arg = func_get_arg($i);
		$ret = str_replace("\\$i", $arg, $ret);
	}

	return($ret);
}

function format_bytes($bytes)
{
	global $lng;

	for($c = 0; $bytes >= 1024; $c++)
		$bytes /= 1024;

	return number_format($bytes,($c ? 1 : 0), $lng['numseps'][0], $lng['numseps'][1]).' '.$lng["B$c"];
}

function set_directory($dir)
{
	global $rpc;
	$rpc->request('set_directory', $dir);
}

function is_valid_dir($pdir)
{
	global $settings;

	return(  (substr($pdir, 0, strlen($_SESSION['rootdir'])) == $_SESSION['rootdir'] && is_dir(substr($pdir, 0, -1)))
			|| ( $settings['allowtmp'] && (substr($pdir, 0, strlen($settings['tmpdir']))  == $settings['tmpdir'] && is_dir(substr($pdir, 0, -1)))));
}

function is_valid_file($pdir, $mustexist = true)
{
	global $settings;


	return(  (substr($pdir, 0, strlen($_SESSION['rootdir'])) == $_SESSION['rootdir'] && (!$mustexist || is_file($pdir)))
			|| ( $settings['allowtmp'] && ((substr($pdir, 0, strlen($settings['tmpdir']))  == $settings['tmpdir']) && (!$mustexist || is_file(substr($pdir, 0, -1))))));
}

function clean_dir($pdir)
{
	$folders = explode('/', $pdir);
	$anz = count($folders);
	for($x = 0; $x < $anz; $x++)
	{
		if($folders[$x] == '..')
		{
			$folders[$x] = '';
			if(isset($folders[$x-1]))
				$folders[$x-1] = '';
		}
		else if($folders[$x] == '.')
			$folders[$x] = '';
	}
	$dir = '/';
	foreach($folders as $folder)
	{
		if($folder != '')
			$dir .= $folder . '/';
	}
	return($dir);
}

function clean_path($pdir)
{
	return clean_dir(dirname($pdir)).basename($pdir);
}


function progressbar($prog, $label = '')
{
	global $imagedir;

	return "<div class=\"progress\"><img src=\"{$imagedir}progress.png\" height=\"10px\" width=\"$prog%\" alt=\"$prog%\" /><div>$label</div></div>";
}

function maxlength($string, $length)
{
	if(strlen($string) > $length)
		return(substr($string, 0, $length - 3).'...');
	else
		return $string;
}

function cutMiddle($string, $length)
{
	if(strlen($string) <= $length)
		return $string;

	$start = floor(($length-3)/2);
	$end   = ceil(($length-3)/(-2));

	return substr($string, 0, $start).'...'.substr($string, $end);
}

function getBin($name, $logit = true)
{
	global $settings;

	$skey = 'binary_'.$name;
	if(!isset($settings[$skey]) || ($settings[$skey] == ''))
	{
		if($logit)
			logger(LOGINFOS, "Executeable $name not set", __FILE__, __LINE__);
		return false;
	}

	$exec = $settings[$skey];

	if($exec[0] != '/')
		$exec = clean_dir(getcwd().'/'.TO_ROOT).$exec;

	if(!is_file($exec) || !is_executable($exec))
	{
		if($logit)
			logger(LOGERROR, "Executeable $name does not exist or ist not executable", __FILE__, __LINE__);
		return false;
	}

	return escapeshellcmd($exec);
}

function replace_latin1($string)
{
	static $latin1 = array( 'ä',  'ö',  'ü',  'Ä',     'Ö',     'Ü',     'ß'    );
   static $utf8   = array( 'Ã¤', 'Ã¶', 'Ã¼', 'Ã„', 'Ã–', 'Ãœ', 'ÃŸ');
	
	return str_replace($latin1, $utf8, $string);
}

function getUsername($id)
{
	global $db, $lng, $list_of_usernames;

	if($id == 0)
		return 'Public';

	if(!isset($list_of_usernames[$id]))
	{
		if(($name = $db->one_result($db->query('SELECT name FROM users WHERE uid = ?', 'i', $id))) !== false)
			$list_of_usernames[$id] = $name;
		else
			return $lng['orphaned'];
	}

	return $list_of_usernames[$id];
}

function replaceSmileys($string)
{
	global $smileyimgs;

	static $smileys = array(
   	':-)' => 'smile.gif',
   	':)'  => 'smile.gif',
   	':('  => 'sad.gif',
   	':-(' => 'sad.gif',
   	';-)' => 'wink.gif',
   	';)'  => 'wink.gif',
   	':|'  => 'neutral.gif',
		':-|' => 'neutral.gif',
		'8)'  => 'cool.gif',
		'8-)' => 'cool.gif',
		':O'  => 'shock.gif',
		':-O' => 'shock.gif'
	);

	if(is_array($string))
		return "<img src=\"{$smileyimgs}{$smileys[$string[1]]}\" alt=\"{$string[1]}\" />";
	
	static $smileyPattern = '#(:\-\)|:\)|:\-\(|:\(|;\-\)|;\)|:\||:\-\||8\-\)|8\)|:O|:\-O)#';
	
	return preg_replace_callback($smileyPattern, 'replaceSmileys', $string);
}

function makeShoutbox()
{
	global $db, $lng, $qsid;


	if(isset($_POST['shout']) && (trim($_POST['shout']) != ''))
	{
		$hash = sha1($_SESSION['uid'].$_POST['shout'].$_POST['time']);
		if(!intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM shouts WHERE hash = ?', 's', $hash))))
			$db->query('INSERT INTO shouts (uid, time, message, hash) VALUES (?, ?, ?, ?)', 'iiss',
				$_SESSION['uid'],
				time(),
				$_POST['shout'],
				$hash);
	}
   $result = $db->query('SELECT u.name, s.sid, s.uid, s.message, s.time FROM users u INNER JOIN shouts s ON u.uid = s.uid ORDER BY time DESC LIMIT 30');

	$shouts  = '<div id="shouts"><table>';
	while($h = $db->fetch($result))
		$shouts .= sprintf('<tr><td><strong>%s</strong><br /><span class="hint">%s</span></td><td>%s</td><td>%s</td></tr>',
			$db->out($h['name']),
			date('d.m.y H:i', $h['time']),
			replaceSmileys($db->out($h['message'])),
			'&nbsp;' // REPLACE BY DELETE-LINK
		);
	$shouts .= '</table></div>';

	$shout  = "<div id=\"shout\"><form action=\"index.php$qsid\" method=\"post\"><div><input type=\"text\" name=\"shout\" class=\"text\" />";
	$shout .= "<input type=\"hidden\" name=\"time\" value=\"".time()."\" /><input class=\"submit\" type=\"submit\" value=\"{$lng['shout']}\" /></div></form></div>";

	return "$shouts<hr />$shout";
}

function checkWrite($o, $error = false)
{
	global $out, $lng;

	if(is_writeable($o))
		return true;
	else
	{
		if($error)
			$out->addError($lng['nowrite']);
		else
			$out->addNotify($lng['nowrite']);
		
		return false;
	}
}

function checkRead($o, $error = false)
{
	global $out, $lng;

	if(is_readable($o))
		return true;
	else
	{
		if($error)
			$out->addError($lng['nowrite']);
		else
			$out->addNotify($lng['nowrite']);

		return false;
	}

}

function checkExec($o, $error = false)
{
	global $out, $lng;

	if(is_executable($o))
		return true;
	else
	{
		if($error)
			$out->addError($lng['nowrite']);
		else
			$out->addNotify($lng['nowrite']);
		
		return false;
	}

}

function checkPerms($o, $error = false)
{
	return(checkWrite($o, $error)
		|| checkRead($o, $error)
      || checkExec($o, $error));
}

function remainingJobs()
{
	global $db;
	
	$running = $db->one_result($db->query('SELECT COUNT(*) AS c FROM jobs WHERE uid = ? AND status = ?',
		'is',
		$_SESSION['uid'],
		'running'));

	return intval($running);
}

function finishedJobs()
{
	global $db;
	
	$count = $db->one_result($db->query('SELECT COUNT(*) AS c FROM jobs WHERE uid = ? AND finishtime >= ? AND finishtime < ?',
		'iii',
		$_SESSION['uid'],
		$_SESSION['lastjcheck'],
		time()));

	return intval($count);
}

function addJobChecker()
{
	global $out, $lng;

	if($_SESSION['lastjcheck'] > 0)
	{
		if(($finished = finishedJobs()) > 0)
		{
			if(($remaining = remainingJobs()) > 0)
			{
				$_SESSION['lastjcheck'] = time();
				$out->addJavascripts('js/jobs.js');
			}
			else
				$_SESSION['lastjcheck'] = 0;

			$out->addNotify(lng('jobsfinished', $finished, $remaining));
			return true;
		}
		else
		{
			$_SESSION['lastjcheck'] = time();
			$out->addJavascripts('js/jobs.js');
		}
	}

	return false;
}


function makeSecQuestion($url, $text, $vals, $method = "post")
{
	global $sid, $lng;

	$notify  = "<form action=\"$url\" method=\"$method\"><div>$text";
	foreach($vals as $k => $v)
		$notify .= "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
	$notify .= "<br /><br /><input class=\"yes\" type=\"submit\" name=\"confirm\" value=\"{$lng['yes']}\" />";
	$notify .= "<input class=\"no\" type=\"submit\" name=\"decline\" value=\"{$lng['no']}\" />";
	$notify .= '</div></form>';

	return $notify;
}

?>
