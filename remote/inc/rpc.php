<?php

class RpcHandler
{
	var $path;
	var $time;
	var $count;

	function __construct($rpcpath)
	{
		$this->path  = $rpcpath;
		$this->time  = 0.0;
		$this->count = 0;
	}

	private function prepare($method, $params)
	{
		$request = xmlrpc_encode_request($method, $params);

		return stream_context_create(array('http' => array('method' => 'POST',
																			'header'  => 'Content-Type: text/xml',
																			'content' => $request)));
	}

	private function execute($context)
	{
		$file = @file_get_contents($this->path, false, $context);
		if($file === false)
		{
      	global $out;
			$out->fatal("Connection Error", "Could not connect to rTorrent.");
		}
		
		$file = str_replace("i8","double",$file);

		return xmlrpc_decode($file);
	}

	function request($method, $params, $dieonfault = true)
	{
		global $out;

		$starttime = microtime(true);

		$context  = $this->prepare($method, $params);
		$response = $this->execute($context);

		$this->time += microtime(true) - $starttime;
		$this->count++;

		if((is_array($response) && xmlrpc_is_fault($response)) || (!is_array($response) && xmlrpc_is_fault(array($response))))
		{
			if($dieonfault)
				$out->fatal('XMLRPC-ERROR', "{$response['faultCode']}: {$response['faultString']}");
			else
				return false;
		}

		return($response);
	}

	function simple_multicall($f1)
	{
		$argc = func_num_args();

		$f = array(array('methodName' => $f1, 'params' => array('')));
		for($i = 1; $i < $argc; $i++)
			$f[] = array('methodName' => func_get_arg($i), 'params' => array(''));
		$result = $this->request('system.multicall', array($f));

		return($result);
	}

	function multicall($f1, $a1)
	{
		$argc = func_num_args();

		$f = array(array('methodName' => $f1, 'params' => $a1));
		for($i = 2; $i < $argc; $i++)
			$f[] = array('methodName' => func_get_arg($i), 'params' => func_get_arg(++$i));
		$result = $this->request('system.multicall', array($f));

		return($result);
	}
}

?>
