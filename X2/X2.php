<?php
define('X_PATH', realpath(dirname(__FILE__)));

import::register();	// register importer autoload

$__rx = array(
	'current' => null
);

/**
 * XRequest class and helper functions
 */
function RQ($url = null, $params = null)
{
	global $__rx;
	
	if (is_null($url) && !is_null($__rx['current']))
		return $__rx['current']['request'];
	
	return new XRequest($url, $params);
}

class XRequest
{
	public $url;
	public $method = 'GET';
	protected $key;
	protected $script = 'Internal_Script';
	
	function __construct($url, $params = null)
	{
		$this->url = $url;
		
		if (is_array($params))
		{
			foreach ($params as $k =>& $v)
				$this->{$k} =& $v;
		}
		
		if (!$this->key)
			$this->key = $url;
		
		$this->method = strtolower($this->method);
	}
	
	function dispatch()
	{
		global $__rx;
	
		$__rx[$this->key] = array(
			'request' => $this,
			'response' => new XResponse()
		);
		
		$keys = array_keys($__rx);
		$last_key = $keys[count($keys) - 2];
		
		$__rx['current'] =& $__rx[$this->key];
		
		$script = new $this->script();
		$script->init();
		$res = $script->run();
		$script->close();
		
		$__rx['current'] =& $__rx[$last_key];
		
		//__($__rx);
		
		return $res;
	}
}

/**
 * XResponse class and helper functions
 */
function RS($var = null, $value = null)
{
	global $__rx;
	
	if (is_null($var))
		return $__rx['current']['response'];
		
	$__rx['current']['response']->body[$var] = $value;
}

function RSH($header)
{
	global $__rx;
	
	$__rx['current']['response']->headers[] = $header;
}

function XRedirect($url, $code = 302)
{
	throw new XException($url, $code);
}

function V($view)
{
	global $__rx;
	$__rx['current']['response']->view = $view;
}

class XResponse
{
	public $headers;
	public $body;
	public $view;
}

function __()
{
	$args = func_get_args();
	$nargs = func_num_args();
	$trace = debug_backtrace();
	$caller = array_shift($trace);

	$key = $caller['file'].':'.$caller['line'];

	echo $key."\n";
	for ($i=0; $i<$nargs; $i++)
		echo print_r($args[$i], 1)."\n";
}

/**
 * XURL helper function
 * 
 * @param string $url
 * @return object XURL
 */
function XURL($url)
{
	return new XURL($url);
}

/**
 * URL parser
 * 
 * http://domain.com/path/to/section/-param1/value1/-param2/value2:some-action.html
 * path - /path/to/section/
 * args - param1=value1, param2=value2 - now merge with $_GET
 * action - some-action
 * view - html
 */
class XURL
{
	public $raw;
	public $path = '/';
	public $segments = array();
	public $action = '';
	public $view;
	public $views = array();
	
	function __construct($url, $views = null)
	{
		$this->raw = $url;
		$this->views($views);
	}
	
	function views($views = null)
	{
		if (is_null($views))
			return $this->views;
			
		$this->views = $views;
		return $this;
	}
	
	function parse()
	{
		$buf = parse_url($this->raw);
		$this->path = urldecode($buf['path']);
		$matches = array();
		
		// parse view
		if (count($this->views) && preg_match('/\.('.implode('|', $this->views).')$/', $this->path, $matches, PREG_OFFSET_CAPTURE) > 0)
		{
			$this->view = $matches[1][0];
			$this->path = substr($this->path, 0, $matches[0][1]);
		}
		
		// try to parse url parameters
		$params_re = '/\/-([\w\-]+)(\/([^\/\:\.]+))?/';
		if (preg_match_all($params_re, $this->path, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $param)
				$_GET[$param[1]] = $param[3];

			$this->path = preg_replace($params_re, '/', $this->path);
		}
		
		// try to parse action and state
		$action_state_re = '/\:([\w\-]+)/';
		if (preg_match_all($action_state_re, $this->path, $matches, PREG_SET_ORDER))
		{
			$this->action = $matches[0][1];
			$this->path = preg_replace($action_state_re, '/', $this->path);
		}

		$this->path = preg_replace('/\/+/', '/', $this->path);
		$this->path .= $this->path{strlen($this->path) - 1} === '/' ? '' : '/';
		$this->segments = explode('/', $this->path);
		array_shift($this->segments);
		array_pop($this->segments);
		
		return $this;
	}
	
	function build()
	{
		$params = array();
		foreach ($_GET as $var => $value)
			$params[] = '-'.$var.'/'.$value;
		
		$this->raw = '/'.$this->path.'/:'.$this->action.'/'.implode('/', $params).($this->view ? '.'.$this->view : '/');
		$this->raw = preg_replace('#/:'.$this->action.'#', ':'.$this->action, $this->raw);
		$this->raw = preg_replace('#/{2,}#', '/', $this->raw);

		return $this;
	}
	
	function __toString()
	{
		return $this->raw;
	}
}

class XAny_Script
{
	function init() { return $this; }
	
	function run() { return $this; }
	
	function close() { return $this; }
}

class XAny_Router
{
	public $controller;
	public $method;
	public $actions;
	
	function __construct()
	{
		$request = RQ();
		$action = 'action_'.preg_replace('/(-|_)(\w)/e', 'strtoupper("$2")', $request->url->action);
		
		$this->actions = array(
			//$action.'_at_',
			$action.'_'.$request->method.'_'.$request->url->view,
			$action.'_'.$request->method,
			$action.'_x_'.$request->url->view,
			$action,
			'action_index'
		);
	}
	
	protected function _findController()
	{
		$this->controller = 'Index_Controller';
		return true;
	}
	
	protected function _findMethod()
	{
		$methods = array_fill_keys(get_class_methods($this->controller), true);
		
		for ($i=0, $n = count($this->actions); $i<$n; $i++)
		{
			if (!array_key_exists($this->actions[$i], $methods))
				continue;
				
			$this->method = $this->actions[$i];
			break;
		}
		
		return true;
	}
	
	function route()
	{
		$this->_findController();
		$this->_findMethod();
		return $this;
	}
}

class XException extends Exception
{
	function __construct($message, $code = 0) 
	{
		parent::__construct($message, $code);
	}
}

/**
 * Import helper
 */
class import
{
	private static $_config;
	private static $_data;
	private static $_counter;
	
	static function register()
	{
		spl_autoload_register(array('import', 'importClass'));
	}
 
	static function unregister()
	{
		spl_autoload_unregister(array('import', 'importClass'));
	}
	
	static function scan($config_file)
	{
		$b_key = 'import::scan ['.$config_file.']';
		
		self::$_config = self::config($config_file);
		
		if (false !== ($cache = self::_cache())) 
		{
			self::$_data = $cache;
			return;
		}
		
		$n = count(self::$_config->scanner['directories']);
		for ($i = 0; $i < $n; $i++)
		{
			if (false === ($path = self::buildPath(self::$_config->scanner['directories'][$i])))
				continue;
			
			self::_scanDirectory($path);
		}
		
		self::_cache(self::$_data);
	}
	
	private static function _cache($value = null)
	{
		if (!self::$_config->cache['enabled'])
			return false;

		$cache_file = self::$_config->cache['file'];
		
		if (false === ($cache_file = self::buildPath($cache_file)))
			return false;
		
		if (!$value && !file_exists($cache_file))	// value not set - its mean load from cache 
			return false;
			
		if ($value) // save to cache
		{
			file_put_contents($cache_file, serialize($value));
			return true;
		}
		
		return unserialize(file_get_contents($cache_file));
	}
	
	private static function _scanDirectory($path)
	{
		if (!file_exists($path)) 
			return;
		
		$di = new RecursiveDirectoryIterator($path);
		foreach (new RecursiveIteratorIterator($di) as $fileinfo)
		{
			$filename = $fileinfo->getPathname();
			
			if (!preg_match(self::$_config->scanner['filenames'], $filename)) 
				continue;
				
			$content = file_get_contents($filename);
			if (!preg_match_all('/^\s*class\s+(\w+)/im', $content, $matches, PREG_SET_ORDER)) 
				continue;
			   
			self::$_data['files'][$filename] = @self::$_data['files'][$filename];
				
			foreach ($matches as $match)
			{
				self::$_data['classes'][$match[1]] = array(
					'name' => $match[1],
					'path' => $filename,
					'loaded' =>& self::$_data['files'][$filename]
				);
			}
		}
	}
	
	/**
	 * Import class by name or files by mask
	 * 
	 * @example import::from('app:classes/utils/Some.class.php'); - load file
	 * @example import::from('app:classes/utils/*'); - load all files with filemask match
	 * @example import::from('Request'); - load Request class
	 * 
	 * @param string $mask
	 * @return 
	 */
	static function from($mask)
	{
		return false === strpos($mask, '/') ? import::importClass($mask) : import::importFiles($mask);   
	}
	
	/**
	 * Import configuration
	 * 
	 * @example import::config('app:path/to/config.file'); - load from APP_PATH/configs/path/to/config.file
	 * 
	 * @param string $path	configuration file path
	 * @return object		 first level keys of configuration array converted in to object 
	 */
	static function config($path)
	{
		$key = $path;
		if (@array_key_exists($key, self::$_data['configs']))
			return self::$_data['configs'][$key];
			
		if (false === ($path = self::buildPath($path)) || !file_exists($path))
			return false;
		
		switch (substr($key, strrpos($key, '.')))
		{
			case '.ini':
				self::$_data['configs'][$key] = (object) parse_ini_file($path);
				break;
			case '.yml':
				require_once(X_PATH.'/libs/Spyc.lib.php');
				self::$_data['configs'][$key] = (object) spyc_load_file($path); 
				break;
			case '.php':
				self::$_data['configs'][$key] = (object) require($path);
				break;
		}
		
		return self::$_data['configs'][$key];
	}
	
	/**
	 * Import class by name
	 * 
	 * @param string $class_name
	 * @return string filename of class
	 */
	static function importClass($class_name)
	{
		$b_key = 'import::importClass #'.++self::$_counter.' ['.$class_name.']';
		
		if (!@array_key_exists($class_name, self::$_data['classes']))	// class not found
			return false;

		if (self::$_data['classes'][$class_name]['loaded'])	// class already loaded 
			return; 
 
		require(self::$_data['classes'][$class_name]['path']);	// load file
		self::$_data['classes'][$class_name]['loaded'] = true;
		
		return self::$_data['classes'][$class_name]['path'];
	}
	
	static function importFiles($path)
	{
		if (false === ($path = self::buildPath($path)))
			throw new Exception("type of path [$path] not defined");
			
		if (strpos($path, '*'))	// import directory recursively
		{
			foreach (glob($path) as $item)
			{
				if (!is_dir($item))	// its simple file
				{
					self::_importFile($item);
					continue;
				}
				
				$di = new RecursiveDirectoryIterator($item);
				foreach (new RecursiveIteratorIterator($di) as $fileinfo)
					self::_importFile($fileinfo->getPathname());
			}
		}
		else	// import simple file
			self::_importFile($path);
	}
	
	static function buildPath($path)
	{
		list($type, $path) = explode(':', $path);
		$type_const = strtoupper($type).'_PATH';
		
		if (!defined($type_const)) 
			return false;
			
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		return constant($type_const).DIRECTORY_SEPARATOR.$path;
	}
	
	private static function _importFile($path)
	{
		if (!preg_match(self::$_config->import['filemask'], $path) || isset(self::$_data['files'][$path])) 
			return;

		require($path);
		self::$_data['files'][$path] = true;
	}
}