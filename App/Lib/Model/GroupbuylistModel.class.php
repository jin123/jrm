<?php
class GroupbuylistModel extends Model{
	protected $_validate = array(
			array('usname','require','请完善用户信息'),
			array('tel','require','请完善用户信息'),
			array('addr','require','请完善用户信息'),
	 );
	protected $_auto = array (		
		array('ctime','time',1,'function'), 
	);
}

?>
