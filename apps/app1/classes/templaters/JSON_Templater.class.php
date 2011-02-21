<?php

class JSON_Templater extends Any_Templater
{
	function display()
	{		
		return json_encode($this->response->body);
	}
}
