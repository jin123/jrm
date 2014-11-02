<?php
class LotteryAction extends CommonAction{
	public function index(){
		$count = M('Lottery')->where(array('type'=>1,'token'=>session('token')))->count();
		$page=new Page($count,25);
		$list=M('Lottery')->where(array('type'=>1,'token'=>session('token')))->limit($page->firstRow.','.$page->listRows)->select();		
		
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	
	}
	public function sn(){
		$id=$this->_get('id');
		$data=M('Lottery')->where(array('token'=>session('token'),'id'=>$id))->find();
		$record=M('Lottery_record')->where('lid='.$id.' and sn!=""')->select();
		$recordcount=M('Lottery_record')->where('lid='.$id.' and sn!=""')->count();
		$datacount=$data['fistnums']+$data['secondnums']+$data['thirdnums']+$data['four']+$data['five']+$data['six'];
		$this->assign('datacount',$datacount);
		$this->assign('recordcount',$recordcount);
		$this->assign('record',$record);	
		$this->display();	
	}
	public function add(){
		if(IS_POST){
			//add the use times . 
			$data=D('lottery');
			$_POST['statdate'] = strtotime($this->_post('statdate'));
			$_POST['enddate'] = strtotime($this->_post('enddate'));
			$this->all_insert('Lottery');
		}else{
			$this->display();
		}
	}
	public function setinc(){
		$id=$this->_get('id');
		$where=array('token'=>session('token'),'id'=>$id);
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		if ($check['status']==0){
			$data=M('Lottery')->where($where)->save(array('status'=>1));
			$tip='恭喜你,活动已经开始';
		}else {
			$data=M('Lottery')->where($where)->save(array('status'=>0));
			$tip='设置成功,活动已经结束';
		}
		if($data!=false){
			$this->success($tip);
		}else{
			$this->error('设置失败');
		}

	}
	public function setdes(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->setDec('status');
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
			$_POST['token']=session('token');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate']=strtotime($_POST['enddate']);
			if(empty($_POST['fist']) || empty($_POST['fistnums'])){
				$this->error('必须设置一等奖奖品和数量');
				exit;
			}
			$where=array('id'=>$_POST['id'],'token'=>session('token'));
			$check=$db->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($db->create()){
				if($db->where($where)->save($_POST)){
					$data['pid'] = $_POST['id'];
					$data['module'] = 'Lottery';
					$data['token'] = session('token');
					$da['keyword'] = $_POST['keyword'];
					M('Keyword')->where($data)->save($da);
					$this->success('修改成功',U('Lottery/index'));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($db->getError());
			}
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'token'=>session('token'));
			$db=M('Lottery');
			$check=$db->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$db->where($where)->find();	
			$this->assign('vo',$lottery);
			//dump($_POST);
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
		$where=array('id'=>$id);
		$data['sendtime'] = time();
		$data['sendstutas'] = 1;
		$back = M('Lottery_record')->where($where)->save($data);
/*		echo M('Lottery_record')->getLastSql();
		exit();
*/
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