<?php

function read_highlights($id)
{
	global $db, $higharr;

	$result = $db->query('SELECT expression, fields, regex, function FROM highlightrules WHERE fid = ? OR fid = 0', 'i', $id);
	if($db->num_rows($result))
	{
		while($h = $db->fetch($result))
		{
			$expr = $h['expression'];
			$regx = ord($h['regex']);
			if($regx)
				$expr = str_replace('/', '\/', $expr);

			$higharr[] = array('expression' => $expr,
				'fields'  => intval($h['fields']),
				'function'=> intval($h['function']),
				'regex'  => $regx);
		}
	}
	else
		$higharr = array();
}

function highlight($field, $message)
{
	global $higharr;
	for($x = 0; $x < count($higharr); $x++)
	{
		if($field | $higharr[$x]['fields'])
		{
			if($higharr[$x]['regex'])
			{
				if(preg_match("/{$higharr[$x]['expression']}/", $message))
					return $higharr[$x]['function'];
			}
			else
			{
				if(strstr($message, $higharr[$x]['expression']))
					return $higharr[$x]['function'];
			}
		}
	}

	return 0;
}

function fetchRss($url, $id, $download, $ddir = '')
{
	global $settings;

	if(!($xml = @file_get_contents($url)) || ($xml == ''))
		return false;

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
	xml_parse_into_struct($parser, $xml, $tags);
	xml_parser_free($parser);

	read_highlights($id);

	if(!$settings['disable_sem'])
	{
		$sem = sem_get(SEM_KEY);
		if(!sem_acquire($sem))
			fatal("Could not acquire Semaphore!", __FILE__, __LINE__);
	}

	$open  = false;
	$count = 0;
	$time  = time();
	$c     = $time.$count;
	$items = array();
	$level = 0;
	$name  = '';
	foreach($tags as $tag)
	{
		if($open)
		{
			if($tag['tag'] == 'item' && $tag['type'] == 'close')
			{
				if(isset($items[$c]))
				{
					if(!isset($items[$c]['title']))
						unset($items[$c]);
					else
					{
						$func = highlight(FEEDTITLE, $items[$c]['title']['value']);

						if(isset($items[$c]['description']))
							$func |= highlight(FEEDDESCR, $items[$c]['description']['value']);


						if($func & 1)
							$items[$c]['marked'] = true;
						else
							$items[$c]['marked'] = false;

						if($func & 2)
						{
							if($ddir == '')
								$ddir = $_SESSION['dir'];

							$durl = false;

							if($download)
								$durl = $items[$c]['link']['value'];
							else if($items[$c]['enclosure']['attributes']['type'] == 'application/x-bittorrent')
								$durl = $items[$c]['enclosure']['attributes']['url'];

							if($durl !== false)
							{
								set_directory($ddir);
								get_torrent($durl, false, true);
							}
						}
					}
				}
				$count++;
				$c = $time.$count;
				$open = false;
			}
			else if($level)
			{
				if($tag['type'] == 'close' && $tag['level'] == $level)
					$level = 0;
				else
				{
					if(isset($tag['value']))
						$items[$c][$name]['value'] .= $tag['value'];
					if(isset($tag['attributes']))
						$items[$c][$name]['attributes'] = array_merge($items[$c][$name]['attributes'], $tag['attributes']);
				}
			}
			else if($tag['type'] == 'complete')
			{
				if(isset($tag['value']))
					$items[$c][$tag['tag']]['value']      = $tag['value'];
				if(isset($tag['attributes']))
					$items[$c][$tag['tag']]['attributes'] = $tag['attributes'];
			}
			else if($tag['type'] == 'open')
			{
				$level = $tag['level'];
				$name  = $tag['tag'];
				$items[$c][$name]['value'] = '';
				$items[$c][$name]['attributes'] = array();
			}
		}
		else if($tag['tag'] == 'item' && $tag['type'] == 'open')
				$open = true;
	}

	if(!$settings['disable_sem'])
		sem_release($sem);

	return($items);
}

function getItems($id)
{
	global $db, $lng;

	if(!($items = cache_get("feed$id")))
	{
		if($h = $db->fetch($db->query('SELECT url, `interval`, directory, download FROM feeds WHERE fid = ? AND uid = ?', 'ii', $id, $_SESSION['uid'])))
		{
			if($items = fetchRss($h['url'], $id, ord($h['download']), $h['directory']))
				cache_put("feed$id", $items, $_SESSION['uid'], time() + $h['interval']);
		}
		else
		{
			$items = false;
			logger(LOGERROR | LOGSECURITY, "User {$_SESSION['uid']} tried to fetch invalid or foreign rss-feed", __FILE__, __LINE__);
		}
	}

	return($items);
}

?>
