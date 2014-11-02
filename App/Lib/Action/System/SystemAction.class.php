<?php
/**
 * @公用控制器
 */
class SystemAction extends Action{
	//初始化页面
	public function _initialize(){
		if (!isset($_SESSION['adminId'])|| (time() - $_SESSION['lastadminTime'] > 3600))
		{
			$this->error('请登陆！',U('System/Login/index'));	
		}
		else
		{
			$_SESSION['lastadminTime'] = time();
		}
	}
	
}
?>