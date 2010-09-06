<?php

class PDOSQLiteStatement extends PDOStatement
{
	private rowcount;

	function set_rowcount($number)
	{
		$this->rowcount = $number;
	}

	function get_rowcount()
	{
		return $this->rowcount;
	}
}

class PDOSQLite extends PDO
{
	public function __construct($dsn)
	{
		parent::__construct($dsn, null, null, array());
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOSQLiteStatement', array($this)));
	}
}

class Database extends DatabaseFrame
{
	function __construct($sql)
	{
		global $out, $db_error, $db_errstr;

		try
		{
			$this->pdo = new PDOSQLite('sqlite:'.$sql['database']);
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
	}

	function num_rows($res)
	{
		return($res->get_rowcount());
	}

	function replace_with_string(&$qry)
	{
		$parts = explode('?', $qry);
		$qry = $parts[0];
		$c = count($parts);
		for($i = 1; $i < $c; $i++)
			$qry .= ":nr$i{$parts[$i]}";
	}

	function query($qry, $types = '')
	{
		global $out, $lng;

		try
		{
			$starttime = microtime(true);

			$argc = func_num_args();
			if(strlen($types) != ($argc - 2))
				$this->fatal('Invalid Query: number of types and number of given Arguments differ!');

			$this->replace_with_string($qry);

			$pattern  = '/SELECT .* FROM (.*) (WHERE .*)$/';
			$pattern2 = '/UPDATE (.*) SET .* (WHERE .*)$/';

			$count = 0;
			$count_qry = preg_replace($pattern,  'SELECT COUNT (*) as c FROM \\1 \\2', $qry, $count);
			if($count == 0)
				$count_qry = preg_replace($pattern2, 'SELECT COUNT (*) as c FROM \\1 \\2', $qry, $count);

			$stmnt = $this->pdo->prepare($qry);

			if($count)
			{
				$c_stmnt = $this->pdo->prepare($count_qry);

				for($i = 2; $i < $argc; $i++)
				{
					$param = func_get_arg($i);
					$c_stmnt->bindValue(':nr'.($i - 1), $param, $this->getParamType($types[$i-2]));
					// To not have to go through all the parameters again, set them for Main-Query
					$stmnt->bindValue(':nr'.($i - 1), $param, $this->getParamType($types[$i-2]));
				}

				$c_stmnt->execute();
				if($c_stmnt != '00000')
					$this->error('Count-Error:', $count_qry);
				$this->num++;
				$stmnt->set_rowcount($this->one_result($c_stmnt));
			}
			else
			{
				for($i = 2; $i < $argc; $i++)
				{
					$param = func_get_arg($i);
					$stmnt->bindValue(':nr'.($i - 1), $param, $this->getParamType($types[$i-2]));
				}
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
				$this->time += microtime(true) - $starttime;
				$this->num++;
				$this->afrows = $stmnt->rowCount()

				return($stmnt);
			}
			else
			{
				$message = '';
				for($i = 0; $i < $argc; $i++)
					$message .= func_get_args($i).'<br />';

				$this->error($message);
			}
		}
		catch(Exception $e)
		{
			$this->fatal($e->getMessage());
		}
	}
}

?>
