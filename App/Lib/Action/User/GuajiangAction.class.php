<?php
/**
*刮刮卡
**/
class GuajiangAction extends CommonAction{

	public function index(){
		$count = M('Lottery')->where(array('type'=>2,'token'=>session('token')))->count();
		$page=new Page($count,25);
		$list=M('Lottery')->field('id,title,joinnum,click,keyword,statdate,enddate,status')->where(array('type'=>2,'token'=>session('token')))->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();	
	}
	public function sn(){
		$id=$this->_get('id');
		$data=M('Lottery')->where(array('id'=>$id,'type'=>2,'token'=>session('token')))->find();
		$record=M('Lottery_record')->where('lid='.$id.' and sn!=""')->select();
		$recordcount=M('Lottery_record')->where('lid='.$id.' and sn!=""')->count();
		$datacount=$data['fistnums']+$data['secondnums']+$data['thirdnums'];
		$this->assign('datacount',$datacount);//奖品数量
		$this->assign('recordcount',$recordcount);//中讲数量
		$this->assign('record',$record);
		//
		$sendCount=M('Lottery_record')->where('lid='.$id.' and sendstutas=1 and sn!=""')->count();
		$this->assign('sendCount',$sendCount);
		$this->display();
	
	
	}
	public function add(){
		if(IS_POST){		
			$db=D('Lottery');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate']=strtotime($_POST['enddate']);
			if($db->create()!=false){
				$db->token = session('token');
				if($id=$db->add()){
					$data['pid'] = $id;
					$data['module'] = 'Lottery';
					$data['keyword'] = $_POST['keyword'];
					$data['token'] = session('token');
					M('Keyword')->add($data);
					$this->success('活动创建成功',U('Guajiang/index'));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($db->getError());
			}
			
			
		}else{
			$this->display();
		}
	}
	public function setinc(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');

		$data=M('Lottery')->where($where)->save(array('status'=>1));
		if($data!=false){
			$this->success('恭喜你,活动已经开始');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}

	}
	public function setdes(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->save(array('status'=>0));
		if($data!=false){
			$this->success('活动已经结束');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}
	
	}
	public function edit(){
		if(IS_POST){
			$db=D('Lottery');
			$_POST['id']=$this->_get('id');
			$where=array('id'=>$_POST['id'],'type'=>2,'token'=>session('token'));
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate']=strtotime($_POST['enddate']);			
			$check=$db->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($db->create()){				
				if($id=$db->where($where)->save($_POST)){
					$data['pid']=$_POST['id'];
					$data['module']='Lottery';
					$da['keyword']=$_POST['keyword'];
					M('Keyword')->where($data)->save($da);
					$this->success('修改成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($db->getError());
			}
			
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'type'=>2,'token'=>session('token'));
			$data=M('Lottery');
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$data->where($where)->find();		
			$this->assign('vo',$lottery);
			//dump($lottery);
			$this->display('add');
		}
	
	}
	public function del(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$db=M('Lottery');
		$check=$db->where($where)->find();
		if($check==false)$this->error('非法操作');
		$back=$db->where($where)->delete();
		if($back==true){
			M('Keyword')->where(array('pid'=>$id,'module'=>'Lottery','token'=>session('token')))->delete();
			$this->success('删除成功');
		}else{
			$this->error('操作失败');
		}
	
	
	}
	
	public function sendprize(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = time();
		$data['sendstutas'] = 1;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('成功发奖');
		}else{
			$this->error('操作失败');
		}
	}
	
	public function sendnull(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = '';
		$data['sendstutas'] = 0;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('已经取消');
		}else{
			$this->error('操作失败');
		}
	}
}


?>