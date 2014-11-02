<?php
	class Lvyou_sceneryModel extends Model{
	protected $_validate = array(
			array('names','require','名称不能为空',1),
			array('gjz','require','关键词不能为空',1),
			array('sort','require','显示顺序不能为空',1),
			array('jieshao','require','介绍不能为空',1),
			array('ms','require','描述不能为空',1),
			array('jianyaoms','require','简要描述不能为空',1),
	 );
}

?>