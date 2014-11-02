<?php
class WeixinAction extends Action
{
    private $token;
    private $data = array();
    public function index()
    {
	    $this->token = $this->_get('token');
		file_put_contents('333.txt',$this->token);
		$wxuser =M('wxuser')->where(array('token'=>$this->token))->find();
		if(!$wxuser)exit;
		$weixin      = new Wechat($this->token);
        $data        = $weixin->request();
        $this->data  = $weixin->request();
        list($content, $type) = $this->reply($data);
        $weixin->response($content, $type);
    }
  
	private function reply($data)
    {   
		//$keyword==''?$data['EventKey']:$data['Count'];
        if ('CLICK' == $data['Event']) {   
			   //$return = $this->click_replay($data);
			   
			    
			   if(is_string($return)){		   
			      return array($return,'text');
			   }
			   else if(is_array($return)){
			   
			    return array($return,'news');
			   
			   }
			   file_put_contents('1112.txt','1111');
        }
        if ('voice' == $data['MsgType']) {
            
            $data['Content'] = $data['Recognition'];
        }
        if ('subscribe' == $data['Event']) {
            $this->requestdata('follownum');
 		    $from_user = $data['FromUserName'];
 		    $user_where['token'] = $this->token;
 		    $user_where['wecha_id'] = $data['FromUserName'];
 		    
            $info =  M('userinfo')->where($user_where)->find();
            if(!$info){
            	     $access_info = $this->get_access_token();
			         $access_token = $access_info['access_token'];
			         $get_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$from_user;					 
			         $post_data_json = $this->curlGet($get_url);
			         $post_data = json_decode($post_data_json,true);
			         $add_data['token'] = $this->token;
			         $add_data['wecha_id'] = $post_data['openid'];
			         $add_data['wechaname'] = $post_data['nickname'];
			         $add_data['truename']  = $post_data['nickname'];
			         $add_data['sex'] = $post_data['sex'];
			         $add_data['province'] = $post_data['province'];
			         $add_data['country'] = $post_data['country'];
			         $add_data['address'] = $post_data['city'];
			         $add_data['headimgurl']= $post_data['headimgurl'];
			         $add_data['subscribe_time']= $post_data['subscribe_time'];
			         $add_user = M('userinfo')->add($add_data);     
              }
              else{
                 M('userinfo')->where($user_where)->save(array('is_follow'=>1));
            
              }
            $data = M('Areply')->where(array(
				'token'=>$this->token
			))->field('home,keyword,content')->find();
 
            if ($data['keyword'] == '首页' || $data['keyword'] == 'home') {
                return $this->shouye();
            }
            if ($data['home'] == 1) {
                $like['keyword'] = array(
                    'like',
                    '%' . $data['keyword'] . '%'
                );
				$like['token'] = $this->token;
                $back = M('Img')->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
                foreach ($back as $keya => $infot) {
                    if ($infot['url'] != false) {
                        $url = $this->getFuncLink($infot['url']);
                    } else {
                        $url = rtrim(C('site_url'), '/') . U('Wap/Drugs/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
                    }
					if (!(strpos($infot['pic'], 'http') === FALSE)) {
						$infot['pic'] = html_entity_decode($infot['pic']);
					} else {
						$infot['pic'] = C('site_url').$infot['pic'];
					}
					$return[] = array(
                        $infot['title'],
                        $infot['text'],
                        $infot['pic'],
                        $url
                    );
                }
                return array(
                    $return,
                    'news'
                );
            } else {
                return array(
                    $data['content'],
                    'text'
                );
            }
           } elseif ('unsubscribe' == $data['Event']) {
            $this->requestdata('unfollownum');
           } elseif ('LOCATION' == $data['Event']){
			  return '';		
		   }
            if ($this->data['Location_X']) {
            
             
                return $this->map($this->data['Location_X'], $this->data['Location_Y']);
            }
			$key = $data['Content']?$data['Content']:$data['EventKey'];
			//file_put_contents('abc.txt',$key);
			$newkey=explode("#",$key);
			$where['module']="Wxscreen";
			$where['token']=$this->token;
			$wx=M('Keyword')->where($where)->order('id desc')->find();
			$wxkey=$wx['keyword'];
			$word = mb_substr($key,2,220,"UTF-8");
			$ident = mb_substr($key,3,220,"UTF-8");
			$s_key = mb_substr($key,0,2,"UTF-8");
			$ss_key = mb_substr($key,0,3,"UTF-8");
			if($key=="2"){
			     $like['token'] = $this->token;
			     $like['keyword'] = "点击签到";
			    $back = M('Img')->field('id,text,pic,url,title')->limit(1)->order('id desc')->where($like)->select();	
                foreach ($back as $keya => $infot) {
						 $url = rtrim(C('site_url'), '/') . U('Wap/Signscore/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
              
					if(!strpos($infot['pic'],C('site_url'))){
					     $infot['pic'] = C('site_url').$infot['pic'];
					
					}
					$return[] = array(
					   $infot['title'],
                        $infot['info'],
                        $infot['pic'],
                        $url
                    );
			      }
				  return array($return,'news');
			}
			if(strpos($key,'公交')){
			
			    $res = $this->gongjiao($key);
			    return array($res,'text');
			} 
			if($s_key=="火车"){
			   $search = array(mb_substr($word,0,2,"UTF-8"),mb_substr($word,2,100,"UTF-8"));
			   $res = $this->huoche($search);
			   return  array($res,'text');
			}
			if($s_key=="翻译"){
			
			    $res= $this->translate_word($word);
			    $res = json_decode($res,true);
			      return array($res['web'][0]['value'][0],'text');
			
			}
			if($s_key=="周边"){
			 
                 $res = $this->surround_arr($word);
                 return array($res,'text');
			 
			 }
           if($s_key=="音乐"){
               
              $data = $this->getMusicInfo($word);
              
             
			  return array(
                  array(
                      $data['Title'],
                      $data['Title'],
                      $data['MusicUrl'],
                      $data['MusicUrl']
                 ),
                 'music'
                );
            
            }
			 if($ss_key=="身份证"){
             
			     if(!$ident){
			     
			       return array('身份证查询　身份证＋号码　　例：身份证342423198803015568','text');
			     
			     }
			     $result = $this->ident_search($ident);
			     
			     $result = json_decode($result,true);
			     if($result['success']=="1"){
			     
			        return array("【身份证信息】\n 年份：".$result['result']['born']." \n 地址:".$result['result']['att']."\n 性别：".$result['result']['sex'],'text');
			     
			     }
			 }
			 if($s_key=="手机"){

			    $word = mb_substr($key,2,220,"UTF-8");
			    if(!word){
			    
			         return array('手机归属地查询(吉凶 运势) 手机＋手机号码　例：手机13917778912','text');
			    
			    }
			    $result = $this->phone_addr($word);
			     $result = json_decode($result,true);
			    if($result['success']==1){
			       $tell_info ="【手机归属地】\n ".$result['result']['att'].$result['result']['ctype'];
			    
			        return array($tell_info,'text');
			    }
			 }
			 
			
			if(strpos($key,'天气')>0){
			    
			     $res = $this->getWeatherInfo($key);
			     foreach($res as $k=>$v){
			          $return[] = array(
			           $v['Title'],
			           $v['Description'],
			           $v['PicUrl'],
			           ''
			          
			          );
			     
			     }
			return array($return,'news');
			}
			if($newkey[0]==$wxkey &&$newkey[1]!=''){
				
				$token=$this->token;
				$wecha_id=$this->data['FromUserName'];
				$info=$newkey[1];
				$like['keyword'] = array(
					'like',
					'%' . $newkey[0] . '%'
				);
				$like['token'] = $this->token;
				$data = M('Keyword')->where($like)->order('id desc')->find();
				$pid=$data['pid'];
				$blog=$this->wxscreen($token,$wecha_id,$info,$pid);
				if($blog=="1"){//失败
					return array(
						'上墙失败',
						'text'
					);
					
				}else if($blog=="0"){//成功无需审核
					return array(
						'成功上墙',
						'text'
					);
					
				}else if($blog=="2"){
					return array(
						'成功上墙等待审核',
						'text'
					);
				
				}
				else{
					return array(
						'服务器错误',
						'text'
					);
				
				}
			}
             $thisInfo = M('reply_info')->where(array('infotype'=>'map'))->find();

            switch ($key) {
                case '首页':
                case 'home':
                    return $this->home();
                    break;
                case '主页':
                    return $this->home();
                    break;
				case  $thisInfo['keyword']:
					return $this->fujin();
					break;
                case '微相册':
                    return $this->xiangce();
                    break;
                case '相册':
                    return $this->xiangce();
                    break;
                case '会员':
                    return $this->member();
                    break;
                case '会员卡':
                    return $this->member();
                    break;
                case '全景':
                    $pro = M('reply_info')->where(array(
                        'infotype' => 'panorama',
						'token' => $this->token
                    ))->find();
                    if ($pro) {
                        return array(
                            array(
                                array(
                                    $pro['title'],
                                    strip_tags(htmlspecialchars_decode($pro['info'])),
                                    rtrim(C('site_url'), '/') .$pro['picurl'],
                                    C('site_url') . '/index.php?g=Wap&m=Panorama&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                                )
                            ),
                            'news'
                        );
                    } else {
                        return array(
                            array(
                                array(
                                    '360°全景看车看房',
                                    '通过该功能可以实现3D全景看车看房',
                                    rtrim(C('site_url'), '/') . '/Public/images/panorama/360view.jpg',
                                    C('site_url') . '/index.php?g=Wap&m=Panorama&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                                )
                            ),
                            'news'
                        );
                    }
                    break;
                case '微房产':
                    $Estate = M('Estate')->where(array(
						'token'=>$this->token
					))->find();
					if (!(strpos($Estate['cover'], 'http') === FALSE)) {
						$Estate['cover'] = html_entity_decode($Estate['cover']);
					} else {
						$Estate['cover'] = C('site_url').$Estate['cover'];
					}
                    return array(
                        array(
                            array(
                                $Estate['title'],
                                str_replace('&nbsp;', '', strip_tags(htmlspecialchars_decode($Estate['estate_desc']))),
                                $Estate['cover'],
                                C('site_url') . '/index.php?g=Wap&m=Estate&a=index&&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&pid=' . $Estate['id'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
                default:					
                case '商城':
                    $pro = M('reply_info')->where(array(
                        'infotype' => 'Shop',
                        'token' => $this->token
                    ))->find();
					if (!(strpos($pro['picurl'], 'http') === FALSE)) {
						$pro['picurl'] = html_entity_decode($pro['picurl']);
					} else {
						$pro['picurl'] = C('site_url').$pro['picurl'];
					}
                    return array(
                        array(
                            array(
                                $pro['title'],
                                strip_tags(htmlspecialchars_decode($pro['info'])),
                                $pro['picurl'],
                                C('site_url') . '/index.php?g=Wap&m=Product&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'

                    );
                    break;
               case '微医疗':
                    $pro = M('reply_info')->where(array(
                    'infotype' => 'Hospital',
                    'token' => $this->token
                    ))->find();
                    if (!(strpos($pro['picurl'], 'http') === FALSE)) {
                        $pro['picurl'] = html_entity_decode($pro['picurl']);
                    } else {
                        $pro['picurl'] = C('site_url').$pro['picurl'];
                    }
                    return array(
                            array(
                                    array(
                                            $pro['title'],
                                            strip_tags(htmlspecialchars_decode($pro['info'])),
                                            $pro['picurl'],
                                            C('site_url') . '/index.php?g=Wap&m=Medical&a=medical&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                                    )
                            ),
                            'news'
                
                    );
                    break;
                case '微留言':
                    $Liuyan = M('Liuyan_set')->where(array(
                        'token' => $this->token
                    ))->find();
					if (!(strpos($Liuyan['pic'], 'http') === FALSE)) {
						$Liuyan['pic'] = html_entity_decode($Liuyan['pic']);
					} else {
						$Liuyan['pic'] = C('site_url').$Liuyan['pic'];
					}
                    return array(
                        array(
                            array(
                                $Liuyan['tit'],
                                strip_tags(htmlspecialchars_decode($Liuyan['info'])),
                                $Liuyan['pic'],
                                C('site_url') . '/index.php?g=Wap&m=Liuyan&a=index&pid='.$Liuyan['id'].'&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;	
				case'意见反馈':
					$advice = M('Advice_set')->where(array(
                        'token' => $this->token
                    ))->find();
					if (!(strpos($advice['pic'], 'http') === FALSE)) {
						$advice['pic'] = html_entity_decode($advice['pic']);
					} else {
						$advice['pic'] = C('site_url').$advice['pic'];
					}
                    return array(
                        array(
                            array(
                                $advice['tit'],
                                strip_tags(htmlspecialchars_decode($advice['info'])),
                                $advice['pic'],
                                C('site_url') . '/index.php?g=Wap&m=Advice&a=index&pid='.$advice['id'].'&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;	
                case '商城':
                    $pro = M('reply_info')->where(array(
                        'infotype' => 'Shop',
                        'token' => $this->token
                    ))->find();
					if (!(strpos($pro['picurl'], 'http') === FALSE)) {
						$pro['picurl'] = html_entity_decode($pro['picurl']);
					} else {
						$pro['picurl'] = C('site_url').$pro['picurl'];
					}
                    return array(
                        array(
                            array(
                                $pro['title'],
                                strip_tags(htmlspecialchars_decode($pro['info'])),
                                $pro['picurl'],
                                C('site_url') . '/index.php?g=Wap&m=Product&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'

                    );
                    break;					
                default:
                    return $this->keyword($key);
            }
	}
		function surround_arr($name){
	
	        return $this->curlGet("http://api.map.baidu.com/telematics/v3/local?location=116.305145,39.982368&keyWord=%E9%85%92%E5%BA%97&output=json");
	
	
	}
	
	//身份证查询
	
	function ident_search($name){
	
	       return $this->curlGet("http://api.k780.com:88/?app=idcard.get&idcard=".$name."&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json");
	}
	function translate_word($name){

	   return $this->curlGet('http://fanyi.youdao.com/openapi.do?keyfrom=jinwebdev&key=1955800048&type=data&doctype=json&version=1.1&q='.urlencode($name));
	}
	//手机归属地
	
	function phone_addr($tell){
             $appkey='101312';
             $sign='a9188406bf366b55d58c97b920814f6e2';
            return $this->curlGet("http://api.k780.com:88/?app=phone.get&phone=".$tell."&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json&");
    }
	//天气查询
	function getWeatherInfo($cityName)
  {
    if ($cityName == "" || (strstr($cityName, "+"))){
        return array('发送天气+城市，例如"天气深圳"','text');
    }
    $url = "http://api.map.baidu.com/telematics/v3/weather?location=".$cityName."&output=json&ak=h9IoXRemuwwuLDmZUe4Kqgvr";
    $output = $this->curlGet($url);
    $result = json_decode($output, true);  
    if ($result["error"] != 0){
        return $result["status"];
    }
    $curHour = (int)date('H',time());
    $weather = $result["results"][0];
    $weatherArray[] = array("Title" =>$weather['currentCity']."预报", "Description" =>"", "PicUrl" =>"", "Url" =>"");
    for ($i = 0; $i < count($weather["weather_data"]); $i++) {
        $weatherArray[] = array("Title"=>
            $weather["weather_data"][$i]["date"]."\n".
            $weather["weather_data"][$i]["weather"]." ".
            $weather["weather_data"][$i]["wind"]." ".
            $weather["weather_data"][$i]["temperature"],
        "Description"=>"", 
        "PicUrl"=>(($curHour >= 6) && ($curHour < 18))?$weather["weather_data"][$i]["dayPictureUrl"]:$weather["weather_data"][$i]["nightPictureUrl"], "Url"=>"");
    }
    return $weatherArray;
   } 
	public function wxscreen($token,$wecha_id,$info,$pid)
    {

			$token = $token;
			$wecha_id=$wecha_id;
			//file_put_contents('blog.txt',$pid);
			$api=M('Diymen_set')->where(array('token'=>$token))->find();
			//file_put_contents('api.txt',$api);
			$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$json=json_decode($this->curlGet($url_get));
			$access_token = $json->access_token;
			$openid =$wecha_id;
			$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
			$json2=json_decode($this->curlGet($url));
			$data['info']=$info;
			$data['name']=$json2->nickname;
			$data['headimg']=$json2->headimgurl;//用户头像
			$data['sex']=$json2->sex;
			$data['city']=$json2->city;//用户所在城市
			$data['province']=$json2->province;//用户所在省份
			$data['country']=$json2->country;//用户所在国家
			$data['wxid']=$wecha_id;
			$data['time']=time();
			$data['pid']=$pid;
			$issh = M('Wxscreen_set')->getField('issh');
			if($issh==1){
				$isval = 0;
			}else{
				$isval = 1;
			}
			$data['isval']=$isval;
			$blog=M('Wxscreen')->add($data); 
			if($blog===false){
				return "1";//上墙失败
			}else{
				if($isval==1){
					return "0";//上墙成功
				}else{
					return "2";//上墙成功等待审核
				}
				
			}			
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
    function xiangce()
    {
        $photo           = M('Photo')->where(array(
		'status' => 1 , 'token'=>$this->token
		))->find();
        $data['title']   = $photo['title'];
        $data['keyword'] = $photo['info'];
        $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Photo/index', array(
		'wecha_id' => $this->data['FromUserName'],'token'=>$this->token
		));
        
		$data['picurl']  = $photo['picurl'] ? $photo['picurl'] : rtrim(C('site_url'), '/') . '/tpl/static/images/yj.jpg';
        return array(
            array(
                array(
                    $data['title'],
                    $data['keyword'],
                    $data['picurl'],
                    $data['url']
                )
            ),
            'news'
        );
    }
	
    function member()
    {
        $card     = M('member_card_create')->where(array(
            'wecha_id' => $this->data['FromUserName'],
			'token' => $this->token
        ))->find();
		
        $cardInfo = M('member_card_set')->where(array(
			'token'=>$this->token
		))->find();
        if ($card == false) {
            $data['picurl']  = rtrim(C('site_url'), '/') . '/Public/static/images/member.jpg';
            $data['title']   = '会员卡,省钱，打折,促销，优先知道,有奖励哦';
            $data['keyword'] = '尊贵vip，是您消费身份的体现,会员卡,省钱，打折,促销，优先知道,有奖励哦';
            $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Card/get_card', array(
                'token' => $this->token,
                'wecha_id' => $this->data['FromUserName'],
				'cardid' =>$cardInfo['id']
            ));
        } else {
            $data['picurl']  = rtrim(C('site_url'), '/') . '/Public/static/images/vip.jpg';
            $data['title']   = $cardInfo['cardname'];
            $data['keyword'] = $cardInfo['msg'];
            $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Card/vip', array(
                'token' => $this->token,
                'wecha_id' => $this->data['FromUserName'],
				'cardid' =>$cardInfo['id']
            ));
        }
        return array(
            array(
                array(
                    $data['title'],
                    $data['keyword'],
                    $data['picurl'],
                    $data['url']
                )
            ),
            'news'
        );
    }
	
	
    function keyword($key)
    {	
		//$data['EventKey']

        $like['keyword'] = array(
            'like',
            '%' . $key . '%'
        );
		$like['token'] = $this->token;
        $data = M('Keyword')->where($like)->order('id desc')->find();
     //   return array(M('Keyword')->getlastsql(),'text');
        if ($data != false) {
            switch ($data['module']) {
                case 'Img':
                    //$this->requestdata('imgnum');
                    $img_db   = M($data['module']);
                    $back     = $img_db->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
                    $idsWhere = 'id in (';
                    $comma    = '';
                    foreach ($back as $keya => $infot) {
                        $idsWhere .= $comma . $infot['id'];
                        $comma = ',';
                        if ($infot['url'] != false) {
                            if (!(strpos($infot['url'], 'http') === FALSE)) {
                                $url = html_entity_decode($infot['url']);
								$url.='&token='.$this->token.'&wecha_id='.$this->data['FromUserName'].'&openid=fromuserid';
                            } else {
                                $url = $this->getFuncLink($infot['url']);
                            }
                        } else {
                            $url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
                                'token' => $this->token,
                                'id' => $infot['id'],
                                'wecha_id' => $this->data['FromUserName']
                            ));
                        }
						if (!(strpos($infot['pic'], 'http') === FALSE)) {
							$infot['pic'] = html_entity_decode($infot['pic']);
						} else {
							$infot['pic']=C('site_url').$infot['pic'];
						}
                        $return[] = array(
                            $infot['title'],
                            $infot['text'],
                            $infot['pic'],
                            $url
                        );
                    }
                    $idsWhere .= ')';
                    if ($back) {
                        $img_db->where($idsWhere)->setInc('click');
                    }
                    return array(
                        $return,
                        'news'
                    );
                    break;
				case 'Reply_info':
                    $Reply_info = M('Reply_info')->where(array(
                        'id' => $data['pid']
                    ))->find();
                    return array(
                        array(
                            array(
                                $Reply_info['title'],
                                $Reply_info['info'],
                                C('site_url').$Reply_info['picurl'],
                                C('site_url') . '/index.php?g=Wap&m=Drugs&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&rid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
                case 'Host':
                    $host = M('Host')->where(array(
                        'id' => $data['pid']
                    ))->find();
                    return array(
                        array(
                            array(
                                $host['name'],
                                $this->getimgurl($host['info']),
                                $host['ppicurl'],
                                C('site_url') . '/index.php?g=Wap&m=Host&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&hid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
				//2014/4/9*苏荣珍摇一摇
				/****************************/
				 case 'Shake':
                    $shake = M('Shake')->where(array(
                        'id' => $data['pid']
                    ))->find(); 
					$shake['pic'] = $this->getimgurl($shake['pic']);
                    return array(
                        array(
                            array(
                                $shake['name'],
                                $shake['info'],
                                $shake['pic'],
                                C('site_url') . '/index.php?g=Wap&m=Shake&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&shakeid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
				/***************************/
				//2014/4/10*苏荣珍微信墙
				/***************************/
				case 'Wxscreen':
					return array(
						'发送"'.$key.'#您想说的话"即可上墙',
						'text'
					);
					break;	
				/***************************/	
				//2014/4/16*微留言
				/****************************/
				 case 'Liuyan_set':
                    $Liuyan = M('Liuyan_set')->where(array(
                        'id' => $data['pid']
                    ))->find();
					if (!(strpos($Liuyan['pic'], 'http') === FALSE)) {
						$Liuyan['pic'] = html_entity_decode($Liuyan['pic']);
					} else {
						$Liuyan['pic'] = C('site_url').$Liuyan['pic'];
					}
                    return array(
                        array(
                            array(
                                $Liuyan['tit'],
                                strip_tags(htmlspecialchars_decode($Liuyan['info'])),
                                $Liuyan['pic'],
                                C('site_url') . '/index.php?g=Wap&m=Liuyan&a=index&pid='.$Liuyan['id'].'&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
				case 'Yl_hospital':
                    /***************************/
                    //2014/6/6*微医疗
                    /****************************/
                    $this->requestdata('other');
                    $info = M('Yl_hospital')
					->where(array('id' => $data['pid']))
					->field('id,name,content,thumb')
					->find();
                    $info['thumb'] = $this->getimgurl($info['thumb']);
                    return array(
                            array(
                                    array(
                                            $info['name'],
                                            strip_tags(htmlspecialchars_decode($info['contnet'])),
                                            $info['thumb'],
                                            C('site_url') . '/index.php?g=Wap&m=Medical&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&hid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                                    )
                            ),
                            'news'
                    );
                    break;
				case 'jiaoyu_jigou':
                    /***************************/
                    //2014/6/6*微教育
                    /****************************/
                    $this->requestdata('other');
                    $info = M('jiaoyu_jigou')->field('id,name,pic,jianjie')->find($data['pid']);
                    $info['thumb'] = $this->getimgurl($info['pic']);
                    return array(
                            array(
                                    array(
                                            $info['name'],
                                            strip_tags(htmlspecialchars_decode($info['jianjie'])),
                                            $info['thumb'],
                                            C('site_url') . '/index.php?g=Wap&m=Education&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['id'] . '&sgssz=mp.weixin.qq.com'
                                    )
                            ),
                            'news'
                    );
                    break;					
                case 'Estate':
                    $Estate = M('Estate')->where(array(
                        'id' => $data['pid']
                    ))->find();
					if (!(strpos($Estate['cover'], 'http') === FALSE)) {
						$Estate['cover'] = html_entity_decode($Estate['cover']);
					} else {
						$Estate['cover'] = C('site_url').$Estate['cover'];
					}
					if (!(strpos($Estate['house_banner'], 'http') === FALSE)) {
						$Estate['house_banner'] = html_entity_decode($Estate['house_banner']);
					} else {
						$Estate['house_banner'] = C('site_url').$Estate['house_banner'];
					}
					if (!(strpos($Estate['banner'], 'http') === FALSE)) {
						$Estate['banner'] = html_entity_decode($Estate['banner']);
					} else {
						$Estate['banner'] = C('site_url').$Estate['banner'];
					}
                    return array(
                        array(
                            array(
                                $Estate['title'],
                                $Estate['estate_desc'],
                                $Estate['cover'],
                                C('site_url') . '/index.php?g=Wap&m=Estate&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&pid=' . $data['pid'] .  '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '楼盘介绍',
                                $Estate['estate_desc'],
                                $Estate['house_banner'],
                                C('site_url') . '/index.php?g=Wap&m=Estate&a=index&&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&pid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '专家点评',
                                $Estate['estate_desc'],
                                $Estate['cover'],
                                C('site_url') . '/index.php?g=Wap&m=Estate&a=impress&&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&pid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '楼盘3D全景',
                                $Estate['estate_desc'],
                                $Estate['banner'],
                                C('site_url') . '/index.php?g=Wap&m=Panorama&a=item&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $Estate['panorama_id'] . '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '楼盘动态',
                                $Estate['estate_desc'],
                                $Estate['house_banner'],
                                C('site_url') . '/index.php?g=Wap&m=Index&a=lists&classid=' . $data['classify_id'] . '&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&pid=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;					
                case 'Text':
                    $this->requestdata('textnum');
                    $info = M($data['module'])->order('id desc')->find($data['pid']);
                    return array(
                        htmlspecialchars_decode(str_replace('{wechat_id}', $this->data['FromUserName'], $info['text'])),
                        'text'
                    );
                    break;
				case 'Survey':
                    $info = M('Survey')->field('id,name,ms,pic')->find($data['pid']);
                    $info['pic'] = $this->getimgurl($info['pic']);
                    return array(
                            array(
                                    array(
                                            $info['name'],
                                            $info['ms'],
                                            $info['pic'],
                                            C('site_url') . '/index.php?g=Wap&m=Survey&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                                    )
                            ),
                            'news'
                    );
                    break;
				case 'Groupbuy':
                    /***************************/
                    //2014/5/25*微团购
                    /****************************/
                    $info = M('Groupbuy')->field('id,name,ms,thumb')->find($data['pid']);
                    $info['thumb'] = $this->getimgurl($info['thumb']);
                    return array(
                            array(
                                    array(
                                            $info['name'],
                                            $info['ms'],
                                            $info['thumb'],
                                            C('site_url') . '/index.php?g=Wap&m=Groupbuy&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                                    )
                            ),
                            'news'
                    );
                    break;
                case 'Product':
                    $this->requestdata('other');
                    $infos = M('Product')->limit(9)->order('id desc')->where($like)->select();
                    if ($infos) {
                        $return = array();
                        foreach ($infos as $info) {
                            $return[] = array(
                                $info['name'],
                                strip_tags(htmlspecialchars_decode($info['intro'])),
                                $info['logourl'],
                                C('site_url') . '/index.php?g=Wap&m=Product&a=product&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $info['id'] . '&sgssz=mp.weixin.qq.com'
                            );
                        }
                    }
                    return array(
                        $return,
                        'news'
                    );
                    break;
                case 'Selfform':
                    $this->requestdata('other');
                    $pro = M('Selfform')->where(array(
                        'id' => $data['pid']
                    ))->find();
                    return array(
                        array(
                            array(
                                $pro['name'],
                                strip_tags(htmlspecialchars_decode($pro['intro'])),
                                $pro['logourl'],
                                C('site_url') . '/index.php?g=Wap&m=Selfform&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
                case 'Panorama':
                    $pro = M('Panorama')->where(array(
                        'id' => $data['pid']
                    ))->find();
					if (!(strpos($pro['frontpic'], 'http') === FALSE)) {
						$pro['frontpic'] = html_entity_decode($pro['frontpic']);
					} else {
						$pro['frontpic']=C('site_url').$pro['frontpic'];
					}
					return array(
                        array(
                            array(
                                $pro['name'],
                                strip_tags(htmlspecialchars_decode($pro['intro'])),
                                $pro['frontpic'],
                                C('site_url') . '/index.php?g=Wap&m=Panorama&a=item&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;
               case 'Wedding':
                    $this->requestdata('other');
                    $wedding = M('Wedding')->where(array(
                        'id' => $data['pid']
                    ))->find();
                    return array(
                        array(
                            array(
                                $wedding['title'],
                                strip_tags(htmlspecialchars_decode($wedding['word'])),
                                $wedding['coverurl'],
                                C('site_url') . '/index.php?g=Wap&m=Wedding&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '查看我的祝福',
                                strip_tags(htmlspecialchars_decode($wedding['word'])),
                                $wedding['picurl'],
                                C('site_url') . '/index.php?g=Wap&m=Wedding&a=check&type=1&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            ),
                            array(
                                '查看我的来宾',
                                strip_tags(htmlspecialchars_decode($wedding['word'])),
                                $wedding['picurl'],
                                C('site_url') . '/index.php?g=Wap&m=Wedding&a=check&type=2&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'] . '&sgssz=mp.weixin.qq.com'
                            )
                        ),
                        'news'
                    );
                    break;					
                case 'Vote':
                    $this->requestdata('imgnum');
                    $img_db   = M($data['module']);
                    $back     = $img_db->field('id,picurl,title,info')->limit(9)->order('id desc')->where($like)->select();
                    $idsWhere = 'id in (';
                    $comma    = '';
                    foreach ($back as $keya => $infot) {
                        $idsWhere .= $comma . $infot['id'];
                        $comma = ',';
                            $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Vote&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $infot['id'] . '&sgssz=mp.weixin.qq.com';
					
						if (!(strpos($infot['picurl'], 'http') === FALSE)) {
							$infot['picurl'] = html_entity_decode($infot['picurl']);
						} else {
							$infot['picurl']=C('site_url').$infot['picurl'];
						}
                        $return[] = array(
                            $infot['title'],
                            $infot['info'],
                            $infot['picurl'],
                            $url
                        );
                    }
                    $idsWhere .= ')';

                    return array(
                        $return,
                        'news'
                    );
                    break;
                case 'Lottery':
                    $this->requestdata('other');
                    $info = M('Lottery')->find($data['pid']);
                    if ($info == false || $info['status'] == 3) {
                        return array(
                            '活动可能已经结束或者被删除了',
                            'text'
                        );
                    }
                    switch ($info['type']) {
                        case 1:
                            $model = 'Lottery';
                            break;
                        case 2:
                            $model = 'Guajiang';
                            break;
                        case 3:
                            $model = 'Coupon';
                    }
                    $id   = $info['id'];
                    $type = $info['type'];
                    if ($info['status'] == 1) {
                        $picurl = $info['starpicurl'];
                        $title  = $info['title'];
                        $id     = $info['id'];
                        $info   = $info['info'];
                    } else {
                        $picurl = $info['endpicurl'];
                        $title  = $info['endtite'];
                        $info   = $info['endinfo'];
                    }
                    $url = C('site_url') . U('Wap/' . $model . '/index', array(
                        'token' => $this->token,
                        'type' => $type,
                        'wecha_id' => $this->data['FromUserName'],
                        'id' => $id,
                        'type' => $type
                    ));
					if (!(strpos($picurl, 'http') === FALSE)) {
						$picurl = html_entity_decode($picurl);
					} else {
						$picurl = C('site_url').$picurl;
					}
                    return array(
                        array(
                            array(
                                $title,
                                $info,
                                $picurl,
                                $url
                            )
                        ),
                        'news'
                    );
                default:
                
                   
                    $this->requestdata('videonum');
                    $info = M($data['module'])->order('id desc')->find($data['pid']);
                  //  return array(M($data['module'])->getlastsql(),'text')
                    return array(
                        array(
                            $info['title'],
                            $info['keyword'],
                            $info['musicurl'],
                            $info['hqmusicurl']
                        ),
                        'music'
                    );
            }
        } else {
            if (!strpos($this->fun, 'liaotian')) {
                $other = M('Other')->where(array(
                    'token' => $this->token
                ))->find();
                if ($other == false) {
                    return array(
                        //'回复帮助，可了解所有功能',
                        'text'
                    );
                } else {
                    if (empty($other['keyword'])) {
                        return array(
                            $other['info'],
                            'text'
                        );
                    } else {
                        $img = M('Img')->field('id,text,pic,url,title')->limit(5)->order('id desc')->where(array(
                            'token' => $this->token,
                            'keyword' => array(
                                'like',
                                '%' . $other['keyword'] . '%'
                            )
                        ))->select();
                        if ($img == false) {
                            return array(
                                '无此图文信息,请提醒商家，重新设定关键词',
                                'text'
                            );
                        }
                        foreach ($img as $keya => $infot) {
                            if ($infot['url'] != false) {
                                if (!(strpos($infot['url'], 'http') === FALSE)) {
                                    $url = html_entity_decode($infot['url']);
                                } else {
                                    $url = $this->getFuncLink($infot['url']);
                                }
                            } else {
                                $url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
                                    'token' => $this->token,
                                    'id' => $infot['id'],
                                    'wecha_id' => $this->data['FromUserName']
                                ));
                            }
                            $return[] = array(
                                $infot['title'],
                                $infot['text'],
                                $infot['pic'],
                                $url
                            );
                        }
                        return array(
                            $return,
                            'news'
                        );
                    }
                }
            }
            return array(
                $this->chat($key),
                'text'
            );
        }
    }
    function getFuncLink($u)
    {
        $urlInfos = explode(' ', $u);
        switch ($urlInfos[0]) {
            default:
                $url = str_replace('{wechat_id}', $this->data['FromUserName'], $urlInfos[0]);
                break;
            case '刮刮卡':
                $Lottery = M('Lottery')->where(array(
                    'token' => $this->token,
                    'type' => 2,
                    'status' => 1

                ))->order('id DESC')->find();
                $url     = C('site_url') . U('Wap/Guajiang/index', array(
                    'token' => $this->token,
                    'wecha_id' => $this->data['FromUserName'],
                    'id' => $Lottery['id']
                ));
                break;
            case '大转盘':
                $Lottery = M('Lottery')->where(array(
                    'token' => $this->token,
                    'type' => 1,
                    'status' => 1
                ))->order('id DESC')->find();
                $url     = C('site_url') . U('Wap/Lottery/index', array(
                    'token' => $this->token,
                    'wecha_id' => $this->data['FromUserName'],
                    'id' => $Lottery['id']
                ));
                break;
            case '商家订单':
                $url = C('site_url') . '/index.php?g=Wap&m=Host&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&hid=' . $urlInfos[1] . '&sgssz=mp.weixin.qq.com';
                break;
            case '优惠券':
                $Lottery = M('Lottery')->where(array(
                    'token' => $this->token,
                    'type' => 3,
                    'status' => 1
                ))->order('id DESC')->find();
                $url     = C('site_url') . U('Wap/Coupon/index', array(
                    'token' => $this->token,
                    'wecha_id' => $this->data['FromUserName'],
                    'id' => $Lottery['id']
                ));
                break;
            case '万能表单':
                $url = C('site_url') . U('Wap/Selfform/index', array(
                    'token' => $this->token,
                    'wecha_id' => $this->data['FromUserName'],
                    'id' => $urlInfos[1]
                ));
                break;
            case '会员卡':
                $url = C('site_url') . U('Wap/Card/vip', array(
                    'token' => $this->token,
                    'wecha_id' => $this->data['FromUserName']
                ));
                break;
            case '首页':
                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Index&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'];
                break;
            case '团购':


                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Groupon&a=grouponIndex&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'];
                break;
            case '商城':
                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Product&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'];
                break;
            case '订餐':
                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Product&a=dining&dining=1&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'];
                break;
            case '相册':
                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Photo&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'];
                break;
        }
        return $url;
    }
    function home()
    {
        return $this->shouye();
    }
    function shouye($name)
    {
        $home = M('Home')->where(array(
			'token'=>$this->token
		))->find();
        if ($home == false) {
            return array(
                '商家未做首页配置，请稍后再试!',
                'text'
            );
        } else {
            $imgurl = $home['picurl'];
			if (!(strpos($imgurl, 'http') === FALSE)) {
				$imgurl = html_entity_decode($imgurl);
			} else {
				$imgurl = C('site_url').$imgurl;
			}

            if ($home['apiurl'] == false) {
                if (!$home['advancetpl']) {
                    $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Index&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com';
                } else {
                    $url = rtrim(C('site_url'), '/') . '/cms/index.php?token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&sgssz=mp.weixin.qq.com';
                }

            } else {
                $url = $home['apiurl'];
            }
        }
        return array(
            array(
                array(
                    $home['title'],
                    $home['info'],
                    $imgurl,
                    $url
                )
            ),
            'news'
        );
    }
    function langdu($data)
    {
        $data   = implode('', $data);
        $mp3url = 'http://www.apiwx.com/aaa.php?w=' . urlencode($data);
        return array(
            array(
                $data,
                '点听收听',
                $mp3url,
                $mp3url
            ),
            'music'
        );
    }
    function jiankang($data)
    {
        if (empty($data))
            return '主人，' . $this->my . "提醒您\n正确的查询方式是:\n健康+身高,+体重\n例如：健康170,65";
        $height  = $data[1] / 100;
        $weight  = $data[2];
        $Broca   = ($height * 100 - 80) * 0.7;
        $kaluli  = 66 + 13.7 * $weight + 5 * $height * 100 - 6.8 * 25;
        $chao    = $weight - $Broca;
        $zhibiao = $chao * 0.1;
        $res     = round($weight / ($height * $height), 1);
        if ($res < 18.5) {
            $info = '您的体形属于骨感型，需要增加体重' . $chao . '公斤哦!';
            $pic  = 1;
        } elseif ($res < 24) {
            $info = '您的体形属于圆滑型的身材，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 24) {
            $info = '您的体形属于肥胖型，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 28) {
            $info = '您的体形属于严重肥胖，请加强锻炼，或者使用我们推荐的减肥方案进行减肥';
        }
        return $info;
    }
    function fujin()
    {
		$infotype = "map";
		$where['token'] = $this->token;
		$reply_info = M('reply_info')->where(array('infotype'=>$infotype,'token'=>$this->token))->find();
		
		$url = trim(C('site_url'), '/') . U('Wap/Map/getlist', array(
                            'token' => $this->token,
                            'wecha_id' => $this->data['FromUserName']
                        ));
		$return[0] = array(
                        $reply_info['title'],
                        $reply_info['intro'],
                        "http://".$_SERVER['HTTP_HOST'].$reply_info['picurl'],
                        $url
       );  
		$back = M('company')->where($where)->order("id desc")->select();               
                if(!$back){               
                    $return = array('商家没有配置门店图文','text');
                }
	            foreach ($back as $keya => $infot){ 
                        $url = rtrim(C('site_url'), '/') . U('Wap/Map/getlist', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
                    $keya = $keya+1;
					$return[$keya] = array(
                        $infot['shortname'],
                        $infot['intro'],
                        "http://".$_SERVER['HTTP_HOST'].$infot['logourl'],
                        $url
                    );  
	            }	         
	            return array($return,'news');
    }
    function recordLastRequest($key, $msgtype = 'text')
    {
        $rdata              = array();
        $rdata['time']      = time();
        $rdata['token']     = $this->_get('token');
        $rdata['keyword']   = $key;
        $rdata['msgtype']   = $msgtype;
        $rdata['uid']       = $this->data['FromUserName'];
        $user_request_model = M('User_request');
        $user_request_row   = $user_request_model->where(array(
            'token' => $this->_get('token'),
            'msgtype' => $msgtype,
            'uid' => $rdata['uid']
        ))->find();
        if (!$user_request_row) {
            $user_request_model->add($rdata);
        } else {
            $rid['id'] = $user_request_row['id'];
            $user_request_model->where($rid)->save($rdata);
        }
    }
    function map($x, $y)
    {
		$map_db   = M('company');
	
		$back     = $map_db->field('id,name,address,logourl')->where(array(
		'token'=>$this->token
		
		))->limit(9)->order('id asc')->select();
		$idsWhere = 'id in (';
		$comma    = '';
		foreach ($back as $keya => $infot) {
		$idsWhere .= $comma . $infot['id'];
		$comma = ',';
		$url = rtrim(C('site_url'),'/').'/index.php?g=Wap&m=Map&a=index&token='.$this->token.'&wecha_id='.$this->data['FromUserName'].'&id='.$infot['id'].'&lat='.$x.'&lng='.$y.'&sgssz=mp.weixin.qq.com';
		if (!(strpos($infot['logourl'], 'http') === FALSE)) {
			$infot['logourl'] = html_entity_decode($infot['logourl']);
		} else {
			$infot['logourl'] = C('site_url').$infot['logourl'];
		}
		$return[] = array(
		$infot['name'],
		$infot['address'],
		$infot['logourl'],
		$url
		);
		}
		$idsWhere .= ')';
		
		return array(
		$return,
		'news'
		);
    }
    function geci($n)
    {
        $name = implode('', $n);
        $str  = $this->myapi.urlencode($name);
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		$reply   = str_replace('{br}', "\n", $reply);
		return $reply;
    }
    function yuming($n)
    {
        $name = implode('', $n);
       $str  = $this->myapi.urlencode($name);
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		return $reply;
    }
    function gongjiao($data)
    {
     $data = explode('公交',$data);
  
      
       if(empty($data)){
  	      echo '公交查询　公交＋城市＋公交编号　例：上海公交774';
       }

	  $vipurl='http://www.twototwo.cn/bus/Service.aspx?format=json&action=QueryBusByLine&key=5da453b2-b154-4ef1-8f36-806ee58580f6&zone='.$data[0].'&line='.$data[1];
      $json = $this->curlGet($vipurl);
		$data=json_decode($json);
		//线路名
		$xianlu=$data->Response->Head->XianLu;
		//验证查询是否正确
		$xdata=get_object_vars($xianlu->ShouMoBanShiJian);
		$xdata=$xdata['#cdata-section'];
		$piaojia=get_object_vars($xianlu->PiaoJia);
		$xdata=$xdata.' -- '.$piaojia['#cdata-section'];		
		$main=$data->Response->Main->Item->FangXiang;
		//线路-路经
		$xianlu=$main[0]->ZhanDian;
		$str=$xdata."\n";
		$str.="【本交公途经】\n";
		if(count($xianlu)==0){
		
		    return "未找到您搜索的公交路线";
		
		}
		for($i=0;$i<count($xianlu);$i++){
			$str.="\n".trim($xianlu[$i]->ZhanDianMingCheng);
		}

       return $str;
    }
    function huoche($data, $time = '')
    {
        $data    = array_merge($data);
        $data[2] = date('Y', time()) . $time;
        if (count($data) != 3) {
            $this->error_msg($data[0] . '至' . $data[1]);
            return false;
        }
        ;
        $time = empty($time) ? date('Y-m-d', time()) : date('Y-', time()) . $time;
        $json = file_get_contents("http://www.twototwo.cn/train/Service.aspx?format=json&action=QueryTrainScheduleByTwoStation&key=5da453b2-b154-4ef1-8f36-806ee58580f6&startStation=" . $data[0] . "&arriveStation=" . $data[1] . "&startDate=" . $data[2] . "&ignoreStartDate=0&like=1&more=0");
        if ($json) {
            $data = json_decode($json);
            $main = $data->Response->Main->Item;
            if (count($main) > 10) {
                $conunt = 10;
            } else {
                $conunt = count($main);
            }
            for ($i = 0; $i < $conunt; $i++) {
                $str .= "\n 【编号】" . $main[$i]->CheCiMingCheng . "\n 【类型】" . $main[$i]->CheXingMingCheng . "\n【发车时间】:　" . $time . ' ' . $main[$i]->FaShi . "\n【耗时】" . $main[$i]->LiShi . ' 小时';
                $str .= "\n----------------------";
            }
        } else {
            $str = '没有找到 ' . $name . ' 至 ' . $toname . ' 的列车';
        }
        return $str;
    }
 
    function getMusicInfo($entity){


   if($entity==""){
			$music="你还没告诉我音乐名称呢";
	   }else{
			$url="http://box.zhangmen.baidu.com/x?op=12&count=1&title=".$entity."$$";
			$ch=curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

			$data=curl_exec($ch);
			$music="没有找到这首歌，换首歌试试吧。";
			try{
				@$menus=simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);
				foreach($menus as $menu){
					if(isset($menu->encode)&& isset($menu->decode)&& !strpos($menu->endode,"baidu.com")&& strpos($menu->decode,".mp3")){
						$result=substr($menu->encode,0,strripos($menu->encode,'/')+1).$menu->decode;
						if(!strpos(result,"?")&& !strpos($result,"xcode")){
							$music=array("Title"=>$entity,"MusicUrl"=>urldecode($result),"HQMusicUrl"=>urldecode($result));
						break;
						}
					}
				}
			}catch(Exception $e){
				}
	   }
		return $music;

}
  

	public function get_access_token(){
	$where['token'] = $this->token;
	 $info =  M('access_token')->where($where)->find();
	 if(!$info){
	      $result = $this->remote_access_token();
	      $data['ctime'] = time();
		  $data['token'] = $this->token;
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
	}
	function remote_access_token(){
	
	
	 $where['token'] = $this->token;
	  $app_info = M("wxuser")->where($where)->order("id desc")->find();
	  $get_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_info['appid']."&secret=".$app_info['appsecret'];     
	  
	 $res = $this->curlGet($get_url);
	 $result = json_decode($res,true);
	
	 return $result;
	
	}
	/*该方法暂时无用*/
	  private function click_key_picword($keyword){
	  
	     $where['token'] = $this->token;
	     $where['keyword'] = $keyword;
	     $data = M('Img')->where($where)->find(); 
	      $return[] = array(
                        $infot['title'],
                        $infot['text'],
                        rtrim(C('site_url'), '/').$infot['pic'],
                        $url
                    ); 
        return  $return;
	  }
	  /*private function click_replay($data){  
        $EventKey = $data['EventKey'];//点击自定义菜单按钮触发的关键词
        
       if($EventKey=="点击签到"){
					$wecha_id = $data['FromUserName'];
					$cardsign   = M('Member_card_sign');  //签到表 
					$where    = array('token'=>$this->token,'wecha_id'=>$wecha_id,'score_type'=>1);  
					$sign = $cardsign->where($where)->order('sign_time desc')->find();
					$today = date('Y-m-d',time());
					 $itoday = date('Y-m-d',$sign['sign_time']); 
					 $token    =  $this->_get('token');
					
					if($itoday == $today){
						return "今天已经签到";
					}      
					 $this->self_sign($data);
					return "签到成功";
		        }
				else if($EventKey=="会员申请"){

			        	$where['token'] = $this->token;
	                 	$where['keyword'] = "会员申请";
			
			           $infot =  M("img")->where($where)->find();
					  $url = rtrim(C('site_url'), '/') . U('Wap/Card/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                        rtrim(C('site_url'), '/').$infot['pic'],
                        $url
                    ); 
                  //  $return = $this->click_key_picword('会员申请');
                    return $return;
			
				}
				else if($EventKey=="微商城"){
				        $where['token'] = $this->token;
	                 	$where['keyword'] = "微商城";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Drugs/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                    //$return = $this->click_key_picword('微商城');
			     	return $return;			
				}
				else if($EventKey=="大转盘"){
				       $where['token'] = $this->token;
	                 	$where['keyword'] = "大转盘";		
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Lottery/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                   // $return = $return = $this->click_key_picword('大转盘');
                    return $return;
				}
				else if($EventKey=="优惠券"){
				        $where['token'] = $this->token;
	                 	$where['keyword'] = "优惠券";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Coupon/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                  //  $return = $this->click_key_picword('优惠券');
                    return $return;
				}
				else if($EventKey=="刮刮卡"){
				        $where['token'] = $this->token;
	                 	$where['keyword'] = "刮刮卡";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Guajiang/index', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                               $infot['title'],
                               $infot['text'],
                               rtrim(C('site_url')).$infot['pic'],
                              $url
                      ); 
                      //$return = $this->click_key_picword('刮刮卡');
                    return $return;
				}
				else if($EventKey=="会员卡"){
				        $where['token'] = $this->token;
	                 	$where['keyword'] = "会员卡";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Card/get_card', array(
                            'token' => $this->token,
                            'id' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                   // $return = $this->click_key_picword('会员卡');
                    return $return;
				}
				else if($EventKey=="意见反馈"){
				
				  $where['token'] = $this->token;
	                 	$where['keyword'] = "意见反馈";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Advice/index', array(
                            'token' => $this->token,
                            'pid' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                  //  $return = $this->click_key_picword('意见反馈');
                    return $return;
				
				
				}
				else if($EventKey=="摇一摇"){
				
                     $where['token'] = $this->token;
	                 	$where['keyword'] = "摇一摇";
			
			           $infot =  M("img")->where($where)->find();
					   $url = rtrim(C('site_url'), '/') . U('Wap/Shake/index', array(
                            'token' => $this->token,
                            'pid' => $infot['id'],
                            'wecha_id' => $this->data['FromUserName']
                        ));
					    $return[] = array(
                        $infot['title'],
                        $infot['text'],
                       rtrim(C('site_url')).$infot['pic'],
                        $url
                    ); 
                    return $return;
				
				
				}
    }*/
	private function self_sign($data){

		 $wecha_id = $data['FromUserName'];
		$token    =  $this->token;
        $cardsign   = M('Member_card_sign');  //签到表 
        $where    = array('token'=>$token,'wecha_id'=>$wecha_id,'score_type'=>1);  
        $sign = $cardsign->where($where)->order('sign_time desc')->find();
         
        
        if($sign == null){ //第一次来签到
            $cardsign->add($where);
            $sign = $cardsign->where($where)->order('id desc')->find();
        }
		$get_card=M('member_card_create')->where(array('wecha_id'=>$wecha_id))->find();
       
         $set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
		 $comm = '';
		 foreach($set_exchange as $k=>$v){
		 
		   $comm.=$k."-".$v;
		 
		 }
		
         $whereinfo =  array('token'=>$token,'wecha_id'=>$wecha_id);
            $userinfo = M('userinfo')->where($whereinfo)->find();
           if($userinfo['continuous'] == 6){
                //先添加今天签到积分 22 分
                $data['expense']    =  $set_exchange['everyday'] + $set_exchange['continuation'];
                $data['is_sign'] = 1;
                $data['sign_time']  = time();
                $cardsign->where($where)->save($data);
                //签到总积分 = 原签到总积分 + 今天签到积分；
                //总积分 = 原总积分 + 今天签到积分；
                $da['sign_score']  = $userinfo['sign_score'] + $data['expense'];
                $da['total_score'] = $userinfo['total_score'] + $data['expense'];
                $da['continuous']  = 0; //清空计数器
                M('userinfo')->where($whereinfo)->save($da);  
                $signined = 1;
           }else{
               //exit('还没够 6 天');
                //是否是连续签到，如果不是，清空计数器
               
				   
					   if ((time() - $sign['sign_time']) > 86400 ) {  // 判断时间是否大于24小时
                       //exit('清零计数器，继续签到');
                       $da['continuous']  = 0; //清空计数器
                       M('Userinfo')->where($whereinfo)->save($da);

                        $data['sign_time']  = time();
                        $data['is_sign']    = 1; 
                        $data['score_type'] = 1;
                        $data['token']      = $token;
                        $data['wecha_id']   = $wecha_id;
                        $data['expense']    = $set_exchange['everyday'];
                        $cardsign->where($where)->save($data);
                      
                        $da['total_score'] = $userinfo['total_score'] +  $data['expense'];
                        $da['sign_score']  = $userinfo['sign_score'] + $data['expense'];
                        $da['continuous']  =  1;
                        M('Userinfo')->where($whereinfo)->save($da); 
                         $signined = 1;
                }else{
                    //是连续签到
                   //  exit('是连续签到 ');
                    $data['sign_time']  = time();
                    $data['is_sign']    = 1; 
                    $data['score_type'] = 1;
                    $data['token']      = $token;
                    $data['wecha_id']   = $wecha_id;
                    $data['expense']    = $set_exchange['everyday'];
                    $cardsign->data($data)->add(); 
 
                    $da['total_score'] = $userinfo['total_score'] +  $data['expense'];
                    $da['sign_score']  = $userinfo['sign_score'] + $data['expense'];
                    $da['continuous']  = $userinfo['continuous'] + 1;
                    M('userinfo')->where($whereinfo)->save($da);
                     $signined = 1; 
              }

        } 
           
	}
   /* function getmp3($data)
    {
        $obj            = new getYu();
        $ContentString  = $obj->getGoogleTTS($data);
        $randfilestring = 'mp3/' . time() . '_' . sprintf('%02d', rand(0, 999)) . ".mp3";
        file_put_contents($randfilestring, $ContentString);
        return rtrim(C('site_url'), '/') . $randfilestring;
    }
    function xiaohua()
    {
        $name = implode('', $n);
         $str  = $this->myapi.urlencode("笑话");
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		$reply   = str_replace('{br}', "\n", $reply);
		return $reply;
    }
    function liaotian($name)
    {
        $name = array_merge($name);
        $this->chat($name[0]);
    }
    function chat($name)
    {
        $this->requestdata('textnum');
        $check = $this->user('connectnum');
        if ($check['connectnum'] != 1) {
            return C('connectout');
        }
        if ($name == "你叫什么" || $name == "你是谁") {
            return '主人你可以叫我' . $this->my . '!';
        } elseif ($name == "你父母是谁" || $name == "你爸爸是谁" || $name == "你妈妈是谁") {
            return '主人,' . $this->my . '是您亲生的！';
        } elseif ($name == '糗事') {
            $name = '笑话';
        } elseif ($name == '网站' || $name == '官网' || $name == '网址' || $name == '3g网址') {
            return "【" . C('site_name') . "】\n" . C('site_name') . "\n【" . C('site_name') . "服务宗旨】\n最强大的微信营销系统！!";
        }
        $str  = $this->myapi.urlencode($name);
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		$reply   = str_replace('{br}', "\n", $reply);
        return str_replace('小九', $this->my,$reply);
		//return $str ;
    }*/
    public function fistMe($data)
    {
        if ('event' == $data['MsgType'] && 'subscribe' == $data['Event']) {
            return $this->help();
        }
    }
    public function help()
    {
        $data = M('Areply')->where(array(
            'token' => $this->token
        ))->find();
        return array(
            preg_replace("/(\015\012)|(\015)|(\012)/", "\n", $data['content']),
            'text'
        );
    }
    function error_msg($data)
    {
        return '没有找到' . $data . '相关的数据';
    }
   /* public function user($action, $keyword = '')
    {
        $user      = M('Wxuser')->field('uid')->where(array(
            'token' => $this->token
        ))->find();
        $usersdata = M('Users');
        $dataarray = array(
            'id' => $user['uid']
        );
        $users     = $usersdata->field('gid,diynum,connectnum,activitynum,viptime')->where(array(
            'id' => $user['uid']
        ))->find();
        $group     = M('User_group')->where(array(
            'id' => $users['gid']
        ))->find();
        if ($users['diynum'] < $group['diynum']) {
            $data['diynum'] = 1;
            if ($action == 'diynum') {
                $usersdata->where($dataarray)->setInc('diynum');
            }

        }
        if ($users['connectnum'] < $group['connectnum']) {
            $data['connectnum'] = 1;
            if ($action == 'connectnum') {
                $usersdata->where($dataarray)->setInc('connectnum');
            }
        }
        if ($users['viptime'] > time()) {
            $data['viptime'] = 1;
        }
        return $data;
    }*/
    public function requestdata($field)
    {
        $data['year']  = date('Y');
        $data['month'] = date('m');
        $data['day']   = date('d');
        $data['token'] = $this->token;
        $mysql         = M('Requestdata');
        $check         = $mysql->field('id')->where($data)->find();
		 $check         = $mysql->field('id')->where($data)->find();
        if($field=="unfollownum"){
        
          $data = $this->data;
          M('userinfo')->where('wecha_id='.'"'.$data['FromUserName'].'"')->save(array('is_follow'=>0));
        }
        if ($check == false) {
            $data['time'] = time();
            $data[$field] = 1;
            $mysql->add($data);
        } else {
            $mysql->where($data)->setInc($field);
        }
    }
   /* public function api_notice_increment($url, $data)
    {
        $ch     = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }*/
 
	
	function getimgurl($imgurl)
	{
		if (!(strpos($imgurl, 'http') === FALSE)) {
			return $imgurl = html_entity_decode($imgurl);
		} else {
			return $imgurl=C('site_url').$imgurl;
		}	
	}
	
	
	
}
?>
