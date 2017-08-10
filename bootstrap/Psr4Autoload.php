<?php
class Psr4Autoload
{
	public $namespaces = array();
	
	public function register()
	{
		spl_autoload_register(array($this , 'loadClass'));
	}
	
	public function addNamespace($namespace , $path)
	{
		$this->namespaces[$namespace][] = $path;
	}

	public function loadClass($className)
	{
		$pos = strrpos($className , '\\');
		
		$namespace = substr($className , 0 , $pos+1);
		
		$realClassName = substr($className , $pos+1);
		
		$this->mapLoad($namespace , $realClassName);
		
	}
	
	public function mapLoad($namespace , $realClassName)
	{
		if (isset($this->namespaces[$namespace]) == false) {
			$fileName = str_replace('\\' , '/' ,strtolower($namespace . $realClassName . '.php'));
		} else {
			foreach ($this->namespaces[$namespace] as $path ) {
				$fileName = strtolower($path . '/' . $realClassName . '.php');
			}
		}
		$this->requireFile($fileName);
	}
	
	public function requireFile($file)
	{
		if (file_exists($file)) {
			include $file;
			return true;
		}
		return false;
	}
}