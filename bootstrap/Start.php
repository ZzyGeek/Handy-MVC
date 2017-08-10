<?php
class Start
{
	static $loader;
	public static function run()
	{
		session_start();
		include 'bootstrap/Psr4Autoload.php';
		//通过配置文件把命名空间拿过来
		$namespace = include 'config/namespaces.php';
		//把配置文件数据库的拿过来
		$GLOBALS['config'] = include 'config/config.php';
        var_dump($GLOBALS);
		die;
		self::$loader = new Psr4Autoload();
		self::$loader->register();
		self::addNamespaces($namespace);
		self::route();
	}

	public static function addNamespaces($namespaces)
	{
		foreach ($namespaces as $path => $namespace) {
			self::$loader->addNamespace($namespace , $path);
		}
		
	}
	public static function route()
	{
		$_GET['m'] = isset($_GET['m']) ? $_GET['m'] : 'Index';
		$_GET['a'] = isset($_GET['a']) ? $_GET['a'] : 'index';
		$_GET['m'] = ucfirst($_GET['m']);
		$controller = 'Controller\\' . $_GET['m'] . 'Controller';
		$c = new $controller();
		call_user_func(array($c , $_GET['a']));
	}
}