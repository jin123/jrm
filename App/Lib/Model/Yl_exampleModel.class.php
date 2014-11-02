<?php
class Yl_exampleModel extends Model{
	protected $_validate=array(
		array('content','require','内容不能为空'),
			array('uname','require','患者姓名不能为空'),
	);
}