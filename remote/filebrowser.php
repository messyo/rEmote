<?php

define('TO_ROOT', './');
define('ACTIVE', 'files');

require_once('inc/global.php');
require_once('inc/functions/file.fun.php');
require_once('inc/header.php');


class CopyElement
{
	var $source;
	var $kill;
	var $type;

	function __construct($source, $kill, $type)
	{
		$this->source = $source;
		$this->kill   = $kill;
		$this->type   = $type;
	}
}

if(isset($_SESSION['last']))
	$last = $_SESSION['last'];

if(isset($_GET['viewmode']))
{
	if($_GET['viewmode'] == 'icon')
		$_SESSION['fileview'] = 1;
	else
		$_SESSION['fileview'] = 0;
}
else if(!isset($_SESSION['fileview']))
	$_SESSION['fileview'] = 0;

if(isset($_REQUEST['change_dir']))
{
	$current_dir = clean_dir($_REQUEST['change_dir']);
	if(!is_valid_dir($current_dir))
		$current_dir = $_SESSION['rootdir'];
}
else if(isset($last))
	$current_dir = $last;
else
	$current_dir = clean_dir($_SESSION['rootdir']);

if(!checkWrite($current_dir))
	$out->addNotify($lng['dirnowrite']);

if(isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case 'newfolder':
			$folder = clean_dir($current_dir.$_REQUEST['foldername']);
			if(!(substr($folder, 0, strlen($_SESSION['rootdir'])) == $_SESSION['rootdir'] && mkdir($folder)))
				$out->addError($lng['nomkdir']);
			break;
		case 'deldir':
			if(isset($_REQUEST['confirm']))
			{
				$folder = clean_dir($_REQUEST['dir']);
				if(!is_valid_dir($folder) || !checkWrite(realdirname($folder)) || !rrmdir($folder))
					$out->addError($lng['normdir']);
				else
					$out->redirect("filebrowser.php$qsid");
			}
			else if(!isset($_REQUEST['decline']))
			{
				// Do Question
				$out->addNotify(makeSecQuestion("filebrowser.php?action=deldir$sid", $lng['fbdelconf'], array('dir' => $_REQUEST['dir'])));
			}
			break;
		case 'delfile':
			if(isset($_REQUEST['confirm']))
			{
				$file = clean_dir($_REQUEST['file']);
				if(substr($file, 0, strlen($_SESSION['dir'])) == $_SESSION['dir'])
				if(!@unlink(substr($file, 0, -1)))
					$out->addError($lng['nodelfile']);
				else
					$out->redirect("filebrowser.php$qsid");
			}
			else if(!isset($_REQUEST['decline']))
			{
				// Do Question
				$out->addNotify(makeSecQuestion("filebrowser.php?action=delfile$sid", $lng['fbdelconf'], array('file' => $_REQUEST['file'])));
			}
			break;
		case 'copydir':
			$folder = clean_dir($_GET['dir']);
			if(is_valid_dir($folder))
				copybufferadd(new CopyElement($folder, false, 'dir'));
			else
				$out->addError($lng['nocopydir']);
			break;
		case 'cutdir':
			$folder = clean_dir($_GET['dir']);
			if(is_valid_dir($folder))
				copybufferadd(new CopyElement($folder, true, 'dir'));
			else
				$out->addError($lng['nocutdir']);
			break;
		case 'copyfile':
			$file = clean_path($_GET['file']);
			if(substr($file, 0, strlen($_SESSION['dir'])) == $_SESSION['dir'] && is_file($file))
				copybufferadd(new CopyElement($file, false, 'file'));
			else
				$out->addError($lng['nocopyfile']);
			break;
		case 'cutfile':
			$file = clean_path($_GET['file']);
			if(substr($file, 0, strlen($_SESSION['dir'])) == $_SESSION['dir'] && is_file($file))
				copybufferadd(new CopyElement($file, true, 'file'));
			else
				$out->addError($lng['nocutfile']);
			break;
		case 'paste':
			$folder = clean_dir($_GET['dir']);
			if(is_valid_dir($folder))
			{
				if(isset($_SESSION['copybuffer']) && count($_SESSION['copybuffer']))
				{
					foreach($_SESSION['copybuffer'] as $copyelement)
					{
						$names = explode('/', $copyelement->source);
						if($names[(count($names) - 1)] == '')
							$target_name = $names[(count($names) - 2)];
						else
							$target_name = $names[(count($names) - 1)];

						if($copyelement->kill)
							executeJob('move', $copyelement->source, $folder . getname($folder, $target_name));
						else
							executeJob('copy', $copyelement->source, $folder . getname($folder, $target_name));
					}
					unset($_SESSION['copybuffer']);
					$_SESSION['lastpaste'] = $_GET['pastetime'];
				}
				else if(!isset($_SESSION['lastpaste']) || ($_SESSION['lastpaste'] != $_GET['pastetime']))
					$out->addError($lng['pastenothin']);
			}
			else
				$out->addError($lng['pastetarget']);
			break;
		case 'dropcopybuffer':
			unset($_SESSION['copybuffer']);
			break;
		case 'dropcopyelement':
			if(!isset($_GET['element']) || trim($_GET['element']) == '' || !isset($_SESSION['copybuffer']))
			break;

			$e = trim($_GET['element']);
			foreach($_SESSION['copybuffer'] as $k => $x)
			{
				if($_SESSION['copybuffer'][$k]->source == $e)
				{
					unset($_SESSION['copybuffer'][$k]);
					break;
				}
			}
			if(!count($_SESSION['copybuffer']))
				unset($_SESSION['copybuffer']);
			break;
	}
}


$_SESSION['last'] = $current_dir;

addJobChecker();

$out->content = "<div id=\"main\">$header<div id=\"content\">";

if(isset($error))
	$out->content .= "<div class=\"error\">DEPRC: $error</div>";
$out->content .= $out->getMessages();
if(isset($_SESSION['copybuffer']) && count($_SESSION['copybuffer']))
{
	$info = $lng['copycutmark'].'<ul>';
	foreach($_SESSION['copybuffer'] as $ce)
	{
		$drop = "<a href=\"filebrowser.php?action=dropcopyelement&amp;element=".rawurlencode($ce->source)."$sid\" title=\"{$lng['drop']}\"><img src=\"{$imagedir}drop.png\" alt=\"Drop\" /></a>";
		if($ce->kill)
			$info .= "<li>$drop ".lng('markcut', $lng[$ce->type], htmlspecialchars($ce->source, ENT_QUOTES)).'</li>';
		else
			$info .= "<li>$drop ".lng('markcopy', $lng[$ce->type], htmlspecialchars($ce->source, ENT_QUOTES)).'</li>';
	}
	$info .= '</ul>';
	$out->content .= "<div class=\"notify\">$info</div>";
}
$top  = "<div id=\"changefolder\"><form action=\"filebrowser.php$qsid\" method=\"post\">";
$top .= "<img src=\"{$imagedir}folder_open.png\" alt=\"dir\" />&nbsp;<input style=\"width: 500px;\" type=\"text\" class=\"text\" name=\"change_dir\" value=\"$current_dir\" />&nbsp;<input type=\"image\" class=\"submitbutton\" src=\"{$imagedir}go.png\" value=\"->\" />";
$top .= "</form></div><div id=\"filebrowser\">";


$top  .= "<div id=\"filebrowseractions\"><form name=\"newfform\" action=\"filebrowser.php?action=newfolder$sid\" method=\"post\">";
$top  .= "<div id=\"folderhome\"><a title=\"{$lng['folder_home']}\" href=\"filebrowser.php?change_dir=" . rawurlencode($_SESSION['rootdir']) . "$sid\"><img src=\"{$imagedir}folder_home.png\" alt=\"home\" /></a></div>";
$top  .= "<div id=\"folderup\"><a title=\"{$lng['folder_up']}\" href=\"filebrowser.php?change_dir=" . rawurlencode(clean_dir($current_dir.'../')) . "$sid\"><img src=\"{$imagedir}folder_up.png\" alt=\"up\" /></a></div>";
if(isset($last))
	$top .= "<div id=\"lastfolder\"><a title=\"{$lng['back']}\" href=\"filebrowser.php?change_dir=" . rawurlencode($last) . "$sid\"><img src=\"{$imagedir}back.png\" alt=\"back\" /></a></div>";

$top .= "<div id=\"folderviewmode\"><a title=\"Viewmode List\" href=\"filebrowser.php?viewmode=list$sid\"><img src=\"{$imagedir}view_detailed.png\" alt=\"list\" /></a>";
$top .= "&nbsp;<a title=\"Viewmode Icons\" href=\"filebrowser.php?viewmode=icon$sid\"><img src=\"{$imagedir}view_icon.png\" alt=\"icon\" /></a></div>";
$top .= "<div id=\"folderjoblist\"><a title=\"Joblist\" onclick=\"return popupfun( this );\" href=\"joblist.php$qsid\"><img src=\"{$imagedir}joblist.png\" alt=\"joblist\" /></a></div>";
if(isset($_SESSION['copybuffer']) && count($_SESSION['copybuffer']))
{
	$top .= "<div id=\"folderpaste\"><a title=\"Paste\" href=\"filebrowser.php?action=paste&amp;dir=".rawurlencode($current_dir)."&amp;pastetime=".time()."$sid\"><img src=\"{$imagedir}editpaste.png\" alt=\"paste\" /></a>";
	$top .= "<a title=\"Drop\" href=\"filebrowser.php?action=dropcopybuffer$sid\"><img src=\"{$imagedir}drop.png\" alt=\"drop\" /></a></div>";
}
$top .= "<div id=\"foldernew\"><input type=\"hidden\" name=\"change_dir\" value=\"$current_dir\" /><input type=\"text\" class=\"text\" value=\"{$lng['newfolder']}\" onclick=\"delcont();\" name=\"foldername\" /><input class=\"submitbutton\" type=\"image\" src=\"{$imagedir}folder_new.png\" value=\"create\" /></div>";
$sizewidth = round(100*(disk_total_space($current_dir)-disk_free_space($current_dir))/disk_total_space($current_dir));
$text = $lng['freespace'].': '.format_bytes(disk_free_space($current_dir)).'/'.format_bytes(disk_total_space($current_dir))." ($sizewidth% in Use)";
$top .= "<div id=\"folderspace\"><img src=\"{$imagedir}hdd.png\" alt=\"Disk\" /><div id=\"progress\">".progressbar($sizewidth).$text;
$top .= "</div></div></form></div>";

$data = scandir($current_dir);
$dirs = $files = '';
if($_SESSION['fileview'] == 0)
{
	$content = '<table id="folder">';
	$out->jsinfos['browsetype'] = '\'list\'';
	foreach($data as $file)
	{
		if($file[0] == '.')
		{
			if(!$settings['showinvisiblefiles'] || $file == '.' || $file == '..')
				continue;
		}
		$rdir = rawurlencode($current_dir . $file);
		$filename = htmlspecialchars($file, ENT_QUOTES);
		if(is_dir($current_dir . $file))
		{
			$line  = "<tr><td class=\"icon\"><img src=\"{$imagedir}folder.png\" alt=\"F\" /></td><td class=\"filename\"><a href=\"filebrowser.php?change_dir=$rdir$sid\">$filename</a></td>";
			$line .= "<td class=\"actions\"><a title=\"{$lng['information']}\" href=\"filedetails.php?dir=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}fileinfo.png\" alt=\"I\" /></a>";
			$line .= "<a title=\"{$lng['checksfv']}\"  class=\"fbchecksfv\"  href=\"filebrowser.php?action=checksfv&amp;dir=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}checksfv.png\" alt=\"C\" /></a>";
			$line .= "<a title=\"{$lng['delete']}\"    class=\"fbdelete\"    href=\"filebrowser.php?action=deldir&amp;dir=$rdir$sid\" onclick=\"return showConfirm( this, 'fdel' );\"><img src=\"{$imagedir}editdelete.png\" alt=\"K\" /></a>";
			$line .= "<a title=\"{$lng['copy']}\"      class=\"fbcopy\"      href=\"filebrowser.php?action=copydir&amp;dir=$rdir$sid\"><img src=\"{$imagedir}editcopy.png\" alt=\"Cp\" /></a>";
			$line .= "<a title=\"{$lng['cut']}\"       class=\"fbcut\"       href=\"filebrowser.php?action=cutdir&amp;dir=$rdir$sid\"><img src=\"{$imagedir}editcut.png\" alt=\"Ct\" /></a>";
			$line .= "<a title=\"{$lng['download']}\"  class=\"fbdownload\"  href=\"filedetails.php?action=downzip&amp;dir=$rdir$sid\"><img src=\"{$imagedir}download.png\" alt=\"D\" /></a>";
			$line .= "<a title=\"{$lng['mktorrent']}\" class=\"fbmktorrent\" href=\"filedetails.php?action=mktorrent&amp;dir=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}mktorrent.png\" alt=\"T\" /></a></td></tr>";
			$dirs .= $line;
		}
		else
		{
			$line  = "<tr><td class=\"icon\"><img src=\"{$fileimgs}small/" . get_icon(strtolower(substr($file, -4))) . "\" alt=\"_\" /></td><td class=\"filename\">$filename</td>";
			$line .= "<td class=\"actions\"><a title=\"{$lng['information']}\" href=\"filedetails.php?file=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}fileinfo.png\" alt=\"I\" /></a>";
			$line .= "<a title=\"{$lng['display']}\"   class=\"fbdisplay\"   href=\"filedisplay.php?file=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}show.png\" alt=\"S\" /></a>";
			$line .= "<a title=\"{$lng['delete']}\"    class=\"fbdelete\"    href=\"filebrowser.php?action=delfile&amp;file=$rdir$sid\" onclick=\"return showConfirm( this, 'fdel' );\"><img src=\"{$imagedir}editdelete.png\" alt=\"K\" /></a>";
			$line .= "<a title=\"{$lng['copy']}\"      class=\"fbcopy\"      href=\"filebrowser.php?action=copyfile&amp;file=$rdir$sid\"><img src=\"{$imagedir}editcopy.png\" alt=\"Cp\" /></a>";
			$line .= "<a title=\"{$lng['cut']}\"       class=\"fbcut\"       href=\"filebrowser.php?action=cutfile&amp;file=$rdir$sid\"><img src=\"{$imagedir}editcut.png\" alt=\"Ct\" /></a>";
			$line .= "<a title=\"{$lng['download']}\"  class=\"fbdownload\"  href=\"filedetails.php?action=download&amp;file=$rdir$sid\"><img src=\"{$imagedir}download.png\" alt=\"D\" /></a>";
			$line .= "<a title=\"{$lng['mktorrent']}\" class=\"fbmktorrent\" href=\"filedetails.php?action=mktorrent&amp;file=$rdir$sid\" onclick=\"return popupfun( this );\"><img src=\"{$imagedir}mktorrent.png\" alt=\"T\" /></a></td></tr>";
			$files .= $line;
		}
	}
	$content .= "$dirs$files</table>";
}
else
{
	$out->jsinfos['browsetype'] = 'icon';
	$out->addJSLang(
		'open',
		'showdetails',
		'delete',
		'copy',
		'cut',
		'fbdelconf',
		'download',
		'mktorrent',
		'display',
		'checksfv'
	);

	$content = '<div id="iconfolder">';
	foreach($data as $file)
	{
		if($file[0] == '.')
		{
			if(!$settings['showinvisiblefiles'] || $file == '.' || $file == '..')
				continue;
		}
		$rdir = rawurlencode($current_dir . $file);
		$filename = htmlspecialchars($file, ENT_QUOTES);
		if(is_dir($current_dir . $file))
		{
			$rdir = rawurlencode($current_dir.$file);
			$dirs  .= "<div><a href=\"filebrowser.php?change_dir=$rdir$sid\" onclick=\"return actions( '$rdir' , 'dir', event);\"><img src=\"{$imagedir}folder_large.png\" alt=\"F\" /><br>$filename</a></div>";
		}
		else
		{
			$files .= "<div><a title=\"{$lng['information']}\" href=\"filedetails.php?file=$rdir$sid\" onclick=\"return actions( '$rdir' , 'file', event);\"><img src=\"{$fileimgs}large/" . get_icon(strtolower(substr($file, -4))) . "\" alt=\"_\" /><br>$filename</a></div>";
		}
	}
	$content .= "$dirs$files</div></div>";
}
$out->content .= "$top$content</div></div></div>";
$out->bodyonload = 'invisible();';
$out->addJSLang('archtar', 'archrar', 'archzip', 'archtargz', 'download', 'downrar', 'downzip', 'downtarbz2', 'fbdelconf', 'yes', 'no' );

$out->addJavascripts('js/filebrowser.js');
$out->renderPage($settings['html_title']);
