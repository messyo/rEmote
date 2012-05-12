<?php

class Template
{
	private $string;
	private $vk;

	function __construct($template)
	{
		$this->string = $template;
	}

	function bindOrder($values)
	{
		$this->vk = array();
		foreach($values as $v)
			$this->vk[] = '{'.$v.'}';
	}

	static function replaceLng($p)
	{
		global $lng;

		return($lng[$p[1]]);
	}

	function renderOrdered(&$values)
	{
		$string = str_replace($this->vk, $values, $this->string);
		$string = preg_replace_callback('/:lng\[([a-z_]+)\]/U', 'Template::replaceLng', $string);

		return $string;
	}

	function render(&$values)
	{
		global $lng;

		foreach($values as $k => $v)
		{
			$vk[] = '{'.$k.'}';
			$vr[] = $v;
		}

		$string = str_replace($vk, $vr, $this->string);
		$string = preg_replace_callback('/:lng\[([a-z_]+)\]/U', 'Template::replaceLng', $string);

		return $string;
	}

	static function quickparse(&$s, &$values)
	{
		foreach($values as $k => $v)
		{
			$vk[] = '{'.$k.'}';
			$vr[] = $v;
		}

		$string = str_replace($vk, $vr, $s);
		$string = preg_replace_callback('/:lng\[([a-z_]+)\]/U', 'Template::replaceLng', $string);

		return $string;
	}
}

?>
