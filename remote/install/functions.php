<?php

function get_rand($c)
{
	static $v = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabzdefghijklmnopqrstuvwxyz1234567890';
	$r = '';

	for($x = 0; $x < $c; $x++)
		$r .= $v{mt_rand(0, strlen($v) -1)};

	return($r);
}

function makeLanguageChoice()
{
	if(!is_dir(TO_ROOT.'languages'))
		return false;
	$d = opendir(TO_ROOT.'languages');
	while(($node = readdir($d)) !== false)
	{
		if($node == '.' || $node == '..')
			continue;
		if(is_file(TO_ROOT.'languages/'.$node.'/info.lng.php'))
			$ret[] = $node;
	}
	closedir($d);

	return $ret;
}

function postNotEmpty($key)
{
	return(isset($_POST[$key]) && (trim($_POST[$key]) != ''));
}

function makeRandomStr($len, $simple)
{
	$ressource = 'abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	if(!$simple)
		$ressource .= '_!"§$()[]+-.';

	$rlen = strlen($ressource) - 1;
	$str = '';
	for($x = 0; $x < $len; $x++)
		$str .= $ressource[mt_rand(0, $rlen)];

	return $str;
}

?>
