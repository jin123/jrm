<?php
/**
 *微站栏目
**/
class ClassifyAction extends CommonAction{
	public function index(){
		$db=D('Classify');
		$where['token']=session('token');
		$where['isrecycle']=0;
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('sorts desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	
	public function add(){
		$this->display();
	}
	
	public function edit(){
		$id=$this->_get('id','intval');
		$info=M('Classify')->where(array('token'=>session('token')))->find($id);
		$this->assign('info',$info);
		$this->display();
	}
	
	/* public function del(){
		$id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		$where['token']=session('token');
		$res=D('Classify')->where($where)->find();
		if(D(MODULE_NAME)->where($where)->delete()){
			D('Img')->where(array('classid'=>$id))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	} */
	public function del(){
		$id=$this->_get('id','intval');
		//$where['id']=$this->_get('id','intval');
		$token=session('token');
		$where['isrecycle']=-1;
		//$res=D('Classify')->where(array('token'=>$token,'id'=>$id))->find();
		$info=D('Classify')->where(array('token'=>$token,'id'=>$id))->save($where);
		//dump($info);exit;
		if($info){
			D('Img')->where(array('classid'=>$id))->save($where);
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