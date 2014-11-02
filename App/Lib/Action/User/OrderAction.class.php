<?php
class OrderAction extends CommonAction{	
	function index(){
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		//import("@.ORG.Page");
		$page = isset($_GET['page'])?$_GET['page']:1;
		$url = C("api_url")."/order.aspx?page=1&pageindex=".$page."&vid=".$vipid['vipid']/*."&ob=1"*/;
        if(isset($_POST['Name']) && trim($_POST['Name'])!=""){
        	$url = C("api_url")."/order.aspx?page=1&pageindex=".$page."&vid=".C('vipid')."&pName=".$_POST['Name'];
        }
		$data = $this->curlGet($url);
	
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $data),true);
		foreach($data['rows'] as $k=>$v){
		$where['unid'] = $v['UnId'];
		$list=M('ShopOrder')->where($where)->field('status')->find();
	  	$data['rows'][$k]['status'] = $list['status'];
		}
		//dump($data);
		$this->assign('status',$status);
		$this->assign('data',$data);
		$this->display();
	
	}
	//订单详情
	function orderInfo(){
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		$unid=$this->_get('unid');
		$data = $this->curlGet(C('api_url')."/order.aspx?page=1&UnId=".$unid."&vid=".$vipid['vipid']);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $data),true);
		//dump($data['rows']);die;
		$this->assign('data',$data['rows'][0]);
		$this->display();
	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	
	//改变开启状态
	public function istake(){
		$shoporder=M('ShopOrder');
		$unid=I('get.unid');
		if (empty ($unid)) {
			$this->error ( '参数错误' );
			die();
		}
		if(isset($_GET['status'])){
			$check=$_GET['status'];
		}else{
			$result=$shoporder->field('status')->where(array('unid'=>$unid))->find();
			$check=$result['status'];
		}
		$data=array();
		if($check){
			$data['status']=0;
		}else{
			$data['status']=1;
		}
		$data['UserId']=$_GET['userid'];
		$data['unid']=$unid;
		$result=$shoporder->add($data);
		header('Location:'.$_SERVER['HTTP_REFERER']);
	}
	function out_data(){
	    $this->display('out');
	
	}
	function type_manage(){
	    $url=C('api_url').'/Sort.aspx?Flag=1&page=1&sortid=2';
		$info = curlGet($url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
	   foreach($data['rows'] as $key=>$val){
	   
	       $where['token'] = session('token');
	       $where['flag_id'] = $val['unid'];
	       $flag_info =  M('drugs_flag')->where($where)->find();
	       $type_info = M('drugs_type')->where(array('token'=>session('token'),'id'=>$flag_info['send_id']))->find();
	       $data['rows'][$key]['name'] = $type_info['name'];
	       $data['rows'][$key]['ctime'] = $type_info['ctime'];
	   
	   }
	   $this->assign('data',$data);
       $this->assign('wxinfo',$wxinfo);
	   $this->display();
	
	}
	function  add_drugs_type(){
	 $id = $_GET['id'];
	 $list= array();
	  if(IS_POST){
		//	 $data['typeid'] = $_GET['typeid'];
			 $data['name'] = $_POST['name'];
			// $data['color'] = $_POST['color'];
			 $data['ctime'] = time();
			 $data['token'] = session('token');
			 if(!isset($id) || empty($id)){
			   $res =  M('drugs_type')->add($data);
			 }
			 else{
			 
			  $res =  M('drugs_type')->where(array('id'=>$id))->save($data);
			 
			 }
			 exit(json_encode($res));
		 }
		 if(!empty($id)){
		 
		    $list = M('drugs_type')->where(array('id'=>$id))->find();
		 }
		//echo M('drugs_type')->getlastsql();
		 $this->assign('list',$list);
		 $this->display();
	}
	//添加typeid下面的分类追加到自定义的分类
	function add_second_flag(){
	    $sortid=$this->_get('typeid');
		//$send_id=$this->_get('sen_id');
		//1级分类
		$url=C('api_url').'/Sort.aspx?Flag=1&page=1&sortid=2';
		$info = $this->curlGet($url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
		//获取已经添加的分类
	    $where['token'] = session('token');
		$where['send_id'] = $send_id;
		$other_where = "token="."'".session('token')."'"." AND send_id !=".$send_id;
		$other_hase_insert  = M('drugs_flag')->where($other_where)->select();
		$has_insert= M('drugs_flag')->where($where)->select();
		$insert = array();
		$other_insert = array();
		foreach($other_hase_insert as $v){
		 
		    $other_insert[] = $v['flag_id'];
		
		}
		foreach($has_insert as $v){
		 
		    $insert[] = $v['flag_id'];
		
		}
		if(IS_POST){
		}
		else{
	     $this->assign('data_info',$data['rows']);
	     $this->assign('send_id',$send_id);
		 $this->assign('insert',$insert);
		  $this->assign('other_insert',$other_insert);
		 $this->display();
		 }
	}
	function add_flag_second(){
	
	     $flag_id = $_POST['flag_id'];
		 $flag_id = substr($flag_id,1);
		 $flag_id = explode(",",$flag_id);//从接口获取的分类
		 $send_id = $_POST['send_id'];//即将要追加的分类id
		 $count = M('drugs_flag')->where(array('token'=>session('token'),'send_id'=>$_POST['send_id']))->count(); 
		 if($count!=0){
		    
		 
		     M('drugs_flag')->where(array('token'=>session('token'),'send_id'=>$_POST['send_id']))->delete(); 
		 } 
		 foreach($flag_id  as $v){
		    $data['flag_id'] = $v;
			$data['send_id'] = $_POST['send_id'];
			$data['token'] = session("token");
		    M('drugs_flag')->add($data);
		  
		 
		 }
	
	     $result = array('status'=>1,"msg"=>"添加成功");
		 exit(json_encode($result));
	}
	//删除自定义的分类
	function del_drugs_type(){
	
	   $id = $_GET['id'];
	   $where['id'] = $id;
	   $count = M('drugs_flag')->where(array('token'=>session('token'),'send_id'=>$id))->count();
	   

	   if($count!=0){
	      
	      $this->error('当前分类下有子分类');return;
	   }
	    $del = M('drugs_type')->where($where)->delete();
	   if($del){
	   
	      $this->success('删除成功');
	   }
	   else{
	   
	     $this->error('删除失败');
	   
	   }
	
	}
	function add_new_type(){
	
	
	   $unid = $_POST['unid'];
	   $name=$_POST['name'];
	   $where['token'] = session('token');
	   $where['flag_id'] = $unid;
	   $flag_info = M('drugs_flag')->where($where)->find();
	   if($flag_info){
	   
	        $drugs_type_data['name'] = $name;
	        $res = M('drugs_type')->where(array('id'=>$flag_info['send_id']))->save($drugs_type_data);
	        exit(json_encode($res));
	   
	   }
	   else{
	   
	      $drugs_type_data['name'] = $name;
	      $drugs_type_data['ctime'] = time(); 
	      $drugs_type_data['token'] = session('token');
	      $add = M('drugs_type')->add($drugs_type_data);
	      $drugs_flag_data['send_id'] = $add;
	      $drugs_flag_data['flag_id'] = $unid;
	      $drugs_flag_data['token'] = session('token');
	      $res = M('drugs_flag')->add($drugs_flag_data);
	      exit(json_encode($res));
	   }
	
	}
	
	
	
	public $reply_info_model;
	public $infoTypes;
	public function _initialize() {
		parent::_initialize();
		
	}
	public function set(){
	$reply_info=M('reply_info');
        //$infotype = $this->_get('infotype');
		$thisInfo = $reply_info->where(array('token'=>session('token')))->find();
		if(IS_POST){
			$row['keyword']=$this->_post('keyword');
			//echo $this->_post('title');exit;
			$row['title']=$this->_post('title');
			$row['info']=$this->_post('info');
			$row['picurl']=$this->_post('picurl');
			//$row['infotype']=$this->_post('infotype');
			$keyword_model=M('Keyword');
			if ($thisInfo){
				
				$where=array('token'=>session('token'));
				//dump($row);exit;
				$reply_info->where($where)->save($row);
				
				$keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST['keyword']));
				//echo $keyword_model->getLastSql();exit;
				
				$this->success('修改成功',U('Order/set',$where));
						
			}else {
			
				$this->all_insert('Reply_info','/set');
				//$keyword_model=M('Keyword');
				$data['pid']=$thisInfo['id'];
				$data['keyword']=$_POST['keyword'];
				$data['token']=session('token');
				$data['module']='Reply_info';
				$keyword_model->add($data);
				//echo $keyword_model->getLastSql();exit;
				
				
			}
		}else{
			//
			$config=unserialize($thisInfo['config']);
			$this->assign('config',$config);
			//
			//$this->assign('infoType',$this->infoTypes[$infotype]);
			$this->assign('set',$thisInfo);
			$this->display();
		}
	}
	function moban(){
		/*if($_GET['mb']){
			if($_GET['mb']=='default'){
				$a='default1';
			}else{
				$a='default';
			}
			$arr=array('DEFAULT_THEME'=>$a);
			//var_dump($arr);exit;
			file_put_contents('./App/Conf/Wap/config.php', '<?php return '.var_export($arr, true).'?>');
		}
		$as=require './App/Conf/Wap/config.php';
		$this->assign('a',$as['DEFAULT_THEME']);*/
		$moban = M('drug_moban')->where(array('token'=>session('token')))->find();
		$this->assign('moban',$moban);
		$this->display();
	}
	
	function select_moban(){
	   $moban = M('drug_moban')->where(array('token'=>session('token')))->find();
	   $data['type']= $_POST['type'];
	   if($moban){
	        M('drug_moban')->where(array('token'=>session('token')))->save($data);
	   
	   
	    }
	    else{
	    
	    $data['token'] = session('token');
	    $data['ctime'] = time();
	     M('drug_moban')->add($data);
	    }
	
	}
}