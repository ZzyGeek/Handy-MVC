<?php
namespace Controller;
use Controller\Controller;
use Model\UserModel;
use Framewrok\Page;

class UserController extends Controller
{
	public $user;

	public function __construct()
	{
		parent::__construct();
		$this->user = new UserModel();
	}
	
	//显示注册的模板
	public function register()
	{
		$this->display();
	}
	//执行注册方法
	public function doRegister()
	{
		//var_dump($_POST);
		$data['username'] = $_POST['username'];
		$data['password'] = $_POST['password'];
		$data['ctime'] = time();
		$result = $this->user->doAdd($data);
		// $model = new Model()
		//$model->add($data);
		//model???
		var_dump($result);
		if ($result) {
			$this->success();
		} else {
			$this->error();
		}
	}
	//登录
	public function login()
	{
		$this->display();
	}

	//执行登录
	public function doLogin()
	{
		$data['username'] = $_POST['username'];
		$data['password'] = $_POST['password'];

		$result = $this->user->doFind($data);

		if ($result) {
			$_SESSION['username'] =  $data['username'];
			$this->success();
		} else {
			$this->error();
		}

	}
}