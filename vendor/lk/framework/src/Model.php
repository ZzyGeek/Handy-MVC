<?php
namespace Framework;
//数据库操作类
class Model
{
	//【1】
	//链接
	protected $link;
	//主机名
	protected $host;
	//用户名
	protected $user;
	//密码
	protected $pwd;
	//字符集
	protected $charset;
	//数据库名
	protected $dbName;
	//表名
	protected $table;
	//表前缀
	protected $prefix;
	//字段
	protected $fields;
	//???? 选项
	protected $options;
	//sql
	protected $sql;

	//初始化成员属性
	//【2】
	public function __construct($config = null) //这里你传过来的就是一个数组
	{
		if (is_null($config)) {
			$config = $GLOBALS['config'];
		}
		$this->host = $config['DB_HOST'];
		$this->user = $config['DB_USER'];
		$this->pwd = $config['DB_PWD'];
		$this->charset = $config['DB_CHARSET'];
		$this->dbName = $config['DB_NAME'];
		$this->prefix = $config['DB_PREFIX'];
		$this->link = $this->connect();
		
		$this->table = $this->getTable(); //【4】
		
		
		$this->fields = $this->getFields();	//【5】
	}
	//查询最大值
	public function max($fields = null)
	{
		if (empty($fields)) {
			$fields = $this->fileds['_pk'];
		}
		
		$sql = "SELECT max($fields) AS m FROM $this->table";
		
		//echo $sql;
		
		$result = $this->query($sql);
		return $result[0]['m'];
	}
	//删除方法
	public function delete()
	{
		$sql = 'DELETE FROM %TABLE% %WHERE%';
		$sql = str_replace(
			array('%TABLE%' , '%WHERE%'),
			array(
				$this->parseTable(),
				$this->parseWhere()
			),
			$sql
		);
		return $this->exec($sql);
	}
	
	//修改
	public function update($data)
	{
		if (!is_array($data)) {
			return false;
		}
		//update table set username='xiaoliangmaren',passwrod = '123' where id = ?
		
		$sql = 'UPDATE %TABLE% %SET% %WHERE%';
		$sql = str_replace(
			array('%TABLE%' , '%SET%' , '%WHERE%'),
			array(
				$this->parseTable(),
				$this->parseSet($data),
				$this->parseWhere()
			),
			$sql
		);
		
		return $this->exec($sql);
		
	}
	//处理修改的set问题
	
	protected function parseSet($data)
	{
		//var_dump($data);
		///$set = '';
		$str = '';
		foreach ($data as $key => $val) {
			$str .= $key .'='."'$val',";
		}
		return 'SET ' . rtrim($str , ',');
	}
	//添加
	public function add($data)
	{
		if (!is_array($data)) {
			return false;
		}
		$sql = 'INSERT INTO %TABLE% (%FIELDS%) VALUES(%VALUES%)';
		
		$sql = str_replace(
			array('%TABLE%' , '%FIELDS%' , '%VALUES%'),
			array(
				$this->parseTable(),
				$this->parseAddFields(array_keys($data)),
				$this->parseAddVal(array_values($data)),
			),
			$sql
		);
		return $this->exec($sql);
	}
	

	//执行方法
	protected function exec($sql)
	{
		$result = mysqli_query($this->link , $sql);
		if ($result) {
			return mysqli_insert_id($this->link);
		} else {
			return false;
		}
	}
	//处理添加方法的值
	protected function parseAddVal($data)
	{	
		$str = '';
		foreach ($data as $key => $val) {
			$str .= '\''.$val.'\',';
		}
		
		return trim($str , ',');
	}
	//处理添加方法字段
	//insert into name (字段 ， 字段2) values （’讲不完了‘ ，’202cb962ac59075b964b07152d234b70‘）
	protected function parseAddFields($data)
	{
		return join(',' , $data);
	}
	
	//根据字段查询[7]
	public function select()
	{
		$sql = 'SELECT %FIELDS% FROM %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';
		

		$sql = str_replace(
			array('%FIELDS%' , '%TABLE%' , '%WHERE%' , '%GROUP%' , '%HAVING%' ,'%ORDER%' , '%LIMIT%'),
			array(
				$this->parseFields(isset($this->options['fields']) ? $this->options['fields'] : null),
				$this->parseTable(),
				$this->parseWhere(),
				$this->parseGroup(),
				$this->parseHaving(),
				$this->parseOrder(),
				$this->parseLimit()
			),
			$sql
			);
			
			echo $sql;
		$data = $this->query($sql);
		
		return $data;
		
	}
	//处理limit
	protected function parseLimit()
	{
		
		$limit = '';
		if (empty($this->options['limit'])) {
			$limit = '';
		} else {
			if (is_string($this->options['limit'][0])) {
				$limit = 'LIMIT ' . $this->options['limit'][0];
			}
			
			if (is_array($this->options['limit'][0])) {
				$limit = 'LIMIT ' . join(',' , $this->options['limit'][0]);
			}
		}
		return $limit;
	}
	//处理order
	protected function parseOrder()
	{
		
		$order = '';
		if (empty($this->options['order'])) {
			$order = '';
		} else {
			$order = 'ORDER BY ' . $this->options['order'][0];
		}
		return $order;
	}
	//处理having
	protected function parseHaving()
	{
		$having = '';
		if (empty($this->options['having'])) {
			$having = '';
		} else {
			$having = 'HAVING ' . $this->options['having'][0];
		}
		return $having;
	}
	//处理grop
	protected function parseGroup()
	{
		$group = '';
		if (empty($this->options['group'])) {
			$group = '';
		} else {
			$group = 'GROUP BY ' . $this->options['group'][0];
		}
		return $group;
	}
	//处理查询里面where方法
	protected function parseWhere()
	{
		$where = '';
		if (empty($this->options['where'])) {
			$where = '';
		} else {
			$where = 'WHERE ' . $this->options['where'][0];
		}
		return $where;
	}
	//处理查询的是表方法
	protected function parseTable()
	{
		$table = '';
		if (isset($this->options['table'])) {
			$table = $this->prefix . $this->options['table'][0];
		} else {
			$table = $this->prefix . $this->table;
		}
		return $table;
	}
	
	
	//处理查询字段
	protected function parseFields($options)
	{
		$fields = '';
		if (empty($options)) {
			return '*';
		} else {
			if (is_string($options[0])) {
				$fields = explode(',' , $options[0]);
				$tmpArr = array_intersect($fields , $this->fields);
				//select username , passwrod fom ......
				$fields = join(',' , $tmpArr);
				return $fields;
			}
			
			if (is_array($options[0])) {
				$fields = join(',' , array_intersect($options[0] , $this->fields));
			}
		}
		return $fields;
		
	}
	//数据库链接方法
	//【5】
	protected function getFields()
	{
		$cacheFile = 'cache/' . $this->table . '.php';
		
		if (file_exists($cacheFile)) {
			
			return include $cacheFile;
			
		} else {
			$sql = 'DESC ' . $this->table;
			$data = $this->query($sql);
			
			//var_dump($data);
			$fields = [];
			foreach ($data as $key => $val) {
				$fields[] = $val['Field'];
				if ($val['Key'] == 'PRI' ) {
					$fields['_pk'] = $val['Field'];
				}
			}
			$string = "<?php \n return " . var_export($fields , true) . ';?>';
			
			//echo $string;
			//$this->table = str_replace('\\' , '/' , $this->table);
			file_put_contents('cache/' . $this->table . '.php' , $string);
			return $fields;
		}
		
	}
	//用call方法来解决连贯造作
	public function __call($func , $args)
	{
		if (in_array($func , ['fields','table','where','order','group','having','limit'])) {
			//var_dump($func,$args);
			$this->options[$func] = $args;
			return $this;
		} else if(strtolower(substr($func , 0 , 5)) == 'getby') {
			$fields = strtolower(substr($func , 5));
			return $this->getBy($fields , $args[0]);
		} else {
			exit('不存在的方法');
		}
	}
	//getby方法
	protected function getBy($fields , $val)
	{
		
		$sql = "select * from $this->table where $fields = '$val'";
		$result = $this->query($sql);
		
		return $result;
	}
	//【6】发送方法
	protected function query($sql)
	{
		$result = mysqli_query($this->link , $sql);
		$data = [];
		if ($result) {
			while ($rows = mysqli_fetch_assoc($result)) {
				$data[] = $rows;
			}
		} else {
			return false;
		}
		return $data;
	}
	//【4】
	protected function getTable()
	{
		$table = '';
		if (isset($this->table)) {
			//return $this->prefix . $this->table;
			$table = $this->prefix . $this->table;
		} else {
			$table = $this->prefix . strtolower(substr(get_class($this) , 0 , -5));
			//把model截掉
			//Model\UserModel
			$table = substr($table , 8);
			
		}
		return $table;
	}
	
	//【3】
	protected function connect()
	{
		$link = mysqli_connect($this->host , $this->user , $this->pwd);
		
		if (!$link) {
			exit('数据库链接失败!');
		}
		
		mysqli_set_charset($link , $this->charset);
		
		mysqli_select_db($link , $this->dbName);
		
		return $link;
	}
}






