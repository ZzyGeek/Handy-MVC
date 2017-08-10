<?php
class Image
{
	//路径
	public $path = './';
	//初始化路径
	
	public function __construct($path = './')
	{
		$this->path = rtrim($path , '/') . '/';
	}
	
	//判断路径是否存在
	//获取文件信息
	//判断目标图片大小是否大于水印图片大小
	//获取位置 1-9  非0-9的就是随机
	//打开图片
	//合并图片
	//保存图片
	//销毁图片
	public function water($dst , $src , $isRand = true , $prefix = 'water_' , $opacity =100 , $position = 110)
	{
		//目标图片路径
		$dst = $this->path . $dst;
		//水印图片路径
		$src = $this->path . $src;
		
		if (!file_exists($dst)) {
			exit('目标图片不存在');
		}
		if (!file_exists($src)) {
			exit('水印图片不存在');
		}
		//开始比较大小（获取所有的图片信息）
		$dstInfo = self::getImageInfo($dst);
		$srcInfo = self::getImageInfo($src);
		
		//比较
		if (!$this->checkSize($dstInfo , $srcInfo)) {
			exit('水印图片太大');
		}
		////获取位置 1-9  非0-9的就是随机
		
		$position = self::getPosition($dstInfo , $srcInfo , $position);
		
		//打开图片
		$dstRes = self::openImg($dst , $dstInfo);
		$srcRes = self::openImg($src , $srcInfo); // mime
		//开始合并
		$newRes = self::mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity);
		
		//处理随机
		if ($isRand) {
			$newPath = $this->path . $prefix . uniqid() . $dstInfo['name'];
		} else {
			$newPath = $this->path . $prefix . $dstInfo['name'];
		}
		
		//保存图片
		self::saveImg($newPath , $newRes , $dstInfo);
		
		//销毁资源
		imagedestroy($dstRes);
		imagedestroy($srcRes);
	}

	
	//缩略图
	public function thumb($img, $width, $height, $prefix = 'thumb_')
	{
		if (!file_exists($img)) {
			exit('文件路径不正在');
		}
		
		$info = self::getImageInfo($img);
		$newSize = self::getNewSize($width,$height,$info);
		$res = self::openImg($img, $info);
		$newRes = self::kidOfImage($res,$newSize,$info);
		$newPath = $this->path.$prefix.$info['name'];
		self::saveImg($newPath,$newRes,$info);
		imagedestroy($newRes);
		return $newPath;
	}
	
	
	private static function kidOfImage($srcImg, $size, $imgInfo)
	{
		$newImg = imagecreatetruecolor($size["width"], $size["height"]);		
		$otsc = imagecolortransparent($srcImg);
		if ( $otsc >= 0 && $otsc < imagecolorstotal($srcImg)) {
			 $transparentcolor = imagecolorsforindex( $srcImg, $otsc );
				 $newtransparentcolor = imagecolorallocate(
				 $newImg,
				 $transparentcolor['red'],
					 $transparentcolor['green'],
				 $transparentcolor['blue']
			 );

			 imagefill( $newImg, 0, 0, $newtransparentcolor );
			 imagecolortransparent( $newImg, $newtransparentcolor );
		}

	
		imagecopyresized( $newImg, $srcImg, 0, 0, 0, 0, $size["width"], $size["height"], $imgInfo["width"], $imgInfo["height"] );
		imagedestroy($srcImg);
		return $newImg;
	}
	
	private static function getNewSize($width, $height, $imgInfo)
	{	
		//将原图片的宽度给数组中的$size["width"]
		$size["width"] = $imgInfo["width"];   
		//将原图片的高度给数组中的$size["height"]
		$size["height"] = $imgInfo["height"];  
		
		if($width < $imgInfo["width"]) {
			//缩放的宽度如果比原图小才重新设置宽度
			$size["width"] = $width;             
		}

		if ($width < $imgInfo["height"]) {
			//缩放的高度如果比原图小才重新设置高度
			$size["height"] = $height;       
		}

		if($imgInfo["width"]*$size["width"] > $imgInfo["height"] * $size["height"]) {
			$size["height"] = round($imgInfo["height"] * $size["width"] / $imgInfo["width"]);
		} else {
			$size["width"] = round($imgInfo["width"] * $size["height"] / $imgInfo["height"]);
		}

		return $size;
	}
	
	
	
	
	
	
	
	//保存图片
	public static function saveImg($path , $res , $info)
	{
		//imagepng();
		switch ($info['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				imagejpeg($res, $path);
				break;
			case 'image/bmp':
			case 'image/wbmp':
				imagewbmp($res, $path);
				break;
			case 'image/gif':
				imagegif($res, $path);
				break;
			case 'image/png':
			case 'image/x-png':
				imagepng($res, $path);
				break;
		}
	}
	//合并图片
	public static function mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity)
	{
		imagecopymerge($dstRes , $srcRes , $position['x'] , $position['y'] , 0 , 0 ,$srcInfo['width'] , $srcInfo['height'] , $opacity );
		
		return $dstRes;
	}
	//打开图片
	public static function openImg($path , $info)
	{
		
		switch ($info['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$res = imagecreatefromjpeg($path);
				break;
			case 'image/bmp':
			case 'image/wbmp':
				$res = imagecreatefromwbmp($path);
				break;
			case 'image/gif':
				$res = imagecreatefromgif($path);
				break;
			case 'image/png':
			case 'image/x-png':
				$res = imagecreatefrompng($path);
				break;
		}
		return $res;
	}
	//获取图片位置
	public static function getPosition($dstInfo , $srcInfo , $position)
	{
		switch($position) {
			case 1:
				$x = 0;
				$y = 0;
				break;
			case 2:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = 0;
				break;
			case 3:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = 0;
				break;
			case 4:
				$x = 0;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 5:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 6:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 7:
				$x = 0;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 8:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 9:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			default:
				$x = mt_rand(0 , $dstInfo['width'] - $srcInfo['width']);
				$y = mt_rand(0 , $dstInfo['height'] - $srcInfo['height']);
				break;
		}
		
		return [
			'x' => $x,
			'y' => $y
		];
	}
	
	//比较大小的函数
	public function checkSize($dstInfo , $srcInfo)
	{
		if ($dstInfo['width'] < $srcInfo['width']) {
			return false;
		}
		if ($dstInfo['height'] < $srcInfo['height']) {
			return false;
		}
		return true;
	}
	//获取图片信息
	public static function getImageInfo($path)
	{
		//var_dump($path);
		$data = getimagesize($path);
		//var_dump($data);
		$info['width'] = $data[0];
		$info['height'] = $data[1];
		$info['mime'] = $data['mime'];
		$info['name'] = basename($path);
		return $info;
	}
}