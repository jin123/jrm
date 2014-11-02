<?php

class EducationAction extends CommonAction{
		//教育机构简介
	public function index(){
			$where['token'] = session('token');
			$count = M('jiaoyu_jigou')->where($where)->count();
			$page=new Page($count,25);
			$result=M('jiaoyu_jigou')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
			$this->assign('page',$page->show());
			$this->assign('list',$result);
			$this->display();
	}
	public function selectjg(){
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$result = M('jiaoyu_jigou')->where($where)->find();
		if(IS_POST){
			if ($result == null) {
				$this->all_insert('jiaoyu_jigou');
            } else {
				$this->all_save('jiaoyu_jigou');
            }	
		}else{
			$this->assign('list',$result);
			$this->display();
		}
		
	}
	//删除机构
	public function del(){
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$result = M('jiaoyu_jigou')->where($where)->delete();
		if($result){
			$where['jid'] = $this->_get('id');
			$where['token'] = session('token');
			M('jiaoyu_haibao')->where($where)->delete();
			M('jiaoyu_jiangshi')->where($where)->delete();
			M('jisoyu_kecheng')->where($where)->delete();
			M('jiaoyu_xiangce')->where($where)->delete();
			M('jiaoyu_xiaoqu')->where($where)->delete();
			M('jiaoyu_yingxiang')->where($where)->delete();
			$this->success('成功删除',U('Education/selectjg'));
		}else{
			$this->error('删除失败');
		}
	}
	//宣传海报
	
	public function poster(){
		$hb = M('jiaoyu_haibao');
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$result = $hb->where($where)->select();
		$this->assign('jid',$this->_get('jid'));
		$this->assign('list',$result);
		$this->display();
		
	}
	public function setposter(){
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$result = M('jiaoyu_haibao')->where($where)->find();
	
		if(IS_POST){
			if(empty($_POST['name'])){
				$this->error('海报名不能为空');
			}else{
				$data['name'] = $this->_post('name');
				$data['jid'] = $this->_post('jid');
				$data['pic1'] = $this->_post('pic1');
				$data['pic2'] = $this->_post('pic2');
				$data['pic3'] = $this->_post('pic3');
				$data['pic4'] = $this->_post('pic4');
				$data['pic5'] = $this->_post('pic5');
				$data['token'] = session('token');
				if($result != null){
					$where['id'] = $result['id'];
					$data = M('jiaoyu_haibao')->where($where)->save($data);	
				}else{
					$data = M('jiaoyu_haibao')->add($data);	
				}
				
				if($data){
					$this->redirect('Education/poster',array('jid'=>$this->_post('jid')));
				}else{
					$this->error('添加失败');
				}	
			}
		}else{
			$this->assign('list',$result);
			$this->assign('jid',$this->_get('jid'));
			$this->display();
		}
	} 


	//删除海报
	public function hbdelete(){
		$where['id'] = $this->_get('id');
		$where['jid'] = $this->_get('jid');
		$result = M('Jiaoyu_haibao') ->where($where)->delete();
		if($result){
			$this->redirect('Education/poster',array('id'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}
	
	
	
	//校区
	public function campus(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$count = M('jiaoyu_xiaoqu')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_xiaoqu')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		echo M('jiaoyu_xiaoqu')->getLastSql();
		$this->assign('page',$page->show());
		$this->assign('list',$result);
		$this->assign('jid',$this->_get('jid'));
		$this->display();
		
	}
	
	//校区添加修改
	public function setcampus(){
		
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$where['jid'] = $this->_get('jid');
		$result = M('jiaoyu_xiaoqu')->where($where)->find();
		if(IS_POST){
			$data['name'] = $this->_post('name');
			$data['sort'] = $this->_post('sort');
			$data['jianjie'] = $this->_post('jianjie');
			$data['jid'] = $this->_post('jid');
			$data['token'] = $_SESSION['token'];
			
			
			if($result == null){
				$data = M('jiaoyu_xiaoqu')->add($data);	
			}else{
				$data = M('jiaoyu_xiaoqu')->where($where)->save($data);
			}
			if($data){
				$this->redirect('Education/campus',array('jid'=>$this->_post('jid')));
			}else{
				$this->error('添加失败');
			}	
		}else{
			$this->assign('jid',$this->_get('jid'));
			$this->assign('list',$result);
			$this->display();
		}
	}

	public function delcampus(){
		$where['id'] = $this->_get('id');
		$where['jid'] = $this->_get('jid');
		$result = M('Jiaoyu_xiaoqu')->where($where)->delete();
		if($result){
			$this->redirect('Education/campus',array('jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}
	
	
	//培训课程
	public function course(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$count = M('jiaoyu_kecheng')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_kecheng')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('list',$result);
		$this->assign('jid',$this->_get('jid'));
		
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$result = M('jiaoyu_xiaoqu')->field('id,name')->where($where)->select();
		$this->assign('xiaoqu',$result);
		$this->display();
	}

	public function setcourse(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$result = M('jiaoyu_xiaoqu')->where($where)->select();
		$where['id'] = $this->_get('id');
		$list = M('jiaoyu_kecheng')->where($where)->find();

		if(IS_POST){
			$data['name'] = $this->_post('name');
			$data['campus'] = $this->_post('campus');
			$data['pic'] = $this->_post('pic');
			$data['describe'] = $this->_post('describe');
			$data['sort'] = $this->_post('sort');
			$data['jid'] = $this->_get('jid');
			$data['token'] = session('token');
			
			if($list == null){
				$data = M('jiaoyu_kecheng')->add($data);
			}else{
				$data = M('jiaoyu_kecheng')->where($where)->save($data);
			}
			if($data){
				$this->redirect('Education/course',array('jid'=>$this->_get('jid')));
			}else{
				$this->error('添加失败');
			}
				
		}else{
			$this->assign('result',$result);
			$this->assign('list',$list);
			$this->assign('jid',$jid);
			$this->display();	
		}
	}
	
	public function delcourse(){
		$where['id'] = $this->_get('id');
		$where['jid'] = $this->_get('jid');
		$result = M('jiaoyu_kecheng')->where($where)->delete();
		if($result){
			$this->redirect('Education/course',array('jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}	
	
	/* 讲师风采 20*/
	public function jiangshi(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$count = M('jiaoyu_jiangshi')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_jiangshi')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('list',$result);
		$this->assign('jid',$this->_get('jid'));
		$this->display();
	}


	public function setjs(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$where['id'] = $this->_get('id');
		$list = M('jiaoyu_jiangshi')->where($where)->find();

		if(IS_POST){
			$data['name'] = $this->_post('name');
			$data['pic'] = $this->_post('pic');
			$data['jianjie'] = $this->_post('jianjie');
			$data['jid'] = $this->_get('jid');
			$data['token'] = $_SESSION['token'];
			
			if($list == null){
				$data = M('jiaoyu_jiangshi')->add($data);
			}else{
				$data = M('jiaoyu_jiangshi')->where($where)->save($data);
			}
			if($data){
				$this->redirect('Education/jiangshi',array('jid'=>$this->_get('jid')));
			}else{
				$this->error('添加失败');
			}
				
		}else{
			$this->assign('list',$list);
			$this->assign('jid',$this->_get('jid'));
			$this->display();	
		}
	}
	
	public function deljs(){
		$where['id'] = $this->_get('id');
		$where['jid'] = $this->_get('jid');

		$result = M('jiaoyu_jiangshi')->where($where)->delete();
		if($result){
			$this->redirect('Education/jiangshi',array('jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}	
	

	//教学相册
	public function photo(){

		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$count = M('jiaoyu_xiangce')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_xiangce')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('photo',$result);
		$this->assign('jid',$this->_get('jid'));
		$this->display();			

	}

	public function setphoto(){
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$where['id'] = $this->_get('id');
		$list = M('jiaoyu_xiangce')->where($where)->find();

		if(IS_POST){
			$data['name'] = $this->_post('name');
			$data['pic'] = $this->_post('pic');
			$data['jianjie'] = $this->_post('jianjie');
			$data['jid'] = $this->_get('jid');
			$data['token'] = $_SESSION['token'];
			
			if($list == null){
				$data = M('jiaoyu_xiangce')->add($data);
			}else{
				$data = M('jiaoyu_xiangce')->where($where)->save($data);
			}
			if($data){
				$this->redirect('Education/photo',array('jid'=>$this->_get('jid')));
			}else{
				$this->error('添加失败');
			}
				
		}else{
			$this->assign('list',$list);
			$this->assign('jid',$jid);
			$this->display();	
		}
	}

	public function xcdelete(){
		$where['id'] = $this->_get('id');
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$result = M('jiaoyu_xiangce')->where($where)->delete();
		if($result){
			$data['pid'] = $this->_get('id');
			$data['token'] = session('token');
			M('jiaoyu_xiangpian')->where($data)->delete();
			$this->redirect('Education/photo',array('jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}


	public function list_add(){
	
		$where['pid'] = $this->_get('id');
		$where['token'] = session('token');
		$count = M('jiaoyu_xiangpian')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_xiangpian')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('list',$result);
		$this->assign('jid',$this->_get('jid'));
		$this->assign('id',$this->_get('id'));
		$this->display();			
		
	}

	public function addlist(){
		$data['pid'] = $this->_post('pid');
		$data['name'] = $this->_post('name');
		$data['pic'] = $this->_post('pic');
		$data['sort'] = $this->_post('sort');
		$data['student'] = $this->_post('status');
		$data['jianjie'] = $this->_post('jianjie');
		$data['token'] = session('token');
		$result = M('jiaoyu_xiangpian')->add($data);
		$this->redirect('Education/list_add',array('id'=>$this->_post('pid'),'jid'=>$this->_post('jid')));
	}
	
	
	public function list_edit(){
		$check=M('jiaoyu_xiangpian')->field('id,pid')->where(array('id'=>$this->_post('id')))->find();
		$pid = $this->_post('pid');
		if($check==false){$this->error('照片不存在');}
		if(IS_POST){

			$data['name'] = $this->_post('name');
			$data['pic'] = $this->_post('pic');
			$data['sort'] = $this->_post('sort');
			$data['student'] = $this->_post('status');
			$data['jianjie'] = $this->_post('jianjie');
			$data['token'] = session('token');
			
			$where['id'] =$this->_post('id');
			$data = M('jiaoyu_xiangpian')->where($where)->save($data);
			if($data){
				$this->redirect('Education/list_add',array('id'=>$this->_post('pid'),'jid'=>$this->_post('jid')));
			}else{
				$this->error('修改失败');
			}
			
		}else{
			$this->error('非法操作');
		}
	}	
	
	public function list_del(){
		$where['id'] = $this->_get('id');
		$where['pid'] = $this->_get('pid');
		$where['token'] = session('token');
		$result = M('jiaoyu_xiangpian')->where($where)->delete();
		if($result){
			$this->redirect('Education/list_add',array('id'=>$this->_get('pid'),'jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}	
	
	
	
	//学院印象
	public function effect(){
	
		$where['jid'] = $this->_get('jid');
		$where['token'] = session('token');
		$count = M('jiaoyu_yingxiang')->where($where)->count();
		$page=new Page($count,25);
		$result=M('jiaoyu_yingxiang')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('list',$result);
		$this->assign('jid',$this->_get('jid'));
		$this->display();			
		
	}
	
	public function seteffect(){
		$where['jid'] = $this->_get('jid');
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$list = M('jiaoyu_yingxiang')->where($where)->find();
		if(IS_POST){

			$data['name'] = $this->_post('name');
			$data['content'] = $this->_post('content');
			$data['sort'] = $this->_post('sort');
			$data['isno'] = $this->_post('isno');
			$data['token'] = session('token');
			
			$where['id'] = $this->_post('id');
			$where['jid'] = $this->_post('jid');

			if($list == null){
				$data = M('jiaoyu_yingxiang')->add($data);
			}else{
				$data = M('jiaoyu_yingxiang')->where($where)->save($data);
			}
			if($data){
				$this->redirect('Education/jiangshi',array('jid'=>$this->_get('jid')));
			}else{
				$this->error('添加失败');
			}
				
		}else{
			$this->assign('list',$list);
			$this->assign('jid',$jid);
			$this->display();	
		}
	}	
	

	public function save(){
		$where['id'] = $this->_get('id');
		$where['jid']= $this->_get('jid');
		$where['isno'] = $this->_get('isno');
		if($isno==0){
			$data['isno'] ='1';
			$result = M('jiaoyu_yingxiang ')->where("id=$id AND `token`='{$_SESSION['token']}'")->save($data);
			$this->redirect('Education/effect',array('jid'=>$this->_get('jid')));
		}else{
			$data['isno'] ='0';
			$result = M('jiaoyu_yingxiang ')->where("id=$id AND `token`='{$_SESSION['token']}'")->save($data);
			$this->redirect('Education/effect',array('jid'=>$this->_get('jid')));
		}
		
	}
	public function yxdelete(){
		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$result = M('jiaoyu_yingxiang')->where($where)->delete();
		if($result){
			$this->redirect('Education/effect',array('jid'=>$this->_get('jid')));
		}else{
			$this->error('删除失败');
		}
	}




	public function addjs(){
		$jid = $_GET['jid'];
		$this->assign('jid',$jid);
		$this->display();
	}
	public function addjas(){
		$js = M('jiaoyu_jiangshi');
		$data['jid'] = $_POST['jid'];
		$data['name'] = $_POST['name'];
		$data['pic'] = $_POST['pic'];
		$data['jianjie'] = $_POST['jianjie'];
		
		$data['token'] = $_SESSION['token'];
		$result = $js->add($data);
		if($result){
			$this->redirect('Education/jiangshi',array('jid'=>$_POST['jid']));
		}else{
			$this->error('数据添加失败');
		}
	}
	public function updatejs(){
		$js = M('jiaoyu_jiangshi');
		$id = $_GET['id'];
		$result = $js->where("id=$id AND `token`='{$_SESSION['token']}'")->find();
		//dump($result);
		//exit;
		$this->assign('list',$result);
		$this->display();
	}
	public function updatejas(){
		$js = M('jiaoyu_jiangshi');
		$id = $_POST['id'];
		$jid = $_POST['jid'];
		
		$data['name'] = $_POST['name'];
		$data['pic'] = $_POST['pic'];
		$data['jianjie'] = $_POST['jianjie'];
		$result = $js->where("id=$id AND `token`='{$_SESSION['token']}'")->save($data);
		if($result){
			$this->redirect('Education/jiangshi',array('jid'=>$_POST['jid']));
		}else{
			$this->error('修改失败');
		}
	}

}
?>