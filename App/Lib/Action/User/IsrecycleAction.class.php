<?php
/**
 *回收站
 **/
class IsrecycleAction extends CommonAction{
	
	public function index(){
		$db=D('Img');
		if($this->_get('classid')){$where['classid'] = $this->_get('classid');}
		$where['token'] = session('token');
		$where['isrecycle']=-1;
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	
	//删除文章
	public function del(){
		//$id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		if(D('Img')->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	//还原文章
	public function restore(){
		$id=$this->_get('id','intval');
		$where['isrecycle']=0;
		$infos=D('Img')->field('classid')->where(array('id'=>$id))->find();
		$class=D('Classify')->where(array('id'=>$infos['classid'],'isrecycle'=>0))->find();
		if($class){
			$info=D('Img')->where(array('id'=>$id))->save($where);
			if($info){
				$this->success('操作成功',U(MODULE_NAME.'/index'));
			}else{
				$this->error('操作失败',U(MODULE_NAME.'/index'));
			}
		}else{
			$where['classname']='';
			//$where['classid']=0;
			$info=D('Img')->where(array('id'=>$id))->save($where);
			if($info){
				$this->success('操作成功',U(MODULE_NAME.'/index'));
			}else{
				$this->error('操作失败',U(MODULE_NAME.'/index'));
			}
		}
		
		
	
	}
	
	//批量删除文章
	public function delete(){
		$img=D('Img');
		$getid=$this->_request('id');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$img->where(array('id'=>array('IN',$getids)))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	
	//回收站栏目显示
	public function classify(){
		$db=D('Classify');
		$where['token']=session('token');
		$where['isrecycle']=-1;
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('sorts desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display('Isrecycle/classify');
	}
	//删除栏目
	public function dels(){
		$id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		if(D('Classify')->where($where)->delete()){
			//D('Img')->where(array('classid'=>$id))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}public function delq(){
		$id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		if(D('Classify')->where($where)->delete()){
			$info=D('Img')->where(array('classid'=>$id))->delete();
			if($info){	
				$this->success('操作成功',U(MODULE_NAME.'/index'));
			}else{
				$this->error('操作失败',U(MODULE_NAME.'/index'));
			}
		}
	}
	//还原栏目
	public function restores(){
		$id=$this->_get('id','intval');
		//$where['id']=$this->_get('id','intval');
		$where['isrecycle']=0;
		//$infos=D('Img')->field('classid')->where(array('id'=>$id))->find();
		$class=D('Classify')->where(array('id'=>$id))->save($where);
		//if($class){
			//$info=D('Img')->where(array('id'=>$id))->save($where);
		if($class){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
		
	}
	
	//还原栏目以及栏目下所有文档
	public function restoreq(){
		$id=$this->_get('id','intval');
		//$where['id']=$this->_get('id','intval');
		$where['isrecycle']=0;
		//$infos=D('Img')->field('classid')->where(array('id'=>$id))->find();
		$class=D('Classify')->where(array('id'=>$id))->save($where);
		if($class){
			$info=D('Img')->where(array('classid'=>$id))->save($where);
			if($info){	
				$this->success('操作成功',U(MODULE_NAME.'/index'));
			}else{
				$this->error('操作失败',U(MODULE_NAME.'/index'));
			}
		}
		
	}

	//批量删除
	public function deletes(){
		$classify=D('Classify');
		$getid=$this->_request('id');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$classify->where(array('id'=>array('IN',$getids)))->delete();
		if($result){
			D('Img')->where(array('classid'=>array('IN',$getids)))->delete();
			//D('Groupbuylist')->where(array('gid'=>array('IN',$getids)))->delete();
			//M('keyword')->where(array('pid'=>array('IN',$getids),'token'=>session('token')))->delete();
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}