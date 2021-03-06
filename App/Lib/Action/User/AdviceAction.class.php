<?php
/**
*意见反馈
**/

class AdviceAction extends CommonAction{
	public $pid;
	
	public function __construct(){
		parent::__construct();
		$where['token'] = session('token');
        $ly_data = M('advice_set')->where($where)->find();
		$this->pid = $ly_data['id'];
	}
	
	public function index(){
		$db = M('advice_set');
		$where['token'] = session('token');
        $ly_data = $db->where($where)->find();
        if (IS_POST) {
            if ($ly_data == null) {
                $this->all_insert('advice_set');
            } else {
                $this->all_save('advice_set');
            }
        } else {
            $this->assign('ly_data', $ly_data);
            $this->display();
        }
	}

	public function advice(){
		$count = M('advice')->where(array('pid'=>$this->pid))->count();
		$page = new Page($count,10);
		$limit = $page->firstRow.','.$page->listRows;
		$ly = M('advice')->where(array('pid'=>$this->pid))->limit($limit)->order('id desc')->select();
		$this->assign('ly',$ly);
		$this->assign('page',$page->show());
		$this->display();

	}
   function  replay(){
        
       $id  = $_GET['id'];
	   if(IS_POST){
	   
	       $add['replay_id'] = $id;
		   $add['content'] = $_POST['content'];
		   $add['ctime'] = time();
		   $res = M('replay')->add($add);
		   exit($res);
		 
	      
	   }
	   $where['id'] = $id;
	   $list  = M('advice')->where($where)->find();
	 //  echo M('advice')->getlastsql();
	   $this->assign('list',$list);
	   $this->display();
   
   }
	public function del(){
		$where['id'] = I ('id','','intval');
		$where['pid'] = $this->pid;
		if (M('advice')->where($where)->delete()) {
			$this->success('删除成功', U('Advice/advice'));
		}else{
			$this->error('删除失败');
		}
	}

	public function save(){

		$id = I ('id','','intval');
		$data = M('advice')->where(array('id'=>$id,'pid'=>$this->pid))->find();
		if($data['isval']==0){
			$arr = array('isval' => '1');
		}else{
			$arr = array('isval' => '0');
		}

		if (M('advice')->where('id='.$id)->data($arr)->save()){
			$this->success('审核成功', U('Advice/advice'));
		}else {
			$this->error('审核失败');
		}	

	}
			
	public function shenhe(){
		if(!IS_AJAX) halt('页面不存在');
		$ids = I('ids');
		$idss = explode(',',$ids);
		
		foreach($idss as $val)
			{
			$id = $val;
			$arr = array('isval' => '1');
			M('advice')->where(array('id='=>$id,'pid'=>$this->pid))->data($arr)->save();
		}

	}
	public function shanchu(){

		if(!IS_AJAX) halt('页面不存在');
		$ids = I('ids');
		$idss = explode(',',$ids);
		foreach($idss as $val){
			M('advice')->where(array('id='=>$val,'pid'=>$this->pid))->delete();
		}
	}
	
}
?>