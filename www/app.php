<?php

date_default_timezone_set('Europe/Moscow');			// setup timezone
define('APP_PATH', realpath('../apps/app1'));		// setup application path
define('SHARED_PATH', realpath('../apps/shared'));	// setup shared libraries path

include '../X2/X2.php';					// include X2

import::scan('app:configs/import.php');	// search classes for autoloading

RQ($_SERVER['REQUEST_URI'], array(					// create new request
		'method' => $_SERVER['REQUEST_METHOD'],		// method of request
		'script' => 'Application_Script'			// control script
	)
)->dispatch();										// run dispatching
