<?php 

class MediaAction extends Action{
	public $token;
	public $wecha_id;
	public function _initialize(){
	
	
	
		$this->wecha_id = $this->_get('wecha_id');
		$this->token = $this->_get('token');
	}
	function index(){
	 	$list = $this->curlGet(C('api_url').'/Sort.aspx?Flag=1&page=1&sortid=0');
	 	$flag  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
	 	$this->assign('flag',$flag);
	 	$db=D('Flash');
	 	$_POST["type"]=$_POST["type"];
	 	$where['token'] = $this->token;
	 	$where['type'] = 1;
	 	$info=$db->where($where)->select();
		
	 	//药品
	 	
	 	$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
	 	
	   $url = C('api_url').'/Product.aspx?page=1&pagesize=10&tuijian=1&vipid='.$vipid['vipid'];
	    if(IS_POST){
	    	$key=$this->_post('searchkey');
	    	$url.= C('api_url').'/Product.aspx?page=1&pagesize=10&vipid='.$vipid['vipid']."&name=".$key;
	    }
	    $list = $this->curlGet($url);
	    $data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
	    $this->assign('info',$info);
	    $this->assign('data',$data);
    	$this->display();
	 }
	 function preview(){
	    $db=D('Flash');
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
	 	$where['token'] = $this->token;
	 	$where['type'] = 1;
	 	$info=$db->where($where)->select();
	    $drugs_where['token'] = $this->token;
		$result =M('drugs_type')->where($drugs_where)->limit(9)->order('id desc')->select();
		$data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		$this->assign('data',$data);
		$this->assign('flag',$result);
		$this->assign('info',$info);
	    $this->display();
	 
	 }
	//二级分类页
	function lists(){
		$sortid=$this->_get('sortid');
		$title=$this->_get('title');
		//二级分类
		$url=C('api_url').'/Sort.aspx?Flag=2&page=1&sortid='.$sortid;
		$info = $this->curlGet($url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
		foreach ($data['rows'] as $k=>$v){
			$third_url = C('api_url').'/Sort.aspx?Flag=3&page=1&sortid='.$v['unid'];
			$third = $this->curlGet($third_url);
			$third = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $third),true);
			$data['rows'][$k]['third'] = $third['rows'];
		}
		$this->assign('id',$sortid);
		$this->assign('title',$title);
	 	$this->assign('data',$data);
	    $this->display();	
	}
	//预览二级页面
	function  preview_lists(){
	
	    $sortid=$this->_get('sortid');
		$where['send_id'] = $sortid;
		$data = M('drugs_flag')->where($where)->select();
		$key=0;
		$list = array();
	    foreach($data as $k=>$v){
		      
		      $result = curlGet(C('api_url').'/Sort.aspx?page=1&flag=2&sortid='.$v['flag_id']);
              $result = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $result),true);
		//	  echo "<pre>";var_dump($result['rows']);
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
	    $this->display();	
	
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
		$list = $this->curlGet($url);
		$data  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		//dump($data);exit;
		//汉字转拼音
		$GetPin = new GetPin();
		$name = $GetPin->Pinyin($data['rows'][0]['Name']);
		$this->assign('name',$name);
		$this->assign('data',$data['rows']);
		$this->display();
	}
	
	/* //三级分类页
	function lists2(){
		$sortid=$this->_get('sortid');
		$title=$this->_get('title');
		
		//上一级分类id
		$sencendid=$this->_get('sencendid');
		$first_url = C('api_url').'/Sort.aspx?Flag=3&sortid='.$sortid;
		
		$info = $this->curlGet($first_url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
		
		$two_info = $this->curlGet(C('api_url').'/Sort.aspx?Flag=2&sortid='.$sencendid);
		$two_info = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $two_info),true);
		//echo "<pre>";
		//var_dump($two_info);
		$this->assign('two_info',$two_info['rows']);
		$this->assign('sencendid',$sencendid);
		$this->assign('title',$title);
		$this->assign('id',$sortid);
		$this->assign('data',$data);
		$this->display();
	} */
	
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
			$url.="&name=".Name;
		}
		//echo $url;
		$info = $this->curlGet($url);
		$data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $info),true);
		
		$this->assign('title',$title);
		$this->assign('data',$data);
		
		$this->display();
	}
	
	//分类
	function fenlei(){
		$list = $this->curlGet(C('api_url').'/Sort.aspx?Flag=1&sortid=0');
		$flag  = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $list),true);
		$this->assign('flag',$flag);
		$this->display();
	}
	
	//添加购物车
	function add_gouwuche(){
		$shopcar = M("shop_car");
	    $where['openid'] = $this->wecha_id;
		$is_login = M('shop_login')->where($where)->find();
		if(!$is_login){
		  $login_url = "/index.php?s=Wap/Public/register&wecha_id=".$this->wecha_id."&return_url=".urlencode($_SERVER['HTTP_REFERER']); 
		  $res=array('error'=>2,'msg'=>'当前用户没有注册','login_url'=>$login_url);
		  exit(json_encode($res));
		
		}
		if(IS_POST){
			$where['pid']=$_POST['UnId'];
			$where['vid']=$_POST['VipId'];
			$where['JiaGe']=$_POST['JiaGe'];
			$where['wecha_id']=$_GET['wecha_id'];
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
		$shopcar=M('ShopCar');    
		$where['wecha_id'] = $this->wecha_id;
	    $count = $shopcar->where($where)->count();   
	    $page=new WeinxinPage($count,5);
	    
	    $data=$shopcar->limit($page->firstRow.','.$page->listRows)->where($where)->order('createtime desc')->select();
             // echo   $shopcar->getlastsql();
	    $total=0;
	    foreach($data as $k=>$v){
	    	
	    	$total=$v['Money']+$total;
	    }
	    $this->assign('total',$total);
	    $this->assign('count',$count);
	    $this->assign('page',$page->show());
	    $this->assign('data',$data);

		$this->display();
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
		die(json_encode($res));
	}
	
	//购买药品  已经废弃
	function shopping(){
		$where['openid'] = $this->wecha_id;
		$is_login = M('shop_login')->where($where)->find();
		if(!$is_login){
			$return['log_url'] = "/index.php?s=Wap/Public/register/vid/".$_POST['VipId']."/unid/".$_POST['UnId']."/name/".$_POST['Name']."&token=".$this->token."&wecha_id=".$this->wecha_id."&return_url=".urlencode("/index.php?s=/Wap/Drugs/xiangqing/vid/".$_POST['VipId']."/unid/".$_POST['UnId']."/name/".$_POST['Name']."/wecha_id/".$this->wecha_id."/token/".$this->token);
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
		
	//我的药箱（会员中心）
	function hyzx(){
		$vipid=M('Wxuser')->where(array('token'=>$this->token))->find();
		//dump($vipid);exit;
		$this->assign('vipid',$vipid['vipid']);
		$wecha_id = $_GET['wecha_id'];
         $user = get_uid($wecha_id);
         
		$user_info=M('Userinfo');
		$userdata=$user_info->where(array('wecha_id'=>$wecha_id))->find();
		$this->assign('userdata',$userdata);
	    $return_url = "/index.php?".$_SERVER['QUERY_STRING'];
          if(!$user){
		  
		     header("location:index.php?s=Wap/Public/register/&wecha_id=".$wecha_id."&return_url=".urlencode($return_url));
		  
		  }
	     $url =C('api_url')."/order.aspx?page=1&UserId=".$user['UserId']."&vid=".$vipid['vipid'];
//dump($url);exit;
	    $data =  $this->curlGet($url);
	    
	    $data = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $data),true);
	    foreach($data['rows'] as $k=>$v){
		   $count = $count+$v['Price']*$v['Sum'];
		   $where['unid'] = $v['UnId'];
		   $order = M("shop_order")->field('status')->where($where)->find();
		   $data['rows'][$k]['status'] = $order['status'];
		}
        $user_api = C("api_url")."/user.aspx?page=3&Unid=".$user['UserId'];
	    $userinfo =  $this->curlGet($user_api);
		 $userinfo = json_decode(str_replace(array("\r", "\n", "\r\n"), "", $userinfo),true);
	    $this->assign('userinfo',$userinfo['rows'][0]);
	    $this->assign('count',$count);
	    $this->assign('data',$data);
		$this->display();
	}
	
	//我的订单
	function myorder(){
		$shoporder=M('ShopOrder');
		$data=$shoporder->select();
		$this->assign('data',$data);
		$this->display('Drugs/hyzx');
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
		
	    $shop_car = M("shop_car");
	    $ids = $_POST['ids'];
		$ids = substr($ids,1);
	    $map['id'] = array('exp',' IN ('.$ids.') ');
	    $car_info = $shop_car->where($map)->select();
        foreach($car_info as $val){
		
		
		
		   $this->bug_goods($this->wecha_id,$val);
		
		
		}
		$url = "/index.php?s=Wap/Drugs/hyzx&wecha_id=".$this->wecha_id;
		$return=array('error'=>1,'msg'=>'购买成功','url'=>$url);
			exit(json_encode($return));
	}
	function bug_goods($wecha_id,$data){
     	$userid=M('shop_login')->where(array('openid'=>$wecha_id))->find();
		$url=C('api_url').'/order.aspx?page=2&VID='.$data['vid'].'&PID='.$data['pid'].'&UserID='.$userid['UserId'].'&Price='.$data['JiaGe'].'&Sum='.$data['Sum'];
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
		$wecha_id = $_GET['wecha_id'];
		$user = get_uid($wecha_id);
	   if(IS_POST){
	        $name = $_POST['name'];
			$address = $_POST['address'];
			$Email = $_POST['Email'];
		    $url = C('api_url')."/user.aspx?page=4&Unid=".$user['UserId']."&Email=".$Email."&Addres=".$address."&Name=".$name;
		   $list = $this->curlGet($url);
	       if($list==="Success"){
		   
		      exit(json_encode(1));
		   }
		   else{
		     exit(json_encode(0));
		   
		   }
	       return;
	   }
         $user_api = C("api_url")."/user.aspx?page=3&Unid=".$user['UserId'];
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
    	$this->display();
	}

}
