<?php

class LoginAction extends Action{
	
	public function index(){
		$this->display();
	}
	
	public function insert(){

		$daili = M('daili');

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

		$where['dluser'] = $this->_post('userid');
        $where['dlpwd'] =  $this->_post('pwd','md5');
		$result = $daili->where($where)->field('id,dluser')->find();
		if($result)
		{

			$id = 'id='.$result['id'];
			$data['logintime'] = time();
			$data['loginip'] = get_client_ip();

			$_SESSION['dluserId'] = $result['id'];
			$_SESSION['logindailiName'] = $result['dluser'];
			$_SESSION['lastdluserTime'] = $data['logintime'];

			$daili->where($id)->save($data);
			$this->redirect('/Daili/Index/Index');
		}
		else
		{
			$this->error("用户或密码错误！");
		}
    }

	//注销登录
	public function logout(){
		if(isset($_SESSION['dluserId'])) {
			unset($_SESSION['dluserId']);
			unset($_SESSION['lastdluserTime']);
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