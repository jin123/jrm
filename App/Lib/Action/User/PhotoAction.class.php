<?php
class PhotoAction extends CommonAction{
	public function index(){		
		//相册列表
		$data=M('Photo');
		$where['token'] = session('token');
		$count      = $data->where($where)->count();
		$Page       = new Page($count,12);
		$show       = $Page->show();
		$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();	
		$this->assign('page',$show);		
		$this->assign('photo',$list);
		$this->display();		
	}
	public function edit(){
		$data=D('Photo');
		if(IS_POST){
			$this->all_save('Photo');
		}else{
			$photo=$data->where(array('id'=>$this->_get('id')))->find();
			if($photo==false){
				$this->error('相册不存在');
			}else{
				$this->assign('photo',$photo);
			}
			$this->display();		
		
		}
	}
	public function list_edit(){
		$check=M('Photo_list')->field('id,pid')->where(array('id'=>$this->_post('id')))->find();
		$pid = $this->_post('pid');
		if($check==false){$this->error('照片不存在');}
		if(IS_POST){
			$this->all_save("Photo_list","/index/");		
		}else{
			$this->error('非法操作');
		}
	}
	public function list_del(){
		$check=M('Photo_list')->field('id,pid')->where(array('id'=>$this->_get('id')))->find();
		if($check==false){$this->error('服务器繁忙');}
		if(empty($_POST['edit'])){
			if(M('Photo_list')->where(array('id'=>$check['id']))->delete()){
				M('Photo')->where(array('id'=>$check['pid']))->setDec('num');
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	public function list_add(){
		$checkdata=M('Photo')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->find();
		if($checkdata==false){$this->error('相册不存在');}

		if(IS_POST){			
			M('Photo')->where(array('id'=>$this->_post('pid')))->setInc('num');
			$this->all_insert('Photo_list');		
		}else{
			$db=M('Photo_list');
			$count      = $db->where(array('pid'=>$this->_get('id'),'token'=>session('token')))->count();
			$Page       = new Page($count,120);
			$show       = $Page->show();
			$list = $db->where(array('pid'=>$this->_get('id'),'token'=>session('token')))->order('sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('page',$show);		
			$this->assign('photo',$list);
			$this->display();	
		
		}
		
	}
	public function add(){
		if(IS_POST){			
			$this->all_insert('Photo','/add');			
		}else{
			$this->display();	
		
		}
		
	}
	public function del(){
		$check=M('Photo')->field('id')->where(array('id'=>$this->_get('id')))->find();
		if($check==false){$this->error('服务器繁忙');}
		if(empty($_POST['edit'])){
			if(M('Photo')->where(array('id'=>$check['id']))->delete()){
				M('Photo_list')->where(array('pid'=>$check['id']))->delete();
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	
	}


}


?>