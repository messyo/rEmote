<?php

define('TO_ROOT', '../../../');

require_once(TO_ROOT.'inc/global.php');

if(!isset($_GET['hash']))
	$out->quit("nohash");

if(isset($_GET['key']))
{
	$f = cache_get('bitfields');
	if(($f === false) || !isset($f[$_GET['hash']][$_GET['key']]))
		$out->quit("nocache");

	$bitfield = $f[$_GET['hash']][$_GET['key']];
}
else
{
	if(false === ($response = $rpc->request('d.get_bitfield', array($_GET['hash']), false)))
		$out->quit("norpc");

	$bitfield = $response;
}

$width=100;

if(isset($_GET['width']) && $_GET['width'] > 0)
    $width = intval($_GET['width']);


$im = imagecreate($width, 1);

$downloaded_color = imagecolorallocate($im, 0x5C, 0x78, 0xB7);
$missing_color    = imagecolorallocate($im, 0xDD, 0xDD, 0xFF);

$length = strlen($bitfield) * 4;

$lastx = 0;
$lastcolor = $downloaded_color;

$value = 0;

$oldx = -293;

for($i = 0; $i < $length; ++$i) {
    $char = hexdec($bitfield[floor($i / 4)]);
	if($char & (8 >> ($i % 4))) {
		$value |= 2;
	} else {
		$value |= 1;
	}
	$x = $i * $width / ($length - 1);
	if(floor($x) != $oldx) {
		if($value == 3) {
			$color = $lastcolor == $missing_color ? $downloaded_color : $missing_color;
		} else {
			$color = $value & 1 ? $missing_color : $downloaded_color;
		}

		imageline($im, $oldx, 0, floor($x), 0, $color);

		$lastcolor = $color;
		$oldx = floor($x);
		$value = 0;
	}
}

header('Content-Type: image/png');
imagepng($im);
$out->quit();

?>
