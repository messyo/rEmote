<?php

function sidebar_render()
{
	global $settings, $global, $lng, $imagedir, $qsid, $filter, $ftext, $refresh_arr;

	$sidebar = '';
	$percup  = $settings['maxupspeed'] == 0 ? 0 : $global['upspeed']*100/($settings['maxupspeed']*1024);
	$percdwn = $settings['maxdownspeed'] == 0 ? 0 : $global['downspeed']*100/($settings['maxdownspeed']*1024);
	$box  = "<h2>{$lng['speed']}</h2><div class=\"sidebarcontent\" id=\"sidebarspeed\">";
	$box .="<div class=\"label\"><img src=\"{$imagedir}max_up.png\" alt=\"Up\" /></div><div id=\"sidebarup\">".progressbar($percup > 100 ? 100 : $percup, format_bytes($global['upspeed']).'/s</div>');
	$box .="<div class=\"label\"><img src=\"{$imagedir}max_down.png\" alt=\"Down\" /></div><div id=\"sidebardown\">".progressbar($percdwn > 100 ? 100 : $percdwn, format_bytes($global['downspeed']).'/s</div>');
	$sidebar .= $box.'</div>';




	$free = disk_free_space($_SESSION['dir']); $total = disk_total_space($_SESSION['dir']); $progress = ($total - $free) / $total * 100;
	$free = format_bytes($free); $total = format_bytes($total);
	$box  = "<h2>{$lng['diskspace']}</h2><div class=\"sidebarcontent\" id=\"sidebardisk\">";
	$box .= "<div>{$lng['freespace']}:<br />$free/$total</div>";
	$box .= progressbar($progress);
	$sidebar .= $box.'</div>';

	$upspeed   = intval($global['uplimit']/1024);
	$downspeed = intval($global['downlimit']/1024);
	$box  = "<h2>{$lng['maxspeeds']}</h2><div class=\"sidebarcontent\" id=\"sidebarbandwith\">";
	$box .= "<form action=\"control.php$qsid\" method=\"post\">";
	$box .= "<div class=\"label\"><img src=\"{$imagedir}max_up.png\" alt=\"Up\" /></div><div><input type=\"text\" class=\"num\" name=\"maxup\" value=\"$upspeed\" />&nbsp;KB/s</div>";
	$box .= "<div class=\"label\"><img src=\"{$imagedir}max_down.png\" alt=\"Down\" /></div><div><input type=\"text\" class=\"num\" name=\"maxdown\" value=\"$downspeed\" />&nbsp;KB/s</div>";
	$box .= "<input type=\"submit\" class=\"submit\" name=\"maxspeeds\" value=\"{$lng['apply']}\" />";
	$box .= '</form></div>';
	$sidebar .= $box;

	$box  = "<h2>{$lng['filter']}</h2><div class=\"sidebarcontent\" id=\"sidebarfilter\">";
	$box .= "<form action=\"index.php$qsid\" method=\"post\">";
	$box .= "<div><input type=\"text\" name=\"ftext\" class=\"text\" value=\"$ftext\" onkeyup=\"filter( this );\" /></div>";
	$box .= "<div><input type=\"submit\" name=\"fsubmit\" id=\"fsubmit\" class=\"submit\" value=\"{$lng['apply']}\" /></div></form></div>";
	$sidebar .= $box;

	$box  = "<h2>{$lng['refresh']}</h2><div class=\"sidebarcontent\" id=\"sidebarrefresh\">";
	$box .= "<form action=\"control.php$qsid\" method=\"post\">";
	$box .= "<div><label for=\"refinterval\">{$lng['interval']}:</label> <input type=\"text\" class=\"num\" name=\"refinterval\" id=\"refinterval\" value=\"{$_SESSION['refinterval']}\" />&nbsp;{$lng['sec']}</div>";
	$box .= '<div><select name="refmode">';
	foreach($refresh_arr as $key => $val)
	{
		if($_SESSION['refmode'] == $key)
			$box .= "<option value=\"$key\" selected=\"selected\">{$lng[$val]}</option>";
		else
			$box .= "<option value=\"$key\">{$lng[$val]}</option>";
	}
	$box .= "</select>";
	$box .= "<input type=\"submit\" name=\"refsubmit\" id=\"refsubmit\" class=\"submit\" value=\"{$lng['apply']}\" /></div></form></div>";
	$sidebar .= $box;



	if($_SESSION['status'] >= 2)
	{
		$box    = "<h2>{$lng['serverinfo']}</h2>";
		$box   .= "<div class=\"sidebarcontent\" id=\"sidebarserver\">rEmote: {$global['versions']['remote']}<br />rtorrent: {$global['versions']['rtorrent']}<br />libtorrent: {$global['versions']['libtorrent']}<hr />";
		$l = fopen('/proc/loadavg', 'r');
		$loads = explode(' ', fgets($l));
		fclose($l);
		$perc = $loads[0] > 1 ? 100 : ($loads[0]*100);
		$box   .= "<div id=\"sidebarload\"><div>{$lng['load']}: {$loads[0]} {$loads[1]} {$loads[2]}</div>".progressbar($perc, $perc.'%').'</div>';
		$sidebar .= $box.'</div>';
	}

	if($settings['shoutbox'] && $_SESSION['shoutbox'] == 1 )
	{
		$box  = "<h2>{$lng['shoutbox']}</h2>";
		$box .= "<div class=\"sidebarcontent\" id=\"sidebarshoutbox\">";
		$box .= makeShoutbox();
		$box .= '</div>';
		$sidebar .= $box;
	}

	return($sidebar);
}

?>
