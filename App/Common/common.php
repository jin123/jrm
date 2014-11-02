<?php
/**
 * 中文截取
 * @param unknown $str
 * @param number $start
 * @param unknown $length
 * @param string $charset
 * @param string $suffix
 * @return string|unknown
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr"))
		return mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		return iconv_substr($str,$start,$length,$charset);
	}
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));
	if($suffix) return $slice."…";
	return $slice;
}

/*
 * 生成图形XML数据源文件
 *  @param array $data 数据
 * @param string $xml_name 文件名
 * @return string 路径
 */
function to_chartxml($data,$xml_name){
	if(!is_array($data)) return false;
    $arr=array('chart'=>$data);
    $xml=to_chartxml_sub($arr);
    $xml_path=DATA_PATH.$xml_name.'.xml';
    file_put_contents($xml_path,$xml);
    return $xml_path;
}

function to_chartxml_sub($data){
    if(!is_array($data)) return false;
    $xml_str='';
    foreach ($data as $key=>$value) {
    	if($value['_single']){    
    		foreach ($value as $single_k=>$single_v){     		      		  
		      if(substr($single_k,0,1)!="_"){	
		          $xml_str.="<".$key;
		          foreach ($single_v as $pro_k=>$pro_v){
		                  $pro_v=iconv("UTF-8","GB2312",$pro_v);
		                  $xml_str.=" {$pro_k}='$pro_v'";
		          } 
		          $xml_str.=" />\n";
		      }    		 
    		}    		
    	}else{
    	    $xml_str.="<".$key;
    	    if($value['_property']){
    	        foreach ($value['_property'] as $pro_k=>$pro_v){
    	            $pro_v=iconv("UTF-8","GB2312",$pro_v);
    	            $xml_str.=" {$pro_k}='{$pro_v}'";
    	        }
    	    }
    	    $xml_str.=">\n";
    		foreach ($value as $items_k=>$items_v){ 
    		    if(substr($items_k,0,1)!="_"){    		        
    		        $xml_str.=to_chartxml_sub(array("{$items_k}"=>$items_v));    		        
    		    }
    		}
    		$xml_str.="</{$key}>\n";
    	}
    }
    return $xml_str;
}function  get_uid($openid){	  $where['openid'] = $openid;	  return M('shop_login')->where($where)->find();		  	} function get_access_token($token){      $where['token'] = $token;	  $info =  M('access_token')->where($where)->find();		 if(!$info){		      $result = remote_access_token($token);	      $data['ctime'] = time();	      $data['access_token'] = $result['access_token'];	      M('access_token')->add($data);	       return $result;	 	 }	 else{	 	     $time = strtotime('-2 hours');//当前时间十二分钟前	     	     if($time>$info['ctime']){	        $res = remote_access_token($token);			//var_dump($res);	        $data['ctime'] = time();      	        $data['access_token'] = $res['access_token'];	        M('access_token')->where('id='.$info)->save($data);	       return   $res;	     	     }	     else{	     	       return $info;	     	     }		 	 	 }	}	function remote_access_token($token){	 $where['token'] = $token;	  $app_info = M("wxuser")->where($where)->order("id desc")->find();	  $get_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_info['appid']."&secret=".$app_info['appsecret'];     	 $res = curlGet($get_url);	 $result = json_decode($res,true);		 return $result;		}	function curlPost($url,$post_data){		  $ch = curl_init();//初始化curl      curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页      curl_setopt($ch, CURLOPT_HEADER, 0);//设置header      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上      curl_setopt($ch, CURLOPT_POST, 1);//post提交方式       curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);       $data = curl_exec($ch);//运行curl      curl_close($ch);      return $data;//输出结果	}	function curlGet($url){		$ch = curl_init();		$header = "Accept-Charset: utf-8";		curl_setopt($ch, CURLOPT_URL, $url);		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		$temp = curl_exec($ch);		return $temp;	}	//获取用户分组	function sel_group($token){		  $access_info = get_access_token($token);	  $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=".$access_info['access_token'];	  $res = curlGet($url);  	  return json_decode($res,true);	}	//用户所属组名	function user_in_group($token,$openid){	       $access_info = get_access_token($token);	       $url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=".$access_info['access_token'];		    $post_data ='{"openid":'.'"'.$openid.'"'.'}';			//var_dump($post_data);die;	       $res = curlPost($url,$post_data);	        return json_decode($res,true);	}	//移动分组下的用户到另一个分组    function move_user_group($token,$gid,$oid){          $access_info = get_access_token($token);          $post_url="https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=".$access_info['access_token'];         $post_data = '{"openid":'.'"'.$oid.'","to_groupid":'.$gid.'}';                  $res = curlPost($post_url,$post_data);         return $res;        }      function  xml_to_array($xml){			$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";			if(preg_match_all($reg, $xml, $matches)){				$count = count($matches[0]);				for($i = 0; $i < $count; $i++){				$subxml= $matches[2][$i];				$key = $matches[1][$i];					if(preg_match( $reg, $subxml )){						$arr[$key] = xml_to_array( $subxml );					}else{						$arr[$key] = $subxml;					}				}			}			return $arr;		}