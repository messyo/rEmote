<?php

define('TO_ROOT', './');
define('ACTIVE',  'add');

require_once('inc/global.php');
require_once('inc/functions/add.fun.php');
require_once('inc/functions/torrents.fun.php');
require_once('inc/header.php');

$diroptions_arr = array('dirnochange', 'dirchngonce', 'dirchngperm');

logger(LOGDEBUG, 'Called add.php', __FILE__, __LINE__);

if(isset($_POST['add']))
{

	/* So we got something.. */
	if(isset($_POST['start']) && $_POST['start'] == "true")
		$start = true;
	else
		$start = false;
	
	if(isset($_POST['add_resume_data']) && $_POST['add_resume_data'] == "true")
		$add_resume_data = true;
	else
		$add_resume_data = false;

	logger(LOGDEBUG, 'POST-Parameter "add" is given (start is '.intval($start).')', __FILE__, __LINE__);

	$added_anything = false;

	/* Let's change the directory first */
	$changeback = false;
	if(isset($_POST['diroptions']) && $_POST['diroptions'])
	{
		if($valid_dir = is_valid_dir(clean_dir($_POST['directory'])))
		{
			$dir = clean_dir($_POST['directory']);
			if($_POST['diroptions'] == 2) /* Change permanent */
			{
				$_SESSION['dir'] = clean_dir($_POST['directory']);
				$db->query("UPDATE users SET dir = ? WHERE uid = ?", 'ss', $_SESSION['dir'], $_SESSION['uid']);
			}
		}
	}
	else
	{
		$valid_dir = true;
		$dir = $_SESSION['dir'];
	}

	if($valid_dir)
	{
		if(isset($_POST['public']) && $_POST['public'] == 'true')
			$public = true;
		else
			$public = false;

		if(!$settings['disable_sem'])
		{
			$sem = sem_get(SEM_KEY);
			if(!sem_acquire($sem))
				fatal("Could not acquire Semaphore!", __FILE__, __LINE__);
		}


		set_directory($dir);
		/* So let's have a look on the add-by-url-fields */
		for($x = 1; isset($_POST["addbyurl$x"]); $x++)
		{
			if($_POST["addbyurl$x"] != '')
			{
				if(($err = get_torrent($_POST["addbyurl$x"], $public, $start, $add_resume_data)) != '')
					$invalid[] = "{$_POST["addbyurl$x"]} - $err";
				else
					$added_anything = true;
			}
		}

		/* Now the add Torrens via Upload */
		for($x = 1; isset($_FILES["addbyfile$x"]); $x++)
		{
			if($_FILES["addbyfile$x"]['size'])
			{
				logger(LOGDEBUG, 'Uploaded file (number '.$x.'), libtorrent resume-data is '.intval($add_resume_data), __FILE__, __LINE__);

				if($add_resume_data)
            	add_libtorrent_resume_data($_FILES["addbyfile$x"]['tmp_name']);

				if(($err = add_file($_FILES["addbyfile$x"]['tmp_name'], $_FILES["addbyfile$x"]['name'], $public, $start)) != '')
					$invalid[] = "{$_FILES["addbyfile$x"]['name']} - $err";
				else
					$added_anything = true;
			}
		}

		if(isset($invalid) && count($invalid))
		{
			$error = $lng['adderror'].'<br />';
			foreach($invalid as $oneinvalid)
				$error .= '<br />'.htmlspecialchars($oneinvalid, ENT_QUOTES);
		}

		if(!$settings['disable_sem'])
			sem_release($sem);
	}
	else
		$error = $lng['addinvdir'];

	if($added_anything && !isset($error) && (!isset($_POST['more']) || $_POST['more'] != 'true'))
		$out->redirect("index.php$qsid");
}


if(addJobChecker())
	$m = $out->getMessages();
else
	$m = '';
$out->content = "<div id=\"main\">$header<div id=\"content\">$m<form action=\"add.php$qsid\" method=\"post\" enctype=\"multipart/form-data\">";

if(isset($error))
	$out->content .= "<div class=\"error\">$error</div>";

$addbyurl  = "<fieldset class=\"box\"><legend>{$lng['addbyurl']}</legend>";
for($x = 1; $x <= $settings['maxaddfieldsurl']; $x++)
	$addbyurl .= "<div class=\"addfield\">$x.&nbsp;<input type=\"text\" class=\"text\" name=\"addbyurl$x\" /></div>";
$addbyurl .= "</fieldset>";




$addbyfile = "<fieldset class=\"box\"><legend>{$lng['addbyupl']}</legend>";
for($x = 1; $x <= $settings['maxaddfieldsfile']; $x++)
	$addbyfile .= "<div class=\"addfield\">$x.&nbsp;<input type=\"file\" class=\"file\" name=\"addbyfile$x\" accept=\"application/x-bittorrent\" /></div>";
$addbyfile .= "</fieldset>";




$adddir    = "<fieldset class=\"box\"><legend>{$lng['diroptions']}</legend><input class=\"longinput\" type=\"text\" name=\"directory\" value=\"{$_SESSION['dir']}\" />";
$adddir   .= "<br /><br /><select name=\"diroptions\">";
foreach($diroptions_arr as $dirkey => $diroption)
	$adddir .= "<option value=\"$dirkey\">{$lng[$diroption]}</option>";
$adddir .= "</select></fieldset>";



if($settings['def_start_torrent'])
	$checked = ' checked="checked"';
else
	$checked = '';


$addbox    = "<fieldset class=\"box\"><legend>{$lng['add']}</legend>";
$addbox   .= "<div id=\"addadd\"><input type=\"checkbox\" name=\"start\" value=\"true\" id=\"startbox\"$checked /><label for=\"startbox\">&nbsp;{$lng['addstart']}</label>";
$addbox   .= "<input type=\"checkbox\" name=\"add_resume_data\"  value=\"true\" id=\"addresumebox\"  /><label for=\"addresumebox\">&nbsp;{$lng['addresumedat']}</label>";
$addbox   .= "<input type=\"checkbox\" name=\"more\"  value=\"true\" id=\"morebox\"  /><label for=\"morebox\">&nbsp;{$lng['addmore']}</label>";
if($settings['real_multiuser'])
	$addbox   .= "<input type=\"checkbox\" name=\"public\"  value=\"true\" id=\"publicbox\"  /><label for=\"publicbox\">&nbsp;{$lng['addpublic']}</label>";
$addbox   .= "<input type=\"submit\" name=\"add\" value=\"{$lng['addordir']}\" /></div><div class=\"hint\">{$lng['addodirhint']}</div>";
$addbox   .= "</fieldset>";

$out->content .= "$addbyurl$addbyfile$adddir$addbox</form></div></div>";

$out->renderPage($settings['html_title']);

?>
