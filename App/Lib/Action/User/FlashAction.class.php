<?php
/**
 *首页幻灯片回复
**/
class FlashAction extends CommonAction{
	public function index(){
	    echo $_GET['type'];
		$db=D('Flash');
		$where['type'] = isset($_GET['type'])?$_GET['type']:0;
		$where['token'] = session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->assign('type',isset($_GET['type'])?$_GET['type']:0);
		$this->display();
	}
	public function add(){
	    $type = isset($_GET['type'])?$_GET['type']:0;
	    $this->assign('type',$type);
	    $this->display();
	}
	public function edit(){
		$where['id']=$this->_get('id','intval');
		//$where['uid']=session('uid');
		///$where['token'] = session('token');
		$res=D('Flash')->where($where)->find();
		//$this->assign('type',isset($_GET['type'])?$_GET['type']:0);
		$this->assign('info',$res);
		$this->display();
	}
	public function del(){
		$where['id']=$this->_get('id','intval');
		$info = M('flash')->where(array('id'=>$this->_get('id','intval')))->find();
		$where['uid']=session('uid');
		$where['token'] = session('token');
		if(D("flash")->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index/?type='.$info['type']));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index/?type='.$info['type']));
		}
	}
	public function insert(){
	    $type = isset($_GET['type'])?$_GET['type']:0;
	    $_POST['type'] = $type;
		$this->all_insert('flash','/index/?type='.$type);
	}
	public function upsave(){
	   
	    $where['id']=$_POST['id'];
		$info = M('flash')->where($where)->find();
		$this->all_save('flash','/index/?type='.$info['type']);
	}

}
?>