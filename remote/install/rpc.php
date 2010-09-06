<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$out = "<h1>{$lng['checkrt']}</h1>";

if((strtolower(substr($rpc_connect, -5)) == '/rpc2' ||
	strtolower(substr($rpc_connect, -4)) == '/rpc')  &&
	!strpos('@', $rpc_connect))
{
	$out .= "<div class=\"error\">{$lng['insecurerpc']}</div>";
	$rand  = get_rand(32);
	$out .= '<div>'.lng('insechint', "http://localhost/$rand", "SCGIMount /$rand 127.0.0.1:5000").'</div>';
}

$request = xmlrpc_encode_request('system.client_version', array());
$context = stream_context_create(array('http' => array('method' => 'POST',
																		'header'  => 'Content-Type: text/xml',
																		'content' => $request)));
if(($file = file_get_contents($rpc_connect, false, $context)) === false)
{
	$out .= "<div class=\"error\">{$lng['noconnect']}</div>";
	$success = false;
}
$file = str_replace("i8","double",$file);
$response = xmlrpc_decode($file);

if(!is_string($response) || $response == '')
{
	$out .= "<div class=\"error\">{$lng['noconnect']}</div>";
	$success = false;
}

if(version_compare($response, MIN_RT) < 0)
{
	$out .= "<div class=\"error\">".lng('rtwrongvers', MIN_RT)."</div>";
	$success = false;
}
else
	$out .= '<div class="success">'.lng('rtfound', $response).'</div>';

if(!$success)
	$retry_possible = true;

?>
