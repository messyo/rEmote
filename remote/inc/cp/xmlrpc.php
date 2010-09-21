<?php

if(!defined('IN_CP') || $_SESSION['status'] < 2)
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

$methods = $rpc->request('system.listMethods', array());

if(isset($_GET['c']))
	$command = $_GET['c'];
else
	$command = false;

if($command !== false && array_search($command, $methods) === false)
{
	$command = false;
	logger(LOGSECURITY, "Manipulated xmlrpc-string by User {$_SESSION['uid']}", __FILE__, __LINE__);
}

$cpout = '';


$m = '<div class="notify">';
if($command !== false)
{
	$result = $rpc->multicall('system.methodHelp', array($command),
										'system.methodSignature', array($command));

	$help = htmlspecialchars($result[0][0], ENT_QUOTES);
	$m .= "<div id=\"xmlrpchelp\">$help</div>";
	$m .= '<div id="xmlrpcsignature">'.count($result[1][0][0])." {$lng['parameters']}<ul>";
	if(is_array($result[1][0][0]))
	{
		foreach($result[1][0][0] as $p)
			$m .= "<li>$p</li>";
	}	
	$m .= '</ul></div>';
}
else
	$m .= $lng['xmlfinfo'];
$m .= '</div>';

$cpout .= $m;

$m = "<fieldset class=\"box\" id=\"xmlrpccommandlist\"><legend>{$lng['commandlist']}</legend><ul class=\"xmlrpcblock\">";
$x = 0;
foreach($methods as $method)
{
	if($command === $method)
		$m .= "<li class=\"active\"><a href=\"controlpanel.php?mod=$mod&amp;c=$method$sid\" style=\"color: black; text-decoration: none;\">$method</a></li>";
	else
		$m .= "<li><a href=\"controlpanel.php?mod=$mod&amp;c=$method$sid\" style=\"color: black; text-decoration: none;\">$method</a></li>";
	if(++$x > 20)
	{
		$x = 0;
		$m .= '</ul><ul class="xmlrpcblock">';
	}
}
$m .= '</ul></fieldset>';

$cpout .= $m;

?>
