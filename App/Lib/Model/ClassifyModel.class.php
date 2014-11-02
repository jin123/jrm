<?php
class ClassifyModel extends Model{

	protected $_validate =array(
		array('name','require','栏目名称不能为空',1),
	);
	
	protected $_auto = array (
		array('token','gettoken',self::MODEL_INSERT,'callback'),
	);

	function gettoken(){
		return session('token');
	}
	
}