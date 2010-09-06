<?php


// Determines the ending of a List or Dictionary in a Torrent-File
function getEndOfList($start, &$file)
{
	$offset = $start;
	$len = strlen($file);

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
	/* Greetz fly out to wtorrent ;-)
	 * This function is x-times faster than using any bencode-library :-P
	 * ;-)
	 */

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
