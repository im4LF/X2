<?php

class Application_Script extends XAny_Script
{
	public $config;
	
	function init()
	{
		$this->config = import::config('app:configs/app.php');
	}
	
	function run()
	{
		$request = RQ();	// get current request
		
		$request->url = XURL($request->url)					// setup new URL parser
				->views(array_keys($this->config->views))	// configure allowed views (html, json, xml)
				->parse();									// parse url
				
		// create new Router class with current request (XRequest object)
		$router = new Router();
		
		$router->route();	// try to find route
		
		// create new controller with current XRequest object and node date
		$controller = new $router->controller();
		
		// create new controller with current XRequest object and node date
		$controller = new $router->controller();
		
		// call controller's method and get XResponse from it
		try
		{
			// call controller's method
			$controller->{$router->method}();
			
			// get templater class name from config
			// if urls's view not defined - use default
			$templater_class = $this->config->views[$request->url->view ? $request->url->view : 'default'];
			
			// try to load templater config
			$templater_config = isset($this->config->templaters[$templater_class]) ? $this->config->templaters[$templater_class] : null;
			
			// create templater 
			$templater = new $templater_class($templater_config);
		}
		catch (XException $e)	// 302 - redirect
		{
			switch ($e->getCode())
			{
				case 302:
					RSH('location: '.$e->getMessage());
					$templater = new Any_Templater();
				break;
			}
		}
		
		// init templater, print headers and display result
		echo $templater->init()->headers()->display();
	}
}