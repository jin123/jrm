<?php

// 前台用户登陆
class LoginAction extends Action {

    public function index(){
		$this->display();
    }

	public function insert(){
		$admin = M('wxuser');

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
        $where['password'] =  $this->_post('pwd','md5');
		$result = $admin->where($where)->field('id,username,password,token,endtime,sitename')->find();
		if($result)
		{	
			$logintime = time();
			if($result['endtime']<=$logintime){
				$this->error('帐号使用时间到期！');
				exit;
			}	
			
			$id = 'id='.$result['id'];
			$data['logintime'] = time();
			$data['loginip'] = get_client_ip();

			$_SESSION['UserId'] = $result['id'];
			$_SESSION['loginUserName'] = $result['username'];
			$_SESSION['lastLoginTime'] = $data['logintime'];
			$_SESSION['token'] = $result['token'];
			$_SESSION['sitename'] = $result['sitename'];

			$admin->where($id)->save($data);
			$this->success('登录成功！',__APP__.'/Index/index');
		}
		else
		{
			$this->error("用户不存在！");
		}
    }

	//注销登录
	public function logout(){
		if(isset($_SESSION['UserId'])) {
			unset($_SESSION['UserId']);
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