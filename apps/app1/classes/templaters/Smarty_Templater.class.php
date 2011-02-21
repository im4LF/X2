<?php

class Smarty_Templater extends Any_Templater
{
	public $smarty;
	
	function init()
	{		
		import::from($this->config['lib'].'/Smarty.class.php');
		
		$this->smarty = new Smarty();
		$this->smarty->template_dir = import::buildPath($this->config['template_dir']);
		$this->smarty->compile_dir = import::buildPath($this->config['compile_dir']);
		foreach ($this->config['plugins_dir'] as $dir)
			$this->smarty->plugins_dir[] = import::buildPath($dir);
			
		return $this;
	}
	
	function display()
	{
		if (is_array($this->response->body))
		{
			foreach ($this->response->body as $var => $value)
				$this->smarty->assign($var, $value);
		}
		
		return $this->smarty->fetch($this->response->view.'.tpl');
	}
}
