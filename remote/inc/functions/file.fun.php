<?php
//
// ===========================================
// ======== Part of the rEmote-WebUI =========
// ===========================================
//
// Contains filebrowser functions
//

function get_icon($ending)
{
	switch($ending)
	{
		case '.avi':
		case 'mpeg':
		case '.mpg':
		case 'divx':
		case '.mkv':
		case '.ogm':
		case '.mp4':
		case '.wmv':
		case '.vob':
		case '.flv':
			$img = 'movie.png';
			break;
		case '.pdf':
		case '.dvi':
			$img = 'acroread.png';
			break;
		case '.doc':
		case 'docx':
		case '.xls':
		case 'xlsx':
		case '.pps':
		case 'ppsx':
		case '.odt':
		case '.odp':
		case '.odx':
			$img = 'office.png';
			break;
		case '.mp3':
		case '.wma':
		case '.aac':
		case '.ogg':
		case 'flac':
			$img = 'audio.png';
			break;
		case '.mov':
		case '.3gp':
			$img = 'quicktime.png';
			break;
		case 'html':
		case '.htm':
		case '.php':
		case '.cgi':
			$img = 'html.png';
			break;
		case '.jpg':
		case 'jpeg':
		case '.png':
		case '.bmp':
		case 'tiff':
		case '.xpm':
		case '.gif':
		case '.ico':
			$img = 'image.png';
			break;
		case '.nfo':
		case '.sfv':
			$img = 'info.png';
		break;
		case '.tar':
			$img ='tar.png';
			break;
		case '.tgz':
		case 'r.gz':
		case '.bz2':
		case '.rar':
		case '.zip':
			$img = 'tgz.png';
			break;
		case '.iso':
		case '.mdf':
		case '.mds':
		case '.ccd':
		case '.bin':
		case '.cue':
		case '.nrg':
		case '.vcd':
			$img = 'cdrom.png';
			break;
		default:
			$img = 'file.png';
			break;
	}
	return($img);
}

function full_copy( $source, $target )
{
	if(is_dir($source))
	{
		@mkdir($target);
		$success = true;
		$d = dir($source);

		while(false !== ($entry = $d->read()))
		{
			if($entry == '.' || $entry == '..')
				continue;

			$Entry = "$source/$entry";
			if(is_dir($Entry))
				$success &= full_copy( $Entry, $target . '/' . $entry );
			else
				$success &= copy( $Entry, $target . '/' . $entry );
		}

		$d->close();
		return($success);
	}
	else
		return(copy($source, $target));
}

function make_permission_table($file)
{
	global $imagedir, $lng;
	
	$no  = "<img src=\"{$imagedir}button_cancel.png\" alt=\"{$lng['no']}\" />";
	$yes = "<img src=\"{$imagedir}button_ok.png\" alt=\"{$lng['yes']}\" />";
	
	$p = fileperms($file);

	$ret  = '<table class="permtable">';
	$ret .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td><img src=\"{$imagedir}p_read.png\" alt=\"Read\"></td><td><img src=\"{$imagedir}p_write.png\" alt=\"Write\"></td><td><img src=\"{$imagedir}p_exec.png\" alt=\"Exec\"></td></tr>";
	$ret .= "<tr><td>{$lng['powner']}</td><td>&nbsp;</td><td>" . (($p & 0x0100) ? $yes : $no) . '</td><td>' . (($p & 0x0080) ? $yes : $no) . '</td><td>' . (($p & 0x0040) ? $yes : (($p & 0x0800) ? $yes : $no)) . '</td></tr>';
	$ret .= "<tr><td>{$lng['pgroup']}</td><td>&nbsp;</td><td>" . (($p & 0x0020) ? $yes : $no) . '</td><td>' . (($p & 0x0010) ? $yes : $no) . '</td><td>' . (($p & 0x0008) ? $yes : (($p & 0x0400) ? $yes : $no)) . '</td></tr>';
	$ret .= "<tr><td>{$lng['pother']}</td><td>&nbsp;</td><td>" . (($p & 0x0004) ? $yes : $no) . '</td><td>' . (($p & 0x0002) ? $yes : $no) . '</td><td>' . (($p & 0x0001) ? $yes : (($p & 0x0200) ? $yes : $no)) . '</td></tr>';
	$ret .= '</table>';
	return($ret);
}

function getname($dir, $name)
{
	if(is_file($dir . $name) || is_dir($dir . $name))
	{
		$parts = pathinfo($name);
		if(isset($parts['extension']))
		{
			$filename = substr($name, 0, ((-1) * strlen($parts['extension'])) - 1);    // filename by pathinfo only exists in php 5.2.0 or greater
			return(getname($dir, $filename.'_.'.$parts['extension']));
		}
		else
			return(getname($dir, $name.'_'));
	}
	else
		return($name);
}

function fullcopy($dir1, $dir2)
{
	if(is_dir($dir1))
	{
		if(is_file($dir2))
			return false;
		if(!is_dir($dir2))
			mkdir($dir2);
		$dir = opendir($dir1);
		$success = 1;
		while(($node = readdir($dir)) !== false)
		{
			if($node == '.' || $node == '..')
				continue;
			$success &= fullcopy("$dir1/$node", "$dir2/$node");
		}
		return $success;
	}
	else
	{
		return intval(copy($dir1, $dir2));
	}
}

function rrmdir($dir)
{
	$s = true;
	$dirs = scandir($dir);
	foreach($dirs as $d)
	{
		if($d == '.' || $d == '..')
			continue;
		if(!is_dir("$dir/$d") || is_link("$dir/$d"))
		{
			if(!unlink("$dir/$d"))
				$s = false;
		}
		else
		{
			if(!rrmdir("$dir/$d"))
				$s = false;
		}
	}
	if($s)
	{
		if(!rmdir($dir))
			$s = false;;
	}
	return($s);
}

function copybufferadd($e)
{
	if(isset($_SESSION['copybuffer']))
	{
		foreach($_SESSION['copybuffer'] as $x)
		{
			if($x->source == $e->source)
			{
				if($x->kill != $e->kill)
					$x->kill = $e->kill;

				return;
			}
		}
	}

	$_SESSION['copybuffer'][] = $e;
}

function dirsize($dir)
{
	$size = trim(shell_exec('du -s '.escapeshellarg($dir).' | cut -f 1'));
	return(intval($size)*1024);
}

function insertPid($jid, $pid)
{
	global $db;

	$db->query('UPDATE jobs SET pid = ? WHERE jid = ?', 'ii', $pid, $jid);
}

function insertJob($uid, $function, $file1, $file2, $options = '')
{
	global $db;
	
	if($_SESSION['lastjcheck'] == 0)
		$_SESSION['lastjcheck'] = time();

	$db->query('INSERT INTO jobs (uid, status, function, file1, file2, options, starttime) VALUES (?, \'running\', ?, ?, ?, ?, ?)', 'issssi',
					$uid,
					$function,
					$file1,
					$file2,
					$options,
					time());


	return($db->insert_id());
}

function updateStatus($jid, $status, $time = 0)
{
	global $db;

	$db->query('UPDATE jobs SET status = ?, finishtime = ? WHERE jid = ?', 'sii', $status, $time, $jid);
}

function executeJob($function, $file1, $file2, $options = '')
{
	global $settings;

	$script  = TO_ROOT.'bash/execute_job.php';
	if(!is_executable($script))
		fatal("Execute-Job-PHP-Script is not executable", __FILE__, __LINE__);
	if((($php = getbin('php')) === false) || (($nohup = getbin('nohup')) === false))
		fatal("Missing Binarys, check Logfile", __FILE__, __LINE__);
	$jid = insertJob($_SESSION['uid'], $function, $file1, $file2, $options);
	$pid = shell_exec(sprintf('%s %s %s %s %d > /dev/null & echo $!',
		         $nohup,
					$php,
					escapeshellarg($script),
					escapeshellarg(TO_ROOT),
					$jid));
	$pid = intval($pid);
	if($pid > 0)
		insertPid($jid, $pid);
	else
		updateStatus($jid, 'unabled', time());

	return($pid);
}


function realdirname($path)
{
		if(is_dir($path))
			$dir = clean_dir(clean_dir($path).'../');
		else
			$dir = dirname($path);

	return($dir);
}

?>
