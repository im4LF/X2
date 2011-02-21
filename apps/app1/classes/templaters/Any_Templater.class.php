<?php

class Any_Templater
{
	public $config;
	public $response;
	
	function __construct($config = null)
	{
		$this->config = $config;
		$this->response = RS();
	}
	
	function init()
	{
		return $this;
	}
	
	function headers()
	{
		if (!is_array($this->response->headers))
			return $this;
			
		foreach ($this->response->headers as $header)
			header($header);
			
		return $this;
	}
	
	function display() {}
}
