<?php
class LabelAction extends CommonAction{
	
 
	//标签显示
	public function index(){
		$userlabel=D('UserLabel');
		
		$count=$userlabel->count();
		$page=new Page($count,25);
		$list = $userlabel->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('list',$list);	
		$this->assign('page',$page->show());	
		$this->display('Member/labelindex');
	}
	
	//添加标签
	public function labeladd(){
		$userlabel=D('UserLabel');
		if(IS_POST){
			if($userlabel->create()){
				$userlabel->add();
			}
		}else{
			$this->display('Member/labeladd');
		}	
	}
	
	//修改标签
	public function edit(){
		$userlabel=D('UserLabel');
		$id=$this->_get('id');
		$data=$userlabel->where(array('id'=>$id))->find();
		$this->assign('data',$data);
		if(IS_POST){
			if($userlabel->create()){
				$userlabel->where(array('id'=>$id))->save();
			}
		}else{
			$this->display('Member/labeladd');
		}
	}
	
	//删除标签
	public function del(){
		$userlabel=M('UserLabel');
		$id=$this->_get('id');
		$data=M('User_tag')->where(array('label_id'=>$id))->count();
		if($data!=0){
			$this->error('用户已使用该标签，不能删除');
		}else{
			if($userlabel->delete($id)){
				
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}
	}
	
	//用户显示标签
	public function tag(){
		 $count=M('userinfo')->where(array('token'=>session('token')))->count();
	   $page=new Page($count,10);
       
		$list=M('userinfo')->field('wecha_id,wechaname')->where(array('token'=>session('token')))->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
		foreach($list as $k=>$v){
			//$group_info = $this->find_user_group($v['wecha_id']);
			$user_tag_info = M("user_tag")->where('wecha_id='.'"'.$v['wecha_id'].'"')->select();
			$list[$k]['label_list'] = '';
			if($user_tag_info){
				$lab_id = 0;
				$ids = '';
				foreach($user_tag_info as $v){
					$ids.=",".$v['label_id'];
				}
				$lab_id = substr($ids,1);
				$map['id'] = array('exp',' IN ( '.$lab_id.')');
				$user_lable = M("user_label")->where($map)->select();
				$label_list = '';
				foreach($user_lable as $v){
					$label_list.=",".$v['labelname'];
				}
				$label_list = substr($label_list,1);
				$list[$k]['label_list'] = $label_list;
			}			
		}
			$this->assign('list',$list);
			$this->assign('page',$page->show());
			$this->display('Member/tag');
	}
	

	
	//标签选择
	public function adduserlabel(){
		$userlabel=D('UserLabel');
		$wecha_id=$this->_get('wecha_id');
		$lable_list= array();
		$arr = D('UserTag')->where('wecha_id='.'"'.$wecha_id.'"')->select();
		if($arr){
			foreach($arr as $v){			
			    array_push($lable_list,$v['label_id']);			
			}
		}
		$this->assign('lable_list',$lable_list);
		unset($lable_list);
		$this->assign('wecha_id',$wecha_id);
		$count=$userlabel->count();
		$page=new Page($count,25);
		$list = $userlabel->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display('Member/adduserlabel');
	}
	//批量添加标签
	public function addlabel_list(){
		
		if(IS_POST){
			$usertag=D('UserTag');
			$getid=$_POST['id'];
			$getwecha_id=$_POST['wecha_id'];
			$res=$usertag->where(array('wecha_id'=>$getwecha_id))->count();
			if($res!=0){
				$res=$usertag->where(array('wecha_id'=>$getwecha_id))->delete();
			}
			if(!$getid){
				$this->error('请选择标签');				
			}
			foreach($getid as $v){
			     $data['label_id'] = $v;
				 $data['wecha_id'] = $getwecha_id;
				 $data['ctime'] = time();
				 if($usertag->create()){
			     	$res = $usertag->add($data);
				 }
			}
		}
	}
	
	public function dellabel(){
		$usertag=D('UserTag');
		$getwecha_id=$this->_get('wecha_id');
		if($usertag->where(array('wecha_id'=>$getwecha_id))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

	//汇总各标签用户
	public function labelusergroup(){
		$prefix = C ( 'DB_PREFIX' );
		$label_id=$this->_get('id');
		$labelname=M('UserLabel')->field('labelname')->where(array('id'=>$label_id))->find();
		$sql="select * from {$prefix}userinfo where wecha_id in (select wecha_id from {$prefix}user_tag where label_id = {$label_id}) ";
		$Model = new Model();
		$result=$Model->query($sql);
		$this->assign('labelname',$labelname);
		$this->assign('userinfo',$result);
		$this->display('Member/labelusergroup');
	}
	
	public function signset(){
		
		 $data = M('Member_card_exchange')->where(array(

            'token' => $this->token,

        ))->find();

        if (IS_POST) {
		
            $_POST['token']       = $this->token;

         
            $_POST['create_time'] = time();
			//
            if ($data == false) {

                $this->all_insert('Member_card_exchange', '/signset?id=' . $this->thisCard['id']);
				
            } else {

                $_POST['id'] = $data['id'];

                $this->all_save('Member_card_exchange', '/signset?&id=' . $this->thisCard['id']);

            }

        } else {

            $this->assign('signset', $data);

            $this->display('Member/signset');

        }

	}
	
	public function sign(){
		
		$sign=M('Userinfo');
		$where=array('token'=>session('token'));
		$data=$sign->where($where)->select();
		foreach ($data as $k=>$v){
			if($v['total_score']!="0"){
				
				$info=M('MemberCardSign')->field('sign_time')->where(array('wecha_id'=>$v['wecha_id']))->order('sign_time desc')->find();
				$data[$k]['sign_time']=$info['sign_time'];
			}else{
				unset($data[$k]);
			}
		} 
		
		$this->assign('data',$data);
		$this->display('Member/sign');
	
	}
	
	
	
}