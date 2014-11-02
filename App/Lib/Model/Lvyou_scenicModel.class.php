<?php
	class Lvyou_scenicModel extends Model{
	protected $_validate = array(
			array('name','require','景区名称不能为空',1),
			array('gjz','require','关键词不能为空',1),
			array('sort','require','显示顺序不能为空',1),
			array('ms','require','景区介绍不能为空',1),
	 );
}

?>