<?php 

class PublicAction extends Action{
	private $wecha_id;
	public $token;
	public function _initialize(){
		$this->wecha_id = $this->_get('wecha_id');
		$this->token = $this->_get('token');
	}
	function login(){
	$wid = $this->wecha_id;
	$this->assign('return_url',$_GET['return_url']);
	$this->assign('wecha_id',$wid);
	$this->display();
	
	
	}
	function dologin(){
	     $url = C('api_url')."/user.aspx?page=2&Email=".$_POST['tell']."&pwd=".$_POST['pwd'];
	     $res  = curlGet($url);
	     if(intval($res)!=0){
	        $user_info = M('shop_login')->where(array('UserId'=>$res))->find();
	        if(!$user_info){
	        
	             $data['UserId'] = $res;
	             $data['openid'] = $_POST['openid'];
	             $res = M('shop_login')->add($data);
	              $user_info = M('shop_login')->where(array('id'=>$res))->find();
	              
	             cookie('unid',$user_info['UserId'],3600*24*7);
	        
	        }
	        else{     
	          // var_dump($user_info);die;
	           cookie('unid',$user_info['UserId'],3600*24*7);
	        }
	        exit(json_encode(1));
	     
	     }
	     else{
	     
	        exit(json_encode(0));
	     }
	     
	}
	function register(){
     
		$unid=$this->_get('unid');
		$vid=$this->_get('vid');
		//var_dump($_GET['return_url']);
        if(isset($_GET['return_url'])){
					
					    $return_url = urldecode($_GET['return_url']);
					}
					else{
					
					  $return_url = '';
					
		}
		$this->assign('unid',$unid);
		$this->assign('vid',$vid);
		if(IS_POST){
			
			$tel=$_POST['Tel'];
			$pwd=$_POST['password'];
			$url=C("api_url")."/user.aspx?page=1&Tel=".$tel."&Pwd=".$pwd;
			$list = $this->curlGet($url);
			if(intval($list)==0){
				
				
				    $return=array('error'=>2,'msg'=>'失败','return_url'=>'');
					exit(json_encode($return));
			
			}
			else{
				$add['openid']  = $this->wecha_id;
				$add['UserId'] = intval($list);
				$add['result'] = $list;
				$res=M('shop_login')->add($add);
				if($res){
					$return=array('error'=>0,'msg'=>'注册成功','return_url'=>$return_url);
					cookie('unid',intval($list),3600*24*7);
					exit(json_encode($return));
				}
			
			else{
			
				$return=array('error'=>1,'msg'=>'失败','return_url'=>'');
			    exit(json_encode($return));
			
			
			}
		  }
		}
		$this->assign('return_url',urldecode($_GET['return_url']));
		$this->assign('wecha_id',$this->wecha_id);
		$this->display();
		
	}
	function edit_user_info(){
		$UnId = cookie('unid');
		$Email = $_POST['Email'];
		$Addres = $_POST['Addres'];
		$Name = $_POST['Name'];
		//$user = get_uid($_GET['wecha_id']);
		$url = C('api_url')."/user.aspx?page=4&Unid=".$UnId."&Tel=".$_POST['Tel']."&Email=".$Email."&Addres=".$Addres."&Name=".$Name;
		//die($url);
		$list = $this->curlGet($url);
	     echo $list;
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
}

