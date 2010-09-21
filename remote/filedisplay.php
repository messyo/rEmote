<?php

define('TO_ROOT', './');

require_once(TO_ROOT.'inc/global.php');
require_once(TO_ROOT.'inc/functions/file.fun.php');

if(isset($_GET['file']) && is_valid_file($_GET['file']))
	$object = $_GET['file'];
else
	$out->fatal("Invalid File", "The File you wanted to see, is not available");

$ending = substr($object, -4);

if(isset($_GET['passthru']) && ($_GET['passthru'] == true))
{
	switch($ending)
	{
		case '.jpg':
		case 'jpeg':
			if(!isset($type))
				$type = 'jpeg';
		case '.png':
			if(!isset($type))
				$type = 'png';
		case '.bmp':
			if(!isset($type))
				$type = 'bmp';
		case 'tiff':
			if(!isset($type))
				$type = 'tiff';
		case '.xpm':
			if(!isset($type))
				$type = 'x-xpixmap';
		case '.gif':
			if(!isset($type))
				$type = 'gif';
		case '.ico':
			if(!isset($type))
				$type = 'x-icon';
			header('Content-type: image/' . $type);
			readfile($object);
			break;
		case '.nfo':
			/*
			-------------------------------------------------------------------
			NFO Viewer v1.1 by Richard Davey, Core PHP (rich@corephp.co.uk)

			Released 3rd May 2007
			Updated 5th January 2008

			Includes base font from the Damn NFO Viewer
			-------------------------------------------------------------------
			You are free to use this in any product, or on any web site.
			I'd appreciate it if you email and tell me where you use it, thanks.
			Latest builds at: http://nfo.corephp.co.uk
			-------------------------------------------------------------------

			This script accepts the following $_REQUEST parameters:

			bg              optional        The background colour of the image (default black)
			filename        required        The NFO file to load and display
			colour          required        The font to use when rendering
			*/

			//    PHP Version sanity check
			if (version_compare('4.0.6', phpversion()) == 1)
			{
				logger(LOGERROR, 'NFOGEN: This version of PHP is not fully supported. You need 4.0.6 or above.', __FILE__, __LINE__);
				exit();
			}

			//    GD check
			if (extension_loaded('gd') == false && !dl('gd.so'))
			{
				echo 'You are missing the GD extension for PHP, sorry but I cannot continue.';
				exit();
			}

			$red    = 255;
			$green  = 255;
			$blue   = 255;
			$colour = 5;

			if (file_exists($imagedir.'nfogen.png'))
				$fontset = imagecreatefrompng($imagedir.'nfogen.png');
			else
			{
				logger(LOGERROR, "NFOGEN: Aborting, cannot find the required fontset nfogen.png in path: $imagedir", __FILE__, __LINE__);
				exit();
			}

			$x      = 0;
			$y      = 0;
			$fontx  = 5;
			$fonty  = 12;
			$colour = $colour * $fonty;

			$nfo = file($object);

			// Calculate max width and height of image needed - height is easy, do first:
			$image_height = count($nfo) * 12;
			$image_width = 0;

			// Width needs a loop through the text
			for($c = 0; $c < count($nfo); $c++)
			{
				$line = $nfo[$c];
				$temp_len = strlen($line);
				if($temp_len > $image_width)
					$image_width = $temp_len;
			}

			$image_width = $image_width * $fontx;

			// Sanity Checks
			if($image_width > 1600)
				$image_width = 1600;

			$im = imagecreatetruecolor($image_width, $image_height);
			$bgc = imagecolorallocate($im, $red, $green, $blue);
			imagefill($im, 0, 0, $bgc);

			for($c = 0; $c < count($nfo); $c++)
			{
				$x = $fontx;
				$line = $nfo[$c];

				for ($i = 0; $i < strlen($line); $i++)
				{
					$current_char = substr($line, $i, 1);
					if ($current_char !== "\r" && $current_char !== "\n")
					{
						$offset = ord($current_char) * 5;
						imagecopy($im, $fontset, $x, $y, $offset, $colour, $fontx, $fonty);
						$x += $fontx;
					}
				}

				$y += $fonty;
			}

			header("Content-type: image/png");
			imagepng($im);
			imagedestroy($im);
			break;
	}
	session_write_close();
	exit;
}

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
		$content = '<img src="filedisplay.php?passthru=true&amp;file='.rawurlencode($object).$sid.'" alt="'.rawurlencode($object).'" />';
		break;
	case '.txt':
	case '.sfv':
		$file    = file_get_contents($object);
		$file    = htmlspecialchars($file, ENT_QUOTES);
		$content = str_replace("\n", '<br />', $file);
		break;
	case '.php':
	case 'html':
		$file    = file_get_contents($object);
		$file    = highlight_string($file, true);
		$content = str_replace("\n", '<br />', $file);
		break;
	case '.tar':
	case '.tgz':
	case 'r.gz':
	case '.bz2':
		if(($cmd = getBin('tar')) === false)
			break;
		$file = shell_exec($cmd.' -tf '.escapeshellarg($object));
	case '.rar':
		if(!isset($file))
		{
			if(($cmd = getBin('unrar')) === false)
				break;
			$file = shell_exec($cmd.' vb '.escapeshellarg($object));
		}
		$file = htmlspecialchars($file, ENT_QUOTES);
		$content = str_replace("\n", '<br />', $file);
		break;
	case '.zip':
		if(($cmd = getBin('unzip')) === false)
			break;
		$cmd   = shell_exec($cmd.' -l '.escapeshellarg($object));
		$lines = explode("\n", $cmd);
		$c = count($lines) - 2;
		$content = '';
		for($x = 2; $x < $c; $x++)
		{
			$parts = explode(' ', $lines[$x]);
			$content .= htmlspecialchars($parts[count($parts) - 1], ENT_QUOTES).'<br />';
		}
		break;
}

if(!isset($content) || $content == '')
{
	$out->addError($lng['internerror']);
	$content = $out->getMessages(Render::ERROR);
}

$headline = htmlspecialchars($object, ENT_QUOTES);
$out->content = "<div id=\"main\"><div id=\"content\"><h2>$headline</h2>$content</div></div>";
$out->renderPage($settings['html_title'], true, true);

?>
