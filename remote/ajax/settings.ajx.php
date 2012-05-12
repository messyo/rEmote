<?php

define('TO_ROOT', '../');

require_once(TO_ROOT.'inc/global.php');

if(isset($_GET['which']))
{
	$result = $db->query('SELECT inputtype FROM settings WHERE skey = ?', 's', $_GET['which']);
	if(($h = $db->fetch($result)) && ($h['inputtype'] == 'bin'))
	{
		list(,$bin) = explode('_', $_GET['which'], 2);
		$which = exec('which '.escapeshellarg($bin));
		if($which != '')
			echo $which;
		else
			echo "ERROR: {$lng['nobinary']}";
	}
	else
		logger(LOGSECURITY, "User {$_SESSION['uid']} tried to inject \"{$_POST['which']}\"", __FILE__, __LINE__);
}
else if(isset($_GET['info']))
{
	require_once(TO_ROOT."languages/{$_SESSION['lng']}/settings.lng.php");

	$info = $_GET['info'];
	if(isset($lng["help_$info"]))
		echo $lng["help_$info"];
	else
		echo "ERROR: {$lng['noinfo']} for $info";
}

session_write_close();

?>
