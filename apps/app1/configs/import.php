<?php
return array(
	'cache' => array(
		'file' => 'app:cache/import.txt',
		'enabled' => false
	),
	
	'import' => array(
		'filemask' => '/\.php$/'
	),
	
	'scanner' => array(
		'directories' => array('app:controllers', 'app:scripts', 'app:classes'),
		'filenames' => '/\.(class|controller|action|script)\.php$/'
	)
);
