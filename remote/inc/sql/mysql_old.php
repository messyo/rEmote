<?php

class Database extends DatabaseFrame
{
	protected $link;

	function __construct($sql)
	{
		global $out, $db_error, $db_errstr;

		$this->link = @mysql_connect($sql['host'], $sql['user'], $sql['password']);
		if(!$this->link || !mysql_select_db($sql['database'], $this->link))
		{
			if(isset($out) && is_object($out))
				$out->fatal('SQL-Error('.__LINE__.')', 'Connection Error', mysql_error());
			else
			{
				$db_error = true;
				$db_errstr = 'Connection Error: '.mysql_error();
			}

		}
		$this->time = 0.0;
		$this->num = 0;
	}

	function __destruct()
	{
		 mysql_close($this->link);
	}

	function query($qry, $types = '')
	{
		global $out;

		$starttime = microtime(true);

		$argc = func_num_args();
		if(($argc > 1) && (strlen($types) != ($argc - 2)))
			$this->fatal('Invalid Query', 'Number of types and number of given Arguments differ!<br>'.$qry);

		if($argc > 2 && strpos($qry, '?'))
		{
			$qry_parts = explode('?', $qry);
			$qry = $qry_parts[0];
			for($i = 2; $i < $argc; $i++)
			{
				switch($types[$i-2])
				{
					case 'd':
					case 'i':
							$qry .= func_get_arg($i).$qry_parts[$i - 1];
						break;
					case 's':
					case 'b':
					default:
							$a = func_get_arg($i);
							$qry .= '\''.$this->escape_string($a).'\''.$qry_parts[$i - 1];
						break;
				}
			}
		}

		if($result = mysql_query($qry, $this->link))
		{
			$this->time += microtime(true) - $starttime;
			$this->num++;
			return($result);
		}
		else
			$this->error($qry, mysql_errno($this->link), mysql_error($this->link), __LINE__);
	}
	
	function affected_rows()
	{
		return mysql_affected_rows($this->link);
	}

	function num_rows($res)
	{
		return(mysql_num_rows($res));
	}

	function insert_id()
	{
		return(mysql_insert_id($this->link));
	}

	function escape_string($string)
	{
		return(mysql_real_escape_string($string));
	}

	function fetch($res)
	{
		$arr = mysql_fetch_assoc($res);
		
		if(is_array($arr) && count($arr))
		{
			foreach($arr as $key => $val)
				$arr[$key] = stripslashes($val);
		}

		return $arr;
	}
	
}

?>
