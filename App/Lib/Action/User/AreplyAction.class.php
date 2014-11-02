<?php
/**
 *关注回复
**/
class AreplyAction extends CommonAction{
	public function index(){
		$db=D('Areply');
		$where['token']=$_SESSION['token'];
		$res=$db->where($where)->find();
		$this->assign('areply',$res);
		$this->display();
	}
	
	public function insert(){
		$db=D('Areply');
		$res=$db->where(array('token' => $_SESSION['token']))->find();
		if($res==false){
			$where['content']=$this->_post('content');
			if(isset($_POST['keyword'])){
				$where['keyword']=$this->_post('keyword');
				$where['token']=$this->_session('token');
			}			
			if($where['content']==false){$this->error('内容必须填写');}
				$where['createtime']=time();
				$id=$db->data($where)->add();
			if($id){
				$this->success('发布成功',U('Areply/index'));
			}else{
				$this->error('发布失败',U('Areply/index'));
			}
		}else{
			$data['id']=$res['id'];
			$data['content']=$this->_post('content');
			$data['home']=intval($this->_post('home'));
			$data['updatetime']=time();
			if(isset($_POST['keyword'])){
				$data['keyword']=$this->_post('keyword');
			}
			$where['token']=$this->_session('token');
			if($db->where($where)->save($data)){
				$this->success('更新成功',U('Areply/index'));
			}else{
				$this->error('更新失败',U('Areply/index'));
			}
		}
	}
}
?>