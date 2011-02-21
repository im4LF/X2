<?php

class Index_Controller 
{
	function action_index() 
	{
		V('index');				// setup view
		RS('title', 'app1');	// pass variable to response
		RS('items', array(		// pass variable to response
			array('title' => 'title A', 'description' => 'description A'),
			array('title' => 'title B', 'description' => 'description B')
		));
	}
}