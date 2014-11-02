<?php
/**
 * @公用控制器
 */
class SystemAction extends Action{
	//初始化页面
	public function _initialize(){
		if (!isset($_SESSION['dluserId'])|| (time() - $_SESSION['lastdluserTime'] > 3600))
		{
			$this->error('请登陆！',U('Daili/Login/index'));	
		}
		else
		{
			$_SESSION['lastdluserTime'] = time();
		}
	}
	
}
?>