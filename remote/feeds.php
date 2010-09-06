<?php

define('TO_ROOT', './');
define('ACTIVE',  'feeds');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/defines/feeds.php');
require_once(TO_ROOT.'inc/functions/feeds.fun.php');
require_once(TO_ROOT.'inc/functions/torrents.fun.php');
require_once(TO_ROOT.'inc/functions/add.fun.php');
require_once(TO_ROOT.'inc/header.php');


$result = $db->query('SELECT fid, name, download, allowhtml FROM feeds WHERE uid = ?', 'i', $_SESSION['uid']);
if(!$db->num_rows($result))
{
	$out->content = "<div id=\"main\">$header<div id=\"content\"><div class=\"error\">{$lng['norsssrc']}</div></div></div>";
	$out->renderPage($settings['html_title']);
}


$feedarr = array(-1 => array('name' => $lng['choosefeed']));
while($h = $db->fetch($result))
{
	$feedarr[$h['fid']] = array(
							   		'name'      => $h['name'],
							   		'download'  => $h['download'],
										'allowhtml' => $h['allowhtml']);
}


if(isset($_REQUEST['feedid']) && ($_REQUEST['feedid'] >= 0) && isset($feedarr[$_REQUEST['feedid']]))
{
	$feedid    = $_REQUEST['feedid'];
	$download  = ord($feedarr[$feedid]['download']);
	$allowhtml = ord($feedarr[$feedid]['allowhtml']);
}
else
	$feedid = 0;

if($feedid && (isset($_GET['add']) && trim($_GET['add']) != ''))
{
	$dir = $db->one_result($db->query('SELECT directory FROM feeds WHERE fid = ?', 'i', $feedid), 'directory');
	if(!$settings['disable_sem'])
	{
		$sem = sem_get(SEM_KEY);
		if(!sem_acquire($sem))
			fatal("Could not acquire Semaphore!", __FILE__, __LINE__);
	}

	if(($dir !== false) && ($dir != ''))
		set_directory($dir);
	
	$return = get_torrent($_GET['add'], false, false);
	if($return != '')
		$out->addError($return); 
	
	if(!$settings['disable_sem'])
		sem_release($sem);
}

if(isset($_GET['descrid']) && is_numeric($_GET['descrid']))
	$descrid = $_GET['descrid'];
else
	$descrid = -1;

$tabs  = $out->getMessages(Render::ERROR);
$tabs .= "<form action=\"feeds.php$qsid\" name=\"feedselect\" method=\"post\"><div id=\"feedchooser\"><select name=\"feedid\" onchange=\"changeFeed();\">";
foreach($feedarr as $fdid => $feed)
	$tabs .= sprintf('<option value="%d"%s>%s</option>',
		$fdid,
		($feedid == $fdid) ? ' selected="selected"' : '',
		htmlspecialchars($feed['name'], ENT_QUOTES));

//'<li class="'.($feedid == $fdid ? 'viewselon' : 'viewseloff')."\"><a href=\"feeds.php?feedid=$fdid$sid\" title=\"{$lng['readthis']}\">".htmlspecialchars($feed['name'], ENT_QUOTES).'</a></li>';
$tabs .= "</select>&nbsp;<img src=\"{$imagedir}/loading.gif\" alt=\"{$lng['loading']}\" style=\"display: none;\" id=\"loading\" /><input id=\"feedapply\" type=\"submit\" value=\"{$lng['apply']}\" /></div></form>";

if($feedid)
{
	if($items = getItems($feedid))
	{
		$out->jsinfos['feedid'] = $feedid;
		$out->addJSLang('loading');

		$table  = '<table id="rsstable">';
		foreach($items as $itemid => $item)
		{
			$url = "feeds.php?feedid=$feedid&amp;descrid=$itemid$sid";

			if(isset($item['title']['value']))
				$title = htmlspecialchars($item['title']['value'], ENT_QUOTES);
			else
				$title = '[Unavailable]';
			if($item['marked'])
				$title = "<span class=\"highlighted\">$title</span>";
			if(isset($item['link']['value']))
			{
				if($download)
				{
					$link  = "<a href=\"feeds.php?feedid=$feedid&amp;add=".rawurlencode($item['link']['value']).$sid."\" title=\"{$lng['rsstort']}\"><img src=\"{$imagedir}addtort.png\" alt=\"add to rTorrent\" /></a>";
					$link .= "&nbsp;<a href=\"{$item['link']['value']}\" title=\"{$lng['download']}\"><img src=\"{$imagedir}download.png\" alt\"Download\" /></a>";
				}
				else
				{
					$link = "<a href=\"{$item['link']['value']}\" title=\"{$lng['openlink']}\"><img src=\"{$imagedir}openlink.png\" alt\"Open\" /></a>";
					if(isset($item['enclosure']) && isset($item['enclosure']['attributes']['url']))
					{
						if(!isset($item['enclosure']['attributes']['type']) || ($item['enclosure']['attributes']['type'] == 'application/x-bittorrent'))
						{
							$link .= "&nbsp;<a href=\"feeds.php?feedid=$feedid&amp;add=".rawurlencode($item['enclosure']['attributes']['url']).$sid."\" title=\"{$lng['rsstort']}\"><img src=\"{$imagedir}addtort.png\" alt=\"add to rTorrent\" /></a>";
							$link .= "&nbsp;<a href=\"{$item['enclosure']['attributes']['url']}\" title=\"{$lng['download']}\"><img src=\"{$imagedir}download.png\" alt\"Download\" /></a>";
				   	}
					}
				}
			}
			else
				$link = '';
			if(isset($item['description']['value']) && ($descrid == $itemid))
			{
				if($allowhtml)
					$descr = '<br />'.$item['description']['value'];
				else
					$descr = '<br />'.str_replace("\n", '<br />', htmlspecialchars($item['description']['value']));
				$url = "feeds.php?feedid=$feedid$sid";
			}
			else
				$descr = '';
			if(isset($item['pubDate']['value']))
				$date = date('d.m.Y H:i:s', strtotime($item['pubDate']['value']));
			else
				$date = '';
			$table .= "<tr><td class=\"rsstitle\"><a href=\"$url\" onclick=\"return getDescr( $itemid, this );\" title=\"{$lng['showdetails']}\">$title</a>$descr</td><td class=\"rssdate\">$date</td><td class=\"actions\">$link</td></tr>";
		}
		$table .= '</table>';
		$tabcontent = $table;
	}
	else
		$tabcontent = "<div class=\"error\">{$lng['couldntread']}</div>";
}
else
	$tabcontent = "<div class=\"notify\">{$lng['choosefeed']}</div>";

$out->javascripts[] = 'js/feeds.js';
$out->bodyonload = 'invisible();';
if(addJobChecker())
	$m = $out->getMessages();
else
	$m = '';
$out->content = "<div id=\"main\">$header<div id=\"content\">$m$tabs<div class=\"tabsbody\">$tabcontent</div></div></div>";
$out->renderPage($settings['html_title']);


?>
