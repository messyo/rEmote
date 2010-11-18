<?php

class Shoutbox
{
	private $smileyPattern = '#(:\-\)|:\)|:\-\(|:\(|;\-\)|;\)|:\||:\-\||8\-\)|8\)|:O|:\-O)#';
	private $smileys = array(
		':-)' => 'smile.gif',
		':)'  => 'smile.gif',
		':('  => 'sad.gif',
		':-(' => 'sad.gif',
		';-)' => 'wink.gif',
		';)'  => 'wink.gif',
		':|'  => 'neutral.gif',
		':-|' => 'neutral.gif',
		'8)'  => 'cool.gif',
		'8-)' => 'cool.gif',
		':O'  => 'shock.gif',
		':-O' => 'shock.gif'
	);
	private $usernamePattern = null;


	private function replaceSmileys($string)
	{
		global $smileyimgs;

		if(is_array($string))
			return "<img src=\"{$smileyimgs}{$this->smileys[$string[1]]}\" alt=\"{$string[1]}\" />";

		return preg_replace_callback($this->smileyPattern, array($this, 'replaceSmileys'), $string);
	}

	private function highlightUsername($string)
	{
		if(empty($this->useranamePattern))
			$this->usernamePattern = '#((^| )+)(' . preg_quote($_SESSION['username']) . ')(($| )+)#';

   	$string = preg_replace($this->usernamePattern, '\\1<span class="highlight">\\3</span>\\4', $string);
	
		return $string;
	}
	
	public function processMessage($string)
	{
		$string = $this->highlightUsername($string);
		$string = $this->replaceSmileys($string);
		
		return $string;
	}

	public function getShouts()
	{
		global $db, $lng, $qsid;

		$result = $db->query('SELECT u.name, s.sid, s.uid, s.message, s.time FROM users u INNER JOIN shouts s ON u.uid = s.uid ORDER BY time DESC LIMIT 30');

		$shouts = '<table>';
		while($h = $db->fetch($result))
			$shouts .= sprintf('<tr><td><strong>%s</strong> <span class="hint">%s</span></td><td>%s</td><td>%s</td></tr>',
				$db->out($h['name']),
				date('d.m.y H:i', $h['time']),
				$this->processMessage($db->out($h['message'])),
				'&nbsp;' // REPLACE BY DELETE-LINK
			);
		$shouts .= '</table>';

		return $shouts;
	}


	public function makeShoutbox()
	{
		global $db, $lng, $qsid;


		if(isset($_POST['shout']) && (trim($_POST['shout']) != ''))
		{
			$hash = sha1($_SESSION['uid'].$_POST['shout'].$_POST['time']);
			if(!intval($db->one_result($db->query('SELECT COUNT(*) AS c FROM shouts WHERE hash = ?', 's', $hash))))
				$db->query('INSERT INTO shouts (uid, time, message, hash) VALUES (?, ?, ?, ?)', 'iiss',
					$_SESSION['uid'],
					time(),
					$_POST['shout'],
					$hash);
		}

		$shouts  = '<div id="shouts">';
		$shouts .= $this->getShouts();
		$shouts .= '</div>';

		$shout  = "<div id=\"shout\"><form action=\"index.php$qsid\" method=\"post\"><div><input type=\"text\" name=\"shout\" class=\"text\" />";
		$shout .= "<input type=\"hidden\" name=\"time\" value=\"".time()."\" /><input class=\"submit\" type=\"submit\" value=\"{$lng['shout']}\" /></div></form></div>";

		return "$shouts<hr />$shout";
	}
}

class BoxArea
{
	const BOX_SPEEDSTATS       = 1;
	const BOX_DISKSTATS        = 2;
	const BOX_BANDWITHSETTINGS = 3;
	const BOX_FILTER           = 4;
	const BOX_REFRESHSETTINGS  = 5;
	const BOX_SERVERSTATS      = 6;
	const BOX_SHOUTBOX         = 7;
	const BOX_LINKLIST         = 8;
	const BOX_ONLINELIST       = 9;

	public function renderBoxSpeedstats($pos, $anz)
	{
   	global $settings, $global, $imagedir, $lng;
		
		$percup  = $settings['maxupspeed'] == 0 ? 0 : $global['upspeed']*100/($settings['maxupspeed']*1024);
		$percdwn = $settings['maxdownspeed'] == 0 ? 0 : $global['downspeed']*100/($settings['maxdownspeed']*1024);
		
		$box  = "<div class=\"box\" id=\"boxspeed\"><h2>{$lng['speed']}</h2><div class=\"boxcontent\">";
		$box .= "<div class=\"label\"><img src=\"{$imagedir}max_up.png\" alt=\"Up\" /></div><div id=\"boxup\">".progressbar($percup > 100 ? 100 : $percup, format_bytes($global['upspeed']).'/s</div>');
		$box .= "<div class=\"label\"><img src=\"{$imagedir}max_down.png\" alt=\"Down\" /></div><div id=\"boxdown\">".progressbar($percdwn > 100 ? 100 : $percdwn, format_bytes($global['downspeed']).'/s</div>');
		$box .= "</div></div>";

		return $box;
	}
	
	public function renderBoxDiskstats($pos, $anz)
	{
		global $lng;

		$free = disk_free_space($_SESSION['dir']); $total = disk_total_space($_SESSION['dir']); $progress = ($total - $free) / $total * 100;
		$free = format_bytes($free); $total = format_bytes($total);
		
		$box  = "<div class=\"box\" id=\"boxdiskstats\"><h2>{$lng['diskspace']}</h2><div class=\"boxcontent\" id=\"boxdisk\">";
		$box .= "<div>{$lng['freespace']}:<br />$free/$total</div>";
		$box .= progressbar($progress);
		$box .= '</div></div>';

		return $box;
	}

	public function renderBoxBandwithsettings($pos, $anz)
	{
		global $imagedir, $lng, $global, $qsid;

		$upspeed   = intval($global['uplimit']/1024);
		$downspeed = intval($global['downlimit']/1024);
		$box  = "<div class=\"box\" id=\"boxbandwith\"><h2>{$lng['maxspeeds']}</h2><div class=\"boxcontent\">";
		$box .= "<form action=\"control.php$qsid\" method=\"post\">";
		$box .= "<div class=\"label\"><img src=\"{$imagedir}max_up.png\" alt=\"Up\" /></div><div><input type=\"text\" class=\"num\" name=\"maxup\" value=\"$upspeed\" />&nbsp;KB/s</div>";
		$box .= "<div class=\"label\"><img src=\"{$imagedir}max_down.png\" alt=\"Down\" /></div><div><input type=\"text\" class=\"num\" name=\"maxdown\" value=\"$downspeed\" />&nbsp;KB/s</div>";
		$box .= "<input type=\"submit\" class=\"submit\" name=\"maxspeeds\" value=\"{$lng['apply']}\" /></div>";
		$box .= '</form></div>';

		return $box;
	}

	public function renderBoxFilter($pos, $anz)
	{
		global $lng, $qsid, $ftext;

		$box  = "<div class=\"box\" id=\"boxfilter\"><h2>{$lng['filter']}</h2><div class=\"boxcontent\">";
		$box .= "<form action=\"index.php$qsid\" method=\"post\">";
		$box .= "<div><input type=\"text\" name=\"ftext\" class=\"text\" value=\"$ftext\" onkeyup=\"filter( this );\" /></div>";
		$box .= "<div><input type=\"submit\" name=\"fsubmit\" id=\"fsubmit\" class=\"submit\" value=\"{$lng['apply']}\" /></div></form></div></div>";
		
		return $box;
	}

	public function renderBoxRefreshsettings($pos, $anz)
	{
		global $lng, $refresh_arr, $qsid;

		$box  = "<div class=\"box\" id=\"boxrefresh\"><h2>{$lng['refresh']}</h2><div class=\"boxcontent\">";
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
		$box .= "<input type=\"submit\" name=\"refsubmit\" id=\"refsubmit\" class=\"submit\" value=\"{$lng['apply']}\" /></div></form></div></div>";
		
		return $box;
	}

	public function renderBoxServerstats($pos, $anz)
	{
		global $lng, $global;

		$box    = "<div class=\"box\" id=\"boxserver\"><h2>{$lng['serverinfo']}</h2>";
		$box   .= "<div class=\"boxcontent\">rEmote: {$global['versions']['remote']}<br />rtorrent: {$global['versions']['rtorrent']}<br />libtorrent: {$global['versions']['libtorrent']}<hr />";
		if($l = @fopen('/proc/loadavg', 'r'))
		{
			$loads = explode(' ', fgets($l));
			fclose($l);
			$perc = $loads[0] > 1 ? 100 : ($loads[0]*100);
			$box .= "<div id=\"boxload\"><div>{$lng['load']}: {$loads[0]} {$loads[1]} {$loads[2]}</div>".progressbar($perc, $perc.'%').'</div>';
		}
		else
		{
      	$box .= "<div id=\"boxload\">---</div>";
		}	
		$box .= '</div></div>';

		return $box;
	}

	public function renderBoxShoutbox($pos, $anz)
	{
		global $lng;

		$shoutbox = new Shoutbox();

		$width = '';
		if(($pos == BOX_TOP) || ($pos == BOX_BOTTOM))
			$width = ' style="width: '.(100/$anz).'%;"';

		$box  = "<div class=\"box\" id=\"boxshoutbox\"$width><h2>{$lng['shoutbox']}</h2>";
		$box .= "<div class=\"boxcontent\">";
		$box .= $shoutbox->makeShoutbox();
		$box .= '</div></div>';
		
		return $box;
	}
	
	public function renderBoxLinklist($pos, $anz)
	{
		global $lng, $db;
		
		$width = '';
		if(($pos == BOX_TOP) || ($pos == BOX_BOTTOM))
			$width = ' style="width: '.(100/$anz).'%;"';
		
		$box  = "<div class=\"box\" id=\"boxlinklist\"$width><h2>{$lng['linklist']}</h2>";
		$box .= "<div class=\"boxcontent\">";
		
		
		if(false === ($l = cache_get('extlinks')))
		{
			$result = $db->query('SELECT label, url FROM extlinks WHERE uid = ? OR public = 1 ORDER BY label ASC', 'i', $_SESSION['uid']);
			$list = array();
			
			while($h = $db->fetch($result))
				$list[$db->out($h['label'])] = $db->out($h['url']);

			$l = '<ul>';
			foreach($list as $label => $url)
				$l .= "<li><a href=\"$url\" title=\"$label\">$label</a></li>";
			$l .= '</ul>';

			cache_put('extlinks', $l, $_SESSION['uid'], time() + (60*60*24*7));
		}

		$box .= $l.'</div></div>';

   	return $box;
	}
	
	public function renderBoxOnlinelist($pos, $anz)
	{
		global $lng, $db, $settings;
		
		$width = '';
		if(($pos == BOX_TOP) || ($pos == BOX_BOTTOM))
			$width = ' style="width: '.(100/$anz).'%;"';
		
		$box  = "<div class=\"box\" id=\"boxonlinelist\"$width><h2>{$lng['onlinelist']}</h2>";
		$box .= "<div class=\"boxcontent\">";

		$result = $db->query('SELECT DISTINCT u.name FROM users u, sessions s WHERE u.uid = s.uid AND u.uid != ? AND s.time > ?',
			'ii',
			$_SESSION['uid'],
			time() - $settings['display_as_online']);

		if($db->num_rows($result))
		{
			$l = '<ul>';
      	while($h = $db->fetch($result))
				$l .= '<li>'.$db->out($h['name']).'</li>';
			$l .= '</ul>';
		}
		else
			$l = "<span class=\"hint\">{$lng['nobodyon']}</span>";

		$box .= $l.'</div></div>';

   	return $box;
	}


	public function renderBox($boxname, $pos, $anz)
	{
		global $settings;

   	switch($boxname)
		{
			case BoxArea::BOX_SPEEDSTATS:
            return $this->renderBoxSpeedstats($pos, $anz);

			case BoxArea::BOX_DISKSTATS:
            return $this->renderBoxDiskstats($pos, $anz);

			case BoxArea::BOX_BANDWITHSETTINGS:
            return $this->renderBoxBandwithsettings($pos, $anz);

			case BoxArea::BOX_FILTER:
            return $this->renderBoxFilter($pos, $anz);

			case BoxArea::BOX_REFRESHSETTINGS: 
            return $this->renderBoxRefreshsettings($pos, $anz);

			case BoxArea::BOX_SERVERSTATS:
				if(($_SESSION['status'] <= USER) && !$settings['user_see_serverinfo'])
					return '';
            return $this->renderBoxServerstats($pos, $anz);

			case BoxArea::BOX_SHOUTBOX:
				if(!$settings['shoutbox'])
					return '';
            return $this->renderBoxShoutbox($pos, $anz);
			
			case BoxArea::BOX_LINKLIST:
				return $this->renderBoxLinklist($pos, $anz);
			
			case BoxArea::BOX_ONLINELIST:
				return $this->renderBoxOnlinelist($pos, $anz);
		}
	}

	public function renderArea($boxes, $id, $pos)
	{
		$str = '';

   	foreach($boxes as $b)
		{
			$str .= $this->renderBox($b, $pos, count($boxes));
		}

		return "<div class=\"boxarea\" id=\"$id\">$str</div>";
	}
}




?>
