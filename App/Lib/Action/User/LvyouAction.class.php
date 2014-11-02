<?php
class LvyouAction extends CommonAction{
	function __construct(){
		parent::__construct();
	}
	//列表
	public function lyjj(){
		$data   = M('Lvyou_intro');
		$lid=$_GET['id'];
		//$this -> assign('lib',$lid);
		$list=$data->where(array('lid'=>$lid))->order('id desc')->find();
		$this -> assign('vo',$list);
	  if($_POST){	
	      if($list!=""){
	          $updata=$data->where(array('id'=>$_POST['id']))->save($_POST);
	           if($updata){
	                     $this->success('修改成功',U('Lvyou/index'));
	           }else{
	                    $this->error('修改失败',U('Lvyou/index'));
	            }
	       }else{
	         $adds=$data->add($_POST);
	        if($adds){
	                     $this->success('添加成功',U('Lvyou/index'));
	             }else{
	                    $this->error('添加失败',U('Lvyou/index'));
	           }
	       }	
		}else{
			//echo $lid=$_GET['id'];
			$this -> assign('aa',$lid);
		    $this->display('Lvyou/index');
		}
	}
	//宣传海报
	public function poster(){
	$data   = D('Lvyou_poster');	
	$list=$data->order('id desc')->find();
	$this -> assign('vo',$list);
	$picall=$data->order('id desc') ->field('id,pic1')->select();
    foreach ($picall as $v){
    	$picju[pic1]=$v[pic1];
    }
	$picjus=implode(',', $picju);
    $all=explode(',', $picjus);
	$this -> assign('va',$all);
	 $da=array();
	  if($_POST){
	  	$pics=$_POST['pic6'];
	  	  foreach($pics as $va){
	           $da[]=$va;
	  }
	  $_POST['pic1']=implode(',',$da);
	  unset($_POST['pic6']);
	      if($list!=""){
	      	$_POST['time']= time();
	          $updata=$data->where(array('id'=>$_POST['id']))->save($_POST);
	           if($updata){
	                  $this->success('修改成功',U('Lvyou/index'));
	           }else{
	                  $this->error('修改失败',U('Lvyou/poster'));
	            }
	       }else{
	         $adds=$data->add($_POST);
	        if($adds){
	                  $this->success('添加成功',U('Lvyou/index'));
	             }else{
	                 $this->error('添加失败',U('Lvyou/poster'));
	           }
	       }	
		}else{
		    $this->display();
		}
	}
	//景区列表
	//public function sceniclist(){
	public function index(){
		//echo $_SESSION['token'];
		$data  = D('Lvyou_scenic'); 
		$count = $data->where(array('token'=>$_SESSION['token']))->count();
		$Page  = new Page($count,20);
		$show  = $Page->show();
		$list  = $data->where(array('token'=>$_SESSION['token']))->limit($Page->firstRow.','.$Page->listRows)->order("sort desc")->select();	
		//echo $data->getlastsql();exit;
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Lvyou/scenic');
	}
	//景区添加 
	public function scenicadd(){
		if (IS_POST){
			$name = trim($this->_post('name'));
			$gjz  = trim($this->_post('gjz'));
			$sort = trim($this->_post('sort'));
			$ms   = trim($this->_post('ms'));
			$data = D('Lvyou_scenic');
			$where['token'] = $_SESSION['token'];
			$where['name'] = $name;
			$where['gjz']  = $gjz;
			$where['sort'] = $sort;
			$where['ms']   = $ms;
			if ($data -> create()){
				$result = $data->add($where);
				if ($result){
					$this->success('景区添加成功',U('Lvyou/index'));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}	
		}else{
			$this -> display();
		}
	}
	//景区修改
	public function scenicset(){
		$data   = D('Lvyou_scenic');
		$status = trim($this->_get('status'));
		$id     = trim($this->_get('id'));
		$where['id'] = $id;
		$where['token'] = $_SESSION['token'];
		$list   = $data->where($where)->find();
		$this -> assign('vo',$list);
		if (IS_POST){ 
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['time']= time();
			  if ($data -> create()){
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('Lvyou/index'));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{	
			 $this->display('Lvyou/scenicadd');
		}
	}
	//删除景区
	public function delete_scenic(){
		$check = M('Lvyou_scenic')->field('id')->where(array('id'=>$this->_get('id'),'token'=>$_SESSION['token']))->find();
		if ($check == false){$this->error('服务器繁忙');}
		$check1 = M('Lvyou_scenery')->field('id')->where(array('scenicid'=>$check['id'],'token'=>$_SESSION['token']))->select();
		if($check1){
			for($i=0;$i<count($check1);$i++){
				/*$check2 = M('Lvyou_mpress')->field('id')->where(array('zid'=>$check1[$i]['id']))->select();
				for($j=0;$j<count($check2);$j++){
					M('Lvyou_impress')->where(array('id'=>$check[$j]['id']))->delete();
				}
				$check2 = M('Lvyou_comment')->field('id')->where(array('zid'=>$check1[$i]['id']))->select();
				for($j=0;$j<count($check2);$j++){
					M('Lvyou_comment')->where(array('id'=>$check[$j]['id']))->delete();
				}*/
				M('Lvyou_impress')->where(array('zid'=>$check1[$i]['id'],'token'=>$_SESSION['token']))->delete();
				M('Lvyou_comment')->where(array('zid'=>$check1[$i]['id'],'token'=>$_SESSION['token']))->delete();
				M('Lvyou_scenery')->where(array('id'=>$check1[$i]['id'],'token'=>$_SESSION['token']))->delete();
			}
		}
		//echo M('Lvyou_scenery')->getlastsql();
		//dump($check1);exit;
		if (empty($_POST['set'])){
			if (M('Lvyou_scenic')->where(array('id'=>$check['id'],'token'=>$_SESSION['token']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	//景点列表
	public function scenerylist(){
		/*$data  = D('Lvyou_scenery'); 
		$count = $data->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();	
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Lvyou/scenery');*/
		$id=$_GET['id'];
		$data = new Model();
		$count = M('Lvyou_scenery')->where(array('scenicid'=>$id,'token'=>$_SESSION['token']))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		//$list = $data->table('tp_lvyou_scenery a, tp_lvyou_scenic b')->where('a.scenicid ='.$id)->field('a.*,b.name,b.id as bid')->order('a.sort desc' )->limit($Page->firstRow.','.$Page->listRows)->select();
		$list = $data->table('tp_lvyou_scenery ')->where(array('scenicid'=>$id,'token'=>$_SESSION['token']))->order('sort desc' )->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo $data->getlastsql();
		//dump($list);exit;
		$this -> assign('page',$show);
		$this -> assign('list',$list);
		$this -> assign('id',$id);
		$this -> display('Lvyou/scenery');
	}
 	//添加景点
	public function sceneryadd(){
		//$data  = D('Lvyou_scenic');
		//$list  = $data->field('name,id as bid')->order("id desc")->select();	
		//$this -> assign('list',$list);
	if (IS_POST){
			$name      = trim($this->_post('names'));
			$gjz       = trim($this->_post('gjz'));
			$bid       = trim($this->_get('id'));
	       // $bid       = trim($this->_post('bid'));
	        //var_dump($bid);exit;
			$jianyaoms = trim($this->_post('jianyaoms'));
			$ms        = trim($this->_post('ms'));
			$sort      = trim($this->_post('sort'));
			$jieshao   = trim($this->_post('jieshao'));
			$pic       = trim($this->_post('pic'));
		
			$data = D('Lvyou_scenery');
			$where['names']      = $name;
			$where['gjz']       = $gjz;
			$where['scenicid']  = $bid;
			$where['jianyaoms'] = $jianyaoms;
			$where['ms']        = $ms;
			$where['sort']      = $sort;
			$where['jieshao']   = $jieshao;
			$where['pic']       = $pic;
			$where['token']   =$_SESSION['token'];
			if ($data -> create()){
				$result=$data->add($where);	
				//echo $data->getlastsql();
				if ($result){
					$this->success('景点添加成功',U('/Lvyou/scenerylist/id/'.$bid ));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}	
		}else{
			$this -> assign('id',$_GET['id']);
			$this -> display('Lvyou/sceneryadd');
		}
	}
	//修改景点
	public function sceneryset(){
		$data  = D('Lvyou_scenic');
		$list1  = $data->field('name,id as bid')->order("id desc")->select();	
		$this -> assign('list',$list1);
	     $data   = D('Lvyou_scenery');
		$status = trim($this->_get('status'));
		$sid     = trim($this->_get('sid'));
		$id     = trim($this->_get('id'));
		$where['id'] = $sid;
		$where['token']=$_SESSION['token'];
		$list   = $data->where($where)->find();
		$this -> assign('vo',$list);
		if (IS_POST){ 
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['time']= time();
			$_POST['scenicid']= $id ;
			$_POST['token']=$_SESSION['token'];
			if ($data -> create()){
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('/Lvyou/scenerylist/id/'.$id ));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{	
			$this -> assign('id',$_GET['id']);
			$this->assign('set',$check);
			$this -> display('Lvyou/sceneryadd');
		}
	}
	//删除景点
	public function delete_scenery(){
		$check = M('Lvyou_scenery')->field('id')->where(array('id'=>$this->_get('id'),'token'=>$_SESSION['token']))->find();
		if ($check == false){$this->error('服务器繁忙');}
		M('Lvyou_impress')->where(array('zid'=>$check['id']))->delete();
		M('Lvyou_comment')->where(array('zid'=>$check['id']))->delete();
		if (empty($_POST['set'])){
			if (M('Lvyou_scenery')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	//批量删除景点
	public function del_scenery(){
		$scenery=D('Lvyou_scenery');
		$getid=$this->_request('id');	
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		M('Lvyou_impress')->where(array('zid'=>array('IN',$getids),'token'=>$_SESSION['token']))->delete();
		M('Lvyou_comment')->where(array('zid'=>array('IN',$getids),'token'=>$_SESSION['token']))->delete();
		$result=$scenery->where(array('id'=>array('IN',$getids),'token'=>$_SESSION['token']))->delete();
		if($result){
			$this->success('成功');
		}else{
			$this->error('失败');
		}
	}
	
	//游客印象列表
	public function impress(){
		$zid=$_GET['zid'];
		$data  = D('Lvyou_impress'); 
		$count = $data->where(array('zid'=>$zid,'token'=>$_SESSION['token']))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where(array('zid'=>$zid,'token'=>$_SESSION['token']))->limit($Page->firstRow.','.$Page->listRows)->order("sort desc")->select();	
		//echo $data->getlastsql();exit;
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> assign('zid',$zid);
		$this -> assign('id',$_GET['jdid']);
		$this -> display('Lvyou/impress');
	}
	//添加游客印象
	public function impressadd(){
		if (IS_POST){
			$name       = trim($this->_post('name'));
			$gjz        = trim($this->_post('gjz'));
			$sort       = trim($this->_post('sort'));
			$impressnum = trim($this->_post('impressnum'));
			$isshow     = trim($this->_post('isshow'));
			$zid     = trim($this->_post('zid'));
		
			$data = D('Lvyou_impress');
			$where['name']       = $name;
			$where['gjz']        = $gjz;
			$where['sort']       = $sort;
			$where['impressnum'] = $impressnum;
			$where['isshow']     = $isshow;
			$where['zid']     = $zid;
			$where['token']  =$_SESSION['token'];
			if ($data -> create()){
				$result = $data->add($where);
				if ($result){
					$this->success('游客印象添加成功',U('/Lvyou/impress/zid/'.$zid));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}	
		}else{
			$this -> assign('zid',$_GET['zid']);
			$this -> display('Lvyou/impressadd');
		}
	}
	//游客印象修改
	public function set_impress(){
		$data   = D('Lvyou_impress');
		$status = trim($this->_get('status'));
		$id     = trim($this->_get('id'));
		$zid     = trim($this->_get('zid'));
		$where['id'] = $id;
		$where['token']=$_SESSION['token'];
		$list   = $data->where($where)->find();
		$this -> assign('vo',$list);
		if (IS_POST){ 
			//var_dump($id);exit;
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['time']= time();
			$_POST['zid']= $zid ;
			$_POST['toke']=$_SESSION['token'];
			if ($data -> create()){
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('/Lvyou/impress/zid/'.$zid));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{	
			$this->assign('zid',$zid);
			$this->assign('set',$check);
			$this->display('Lvyou/impressadd');
		}
	}
	//删除游客印象
	public function delete_impress(){
	$check = M('Lvyou_impress')->field('id')->where(array('id'=>$this->_get('id'),'token'=>$_SESSION['token']))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Lvyou_impress')->where(array('id'=>$check['id'],'token'=>$_SESSION['token']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	//批量删除游客印象
	public function del_impress(){
		$impress=D('Lvyou_impress');
		$getid=$this->_request('id');	
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$impress->where(array('id'=>array('IN',$getids),'token'=>$_SESSION['token']))->delete();
		if($result){
			$this->success('成功');
		}else{
			$this->error('失败');
		}
	}
	//专家点评
	public function comment(){
		$zid=$_GET['zid'];
		$data  = D('Lvyou_comment'); 
		$count = $data->where(array('zid'=>$zid,'token'=>$_SESSION['token']))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where(array('zid'=>$zid,'token'=>$_SESSION['token']))->limit($Page->firstRow.','.$Page->listRows)->order("sort desc")->select();	
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> assign('zid',$_GET['zid']);
		$this -> assign('id',$_GET['jdid']);
		$this -> display('Lvyou/comment');
	}
	//添加专家点评
	public function commentadd(){
		if (IS_POST){
			$title   = trim($this->_post('title'));
			$gjz     = trim($this->_post('gjz'));
			$sort    = trim($this->_post('sort'));
			$name    = trim($this->_post('name'));
			$zhiwei  = trim($this->_post('zhiwei'));
			$pic     = trim($this->_post('pic'));
			$jieshao = trim($this->_post('jieshao'));
			$content = trim($this->_post('content'));
			$zid = trim($this->_post('zid'));
			$data = D('Lvyou_comment');
			$where['title']   = $title;
			$where['gjz']     = $gjz;
			$where['sort']    = $sort;
			$where['name']    = $name;
			$where['zhiwei']  = $zhiwei;
			$where['pic']     = $pic;
			$where['jieshao'] = $jieshao;
			$where['content'] = $content;
			$where['zid'] = $zid;
			$where['token']=$_SESSION['token'];
			if ($data -> create()){
				$result = $data->add($where);
				if ($result){
					$this->success('专家点评添加成功',U('/Lvyou/comment/zid/'.$zid));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}	
		}else{
			$this -> assign('zid',$_GET['zid']);
			$this -> display('Lvyou/commentadd');
		}
	}
	//专家点评修改
	public function set_comment(){
		$data   = D('Lvyou_comment');
		$status = trim($this->_get('status'));
		$id     = trim($this->_get('id'));
		$zid     = trim($this->_get('zid'));
		$where['id'] = $id;
		$where['token']=$_SESSION['token'];
		$list   = $data->where($where)->find();
		$this -> assign('vo',$list);
		if (IS_POST){ 
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['time']= time();
			$_POST['zid']= $zid ;
			$_POST['token']=$_SESSION['token'];
			if ($data -> create()){
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('/Lvyou/comment/zid/'.$zid));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{	
			$this->assign('set',$check);
			$this->assign('zid',$zid);
			$this->display('Lvyou/commentadd');
		}
	}

	//删除专家点评
	public function delete_comment(){
	$check = M('Lvyou_comment')->field('id')->where(array('id'=>$this->_get('id'),'token'=>$_SESSION['token']))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Lvyou_comment')->where(array('id'=>$check['id'],'token'=>$_SESSION['token']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	//批量删除专家点评
	public function del_comment(){
		$comment=D('Lvyou_comment');
		$getid=$this->_request('id');	
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$comment->where(array('id'=>array('IN',$getids),'token'=>$_SESSION['token']))->delete();
		if($result){
			$this->success('成功');
		}else{
			$this->error('失败');
		}
	}
	public function modjqtitle(){
		$where['id']=trim($_POST['id']);
		$name['name']=trim($_POST['title']);
		$data   = M('Lvyou_scenic');
		$data->where($where)->save($name);
		echo $data->getlastsql();
		if($data->where($where)->save($name)){
			//echo $data->getlastsql();
			echo "1";
		}
	}
}