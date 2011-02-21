<?php
return array(
	'views' => array(
		'default' => 'Smarty_Templater',
    	'html' => 'Smarty_Templater',
    	'json' => 'JSON_Templater',
    	'xml' => 'XML_Templater'
	),
	'templaters' => array(
		'Smarty_Templater' => array(
			'lib' => 'shared:Smarty-2.6.26/libs',
        	'template_dir' => 'app:views',
        	'compile_dir' => 'app:tmp/smarty_compiled',
        	'plugins_dir' => array('app:views/__plugins')
		)
	)
);
