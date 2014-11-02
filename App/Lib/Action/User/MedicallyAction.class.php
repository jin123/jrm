<?php
/**
*留言板
**/
class MedicallyAction extends CommonAction{
	public $pid;
	
	public function __construct(){
		parent::__construct();
		$where['token'] = session('token');
		$where['hid']=$this->_get('hid');
        $ly_data = M('Yl_liuyan_set')->where($where)->find();
		$this->pid = $ly_data['id'];
	}
	
	public function index(){
		$db = M('Yl_liuyan_set');
		$where['token'] = session('token');
		$hid=$this->_get('hid');
		//dump($hid);exit;
		$this->assign('hid',$hid);
		$where['hid'] = $hid;
        $ly_data = $db->where($where)->find();
        if (IS_POST) {
            if ($ly_data == null) {
            	$_POST['token']=session('token');
	            if ($db -> create($_POST)){
					$result = $db->add();
					if ($result){
						$this->success('留言设置成功',U('Medically/index',array('hid'=>$hid)));
					}else{
						$this->error('服务器繁忙,请稍候再试');
					}
				}else{
					$this->error($db->getError());
				}
            } else {
	            $check = $db->where($where)->find();
				if ($check == false)$this->error('非法操作');
				if ($db -> create()){
					$_POST['updatetime']=time();
					if ($db->where($where)->save($_POST)){
						$this->success('修改成功',U('Medically/index',array('hid'=>$hid)));
					}else{
						$this->error('操作失败');
					}
				}else{
					$this->error($db->getError());
				}
            }
        } else {
            $this->assign('ly_data', $ly_data);
            $this->display('Medical/liuyan');
        }
	}

	public function liuyan(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		//dump($hid);exit;
		$token= session('token');
		$count = M('Yl_liuyan')->where(array('pid'=>$this->pid))->count();
		$page = new Page($count,10);
		$limit = $page->firstRow.','.$page->listRows;		
		$ly = M('Yl_liuyan')->where(array('pid'=>$this->pid))->limit($limit)->order('id desc')->select();
		$this->assign('ly',$ly);
		$this->assign('page',$page->show());
		$this->display('Medical/liuyanuser');

	}

	public function del(){
		$hid=$this->_get('hid');
		$where['id'] = I ('id','','intval');
		$where['pid'] = $this->pid;
		if (M('Yl_liuyan')->where($where)->delete()) {
			$this->success('删除成功', U('Medically/liuyan',array('hid'=>$hid)));
		}else{
			$this->error('删除失败');
		}
	}

	public function save(){
		$hid=$this->_get('hid');
		$id = I ('id','','intval');
		$data = M('Yl_liuyan')->where(array('id'=>$id,'pid'=>$this->pid))->find();
		
		if($data['isval']==0){
			$arr = array('isval' => '1');
		}else{
			$arr = array('isval' => '0');
		}

		if (M('Yl_liuyan')->where('id='.$id)->data($arr)->save()){
			$this->success('完成设置', U('Medically/liuyan',array('hid'=>$hid)));
		}else {
			$this->error('审核失败');
		}	

	}
			
	public function shenhe(){
		if(!IS_AJAX) halt('页面不存在');
		$ids = $this->_post('ids');
		$idss = explode(',',$ids);			
	    $arr = array('isval' => '1');
		$result=M('Yl_liuyan')->where(array('id'=>array('IN',$idss)))->save($arr);
		if($result){
			die(json_encode(array('error'=>0)));
		}else{
			die(json_encode(array('error'=>1)));
		}
	}
	public function shanchu(){

		if(!IS_AJAX) halt('页面不存在');
		$ids = I('ids');
		$idss = explode(',',$ids);
		foreach($idss as $val){
			M('Yl_liuyan')->where(array('id='=>$val,'pid'=>$this->pid))->delete();
		}
	}
	
	//批量删除
	public function del_list(){
		$liuyan=D('Yl_liuyan');
		$getid=$this->_request('id');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$liuyan->where(array('id'=>array('IN',$getids)))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	
}
?>