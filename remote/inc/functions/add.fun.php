<?php
//
// ===========================================
// ======== Part of the rEmote-WebUI =========
// ===========================================
//
// Contains torrentadd functions
//
function get_cookies($host)
{
	global $db;

	$result = $db->query('SELECT cookies FROM cookies WHERE uid = ? AND host = ?', 'is',
											$_SESSION['uid'],
											$host);
	if($data = $db->fetch($result))
		return($data['cookies']);
	return('');
}

function add_single_torrent($file, $action, $public, $delete = true)
{
	global $settings;

	if(!$settings['disable_sem'])
	{
		$sem = sem_get(SEM_KEY);
		if(!sem_acquire($sem))
			fatal("Could not acquire Semaphore!", __FILE__, __LINE__);
	}

	set_directory($_SESSION['dir']);

	$return = add_torrent($file, $action, $public, $delete);

	if(!$settings['disable_sem'])
		sem_release($sem);

	return $return;
}

function add_torrent($file, $action, $public, $delete = true)
{
	global $lng, $settings, $db, $rpc;

	if(($settings['max_torrent_size'] > 0) && (filesize($file) > $settings['max_torrent_size']))
	{
		logger(LOGERROR, "User {$_SESSION['uid']} tried to insert a Torrentfile larger than the allowed maximum size.", __FILE__, __LINE__);
		return $lng['torrent2big'];
	}

	if(($hash = quick_get_hash($file)) === false)
		return($lng['invalidfile']);

	if(torrent_exists($hash))
		return($lng['torrexists']);
	$rpc->request($action, $file);
	if(!($time = filesize($file)))
		$time = SLEEP_AFTER_TORRENT_LOAD;
	usleep($time + 10000);

	if($delete)
		unlink($file);

	if(!torrent_exists($hash))
	{
		logger(LOGERROR, "Torrent $file could not be added", __FILE__, __LINE__);
		return($lng['notadded']);
	}

	if($settings['real_multiuser'])
	{
		if($public)
			$uid = 0;
		else
			$uid = $_SESSION['uid'];
		$db->query('INSERT INTO torrents (hash, uid) VALUES (?, ?)', 'si', $hash, $uid); // In this case, hash can be insertet whithout escaping, as hash comes directly from quick_get_hash
	}
	logger(LOGADDDEL, "File '$file' added", __FILE__, __LINE__);
	return('');
}

function get_torrent($url, $public, $start = false)
{
	global $settings, $lng;

	$action = 'load';
	if($start)
		$action .= '_start';

	if(($pieces = parse_url($url)) === false || !isset($pieces['host']) || !isset($pieces['path']))
		return($lng['addinvurl']);

	if(($wget = getBin('wget')) === false)
		return($lng['inernerror']);

	$host = $pieces['host'];
	$filename = basename($pieces['path']);
	$cookies = get_cookies($host);

	if($cookies != '')
		// Download via WGET and import as local Torrent
		$command = sprintf('%s -q --header=%s %s --no-check-certificate -O %s',
								$wget,
								escapeshellarg("Cookie: $cookies"),
								escapeshellarg($url),
								escapeshellarg($settings['tmpdir'].$filename));
	else
		$command = sprintf('%s %s -q --no-check-certificate -O %s',
								$wget,
								escapeshellarg($url),
								escapeshellarg($settings['tmpdir'].$filename));

	shell_exec($command);
	if(is_file($settings['tmpdir'].$filename))
		return(add_torrent($settings['tmpdir'].$filename, $action, $public));
	else
	{
		logger(LOGERROR, "File '$filename' could not be downloaded", __FILE__, __LINE__);
		return($lng['downloaderr']);
	}
}

function add_file($tmpname, $filename, $public, $start=0)
{
	global $settings;

	$action = 'load';
	if($start)
		$action .= '_start';

	logger(LOGADDDEL | LOGDEBUG, "Tried to add file '$filename'", __FILE__, __LINE__);

	if(@copy($tmpname, $settings['tmpdir'].$filename))
		return(add_torrent($settings['tmpdir'].$filename, $action, $public));
	else
		return($lng['torrnotfound']);
}

?>
