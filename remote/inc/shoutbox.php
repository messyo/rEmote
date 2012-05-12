<?php

class Shoutbox
{
	var $smileyPattern = '#(:\-\)|:\)|:\-\(|:\(|;\-\)|;\)|:\||:\-\||8\-\)|8\)|:O|:\-O)#';
	var $smileys = array(
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

	function replaceSmileys($string)
	{
		global $smileyimgs;

		if(is_array($string))
			return "<img src=\"{$smileyimgs}{$this->smileys[$string[1]]}\" alt=\"{$string[1]}\" />";

		return preg_replace_callback($this->smileyPattern, array($this, 'replaceSmileys'), $string);
	}

	function getShouts()
	{
		global $db, $lng, $qsid;

		$result = $db->query('SELECT u.name, s.sid, s.uid, s.message, s.time FROM users u INNER JOIN shouts s ON u.uid = s.uid ORDER BY time DESC LIMIT 30');

		$shouts = '<table>';
		while($h = $db->fetch($result))
			$shouts .= sprintf('<tr><td><strong>%s</strong><br /><span class="hint">%s</span></td><td>%s</td><td>%s</td></tr>',
				$db->out($h['name']),
				date('d.m.y H:i', $h['time']),
				$this->replaceSmileys($db->out($h['message'])),
				'&nbsp;' // REPLACE BY DELETE-LINK
			);
		$shouts .= '</table>';

		return $shouts;
	}


	function makeShoutbox()
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

?>
