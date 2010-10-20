<?php

if(!defined('IN_MYCP'))
	exit("<a href=\"controlpanel.php$qsid\">Controlpanel</a>");

require_once(TO_ROOT.'inc/boxarea.php');


$possible_boxes = array(
	BoxArea::BOX_SPEEDSTATS       => array('label' => 'boxspeed',    'visible' => true                                                              ),
	BoxArea::BOX_DISKSTATS        => array('label' => 'boxdisk',     'visible' => true                                                              ),
	BoxArea::BOX_BANDWITHSETTINGS => array('label' => 'boxbandwith', 'visible' => true                                                              ),
	BoxArea::BOX_FILTER           => array('label' => 'boxfilter',   'visible' => true                                                              ),
	BoxArea::BOX_REFRESHSETTINGS  => array('label' => 'boxrefresh',  'visible' => true                                                              ),
	BoxArea::BOX_SERVERSTATS      => array('label' => 'boxserver',   'visible' => (($_SESSION['status'] > USER) || $settings['user_see_serverinfo'])),
	BoxArea::BOX_SHOUTBOX         => array('label' => 'boxshoutbox', 'visible' => $settings['shoutbox']                                             )
);


$areas = array(
	BOX_NONE   => array(),
	BOX_SIDE   => array(),
	BOX_TOP    => array(),
	BOX_BOTTOM => array(),
	BOX_RIGHT  => array(),
);

foreach($possible_boxes as $k => $v)
{
	if($v['visible'])	
		$areas[BOX_NONE][] = $k;
}



// perform actions

if(isset($_GET['move']) && isset($possible_boxes[$_GET['move']]) && isset($_GET['dir']) && (($_GET['dir'] == 'up') || ($_GET['dir'] == 'down')))
{
	$obj = $_GET['move'];
	$dir = $_GET['dir'];
	
	foreach($_SESSION['boxpositions'] as $k => $b)
	{
   	// Look where it is...
		if(in_array($obj, $b))
		{
			$in = $k;
			break;
		}
	}
	if(!isset($in))
		$in = BOX_NONE;

	if($in == BOX_NONE)
	{
		// insert into sidebar
		for($i = count($_SESSION['boxpositions'][BOX_SIDE]) -1; $i >= 0; $i--)                    // Move everything one down
			$_SESSION['boxpositions'][BOX_SIDE][$i + 1] = $_SESSION['boxpositions'][BOX_SIDE][$i];

		$_SESSION['boxpositions'][BOX_SIDE][0] = $obj;
	}
	else
	{
		$pos = array_search($obj, $_SESSION['boxpositions'][$in]);
   	if($dir == 'up')
		{
			if(0 == $pos) // Look if it is at the first position already...
			{
				// Move one area to the top
				// move all elements one pos to the top
				for($x = 1; $x < count($_SESSION['boxpositions'][$in]); $x++)
					$_SESSION['boxpositions'][$in][$x - 1] = $_SESSION['boxpositions'][$in][$x];
				unset($_SESSION['boxpositions'][$in][$x - 1]);

				// Now insert into the above
				if($in > 0) // No inserting neccessary if we are the top area
				{
            	$_SESSION['boxpositions'][$in - 1][] = $obj;
				}
			}
			else
			{
				// Move one position up
				$_SESSION['boxpositions'][$in][$pos] = $_SESSION['boxpositions'][$in][$pos - 1];
				$_SESSION['boxpositions'][$in][$pos - 1] = $obj;
			}
		}
		else // $dir == 'down'
		{
      	if((count($_SESSION['boxpositions'][$in]) -1) == $pos) // We are the last one... so move it one area down
			{
				if($in != (count($_SESSION['boxpositions'])-1)) // If we are the last area, we cannot move anywhere... so simply ignore ;)
				{
					//Make Room in the new area for one element at the top...
					for($i = count($_SESSION['boxpositions'][$in + 1]) -1; $i >= 0; $i--)                    // Move everything one down
						$_SESSION['boxpositions'][$in + 1][$i + 1] = $_SESSION['boxpositions'][$in + 1][$i];

					$_SESSION['boxpositions'][$in + 1][0] = $obj;

            	unset($_SESSION['boxpositions'][$in][$pos]); // Remove out of the old area
				}
			}
			else // We just need to flip positions with the element below us
			{
				$_SESSION['boxpositions'][$in][$pos] = $_SESSION['boxpositions'][$in][$pos + 1];
				$_SESSION['boxpositions'][$in][$pos + 1] = $obj;
			}
		}	
	}
	// All actions are performed so write it to the database
	$db->query('DELETE FROM boxpositions WHERE uid = ?', 'i', $_SESSION['uid']);
	$qryfront = 'INSERT INTO boxpositions (uid, boxid, area, position) VALUES ';
	$qryvals = array();

	$x = 0;
	foreach($_SESSION['boxpositions'] as $akey => $a)
	{
		foreach($a as $box)
			$qryvals[] = '(' . $_SESSION['uid'] . ',' . $box . ',' . $akey . ',' . $x++.')';
	}

	$qry = $qryfront.implode(', ', $qryvals);
	$db->query($qry);
}


// End of actions


foreach($_SESSION['boxpositions'] as $akey => $a)
{
	foreach($a as $box)
	{
		$areas[$akey][] = $box;                                        // PUT ELEMENT IN CORRECT AREA ...
		unset($areas[BOX_NONE][array_search($box, $areas[BOX_NONE])]);	// AND REMOVE IT FROM "NONE"-Section
	}
}

$strareas = '';

foreach($areas as $key => $area)
{
	$strarea  = '<div class="cpboxarea">';
   $strarea .= "<h2>{$lng['boxarea_'.$key]}</h2><ol>";
	foreach($area as $box)
	{
		if($key == BOX_NONE)
		{
      	$links = "<a href=\"controlpanel.php?mod=$mod&amp;sub=$sub&amp;move=$box&amp;dir=down$sid\"><img src=\"{$imagedir}1downarrow.png\" alt=\"{$lng['movedown']}\" /></a>&nbsp;";
		}
		else
		{
      	$links  = "<a href=\"controlpanel.php?mod=$mod&amp;sub=$sub&amp;move=$box&amp;dir=up$sid\"><img src=\"{$imagedir}1uparrow.png\" alt=\"{$lng['moveup']}\" ></a>&nbsp;";
      	$links .= "<a href=\"controlpanel.php?mod=$mod&amp;sub=$sub&amp;move=$box&amp;dir=down$sid\"><img src=\"{$imagedir}1downarrow.png\" alt=\"{$lng['movedown']}\" /></a>&nbsp;";
		}

   	$strarea .= "<li>$links{$lng[$possible_boxes[$box]['label']]}</li>";
	}
	$strarea .= '</ol></div>';
	$strareas .= $strarea;
}


$cpout = $strareas;

?>
