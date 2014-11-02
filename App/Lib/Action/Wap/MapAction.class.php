<?php

class MapAction extends Action{

    public function index()
    {
		$where['id'] = $this->_get('id');
		$where['token'] = $this->_get('token');
		$data = M('company')->where($where)->find();
		$this->assign('map',$data);
		$this->display();

    }
	function street(){
	
	   $info = M('Company')->where(array('id'=>$_GET['id']))->find();
	   $this->assign('info',$info);
	   $this->display();
	
	}

      public function getlist(){
	       $where = "token="."'".$_GET['token']."'";
	       if(isset($_GET['id'])){
		      $where.=" AND id=".$_GET['id'];
	       }
	       if(IS_POST){	
	       	 $where.=" AND name like '%".$_POST['keyword']."%'". " OR (  address like '%".$_POST['keyword']."%' AND token= "."'".$_GET['token']."'".")";
	       }
	      $sel = M('Company')->where($where)->select();
	    
	      $this->assign('info',$sel);
	      $this->display('look');

	
	
	}
    public function maplist()
    {
		$where['token'] = $this->_get('token');
		$data = M('company')->where($where)->select();
		$count = M('company')->where($where)->count();
		$this->assign('res',$data);
		/*if ($count==1){
			$this->content($data[0]['id']);
			exit();
		}*/
		$this->display();
	}

    public function content($contentid=0)
    {
		if (!$contentid){
			$where['id']=intval($_GET['id']);
		}else{
			$where['id'] = $contentid;
		}
		$where['token'] = $this->_get('token');
		$data = M('company')->where($where)->find();
		$this->assign('map',$data);
		$this->display('content');

	}

	
}
?>