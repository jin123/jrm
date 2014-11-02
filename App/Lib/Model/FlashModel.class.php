<?php
class FlashModel extends Model{
	protected $_validate =array(
		array('img','require','分类图片不能为空',1),
		//array('img','url','分类图片地址不正确',1),
	);
}