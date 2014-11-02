<?php
class MessageAction extends CommonAction {
    public function index(){
         if(IS_POST){
		    $title = $_POST['title'];
		    $object = $_POST['object'];//用户群
		    $msgtype = $_POST['msgtype'];//消息类型
		    $content = $_POST['content'];//文本内容
			$sex = $_POST['sex'];
			$thumb_id = $_POST['thumb_id'];
			$gid = $_POST['gid'];
			$city = $_POST['city'];
		    $where['token'] = session('token');
		     if($object==2){
				  if(!isset($thumb_id) || empty($thumb_id)){
			     
					 if($content==""){
					 
						$json['errcode'] = 1;
						$json['errmsg'] = "文本或者图片内容不能为空";
					     exit(json_encode($json));	  
					 }
				     exit($this->text_gmsg_group($gid,$content));
			       }
					else if(!isset($content) || empty($content)){
					  
					 if($thumb_id==""){
						 
							$json['errcode'] = 1;
							$json['errmsg'] = "文本或者图片内容不能为空";
						    exit(json_encode($json));	  
						 }
						 exit($this->img_gmsg_group($gid,$thumb_id));
					
					}
			       exit($this->group_msg($gid,$content,$thumb_id,$title));
			 }
			 if($sex!=2){
			      $where['sex'] = $sex;
					$where['is_follow'] = 1;
			       $openids = $this->sel_openids($where);

				 if(!isset($thumb_id) || empty($thumb_id)){
			     
					 if($content==""){
					 
						$json['errcode'] = 1;
						$json['errmsg'] = "文本或者图片内容不能为空";
					     exit(json_encode($json));	  
					 }
				     exit($this->text_gmsg($openids,$content));
			       }
					else if(!isset($content) || empty($content)){
					  
					 if($thumb_id==""){
						 
							$json['errcode'] = 1;
							$json['errmsg'] = "文本或者图片内容不能为空";
						    exit(json_encode($json));	  
						 }
						 exit($this->img_gmsg($openids,$thumb_id));
					
					}
			    	
				  
			       $res = $this->openid_group_msg($openids,$content,$thumb_id,$title);
				    $add['msgtype'] = $msgtype;		
					$add['content'] = $res;
					$add['token'] = session('token');
					M("gmsg_log")->add($add);
					exit($res);
					return;
			 }		 
			 if($city!=1){
				 if(!isset($thumb_id) || empty($thumb_id)){
			     
					 if($content==""){
					 
						$json['errcode'] = 1;
						$json['errmsg'] = "文本或者图片内容不能为空";
					     exit(json_encode($json));	  
					 }
				     exit($this->text_gmsg($openid,$content));
			       }
					else if(!isset($content) || empty($content)){
					  
					 if($thumb_id==""){
						 
							$json['errcode'] = 1;
							$json['errmsg'] = "文本或者图片内容不能为空";
						     exit(json_encode($json));	  
						 }
						 exit($this->img_gmsg($openid,$thumb_id));
					
					}

				$where['address'] = $city;
			    $openids = $this->sel_openids($where);
			    $res =  $this->openid_group_msg($openids,$content,$thumb_id,$title);
			    $add['msgtype'] = $msgtype;		
				$add['content'] = $res;
				$add['token'] = session('token');
				M("gmsg_log")->add($add);
				exit($res);
				return;
			 }   
				// $where['address'] = $city;
				/*给所有用户发送*/
				if($thumb_id=="" || $content==""){
						 
							$json['errcode'] = 1;
							$json['errmsg'] = "文本或者图片内容不能为空";
					 exit(json_encode($json));	  
				}
			   $openids = $this->sel_openids($where);			    
			   $res =  $this->openid_group_msg($openids,$content,$thumb_id,$title);
               $add['msgtype'] = $msgtype;		
				$add['content'] = $res;
				$add['token'] = session('token');
				M("gmsg_log")->add($add);
               exit($res);				
			 return;
		 } 
		 $where['token'] = session('token');
		 $where['is_follow'] = 1;
		 $data = M('userinfo')->field('wecha_id,sex,address')->where($where)->select();
		 $city = M('userinfo')->distinct(true)->field('address')->where($where)->select();
		 $this->assign('list',$data);
		  $this->assign('city',$city);
	    $this->display();
      
    }
	function sel_openids($where){
	
	     $list = M('userinfo')->field('wecha_id')->where($where)->select();
		 $openids =array();
		 foreach($list as $v){			
			 $openids[] = $v['wecha_id'];
		 }

	       return $openids;
	      
	}
	public function get_access_token(){
	$where['token'] = session('token');
	 $info =  M('access_token')->where($where)->find();
	
	 if(!$info){	 
	      $result = $this->remote_access_token();
	      $data['ctime'] = time();
	      $data['access_token'] = $result['access_token'];
		  $data['token'] = session('token');
	      M('access_token')->add($data);
	       return $result;
	 
	 }
	 else{
	     $time = strtotime('-2 hours');//
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
	 
	
	/* 上传图文素材*/
	
	}
	function upload_material($post,$access_info){
	   $post_url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=".$access_info['access_token'];
	   $res = $this->curlPost($post_url,$post);
	   return json_decode($res,true);
	}
	
	function group_msg($gid,$content,$thumb_id,$title){
		     $access_info = $this->get_access_token();
	        $post = '{
   "articles": [
		 {
		    "thumb_media_id":'.'"'.$thumb_id.'",
              "author":"",
			 "title":'.'"'.$title.'",
			 "content_source_url":"",
			"content":'.'"'.$content.'",
			"digest":"",
            "show_cover_pic":"1"
		 }
   ]
}';          
            $result = $this->upload_material($post,$access_info);
			if($result['errcode']!=0){
			  exit(json_encode($result));
			}
	          $url ="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$access_info['access_token'];
	           $thumb_media_id = $result["media_id"];
		       $post_data = '{
					   "filter":{
						   "group_id":'.'"'.$gid.'"
					   },
					   "mpnews":{
						  "media_id":'.'"'.$thumb_media_id.'"
					   },
						"msgtype":"mpnews"
					}';
					
		     $res = $this->curlPost($url,$post_data);

			$add['msgtype'] = '-1';		
			$add['content'] = $res;
			$add['token'] = session('token');
			M("gmsg_log")->add($add);
			exit($res);
			$res = json_decode($res,true);
		
	}
	function remote_access_token(){
	
	
	 $where['token'] = $this->token;
	  $app_info = M("wxuser")->where($where)->order("id desc")->find();
	  $get_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_info['appid']."&secret=".$app_info['appsecret'];     
	  
	 $res = $this->curlGet($get_url);
	 $result = json_decode($res,true);
	
	 return $result;
	
	}
    function find_user_group(){
	
	     $access_info = $this->get_access_token();
	     $res = $this->curlGet("https://api.weixin.qq.com/cgi-bin/groups/get?access_token=".$access_info['access_token']);
	     exit($res);
	}
    public function sendto(){
        $where['token'] = session('token');
		 $list = M("gmsg_log")->where($where)->order('id desc')->select();
		 foreach($list as $k=>$v){
		 
		    $content = json_decode($v['content'],true);
		    $list[$k]['errcode'] = $content['errcode'];
			$list[$k]['errmsg'] = $content['errmsg'];
			if($v['errcode']==0){
			$list[$k]['msg_id'] = $content['msg_id'];
			}
		 }
		 $this->assign('list',$list);
	    $this->display();
      
    }
	function del(){
	
	    $id = $_GET['id'];
	    
	    $res = $this->del_gmsg($id);
		exit($res);
	}
	function del_gmsg($id){
	     $access_info = $this->get_access_token();
	     $url = "https://api.weixin.qq.com//cgi-bin/message/mass/delete?access_token=".$access_info['access_token'];
	     $post_data = '{
		   "msgid":'.$id.'
		}';
		$res = $this->curlPost($url,$post_data);
		return $res;
	
	}
	function one__message(){
	    $map['token'] = session('token');
		$search_info = null;
		if(IS_POST){
		
		   $where = "token="."'".session('token')."'";
		   if(!empty($_POST['wechaname'])){
		   
		      $where.=" AND wechaname like "."'%".trim($_POST['wechaname'])."%'";
		   
		   }
		   $search_info = M('userinfo')->where($where)->select();
		
		
		}
		$this->assign('search_info',$search_info);
	    $this->display();
	
	
	}
	
	function text_gmsg_group($gid,$content){
	$access_info = $this->get_access_token();
	  $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$access_info['access_token'];
	     $post_data = '{
		   "filter":{
			   "group_id":'.'"'.$gid.'"
		   },
		   "text":{
			 "content":'.'"'.$content.'"
		   },
			"msgtype":"text"
		}';
	    //  die($this->curlPost($url,$post_data));
		return $this->curlPost($url,$post_data);
	
	}
	function imglist(){
	   $map['token'] = $this->token;
	   $list = M('pic_material')->where($map)->select();
	   $this->assign("list",$list);
	   $this->display();
	
	}
	function insert(){
      $id = $_GET["id"];
	  $map['token'] = $this->token;
	  $map['id'] = $_GET['id'];
          if(IS_POST){
		  
		      $data['title'] = $_POST['title'];
			  $data['auther'] = $_POST['author'];
			  $data['thumb_id'] = $_POST['thumb_id'];
			  $data['content'] = $_POST['content'];
			  $data['token'] = $this->token;
		      $data['url'] = $_POST['url'];
			  if(!$id){
			    $res = M("pic_material")->add($data);
			  }
			  else{
			  
			  $res = M("pic_material")->where($map)->save($data);
			  }
			 // die( M("pic_material")->getlastsql());
			  if($res){
			     $this->success("操作成功","/index.php?s=/Message/imglist");
			  
			  }
			  else{
			  
			   $this->error("操作失败");
			  }
		     return;
		  }
         $map['token'] = $this->token;
		 $map['id'] = $_GET['id'];
	     $list = M('pic_material')->where($map)->find();
	   $this->assign("set",$list);
        $this->display();

	}
	function img_gmsg_group($gid,$thumb_id){
		$access_info = $this->get_access_token();
	 $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$access_info['access_token'];
	     $post_data = '{
		   "filter":{
			  "group_id":'.'"'.$gid.'"
		   },
		   "mpnews":{
			    "media_id":'.'"'.$thumb_id.'"
		   },
			"msgtype":"mpnews"
		}';
		//echo $post_data;
	die($this->curlPost($url,$post_data));
	    return $this->curlPost($url,$post_data);
	
	}
	function text_gmsg($openid,$content){
	  $access_info = $this->get_access_token();
	  $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$access_info['access_token'];
      $ids = '';
	  if(is_array($openid)) {
				 foreach($openid as $v){				 
					 $ids.=','.'"'.$v.'"';
				 }
				   $id = substr($ids,1);
			 }
			 else if(is_string($openid)){
			 
			       $id = substr($openid,0);
			 
			 }
	   /* $post_data = '{
          "touser": [
        '.$id.'
	   "text": {"content":'.'"'.$content.'"}
         }
        ';*/
		$post_data = '{
   "touser": [
 '.$id.' ], "msgtype": "text", "text": { "content":'.'"'.$content.'"}

}
';
		$res = $this->curlPost($url,$post_data);
		return $res;
	}
	function img_gmsg($openid,$thumb_id){
	   $access_info = $this->get_access_token();
	  $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$access_info['access_token'];
	   $ids = '';
	  if(is_array($openid)) {
				 foreach($openid as $v){				 
					 $ids.=','.'"'.$v.'"';
				 }
				   $id = substr($ids,1);
			 }
			 else if(is_string($openid)){
			 
			       $id = substr($openid,0);
			 
			 }
	   $post_data = '{
		   "touser":[
			'.$id.'
		   ],
		   "image":{
			     "media_id":'.'"'.$thumb_id.'",
		   },
			"msgtype":"image"
		}';
		  var_dump($post_data);var_dump($this->curlPost($url,$post_data));die;
			return $this->curlPost($url,$post_data);
	
	}
	function send_to_someone(){
		    $thumb_id = $_POST['thumb_id'];
		    $content = $_POST['content'];//文本内容
			$title = $_POST['title'];
			$openid = substr($_POST['openid'],1);
		
			if(!isset($thumb_id) || empty($thumb_id)){
			     
			     if($content==""){
				 
				    $json['errcode'] = 1;
					$json['errmsg'] = "文本或者图片内容不能为空";
				     exit(json_encode($json));	  
				 }
				 exit($this->text_gmsg($openid,$content));
			}
			else if(!isset($content) || empty($content)){
			  
			 if($thumb_id==""){
				 
				    $json['errcode'] = 1;
					$json['errmsg'] = "文本或者图片内容不能为空";
				    exit(json_encode($json));
				 }
				 exit($this->img_gmsg($openid,$thumb_id));
			
			}
			if(empty($content) || empty($thumb_id)){
			     
			     if($content==""){
				 
				    $json['errcode'] = 1;
					$json['errmsg'] = "文本或者图片内容不能为空";
					 exit(json_encode($json));
				 
				 }
				 exit($this->text_gmsg($openid,$content));
			}
			$json = $this->openid_group_msg($openid,$content,$thumb_id,$title);
			  $add['msgtype'] = $msgtype;		
					$add['content'] = $json;
					$add['token'] = session('token');
					M("gmsg_log")->add($add);
                    exit($json);			       
		}	
	function openid_group_msg($openid,$content,$thumb_id,$title){
	 $access_info = $this->get_access_token();
	  $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$access_info['access_token'];
	        $post = '{
        "articles": [
		 {
		      "thumb_media_id":'.'"'.$thumb_id.'",
			  "author":"",
			  "title":'.'"'.$title.'",
			  "content_source_url":"",
			 "content":'.'"'.$content.'"
			 "digest":'.'"'.$content.'"
             "show_cover_pic":"1"
		 }
   ]
}';    
            $result = $this->upload_material($post,$access_info);
            if($result['errcode']!=0){
			   exit(json_encode($result));
			
			}		
			$ids = '';
	        if($result['errcode']!=0){
			    exit(json_encode($result));			
			}
			 	if(is_array($openid)) {
				 foreach($openid as $v){				 
					 $ids.=','.'"'.$v.'"';
				 }
				   $id = substr($ids,1);
			 }
			 else if(is_string($openid)){
			 
			       $id = substr($openid,0);
			 
			 }
	          $thumb_media_id = $result["media_id"];
				  $post_data='{
				   "touser":[
					'.$id.'
				   ],
				   "mpnews":{
					   "media_id":'.'"'.$thumb_media_id.'"
				   },
					"msgtype":"mpnews"
				}';
	        $res = $this->curlPost($url,$post_data);
	         return  $res;
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
	//上传图片，以及保存到微信
	function upload_pic(){
           
	       $file =$_SERVER['DOCUMENT_ROOT']."/".$_POST['file'];
		    $access_info = $this->get_access_token();
			//var_dump($access_info);
			$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_info['access_token']."&type=thumb";
			$post_data=array('media'=>"@".$file);
	        $res = $this->curlPost($url,$post_data);
			exit($res);
	}
}
