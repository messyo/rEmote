<?php


// Determines the ending of a List or Dictionary in a Torrent-File
function getEndOfList($start, &$file)
{
	$offset = $start;
	$len = strlen($file);

	if($offset > ($len - 2))
		return false;

	while($offset < $len)
	{
		$key = $file[++$offset];
		switch($key)
		{
			case 'e':
				return $offset;
				break;
			case 'i':
				$offset = strpos($file, 'e', $offset + 1);
				break;
			case 'l':
			case 'd':
				if(($offset = getEndOfList($offset, $file)) === false)
					return false;
				break;
			default:
				$nextdp = strpos($file, ':', $offset + 1);
				if(($int = intval(substr($file, $offset, ($nextdp - $offset)))) > 0)
					$offset = $nextdp + $int;
				else
					return false;
				break;
		}
	}
	return false;
}

function quick_get_hash($file)
{
	global $settings;

	$file = file_get_contents($file);

	// Find start of Info-Dictionary
	$start = strpos($file, '4:infod') + 6;

	// Find end of Info-Dictionary
	if(($end = getEndOfList($start, $file)) === false)
		return false;

	// Get String of Info-Dictionary
	$info = substr($file, $start, ($end - $start + 1));

	if(!strlen($info))
		return false;
	return(strtoupper(sha1($info)));
}

function get_files(&$str)
{
	if(false === ($start = strpos($str, '4:infod')))
	{
		logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
		return false;
	}
	$infostart = $start + 6;

	if(false === ($start = strpos($str, '5:filesl', $infostart)))
	{
		// SingleFile Torrent
		if(false === preg_match('#6:lengthi([0-9]+)e#', $str, $matches))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
			return false;
		}
		$len = $matches[1];
		
		if(false === ($start = strpos($str, '4:name', $infostart)))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
			return false;
		}
		$region = substr($str, $start + 6, 10);
		
		if(false === preg_match('#([0-9]+)[^0-9]*#', $region, $matches))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
			return false;
		}
		$strlen = intval($matches[1]);
		$start = strpos($str, ':', $start + 5) + 1;
		$name = substr($str, $start + 1, $strlen);

		return array(array('length' => $len, 'path' => array($name)));
	}
	$start += 7;

	if(false === ($end = getEndOfList($start, $str)))
	{
		logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
		return false;
	}

	$filestr = substr($str, $start, $end - $start + 1);
	$end = 0;
	$len = strlen($filestr);

	$files = array();
	
	while(true)
	{
		$start = $end + 1;
		$end   = getEndOfList($start, $filestr);
		if($end === false)
			break;
		$onefile = substr($filestr, $start, $end - $start + 1);

		preg_match('#d6:lengthi([0-9]+)e4:pathl(.*)ee#', $onefile, $matches);

		$pathlist = $matches[2];
		$path = array();
		while(strlen($pathlist))
		{
			if(strpos($pathlist, ':') === false)
				break;
			list($len, $pathlist) = explode(':', $pathlist, 2);
			$path[] = substr($pathlist, 0, $len);
			$pathlist = substr($pathlist, $len);
		}

		$files[] = array('length' => intval($matches[1]), 'path' => $path);
	}

   if(count($files))
		return $files;
	else
		return false;
}

function generate_resume_data(&$str, $dir = null)
{
	$files = get_files($str);
	$filestr = '5:filesl';
	$total_len = 0;
	if(empty($dir))
		$dir  = $_SESSION['dir'];
	
	if(false !== strpos($str, '5:filesl'))
	{
		if(false === ($start = strpos($str, '4:name')))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
			return false;
		}
		$region = substr($str, $start + 6, 10);
		logger(LOGDEBUG, "Region is $region", __FILE__, __LINE__);
		
		if(false === preg_match('#([0-9]+)[^0-9]*#', $region, $matches))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
			return false;
		}
		$strlen = intval($matches[1]);
		$start = strpos($str, ':', $start + 5) + 1;
		$name = substr($str, $start, $strlen);
		logger(LOGDEBUG, "Name is $name", __FILE__, __LINE__);
		$dir .= "/$name/";
	}

	if(!preg_match("#12:piece lengthi([0-9]+)e#", $str, $matches))
	{
		logger(LOGERROR, 'Could not generate libtorrent resume-data as torrent could not be parsed', __FILE__, __LINE__);
		return false;
	}
   $chunk_len = intval($matches[1]);

	foreach($files as $f)
	{
		$filestr .= 'd8:priorityi1e5:mtimei';
		$total_len += $f['length'];
		$path = implode('/', $f['path']);
		if(!is_file("$dir$path") || !is_readable("$dir$path"))
		{
			logger(LOGERROR, 'Could not generate libtorrent resume-data as required File was not found', __FILE__, __LINE__);
			return false;
		}	
		$filestr .= filemtime("$dir$path").'ee'; // ee = End for INteger and end for dictionary...
	}	
	$filestr .= 'e';

	$chunks = ceil($total_len/$chunk_len);
	$resume = "17:libtorrent_resumed{$filestr}8:bitfieldi{$chunks}ee";

   logger(LOGDEBUG, 'Resume data was generated', __FILE__, __LINE__);

	return $resume;
}

function add_libtorrent_resume_data($path, $dir = null)
{
	if(!is_file($path) || !is_readable($path) || !is_writable($path))
	{
		logger(LOGERROR, 'Could not generate write libtorrent resume-data as Torrent could not be written', __FILE__, __LINE__);
      return false;
	}

	$str = file_get_contents($path);
	if(false === ($resume = generate_resume_data($str, $dir)))
		return false;

	$torrent = substr($str, 0, -1);
	unset($str);
	$torrent .= $resume . 'e';

	file_put_contents($path, $torrent);
	unset($torrent);
	
	return true;	
}


function add_missing_hashes()
{
	global $db, $rpc, $settings;

	if(!$settings['disable_sem'])
		$sem = sem_get(SEM_KEY);

	if(!$settings['disable_sem'] && !sem_acquire($sem))
		fatal("Could not acquire Semaphore!", __FILE__, __LINE__);

	/*
	 * Check if Locked, for uploading torrents may be in progress
	 */


	// Get Hashes in rTorrent
	$response = $rpc->request('d.multicall', array('main', 'd.get_hash='));

	// Get Hashes in Database
	$hashes = array();
	$result = $db->query("SELECT hash FROM torrents");
	while($h = $db->fetch($result))
		$hashes[] = $h['hash'];

	// Walk through rTorrents hashes and insert missing into databse
	for($x = 0; $x < count($response); $x++)
	{
		if(!in_array($response[$x][0], $hashes))
			$db->query('INSERT INTO torrents (hash, uid) VALUES (?, 0)', 's', $response[$x][0]);
	}

	if(!$settings['disable_sem'])
		sem_release($sem);
}

function torrent_exists($hash)
{
	global $rpc;

	$response = $rpc->request('d.get_hash', array($hash), false);

	if($response === false)
		return false;
	else
		return true;
}

?>
