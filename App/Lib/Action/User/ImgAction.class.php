<?php
/**
 *图文回复
**/
class ImgAction extends CommonAction{
	public function index(){
		$db=D('Img');
		$where['token'] = session('token');
		$where['isrecycle']=0;
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	
	public function add(){
		$db=M('Classify');
		$where['token']=session('token');
		$class=$db->where($where)->select();
		if($class==false){$this->error('请先添加网站分类',U('Classify/index'));}
		$info=$db->where($where)->select();
		$this->assign('info',$info);
		$this->display();
	}
	
	public function insert(){
		$pat = "/<(\/?)(script|i?frame|style|html|body|title|font|strong|span|div|marquee|link|meta|\?|\%)([^>]*?)>/isU";
		$_POST['info'] = preg_replace($pat,"",$_POST['info']);
		if(empty($_POST['keyword'])){
			$this->error('关键词不能为空');
		}
		$this->all_insert();
	}
	
	public function edit(){
		$db=M('Classify');
		$info=$db->where()->select();
		$where['id']=$this->_get('id','intval');
		$res=D('Img')->where($where)->find();
		$this->assign('info',$res);
		$this->assign('res',$info);
		$this->display();
	}

	public function upsave(){
		if(empty($_POST['keyword'])){
			$this->error('关键词不能为空');
		}
		$this->all_save();
	}

	public function del(){
		$id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		if(D(MODULE_NAME)->where($where)->delete()){
			M('Keyword')->where(array('pid'=>$id,'module'=>'Img'))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	

}
?>