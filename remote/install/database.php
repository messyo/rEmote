<?php

if(!defined('IN_INSTALL'))
	exit("Not in Install");

$sqls = array('mysql', 'mysql_old', 'sqlite', 'pgsql', 'oci');

$out = "<h1>{$lng['database']}</h1>";

$complete = true;

if(!isset($sql['type']) || $sql['type'] == '' || !in_array($sql['type'], $sqls))
{
	$out .= "<div class=\"error\">{$lng['dbinvalid']}</div>";
	$success = false;
}
else
{
	if(!isset($sql['database']) || $sql['database'] == '')
		$complete = false;
	if($sql['type'] != 'sqlite')
	{
		if(!isset($sql['host']) || $sql['host'] == '')
			$complete = false;
		if(!isset($sql['user']) || $sql['user'] == '')
			$complete = false;
		if(!isset($sql['password']) || $sql['password'] == '')
			$complete = false;
	}
	if(!$complete)
	{
		$success = false;
		$out .= "<div class=\"error\">{$lng['incomplete']}</div>";
	}
	else
	{
		if(!isset($_SESSION['database']) || !$_SESSION['database'])
		{
			require_once(TO_ROOT.'inc/sql/database.php');
			require_once(TO_ROOT.'inc/sql/'.$sql['type'].'.php');
		}
		if($sql['type'] != 'mysql_old')
		{
			$c = true;
			try
			{
				new PDO("{$sql['type']}:dbname={$sql['database']};host={$sql['host']}", $sql['user'], $sql['password']);
			}
			catch(PDOException $e)
			{
				$c = false;
				$db_errstr = $e->getMessage();
			}
		}
		else
		{
			if(mysql_connect($sql['host'], $sql['user'], $sql['password']) && mysql_select_db($sql['database']))
				$c = true;
			else
			{
				$c = false;
				$db_errstr = mysql_error();
			}
		}

		if($c)
		{
			$out .= "<div class=\"success\">{$lng['connected']}</div>";
		}
		else
		{
			$params  = lng('dbtype', $sql['type']);
			$params .= '<br />'.lng('dbdb', $sql['database']);
			if($sql['type'] != 'sqlite')
			{
				$params .= '<br />'.lng('dbhost', $sql['host']);
				$params .= '<br />'.lng('dbuser', $sql['user']);
				$params .= '<br />'.lng('dbpass', $sql['password']);
			}
			$out .= "<div class=\"error\">".lng('couldntcon', $params, $db_errstr)."</div>";
			$success = false;
		}
	}
}

if($success)
{
	// Lookup Tables
	if(($tables = @file_get_contents('database.txt')) === false)
		$out .= "<div class=\"error\">".lng('couldntread', 'databasdatabase')."</div>";
	else
	{
		$db = new Database($sql);
		if(isset($db_error) && $db_error)
		{
			$out .= "<div class=\"error\">$db_errstr</div>";
			$success = false;
		}
		else
		{
			$details = '';

			$res = $db->query('SHOW TABLES');
			while($h = $db->fetch($res))
			{
				$name = $h["Tables_in_{$sql['database']}"];
				$t[$name] = array();
				$res2 = $db->query("SHOW COLUMNS FROM $name");
				while($i = $db->fetch($res2))
					$t[$name][] = $i['Field'];

			}
			$tl = explode('[', $tables);
			foreach($tl as $l)
			{
				$l = trim($l);
				if(strlen($l))
				{
					list($name, $cols) = explode(']', $l);
					$d = explode("\n", $cols);
					if(isset($t[$name]))
					{
						// Check Collums
						foreach($d as $p)
						{

							$p = trim($p);
							if(!strlen($p))
								continue;
							$tmparr = explode('|', $p);
							if(count($tmparr) < 3)
								echo "=====  $p  =====<br />\n";
							list($nm, $tp, $ar) = $tmparr;
							  
							if(!in_array($nm, $t[$name]))
							{
								$details .= "<br />Trying to Alter $name to add $nm<br>\n";
								$db->query("ALTER TABLE $name ADD $nm $tp NOT NULL");
							}
						}
					}
					else
					{
						//Create Table
						$details .= "<br />Trying to Create Table $name";
						$primk = '';
						$first = true;
						$sql = "CREATE TABLE $name (";
						foreach($d as $p)
						{
							if(!strlen($p))
								continue;
							if(!$first)
								$sql .= ', ';
							else
								$first = false;
							$p = trim($p);
							list($nm, $tp, $ar) = explode('|', $p);
							$sql .= "`$nm` $tp NOT NULL";
							if(strlen($ar))
							{
								$args = explode(',', $ar);
								foreach($args as $a)
								{
									switch($a)
									{
										case 'PK':
											if($primk == '')
												$primk  = "`$nm`";
											else
												$primk .= ", `$nm`";
											break;
										case 'AI':
											$sql .= ' AUTO_INCREMENT';
											break;
										default:
											break;
									}
								}
							}
						}
						if(strlen($primk))
						{
							$sql .= ", PRIMARY KEY ($primk)";
						}
						$sql .= ')';
						$db->query($sql);
					}
				}
			}
			if($details != '')
				$out .= "<div class=\"small\">$details</div>";
			$out .= "<div class=\"success\">{$lng['uptodate']}</div>";
			$db->query('TRUNCATE TABLE sessions');
		}
	}
}

if(!$success)
	$retry_possible = true;
else
	$_SESSION['database'] = true;

?>
