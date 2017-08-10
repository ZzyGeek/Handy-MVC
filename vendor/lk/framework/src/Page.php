<?php
namespace Framework;
class Page
{
	//总条数
	protected $total;
	//总页数
	protected $pageTotal;
	//每页显示数
	protected $num;
	//当前页
	protected $page;
	//超链接
	protected $url;
	//偏移量
	protected $offset;
	
	
	//初始化成员属性
	public function __construct($total , $num = 3)
	{
		$this->total = ($total < 1) ? 1 : $total;
		$this->num = $num;
		//处理总页数
		$this->pageTotal = $this->getPageTotal();
		//求出来当前页
		$this->page = $this->getPage();
		
		//处理偏移量
		$this->offset = $this->getOffset();
		
		//处理url
		$this->url = $this->getUrl();
		
		
		
	}
	//设置url
	//http://localhost/1606/5/page.php?name=likun
	protected function setUrl($page)
	{
		if (strstr($this->url , '?')) {
			return $this->url . '&page=' . $page;
		} else {
			return $this->url . '?page=' . $page;
		}
	}
	//处理首页
	protected function first()
	{
		return $this->setUrl(1);
	}
	//处理最后一页
	protected function last()
	{
		return $this->setUrl($this->pageTotal);
	}
	//上一页
	protected function prev()
	{
		$page = (($this->page - 1) < 1) ? 1 : $this->page - 1;
		
		return $this->setUrl($page);
	}
	//下一页
	protected function next()
	{
		$page = ($this->page + 1) > $this->pageTotal ? $this->pageTotal : $this->page + 1;
		return $this->setUrl($page);
	}
	//处理url
	protected function getUrl()
	{
		//return $_SERVER;
		//获取文件地址
		$path = $_SERVER['SCRIPT_NAME'];
		//获取主机名
		$host = $_SERVER['SERVER_NAME'];
		//获取端口号
		$port = $_SERVER['SERVER_PORT'];
		//获取协议
		$scheme = $_SERVER['REQUEST_SCHEME'];
		//获取网页的请求参数
		$queryString = $_SERVER['QUERY_STRING'];
		
		//var_dump($queryString);
		if (strlen($queryString)) {
			parse_str($queryString , $array);
			//var_dump($array);
			unset($array['page']);
			//var_dump($array);
			$path = $path . '?' . http_build_query($array);
			
			//var_dump($path);
		}
		$url = $scheme . '://' . $host . ':' . $port . $path;
		
		return $url;
	}
	
	
	//处理偏移量
	public function getOffset()
	{
		$start = ($this->page - 1) * $this->num;
		
		return 'LIMIT ' . $start . ',' . $this->num;
	}
	//求出来当前页
	protected function getPage()
	{
		return isset($_GET['page']) ? $_GET['page'] : 1;
	}
	
	//处理总页数
	protected function getPageTotal()
	{
		return ceil($this->total / $this->num);
	}
	
	//渲染
	
	public function render()
	{
		return [
			'first' => $this->first(),
			'last' => $this->last(),
			'prev' => $this->prev(),
			'next' => $this->next()
		];
	}
	
	
}









