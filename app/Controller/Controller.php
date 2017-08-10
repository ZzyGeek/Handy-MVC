<?php
namespace Controller;
use Framework\Tpl;
class Controller extends Tpl
{
	public function __construct()
	{

		parent::__construct($GLOBALS['config']['TPL_CACHE_DIR'] , $GLOBALS['config']['TPL_DIR'] , $GLOBALS['config']['TPL_LIFETIME']);
	}

	//成功的方法
	public function success($msg = null , $url = null , $time = 3)
	{
		if (is_null($url)) {
			$url = 'index.php';
		} else {
			$url = $_SERVER['HTTP_REFERER'];
		}

		$this->assign('msg' , $msg);
		$this->assign('url' , $url);
		$this->assign('time' , $time);
		$this->display('success.html');
	}
	//失败的方法
	public function error($msg = null , $url = null , $time = 3)
	{
		if (is_null($url)) {
			$url = 'index.php';
		} else {
			$url = $_SERVER['HTTP_REFERER'];
		}

		$this->assign('msg' , $msg);
		$this->assign('url' , $url);
		$this->assign('time' , $time);
		$this->display('error.html');
		exit();
	}
	//重构display方法
	public function display($filePath = null , $isExecute = true)
	{
		if ($filePath == null) {
			$filePath = $_GET['m'] . '/' . $_GET['a'] . '.html';
		}

		parent::display($filePath , $isExecute);

	}
}