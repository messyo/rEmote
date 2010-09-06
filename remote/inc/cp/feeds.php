<?php

if(!defined('IN_CP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

require_once(TO_ROOT.'inc/defines/feeds.php');

$fields_arr = array( 1 => 'title',     2 => 'description', 3 => 'titleanddes' );
$func_arr   = array( 1 => 'highlight', 2 => 'downact',     3 => 'highanddown' );
$regex_arr  = array( 'plain', 'regex' );
$feeds_arr  = array( $lng['all'] );
$result = $db->query('SELECT fid, name FROM feeds WHERE uid = ?', 'i', $_SESSION['uid']);
while($h = $db->fetch($result))
	$feeds_arr[$h['fid']] = htmlspecialchars($h['name'], ENT_QUOTES);



if(isset($_GET['edit']) && is_numeric($_GET['edit']))
	$edit = $_GET['edit'];
else
	$edit = 0;

if(isset($_REQUEST['field']) && (($_REQUEST['field'] == 'rule') || ($_REQUEST['field'] == 'feed')))
	$field = $_REQUEST['field'];
else
	$field = '';


$v_name  = '';
$v_url   = '';
$v_dir   = '';
$v_int   = 0;
$v_down  = 0;
$v_html  = 0;
$v_feeds = '';
$v_expr  = '';
$v_sin   = 3;
$v_regex = 0;
$v_func  = 0;

if(isset($_GET['kill']) && is_numeric($_GET['kill']))
{
	if($field == 'feed')
	{
		if(false !== ($h = $db->one_result($db->query('SELECT name FROM feeds WHERE fid = ? AND uid = ?', 'ii', $_GET['kill'], $_SESSION['uid']))))
			$out->addNotify(makeSecQuery(lng('feedconfirm', $h), $mod, array('kill' => $_GET['kill'], 'field' => $field)));
		else
			$out->addError($lng['couldntread']);
	}
	else
	{
		if(false !== ($h = $db->one_result($db->query('SELECT name FROM highlightrules WHERE hid = ? AND uid = ?', 'ii', $_GET['kill'], $_SESSION['uid']))))
			$out->addNotify(makeSecQuery(lng('highconfirm', $h), $mod, array('kill' => $_GET['kill'], 'field' => $field)));
		else
			$out->addError($lng['couldntread']);
	}
}
else if(isset($_POST['confirm']) && isset($_POST['kill']) && is_numeric($_POST['kill']))
{
	if($field == 'feed')
	{
		$db->query('DELETE FROM feeds WHERE fid = ? AND uid = ?', 'ii', $_POST['kill'], $_SESSION['uid']);
		if($db->affected_rows())
			$out->addSuccess($lng['feeddeleted']);
		else
			$out->addError($lng['notdeleted']);
	}
	else
	{
		$db->query('DELETE FROM highlightrules WHERE hid = ? AND uid = ?', 'ii', $_POST['kill'], $_SESSION['uid']);
		if($db->affected_rows())
			$out->addSuccess($lng['highdeleted']);
		else
			$out->addError($lng['notdeleted']);
	}
}


if(isset($_POST['save']))
{
	if($field == 'feed')
	{
		$v_name = $_POST['feedname'];
		$v_url  = $_POST['feedurl'];
		$v_dir  = $_POST['feeddir'];
		$v_int  = intval($_POST['feedint']);
		if(isset($_POST['feeddown']) && ($_POST['feeddown'] == 'true'))
			$v_down = 1;
		if(isset($_POST['feedhtml']) && ($_POST['feedhtml'] == 'true'))
			$v_html = 1;


		if(trim($v_name) == '')
			$out->addError($lng['invalidname']);
		if(trim($v_url) == '')
			$out->addError($lng['invalidurl']);
		if($v_int <= 0)
			$out->addError($lng['invalidint']);

		if(!$out->hasError())
		{
			if($edit)
				$db->query('UPDATE feeds SET name = ?, url = ?, directory = ?, `interval` = ?, download = ?, allowhtml = ? WHERE fid = ? AND uid = ?',
									'sssiiiii',
									$v_name,
									$v_url,
									$v_dir,
									$v_int*60,
									$v_down,
									$v_html,
									$edit,
									$_SESSION['uid']);
			else
				$db->query('INSERT INTO feeds (name, url, directory, `interval`, download, allowhtml, uid) VALUES (?, ?, ?, ?, ?, ?, ?)',
									'sssiiii',
									$v_name,
									$v_url,
									$v_dir,
									$v_int*60,
									$v_down,
									$v_html,
									$_SESSION['uid']);
		}
	}
	else
	{
		$v_name  = $_POST['rulename'];
		$v_feeds = $_POST['rulefeeds'];
		$v_expr  = $_POST['ruleexpr'];
		$v_sin   = $_POST['rulesin'];
		$v_func  = $_POST['rulefunc'];
		if(isset($_POST['ruleregex']) && ($_POST['ruleregex'] == 'true'))
			$v_regex = 1;


		if(trim($v_name) == '')
			$out->addError($lng['invalidname']);
		if($v_expr == '')
			$out->addError($lng['invalidexpr']);
		if(!array_key_exists($v_feeds, $feeds_arr))
			$out->addError($lng['internerror']);
		if(!array_key_exists($v_sin, $fields_arr))
			$out->addError($lng['internerror']);
		if(!array_key_exists($v_func, $func_arr))
			$out->addError($lng['internerror']);



		if(!$out->hasError())
		{
			if($edit)
				$db->query('UPDATE highlightrules SET name = ?, fid = ?, expression = ?, fields = ?, regex = ?, function = ? WHERE hid = ? AND uid = ?',
									'sisiiiii',
									$v_name ,
									$v_feeds,
									$v_expr ,
									$v_sin  ,
									$v_regex,
									$v_func ,
									$edit,
									$_SESSION['uid']);
			else
				$db->query('INSERT INTO highlightrules (name, fid, expression, fields, regex, function, uid) VALUES (?, ?, ?, ?, ?, ?, ?)',
									'sisiiii',
									$v_name ,
									$v_feeds,
									$v_expr ,
									$v_sin  ,
									$v_regex,
									$v_func ,
									$_SESSION['uid']);
		}
	}

	if(!$out->hasError())
	{
		if($db->affected_rows())
		{
			$success = $lng['saved'];
			$edit   = 0;
			$v_name = '';
			$v_url  = '';
			$v_dir  = '';
			$v_int  = 0;
			$v_down = 0;
			$v_html = 0;
			$v_feeds = '';
			$v_expr  = '';
			$v_sin   = 3;
			$v_regex = 0;
			$v_func  = 0;
			$field  = '';
		}
		else
			$out->addError($lng['notsaved']);
	}	
}
else if($edit)
{
	if($field == 'rule')
		$qry = 'SELECT name, fid, expression, fields, regex, function FROM highlightrules WHERE hid = ? AND uid = ?';
	else
		$qry = 'SELECT name, url, directory, `interval`, download, allowhtml FROM feeds WHERE fid = ? AND uid = ?';
	
	if($h = $db->fetch($db->query($qry, 'ii', $edit, $_SESSION['uid'])))
	{
		$v_name = $db->out($h['name']);
		if($field == 'rule')
		{
			$v_feeds = $h['fid'];
			$v_expr  = $db->out($h['expression']);
			$v_sin   = $h['fields'];
			$v_func  = $h['function'];
			$v_regex = ord($h['regex']);
		}
		else
		{
			$v_url  = $db->out($h['url']);
			$v_dir  = $db->out($h['directory']);
			$v_int  = intval($h['interval'])/60;
			$v_down = ord($h['download']);
			$v_html = ord($h['allowhtml']);
		}
	}
	else
	{
		$error = $lng['couldntread'];
		$edit  = 0;
		$field = '';
	}
}

$message = $out->getMessages();

$new = '';
if(($field != '') && !isset($_GET['kill']))
{
	if($field == 'feed')
	{
		$new .= '<fieldset class="box" id="newfeed"><legend>'.($edit ? $lng['editfeed'] : $lng['newfeed']).'</legend>';
		$new .= $edit ? "<form action=\"controlpanel.php?mod=$mod&amp;edit=$edit&amp;field=$field$sid\" method=\"post\">" : "<form action=\"controlpanel.php?mod=$mod&amp;field=$field$sid\" method=\"post\">";
		$new .= '<table id="newfeed">';
		$new .= "<tr><td><label for=\"feedname\">{$lng['name']}</label></td><td><input id=\"feedname\" type=\"text\" class=\"text\" name=\"feedname\" value=\"$v_name\" /></td></tr>";
		$new .= "<tr><td><label for=\"feedurl\">{$lng['url']}</label></td><td><input id=\"feedurl\" type=\"text\" class=\"longtext\" name=\"feedurl\" value=\"$v_url\" /></td></tr>";
		$new .= "<tr><td><label for=\"feeddir\">{$lng['dir']}</label></td><td><input id=\"feeddir\" type=\"text\" class=\"longtext\" name=\"feeddir\" value=\"$v_dir\" /></td></tr>";
		$new .= "<tr><td><label for=\"feedint\">{$lng['interval']}</label></td><td><input id=\"feedint\" type=\"text\" class=\"num\" name=\"feedint\" value=\"$v_int\" />&nbsp;{$lng['minutes']}</td></tr>";
		$new .= "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"feeddown\" name=\"feeddown\" value=\"true\" ".($v_down ? ' checked="checked"' : '')."/>&nbsp;<label for=\"feeddown\">{$lng['feedlongdld']}</label></td></tr>";
		$new .= "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"feedhtml\" name=\"feedhtml\" value=\"true\" ".($v_html ? ' checked="checked"' : '')."/>&nbsp;<label for=\"feedhtml\">{$lng['feedlonghtm']}</label></td></tr>";
		
		$new .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"save\" value=\"".($edit ? $lng['save'] : $lng['add'] )."\" /></td></tr>";
		$new .= '</table></form></fieldset>';
	}
	else if($field == 'rule')
	{
		$feedoptions = $fieldoptions = $funcoptions = '';

		foreach($feeds_arr as $k => $v)
		{
			$checked = ($k == $v_feeds) ? ' selected="selected"' : '';
			$feedoptions .= "<option value=\"$k\"$checked>$v</option>";
		}
		foreach($fields_arr as $k => $v)
		{
			$checked = ($k == $v_sin) ? ' selected="selected"' : '';
			$fieldoptions .= "<option value=\"$k\"$checked>{$lng[$v]}</option>";
		}
		foreach($func_arr as $k => $v)
		{
			$checked = ($k == $v_func) ? ' selected="selected"' : '';
			$funcoptions .= "<option value=\"$k\"$checked>{$lng[$v]}</option>";
		}

		$new .= '<fieldset class="box" id="newfeed"><legend>'.($edit ? $lng['edithigh'] : $lng['newhigh']).'</legend>';
		$new .= $edit ? "<form action=\"controlpanel.php?mod=$mod&amp;edit=$edit&amp;field=$field$sid\" method=\"post\">" : "<form action=\"controlpanel.php?mod=$mod$sid\" method=\"post\">";
		$new .= '<table id="newfeed">';
		$new .= "<tr><td><label for=\"rulename\">{$lng['name']}</label></td><td><input id=\"rulename\" type=\"text\" class=\"text\" name=\"rulename\" value=\"$v_name\" /></td></tr>";
		$new .= "<tr><td><label for=\"ruleexpr\">{$lng['expression']}</label></td><td><input id=\"ruleexpr\" type=\"text\" class=\"text\" name=\"ruleexpr\" value=\"$v_expr\" /></td></tr>";
		$new .= "<tr><td><label for=\"rulefeeds\">{$lng['feeds']}</label></td><td><select name=\"rulefeeds\">$feedoptions</select></td></tr>";
		$new .= "<tr><td><label for=\"rulesin\">{$lng['lookin']}</label></td><td><select name=\"rulesin\">$fieldoptions</select></td></tr>";
		$new .= "<tr><td><label for=\"rulefunc\">{$lng['actions']}</label></td><td><select name=\"rulefunc\">$funcoptions</select></td></tr>";
		$new .= "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"ruleregex\" name=\"ruleregex\" value=\"true\" ".($v_regex ? ' checked="checked"' : '')."/>&nbsp;<label for=\"ruleregex\">{$lng['regex']}</label></td></tr>";
		
		$new .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"save\" value=\"".($edit ? $lng['save'] : $lng['add'] )."\" /></td></tr>";
		$new .= '</table></form></fieldset>';
	}	
}

$result = $db->query('SELECT fid, name, url, directory, `interval`, download, allowhtml FROM feeds WHERE uid = ?', 'i', $_SESSION['uid']);

$feedstable  = "<table id=\"feedstable\"><thead><tr><td class=\"tableheadline\" colspan=\"7\"><h2>{$lng['feeds']}</h2></td></tr>";
$feedstable .= "<tr><td>{$lng['name']}</td><td>{$lng['url']}</td><td>{$lng['dir']}</td><td>{$lng['interval']}</td><td>{$lng['feeddownld']}</td><td>{$lng['allowhtml']}</td><td>&nbsp;</td></tr></thead>";
while($h = $db->fetch($result))
{
	$name = htmlspecialchars($h['name'], ENT_QUOTES);
	$row = "<tr><td>$name</td>";
	$url = $h['url'];
	$url = maxlength($url ,72);
	$url = htmlspecialchars($url, ENT_QUOTES);
	
	$dir = $h['directory'];
	$dir = maxlength($dir, 72);
	$dir = htmlspecialchars($dir, ENT_QUOTES);
	
	$row .= "<td>$url</td>";
	$row .= "<td>$dir</td><td>".($h['interval']/60).'&nbsp;'.$lng['minutes'].'</td>';
	if(ord($h['download']))
		$img1 = "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />";
	else
		$img1 = "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
	if(ord($h['allowhtml']))
		$img2 = "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />";
	else
		$img2 = "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
	$row .= "<td>$img1</td><td>$img2</td>";
	$row .= "<td><a href=\"controlpanel.php?mod=$mod&amp;field=feed&amp;edit={$h['fid']}$sid\" title=\"{$lng['editfeed']}\"><img src=\"{$imagedir}edit.png\" alt=\"Edit\" /></a>";
	$row .= "&nbsp;<a href=\"controlpanel.php?mod=$mod&amp;field=feed&amp;kill={$h['fid']}$sid\" title=\"{$lng['killfeed']}\"><img src=\"{$imagedir}delete.png\" alt=\"Delete\" /></td></tr>";
	$feedstable .= $row;
}
$feedstable .= "</table>";


$highlighttable  = "<table id=\"highlighttable\"><thead><tr><td class=\"tableheadline\" colspan=\"9\"><h2>{$lng['highlights']}</h2></td></tr>";
$highlighttable .= "<tr><td>{$lng['name']}</td><td>{$lng['feeds']}</td><td>{$lng['expression']}</td><td>{$lng['usetitle']}</td><td>{$lng['usedescr']}</td><td>{$lng['regex']}</td>";
$highlighttable .= "<td>{$lng['highlight']}</td><td>{$lng['download']}</td><td>&nbsp;</td></tr></thead>";
$result = $db->query('SELECT hid, fid, name, expression, fields, regex, function FROM highlightrules WHERE uid = ?', 'i', $_SESSION['uid']);
if($db->num_rows($result))
{
	while($h = $db->fetch($result))
	{
		$name       = htmlspecialchars($h['name'],       ENT_QUOTES);
		$fid        = $feeds_arr[$h['fid']];
		$expression = htmlspecialchars($h['expression'], ENT_QUOTES);
		$title      = (intval($h['fields']) & FEEDTITLE) ?  "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />" : "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
		$descr      = (intval($h['fields']) & FEEDDESCR) ?  "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />" : "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
		$regex      = ord($h['regex']) ? "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />" : "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
		$highlight  = (intval($h['function']) & FEEDHIGHLIGHT) ?  "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />" : "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
		$download   = (intval($h['function']) & FEEDDOWNLOAD ) ?  "<img src=\"{$imagedir}yes.png\" alt=\"yes\" />" : "<img src=\"{$imagedir}no.png\" alt=\"no\" />";
		
		$row  = "<tr><td>$name</td>";
		$row .= "<td>$fid</td>";
		$row .= "<td>$expression</td>";
		$row .= "<td>$title</td>";
		$row .= "<td>$descr</td>";
		$row .= "<td>$regex</td>";
		$row .= "<td>$highlight</td>";
		$row .= "<td>$download</td>";

		$row .= "<td><a href=\"controlpanel.php?mod=$mod&amp;field=rule&amp;edit={$h['hid']}$sid\" title=\"{$lng['editfeed']}\"><img src=\"{$imagedir}edit.png\" alt=\"Edit\" /></a>";
		$row .= "&nbsp;<a href=\"controlpanel.php?mod=$mod&amp;field=rule&amp;kill={$h['hid']}$sid\" title=\"{$lng['killfeed']}\"><img src=\"{$imagedir}delete.png\" alt=\"Delete\" /></td></tr>";

		$highlighttable .= $row;
	}
}
else
	$highlighttable .= "<tr><td colspan=\"8\"><em>{$lng['norules']}</em></td></tr>";
$highlighttable .= '</table>';

$addfeed = "<a class=\"addoption\" href=\"controlpanel.php?mod=$mod&amp;field=feed$sid\" title=\"{$lng['newfeed']}\"><!--<img src=\"{$imagedir}add.png\" alt=\"add\" />&nbsp;-->{$lng['add']}</a>";
$addrule = "<a class=\"addoption\" href=\"controlpanel.php?mod=$mod&amp;field=rule$sid\" title=\"{$lng['newhigh']}\"><!--<img src=\"{$imagedir}add.png\" alt=\"add\" />&nbsp;-->{$lng['add']}</a>";


$cpout = "$message$new$feedstable$addfeed$highlighttable$addrule";



?>
