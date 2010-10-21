<?php

define('TO_ROOT', '../');

define('NO_GC', true); // Don't call Garbage-Collector

require_once(TO_ROOT.'inc/global.php');


function add($hash, $field, $value)
{
	global $output;

	$value = str_replace('\'', '\\\'', $value);
	$output .= ", '$hash', '$field', '$value'";
}

function error_handler($code, $text, $file, $line)
{
	exit("ERROR ($code) in $file:$line: $text");
}

define('IS_REFRESH', true);
require_once(TO_ROOT.'inc/defines/torrent.php');
require_once(TO_ROOT.'inc/functions/list.fun.php');
require_once(TO_ROOT.'inc/boxarea.php');
session_write_close();

set_error_handler('error_handler');

//if(!(isset($_SESSION['status']) && $_SESSION['status'] > 0))
//	die('ERROR: Invalid Session, maybe timeout');


$totup_format = format_bytes($global['upspeed']);
$totdown_format = format_bytes($global['downspeed']);
$percup  = $settings['maxupspeed'] == 0 ? 0 : $global['upspeed']*100/($settings['maxupspeed']*1024);
$percdwn = $settings['maxdownspeed'] == 0 ? 0 : $global['downspeed']*100/($settings['maxdownspeed']*1024);

$output  = "[ '$totup_format/s / $totdown_format/s - {$settings['html_title']}'";
$output .= ', \''.progressbar($percup  > 100 ? 100 : $percup,  $totup_format.'/s').'\'';
$output .= ', \''.progressbar($percdwn > 100 ? 100 : $percdwn, $totdown_format.'/s').'\'';

$free = disk_free_space($_SESSION['dir']); $total = disk_total_space($_SESSION['dir']); $progress = ($total - $free) / $total * 100;
$free = format_bytes($free); $total = format_bytes($total);

$output .= ", '<div>{$lng['freespace']}:<br />$free/$total</div>".progressbar($progress).'\'';

if($settings['shoutbox']
	&& (
		in_array(BoxArea::BOX_SHOUTBOX, $_SESSION['boxpositions'][BOX_SIDE])
		|| in_array(BoxArea::BOX_SHOUTBOX, $_SESSION['boxpositions'][BOX_TOP])
		|| in_array(BoxArea::BOX_SHOUTBOX, $_SESSION['boxpositions'][BOX_BOTTOM])
		|| in_array(BoxArea::BOX_SHOUTBOX, $_SESSION['boxpositions'][BOX_RIGHT])
		)
	)
{
	$shoutbox = new Shoutbox;
	$shoutboxcontent = addslashes($shoutbox->getShouts());
}
else
{
	$shoutboxcontent = '';
}

$output .= ", '$shoutboxcontent'";


if($_SESSION['status'] >= 2)
{
	$l = fopen('/proc/loadavg', 'r');
	$loads = explode(' ', fgets($l));
	fclose($l);
	$perc = $loads[0] > 1 ? 100 : ($loads[0]*100);
	$output .= ", '<div>{$lng['load']}: {$loads[0]} {$loads[1]} {$loads[2]}</div>".progressbar($perc, $perc.'%').'</div>\'';
}
else
	$output .= ', \'\'';



if($_SESSION['refmode'] == 2)
{
	// refresh all

	$data=get_full_list($_SESSION['viewmode'], $_SESSION['groupmode'], $_SESSION['sourcemode']);


	$cache = cache_get('refresh');
	if($cache === false)
		$cache = array();
	$changed = false;

	if(is_array($data) && count($data))
	{
		foreach($data as $groupid => $group)
		{
			$t_count     = 0;
			$t_done      = 0.0;
			$t_eta       = 0.0;
			$t_sup       = 0.0;
			$t_sdwn      = 0.0;
			$t_seeded    = 0.0;
			$t_completed = 0.0;
			$t_size      = 0.0;
			$t_ratio     = 0.0;

			foreach($group as $item)
			{
				$l = array();
				$l_hash         = $item[HASH];
				$l_status       = $item[STATUS];
				$l['statuskey'] = $lng["status$l_status"];
				$l['statusimg'] = "<img src=\"{$imagedir}status_$l_status.png\" alt=\"{$l['statuskey']}\" />";
				$l['done']      = progressbar($item[PERCENT_COMPLETE], $item[PERCENT_COMPLETE].'%');
				$l['eta']       = $item[ETA];
				$l['upspeed']   = ($item[UP_RATE] ? ('<span class="speedhighlight">'.format_bytes($item[UP_RATE]).'/s</span>') : format_bytes($item[UP_RATE]).'/s');
				$l['downspeed'] = ($item[DOWN_RATE] ? ('<span class="speedhighlight">'.format_bytes($item[DOWN_RATE]).'/s</span>') : format_bytes($item[DOWN_RATE]).'/s');
				$l['seeded']    = format_bytes($item[UP_TOTAL]);
				$l['completed'] = format_bytes($item[COMPLETED_BYTES]);
				$l['peers']     = "{$item[PEERS_CONNECTED]}/{$item[PEERS_NOT_CONNECTED]} ({$item[PEERS_COMPLETE]})";
				$l['ratio']     = round($item[RATIO]/1000, 2);
				$l['message']   = $item[MESSAGE];
				if($l['ratio'] < 1)
					$l['ratio']  = '<span style="color: #'.dechex(255-($l['ratio']*255))."0000;\">{$l['ratio']}</span>";
				//$l['check']     = "<input type=\"checkbox\" class=\"checkbox\" name=\"multiselect[]\" value=\"$l_hash\" />";

				$t_count     += 1;
				$t_done      += $item[PERCENT_COMPLETE];
				$t_sup       += $item[UP_RATE];
				$t_sdwn      += $item[DOWN_RATE];
				$t_seeded    += $item[UP_TOTAL];
				$t_completed += $item[COMPLETED_BYTES];
				$t_size      += $item[SIZE_BYTES];
				$t_ratio     += $item[RATIO];

				if(isset($cache[$l_hash]))
				{
					$diff = array_diff($l, $cache[$l_hash]);
					if(count($diff))
					{
						$changed = true;
						foreach($diff as $key => $val)
							add($l_hash, $key, $val);
					}
				}
				else
				{
					$changed = true;
					$cache[$l_hash] = $l;
					foreach($l as $key => $val)
						add($l_hash, $key, $val);
				}
			}

			$ident          = 'group'.$groupid;
			$l['count']     = $t_count;
			$l['done']      = round($t_done/$t_count, 2).'%';
			$l['upspeed']       = ($t_sup  ? ('<span class="speedhighlight">'.format_bytes($t_sup).'/s</span>') : format_bytes($t_sup).'/s');
			$l['downspeed']      = ($t_sdwn ? ('<span class="speedhighlight">'.format_bytes($t_sdwn).'/s</span>') : format_bytes($t_sdwn).'/s');
			$l['speeds']    = "{$l['upspeed']}/{$l['downspeed']}";
			$l['seeded']    = format_bytes($t_seeded);
			$l['completed'] = format_bytes($t_completed);
			$l['size']      = format_bytes($t_size);
			$l['ratio']     = $t_completed > 0 ? round(($t_seeded/$t_completed), 2) : 0;
			if($l['ratio'] < 1)
				$l['ratio'] = '<span style="color: #'.dechex(255-($l['ratio']*255))."0000;\">{$l['ratio']}</span>";

			if(isset($cache[$ident]))
			{
				$diff = array_diff($l, $cache[$ident]);
				if(count($diff))
				{
					$changed = true;
					foreach($diff as $key => $val)
						add($ident, $key, $val);
				}
			}
			else
			{
				$changed = true;
				$cache[$ident] = $l;
				foreach($l as $key => $val)
					add($ident, $key, $val);
			}
		}
	}

	if($changed)
		cache_put('refresh', $cache, $_SESSION['uid'], time()+(7*24*60*60));
}

header("Content-type: text/txt");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s"). " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

echo $output;
echo ']';

?>
