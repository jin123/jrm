<?php
/**
 *首页幻灯片回复
**/
class FlashshopAction extends CommonAction{
	public function index(){
		$db=D('Flash');
		$where['token'] = session('token');
		$where['type'] = isset($_GET['type'])?$_GET['type']:0;
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
	//	echo $db->getlastsql();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->assign('type',$_GET['type']);
		$this->display();
	}
	public function add(){
	    $this->assign('type',$_GET['type']);
		$this->display();
	}
	public function edit(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		$where['token'] = session('token');
		$res=D('Flash')->where($where)->find();
		$this->assign('info',$res);
		$this->display();
	}
	public function del(){
	    $where['id']=$this->_get('id','intval');
		$info = M('flash')->where(array('id'=>$this->_get('id','intval')))->find();
		$where['uid']=session('uid');
		$where['token'] = session('token');
		$del = D('flash')->where($where)->delete();
		if($del){
			$this->success('操作成功',U(MODULE_NAME.'/index/?type='.$info['type']));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index/?type='.$info['type']));
		}
	}
	public function insert(){
		//C('TOKEN_ON',false);
		$_POST['type'] = $_GET['type'];
		$this->all_insert('flash','/index/?type='.$_GET['type']);
	}
	public function upsave(){
		 $where['id']=$_POST['id'];
		$info = M('flash')->where($where)->find();
		$this->all_save('flash','/index/?type='.$info['type']);
	}

}
?>