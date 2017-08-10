<?php
namespace Framework;
class Tpl
{
	//缓存文件路径
	protected $cacheDir = './cache/';
	//模板文件路径
	protected $tplDir = './tpl/';
	
	//缓存有效周期
	protected $lifeTime = 3600;
	
	//保存全局分配过来的变量
	protected $vars = [];
	
	//初始化成员属性
	public function __construct($cacheDir = null , $tplDir = null , $lifeTime = null)
	{
		//判断缓存文件路径
		if (isset($cacheDir)) {
			if ($this->checkDir($cacheDir)) {
				$this->cacheDir = $cacheDir;
			}
		}
		//判断模板文件
		if (isset($tplDir)) {
			if ($this->checkDir($tplDir)) {
				$this->tplDir = $tplDir;
			}
		}
		//判断声明周期
		if (isset($lifeTime)) {
			$this->lifeTime = $lifeTime;
		}
	}
	//判断文件路径
	protected function checkDir($path)
	{
		if (!file_exists($path) || !is_dir($path)) {
			return mkdir($path , 0777 , true);
		}
		
		if (!is_readable($path) || !is_writeable($path)) {
			return chmod($path , 0777);
		}
		return true;
		
		
	}
	
	///assign('title' , '标题')
	public function assign($name , $val)
	{
		$this->vars[$name] = $val;
	}
	//显示模板的方法
	public function display($filePath = null , $isExecute = true)
	{
		//检测$filePath是否为空
		if (empty($filePath)) {
			exit('没有模板文件被出入！');
		}
		//生成模板文件的路径
		$tplFileName = rtrim($this->tplDir , '/') .'/'. $filePath;
		if (!file_exists($tplFileName)) {
			exit('模板文件不存在！');
		}
		//生成缓存文件路径
		$cacheFileName = rtrim($this->cacheDir , '/') . '/' . md5($filePath) . '.php';
		//echo $cacheFilePath;
		//判断缓存文件是否存在
		if (!file_exists($cacheFileName)) {
			//执行编译
			$html = $this->complie($tplFileName);
			//写入
			if (!file_put_contents($cacheFileName,$html)) {
				exit('文件写入失败');
			}
		}
		//判断声明周期
		//判断缓存文件有效期 + 手动设置的时间如果说它大于当前的时间 （是不是不过期）
		$lifeTime = filectime($cacheFileName) + $this->lifeTime > time() ? false : true;
		//判断模板文件修改的时间是否大于缓存文件修改的时间
		$complieTime = filemtime($tplFileName) > filemtime($cacheFileName) ? true : false;
		
		if ($lifeTime || $complieTime) {
			unlink($cacheFileName);
			//再次执行编译
			$html = $this->complie($tplFileName);
			//写入
			if (!file_put_contents($cacheFileName,$html)) {
				exit('文件写入失败');
			}
		}
		//判断是否显示
		
		if ($isExecute) {
			extract($this->vars);
			include $cacheFileName;
		}
	}
	//编译方法
	protected function complie($fileName)
	{
		$html = file_get_contents($fileName);
		
		$key = [
				'{if %%}' => '<?php if(\1): ?>',
				'{else}' => '<?php else : ?>',
				'{else if %%}' => '<?php elseif(\1) : ?>',
				'{elseif %%}' => '<?php elseif(\1) : ?>',
				'{/if}' => '<?php endif;?>',
				'{$%%}' => '<?=$\1;?>',
				'{foreach %%}' => '<?php foreach(\1) :?>',
				'{/foreach}' => '<?php endforeach;?>',
				'{for %%}' => '<?php for(\1):?>',
				'{/for}' => '<?php endfor;?>',
				'{while %%}' => '<?php while(\1):?>',
				'{/while}' => '<?php endwhile;?>',
				'{continue}' => '<?php continue;?>',
				'{break}' => '<?php break;?>',
				'{$%% = $%%}' => '<?php $\1 = $\2;?>',
				'{$%%++}' => '<?php $\1++;?>',
				'{$%%--}' => '<?php $\1--;?>',
				'{comment}' => '<?php /* ',
				'{/comment}' => ' */ ?>',
				'{/*}' => '<?php /* ',
				'{*/}' => '* ?>',
				'{section}' => '<?php ',
				'{/section}' => '?>',
				'{{%%(%%)}}' => '<?=\1(\2);?>',
				'{include %%}' => '<?php include "\1";?>'
		];
		
		foreach ($key as $keys => $val) {
			
			$pattern = '#'.str_replace('%%' , '(.+)' , preg_quote($keys,'#')).'#imsU';
			//echo $pattern;
			$replace = $val;
			
			if (stripos($keys , 'include')) {
				//还有include 没有处理
				$html = preg_replace_callback($pattern , array($this , 'parseInclude') , $html);
			} else {
				$html = preg_replace($pattern , $replace , $html);
			}
			
			
			
			
		}
		
		return $html;
	}
	protected function parseInclude($data)
	{
		//var_dump($data);
		$file = str_replace('\'' , '' , $data[1]);
		//echo $file;
		$path = $this->parsePath($file);
		$this->display($file , false);
		$string = '<?php include "'.$path.'" ?>';
		return $string;
	}
	protected function parsePath($file)
	{
		return rtrim($this->cacheDir , '/') . '/' . md5($file) . '.php';
	}
	
}








