<?php

class IndexAction extends SystemAction{
	
	public function index(){
		$this->display();
	}

	public function insert(){
		$file=$this->_post('files');
		unset($_POST['files']);

		if($this->update_config($_POST,CONF_PATH.$file)){
			$this->success('操作成功',U('Index/index'));
		}else{
			$this->success('操作失败',U('Index/index'));
		}		
	}
	
	private function update_config($config, $config_file = '') {
		!is_file($config_file) && $config_file = CONF_PATH . 'web.php';
		if (is_writable($config_file)) {
			//$config = require $config_file;
			//$config = array_merge($config, $new_config);
			//dump($config);EXIT;
			file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
			@unlink(RUNTIME_FILE);
			return true;
		} else {
			
			return false;
		}
	}
	public function edit(){
		$db=M('admin');
		if(IS_POST){
			if(empty($_POST['userpwd']) || $_POST['userpwd'] != $_POST['userpwdok']) {
				$this->error('请确认新密码！');
			}
			$where['pwd'] =  $this->_post('oldpwd','md5');
			$result =$db->where($where)->find();
			if(!$result){
				$this->error('原始密码错误，请确认新密码！');
			}
			$data['pwd'] =  $this->_post('userpwd','md5');
			$id .= 'id ='.$this->_post('id');
			$admin =$db->where($id)->save($data);
			if($admin){
				$this->success("修改密码成功！");
			}else{
				$this->error("修改密码失败！");
			}
		}
		$data=$db->find();
		$this->assign('dinfo',$data);
		$this->display();
	}
	
}
?>