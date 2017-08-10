<?php
namespace Framework;
class Verify
{
	//图片宽
	private $width;
	//高
	private $height;
	//图片类型
	private $imgType;

	//字体类型
	private $type;

	//字体个数
	private $num;

	//资源
	private $img;

	//画布上的字符串
	private $getCode;
	
	//初始化一批成员属性
	
	public function __construct($width = 100 , $height = 40 , $imgType = 'jpeg', $num = 4, $type = 3)
	{
		$this->width = $width;
		$this->height = $height;
		$this->imgType = $imgType;
		$this->num = $num;
		$this->type = $type;
		$this->getCode = $this->getCode();
		//echo $this->getCode;
	}
	
	//获取字符串
	private function getCode()
	{
		$string = '';
		switch ($this->type) {
			case 1:
				$string = join('' , array_rand(range(0 , 9) , $this->num));
				break;
			case 2:
			
				$string = implode('' , array_rand(array_flip(range('a','z')),$this->num));
				
				break;
				//混合的
			case 3:
				$str = 'abcdefghjkmnpqrstuvwxyzQWERTYUIPASDFGHJKLZXCVBNM23456789';
				$string = substr(str_shuffle($str) , 0 , $this->num);
				/*
				for ($i = 0; $i < $this->num ; $i++) {
					$rand = mt_rand(0 , 2);
					switch ($rand) {
						case 0:
							$char = mt_rand(48 , 57);
							break;
						case 1:
							$char = mt_rand(65 , 90);
							break;
						case 2:
							$char = mt_rand(97 , 122);
							break;
					}
					$string .= sprintf('%c' , $char);
				}
				*/
				break;
				
		}
		return $string;
	}
	
	//创建画布
	private function createImg()
	{
		$this->img = imagecreatetruecolor($this->width , $this->height); 
	}
	
	//搞背景颜色
	private function bgColor()
	{
		return imagecolorallocate($this->img , mt_rand(130 , 255) , mt_rand(130 , 255) , mt_rand(130 , 255)); 
	}
	
	//准备字体的颜色
	private function fontColor()
	{
		return imagecolorallocate($this->img , mt_rand(0 , 120) , mt_rand(0 , 120) , mt_rand(0 , 120));
	}
	
	//填充背景色
	
	private function fill()
	{
		return imagefilledrectangle($this->img , 0 , 0 , $this->width , $this->height , $this->bgColor()); 
	}
	
	//画点
	private function pixed()
	{
		for ($i = 0; $i < 50; $i++) {
			imagesetpixel($this->img , mt_rand(0 , $this->width) , mt_rand(0 , $this->height) , $this->fontColor());
		}
	}
	//划线
	private function arc()
	{
		for ($i =0; $i < 3; $i++) {
			imagearc($this->img , mt_rand(10 , $this->width) , mt_rand(10 , $this->height) , 80 , 20 , 20 , 180 , $this->fontColor());
		}
	}
	
	//写字
	private function write()
	{
		for ($i = 0; $i < $this->num; $i++) {
			$x = ceil($this->width/$this->num) * $i;
			$y = mt_rand(10 , $this->height - 20);
			
			imagechar($this->img , 5 , $x , $y , $this->getCode[$i] , $this->fontColor());
		}
	}
	
	//输出图片
	
	private function out()
	{
		$func = 'image'.$this->imgType;
		$header = 'Content-type:image' . $this->imgType;
		//判断这个函数是否存在
		if (function_exists($func)) {
			$func($this->img); //imagejpeg imagepng imagegif
			header($header);
		} else {
			exit('不支持该图片类型');
		}
	}
	//得到图片
	public function getImg()
	{
		$this->createImg();
		$this->fill();
		$this->arc();
		$this->pixed();
		$this->write();
		$this->out();
	}
	//销毁资源
	
	public function __destruct()
	{
		imagedestroy($this->img);
	}
	
}


























