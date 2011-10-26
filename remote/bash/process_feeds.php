<?php

DEFINE('LOGDEBUG',    1);
DEFINE('LOGERROR',    2);
DEFINE('LOGSECURITY', 4);
DEFINE('LOGADDDEL',   8);
DEFINE('LOGINFOS',    16);
DEFINE('LOGSETTINGS', 32);
DEFINE('LOGUSERS',    64);

DEFINE('TO_ROOT', '../');

if(!isset($_SERVER['argv']) || !count($_SERVER['argv']))
{
	header("Location: ".TO_ROOT.'index.php');
	exit;
}

ini_set('error_reporting',    E_ALL);
ini_set('max_execution_time', 0);

// Provide This Class for the whole error-Handling-Stuff
class smallRender
{
	function fatal($headline, $content) { echo "FATAL: $headline - $content\n\n"; exit; }
	function quit($message = '')        { echo "QUIT: $message\n";                exit; }
	function redirect($to)              { echo "Redirected to $to\n";             exit; }
}


// Arguments:
// 0: ProgName
// 1: To Root


$to_root = $argv[1];

require_once($to_root.'config.php');
require_once($to_root.'inc/sql/database.php');
require_once($to_root.'inc/sql/'.$sql['type'].'.php');
require_once($to_root.'inc/functions/base.fun.php');
require_once($to_root.'inc/functions/torrents.fun.php');
require_once($to_root.'inc/functions/add.fun.php');
require_once($to_root.'inc/defines/feeds.php');
require_once($to_root.'inc/functions/feeds.fun.php');
require_once($to_root.'inc/rpc.php');

$out = new smallRender();
$db  = new Database($sql);
$rpc = new RpcHandler($rpc_connect); 

logger(LOGDEBUG, 'process_feeds called', __FILE__, __LINE__);

/*
 * Try to get settings out of cache.
 * If not successfullt, get settings out of settingstable an write to cache
 */
if(!($settings = simple_cache_get('settings')))
{
	require_once($to_root.'inc/functions/settings.fun.php');
	$settings = get_and_rebuild_settings();
}

$result = $db->query('SELECT uid, dir FROM users');
$userDirs = array();
while($h = $db->fetch($result))
	$userDirs[$h['uid']] = stripslashes($h['dir']);


$result = $db->query('SELECT DISTINCT f.fid, f.url, f.interval, f.directory, f.uid, f.download, h.function FROM feeds f, highlightrules h WHERE f.uid = h.uid AND (h.fid = 0 OR h.fid = f.fid) AND h.function > 1');
while($h = $db->fetch($result))
{
	$id = $h['fid'];
	$_SESSION['uid'] = $h['uid'];
   $_SESSION['dir'] = $userDirs[$h['uid']];

	if(!cache_get("feed$id"))
	{
		if($items = fetchRss($h['url'], $id, intval($h['download']), $h['directory']))
			cache_put("feed$id", $items, $h['uid'], time() + $h['interval']);
	}
}


?>
