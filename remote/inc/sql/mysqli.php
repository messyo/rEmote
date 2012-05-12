<?php

class Database
{
	protected $mysli;
	protected $time;
	protected $num;
	protected $cleaner;
	protected $sql;
	protected $count;
	protected $sqlite;

	function __construct($sql)
	{
		global $out;

		$this->mysqli = new mysqli($sql['host'], $sql['user'], $['password']);
		if(mysqli_connect_error() || !$this->mysqli)
			$out->fatal('Connection Error', mysqli_connect_errno() . ': ' . mysqli_connect_error());
		if(!$this->mysqli->select_db($sql['database']))
			$out->fatal('Connection Error', "Could not use Database \"{$sql['database']}\"");

		$this->time = 0.0;
		$this->num  = 0;
	}

	function __destruct()
	{
		$this->mysqli->close();
	}

	function query($qry, &$numrows, $types)
	{
		global $out, $lng;

		$starttime = microtime(true);
		$argc = func_num_args();


		$stmnt = $this->mysqli->prepare($qry);

		for($i = 2; $i < $argc; $i++)
			$stmnt->bindValue($types[$i - 2], func_get_arg($i));

		$stmnt->execute();

		if($stmnt)
		{
			$this->time += microtime(true) - $starttime;
			$this->num++;
			if($numrows)
				$numrows = $stmnt->num_rows();

			return($stmnt);
		}
		else
		{
			$message = '';
			for($i = 0; $i < $argc; $i++)
				$message .= func_get_arg($i).'<br />';
			
			$this->error($message);
		}
	}

	function num_rows($res)
	{
		// DON'T USE THIS FUNCTION ANYMORE
		// Instead, query gets a reference parameter, to wich num_rows are written

		logger(LOGDEBUG, 'Use of deprecated Function "num_rows"', __FILE__, __LINE__);
		return($res->num_rows);
	}

	function affected_rows($res)
	{
		$res->num_rows();
	}

	function insert_id()
	{
		return($this->mysqli->insert_id());
	}

	function error($text)
	{
		global $out;

		$out->fatal('SQL-ERROR:', "On: $text<br /><br />".$this->mysqli->errno().': '.$this->mysqli->error());
	}

	function fetch($res)
	{
		return $res->fetch();
	}

	function one_result($res, $key)
	{
		if(($h = $this->fetch($res)) && isset($h[$key]))
			return($h[$key]);
		else
			return(false);
	}

	function get_time($decs = 6)
	{
		return(round($this->time, $decs));
	}

	function get_count()
	{
		return($this->num);
	}
}

?>
