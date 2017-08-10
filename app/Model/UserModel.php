<?php
namespace Model;
use Framework\Model;

class UserModel extends Model
{
	public function doAdd($data)
	{
		$result = $this->add($data);
		return $result;
	}	

	//执行查询
	
	public  function doFind($data)
	{
		$result = $this->where("username = '{$data['username']}' and password='{$data['password']}'")->select();
		return $result;
	}
}