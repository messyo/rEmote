<?php

define('TO_ROOT', '../');

require_once(TO_ROOT.'inc/global.php');


if($_SESSION['lastjcheck'] > 0)
{
	if(($finished = finishedJobs()) > 0)
	{
		if(($remaining = remainingJobs()) > 0)
		{
			$_SESSION['lastjcheck'] = time();
			echo '1';
		}
		else
		{
			$_SESSION['lastjcheck'] = 0;
			echo '0';
		}

		echo lng('jobsfinished', $finished, $remaining);
	}
	else
	{
		$_SESSION['lastjcheck'] = time();
		echo '1';
	}
}
else
	echo '0';

session_write_close();

?>
