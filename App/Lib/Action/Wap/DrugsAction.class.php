<?php 

class DrugsAction extends Action{
	public $token;
	public $wecha_id;
	public  $module = 'Drugs';
	public function _initialize(){
	
	   
		$this->wecha_id = $this->_get('wecha_id');
		$this->token = $this->_get('token');
		$moban = M('drug_moban')->where(array('token'=>$this->token))->find();
	
		if($moban['type']==="1" || !isset($moban['type'])){
		
		    $this->module = "Drugs";
		
		}
		else if($moban['type']==="0"){
		   $this->module = "Drugs1";
		
		}
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		$this->assign('wxinfo',$vipid);
		
	}

	function index(){
	    $db=D('Flash');
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
	 	$where['token'] = $this->token;
	 	$where['type'] = 1;
	 	$info=$db->where($where)->select();
	    $drugs_where['token'] = $this->token;
		$result =M('drugs_type')->where($drugs_where)->limit(9)->order('id desc')->select();
		//$url = C('api_url').'/Product.aspx?page=1&pagesize=10&tuijian=1&vipid='.$vipid['vipid'];
		$url = C('api_url').'/Product.aspx?page=1&pagesize=10&vipid='.$vipid['vipid'];
		if(IS_POST){
	    	$key=$this->_post('searchkey');
	    	$url= C('api_url').'/Product.aspx?page=1&pagesize=10&vipid='.$vipid['vipid']."&name=".$key;
	    }
		$list = $this->curlGet($url);
		$data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		
		$this->assign('data',$data);
		$this->assign('flag',$result);
		$this->assign('info',$info);
	    $this->display($this->module."/index");
	 
	 }
	 function test(){
	    $db=D('Flash');
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
	 	$where['token'] = $this->token;
	 	$where['type'] = 1;
	 	$info=$db->where($where)->select();
	    $drugs_where['token'] = $this->token;
		$result =M('drugs_type')->where($drugs_where)->limit(9)->order('id desc')->select();
		//$url = C('api_url').'/Product.aspx?page=1&pagesize=10&tuijian=1&vipid='.$vipid['vipid'];
		$url = C('api_url').'/Product.aspx?page=1&pagesize=10&vipid='.$vipid['vipid'];
		if(IS_POST){
	    	$key=$this->_post('searchkey');
	    	$url= C('api_url').'/Product.aspx?page=1&pagesize=10&vipid='.$vipid['vipid']."&name=".$key;
	    }
		$list = $this->curlGet($url);
		$data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		
		$this->assign('data',$data);
		$this->assign('flag',$result);
		$this->assign('info',$info);
	    $this->display();
	 
	 }
	 /*立即购买*/
   function shopping(){
		$where['openid'] = $this->wecha_id;
		$is_login = M('shop_login')->where($where)->find();
		$UnId = cookie('unid');
		if(!$UnId){
			$return['log_url'] = "/index.php?s=Wap/Public/login/vid/".$_POST['VipId']."/unid/".$_POST['UnId']."/name/".$_POST['Name']."&token=".$this->token."&wecha_id=".$this->wecha_id."&return_url=".urlencode("/index.php?s=/Wap/Drugs/xiangqing/vid/".$_POST['VipId']."/unid/".$_POST['UnId']."/name/".$_POST['Name']."/wecha_id/".$this->wecha_id."/token/".$this->token);
			$return['status'] = "-1";
			$return['msg'] = "当前用户没有注册";

			exit(json_encode($return));
			
		}
		
		$userid=M('shop_login')->where(array('openid'=>$this->wecha_id))->find();
		$url=C('api_url').'/order.aspx?page=2&VID='.$_POST['VipId'].'&PID='.$_POST['UnId'].'&UserID='.$userid['UserId'].'&Price='.$_POST['JiaGe'].'&Sum='.$_POST['Sum'];
		$info = $this->curlGet($url);
		if($info){
			$res=array('error'=>1,'msg'=>'购买成功');
		}else{
			$res=array('error'=>0,'msg'=>'请稍候重试');
		}
		die(json_encode($res));
	}
	//预览二级页面
	function  lists(){
	
	    $sortid=$this->_get('sortid');
		$where['send_id'] = $sortid;
		$data = M('drugs_flag')->where($where)->select();
		$key=0;
		$list = array();
	    foreach($data as $k=>$v){
		      
		      $result = curlGet(C('api_url').'/Sort.aspx?page=1&flag=2&sortid='.$v['flag_id']);
              $result = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $result),true);
              foreach($result['rows'] as $k1=>$v1){
			    
			           $list[$key]['title'] = $v1['title'];
			        	$third_url = C('api_url').'/Sort.aspx?Flag=3&page=1&sortid='.$v1['unid'];
						$third = $this->curlGet($third_url);
						$third = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $third),true);
						$list[$key]['third'] = $third['rows'];
						$key++;
			  
			  }
		      
		
		}
		$this->assign('data',$list);
		 $this->display($this->module."/lists");
	   // $this->display();	
	
	}
	//药品详情页
	function xiangqing(){
		$wxuser=M('Wxuser');
		$baoyou=M('Wxuser')->field('baoyou')->where(array('token'=>$this->token))->find();
		$this->assign('baoyou',$baoyou['baoyou']);

		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		//药品
		$name=$this->_get('name');
		//$vipid=$this->_get('vid');
		$unid=$this->_get('unid');
		$url = C('api_url').'/Product.aspx?page=1&vipid='.$vipid['vipid'].'&unid='.$unid.'&name='.$name;
		//dump($url);exit;
		$list = $this->curlGet($url);
		$data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		//echo "<pre>";
		//dump($data);exit;
		//汉字转拼音
		$GetPin = new GetPin();
		$name = $GetPin->Pinyin($data['rows'][0]['Name']);
		$this->assign('name',$name);
		$this->assign('data',$data['rows']);
		 $this->display($this->module."/xiangqing");
		//$this->display();
	}
	
	
	//药品分类
	function chanpin(){
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		$this->assign('vipid',$vipid['vipid']);
		
		$sortid=$this->_get('sortid');
		
		$title=$this->_get('title');
		$flag = $_GET['Flag'];
		$url=C('api_url').'/Product.aspx?page=1&fenlei='.$sortid.'&vipid='.$vipid['vipid'];
		if(IS_POST){
			$key=$this->_post('searchkey');
			$url.="&fName=".$key;
		}
		//echo $url;
		$info = $this->curlGet($url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
		
		$this->assign('title',$title);
		$this->assign('data',$data);
		 $this->display($this->module."/chanpin");
		//$this->display();
	}
	
	//分类
	function fenlei(){
		$list = $this->curlGet(C('api_url').'/Sort.aspx?Flag=1&sortid=0');
		$flag  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		$this->assign('flag',$flag);
		 $this->display($this->module."/fenlei");
	//	$this->display();
	}
	
	//添加购物车
	function add_gouwuche(){
	   $UnId = cookie('unid');
		$shopcar = M("shop_car");
	    $where['openid'] = $this->wecha_id;
		$is_login = M('shop_login')->where($where)->find();
		 if(!cookie('unid')){
		    $url = "/index.php?s=Wap/Public/login&return_url=".$_SERVER['HTTP_REFERER']."&token=".$_POST['token']."&wecha_id=".$_POST['wecha_id'];
		   $result = array('error'=>2,'login_url'=>$url);
		   exit(json_encode($result));
	    }
		if(IS_POST){
			$where['pid']=$_POST['UnId'];
			$where['vid']=$_POST['VipId'];
			$where['JiaGe']=$_POST['JiaGe'];
			$where['UserId']=$UnId;
			$where['Sum']=$_POST['Sum'];
			$where['Name']=$_POST['Name'];
			$where['Pic']=$_POST['Pic'];
			$where['createtime']=time();
			$where['Money']=$_POST['JiaGe']*$_POST['Sum'];
			if($shopcar->create()){
				if($shopcar->add($where)){
					
					$res=array('error'=>1,'msg'=>'添加购物车成功');
				}else{
					$res=array('error'=>0,'msg'=>'请稍候重试');
				}
				exit(json_encode($res));
			}
			
			
		}
	}
	//购物车
	function gouwuche(){
	    $UnId = cookie('unid');
		$shopcar=M('ShopCar');    
		$where['UserId'] = $UnId;
	    $count = $shopcar->where($where)->count();   
	    $page=new WeinxinPage($count,5);
	    
	    $data=$shopcar->limit($page->firstRow.','.$page->listRows)->where($where)->order('createtime desc')->select();
	    $c=0;
		
	    foreach($data as $k=>$v){
			$c = ($c+intval($v['Money']));
	    }
	    $this->assign('count',$count);
	    $this->assign('page',$page->show());
	    $this->assign('data',$data);
        $this->assign('count_money',$c);
         $this->display($this->module."/gouwuche");
	//	$this->display();
	}
	
	
	//批量删除购物车商品
	function del(){
		$shopcar=M('ShopCar');
		$getid=$this->_request('id');	
		$getid = substr($getid,1);
		if(!$getid){
			$this->error('没有选中记录');	
		}
		$map['id'] = array('exp',' IN ('.$getid.') ');
		$result=$shopcar->where($map)->delete();
	//	echo $shopcar->getlastsql();
		if($result){
			$res=array('error'=>0,'msg'=>'成功删除该药品');
		}else{
			$res=array('error'=>1,'msg'=>'请稍候重试');
		}
		exit(json_encode($res));
	}

		
	//我的药箱（会员中心）
	function hyzx(){
        $UnId = cookie('unid'); 
	    if(!cookie('unid')){
	          header("location: /index.php?s=Wap/Public/login&return_url=/index.php?s=Wap/Drugs/hyzx/wecha_id/".$_GET['token']."/token/".$_GET['token']);
	       }
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		$this->assign('vipid',$vipid['vipid']);
	     $url =C('api_url')."/order.aspx?page=1&UserId=".$UnId."&vid=".$vipid['vipid'];
	    $data =  $this->curlGet($url);
	    $data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $data),true);
	    foreach($data['rows'] as $k=>$v){
		   $count = $count+$v['Price']*$v['Sum'];
		   $where['unid'] = $v['UnId'];
		   $order = M("shop_order")->field('status')->where($where)->find();
		   $data['rows'][$k]['status'] = $order['status'];
		}
        $user_api = C("api_url")."/user.aspx?page=3&Unid=".$UnId;
	    $userinfo =  $this->curlGet($user_api);
		 $userinfo = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $userinfo),true);
	    $this->assign('userinfo',$userinfo['rows'][0]);
	    $this->assign('count',$count);
	    $this->assign('data',$data);
	    $this->display($this->module."/hyzx");
		//$this->display();
	}
	
	//我的订单
	function myorder(){
		$shoporder=M('ShopOrder');
		$data=$shoporder->select();
		$this->assign('data',$data);
		$this->display($this->module."/hyzx");
		//$this->display('Drugs/hyzx');
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
	//结算
	function dobug(){
		 $UnId = cookie('unid');
	    $shop_car = M("shop_car");
	    $ids = $_POST['ids'];
		$ids = substr($ids,1);
	    $map['id'] = array('exp',' IN ('.$ids.') ');
	    $car_info = $shop_car->where($map)->select();
        foreach($car_info as $val){
		
		   
		
		   $this->bug_goods($UnId,$val);
		
		
		}
  	    $shop_car->where($map)->delete();
		$url = "/index.php?s=Wap/Drugs/hyzx&wecha_id=".$this->wecha_id;
		$return=array('error'=>1,'msg'=>'购买成功','url'=>$url);
			exit(json_encode($return));
	}
	function bug_goods($UnId,$data){
	    $Unid = cookie('unid');
     	$userid=M('shop_login')->where(array('openid'=>$wecha_id))->find();
		$url=C('api_url').'/order.aspx?page=2&VID='.$data['vid'].'&PID='.$data['pid'].'&UserID='.$Unid.'&Price='.$data['JiaGe'].'&Sum='.$data['Sum'];
		$info = $this->curlGet($url);
	   return $info;
	
	}
	function add_sum(){
	
	   $id = $_POST['id'];
	   $sum = $_POST['sum'];
	   $where['id'] = $id;
	
	   echo   M("shop_car")->where($where)->save(array('Sum'=>$sum));
	  
	}
	function edit_info(){
	    $Unid = cookie('unid');
		$wecha_id = $_GET['wecha_id'];
		$user = get_uid($wecha_id);
	   if(IS_POST){
	        $name = $_POST['name'];
			$address = $_POST['address'];
			$Email = $_POST['Email'];
		    $url = C('api_url')."/user.aspx?page=4&Unid=".$Unid ."&Email=".$Email."&Addres=".$address."&Name=".$name."&Tel=".$_POST['Tel'];
		   $list = $this->curlGet($url);
	       if($list==="Success"){
		   
		      exit(json_encode(1));
		   }
		   else{
		     exit(json_encode(0));
		   
		   }
	       return;
	   }
         $user_api = C("api_url")."/user.aspx?page=3&Unid=".$Unid;
	     $userinfo =  $this->curlGet($user_api);
		 $userinfo = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $userinfo),true);
		 $this->assign("userinfo",$userinfo['rows'][0]);
	     $this->display();
	
	}

	function hot(){
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
	 	
	   $url = C('api_url').'/Product.aspx?page=1&pagesize=10&tuijian=1&vipid='.$vipid['vipid'];
	    
	    $list = $this->curlGet($url);
	    $data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
	    $this->assign('data',$data);
	    $this->display($this->module."/hot");
    	//$this->display();
	}

}
