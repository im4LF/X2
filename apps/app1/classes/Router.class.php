<?php

class Router extends XAny_Router
{
	function _findController()
	{
		$request = RQ();	// get current request object
		$routs = import::config('app:configs/router.php');

		foreach ($routs as $re => $controller)
		{
			if (!preg_match($re, $request->url->path))
				continue;
				
			$this->controller = $controller;
			return;
		}
		
		throw new XException('not found', 404);
	}
}
