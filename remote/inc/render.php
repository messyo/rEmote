<?php

class Render
{
	const ERROR       = 1;
	const SUCCESS     = 2;
	const NOTIFY      = 4;

	var $bodyonload   = '';
	var $content      = '';
	var $stylesheets  = array();
	var $javascripts  = array();
	var $stylescripts = array();
	var $precache     = array();
	var $jsinfos      = array();
	var $jslang       = array();
	var $metas        = array();
	var $error        = '';
	var $notify       = '';
	var $success      = '';

	function addError($string)
	{
		if($this->error != '')
			$this->error .= "<br />$string";
		else
			$this->error .= $string;
	}

	function addNotify($string)
	{
		if($this->notify != '')
			$this->notify .= "<br />$string";
		else
			$this->notify .= $string;
	}

	function addSuccess($string)
	{
		if($this->success != '')
			$this->success .= "<br />$string";
		else
			$this->success .= $string;
	}

	function getMessages($t = 7)
	{
		$output = '';

		if(($t & Render::ERROR) && ($this->error != ''))
		{
			$output .= "<div class=\"error\">{$this->error}</div>";
			$this->error = '';
		}
		if(($t & Render::SUCCESS) && ($this->success != ''))
		{
			$output .= "<div class=\"success\">{$this->success}</div>";
			$this->success = '';
		}
		if(($t & Render::NOTIFY) && ($this->notify != ''))
		{
			$output .= "<div class=\"notify\">{$this->notify}</div>";
			$this->notify = '';
		}

		return $output;
	}

	function hasError()
	{
		return($this->error != '');
	}

	function setStylesheets($arr)
	{
		$this->stylesheets = $arr;
	}

	function setJavascripts($arr)
	{
		$this->javascripts = $arr;
	}

	function setStyleJavascripts($arr)
	{
		$this->stylescripts = $arr;
	}

	function addStylesheets($sheet1)
	{
		$argc = func_num_args();

		for($i = 0; $i < $argc; $i++)
			$this->stylesheets[] = func_get_arg($i);
	}

	function addJavascripts($js1)
	{
		$argc = func_num_args();

		for($i = 0; $i < $argc; $i++)
			$this->javascripts[] = func_get_arg($i);
	}

	function addPrecache($sheet1)
	{
		$argc = func_num_args();

		for($i = 0; $i < $argc; $i++)
			$this->precache[] = func_get_arg($i);
	}

	function setPrecache($arr)
	{
		$this->precache = $arr;
	}

	function addJSLang($sheet1)
	{
		$argc = func_num_args();

		for($i = 0; $i < $argc; $i++)
		{
			$l = func_get_arg($i);
			$this->jslang[] = str_replace('\'', '\\\'', $l);
		}
	}

	function addMetas($sheet1)
	{
		$argc = func_num_args();

		for($i = 0; $i < $argc; $i++)
			$this->metas[] = func_get_arg($i);
	}

	function fatal($headline, $content)
	{
		$this->stylesheets = array();
		$this->javascripts = array();
		$this->precache    = array();
		$this->jsinfos     = array();
		$this->jslang      = array();
		$this->metas       = array();

		$this->content  = "<div style=\"margin: 200px; border: 2px solid red; color red; padding: 14px; font-family: arial, sans-serif;\"><span style=\"font-weight: bold; color: red;\">$headline</span>";
		$this->content .= "<br />$content</div>";
		$this->renderPage('rEmote - Fatal Error', false, false);
	}

	function quit($message = '')
	{
		/*
		* Closing session on renderPage as this is the last action in every page.
		* If not closing before script execution ends, first alls objects will be destroyed (including SQL-Object)
		* and then session will be written (not the best sollution as session saves Data via the included sql-object
		*/

		session_write_close();
		exit($message);
	}

	function redirect($url)
	{
		header('Location: '.$url);
		$this->quit("Redirected to <a href=\"$url\">$url</a>");
	}

	function renderPage($title, $buildfooter = true, $small = false)
	{
		global $rpc, $db, $lng, $imagedir, $settings, $starttime;

		/* Generate precache */
		$precachestring = '<div id="precache">';
		foreach($this->precache as $img)
			$precachestring .= "<img src=\"$imagedir$img\" alt=\"\" />";
		$precachestring .= '</div>';

		/* Generate Javascript-Variables */
		$jsinfostring = '<script type="text/javascript">';
		foreach($this->jslang as $val)
			$jsinfostring .= "\n\tvar lng$val = '{$lng[$val]}';";
		foreach($this->jsinfos as $key => $val)
			$jsinfostring .= "\n\tvar $key = $val;";
		$jsinfostring .= "\n</script>\n";

		/* Generate Javascript-Files */
		$javascriptsstring = "\n";
		foreach($this->javascripts as $js)
			$javascriptsstring .= "\t<script src=\"$js\" type=\"text/javascript\"></script>\n";
		foreach($this->stylescripts as $js)
			$javascriptsstring .= "\t<script src=\"$js\" type=\"text/javascript\"></script>\n";

		/* Generate Stylesheet-Infos */
		$stylesheetsstring = '';
		foreach($this->stylesheets as $val)
			$stylesheetsstring .= "<link href=\"$val\" rel=\"stylesheet\" type=\"text/css\" />";

		/* Generate Meta-Tags */
		$metastring = '';
		foreach($this->metas as $key => $val)
			$metastring .= "<meta http-equiv=\"$key\" content = \"$val\" />";

		if($this->bodyonload != '')
			$onload = " onload=\"{$this->bodyonload}\"";
		else
			$onload = '';

		if($small)
			$id = ' id="bodysmall"';
		else
			$id = '';


		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo "\n<html xml:lang=\"{$lng['shortkey']}\" xmlns=\"http://www.w3.org/1999/xhtml\">";
		$head  = "<head><meta http-equiv=\"content-type\" content=\"text/html; charset={$settings['encoding']}\" />$metastring<link rel=\"shortcut icon\" href=\"favicon.ico\" /><title>$title</title>";
		$head .= $stylesheetsstring.$jsinfostring.$javascriptsstring.'</head>';
		echo "$head<body$id$onload>$precachestring{$this->content}";
		if($buildfooter && !$small)
		{
			$time = 'Created in ' . round(microtime(true) - $starttime, 6) . 's (MySQL: ' . $db->get_time(6) . 's - '.$db->get_count().' Queries | XMLRPC: '.round($rpc->time, 6)."s - $rpc->count Requests)";
			if($settings['debug_mode'])
				$debug = '<table style="margin: auto;"><tr><td colspan="3"><span style="color: red;">DEBUG-MODE ACTIVATED. PLEASE DEACTIVATE ON PRODUCTIVE-ENVIRONMENTS</span></td></tr><tr><td style="vertical-align: top; text-align: left;"><pre style="font-size: 11px;">'.print_r($_SESSION, true).'</pre></td><td style="vertical-align: top; text-align: left;"><pre>'.print_r($_POST, true).'</pre></td><td style="vertical-align: top; text-align: left;"><pre>'.print_r($_GET, true).'</pre></td></tr></table>';
			else
				$debug = '';
			echo "<div id=\"footer\">$time$debug</div>";
		}
		echo "</body></html>";
		$this->quit();
	}
}

?>
