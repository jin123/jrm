<?php
	class Lvyou_commentModel extends Model{
	protected $_validate = array(
			array('title','require','名称不能为空',1),
			array('gjz','require','关键词不能为空',1),
			array('sort','require','显示顺序不能为空',1),
			array('zhiwei','require','职位不能为空',1),
			array('jieshao','require','专家介绍不能为空',1),
			array('content','require','点评内容不能为空',1),
	 );
}

?>