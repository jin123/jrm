<?php
class UserLabelModel extends Model{
	protected $_auto = array (
	
			array('creattime','time',1,'function'),
			array('updatetime','time',2,'function'),
	);
}