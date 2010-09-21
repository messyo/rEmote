<?php

class CFile
{
	var $name;
	var $index;
	var $path;
	var $size_bytes;
	var $size_chunks;
	var $completed_chunks;
	var $priority;

	function __construct($name, $index, $path, $size_bytes, $size_chunks, $completed_chunks, $priority)
	{
		$this->name             = $name;
		$this->index            = $index;
		$this->path             = $path;
		$this->size_bytes       = $size_bytes;
		$this->size_chunks      = $size_chunks;
		$this->completed_chunks = $completed_chunks;
		$this->priority         = $priority;
	}

	function isFolder()
	{
		return false;
	}
}

class CFolder
{
	var $elements;
	var $name;

	function __construct($name)
	{
		$this->name = $name;
		$this->elements = array();
		$this->folder = true;
	}

	function isFolder()
	{
		return true;
	}
}

$prio_arr = array('off', 'normal', 'high');

// Get list of files associated with a torrent...
function get_file_list($hash)
{
	global $rpc;

	$response = $rpc->request('f.multicall',
								array($hash,
										"",
										'f.get_completed_chunks=',
										'f.get_path=',
										'f.get_path_components=',
										'f.get_priority=',
										'f.get_size_bytes=',
										'f.get_size_chunks='));

	$index=0;
	foreach($response AS $item)
	{
		$retarr[] = new CFile('',
			$index,
			replace_latin1($item[F_PATH]),
			$item[F_SIZE_BYTES],
			$item[F_SIZE_CHUNKS],
			$item[F_COMPLETED_CHUNKS],
			$item[F_PRIORITY]);
		$index++;
	}
	return $retarr;
}

function get_file_array($hash)
{
	global $rpc;

	$name     = $rpc->request('d.get_name', array($hash));
	$response = $rpc->request('f.multicall',
								array($hash,
										"",
										'f.get_completed_chunks=',
										'f.get_path=',
										'f.get_path_components=',
										'f.get_priority=',
										'f.get_size_bytes=',
										'f.get_size_chunks='));

	$index=0;
	$root = new CFolder(htmlspecialchars(replace_latin1($name), ENT_QUOTES));
	foreach($response AS $item)
	{
		$f = $root;

		$c = count($item[F_PATH_COMPS])-1;

		for($x = 0; $x < $c; $x++)
		{
			$p = htmlspecialchars(replace_latin1($item[F_PATH_COMPS][$x]), ENT_QUOTES);
			$found = false;
			foreach($f->elements as $e)
			{
				if($e->name != $p)
					continue;

				$f = $e;
				$found = true;
				break;
			}

			if($found == false)
			{
				$new = new CFolder($p);
				$f->elements[] = $new;
				$f = $new;
			}
		}
		$name = htmlspecialchars(replace_latin1($item[F_PATH_COMPS][$c]), ENT_QUOTES);
		$file = new CFile($name,
			$index,
			$item[F_PATH],
			$item[F_SIZE_BYTES],
			$item[F_SIZE_CHUNKS],
			$item[F_COMPLETED_CHUNKS],
			$item[F_PRIORITY]);
		$f->elements[] = $file;
		$index++;
	}
	return $root;
}

function print_array($arr)
{
	global $imagedir, $fileimgs, $index;

	if($arr->isFolder())
	{
		$index++;
		$return  = "<input type=\"checkbox\" class=\"foldercheck\" name=\"folder$index\" value=\"true\" onchange=\"markbranch( this );\" />";
		$return .= "<img class=\"plusminus\" src=\"{$imagedir}minus.png\" alt=\"-\" onclick=\"branchopenclose( this, 'folder$index' );\" style=\"cursor: pointer;\" />";
		$return .= "<img src=\"{$imagedir}folder.png\" alt=\"F\" />&nbsp;{$arr->name}";
		$return .= "<ul id=\"folder$index\">";
		foreach($arr->elements as $e)
			$return .= '<li>'.print_array($e).'</li>';
		$return .= '</ul>';
	}
	else
		$return = sprintf('<input type="checkbox" class="filecheck" name="priority%d" value="%d" onchange="updateAll();" %s/><img src="%ssmall/%s" alt="_" />&nbsp;%s',
				$arr->index,
				($arr->priority > 0) ? $arr->priority : 1,
				($arr->priority > 0) ? 'checked="checked" ' : '',
				$fileimgs,
				get_icon(strtolower(substr($arr->name, -4))),
				$arr->name
			);

	return $return;
}


function get_peer_list($hash)
{
	global $rpc, $settings;

	$bitfields = $settings['showbitfields'] && $_SESSION['bitfields'];

	if($bitfields)
		$response = $rpc->request("p.multicall",
									array($hash,
										0,
										'p.get_address=',
										'p.get_client_version=',
										'p.get_completed_percent=',
										'p.get_down_rate=',
										'p.get_up_rate=',
										'p.get_port=',
										'p.is_encrypted=',
										'p.get_bitfield='));
	else
		$response = $rpc->request("p.multicall",
									array($hash,
										0,
										'p.get_address=',
										'p.get_client_version=',
										'p.get_completed_percent=',
										'p.get_down_rate=',
										'p.get_up_rate=',
										'p.get_port=',
										'p.is_encrypted='));

	$index = 0;
	$retarr = array();
	$cache  = array();
	foreach($response as $item)
	{
		$retarr[$index]['address']           = $item[P_ADDRESS       ];
		$retarr[$index]['client_version']    = $item[P_CLIENT_VERSION];
		$retarr[$index]['completed_percent'] = $item[P_COMPLETED     ];
		$retarr[$index]['up_rate']           = $item[P_UP_RATE       ];
		$retarr[$index]['down_rate']         = $item[P_DOWN_RATE     ];
		$retarr[$index]['port']              = $item[P_PORT          ];
		$retarr[$index]['is_encrypted']      = $item[P_IS_ENCRYPTED  ];
		if($bitfields)
		{
			// Push to cache
			$cache[$index] = $item[P_BITFIELD];
		}
		$index++;
	}

	if($bitfields && count($cache))
	{
		$already = cache_get('bitfields');
		if(!is_array($already))
			$already = array();

		$already[$hash] = $cache;
		cache_put('bitfields', $already, $_SESSION['uid'], (time() + 120));
	}
	return($retarr);
}


// Get list of trackers associated with torrent...
function get_tracker_list($hash)
{
	global $rpc;

	$response = $rpc->request("t.multicall",
									array($hash,
											'',
											't.get_normal_interval=',
											't.get_scrape_complete=',
											't.get_scrape_time_last=',
											't.get_url=',
											't.is_enabled='));
	$retarr = array();
	$x = 0;
	foreach($response AS $item)
	{
		$retarr[$x]['interval']     = $item[T_INTERVAL   ];
		$retarr[$x]['num_scrapes']  = $item[T_SCRAPES    ];
		$retarr[$x]['last_scrape']  = $item[T_LAST_SCRAPE];
		$retarr[$x]['url']          = $item[T_URL        ];
		$retarr[$x]['enabled']      = $item[T_ENABLED    ];
		$x++;
	}

	return $retarr;
}

function page_tree($hash)
{
	global $bodyonload;
	global $imagedir, $lng, $settings;

	$arr = get_file_array($hash);

	$index = 0;
	$tree = print_array($arr);
	$detailscontent = "<div><input type=\"submit\" name=\"fileprio\" value=\"{$lng['save']}\" /></div><div id=\"tree\">{$tree}</div><div><input type=\"submit\" name=\"fileprio\" value=\"{$lng['save']}\" /></div>";
	$bodyonload = "closeAll();";


	return($detailscontent);
}

function page_list($hash)
{
	global $imagedir, $lng, $settings, $prio_arr;

	$arr = get_file_list($hash);
	$detailscontent  = "<table id=\"filelist\">";
	$detailscontent .= "<tr class=\"thead\"><td>{$lng['size']}</td><td>{$lng['done']}</td><td>{$lng['chunks']}</td><td>{$lng['priority']}</td></tr>";
	$hpriority  = "<select name=\"prio\" onchange=\"listchange(this)\">";
	foreach($prio_arr as $k => $v)
	{
		if(1 == $k)
			$selected = ' selected="selected"';
		else
			$selected = '';
		$hpriority .= "<option value=\"$k\"$selected>{$lng[$v]}</option>";
	}
	$hpriority .= '</select>';
	$detailscontent .= "<tr class=\"thead\"><td><input type=\"submit\" name=\"fileprio\" value=\"{$lng['save']}\" /></td><td>&nbsp;</td><td>&nbsp;</td><td>$hpriority</td></tr>";

	foreach($arr as $file)
	{
		$progress = round($file->completed_chunks/$file->size_chunks*100);
		$bar = progressbar($progress, $progress.'%');
		$size = format_bytes($file->size_bytes);
		$priority  = "<select name=\"priority{$file->index}\">";
		foreach($prio_arr as $k => $v)
		{
			if($file->priority == $k)
				$selected = ' selected="selected"';
			else
				$selected = '';
			$priority .= "<option value=\"$k\"$selected>{$lng[$v]}</option>";
		}
		$priority .= '</select>';
		$line  = "<tr><td colspan=\"4\">{$file->path}</td></tr>";
		$line .= "<tr><td>$size</td><td>$bar</td><td>{$file->completed_chunks}/{$file->size_chunks}</td><td>$priority</td></tr>";
		$detailscontent .= $line;
	}
	$detailscontent .= "<tr class=\"thead\"><td><input type=\"submit\" name=\"fileprio\" value=\"{$lng['save']}\" /></td><td>&nbsp;</td><td>&nbsp;</td><td>$hpriority</td></tr>";
	$detailscontent .= "</table>";

	return($detailscontent);
}

function page_infos($hash)
{
	global $settings, $db, $lng, $rpc, $sid, $dynimgs;

	$prio_arr = array('off', 'low', 'normal', 'high');

	require_once(TO_ROOT.'inc/defines/torrent.php');

	$response = $rpc->multicall(
		'd.get_complete',            array($hash),
		'd.get_completed_bytes',     array($hash),
		'd.get_connection_current',  array($hash),
		'd.get_down_rate',           array($hash),
		'd.get_hash',                array($hash),
		'd.get_message',             array($hash),
		'd.get_name',                array($hash),
		'd.get_peers_complete',      array($hash),
		'd.get_peers_connected',     array($hash),
		'd.get_peers_not_connected', array($hash),
		'd.get_priority',            array($hash),
		'd.get_ratio',               array($hash),
		'd.get_size_bytes',          array($hash),
		'd.get_up_rate',             array($hash),
		'd.get_up_total',            array($hash),
		'd.is_active',               array($hash),
		'd.get_left_bytes',          array($hash),
		'd.get_directory',           array($hash),
		'd.get_down_total',          array($hash),
		'd.get_size_chunks',         array($hash)
	);

	if($response[IS_ACTIVE][0])
	{
		if($response[CONNECTION_CURRENT][0][0] == 's') // Simply check first character , 's' from 'seed', 'l' from 'leech', only 'seed' and 'leech' possible
			$status = 3;
		else
			$status = 2;
	}
	else
	{
		if($response[COMPLETE][0])
			$status = 1;
		else
			$status = 0;
	}

	$infos['name']        = '<strong>'.htmlspecialchars($response[NAME][0], ENT_QUOTES).'</strong>';
	if($settings['real_multiuser'])
	{
		$result = $db->query('SELECT u.name FROM users u, torrents t WHERE u.uid = t.uid AND t.hash = ?', 's', $hash);
		if($h = $db->fetch($result))
			$infos['user'] = htmlspecialchars($h['name'], ENT_QUOTES);
		else
			$infos['user'] = $lng['public'];
	}
	$infos['status']      = $lng["status$status"];
	$infos['hash']        = $response[HASH][0];
	$infos['message']     = htmlspecialchars($response[MESSAGE][0], ENT_QUOTES);
	$infos['size']        = format_bytes($response[SIZE_BYTES][0]);
	$infos['completed']   = format_bytes($response[COMPLETED_BYTES][0]).progressbar($response[COMPLETED_BYTES][0]*100/$response[SIZE_BYTES][0], round($response[COMPLETED_BYTES][0]*100/$response[SIZE_BYTES][0]).'%');
	$infos['down_rate']   = format_bytes($response[DOWN_RATE][0]);
	$infos['up_rate']     = format_bytes($response[UP_RATE][0]);
	$infos['down_total']  = format_bytes($response[DOWN_TOTAL][0]);
	$infos['seeded']      = format_bytes($response[UP_TOTAL][0]);
	$infos['ratio']       = $response[RATIO][0]/1000;
	$infos['peers_con']   = $response[PEERS_CONNECTED][0];
	$infos['peers_nocon'] = $response[PEERS_NOT_CONNECTED][0];
	$infos['peers_compl'] = $response[PEERS_COMPLETE][0];
	$infos['size_chunks'] = $response[SIZE_CHUNKS][0];
	$infos['bitfield']    = "<img alt=\"Bitfield\" style=\"height: 16px; width: 600px; \" src=\"{$dynimgs}bitfield.php?hash={$response[HASH][0]}&amp;width=600$sid\" />";

	$p = "<input type=\"hidden\" name=\"hash\" value=\"$hash\" /><select name=\"prio\">";
	foreach($prio_arr as $k => $v)
	{
		if($k == $response[PRIORITY][0])
			$select = ' selected="selected"';
		else
			$select = '';
		$p .= "<option value=\"$k\"$select>{$lng[$v]}</option>";
	}
	$p .= "</select>&nbsp;<input type=\"submit\" name=\"priochange\" value=\"{$lng['apply']}\" />";


	$infos['priority']    = $p;


	$table = '<table id="detailstable">';
	foreach($infos as $key => $info)
		$table .= "<tr><td class=\"left\">{$lng[$key]}</td><td>$info</td></tr>";
	$table .= '</table>';

	return($table);
}

function page_tracker($hash)
{

	global $lng;
	$response = get_tracker_list($hash);

	$table = "<table id=\"detailstable\"><thead><tr><td>{$lng['url']}</td><td>{$lng['lastscrape']}</td><td>{$lng['scrapeint']}</td><td>{$lng['numscrapes']}</td><td>{$lng['enabled']}</td></tr></thead>";
	foreach($response as $tracker)
	{
		$row  = "<tr><td>{$tracker['url']}</td>";
		if($tracker['last_scrape'] > 0)
			$row .= '<td>'.date("d.m.Y - H:i:s", $tracker['last_scrape']).'</td>';
		else
			$row .= "<td>{$lng['never']}</td>";
		$row .= '<td>'.round($tracker['interval']/60)." {$lng['minutes']}</td>";
		$row .= "<td>{$tracker['num_scrapes']}</td>";
		if($tracker['enabled'] == 1)
			$row .= "<td>{$lng['yes']}</td>";
		else
			$row .= "<td>{$lng['no']}</td>";
		$table .= $row.'</tr>';
	}
	$table .= '</table>';

	return($table);
}

function page_peers($hash)
{
	global $lng, $settings, $dynimgs, $sid;

	$yn = array('no', 'yes');
	$response = get_peer_list($hash);

	$hostcolumn = $_SESSION['hostnames'] ? "<td>{$lng['hostname']}</td>" : '';

	$table = "<table id=\"detailstable\"><thead><tr><td>{$lng['ipaddr']}</td>$hostcolumn<td>{$lng['client']}</td><td>{$lng['complete']}</td><td>{$lng['up_to']}</td><td>{$lng['down_from']}</td><td>{$lng['port']}</td><td>{$lng['encrypted']}</td></tr></thead>";
	foreach($response as $key => $peer)
	{
		$row  = "<tr><td>{$peer['address']}</td>";
		if($_SESSION['hostnames'])
			$row .= '<td>'.gethostbyaddr($peer['address']).'</td>';
		$row .= "<td>{$peer['client_version']}</td>";
		$row .= "<td>{$peer['completed_percent']}%</td>";
		$row .= '<td>'.format_bytes($peer['up_rate']).'/s</td>';
		$row .= '<td>'.format_bytes($peer['down_rate']).'/s</td>';
		$row .= "<td>{$peer['port']}</td>";
		$row .= "<td>{$lng[$yn[$peer['is_encrypted']]]}</td></tr>";
		$table .= $row;
		if($settings['showbitfields'] && $_SESSION['bitfields'])
			$table .= "<tr><td colspan=\"8\"><img src=\"{$dynimgs}bitfield.php?hash=$hash&amp;key=$key&amp;width=600$sid\" alt=\"Bitfield\" height=\"12\" width=\"600\" /></td></tr>";
	}
	$table .= '</table>';

	return($table);
}

?>
