<?php
class Yl_hospitalModel extends Model{
	protected $_validate =array(
			array('name','require','医院名称不能为空',1),
			array('content','require','内容不能为空',1),
			array('address','require','医院地址不能为空',1),
	);
	protected $_auto=array(
	        array('updatetime','time',2,'function')
	);
}