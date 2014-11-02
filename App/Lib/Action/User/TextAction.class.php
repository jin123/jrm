<?php
/**
 *文本回复
**/
class TextAction extends CommonAction{
	public function index(){
		$db=D('Text');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		import('@.ORG.Page');
		$page=new Page($count,25);
		$info=$db->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	public function add(){
		$this->display();
	}
	public function edit(){
		$where['id']=$this->_get('id','intval');
		$res=D('Text')->where($where)->find();
		if(!$res)
		{
			$this->error('参数不正确！',U('Text/index'));	
		}
		$this->assign('info',$res);
		$this->display();
	}

	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		if(D(MODULE_NAME)->where($where)->delete()){
			M('Keyword')->where(array('pid'=>$this->_get('id','intval'),'module'=>'Text','token'=>session('token')))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	
	public function insert(){
		$this->all_insert();
	}
	
	public function upsave(){
		$this->all_save();
	}


}
?>