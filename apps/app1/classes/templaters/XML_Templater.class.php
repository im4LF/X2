<?php

class XML_Templater extends Any_Templater
{
	function init()
	{
		if (!is_array($this->response->headers))
			$this->response->headers[] = 'Content-Type: text/xml';
		else
			$this->response->headers = array_merge(array('Content-Type: text/xml'), $this->response->headers); 
			
		return $this;
	}
	
	function display()
	{		
		return new ArrayToXML($this->response->body);
	}
}

class ArrayToXML 
{
    private $dom;
	
    public function __construct($array) 
	{
        $this->dom = new DOMDocument("1.0", "UTF8");
        $root = $this->dom->createElement('response');
        foreach ($array as $key => $value) 
		{
            $node = $this->createNode($key, $value);
            if ($node != null)
                $root->appendChild($node);
        }
        $this->dom->appendChild($root);
    }
	
    private function createNode($key, $value) 
	{
        $node = null;
		if (false !== ($pos = strpos($key, 'attr_')))
		{
			$node = $this->dom->createAttribute(substr($key, $pos+5));
    		$node->appendChild($this->dom->createTextNode($value)); 
		}		
        elseif (is_string($value) || is_numeric($value) || is_bool($value) || $value == null) 
		{
			if (is_numeric($key)) 
			{						         	
            	$node = $this->dom->createElement('item');
				$node->setAttribute('id', $key);
			}
			else
				$node = $this->dom->createElement($key);
			
            if ($value !== null)
				$node->appendChild($this->dom->createCDATASection($value));
        } 
		else 
		{
        	if (is_numeric($key)) 
			{						         	
            	$node = $this->dom->createElement('item');
				$node->setAttribute('id', $key);
			}
			else
				$node = $this->dom->createElement($key);
				
            if ($value != null) 
			{
                foreach ($value as $key => $value) 
				{
                    $sub = $this->createNode($key, $value);
                    if ($sub != null)
                        $node->appendChild($sub);
                }
            }
        }
        return $node;
    }
	
    public function __toString() 
	{
        return $this->dom->saveXML();
    }
}
