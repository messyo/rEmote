<?php

class DatabaseFrame
{
	protected $pdo;
	protected $time;
	protected $num;
	protected $sql;
	protected $afrows;

	function __construct($sql)
	{
		global $out, $db_error, $db_errstr;

		try
		{
			$this->pdo = new PDO("{$sql['type']}:dbname={$sql['database']};host={$sql['host']}", $sql['user'], $sql['password']);
		}
		catch(PDOException $e)
		{
			if(isset($out) && is_object($out))
				$out->fatal('SQL-Error('.__LINE__.')', 'Connection Error', $e->getMessage());
			else
			{
				$db_error = true;
				$db_errstr = 'Connection Error: '.$e->getMessage();
			}
		}

		$this->time = 0.0;
		$this->num  = 0;
	}

	function __destruct()
	{
		$this->pdo = null;
	}

	function fatal($headline, $message)
	{
		global $out;

		if(isset($out) && is_object($out))
			$out->fatal($headline, $message);
		else
			exit($headline.'<br />'.$message);
	}


	function getParamType($char)
	{
		global $out;

		switch($char)
		{
			case 'i':
				return PDO::PARAM_INT;
			case 's':
			case 'd':
			case 'b':
				return PDO::PARAM_STR;
			default:
				$this->fatal('SQL-Error('.__LINE__.')', "Invalid Query-Param-Type: \"$char\"");
		}
	}

	function query($qry, $types = '')
	{
		global $out, $lng;

		try
		{
			$starttime = microtime(true);

			$argc = func_num_args();

			if(($argc > 1) && (strlen($types) != ($argc - 2)))
				$this->fatal('SQL-Error('.__LINE__.')', 'Invalid Query: number of types and number of given Arguments differ!');


			$stmnt = $this->pdo->prepare($qry);

			for($i = 2; $i < $argc; $i++)
			{
				$param = func_get_arg($i);
				$stmnt->bindValue($i - 1, $param, $this->getParamType($types[$i-2]));
			}


			$stmnt->execute();

			// Due to a Bug in
			// PDOStatement::execute()
			// this function does not return anything in some PHP-Versions
			// In Other Versions, true and false are inversed...
			//
			// So lets simply check on the error-code
			if($stmnt->errorCode() == '00000')
			{
				$this->afrows = $stmnt->rowCount();
				$this->time += microtime(true) - $starttime;
				$this->num++;

				return($stmnt);
			}
			else
			{
				$message = '';
				for($i = 0; $i < $argc; $i++)
					$message .= func_get_arg($i).'<br />';

				$this->error($message, $stmnt->errorCode(), $stmnt->errorInfo(), __LINE__);
			}
		}
		catch(Exception $e)
		{
			$this->fatal('SQL-Error('.__LINE__.')', $e->getMessage());
		}
	}

	function num_rows($res)
	{
		return($res->rowCount());
	}

	function affected_rows()
	{
		return($this->afrows);
	}

	function insert_id()
	{
		return($this->pdo->lastInsertId());
	}

	function error($text, $enum, $info, $line)
	{
		global $out;

		$this->fatal('SQL-Error', "(Line: $line):, On: $text<br /><br /><br />$enum: {$info[2]}");
	}

	function fetch($res)
	{
		return $res->fetch(PDO::FETCH_ASSOC);
	}

	function one_result($res, $key = false)
	{
		if(!($h = $this->fetch($res)))
			return false;

		if($key === false)
			return( count($h) ? current($h) : false );
		else
			return( isset($h[$key]) ? $h[$key] : false );
	}

	function get_time($decs = 6)
	{
		return(round($this->time, $decs));
	}

	function get_count()
	{
		return($this->num);
	}

	function out($str)
	{
   	return htmlspecialchars($str, ENT_QUOTES);
	}
}

?>
