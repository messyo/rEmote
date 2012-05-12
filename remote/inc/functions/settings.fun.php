<?php

function process_settings()
{
	global $db;

	$s = array();
	$result = $db->query('SELECT skey, value, inputtype FROM settings');
	while($h = $db->fetch($result))
	{
		if(substr($h['inputtype'], 0, 3) == 'php')
			list(, $datatype,) = explode('|', $h['inputtype'], 3);  /* inputtype is in Format 'php|$DATATYPE|$PHPCODE' */
		else
			$datatype = $h['inputtype'];
		switch($datatype)
		{
			case 'char':
			case 'txt':
			case 'dir':
			case 'bin':
				$s[$h['skey']] = $h['value'];
				break;
			case 'int':
			case 'yn':
				$s[$h['skey']] = intval($h['value']);
				break;
			case 'float':
				$s[$h['skey']] = floatval($h['value']);
				break;
			default:
				/* Should not occure. If occured, treat like a txt */
				$s[$h['skey']] = $h['value'];
				break;
		}
	}


	return $s;
}

function get_and_rebuild_settings()
{
	$s = process_settings();
	cache_put('settings', $s);

	return($s);
}

function applyValue($key, $value)
{
	// No need for Injection-Protection here, no User-Input expected
	$db->query("UPDATE settings SET $key = ?", 's', $val);
}

?>
