<?php

class PayAction extends SystemAction{
	
	
	public function index(){
		$db=M('Daili');
		$dlid = session('dluserId');

		if(IS_POST){
			if(empty($_POST['userpwd']) || $_POST['userpwd'] != $_POST['userpwdok']) {
				$this->error('请确认新密码！');
			}
			$where['dlpwd'] =  $this->_post('oldpwd','md5');
			$result =$db->where($where)->find();
			if(!$result){
				$this->error('原始密码错误，请确认新密码！');
			}
			$data['dlpwd'] =  $this->_post('userpwd','md5');
			$id .= 'id ='.$this->_post('id');
			$admin =$db->where($id)->save($data);
			if($admin){
				$this->success("修改密码成功！");
			}else{
				$this->error("修改密码失败！");
			}
		}

		$data=$db->where(array('id'=>$dlid))->find();
		list($data['copy'],$data['url']) = split("[|]", $data['copyright']);
		$this->assign('dinfo',$data);
		$this->display();
	}

	public function post(){
		$wxuser = D('Daili');
		$dlid = session('dluserId');
		$data = $wxuser->where(array('id'=>$dlid))->find();
         //充值记录
		$record = M('Inrecords');
		$subject ="充值一次";
		$out_trade_no = session('dluserId').time();


		if(IS_POST){
			$where['id'] = $data['id'];
			$_POST['account'] = $data['account'] + $_POST['price'];
			$wxuser->where($where)->save($_POST);
			$this->success('充值成功！');
			$record->data(array('uid'=>$dlid,'title'=>$subject,'agentname'=>$data["dluser"],'create_time'=>time(),'indent_id'=>$out_trade_no,'price'=>$_POST['price']))->add();
		}else{
		list($data['copy'],$data['url']) = split("|", $data['copyright']);
		$this->assign('data',$data);
		$this->display();
		}
	}
	

}
?>