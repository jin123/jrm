<?php
class Yl_colunmModel extends Model{
	protected $_validate=array(
		array('cname','require','栏目名称不能为空',1),
			array('action','require','控制器名称不能为空',1),
			array('model','require','模型不能为空')
	);
}