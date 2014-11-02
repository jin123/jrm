<?php

class LoginAction extends Action{
	
	public function index(){
		$this->display();
	}
	
	public function insert(){

		$admin = M('Admin');

		if(empty($_POST['userid'])) {
			$this->error('请填写用户名！');
		}elseif (empty($_POST['pwd'])){
			$this->error('请填写密码！');
		}elseif (empty($_POST['verify'])){
			$this->error('请填写验证码！');
		}

		if(session('verify') != md5($_POST['verify'])) {
			$this->error('验证码错误！');
		}

		$where['username'] = $this->_post('userid');
        $where['pwd'] =  $this->_post('pwd','md5');
		$result = $admin->where($where)->field('id,username,pwd')->find();
		if($result)
		{

			$id = 'id='.$result['id'];
			$data['logintime'] = time();
			$data['loginip'] = get_client_ip();

			$_SESSION['adminId'] = $result['id'];
			$_SESSION['loginadminName'] = $result['username'];
			$_SESSION['lastadminTime'] = $data['logintime'];

			$admin->where($id)->save($data);
			$this->redirect('/System/Index/index');
		}
		else
		{
			$this->error("用户或密码错误！");
		}
    }

	//注销登录
	public function logout(){
		if(isset($_SESSION['adminId'])) {
			unset($_SESSION['adminId']);
			unset($_SESSION['lastadminTime']);
			unset($_SESSION);
			session_destroy();
			$this->success('退出成功！',__URL__.'/index');
		}else {
			$this->error('无需重复退出！');
		}
	}

	
	public function verify(){
		import('ORG.Util.Image');
		Image::buildImageVerify();
	}		

}
?>