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
// 2: JID


// functions
// zip
// tar
// rar
// tarbz2
// unzip
// untar
// unrar
// copy
// move

$to_root = $argv[1];
$jid     = $argv[2];

require_once($to_root.'config.php');
require_once($to_root.'inc/sql/database.php');
require_once($to_root.'inc/sql/'.$sql['type'].'.php');
require_once($to_root.'inc/functions/file.fun.php');
require_once($to_root.'inc/functions/base.fun.php');

$out = new smallRender();
$db  = new Database($sql);

/*
 * Try to get settings out of cache.
 * If not successfullt, get settings out of settingstable an write to cache
 */
if(!($settings = simple_cache_get('settings')))
{
	require_once($to_root.'inc/functions/settings.fun.php');
	$settings = get_and_rebuild_settings();
}

if(!is_numeric($jid))
{
	logger(LOGERROR | LOGSECURITY, "Invalid JID ($jid) by User {$session['uid']}", __FILE__, __LINE__."\n");
	exit("Invalid JID\n");
}

$timeout   = time() + 5;

$result = $db->query("SELECT uid, pid, function, file1, file2, options FROM jobs WHERE jid = ?", 'i', $jid);

if($db->num_rows($result) == 0)
	fatal("No Job found, jid: $jid", __FILE__, __LINE__."\n");

$h = $db->fetch($result);

$file1   = $h['file1'];
$file2   = $h['file2'];
$options = explode(" ", $h['options']);

$success = false;

switch($h['function'])
{
	case 'move':
		$success = rename($file1, $file2);
		logger(LOGDEBUG, "Renaming $file1 > $file2", __FILE__, __LINE__);
		break;
	case 'copy':
		$success = fullcopy($file1, $file2);
		break;
	case 'unzip':
		if(($command = getBin('unzip')) === false)
			break;
		shell_exec(sprintf('%s %s %s -qq -d %s',
						$command,
						$settings['overwriteunpack'] ? ' -o' : '-n',
						escapeshellarg($file1),
						escapeshellarg($file2)));
		$success = true;
		break;
	case 'unrar':
		if(($command = getBin('unrar')) === false)
			break;
		$re = shell_exec(sprintf('%s x -o%s -inull %s %s > /dev/null',
						$command,
						$settings['overwriteunpack'] ? '+' : '-',
						escapeshellarg($file1),
						escapeshellarg($file2)));
		if(strlen(trim($re)))
			$success = false;
		else
			$success = true;
		break;
	case 'untar':
		if(($command = getBin('tar')) === false)
			break;
		shell_exec(sprintf('%s -xf%s %s %s',
						$command,
						$settings['overwriteunpack'] ? ' --overwrite' : 'k',
						escapeshellarg($file1),
						escapeshellarg($file2)));
		$success = true;
		break;
	case 'archtar':
		if(($command = getBin('tar')) === false)
			break;
		$dir = getcwd();
		$rdir = realdirname($file2);
		chdir($rdir);
		shell_exec(sprintf('%s -cf %s %s',
						$command,
						escapeshellarg($file1),
						escapeshellarg(substr($file2, strlen($rdir)))));
		chdir($dir);
		$success = is_file($file1);
		break;
	case 'archtarbz2':
		if(($command  = getBin('tar')) === false)
			break;
		if(($command2 = getBin('bzip2')) === false)
			break;
		$dir = getcwd();
		$rdir = realdirname($file2);
		chdir($rdir);
		shell_exec(sprintf('%s -cf - %s | %s > %s',
						$command,
						escapeshellarg(substr($file2, strlen($rdir))),
						$command2,
						escapeshellarg($file1)));
		chdir($dir);
		$success = is_file($file1);
		break;
	case 'archzip':
		if(($command = getBin('zip')) === false)
			break;
		$dir = getcwd();
		$rdir = realdirname($file2);
		chdir($rdir);
		shell_exec(sprintf('%s -0rq %s %s',
						$command,
						escapeshellarg($file1),
						escapeshellarg(substr($file2, strlen($rdir)))));
		chdir($dir);
		$success = is_file($file1);
		break;
	case 'archrar':
		if(($command = getBin('rar')) === false)
			break;
		$dir = getcwd();
		$rdir = realdirname($file2);
		chdir($rdir);
		shell_exec(sprintf('%s a %s %s',
						$command,
						escapeshellarg($file1),
						escapeshellarg(substr($file2, strlen($rdir)))));
		chdir($dir);
		$success = is_file($file1);
		break;
}

if($success)
	updateStatus($jid, 'complete', time());
else
	updateStatus($jid, 'error', time());


?>
