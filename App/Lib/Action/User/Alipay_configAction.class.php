<?php
class Alipay_configAction extends CommonAction{
	public $alipay_config_db;
	public function _initialize() {
		$this->alipay_config_db=M('Alipay_config');
	}
	public function index(){
		$config = $this->alipay_config_db->where(array('token'=>session('token'),'type'=>1))->find();
		if(IS_POST){
			$row['pid']=$this->_post('pid');
			$row['key']=$this->_post('key');
			$row['name']=$this->_post('name');
			$row['open']=$this->_post('open');
			$row['token']=session('token');
			$row['type']=$this->_post('type');
			if ($config){
				$where=array('token'=>$this->token);
				$where=array('id'=>$config['id']);
				$this->alipay_config_db->where($where)->save($row);
			}else {
				$this->alipay_config_db->add($row);
			}
			$this->success('设置成功',U('Alipay_config/index',$where));
		}else{
			$this->assign('config',$config);
			$this->display();
		}
	}

	public function tenpay(){
		$config = $this->alipay_config_db->where(array('token'=>session('token'),'type'=>2))->find();
		if(IS_POST){
			$row['pid']=$this->_post('pid');
			$row['key']=$this->_post('key');
			$row['name']=$this->_post('name');
			$row['open']=$this->_post('open');
			$row['token']=session('token');
			$row['type']=$this->_post('type');
			if ($config){
				$where=array('token'=>$this->token);
				$where=array('id'=>$config['id']);
				$this->alipay_config_db->where($where)->save($row);
			}else {
				$this->alipay_config_db->add($row);
			}
			$this->success('设置成功',U('Alipay_config/tenpay'));
		}else{
			$this->assign('config',$config);
			$this->display();
		}
	}

	public function wxpay(){
		$config = $this->alipay_config_db->where(array('token'=>session('token'),'type'=>3))->find();
		if(IS_POST){
			$row['pid']=$this->_post('pid');
			$row['key']=$this->_post('key');
			$row['name']=$this->_post('name');
			$row['open']=$this->_post('open');
			$row['token']=session('token');
			$row['type']=$this->_post('type');
			$row['PaySignKey'] = $_POST['PaySignKey'];
			if ($config){
				$where=array('token'=>$this->token);
				$where=array('id'=>$config['id']);
				$this->alipay_config_db->where($where)->save($row);
			}else {
				$this->alipay_config_db->add($row);
			}
			$this->success('设置成功',U('Alipay_config/wxpay'));
		}else{
			$this->assign('config',$config);
			$this->display();
		}
	}
	public function wft(){
		$config = $this->alipay_config_db->where(array('token'=>session('token'),'type'=>4))->find();
		if(IS_POST){
			$row['pid']=$this->_post('pid');
			$row['key']=$this->_post('key');
			$row['name']=$this->_post('name');
			$row['open']=$this->_post('open');
			$row['token']=session('token');
			$row['type']=$this->_post('type');
			if ($config){
				$where=array('token'=>$this->token);
				$where=array('id'=>$config['id']);
				$this->alipay_config_db->where($where)->save($row);
			}else {
				$this->alipay_config_db->add($row);
			}
			$this->success('设置成功',U('Alipay_config/wft'));
		}else{
			$this->assign('config',$config);
			$this->display();
		}
	}
}


?>