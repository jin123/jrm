<?php
class WeixinAction extends Action
{
    private $token;
    private $data = array();
    public function index()
    {
        $this->token = $this->_get('token');
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
        if ('CLICK' == $data['Event']) {
            $data['Content'] = $data['EventKey'];
        }
        if ('voice' == $data['MsgType']) {
            $data['Content'] = $data['Recognition'];
        }
        if ('subscribe' == $data['Event']) {
            //$this->requestdata('follownum');
 
            $data = M('Areply')->where(array(
				'token'=>$this->token
			))->field('home,keyword,content')->find();
 
            if ($data['keyword'] == '首页' || $data['keyword'] == 'home') {
                return $this->shouye();
            }
			if($data['keyword'] == '刮刮卡'){
				return $this->keyword($data['keyword']);
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
                        $url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
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
			$key = $data['Content'];
			$newkey=explode("#",$key);
			$where['module']="Wxscreen";
			$where['token']=$this->token;
			$wx=M('Keyword')->where($where)->order('id desc')->find();
			$wxkey=$wx['keyword'];
			//$newkey=mb_substr($key,0,4,'utf8');
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
				//wxscreen($token,$wecha_id,$info,$pid) 
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

            switch ($key) {
                case '首页':
                case 'home':
                    return $this->home();
                    break;
                case '主页':
                    return $this->home();
                    break;
				case '附近':
					return $this->fujin();
					break;
				case '附近门店':
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
                'wecha_id' => $this->data['FromUserName']
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
        $like['keyword'] = array(
            'like',
            '%' . $key . '%'
        );
		$like['token'] = $this->token;
        $data = M('Keyword')->where($like)->order('id desc')->find();

        if ($data != false) {
            switch ($data['module']) {
                case 'Img':
                    $this->requestdata('imgnum');
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
    function kuaidi($data)
    {
        $data = array_merge($data);
        $str  = file_get_contents('http://www.weinxinma.com/api/index.php?m=Express&a=index&name=' . $data[0] . '&number=' . $data[1]);
        return $str;
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
		return array(
			'请发送您现在的地理位置，点击输入框旁边的“+”号发送位置。 ',
			'text'
		); 
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

    function suanming($name)
    {
        $name = implode('', $name);
        if (empty($name)) {
            return '主人' . $this->my . '提醒您正确的使用方法是[算命+姓名]';
        }
        $data = require_once(CONF_PATH . 'suanming.php');
        $num  = mt_rand(0, 80);
        return $name . "\n" . trim($data[$num]);
    }
    function yinle($name)
    {
        $name = implode('', $name);
        $url  = 'http://httop1.duapp.com/mp3.php?musicName=' . $name;
        $str  = file_get_contents($url);

        $obj  = json_decode($str);
        return array(
            array(
                $name,
                $name,
                $obj->url,
                $obj->url
            ),
            'music'
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
    function tianqi($n)
    {
        $name = implode('', $n);
        $str  = $this->myapi.urlencode($name."天气");
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		return $reply;
    }
    function shouji($n)
    {
        $name = implode('', $n);
        $str  = $this->myapi.urlencode($name);
        $json = json_decode(file_get_contents($str));
		$reply = urldecode($json->content);
		$reply   = str_replace('{br}', "\n", $reply);
		return $reply;
    }
    function shenfenzheng($n)
    {
        $n = implode('', $n);
        if (count($n) > 1) {
            $this->error_msg($n);
            return false;
        }
        ;
        $str1     = file_get_contents('http://www.youdao.com/smartresult-xml/search.s?jsFlag=true&type=id&q=' . $n);
        $array    = explode(':', $str1);
        $array[2] = rtrim($array[4], ",'gender'");
        $str      = trim($array[3], ",'birthday'");
        if ($str !== iconv('UTF-8', 'UTF-8', iconv('UTF-8', 'UTF-8', $str)))
            $str = iconv('GBK', 'UTF-8', $str);
        $str = '【身份证】 ' . $n . "\n" . '【地址】' . $str . "\n 【该身份证主人的生日】" . str_replace("'", '', $array[2]);
        return $str;
    }
    function gongjiao($data)
    {
        $data = array_merge($data);
        if (count($data) != 3) {
            $this->error_msg();
            return false;
        }
        ;
        $json    = file_get_contents("http://www.twototwo.cn/bus/Service.aspx?format=json&action=QueryBusByLine&key=5da453b2-b154-4ef1-8f36-806ee58580f6&zone=" . $data[0] . "&line=" . $data[1]);
        $data    = json_decode($json);
        $xianlu  = $data->Response->Head->XianLu;
        $xdata   = get_object_vars($xianlu->ShouMoBanShiJian);
        $xdata   = $xdata['#cdata-section'];
        $piaojia = get_object_vars($xianlu->PiaoJia);
        $xdata   = $xdata . ' -- ' . $piaojia['#cdata-section'];
        $main    = $data->Response->Main->Item->FangXiang;
        $xianlu  = $main[0]->ZhanDian;
        $str     = "【本公交途经】\n";
        for ($i = 0; $i < count($xianlu); $i++) {
            $str .= "\n" . trim($xianlu[$i]->ZhanDianMingCheng);
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
    function fanyi($name)
    {
        $name = array_merge($name);
        $url  = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=kylV2rmog90fKNbMTuVsL934&q=" . $name[0] . "&from=auto&to=auto";
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json);
        $str  = $json->trans_result;
        if ($str[0]->dst == false)
            return $this->error_msg($name[0]);
        $mp3url = 'http://www.apiwx.com/aaa.php?w=' . $str[0]->dst;
        return array(
            array(
                $str[0]->src,
                $str[0]->dst,
                $mp3url,
                $mp3url
            ),
            'music'
        );
    }
    function caipiao($name)
    {
        $name = array_merge($name);
        $url  = "http://api2.sinaapp.com/search/lottery/?appkey=0020130430&appsecert=fa6095e113cd28fd&reqtype=text&keyword=" . $name[0];
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json, true);
        $str  = $json['text']['content'];
        return $str;
    }
    function mengjian($name)
    {
        $name = array_merge($name);
        if (empty($name))
            return '周公睡着了,无法解此梦,这年头神仙也偷懒';
        $data = M('Dream')->field('content')->where("`title` LIKE '%" . $name[0] . "%'")->find();
        if (empty($data))
            return '周公睡着了,无法解此梦,这年头神仙也偷懒';
        return $data['content'];
    }
    function test($name, $data)
    {
        file_put_contents($name, $data);
    }
    function gupiao($name)
    {
        $name = array_merge($name);
        $url  = "http://api2.sinaapp.com/search/stock/?appkey=0020130430&appsecert=fa6095e113cd28fd&reqtype=text&keyword=" . $name[0];
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json, true);
        $str  = $json['text']['content'];
        return $str;
    }
    function getmp3($data)
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
    }
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
    public function user($action, $keyword = '')
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
    }
    public function requestdata($field)
    {
        $data['year']  = date('Y');
        $data['month'] = date('m');
        $data['day']   = date('d');
        $data['token'] = $this->token;
        $mysql         = M('Requestdata');
        $check         = $mysql->field('id')->where($data)->find();
        if ($check == false) {
            $data['time'] = time();
            $data[$field] = 1;
            $mysql->add($data);
        } else {
            $mysql->where($data)->setInc($field);
        }
    }
    public function api_notice_increment($url, $data)
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
    }
    function httpGetRequest_baike($url)
    {
        $headers = array(
            "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Referer: http://www.baidu.com/"
        );
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);

        if ($output === FALSE) {
            return "cURL Error: " . curl_error($ch);
        }
        return $output;
    }
	
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