<?php
namespace Framework;
class Upload
{
	//路径
	protected $path = './';
	//准许的Mime类型
	protected $allowMime = array('image/png' , 'image/jpeg' , 'image/jpg' , 'image/gif' , 'image/wbmp');
	//准许的后缀名
	protected $allowSub = array('png' , 'jpeg' , 'jpg' , 'gif' , 'wbmp');
	//文件准许的大小
	protected $allowSize = 2000000;
	//文件错误号
	protected $errorNum;
	//文件错误信息
	protected $errorInfo;
	//文件大小
	protected $size;
	//文件新名名字
	protected $newName;
	//文件原名
	protected $orgName;
	//是否随机文件名
	protected $isRandName = true;
	//临时文件名
	protected $tmpName;
	//文件的前缀
	protected $prefix;
	//文件的后缀
	protected $subfix;
	//上传文件的mime类型
	protected $type;
	
	public function __construct($array = array())
	{
		//var_dump($array);
		
		foreach ($array as $key => $val) {
			$keys = strtolower($key);
			
			if (!array_key_exists($keys , get_class_vars(get_class($this)))) {
				continue;
			}
			//交给这个函数去给你成员属性一一赋值
			$this->setOption($keys , $val);
			
		}
		
	}
	//上传方法
	public function up($filed) // abc
	{
		//var_dump($_FILES);
		//首先怎么先检测路径是否正确
		if (!$this->checkPath()) {
			exit('没有上传文件');
		}
		//把上传图片信息赋值给临时变量
		$name = $_FILES[$filed]['name'];
		$type = $_FILES[$filed]['type'];
		$tmpName = $_FILES[$filed]['tmp_name'];
		$error = $_FILES[$filed]['error'];
		$size = $_FILES[$filed]['size'];
		
		//把它交个一个函数 叫做setFiles来处理
		
		if ($this->setFiles($name , $type , $tmpName , $error , $size)) {
			//是否启用随机文件名
			$this->newName = $this->createName();
			echo $this->newName;
			//判断 mime 判断 size 判断准许的后缀
			
			if ($this->checkMime() && $this->checkSize() && $this->checkSub()) {
				//移动文件
				if ($this->move()) {
					return $this->newName;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
	//处理移动
	protected function move()
	{
		if (is_uploaded_file($this->tmpName)) {
			$this->path = rtrim($this->path , '/') . '/' .$this->newName;
			if (move_uploaded_file($this->tmpName , $this->path)) {
				return true;
			} else {
				$this->setOption('errorNum' , -6);
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	//检测文件mime类型
	protected function checkMime()
	{
		if (in_array($this->type , $this->allowMime)) {
			return true;
		} else {
			$this->setOption('errorNum' , -3);
			return false;
		}
	}
	//检测大小
	protected function checkSize()
	{
		if ($this->size > $this->allowSize) {
			$this->setOption('errorNum' , -4);
			return false;
		} else {
			return true;
		}
	}
	//检测文件后缀
	protected function checkSub()
	{
		if (in_array($this->subfix , $this->allowSub)) {
			return true;
		} else {
			$this->setOption('errorNum' , -5);
			return false;
		}
	}
	
	//创建文件的新名字
	protected function createName()
	{
		if ($this->isRandName) {
			//随机
			return $this->prefix . $this->randName();
			
		} else {
			//不随机
			return $this->prefix . $this->orgName;
		}
	}
	//随机文件名
	protected function randName()
	{
		return uniqid() . '.' . $this->subfix;
	}
	//处理字段
	protected function setFiles($name , $type , $tmpName , $error , $size)
	{
		if ($error) {
			$this->setOption('errorNum' , $error);
		}
		$this->orgName = $name;
		$this->type = $type;
		$this->tmpName = $tmpName;
		$this->size = $size;
		
		$arr = explode('.' , $name); 
		$this->subfix = array_pop($arr);
		
		return true;
	}
	
	
	
	//检测路径
	protected function checkPath()
	{
		if (empty($this->path)) {
			$this->setOption('errorNum' , -1);
			return false;
		} else{
			if (file_exists($this->path) && is_writeable($this->path)) {
				return true;
			} else {
				$this->path = rtrim($this->path , '/') . '/';
				if (mkdir($this->path , 0777 , true)) {
					return true;
				} else {
					$this->setOption('errorNum' , -2);
					return false;
				}
			}
		}
	}
	
	//获取错误号
	protected function getErrorNum()
	{
		$str = '';
		switch ($this->errorNum) {
			case -1:
				$str = '没有上传文件';
				break;
			case -2:
				$str = '文件夹创建失败';
				break;
			case -3:
				$str = '不准许的MIME类型';
				break;
			case -4:
				$str = '文件大小超过了手动指定的大小';
				break;
			case -5:
				$str = '不准许的文件后缀名';
				break;
			case -6:
				$str = '文件移动失败';
				break;
			case 1:
				$str = '其值为 1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
				break;
			case 2:
				$str = '其值为 2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
				break;
			case 3:
				$str = '其值为 3，文件只有部分被上传。 ';
				break;
			case 4:
				$str = '其值为 4，没有文件被上传。 ';
				break;
			case 6:
				$str = '其值为 6，找不到临时文件夹。PHP 4.3.10 和 PHP 5.0.3 引进。';
				break;
			case 7:
				$str = '其值为 7，文件写入失败。PHP 5.1.0 引进。 ';
				break;
		}
	}
	
	//设置成员属性&&设置错误号 //erron 1
	protected function setOption($key , $val)
	{
		$this->$key = $val;
	}
	
}














