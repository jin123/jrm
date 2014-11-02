
<?php
class MemberAction extends CommonAction{


    function userinfo(){    
	   $where['token']= session('token');
	   $count=M('userinfo')->where($where)->count();
	   $page=new Page($count,10);
       $list=M('userinfo')->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
	   $this->assign('page',$page->show());
       $this->assign('list',$list);
       $this->display();
    } 
    function edit_user_group(){
        $group_id = $_POST['id'];
        $openid = $_POST['wecha_id'];
        echo $this->move_user_group($group_id,$openid);
    
    }
	//编辑组名
	function edit_group_name($gid,$name){
	    $access_info = $this->get_access_token();
	    $url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token=".$access_info['access_token'];
		$post_data = '{"group":{"id":'.$gid.',"name":'.'"'.$name.'"}}';
	    $res = $this->curlPost($url,$post_data);
	   
	}
	//用户所属组名
	function user_in_group($openid){
	       $access_info = $this->get_access_token();
	       $url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=".$access_info['access_token'];
	       $res = $this->curlPost($url,$post_data);
	        return json_decode($res,true);
	}
	//移动分组下的用户到另一个分组
    function move_user_group($gid,$oid){
          $access_info = $this->get_access_token();
          $post_url="https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=".$access_info['access_token'];
          $post_data = '{"openid":'.'"'.$oid.'","to_groupid":'.$gid.'}';
         
         $res = $this->curlPost($post_url,$post_data);
         return $res;
    
    }
	//创建分组
    function create_group($name){
        $access_info = $this->get_access_token();
        $post_url ="https://api.weixin.qq.com/cgi-bin/groups/create?access_token=".$access_info['access_token'];
        $post_data = '{"group":{"name":'.'"'.$name.'"}}';
        return $this->curlPost($post_url,$post_data);
    }
    function add_user_group(){    
    
        if(IS_POST){        
           $name = $_POST['name'];
           $res = $this->create_group($name);
           $res = json_decode($res);
           if(count($res)>0){
           
               $this->success('添加成功',U('Member/userinfo'));
           
           }
        
        }
        $this->display();
    
    }
	public function index(){
		$sql=M('Member');
		$sel_city = null;
		$member=$sql->field('homepic')->find();
		$this->assign('member',$member);
		$where = " 1 ";
		
		$list=M('userinfo')->where(array('token'=>session('token')))->order('id desc')->select();
		if(IS_POST){
			$key = $this->_post('searchkey');
			if(empty($key)){
				exit("关键词不能为空.");
			}
			$map['tel|wechaname'] = array('like',"%$key%"); 
			$map['token']=session('token');
			$this->assign('city',$city);			
			$list = M('userinfo')->where($map)->select();
		}		
		$this->assign('list',$list);
		$tbsign = M('Member_card_sign')->select();
		$this->assign('tbsign',$tbsign);
		$this->assign('sel_city',$sel_city);
		$this->display();
	}
	function group(){
	
	    $res = $this->sel_group();
		$this->assign('res',$res);
		$this->display();
	
	
	}
	function  ad_group(){
	
	   $gid = $_GET['id'];
	   if(IS_POST){
	   
	   
	      if($_POST['gid']==0){
		     $res =  $this->create_group($_POST['name']);
		      exit(json_encode($res));
		  }
		  else{
		  
		      $res = $this->edit_group_name($gid,$_POST['name']);
		       exit(json_encode($res));
		  }
	      return;
	   }
	    $this->assign('gid',$gid);
	    $this->display();
	
	}
	function get_group_user(){
	    $city = $_POST['city'];
	    $where = "1";
	     if($city!="全部"){
	         $where.=" AND address="."'".$city."'";
	    }
	     $res = M('userinfo')->where($where)->order('id desc')->select();
	     $sex_arr = array('1'=>'男',2=>'女');
	    foreach($res as $k=>$v){ 
	           $res[$k]['subscribe_time'] = date('Y-m-d',$v['subscribe_time']);
	            $res[$k]['sex'] = $sex_arr[$v['sex']];           
	    }
	    exit(json_encode($res));
	}
	function  tag(){
		$return = array();
		$return = M('user_tag')->order('id desc')->select();		
		foreach($return as $k=>$v){			
			  $user_info = M('Userinfo')->where('wecha_id='.'"'.$v['wecha_id'].'"')->find();
			  $return[$k]['wecha_id'] = $user_info['wecha_id'];
			  $return[$k]['wechaname'] = $user_info['wechaname'];	
		}
		$this->assign('list',$return);
		$this->display();
	}
public function get_access_token(){
	 $info =  M('access_token')->find();
	 if(!$info){
	      $result = $this->remote_access_token();
	      $data['ctime'] = time();
	      $data['access_token'] = $result['access_token'];
	      M('access_token')->add($data);
	       return $result;
	 
	 }
	 else{
	 
	     $time = strtotime('-2 hours');//当前时间十二分钟前
	     
	     if($time>$info['ctime']){
	        $res = $this->remote_access_token();
	        $data['ctime'] = time();      
	        $data['access_token'] = $res['access_token'];
	        M('access_token')->where('id='.$info)->save($data);
	       return   $res;
	     
	     }
	     else{
	     
	       return $info;
	     
	     }
	 
	 }
	 
	
	/* */
	
	}
	function remote_access_token(){
	
	
	 $where['token'] = $this->token;
	  $app_info = M("wxuser")->where($where)->order("id desc")->find();
	  $get_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_info['appid']."&secret=".$app_info['appsecret'];     
	  
	 $res = $this->curlGet($get_url);
	 $result = json_decode($res,true);
	
	 return $result;
	
	}

	function wea_sear(){
	    $res = $this->getWeatherInfo($_GET['location']);
	}
	function curlPost($url,$post_data){
	
	  $ch = curl_init();//初始化curl
      curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
      curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
      curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
       curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
       $data = curl_exec($ch);//运行curl
      curl_close($ch);
      return $data;//输出结果
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
	function  follow_group(){
	
	    $access_info = $this->get_access_token();
	    $get_url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_info['access_token'];
	    $res = $this->curlGet($get_url);
	    echo "<pre>";
	    var_dump(json_decode($res,true));

	}
	function sel_group(){
	
	  $access_info = $this->get_access_token();
	  $res = $this->curlGet("https://api.weixin.qq.com/cgi-bin/groups/get?access_token=".$access_info['access_token']);
	  return json_decode($res,true);
	}
	function find_user_group($openid){
	
	     $access_info = $this->get_access_token();
	     $post_url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=".$access_info['access_token'];
	     //$post_data = '{"openid":".$openid."}'; 
	      $post_data = '{"openid":'.'"'.$openid.'"'.'}'; 
	     $res = $this->curlPost($post_url,$post_data);
	     $res = json_decode($res,true);
	     return $res['groupid'];
	}
	function send_msg(){
	
	    $ids = $_POST['ids'];
	    $id = substr($ids,1);
	    $id = explode(',',$id);
	    $return = array();
	    foreach($id as $v){	       
	       $return[] = $this->replay_message($v,$_POST['content']);	    
	    }
	     exit(json_encode($return));
	}
	function replay_message($openid,$content){
	     $access_info = $this->get_access_token();
	      $post_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_info['access_token'];
	    $post_data = '{
    "touser":'.'"'.$openid.'"'.',
    "msgtype":"text",
    "text":
    {
         "content":'.'"'.$content.'"'.'
    }
}';

        
	     $json = $this->curlPost($post_url,$post_data);
	     return json_decode($json,true);
	}
	function del_tag(){
		
		
		if(!isset($_GET['id']) || empty($_GET['id'])){
			
			
			$this->error('发生异常');
			return;
		}
		 $res = M('user_tag')->where('id='."'".$_GET['id']."'")->delete();
	    if($res){
			
			$this->success('操作成功');
			
		}
		else{
			$this->error('操作失败');
		}
	}
	function add_tag(){	
		$user_info = M('userinfo')->order('id desc')->select();
		if(IS_POST){
		  $_POST['ctime'] = time();
		  if($_POST['wecha_id']==""){
		       $this->error('请选择会员',U('Member/add_tag'));
		  
		  }
		  if($_POST['tag_name']==""){
		       $this->error('名称不能为空',U('Member/add_tag'));
		  
		  }
		  $res = M('user_tag')->add($_POST);
		  if($res){
		      $this->success('操作成功',U('Member/tag'));
		  }
		  else{
		  
		  $this->error('操作失败',U('Member/add_tag'));
		  
		  }
		
		}
		foreach ($user_info as $k=>$v){
			
			
			if($v['wechaname']==""){
				unset($user_info[$k]);
			}
		}
		$this->assign('data',$user_info);
		$this->display();
	}
	function do_add_tag(){
		
		$data['ctime'] = time();
		$data['wecha_id'] = $_POST['sel_user'];
		$data['tag_name'] = $_POST['tag_name'];
		$res = M('user_tag')->add($data);
		$json = array();
		if($res){			
			$json['s'] = 1;
			$json['m'] = '操作成功';	
		}
		else{
			$json['s'] = 0;
			$json['m'] = '操作失败';
		}
		exit(json_encode($json));
	}
	function edit_tag(){
	
		$id = $_POST['id'];
		$data['tag_name'] = $_POST['tag_name'];
		$sel = M('user_tag')->where('id='.$_POST['id'])->find();
		
		if($sel){
			
			$res = M('user_tag')->where('id='.$_POST['id'])->save($data);
			
		}
		else{
			$data['wecha_id'] = $_POST['wecha_id'];
			$data['ctime'] = time();
			$res = M('user_tag')->add($data);
			//echo M('user_tag')->getlastsql();
			
		}
		if($res){
			
			$this->success('操作成功');
			
		}
		else{
			
			
			$this->error('操作失败');
		}
		 
	}
	public function add(){
		$sql=M('Member');
		$data['token']=$this->_get('token');
		$data['uid']=session('uid');
		$member=$sql->field('id')->where($data)->find();
		$pic['homepic']=$this->_post('homepic');
		if($member!=false){
			$back=$sql->where($data)->save($pic);
			if($back){
				$this->success('更新成功');
			}else{
				$this->error('服务器繁忙，请稍后再试1');
			}
		}else{
			$data['homepic']=$pic['homepic'];
			$back=$sql->add($data);
			if($back){
				$this->success('更新成功');
			}else{
				$this->error('服务器繁忙，请稍后再试');
			}
		}
	}
	public function del(){
		$where['token']= session('token');
		$where['id']=$this->_get('id');
		$where=M('Userinfo')->where($where)->delete();
		if($where){
			$this->success('操作成功');
		}else{
			$this->error('服务器繁忙，请稍候再试');
		} 
	}

	//------------------------------------------
	// 添加消费积分记录
	//------------------------------------------

	//------------------------------------------
	// 添加消费积分记录
	//------------------------------------------

	public function edit(){

		if(!IS_POST){
			$this->error('没有提交任何东西');exit;	
		}
		$token = session('token');
		$wecha_id = $this->_post('wecha_id');
		$add_expend = intval($this->_post('add_expend'));
		$add_expend_time = $this->_post('add_expend_time');
		
		if($add_expend <= 0){
			//$this->error('消费金额必须大于0元');exit;	
		}
		//获取商家设置 tp_member_card_exchange
		$exchange = M('Member_card_exchange');
		$getset = $exchange->where(array('token'=>$token))->find();
		$userinfo = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->find();


		 $data['token']    = $token;
		 $data['wecha_id'] = $wecha_id;
		 $data['sign_time'] = strtotime($add_expend_time);
		 $data['score_type'] = 2;
		 $data['expense']  = ceil($add_expend * $getset['reward']);
		 $data['sell_expense'] = $add_expend; //消费金额
		 $back = M('Member_card_sign')->data($data)->add();

		 //总记录
		 $da['total_score']   = $userinfo['total_score'] +  $data['expense'];
         $da['expend_score']  = $userinfo['expend_score'] + $data['expense'];
         $da['add_expend']    = $add_expend;
         $da['add_expend_time']=strtotime($add_expend_time);
         $back2 = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->save($da);
        if($back && $back2){
			$this->success('操作成功');
		}else{
			$this->error('服务器繁忙，请稍候再试');
		} 
	}
}
?>