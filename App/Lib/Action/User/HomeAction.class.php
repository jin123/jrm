<?php
class HomeAction extends CommonAction{
	public $home_db;
	public function _initialize() {
		parent::_initialize();
		$this->home_db=M('home');
	}
	//配置
	public function set(){
		$where['token'] = $this->_session('token');
		$home=$this->home_db->where($where)->find();
		if(IS_POST){
			if($home==false){				
				$this->all_insert('Home','/set');
			}else{
				$_POST['id']=$home['id'];
				$this->all_save('Home','/set');				
			}
		}else{
			$this->assign('home',$home);
			$this->display();
		}
	}
}



?>