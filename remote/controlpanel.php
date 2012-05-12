<?php

define('TO_ROOT', './');
define('ACTIVE', 'cp');

require_once('inc/global.php');
require_once('inc/header.php');

if(!(@include(TO_ROOT."languages/{$_SESSION['lng']}/controlpanel.lng.php")))
{
	require_once(TO_ROOT."languages/{$settings['default_lng']}/controlpanel.lng.php");
	logger(LOGERROR, "Could not load {$_SESSION['lng']}/controlpanel.lng.php", __FILE__, __LINE__);
}

function makeSecQuery($text, $mod, $vals)
{
	global $sid;
	return makeSecQuestion("controlpanel.php?mod=$mod$sid", $text, $vals);
}


$mods = array(
	0 => array('name' => $lng['cpinfo'],     'perm' => 0, 'file' => 'infos.php'),
	1 => array('name' => $lng['cpcookies'],  'perm' => 0, 'file' => 'cookies.php'),
	2 => array('name' => $lng['cpfeeds'],    'perm' => 0, 'file' => 'feeds.php'),
	3 => array('name' => $lng['cpmycp'],     'perm' => 0, 'file' => 'my.php'),
	4 => array('name' => $lng['cpsettings'], 'perm' => 1, 'file' => 'settings.php'),
	5 => array('name' => $lng['cpusers'],    'perm' => 1, 'file' => 'users.php'),
	6 => array('name' => $lng['cplog'],      'perm' => 2, 'file' => 'log.php'),
	7 => array('name' => $lng['cpxmlrpc'],   'perm' => 2, 'file' => 'xmlrpc.php')
);

$perm = 0;
if($_SESSION['status'] > 1)
	$perm++;
if($_SESSION['status'] > 2)
	$perm++;

$mod = 0;

if(isset($_GET['mod']))
{
	$mod = trim($_GET['mod']);
	if(!isset($mods[$mod]) || $mods[$mod]['perm'] > $perm)
		$mod = 0;
}

define('IN_CP', true);

require_once(TO_ROOT."inc/cp/{$mods[$mod]['file']}");

$cpmenu = '<ul class="tabs">';
foreach($mods as $key => $m)
{
	if($m['perm'] <= $perm)
		$cpmenu .= '<li class="'.($mod == $key ? 'viewselon' : 'viewseloff')."\"><a href=\"controlpanel.php?mod=$key$sid\">{$m['name']}</a></li>";
}
$cpmenu .= '</ul>';

if(addJobChecker())
	$m = $out->getMessages();
else
	$m = '';
$out->content = "<div id=\"main\">$header<div id=\"content\">$cpmenu<div class=\"tabsbody\" id=\"cpcontent\">$cpout</div></div></div>";

$out->renderPage($settings['html_title']);

?>
