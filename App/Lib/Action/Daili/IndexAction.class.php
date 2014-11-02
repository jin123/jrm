<?php

class IndexAction extends SystemAction{
	
	public function index(){
		$db=M('Daili');
		$ldb = D('Agent_level');
		$dlid = session('dluserId');
        
		$data=$db->where(array('id'=>$dlid))->find();
        $level=$ldb->where(array('level'=>$data["levelid"]))->find();
		list($data['copy'],$data['url']) = split("[|]", $data['copyright']);
		$this->assign('dinfo',$data);
		$this->assign('linfo',$level);
		$this->display();
	}

	public function edit(){
		$wxuser = D('Daili');
		$dlid = session('dluserId');
		$data = $wxuser->where(array('id'=>$dlid))->find();
		if(IS_POST){
			$where['id'] = $data['id'];
			$_POST['copyright'] = $_POST['copy'].'|'.$_POST['url'];
			$wxuser->where($where)->save($_POST);
			$this->success('设置成功！');
		}else{
		list($data['copy'],$data['url']) = split("|", $data['copyright']);
		$this->assign('data',$data);
		$this->display();
		}
	}
}
?>