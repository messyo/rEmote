<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$sqls = array(
	'mysql',
	'mysql_old',
	'sqlite',
	'pgsql',
	'oci'
);
$apachemods = array(
	'mod_scgi' => 1
);
$phpextsr    = array(
	'xmlrpc'     => 1,
	'gd'         => 1,
	'xml'        => 1,
	'sysvsem'    => 0,
	'session'    => 1,
	'PDO'        => 0,
	'pdo_mysql'  => 0,
	'pdo_pgsql'  => 0,
	'pdo_sqlite' => 0,
	'pdo_oci'    => 0,
	'mysql'      => 0,
);
$inireqs = array(
	'safe_mode'    => 0,
	'allow_url_fopen' => 1,
//	'open_basedir' => 0,
);

if(!in_array($sql['type'], $sqls))
{
	$out = "<div class=\"error\">{$lng['dbinvalid']}</div>";
	$success = false;
}
else
{
	if($sql['type'] == 'mysql_old')
		$phpextsr['mysql'] = 1;
	else
	{
		$phpextsr['PDO'] = 1;
		$phpextsr['pdo_'.$sql['type']] = 1;
	}
	$out = "<h1>{$lng['reqs']}</h1>";

	$out .= "<table id=\"reqs\">";
	$out .= "<tr class=\"head\"><td>{$lng['req']}</td><td>{$lng['reqd']}</td><td>{$lng['curr']}</td><td>&nbsp;</td></tr>";

	// APACHE STUFF
	if(function_exists('apache_get_modules'))
	{
		$apamods = apache_get_modules();
		foreach($apachemods as $k => $m)
		{
			$passed = true;
			if($m == 1)
				$reqd = $lng['yes'];
			else
				$reqd = $lng['no'];
			if(in_array($k, $apamods))
			{
				$curr = $lng['yes'];
			}
			else
			{
				$curr = $lng['no'];
				if($m == 1)
					$passed = false;
			}

			if($passed)
				$led = 'green';
			else
			{
				$led = 'red';
				$success = false;
			}

			$out .= "<tr><td>{$lng[$k]}</td><td>$reqd</td><td>$curr</td><td><img src=\"images/led$led.png\" /></td></tr>";
		}
	}
	$phpexts = get_loaded_extensions();
	foreach($phpextsr as $k => $m)
	{
		$passed = true;
		if($m == 1)
			$reqd = $lng['yes'];
		else
			$reqd = $lng['no'];
		if(in_array($k, $phpexts))
		{
			$curr = $lng['yes'];
		}
		else
		{
			$curr = $lng['no'];
			if($m == 1)
				$passed = false;
		}

		if($passed)
			$led = 'green';
		else
		{
			$led = 'red';
			$success = false;
		}

		$out .= "<tr><td>{$lng[$k]}</td><td>$reqd</td><td>$curr</td><td><img src=\"images/led$led.png\" /></td></tr>";
	}


	foreach($inireqs as $k => $m)
	{
		if(intval(ini_get($k)))
			$curr = $lng['on'];
		else
			$curr = $lng['off'];

		if($m)
			$reqd = $lng['on'];
		else
			$reqd = $lng['off'];

		if(intval(ini_get($k)) == $m)
			$passed = true;
		else
			$passed = false;

		if($passed)
			$led = 'green';
		else
		{
			$led = 'red';
			$success = false;
		}
		$out .= "<tr><td>{$lng[$k]}</td><td>$reqd</td><td>$curr</td><td><img src=\"images/led$led.png\" /></td></tr>";
	}


	$out .= "</table>";
}


if(!$success)
	$retry_possible = true;

?>
