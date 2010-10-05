<?php

$starttime = microtime('true');

/***************************
 * DEFINE IMPORTANT VALUES *
 ***************************/

//LOGLEVELS
DEFINE('LOGDEBUG',    1);
DEFINE('LOGERROR',    2);
DEFINE('LOGSECURITY', 4);
DEFINE('LOGADDDEL',   8);
DEFINE('LOGINFOS',    16);
DEFINE('LOGSETTINGS', 32);
DEFINE('LOGUSERS',    64);

//USERCLASSES
DEFINE('GUEST',    0);
DEFINE('USER',     1);
DEFINE('ADMIN',    2);
DEFINE('SUADMIN',  3);

//VIEW/SOURCE/GROUP
$view_arr    = array('main', 'started', 'stopped', 'complete', 'incomplete', 'seeding');
$group_arr   = array('grpnone', 'tracker', 'status', 'message', 'traffic');
$refresh_arr = array('refoff', 'refwhole', 'refall', 'refsidebar' );
$source_arr  = array('private', 'public', 'both');


// What the hell?
// Why can't this be php default?
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(date_default_timezone_get());


require_once(TO_ROOT.'config.php');
require_once(TO_ROOT.'inc/render.php');
require_once(TO_ROOT.'inc/sql/database.php');
require_once(TO_ROOT.'inc/sql/'.$sql['type'].'.php');
require_once(TO_ROOT.'inc/functions/base.fun.php');
require_once(TO_ROOT.'inc/sessions.php');
require_once(TO_ROOT.'inc/rpc.php');

$out = new Render();
$db  = new Database($sql);
$rpc = new RpcHandler($rpc_connect);

$result = $rpc->simple_multicall('system.client_version',
											'system.library_version',
											'get_down_rate',
											'get_up_rate',
											'get_download_rate',
											'get_upload_rate');

$global['versions']['remote']     = '2.0.0-beta-1';
$global['versions']['rtorrent']   = $result[0][0];
$global['versions']['libtorrent'] = $result[1][0];
$global['downspeed']              = $result[2][0];
$global['upspeed']                = $result[3][0];
$global['downlimit']              = $result[4][0];
$global['uplimit']                = $result[5][0];

/*
 * Try to get settings out of cache.
 * If not successfull, get settings out of settingstable and write to cache
 */
if(!($settings = simple_cache_get('settings')))
{
	require_once(TO_ROOT.'inc/functions/settings.fun.php');
	$settings = get_and_rebuild_settings();
}

ini_set('error_reporting',          E_ALL);
ini_set('session.gc_divisor',       1000);// Let's set to a very high value... as Ubuntu/Debian do not call the php-Session-GC, we have to call it by ourselves anyway...
ini_set('session.gc_propability',   1);
ini_set('session.use_cookies',      $settings['session_use_cookies']);
ini_set('session.use_only_cookies', 0);
ini_set('max_execution_time',       $settings['max_exec_time']);

session_name($settings['session_name']);


// if(isset($_GET[session_name()]))
// 	session_id($_GET[session_name()]);
// else if(isset($_COOKIE[session_name()]))
// 	session_id($_COOKIE[session_name()]);

// So here is our own GC-Call
if(!defined('NO_GC') && mt_rand(1, SESSION_GC_DIVISOR) == SESSION_GC_DIVISOR)
{
	SessionHandler::gc($settings['session_lifetime']);
	// Also cleanup cache
	$db->query('DELETE FROM cache WHERE expires > 0 AND expires < ?', 'i', time());
}


// Strip MagicQuotes
if(get_magic_quotes_gpc())
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while(list($key, $val) = each($process))
	{
		foreach($val as $k => $v)
		{
			unset($process[$key][$k]);
			if(is_array($v))
			{
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			}
			else
			{
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}


session_set_cookie_params( $settings['session_lifetime'], $settings['cookie_path']);

session_start();


if(SID != '')
{
	$sid  = '&amp;'.SID;
	$qsid = '?'.SID;
}
else
{
	$sid  = '';
	$qsid = '';
}

if(isset($_GET['logout']))
	session_destroy();

$jsinfos = array();

/* Check if Logedin... if not make Login-Field and Die or take login */
if(!isset($_SESSION['status']) || $_SESSION['status'] < 1)
{
	if(TO_ROOT != './')
	{
		session_write_close();
		require_once(TO_ROOT."languages/{$settings['default_lng']}/base.lng.php");
		exit('ERROR: '.$lng['sexpired']);
	}
	else
	{
		require_once(TO_ROOT.'inc/login.php');
	}
}


/* Load the Style and language... if set by login already, variables will be overwritten */
if(!(@include(TO_ROOT."styles/{$_SESSION['style']}/style.php")))
{
	require(TO_ROOT."styles/{$settings['default_style']}/style.php");
	logger(LOGERROR, "Could not load {$_SESSION['style']}/style.php", __FILE__, __LINE__);
}
if(!(@include(TO_ROOT."languages/{$_SESSION['lng']}/base.lng.php")))
{
	require(TO_ROOT."languages/{$settings['default_lng']}/base.lng.php");
	logger(LOGERROR, "Could not load {$_SESSION['lng']}/base.lng.php", __FILE__, __LINE__);
}



$out->setStyleJavascripts($stylejs);
$out->setStylesheets($stylesheets);
$out->setPrecache($precache);

$out->jsinfos['imagedir'] = "'$imagedir'";
$out->jsinfos['sid']      = '"'.SID.'"';

//Administrator can view all Torrents
if(isset($_SESSION['status']) && ($_SESSION['status'] >= ADMIN))
	$source_arr[3] = 'all';
if($settings['real_multiuser'])
	$group_arr[5] = 'user';

?>
