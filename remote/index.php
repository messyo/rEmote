<?php

define('TO_ROOT', './');
define('ACTIVE',  'torrents');

require_once('inc/global.php');
require_once('inc/defines/torrent.php');
require_once('inc/functions/list.fun.php');
require_once('inc/header.php');
require_once('inc/boxarea.php');
require_once('inc/template.php');
if($settings['real_multiuser'])
	require_once('inc/functions/torrents.fun.php');

$content = '';
if($settings['usermessage'] != '')
	$content .= "<div class=\"notify\">{$settings['usermessage']}</div>";


$views = '<select name="view">';
foreach($view_arr as $n => $v)
{
	if($n == $_SESSION['viewmode'])
		$views .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
	else
		$views .= "<option value=\"$n\">{$lng[$v]}</option>";
}
$views .= '</select>';
$group  = '<select name="group">';
foreach($group_arr as $n => $v)
{
	if($n == $_SESSION['groupmode'])
		$group .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
	else
		$group .= "<option value=\"$n\">{$lng[$v]}</option>";
}
$group .= '</select>';
if($settings['real_multiuser'])
{
	$source  = $lng['from'].' <select name="source">';
	foreach($source_arr as $n => $v)
	{
		if($n == $_SESSION['sourcemode'])
			$source .= "<option value=\"$n\" selected=\"selected\">{$lng[$v]}</option>";
		else
			$source .= "<option value=\"$n\">{$lng[$v]}</option>";
	}
	$source .= '</select>';
}
else
	$source = '';

$submit = '';

if($_SESSION['detailsstyle'] > 0)
{
	$out->jsinfos['detailsstyle']   = $_SESSION['detailsstyle'];
	$out->jsinfos['detailsdefmode'] = $settings['details_def_mode'];
	$dlink = '<a onclick="return popupfun( this );" href="details.php?hash=%s%s" title="%s"%s>%s</a>';
	if($_SESSION['detailsstyle'] > 1)
	{
		$out->jsinfos['numcolumns']    = $numcolumns;
		$out->addJSLang('loading', 'filetree', 'filelist', 'infos', 'tracker', 'peers', 'close', 'delconfirm', 'yes', 'no');
	}
}
else
	$dlink = '<a href="details.php?hash=%s%s" title="%s"%s>%s</a>';

/* Build Linkvariables for Table-Head */
$v = array('lngspeed' => $lng['speed']);

$v['l_name']      = '<a href="control.php?sort='.NAME            ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['name']}\">{$lng['name']}</a>";
$v['l_done']      = '<a href="control.php?sort='.PERCENT_COMPLETE."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['done']}\">{$lng['done']}</a>";
$v['l_eta']       = '<a href="control.php?sort='.ETA             ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['eta']}\">{$lng['eta']}</a>";
$v['l_sup']       = '<a href="control.php?sort='.UP_RATE         ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['sup']}\">{$lng['sup']}</a>";
$v['l_sdwn']      = '<a href="control.php?sort='.DOWN_RATE       ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['sdwn']}\">{$lng['sdwn']}</a>";
$v['l_seeded']    = '<a href="control.php?sort='.UP_TOTAL        ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['seeded']}\">{$lng['seeded']}</a>";
$v['l_completed'] = '<a href="control.php?sort='.COMPLETED_BYTES ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['completed']}\">{$lng['completed']}</a>";
$v['l_size']      = '<a href="control.php?sort='.SIZE_BYTES      ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['size']}\">{$lng['size']}</a>";
$v['l_peers']     = '<a href="control.php?sort='.PEERS_CONNECTED ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['peers']}\">{$lng['peers']}</a>";
$v['l_ratio']     = '<a href="control.php?sort='.RATIO           ."&amp;return=index$sid\" title=\"{$lng['orderby']} {$lng['ratio']}\">{$lng['ratio']}</a>";
$v['l_check']     = '';

if($_SESSION['sortkey'] == NAME)
	$v['l_name'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == PERCENT_COMPLETE)
	$v['l_done'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == ETA)
	$v['l_eta'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == UP_RATE)
	$v['l_sup'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == DOWN_RATE)
	$v['l_sdwn'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == UP_TOTAL)
	$v['l_seeded'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == COMPLETED_BYTES)
	$v['l_completed'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == SIZE_BYTES)
	$v['l_size'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == PEERS_CONNECTED)
	$v['l_peers'] .= $sorticons[$_SESSION['sortord']];
else if($_SESSION['sortkey'] == RATIO)
	$v['l_ratio'] .= $sorticons[$_SESSION['sortord']];

if(isset($_POST['fsubmit']) && isset($_POST['ftext']) && trim($_POST['ftext']) != '')
{
	$filter = true;
	$ftext  = strtolower(trim($_POST['ftext']));
}
else
	$filter = false;


/* End */

$l_lstop    = "<input type=\"image\" name=\"multistop\" src=\"{$imagedir}stop.png\" alt=\"{$lng['stop']}\" />";
$l_lstart   = "<input type=\"image\" name=\"multistart\" src=\"{$imagedir}start.png\" alt=\"{$lng['start']}\" />";
$l_ldelete  = "<input type=\"image\" name=\"multidelete\" src=\"{$imagedir}delete.png\" alt=\"{$lng['delete']}\" onclick=\"return delConfirm();\" />";
$l_lhash    = "<input type=\"image\" name=\"multihash\" src=\"{$imagedir}hash.png\" alt=\"{$lng['hash']}\" onclick=\"return confirm('{$lng['mhashconf']}');\" />";
$multilinks  = "$l_lstop&nbsp;$l_lstart&nbsp;$l_ldelete&nbsp;$l_lhash&nbsp;&nbsp;&nbsp;";
$multilinks .= "<span class=\"multimasker\"><img src=\"{$imagedir}check.png\" alt=\"Checkall\" onclick=\"checkall( true );\" />&nbsp;<img src=\"{$imagedir}uncheck.png\" alt=\"uncheck\" onclick=\"checkall( false );\" />&nbsp;&nbsp;&nbsp;</span>";
$multilinks .= "<img src=\"{$imagedir}arrow.png\" alt=\"&crarr;\" />";

$viewoptions = "{$lng['show']} $views $source {$lng['groupby']} $group <input type=\"submit\" class=\"submit\" name=\"viewchange\" value=\"{$lng['viewchange']}\" />";
$table  = "<form name=\"controls\" action=\"control.php?return=index$sid\" method=\"post\"><table id=\"torrenttable\"><thead><tr><td colspan=\"$numcolumns\" class=\"tableheadline\"><h2>{$lng['torrents']}</h2><div class=\"multilinks\">$multilinks</div><div id=\"tshow\">$viewoptions</div></td></tr>";
//eval("\$table .= \"$listhead</thead>\";");
$table .= Template::quickparse($listhead, $v);
$table .= '</thead>';

$tablebody = '';
$t_count = 0;
$sortfun = 'sort_torrents_'.$_SESSION['sortord'];
if(!function_exists($sortfun))
{
	logger('LOGERROR', 'Invalid Sortfunction: '.$sortfun, __FILE__, __LINE__);
	$sortfun = 'sort_torrents_ASC';
}
if($data = get_full_list($_SESSION['viewmode'], $_SESSION['groupmode'], $_SESSION['sourcemode']))
{
	$multiselectoffset = 0;
	$multiselectend    = 0;

	$bodyline = new Template($listbody);
	$bodyline->bindOrder(array('l_hash',
		'l_status',
		'l_statuskey',
		'l_statusimg',
		'l_name',
		'l_done',
		'l_eta',
		'l_sup',
		'l_sdwn',
		'l_seeded',
		'l_completed',
		'l_size',
		'l_peers',
		'l_ratio',
		'l_message',
		'l_check',
		'l_lstopstart',
		'l_ldelete',
		'l_lhash',
		'l_ldetails',
		'l_lfilebrowser',
		'l_links',
		'l_even'));

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

		usort($group, $sortfun);
		if($_SESSION['groupmode'])
		{
			$multiselectend = $multiselectoffset + count($group);
			$multiselecticons = "<span class=\"multimasker groupmasker\"><img src=\"{$imagedir}check.png\" alt=\"Checkall\" onclick=\"checkrange( true , $multiselectoffset, $multiselectend );\" />&nbsp;<img src=\"{$imagedir}uncheck.png\" alt=\"uncheck\" onclick=\"checkrange( false , $multiselectoffset , $multiselectend );\" />&nbsp;&nbsp;&nbsp;</span>";
			$multiselectoffset = $multiselectend;
			if($_SESSION['groupmode'] == 1)
				$grpct = "<tr><td class=\"groupheader\" colspan=\"$numcolumns\">$multiselecticons<h2>$groupid</h2></td></tr>";                  // Group by Tracker
			else if($_SESSION['groupmode'] == 2)
				$grpct = "<tr><td class=\"groupheader\" colspan=\"$numcolumns\">$multiselecticons<h2>{$lng["status$groupid"]}</h2></td></tr>";  // Group by Status
			else if($_SESSION['groupmode'] == 5)
				$grpct = "<tr><td class=\"groupheader\" colspan=\"$numcolumns\">$multiselecticons<h2>".getUsername($groupid).'</h2></td></tr>'; // Group by User
			else
				$grpct = "<tr><td class=\"groupheader\" colspan=\"$numcolumns\">$multiselecticons<h2>{$lng[$groupid]}</h2></td></tr>";          // Group by Message || Traffic
		}
		else
			$grpct = '';
		foreach($group as $item)
		{
			$v = array();
			if($filter && (strpos(strtolower($item[NAME]), $ftext)) === false)
				continue;
			$v['l_hash']      = $item[HASH];
			$v['l_status']    = $item[STATUS];
			$v['l_statuskey'] = $lng["status{$v['l_status']}"];
			$v['l_statusimg'] = "<img src=\"{$imagedir}status_{$v['l_status']}.png\" alt=\"{$v['l_statuskey']}\" />";
			$v['l_name']      = sprintf($dlink, $v['l_hash'], $sid, $lng['viewdetls'], " name=\"{$v['l_hash']}\"", replace_latin1($item[NAME]));
			$v['l_done']      = progressbar($item[PERCENT_COMPLETE], $item[PERCENT_COMPLETE].'%');
			$v['l_eta']       = $item[ETA];
			$v['l_sup']       = ($item[UP_RATE] ? ('<span class="speedhighlight">'.format_bytes($item[UP_RATE]).'/s</span>') : format_bytes($item[UP_RATE]).'/s');
			$v['l_sdwn']      = ($item[DOWN_RATE] ? ('<span class="speedhighlight">'.format_bytes($item[DOWN_RATE]).'/s</span>') : format_bytes($item[DOWN_RATE]).'/s');
			$v['l_seeded']    = format_bytes($item[UP_TOTAL]);
			$v['l_completed'] = format_bytes($item[COMPLETED_BYTES]);
			$v['l_size']      = format_bytes($item[SIZE_BYTES]);
			$v['l_peers']     = "{$item[PEERS_CONNECTED]}/{$item[PEERS_NOT_CONNECTED]} ({$item[PEERS_COMPLETE]})";
			$v['l_ratio']     = round($item[RATIO]/1000, 2);
			$v['l_message']   = $item[MESSAGE];
			if($v['l_ratio'] < 1)
				$v['l_ratio'] = '<span style="color: #'.dechex(255-($v['l_ratio']*255))."0000;\">{$v['l_ratio']}</span>";
			$v['l_check']     = "<input type=\"checkbox\" class=\"checkbox\" name=\"multiselect[]\" value=\"{$v['l_hash']}\" />";

			$t_count     += 1;
			$t_done      += $item[PERCENT_COMPLETE];
			$t_sup       += $item[UP_RATE];
			$t_sdwn      += $item[DOWN_RATE];
			$t_seeded    += $item[UP_TOTAL];
			$t_completed += $item[COMPLETED_BYTES];
			$t_size      += $item[SIZE_BYTES];
			$t_ratio     += $item[RATIO];

			if($item[IS_ACTIVE])
				$v['l_lstopstart'] = "<a href=\"control.php?ctl=stop&amp;hash={$v['l_hash']}&amp;return=torrent$sid\" title=\"{$lng['stopthis']}\"><img src=\"{$imagedir}stop.png\" alt=\"{$lng['stop']}\" /></a>";
			else
				$v['l_lstopstart'] = "<a href=\"control.php?ctl=start&amp;hash={$v['l_hash']}&amp;return=torrent$sid\" title=\"{$lng['startthis']}\"><img src=\"{$imagedir}start.png\" alt=\"{$lng['start']}\" /></a>";
			$v['l_ldelete'] = "<a href=\"control.php?ctl=delete&amp;hash={$v['l_hash']}&amp;return=torrent$sid\" onclick=\"return showConfirm( this , 'del' );\" title=\"{$lng['deletethis']}\"><img src=\"{$imagedir}delete.png\" alt=\"{$lng['delete']}\" /></a>";
			$v['l_lhash'] = "<a href=\"control.php?ctl=hash&amp;hash={$v['l_hash']}&amp;return=torrent$sid\" title=\"{$lng['hashthis']}\"><img src=\"{$imagedir}hash.png\" alt=\"{$lng['hash']}\" /></a>";
			$v['l_ldetails'] = sprintf($dlink, $v['l_hash'], $sid, $lng['viewdetls'], '', "<img src=\"{$imagedir}view.png\" alt=\"{$lng['details']}\" />");
			$v['l_lfilebrowser'] = "<a href=\"filebrowser.php?change_dir=".rawurlencode($item[GET_DIRECTORY])."$sid\" title=\"{$lng['gotodir']}\"><img src=\"{$imagedir}folder.png\" alt=\"{$lng['gotodir']}\" /></a>";

			$v['l_links']     = sprintf('%s&nbsp;%s&nbsp;%s&nbsp;%s&nbsp;%s', $v['l_lstopstart'], $v['l_ldelete'], $v['l_lhash'], $v['l_ldetails'], $v['l_lfilebrowser']);
			$v['l_even']      = $t_count%2;

			$grpct .= $bodyline->renderOrdered($v);
		}

		$v = array();
		$v['l_count']     = $t_count;
		$v['l_done']      = $t_count > 0 ? round($t_done/$t_count, 2).'%' : 0;
		$v['l_sup']       = ($t_sup  ? ('<span class="speedhighlight">'.format_bytes($t_sup).'/s</span>') : format_bytes($t_sup).'/s');
		$v['l_sdwn']      = ($t_sdwn ? ('<span class="speedhighlight">'.format_bytes($t_sdwn).'/s</span>') : format_bytes($t_sdwn).'/s');
		$v['l_speeds']    = $v['l_sup'].'/'.$v['l_sdwn'];
		$v['l_seeded']    = format_bytes($t_seeded);
		$v['l_completed'] = format_bytes($t_completed);
		$v['l_size']      = format_bytes($t_size);
		$v['l_ratio']     = (($t_count > 0) && ($t_completed > 0 )) ? round(($t_seeded/$t_completed), 2) : 0;
		$v['l_groupid']   = $groupid;
		if($v['l_ratio'] < 1)
			$v['l_ratio'] = '<span style="color: #'.dechex(255-($v['l_ratio']*255))."0000;\">{$v['l_ratio']}</span>";
		$summary = Template::quickparse($listfoot, $v);
		$tablebody .= $grpct.$summary;
	}
	$table .= $tablebody;
}
else
{
	$table .= "<tr><td colspan=\"$numcolumns\"><div class=\"notify\">{$lng['notorrents']}</div></tr>";
}

$boxArea = new BoxArea();

$shoutbox_top    = '';
$shoutbox_bottom = '';
if($settings['shoutbox'] && ($_SESSION['shoutbox'] > 1))
{
	$shoutbox = new Shoutbox();
	if($_SESSION['shoutbox'] == 2)
		$shoutbox_top    = "<fieldset class=\"box\"><legend>{$lng['shoutbox']}</legend>".$shoutbox->makeShoutbox()."</fieldset>";
	else
		$shoutbox_bottom = "<fieldset class=\"box\"><legend>{$lng['shoutbox']}</legend>".$shoutbox->makeShoutbox()."</fieldset>";
}

$content .= "$table<tr><td colspan=\"$numcolumns\"><div class=\"multilinks\">$multilinks</div></td></tr></table></form>";

$sidebar = $boxareatop = $boxareabottom = $sidebarclass = '';
if(count($_SESSION['boxpositions'][0]))
{
	$sidebar       = $boxArea->renderArea($_SESSION['boxpositions'][0], 'sidebar');
	$sidebarclass  = ' withsidebar';
}	
if(count($_SESSION['boxpositions'][1]))
	$boxareatop    = $boxArea->renderArea($_SESSION['boxpositions'][1], 'boxareatop');
if(count($_SESSION['boxpositions'][2]))
	$boxareabottom = $boxArea->renderArea($_SESSION['boxpositions'][2], 'boxareabottom');


if(addJobChecker())
	$m = $out->getMessages();
else
	$m = '';
$out->content = "<!-- loggedin --><div id=\"main\">$header$sidebar<div id=\"content\" class=\"contentoftable$sidebarclass\">$m$boxareatop$content$boxareabottom</div></div>";

$out->jsinfos['trows']    = "$trows";
$out->jsinfos['refreshinterval'] = $_SESSION['refinterval'];

$out->addJavascripts('js/details.js', 'js/index.js');
if($_SESSION['refmode'] > 1)
	$out->addJavascripts('js/refresh.js');
else if($_SESSION['refmode'] == 1)
{
	$out->metas['refresh'] = "{$_SESSION['refinterval']}; url=index.php$qsid";
	define('IS_REFRESH', true);
}

//echo '<div class="framebox" ><div><div>Testtext</div></div></div>';
$out->renderPage($settings['html_title']);

?>
