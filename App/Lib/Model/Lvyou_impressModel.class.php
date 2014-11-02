<?php
	class Lvyou_impressModel extends Model{
	protected $_validate = array(
			array('name','require','名称不能为空',1),
			array('gjz','require','关键词不能为空',1),
			array('sort','require','显示顺序不能为空',1),
			array('impressnum','require','印象数不能为空',1),
	 );
}

?>