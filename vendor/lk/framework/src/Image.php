<?php
class Image
{
	//·��
	public $path = './';
	//��ʼ��·��
	
	public function __construct($path = './')
	{
		$this->path = rtrim($path , '/') . '/';
	}
	
	//�ж�·���Ƿ����
	//��ȡ�ļ���Ϣ
	//�ж�Ŀ��ͼƬ��С�Ƿ����ˮӡͼƬ��С
	//��ȡλ�� 1-9  ��0-9�ľ������
	//��ͼƬ
	//�ϲ�ͼƬ
	//����ͼƬ
	//����ͼƬ
	public function water($dst , $src , $isRand = true , $prefix = 'water_' , $opacity =100 , $position = 110)
	{
		//Ŀ��ͼƬ·��
		$dst = $this->path . $dst;
		//ˮӡͼƬ·��
		$src = $this->path . $src;
		
		if (!file_exists($dst)) {
			exit('Ŀ��ͼƬ������');
		}
		if (!file_exists($src)) {
			exit('ˮӡͼƬ������');
		}
		//��ʼ�Ƚϴ�С����ȡ���е�ͼƬ��Ϣ��
		$dstInfo = self::getImageInfo($dst);
		$srcInfo = self::getImageInfo($src);
		
		//�Ƚ�
		if (!$this->checkSize($dstInfo , $srcInfo)) {
			exit('ˮӡͼƬ̫��');
		}
		////��ȡλ�� 1-9  ��0-9�ľ������
		
		$position = self::getPosition($dstInfo , $srcInfo , $position);
		
		//��ͼƬ
		$dstRes = self::openImg($dst , $dstInfo);
		$srcRes = self::openImg($src , $srcInfo); // mime
		//��ʼ�ϲ�
		$newRes = self::mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity);
		
		//�������
		if ($isRand) {
			$newPath = $this->path . $prefix . uniqid() . $dstInfo['name'];
		} else {
			$newPath = $this->path . $prefix . $dstInfo['name'];
		}
		
		//����ͼƬ
		self::saveImg($newPath , $newRes , $dstInfo);
		
		//������Դ
		imagedestroy($dstRes);
		imagedestroy($srcRes);
	}

	
	//����ͼ
	public function thumb($img, $width, $height, $prefix = 'thumb_')
	{
		if (!file_exists($img)) {
			exit('�ļ�·��������');
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
		//��ԭͼƬ�Ŀ�ȸ������е�$size["width"]
		$size["width"] = $imgInfo["width"];   
		//��ԭͼƬ�ĸ߶ȸ������е�$size["height"]
		$size["height"] = $imgInfo["height"];  
		
		if($width < $imgInfo["width"]) {
			//���ŵĿ�������ԭͼС���������ÿ��
			$size["width"] = $width;             
		}

		if ($width < $imgInfo["height"]) {
			//���ŵĸ߶������ԭͼС���������ø߶�
			$size["height"] = $height;       
		}

		if($imgInfo["width"]*$size["width"] > $imgInfo["height"] * $size["height"]) {
			$size["height"] = round($imgInfo["height"] * $size["width"] / $imgInfo["width"]);
		} else {
			$size["width"] = round($imgInfo["width"] * $size["height"] / $imgInfo["height"]);
		}

		return $size;
	}
	
	
	
	
	
	
	
	//����ͼƬ
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
	//�ϲ�ͼƬ
	public static function mergeImg($dstRes , $srcRes , $srcInfo , $position , $opacity)
	{
		imagecopymerge($dstRes , $srcRes , $position['x'] , $position['y'] , 0 , 0 ,$srcInfo['width'] , $srcInfo['height'] , $opacity );
		
		return $dstRes;
	}
	//��ͼƬ
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
	//��ȡͼƬλ��
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
	
	//�Ƚϴ�С�ĺ���
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
	//��ȡͼƬ��Ϣ
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