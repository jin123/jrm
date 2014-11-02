<?php
/**
 *图文回复
**/
class TuAction extends CommonAction{
	public function index(){
		$db=D('Img');
		if($this->_get('classid')){$where['classid'] = $this->_get('classid');}
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
		$where['isrecycle']=0;
		$class=$db->where($where)->select();
		if($class==false){$this->error('请先添加网站分类',U('Classify/index'));}
		$info=$db->where($where)->select();
		$this->assign('info',$info);
		$this->display();
	}
	
	public function insert(){
		/* $pat = "/<(\/?)(script|i?frame|style|html|body|title|font|strong|span|div|marquee|link|meta|\?|\%)([^>]*?)>/isU";
		$_POST['info'] = preg_replace($pat,"",$_POST['info']);
		$this->all_insert('Img'); */
		
		$img = D('Img');
		$pat = "/<(\/?)(script|i?frame|style|html|body|title|font|strong|span|div|marquee|link|meta|\?|\%)([^>]*?)>/isU";
		$_POST['info'] = preg_replace($pat,"",$_POST['info']);
		$_POST['token'] = session('token');
		$_POST['createtime']=time();
		$classid=explode(',', $_POST['classid']);
		$_POST['classname']=$classid[1];

		$id = $img->add($_POST);
		
		if($id){
        	$this->success('操作成功！',U(MODULE_NAME.'/index'));
		}else{
			$this->error('增加失败！');	
		}
	}
	
	public function edit(){
		$db=M('Classify');
		$where['token'] = session('token');
		$where['isrecycle']=0;
		$info=$db->where($where)->select();
		$where['id']=$this->_get('id','intval');
		$res=D('Img')->where($where)->find();
		$this->assign('info',$res);
		$this->assign('res',$info);
		$this->display();
	}

	public function upsave(){
		//$this->all_save('Img');
		
		$img = D('Img');
		$_POST['token'] = session('token');
		$_POST['createtime']=time();
		$classid=explode(',', $_POST['classid']);
		$_POST['classname']=$classid[1];
		//$img->create();
		$id = $img->save($_POST);
		if($id){
        	$this->success('操作成功！',U(MODULE_NAME.'/index'));
		}else{
			$this->error('增加失败！');	
		}
	}

	/* public function del(){
	 * $id=$this->_get('id','intval');
		$where['id']=$this->_get('id','intval');
		if(D('Img')->where($where)->delete()){
			M('Keyword')->where(array('pid'=>$id,'module'=>'Img'))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	} */
	public function del(){
		$id=$this->_get('id','intval');
		$where['isrecycle']=-1;
		$info=D('Img')->where(array('id'=>$id))->save($where);
		if($info){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
		
	}
	

}
?>