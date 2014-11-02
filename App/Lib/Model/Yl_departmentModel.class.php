<?php
class Yl_departmentModel extends Model{
	protected $_validate =array(
			array('name','require','科室名称不能为空',1),
			array('content','require','科室简介不能为空',1),
	);
}