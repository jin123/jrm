<?php
/**

**/
class WxscreenAction extends CommonAction{

	function index(){
		$data = M('Wxscreen_set');
		$where['token']=session('token');
        $ly_data = $data->where($where)->find();
        if (IS_POST) {
            if ($ly_data == null) {
                //$this->all_insert('Wxscreen_set');
				$info['gjz']=trim($_REQUEST['gjz']);
				$info['tit']=trim($_REQUEST['tit']);
				$info['pic']=trim($_REQUEST['pic']);
				$info['headpic']=trim($_REQUEST['headpic']);
				$info['toppic']=trim($_REQUEST['toppic']);
				$info['bottempic']=trim($_REQUEST['bottempic']);
				$info['issh']=trim($_REQUEST['issh']);
				$info['token']=session('token');
				$info['text1']=trim($_REQUEST['text1']);
				$info['text2']=trim($_REQUEST['text2']);
				$blog=$data->add($info);
				$result=$data->where($where)->find();
				$arr['pid']= $result['id'];
				$arr['keyword']=trim($_REQUEST['gjz']);			
				$arr['token']=session('token');
				$arr['module']='Wxscreen';
				$result=M('keyword')->add($arr);
				if($blog===false){
					$this->error('操作失败');
				}else{
					$this->success('操作成功', U('Wxscreen/index'));
				}
            } else {
				$where['id']=trim($_REQUEST['id']);
				$info['gjz']=trim($_REQUEST['gjz']);
				$info['tit']=trim($_REQUEST['tit']);
				$info['pic']=trim($_REQUEST['pic']);
				$info['headpic']=trim($_REQUEST['headpic']);
				$info['toppic']=trim($_REQUEST['toppic']);
				$info['bottempic']=trim($_REQUEST['bottempic']);
				$info['issh']=trim($_REQUEST['issh']);
				$info['token']=session('token');
				$info['text1']=trim($_REQUEST['text1']);
				$info['text2']=trim($_REQUEST['text2']);
				$arr['pid']= trim($_REQUEST['id']);
				$arr['keyword']=trim($_REQUEST['gjz']);	
				$arr['module']='Wxscreen';
				$arr['token']=session('token');
				$where_key=array('pid'=>trim($_REQUEST['id']));
				//$where_key=array('token'=>trim($_REQUEST['id']));
				$result=M('keyword')->where($where_key)->save($arr);
				$blog=$data->where($where)->save($info);
				if($blog===false){
					$this->error('修改失败');
				}else{
					$this->success('修改成功', U('Wxscreen/index'));
				}
			   
            }
        } else {
            $this->assign('ly_data', $ly_data);
            $this->display();
        }
	}

	function liuyan(){
		$pid=trim($_REQUEST['id']);
		$where['pid']=$pid;
		$count = M('Wxscreen')->where($where)->count();
		$page = new Page($count,10);
		$limit = $page->firstRow.','.$page->listRows;
		$pid=trim($_REQUEST['id']);
		$where['pid']=$pid;
		$ly = M('Wxscreen')->limit($limit)->where($where)->order("time desc")->select();
		$this->ly = $ly;
		$this->page = $page->show();
		$this->display();
	}

	function del(){
				$id = I ('id','','intval');
				if (M('Wxscreen')->delete($id)) {
					$this->success('删除成功', U('Wxscreen/liuyan'));
				}else{
					$this->error('删除失败');
				}
			}
	function save(){
		$id = I ('id','','intval');
		
		$arr = array('isval' => '1');
		
		if (M('Wxscreen')->where('id='.$id)->data($arr)->save()){
			$this->success('审核成功', U('Wxscreen/liuyan'));
		}else {
			$this->error('审核失败');
		}	
	}
			
	function shenhe(){
		if(!IS_AJAX) halt('页面不存在');
		$ids = I('ids');
		$idss = explode(',',$ids);
		
		foreach($idss as $val)
			{
			$id = $val;
			$arr = array(
				 'isval' => '1', 
			);
			M('Wxscreen')->where('id='.$id)->data($arr)->save();
				}

	}
		function shanchu(){
		if(!IS_AJAX) halt('页面不存在');
		$ids = I('ids');
		$idss = explode(',',$ids);
		foreach($idss as $val){
		M('Wxscreen')->where('id='.$val)->delete();
		}

	}
	
	function gundong(){
		$token=session('token');
		//var_dump($token);exit;
		$pid=trim($_REQUEST['id']);
		$where['pid']=$pid;
		$where['isval']="1";
		$where_set['token']=$token;
		$where_set['id']=$pid;
		$wx = M('Wxscreen');
		$list = $wx->where($where)->order('time desc')->select();
		$list_set=M('Wxscreen_set')->where($where_set)->find();
		$count      = M('Wxscreen')->where($where)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();
		//var_dump($where_set);exit;
		$this->assign('list', $list);
		$this->assign('list_set', $list_set);
		$this->assign('page', $count);
		$this->assign('pid', $pid);
        $this->display();
	}
	
	function refresh(){
		$pid=trim($_REQUEST['id']);
		//var_dump($pid);exit;
		$where['pid']=$pid;
		$where['isval']="1";
		$wx = M('Wxscreen');
		$list = $wx->where($where)->order('time desc')->select();
		//var_dump($wx->getLastSql());exit;
		$count= $wx->where($where)->count();
		//$this->assign('page', $count);
		//$this->assign('pid', $pid);
		if($list===false){
			//$this->error("删除失败");
			$result['code']="001";
			$result['info']="加载错误";
			$result['count']=$count;
		}else{
			//$this->success('删除成功');
			$result['code']="000";
			$result['info']=$list;
			$result['count']=$count;
		}
		 die(json_encode($result));
		
	}
}


?>