<?php
class GroupbuyAction extends Action
{
    public function index()
    {
    	/*$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		dump($token);
		 if($token==false){
			echo '数据不存在';exit;
		} */
    	//exit;
    	
    	$wecha_id=$this->_get('wecha_id');
    	$token=$this->_get('token');
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	$where['kssj']=array('lt',time());
    	$where['jssj']=array('gt',time());
    	$where['token']=$token;
		$groupbuy=M('Groupbuy')->where($where)->order('id desc')->select();
		$groupbuy_count=M('Groupbuylist')->group('gid')->field('gid,COUNT(id) AS count')->select();
		$count=array();
		foreach ($groupbuy_count as $value){
			$count[$value['gid']]=$value['count'];
		}
		if($groupbuy==false){ }
		$this->assign('groupbuy',$groupbuy);
		$this->assign('count',$count);

		$this->display('Groupbuy/index');
    }
    
    public function content(){
    	$data   = D('Groupbuy');
    	$id     = trim($this->_get('id'));
    	$wecha_id=$this->_get('wecha_id');
    	$token=$this->_get('token');
    	$where['id'] = $id;
    	$list   = $data->where($where)->find();
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	
    	$data   = D('Userinfo');
    	$lists  = $data->where(array('wecha_id'=>$wecha_id))->find();
    	
    	$this -> assign('vo',$list);
    	$this -> assign('lists',$lists);
    	$this->display('Groupbuy/content');
    }
    
    
    public function  contentadd_ajax(){
    	$result=array('error'=>1,'msg'=>'未知错误');
    	
    	$wecha_id = $this->_get('wecha_id');
    	$token=$this->_get('token');
    	$where['gid']=$this->_post('gid');
    	$where['wecha_id'] = $wecha_id;
    	
    	$db = D('Groupbuylist');
    	$_POST['tgnum']=trim($this->_post('buyCount'));
    	$_POST['sn']=uniqid().rand(100,999);
    	$_POST['pay']=$this->_post('pay');
    	$_POST['wecha_id']=$this->_get('wecha_id');
     	
    	//dump($wecha_id);dump($token);exit;
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	$res   = $db->where($where)->find();
    	
    	$data   = D('Userinfo');
    	$lists  = $data->where(array('wecha_id'=>$wecha_id))->find();
    	if($lists == false){
    		$_POST['truename']=$this->_post('usname');
    		$_POST['tel']=$this->_post('tel');
    		$_POST['address']=$this->_post('addr');
    		$_POST['wecha_id']=$this->_get('wecha_id');
    		$_POST['token']=$this->_get('token');
    		$data -> create($_POST);
    		$data -> add();
    	}
    	if($res == false){
    		if ($db -> create($_POST)){
    			$result = $db->add();
    			if ($result){
    				$msg="恭喜您，您已参团成功，请您牢记团购券号码：".$_POST['sn'];
    				
    				$result=array('error'=>0,'msg'=>$msg);
    			}else{
    				$result=array('error'=>2,'msg'=>'参团失败');
    			}
    		}else{ 
    			$result=array('error'=>3,'msg'=>$db->getError());
    		}	
    	}else{
    		$result=array('error'=>4,'msg'=>'您已参加过活动，不可再次参加');
    	}
    	die(json_encode($result));
    }

    public function indent(){
    	$id = trim($this->_get('gid'));
    	$wecha_id = $this->_get('wecha_id');
    	$token=$this->_get('token');
    	$model=new Model();
    	$data = $model->table('tp_groupbuy a,tp_groupbuylist b')
    				  ->field('a.*,b.*')
    				  ->where(array('a.id=b.gid','b.gid'=>$id,'a.token'=>$token,'b.wecha_id'=>$wecha_id))
    				  ->order('b.id desc')
    				  ->find();

    	$datas = $model->table('tp_groupbuylist a,tp_logistics b')
    				   ->field('a.*,b.*')
    				   ->where(array('a.id=b.listid','b.listid'=>$data['id'],'a.wecha_id'=>$wecha_id))
    				   ->order('b.id desc')
    				   ->find();
    	$this -> assign('data',$data);
    	$this -> assign('datas',$datas);
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	$this->display('Groupbuy/indent');
    }
    
    public function lookLogistics(){
    	   	
    	$id  = trim($this->_get('id'));
    	$wecha_id = $this->_get('wecha_id');
    	$token=$this->_get('token'); 	
    	/* $data=M('Groupbuylist');
    	$where['wecha_id']=$wecha_id;
    	$list=$data->where($where)->find(); */	
    	$data=M('Logistics');
    	$where['listid']=$id;
    	$logistics=$data->where($where)->find();
    	$this->assign('data',$logistics);
    	
    	if(!empty($logistics['logisticgs'])&& !empty($logistics['logisticsn'])){
			$data=array($logistics['logisticgs'],$logistics['logisticsn']);
			$kuaidi=$this->kuaidi($data);
			$kuaidi=str_replace("\n", "<br/>", $kuaidi);
    	}else {
    		$kuaidi='未填写物流订单';
    	}
    	$this->assign("kuaidi",$kuaidi);
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	$this->display ('Groupbuy/logistics');
    }
    
    /**
     * 快递查询
     * @param Array $data 快递公司|快递单号
     * @return string
     */
    public function kuaidi($data){
    	$data = array_merge($data);
    	$str  = file_get_contents('http://www.weinxinma.com/api/index.php?m=Express&a=index&name=' . $data[0] . '&number=' . $data[1]);
    	return $str;
    }
    
	public function success(){
		$data   = D('Groupbuylist');
		$wecha_id = $this->_get('wecha_id');
		$token=$this->_get('token');
		$where['sn'] = trim($this->_get('sn'));
		$_POST['fustatus']=1;
		$data->where($where)->save($_POST);
		
		$sn = trim($this->_get('sn'));
		$model=new Model();
		$data = $model->table('tp_groupbuy a,tp_groupbuylist b')
		->field('a.*,b.*')
		->where(array('a.id=b.gid','b.sn'=>$sn,'a.token'=>$token,'b.wecha_id'=>$wecha_id))
		->order('b.id desc')
		->find();
		
		$datas = $model->table('tp_groupbuylist a,tp_logistics b')
		->field('a.*,b.*')
		->where(array('a.id=b.listid','b.listid'=>$data['id'],'a.wecha_id'=>$wecha_id ))
		->order('b.id desc')
		->find();
		$this -> assign('data',$data);
		$this -> assign('datas',$datas);
		$this->assign('wecha_id',$wecha_id);
		$this->assign('token',$token);
		$this -> display('Groupbuy/indent');
	} 
	
	public function orderlist(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('wecha_id',$wecha_id);
		$this->assign('token',$token);
		
		$data=M('Userinfo');
		$where['wecha_id']=$wecha_id;
		$where['token']=$token;
		$userinfo=$data->where($where)->find();

		$this->assign('userinfo',$userinfo);
		
		$groupbuylist=M('Groupbuylist');
		$data=$groupbuylist->table('tp_groupbuy a,tp_groupbuylist b')
			->field('a.*,b.*')
			->where(array('a.id=b.gid','a.token'=>$token,'b.wecha_id'=>$wecha_id))
			->order('b.id desc')
			->select();
		if($data){
			$this->assign('data',$data);	
		}
		$this->display('Groupbuy/orderlist');
	}
	
	public function userinfo(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('wecha_id',$wecha_id);
		$this->assign('token',$token);
		
		$data=M('Userinfo');
		$where['wecha_id']=$wecha_id;
		$datas=$data->where($where)->find();
		if($datas){
			$_POST['updatetime']=time();
			$userinfo=$data->where($where)->save($_POST);
			if($userinfo){
				$result=array('error'=>0,'msg'=>'信息设置成功');
			}else{
				$result=array('error'=>2,'msg'=>'信息设置失败');
			}
		die(json_encode($result));
		}else{
			if($data ->create()){
				$result = $data->add($_POST);
				if($result){
					$result=array('error'=>0,'msg'=>'信息添加成功');
				}else{
					$result=array('error'=>2,'msg'=>'信息添加失败');
				}
			}
		die(json_encode($result));
		}
	}
    
	public function gcontent(){
		$id=$this->_get('id');
		$wecha_id=$this->_get('wecha_id');
    	$token=$this->_get('token');
    	$this->assign('wecha_id',$wecha_id);
    	$this->assign('token',$token);
    	$where['id']=$id;
		$groupbuy=M('Groupbuy')->where($where)->order('id desc')->find();
		
		$groupbuy_count=M('Groupbuylist')->group('gid')->field('gid,COUNT(id) AS count')->select();
		$count=array();
		foreach ($groupbuy_count as $value){
			$count[$value['gid']]=$value['count'];
		}
		if($groupbuy==false){ }
		$this->assign('groupbuy',$groupbuy);
		$this->assign('count',$count);

		$this->display('Groupbuy/gcontent');
	}
	
	
}    
    

 