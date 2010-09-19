<?php

define('TO_ROOT', './');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/functions/file.fun.php');
require_once(TO_ROOT.'inc/functions/add.fun.php');
require_once(TO_ROOT.'inc/functions/torrents.fun.php');

function info($name, $val)
{
	global $lng;

	return("<tr><td>{$lng[$name]}:</td><td>$val</td></tr>");
}

if(isset($_GET['dir']) && is_valid_dir($_GET['dir'].'/'))
{
	$isdir  = true;
	$object = $_GET['dir'];
}
else if(isset($_GET['file']) && is_valid_file($_GET['file']))
{
	$isdir  = false;
	$object = $_GET['file'];
}
else
{
	$out->content = "<div id=\"main\"><div id=\"content\"><h1>{$lng['badfile']}</div></div>";
	$out->renderPage($settings['html_title'], true, true);
}


if(!$isdir)
{
	$actionsarr['download'] = 'download.png';
	$ending = substr($object, -4);
	switch($ending)
	{
		case '.jpg':
		case 'jpeg':
		case '.png':
		case '.bmp':
		case 'tiff':
		case '.xpm':
		case '.gif':
		case '.ico':
		case '.nfo':
		case '.txt':
		case 'html':
		case '.php':
			$actionsarr['display'] = 'show.png';
			break;
		case '.bz2':
		case '.tar':
		case '.tgz':
		case 'r.gz':
			$actionsarr['display'] = 'show.png';
			if(getBin('tar') === false)
				break;
			$actionsarr['extract'] = 'extract.png';
			break;
		case '.rar':
			$actionsarr['display'] = 'show.png';
			if(getBin('unrar') === false)
				break;
			$actionsarr['extract'] = 'extract.png';
			break;
		case '.zip':
			$actionsarr['display'] = 'show.png';
			if(getBin('zip') === false)
				break;
			$actionsarr['extract'] = 'extract.png';
			break;
		case 'rent':
			if(substr($object, -8) != '.torrent')
				break;
			$actionsarr['fadd']      = 'fadd.png';
			$actionsarr['faddstart'] = 'fadd.png';
			if($settings['real_multiuser'])
			{
				$actionsarr['faddpublic']  = 'fadd.png';
				$actionsarr['faddstartpu'] = 'fadd.png';
			}
			$actionsarr['edittorrent'] = 'edit.png';
			break;
		case '.sfv':
			$actionsarr['display'] = 'show.png';
         $actionsarr['checksfv'] = 'checksfv.png';
			break;
	}
}

if(getBin('tar', false) !== false)
{
	$actionsarr['archtar']    = 'compress.png';
	$actionsarr['downtar']    = 'download.png';
	$actionsarr['archtarbz2'] = 'compress.png';
}
if(getBin('rar', false) !== false)
{
	$actionsarr['archrar']    = 'compress.png';
}
if(getBin('zip', false) !== false)
{
	$actionsarr['archzip']    = 'compress.png';
	$actionsarr['downzip']    = 'download.png';
}
if(getBin('mktorrent', false) !== false)
{
	$actionsarr['mktorrent']  = 'mktorrent.png';
}
if(isset($_SESSION['tfiles'][$object]))
{
	$actionsarr['dntorrent']  = 'mktorrent.png';
}

if(isset($_GET['action']) && isset($actionsarr[$_GET['action']]))
{
	switch($_GET['action'])
	{
		case 'display' :
			$out->redirect('filedisplay.php?file='.rawurlencode($object).'&'.SID);
			break;
		case 'fadd':
			$public = false;
		case 'faddpublic':
			if(!isset($public))
				$public = true;
			$action = 'load';
		case 'faddstart':
			if(!isset($public))
				$public = false;
		case 'faddstartpu':
			if(!isset($public))
				$public = true;

			if(!isset($action))
				$action = 'load_start';
			if('' == ($ans = add_single_torrent($object, $action, $public, false)))
				$out->addSuccess($lng['tadded']);
			else
				$out->addError($ans);
			break;
		case 'extract' :
			$path_parts = pathinfo($object);
			$path = $path_parts['dirname'];
			switch($path_parts['extension'])
			{
				case 'tar':
				case 'gz':
				case 'tgz':
				case 'bz2':
					$action = 'untar';
					break;
				case 'zip':
					$action = 'unzip';
					break;
				case 'rar':
					$action = 'unrar';
					break;
			}
			if(executeJob($action, $object, $path))
				$out->addSuccess($lng['jobstarted']);
			else
				$out->addError($lng['jobnostarted']);
			break;
		case 'dntorrent':
			if(!isset($_SESSION['tfiles'][$object]))
				break;
			$nobj = $_SESSION['tfiles'][$object][0];
			$delete = $_SESSION['tfiles'][$object][1];
			if(count($_SESSION['tfiles'] > 1))
				unset($_SESSION['tfiles'][$object]);
			else
				unset($_SESSION['tfiles']);
			$object = $nobj;
		case 'download':
			header("Pragma: no-cache");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream\n");
			header("Content-disposition: attachment; filename=\"" . basename($object) . "\"\n");
			header("Content-transfer-encoding: binary\n");
			header("Content-length: " . filesize($object) . "\n");
			header("Last-Modified: " . date("r", filemtime($object)));
			session_write_close();
			readfile($object);
			if(isset($delete) && $delete)
				unlink($object);
			exit;
			break;
		case 'downtar' :
			$command = 'tar -c -f - ' . escapeshellarg(basename($object));
			$ending  = 'tar';
		case 'downzip' :
			if(!isset($command))
			{
				$command = 'zip -0rq - ' . escapeshellarg(basename($object)) . ' | cat';
				$ending = 'zip';
			}
			header("Pragma: no-cache");
			header("Content-Description: File Transfer");
			header("Content-type: application/octet-stream\n");
			header("Content-disposition: attachment; filename=\"" . basename($object) . '.' .  $ending . "\"\n");
			header("Content-transfer-encoding: binary\n");
			header("Last-Modified: " . date("r"));
			chdir(realdirname($object));
			session_write_close();
			passthru($command);
			exit;
			break;
		case 'archtar':
			$ending = '.tar';
		case 'archtarbz2':
			if(!isset($ending))
				$ending = '.tar.bz2';
		case 'archzip':
			if(!isset($ending))
				$ending = '.zip';
		case 'archrar':
			if(!isset($ending))
				$ending = '.rar';
			$savedir = realdirname($object);
			if(is_valid_dir($savedir))
			{
				if($isdir)
				{
					$parts = explode('/', clean_dir($object));
					$c = count($parts);
					$name = $parts[$c - 2];
				}
				else
					$name = basename($object);
				$name = getname($savedir, $name . $ending);
				if(executeJob($_GET['action'], $name, $object))
					$out->addSuccess($lng['jobstarted']);
				else
					$out->addError($lng['jobnostarted']);
			}
			else
				$error = $lng['invfolder'];
			break;
		case 'mktorrent':
			if(($command = getBin('mktorrent')) === false)
			{
				$out->addError($lng['internerror']);
				break;
			}

			$v_name = preg_replace('/[^a-zA-Z0-9\-\_\.]/U', '_', basename($object)) . '.torrent';
			$v_dir  = realdirname($object);
			$v_url  = '';
			$v_size = 19;
			$v_pub  = false;
			$v_save = false;
			$v_down = true;
			$v_add  = false;
			$chunksizes = array(15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28);
			
			$dialog = 'mktorrent';
			
			if(isset($_POST['create']))
			{
				$v_name = $_POST['torrentname'];
				$v_dir  = clean_dir($_POST['torrentdir']);
				$v_url  = $_POST['torrenturl'];
				$v_size = $_POST['torrentsize'];
				$v_pub  = (isset($_POST['torrentpub'])  && ($_POST['torrentpub']  == 'true')) ? true : false;
				$v_save = (isset($_POST['torrentsave']) && ($_POST['torrentsave'] == 'true')) ? true : false;
				$v_down = (isset($_POST['torrentdown']) && ($_POST['torrentdown'] == 'true')) ? true : false;
				$v_add  = (isset($_POST['torrentadd'])  && ($_POST['torrentadd']  == 'true')) ? true : false;

				if($v_name == '')
					$out->addError($lng['invalidname']);
				if(($v_dir == '') || (!is_valid_dir($v_dir)))
					$out->addError($lng['invaliddir']);
				if($v_url == '')
					$out->addError($lng['invalidurl']);
				if(!in_array($v_size, $chunksizes))
					$out->addError($lng['invalidchunk']);
				if(!$v_save && !$v_down && !$v_add)
					$out->addError($lng['nosaveopt']);

				if(!$out->hasError())
				{
					if(substr($v_name, -8) != '.torrent')
						$v_name .= '.torrent';
					//$v_url = rawurlencode($v_url);


					exec("$command --version", $return);
					if(strstr($return[0], 'Borg'))
						$borg = true;
					else if(strstr($return[0], 'Emil Renner Berthing'))
						$borg = false;
					else
					{
						logger(LOGERROR, 'No valid mktorrent found. Need Emil Renner Berthings Version OR Borg-Version', __FILE__, __LINE__);
						$out->addError($lng['internerror']);
						break;
					}

					if($v_save)
						$file = $v_dir.getname($v_dir, $v_name);
					else
               	$file = $settings['tmpdir'].getname($settings['tmpdir'], $v_name);

					// Let's check on nobody is trieing to write (AND DOWNLOAD!) files he should not download
					if(!is_valid_file($file, false))
					{
               	$out->addError($lng['internerror']);
						logger(LOGSECURITY, "User {$_SESSION['uid']} tried to create torrentfile \"$file\"", __FILE__, __LINE__);
						break;
					}

					if(!checkWrite(realdirname($file), true))
						break;

               $out->addNotify(escapeshellarg($file));

					if($borg)
						$return = shell_exec(sprintf('%s -a %s -bs %d%s -o %s %s',
												$command,
												escapeshellarg($v_url),
												intval(pow(2, $v_size - 10)),
												$v_pub ? ' -pub' : '',
												escapeshellarg($file),
												escapeshellarg($object)));
					else
						$return = shell_exec(sprintf('%s -a %s -l %d%s -o %s %s',
												$command,
												escapeshellarg($v_url),
												$v_size,
												$v_pub ? '' : ' -p',
												escapeshellarg($file),
												escapeshellarg($object)));

					unset($dialog);

					if($settings['debug_mode'])
					{
						logger(LOGDEBUG, 'Created Torrent with borg = '.intval($borg).". Please see Logfile \"{$settings['tmpdir']}rEmote_torrent_creator.log\" for further details", __FILE__, __LINE__);
						$h = fopen("{$settings['tmpdir']}rEmote_torrent_creator.log", 'w');
						fwrite($h, $return);
						fclose($h);
						unset($h);
					}

					unset($return);

					if(is_file($file))
						$out->addSuccess($lng['tcreated']);
					else
					{
						$out->addError($lng['tnotcreated']);
						break;
					}

					if($v_add)
						add_single_torrent($file, 'load', false, false);

					if(!$v_save && !$v_down)
						unlink($file);
					
					if($v_down)
					{
						$_SESSION['tfiles'][$object] = array($file, !$v_save);
						$obj = $isdir ? 'dir' : 'file';
						$robj = rawurlencode($object);
               	$out->redirect("filedetails.php?$obj=$robj&action=dntorrent&".SID);
					}

				}
			}
			break;
		case 'edittorrent':
		   $dialog = 'edittorrent';
			$torrent = file_get_contents($object);
			$len     = strlen($torrent);
			if(($start = strpos($torrent, '8:announce')) === false)
			{
				$out->addError('invalidtorrent');
				break;
			}
			$start = $start + 10;

			$f = false;
			for($i = $start; $i < $len; $i++)
			{
				if($torrent[$i] == ':')
				{
					$f = true;
					break;
				}
			}
			$end = $i;


			if(!$f || (($anlen = intval(substr($torrent, $start, $end - $start))) == 0))
			{
				$out->addError('notfound');
				break;
			}

			$announce = substr($torrent, $end + 1, $anlen);

			if(isset($_POST['newannounce']))
			{
         	$newann = $_POST['newannounce'];
				if($newann == $announce)
					break;
				if(!is_writable($object))
					$out->addError($lng['nowrite']);
				$newlen = strlen($newann);
				$torrent = str_replace("8:announce$anlen:$announce", "8:announce$newlen:$newann", $torrent);


				if(!$out->hasError())
				{
					if(!($h = fopen($object, 'w')))
					{
						$out->addError($lng['internerror']);
						logger(LOGERROR, 'Could not open Torrent file', __FILE__, __LINE__);
					}
					else
					{
						fwrite($h, $torrent);
						fclose($h);
						$out->addSuccess($lng['saved']);
						unset($dialog);
					}
				}
			}
			
			break;
		case 'checksfv':
			$files = file($object);
			$dir   = dirname($object);
			
			$no  = "<img src=\"{$imagedir}button_cancel.png\" alt=\"{$lng['no']}\" />";
			$yes = "<img src=\"{$imagedir}button_ok.png\" alt=\"{$lng['yes']}\" />";

			$passedTotal = true;

			$table = "<table id=\"sfvtable\"><thead><tr><td colspan=\"4\" class=\"tableheadline\"><h2>{$lng['checksfv']}</h2></td></tr>";
			$table .= "<tr><td>{$lng['file']}</td><td>{$lng['sfvhash']}</td><td>{$lng['calchash']}</td><td>{$lng['passed']}</td></tr></thead>";
			foreach($files as $file)
			{
				$parts    = explode(' ', trim($file));
				$extHash  = array_pop($parts);
				$filename = implode(' ', $parts);
				$path     = "$dir/$filename";

				// The following clean code could not be used due to a bug in PHP...
				// http://bugs.php.net/bug.php?id=45028
				// $intHash = hash_file('crc32b', $path);
				//
				if(is_valid_file($path) && is_readable($path))
					$intHash = str_pad(dechex(crc32(file_get_contents($path))), 8, '0', STR_PAD_LEFT);
				else
					$intHash = '';

				$passed = ($intHash == $extHash);
				$passedTotal &= $passed;
				$row = '<tr><td>'.htmlspecialchars($filename, ENT_QUOTES)."</td><td>$extHash</td><td>$intHash</td>";
			   if($passed)
					$row .= "<td>$yes</td>";
				else
					$row .= "<td>$no</td>";
				$row .= '</tr>';

				$table .= $row;
			}
         
			$table .= '</table>';
			$dialog = 'checksfv';

			if($passedTotal)
				$out->addSuccess($lng['sfvpassed']);
			else
				$out->addError($lng['sfvfailed']);

			break;
	}
}


if($isdir)
	$image = "<img src=\"{$imagedir}folder_large.png\" alt=\"Folder\" />";
else
{
	$ending = substr($object, -4);
	$image = "<img src=\"{$fileimgs}large/".get_icon($ending)."\" alt=\"$ending\" />";
}

$title = '';
$parts = explode('/', $object);
for($x = 1; $x < count($parts); $x++)
	$title.= " /{$parts[$x]}";
$title = htmlspecialchars($title, ENT_QUOTES);
$headline = "<h2>$image&nbsp;".$title.'</h2>';


if(!isset($dialog))
{
	$information  = "<fieldset class=\"box\" id=\"fileinfobox\"><legend>{$lng['information']}</legend><table>";
	$information .= info('size', $isdir ? format_bytes(dirsize($object)) : format_bytes(filesize($object)));
	$information .= info('lastchange', date("d.m.Y - H:i:s", filemtime($object)));
	$information .= '</table></fieldset>';

	$permissions  = "<fieldset class=\"box\" id=\"filepermbox\"><legend>{$lng['permissions']}</legend>";
	$permissions .= make_permission_table($object);
	$permissions .= "</fieldset>";

	$actions = "<fieldset class=\"box\" id=\"fileactionbox\"><legend>{$lng['actions']}</legend>";


	if(isset($actionsarr) && is_array($actionsarr) && count($actionsarr))
	{
		$actions .= '<ul id="actions">';
		if($isdir)
			$prefix = 'dir';
		else
			$prefix = 'file';
		$encobject = rawurlencode($object);
		asort($actionsarr);
		foreach($actionsarr as $act => $img)
			$actions     .= "<li><a href=\"filedetails.php?$prefix=$encobject&amp;action=$act$sid\" title=\"{$lng[$act]}\"><img src=\"$imagedir$img\" alt=\"$act\" />&nbsp;<span>{$lng[$act]}</span></a></li>";
		$actions     .= '</ul>';
	}
	else
		$actions .= $lng['noactions'];
	$actions .= '</fieldset>';

	$content = "$information$permissions$actions";
}
else
{
	switch($dialog)
	{
		case 'mktorrent':
			$sizeoptions = '';
			foreach($chunksizes as $s)
			{
         	$c = (($s == $v_size) ? ' selected="selected"' : '');
				$sizeoptions .= "<option value=\"$s\"$c>". format_bytes(pow(2,$s)).'</option>';
			}
			
			$obj = $isdir ? 'dir' : 'file';
			$robj = rawurlencode($object);

			$content  = "<fieldset class=\"box\" id=\"mktorrent\"><legend>{$lng['mktorrent']}</legend>";
			$content .= "<form action=\"filedetails.php?action=mktorrent&amp;$obj=$robj$sid\" method=\"post\">";
			$content .= '<table id="mktorrenttable">';
			$content .= "<tr><td><label for=\"torrentname\">{$lng['filename']}</label></td><td><input id=\"torrentname\" type=\"text\" class=\"longtext\" name=\"torrentname\" value=\"$v_name\" /></td></tr>";
			$content .= "<tr><td><label for=\"torrentdir\">{$lng['dir']}</label></td><td><input id=\"torrentdir\" type=\"text\" class=\"longtext\" name=\"torrentdir\" value=\"$v_dir\" /></td></tr>";
			$content .= "<tr><td><label for=\"torrenturl\">{$lng['tannounce']}</label></td><td><input id=\"torrenturl\" type=\"text\" class=\"longtext\" name=\"torrenturl\" value=\"$v_url\" /></td></tr>";
			$content .= "<tr><td><label for=\"torrentsize\">{$lng['tchunksize']}</label></td><td><select name=\"torrentsize\">$sizeoptions</select></td></tr>";
			$content .= "<tr><td>&nbsp;</td><td><input type=\"radio\" id=\"torrentpub\" name=\"torrentpub\" value=\"true\" ".($v_pub ? ' checked="checked"' : '')."/>&nbsp;<label for=\"torrentpub\">{$lng['public']}</label>";
			$content .= "&nbsp;&nbsp;&nbsp;<input type=\"radio\" id=\"torrentpub\" name=\"torrentpub\" value=\"false\" ".(!$v_pub ? ' checked="checked"' : '')."/>&nbsp;<label for=\"torrentpub\">{$lng['private']}</label></td></tr>";
			$content .= "<tr><td><span class=\"label\">{$lng['saveoptions']}</span></td><td>";
			$content .= "<input type=\"checkbox\" id=\"torrentsave\" name=\"torrentsave\" value=\"true\" ".($v_save ? ' checked="checked"' : '')."/>&nbsp;<label for=\"torrentsave\">{$lng['tsave']}</label><br />";
			$content .= "<input type=\"checkbox\" id=\"torrentdown\" name=\"torrentdown\" value=\"true\" ".($v_down ? ' checked="checked"' : '')."/>&nbsp;<label for=\"torrentdown\">{$lng['tdown']}</label><br />";
			$content .= "<input type=\"checkbox\" id=\"torrentadd\"  name=\"torrentadd\"  value=\"true\" ".($v_add  ? ' checked="checked"' : '')."/>&nbsp;<label for=\"torrentadd\">{$lng['tadd']}</label><br />";
			$content .= "</td></tr>";
			$content .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"create\" value=\"{$lng['create']}\" /></td></tr>";
			$content .= '</table></form></fieldset>';
			break;
		case 'edittorrent':
			$obj = $isdir ? 'dir' : 'file';
			$robj = rawurlencode($object);
			$content  = "<fieldset class=\"box\" id=\"mktorrent\"><legend>{$lng['edittorrent']}</legend>";
			$content .= "<form action=\"filedetails.php?action=edittorrent&amp;$obj=$robj$sid\" method=\"post\">";
			$content .= '<table id="edittorrenttable">';
			$content .= "<tr><td><label for=\"newannounce\">{$lng['tannounce']}</label></td><td><input id=\"newannounce\" type=\"text\" class=\"text\" name=\"newannounce\" value=\"$announce\" /></td></tr>";
			$content .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"create\" value=\"{$lng['save']}\" /></td></tr>";
			$content .= '</table></form></fieldset>';

			break;
		case 'checksfv':
         $content = $table;
			break;
	}
}

$messages = $out->getMessages();



$out->content = "<div id=\"main\"><div id=\"content\">$headline$messages$content</div></div>";
$out->renderPage($settings['html_title'], true, true);

?>
