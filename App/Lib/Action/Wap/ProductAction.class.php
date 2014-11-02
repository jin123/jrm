<?php
class ProductAction extends Action{
	public $token;
	public $wecha_id;
	public $product_model;
	public $product_cat_model;
	public $isDining;
	private $resHandler;
    private $reqHandler;
	public function __construct(){
		parent::__construct();
		$this->token = $this->_get('token');
		$this->assign('token',$this->token);
		$this->wecha_id	= $this->_get('wecha_id');
		if (!$this->wecha_id){
			//$this->wecha_id='';
		}
		$this->assign('wecha_id',$this->wecha_id);
		
		$this->product_model=M('Product');
		$this->product_cat_model=M('Product_cat');

		//购物车
		$calCartInfo=$this->calCartInfo();
		$this->assign('totalProductCount',$calCartInfo[0]);
		$this->assign('totalProductFee',$calCartInfo[1]);
		
	}
	function  pro(){
	
	       $order_info = M('product_cart')->where(array('id'=>204))->find();
                $info = unserialize($order_info ['info']);
                $text = '';
                foreach($info as $k=>$v){
                     $product_info = M('product')->where(array('id'=>$k))->find();
                     $text.=$product_info['name'].$v['count']."个";
                
                }
                var_dump($text);
	}
	 function init_pay($key){
	    import('@.ORG.pay.Utils');
        import('@.ORG.pay.class.RequestHandler');
        import('@.ORG.pay.class.ClientResponseHandler');
        import('@.ORG.pay.class.PayHttpClient');
        $this->reqHandler = new RequestHandler();
        $this->reqHandler->setKey($key);
    }
    public function api_notice_increment($url, $data)
    {
        $ch= curl_init();
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
	 public function save_userinfo($wecha_id,$data){
             $userinfo = M('userinfo')->where(array('wecha_id'=>$wecha_id))->find();
             if(!$userinfo){           
                $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$openinfo['access_token']."&openid=".$openinfo['openid']."&lang=zh_CN";
                $user= json_decode($this->curl_get($info_url),true);
                $data['wecha_id'] = $wecha_id;
                $data['created'] = time();
                $data['wechaname'] = $data['nickname'];
                $id = M('userinfo')->add($data);
             }
    }
     function curl_get($url){
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
	public function userinfo($get,$token){
	 $code = $get['code'];
	 $wxinfo = M('wxuser')->where(array('token'=>$token))->find();
	 $get_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$wxinfo['appid']."&secret=".$wxinfo['appsecret']."&code=".$code."&grant_type=authorization_code";
	 $get_info = $this->curl_get($get_url);
     $token_info = json_decode($get_info,true);
     $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$token_info['access_token']."&openid=".$token_info['openid']."&lang=zh_CN";
     $userinfo = json_decode($this->curl_get($info_url),true);
     return $userinfo;
	}
	public function index(){
	
	
	      $wxinfo = M('wxuser')->where(array('token'=>$this->token))->find();
          $reurl = urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&reload=1");
          $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wxinfo['appid'].'&redirect_uri='.$reurl.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
          if(!isset($_GET['wecha_id'])){        
             if(!isset($_GET['reload'])){
               header('location:'.$url);
              }
              else{           
                   $openinfo = $this->userinfo($_GET,$this->token);
                    if(isset($_GET['reload']) && $_GET['reload']==1){
                          $this->save_userinfo($openinfo['openid'],$openinfo);
                         $rehref = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&wecha_id=".$openinfo['openid'];    
                         header("location: ".$rehref);       
                    }             
              }                
        }
		if (isset($_GET['catid'])){
			$catid=intval($_GET['catid']);
			$where['catid']=$catid;
			
			$thisCat=$this->product_cat_model->where(array('id'=>$catid,'token'=>$this->token))->find();
			$this->assign('thisCat',$thisCat);
		}
	   if(IS_POST){
			$key = $this->_post('search_name');
            $this->redirect('g=Wap&m=Product&a=index&token='.$this->token.'&keyword='.$key."&wecha_id=".$this->wecha_id);
		}
		if (isset($_GET['keyword'])){
            $where['name|intro|keyword'] = array('like',"%".$_GET['keyword']."%");
          
			$where['token'] = $this->token;
            $this->assign('isSearch',1);
		}
		/*if(isset($_POST['search_name']) && !empty($_POST['search_name'])){
		
		     $key = $this->_post('search_name');
		     $where['name|intro|keyword'] = array('like',"%".$key."%");
		
		}*/
		$where['status'] = 1;
		$count = $this->product_model->where($where)->count();
		$this->assign('count',$count); 
		//排序方式
		$method=isset($_GET['method'])&&($_GET['method']=='DESC'||$_GET['method']=='ASC')?$_GET['method']:'DESC';
		$orders=array('time','discount','price','salecount');
		$order=isset($_GET['order'])&&in_array($_GET['order'],$orders)?$_GET['order']:'time';
		$this->assign('order',$order);
		$this->assign('method',$method);
		$where['token'] = $this->token;
		$products = $this->product_model->where($where)->order($order.' '.$method)->limit('5')->select();
		$this->assign('products',$products);
		$this->assign('metaTitle','微信商城');
		$pic_info = M('flash')->where(array('token'=>$this->token,'type'=>3))->select();
		$this->assign('info',$pic_info);
		unset($method);
		unset($products);
		unset($pic_info);
		$this->display();
	}

	public function products(){
		$catWhere=array('parentid'=>0);
		if (isset($_GET['parentid'])){
			$parentid=intval($_GET['parentid']);
			$catWhere['parentid']=$parentid;
			$catWhere['token']=$this->token;

			$thisCat=$this->product_cat_model->where(array('id'=>$parentid,'token'=>$this->token))->find();
			$this->assign('thisCat',$thisCat);
			$this->assign('parentid',$parentid);
		}
		
		$catWhere['token'] = $this->token;
		$cats = $this->product_cat_model->where($catWhere)->order('id asc')->select();
		$this->assign('cats',$cats);
		$this->display();
	}



	//产品购买页面
	
	public function product(){
	      $wxinfo = M('wxuser')->where(array('token'=>$this->token))->find();
          $reurl = urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&reload=1");
          $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wxinfo['appid'].'&redirect_uri='.$reurl.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
          if(!isset($_GET['wecha_id'])){        
             if(!isset($_GET['reload'])){
               header('location:'.$url);
              }
              else{           
                   $openinfo = $this->userinfo($_GET,$this->token);
                    if(isset($_GET['reload']) && $_GET['reload']==1){
                          $this->save_userinfo($openinfo['openid'],$openinfo);
                         $rehref = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&wecha_id=".$openinfo['openid'];    
                         header("location: ".$rehref);       
                    }             
              }                
        }
		$where=array('token'=>$this->token);
		if (isset($_GET['id'])){
			$id=intval($_GET['id']);
			$where['id']=$id;
			$where['token'] = $this->token;
		}
		$product=$this->product_model->where($where)->find();
		$this->assign('product',$product);

		if ($product['endtime']){
			$leftSeconds=intval($product['endtime']-time());
			$this->assign('leftSeconds',$leftSeconds);
		}
		
		$this->assign('metaTitle',$product['name']);
		$product['intro']=str_replace(array('&lt;','&gt;','&quot;','&amp;nbsp;'),array('<','>','"',' '),$product['intro']);
		$intro=$this->remove_html_tag($product['intro']);
		$intro=mb_substr($intro,0,30,'utf-8');
		$this->assign('intro',$intro);
		//分店信息(自定义lbs回复)
		$company_model=M('Company');
		$branchStoreCount=$company_model->where(array('isbranch'=>1,'token'=>$this->token))->count();
		$this->assign('branchStoreCount',$branchStoreCount);
		//销量最高的商品
		$sameCompanyProductWhere=array('id'=>array('neq',$product['id']),'token'=>$this->token);

		$products=$this->product_model->where($sameCompanyProductWhere)->limit('salecount DESC')->limit('0,5')->select();
		$this->assign('products',$products);
		$this->display();
	}

	function remove_html_tag($str){  //清除HTML代码、空格、回车换行符
		//trim 去掉字串两端的空格
		//strip_tags 删除HTML元素

		$str = trim($str);
		$str = @preg_replace('/<script[^>]*?>(.*?)<\/script>/si', '', $str);
		$str = @preg_replace('/<style[^>]*?>(.*?)<\/style>/si', '', $str);
		$str = @strip_tags($str,"");
		$str = @ereg_replace("\t","",$str);
		$str = @ereg_replace("\r\n","",$str);
		$str = @ereg_replace("\r","",$str);
		$str = @ereg_replace("\n","",$str);
		$str = @ereg_replace(" ","",$str);
		$str = @ereg_replace("&nbsp;","",$str);
		return trim($str);
	}

	//加入购物车
	public function addProductToCart(){//商品id|商品价格|商品数量,
		
		$count=isset($_GET['count'])?intval($_GET['count']):1;
		$carts=$this->_getCart();
		$id=intval($_GET['id']);
		if (key_exists($id,$carts)){
			$carts[$id]['count']++;
		}else {
			$carts[$id]=array('count'=>1,'price'=>floatval($_GET['price']));
		}
		$_SESSION['session_cart_products']=serialize($carts);
		$calCartInfo=$this->calCartInfo();
		echo $calCartInfo[0].'|'.$calCartInfo[1];

	}
	//进入购物车
	public function cart(){
		//读取用户信息	
		$userinfo_model=M('Userinfo');
		$thisUser=$userinfo_model->where(array('wecha_id'=>$this->wecha_id,'token'=>$this->token))->find();
		$this->assign('thisUser',$thisUser);
		
		//是否要支付
		$alipay_config_db=M('Alipay_config');
		$payconfig=$alipay_config_db->where(array('token'=>$this->token,'open'=>1))->select();
		$this->assign('payconfig',$payconfig);

		if (IS_POST){
		    $pay_way = trim($this->_post('pay_way'));
            if($pay_way==""){
               //  $this->error('请选择支付方式');return;
               exit(json_encode(array('status'=>0,'msg'=>'请选择支付方式')));
            
            }
			$row=array();
			$carts=$this->_getCart();
			$allCartInfo=$this->calCartInfo($carts);
			$totalFee=$allCartInfo[1];
			$cartsCount=0;

			$productids=array();
			foreach ($carts as $k=>$c){
				array_push($productids,$k);
			}
			$normalCart=array();
			
			$productsByKey=array();

			$orderName='';
			
			if (count($productids)){
				$products=$this->product_model->where(array('id'=>array('in',$productids)))->select();

				if ($products){
					$t=0;
					foreach ($products as $p){
						$productsByKey[$p['id']]=$p;
						if ($t==0){
							$orderName=$p['name'];
						}
						$t++;
					}
				}

				foreach ($carts as $k=>$c){
					$thisProduct=$productsByKey[$k];
					$normalCart[$k]=$c;
					$cartsCount++;
				}
			}

			$orderid=time();
			$row['orderid']=$orderid;
			$orderid=$row['orderid'];
			$row['truename']=$this->_post('truename');
			$row['tel']=$this->_post('tel');
			$row['address']=$this->_post('address');
			$row['token']=$this->token;
			$row['wecha_id']=$this->wecha_id;
			if (!$row['wecha_id']){
				$row['wecha_id']='null';
			}
			$buytimestamp=$this->_post('buytimestamp');//购买时间
			if ($buytimestamp){
				$row['year']=date('Y',$buytimestamp);
				$row['month']=date('m',$buytimestamp);
				$row['day']=date('d',$buytimestamp);
				$row['hour']=$this->_post('hour');
			}

			$row['time']= time();
			//分别加入3类订单
			$orderids=array();//用于存储插入的各类订单id
			$product_cart_model=M('product_cart');
			if ($cartsCount){
                $calCartInfo=$this->calCartInfo($normalCart);
                $weight = 0;
				if (count($normalCart)){
			     if($_GET['token']=="kwfgat1405930691"){
                  $trans_linke['province'] = array(
                    'like',
                    '%' .$_POST['province']. '%'
                  );
                  
                  //10+（10）*9
                  /*例如：7KG货品按首重20元、续重9元计算，则运费总额为：
　　                 20+（7×2-1）*9=137 （元）*/
                      $transinfo =M('product_transet')->where($trans_linke)->find();
                      if($transinfo){
                         $wxuser = M('wxuser')->where(array('toke'=>$this->token))->find();
                         foreach($normalCart as $k=>$v){                            
                           $product_info = M('product')->where(array('id'=>$k))->find(); 
                            $weight = $weight+ $v['count']*$product_info['weight']/$wxuser['unit_weight'];
                         } 
                         
                          $calCartInfo[1] = ($calCartInfo[1]+$transinfo['first_freight'])+($weight-1)*$transinfo['two_freight'];
                      }                     
                 }
					$row['total']=$calCartInfo[0];
					$row['price']=($calCartInfo[1]);
					$row['transportation_price']=($transinfo['first_freight']+($weight-1)*$transinfo['two_freight']);
					$row['diningtype']=0;
					$row['buytime']='';
					$row['tableid']=0;
					$row['info']=serialize($normalCart);
					$row['groupon']=0;
					$row['dining']=0;
					$row['out_trade_no'] = "istroop_".time();
					$row['pay_way'] = trim($this->_post('pay_way'));
					$normal_rt=$product_cart_model->add($row);
					$orderids['normal']=$normal_rt;
				}
				
			}
             $order_od = $normal_rt;
			if ($normal_rt){
				$product_model=M('product');
				$product_cart_list_model=M('product_cart_list');
				$crow=array();
				if ($cartsCount){
					foreach ($carts as $k=>$c){
						$crow['cartid']=intval($orderids[$c['type']]);
						$crow['productid']=$k;
						$crow['price']=$c['price'];
						$crow['total']=$c['count'];
						$crow['wecha_id']=$row['wecha_id'];
						$crow['token']=$row['token'];
						$crow['time']=$time;
						$product_cart_list_model->add($crow);
						$product_model->where(array('id'=>$k))->setInc('salecount',$c['count']);
					}
				}
				$_SESSION['session_cart_products']='';
				//保存个人信息
				if ($_POST['saveinfo']){
					$userRow=array('tel'=>$row['tel'],'truename'=>$row['truename'],'address'=>$row['address'],'province'=>$row['province']);
					if ($thisUser){
						$userinfo_model->where(array('id'=>$thisUser['id']))->save($userRow);
					}else {
						$userRow['token']=$this->token;
						$userRow['wecha_id']=$this->wecha_id;
						$userRow['wechaname']='';
						$userRow['qq']=0;
						$userRow['sex']=-1;
						$userRow['age']=0;
						$userRow['birthday']='';
						$userRow['info']='';
						$userRow['total_score']=0;
						$userRow['sign_score']=0;
						$userRow['expend_score']=0;
						$userRow['continuous']=0;
						$userRow['add_expend']=0;
						$userRow['add_expend_time']=0;
						$userRow['live_time']=0;
						$userinfo_model->add($userRow);
					}
				}
				
			}
						
			
            $pay_info = M('product_area')->where(array('wecha_id'=>$this->wecha_id))->find();
             $area_info['province'] = $_POST['province'];
             $area_info['city'] = $_POST['city'];
            if(!$pay_info){
                $area_info['wecha_id'] = $this->wecha_id;
                M('product_area')->add($area_info);
            }
            else{
               M('product_area')->where(array('wecha_id'=>$this->wecha_id))->save($area_info);
            }
			if ($pay_way == "支付宝"){
			 exit(json_encode(array('reload'=>1,'status'=>1,'url'=>'/index.php?s=Alipay/pay&token='.$this->token."&wecha_id=".$this->wecha_id."&success=1&>$totalFee=".$totalFee."&orderName=".$orderName."&orderid=".$orderid)));
			 
			 
			}
			else if($pay_way == '财付通'){
		
			  exit(json_encode(array('status'=>0,'msg'=>'该功能暂时没有开通，请尝试其他支付方式')));
			
			}
			else if($pay_way == '微支付'){
			     $order_info = M('product_cart')->where(array('id'=>$order_od))->find();
                $info = unserialize($order_info ['info']);
                $text = '';
                foreach($info as $k=>$v){
                     $product_info = M('product')->where(array('id'=>$k))->find();
                     $text.=" ".$product_info['name'].$v['count']."个";
                
                }
                $order_info['info'] =$text;
			    exit(json_encode(array('reload'=>0,'data'=>$order_info,'status'=>1)));
			 
			
			}
			else if($pay_way == '货到付款'){
			exit(json_encode(array('reload'=>1,'status'=>1,'url'=>'/index.php?s=Wap/Product/my&token='.$this->token."&wecha_id=".$this->wecha_id."&success=1&>$totalFee=".$totalFee."&orderName=".$orderName."&orderid=".$orderid."&pid=".$order_od)));
			 
			}
			else if($pay_way == '在线支付'){
			    exit(json_encode(array('reload'=>1,'status'=>1,'url'=>'/index.php?s=Wap/Product/pay&token='.$this->token."&wecha_id=".$this->wecha_id."&pid=".$order_od)));
			 
			}
			
		}else{
			$totalFee=0;
			$totalCount=0;
			$products=array();
			$ids=array();
			$carts=$this->_getCart();
			foreach ($carts as $k=>$c){
				if (is_array($c)){
					$productid=$k;
					$price=$c['price'];
					$count=$c['count'];
					if (!in_array($productid,$ids)){
						array_push($ids,$productid);
					}
					$totalFee+=$price*$count;
					$totalCount+=$count;
				}
			}
			if (count($ids)){
				$list=$this->product_model->where(array('id'=>array('in',$ids)))->select();
			}
	
			
			if ($list){
				$i=0;
				foreach ($list as $p){
					$list[$i]['count']=$carts[$p['id']]['count'];
					$i++;
				}
			}
			$area_info =  M('area')->where("topno=0")->select();
			$pay_info = M('product_area')->where(array('wecha_id'=>$this->wecha_id))->find();
			$par_info = M('alipay_config')->where(array('token'=>$this->token,'type'=>'3'))->find();
	     	$wxuser = M('wxuser')->where(array('token'=>$this->token))->find();
		    $info = array_merge($par_info,$wxuser);
			$this->assign('wepay_info',$info);
			$this->assign('pay_info',$pay_info);
			$this->assign('area_info',$area_info);
			$this->assign('products',$list);
			$this->assign('totalFee',$totalFee);
			$this->assign('metaTitle','购物车');
			
			$this->display();
		}
	}
	//function pay($data,$token,$wecha_id){
	  function pay(){
	  
	  $pid = $_GET['pid'];
	  $token = $_GET['token'];
	  $wecha_id = $_GET['wecha_id'];
	  $data = M('product_cart')->where(array('id'=>$pid))->find();
	  $pay_info = M('alipay_config')->where(array('token'=>$token,'type'=>4))->find();
	   $this->init_pay($pay_info['key']);
	    $card_info=unserialize($data['info']);
        $productid = key($card_info);
        $product_info = M('product')->where(array('id'=>$productid))->find();
	    $url = "https://pay.swiftpass.cn/pay/gateway";
        $this->reqHandler->setParameter('mch_id',$pay_info['pid']);//必填项，商户号，由威富通分配
        $this->reqHandler->setParameter('service','pay.weixin.jspay');//接口类型：pay.weixin.scancode
        $this->reqHandler->setParameter('body',$product_info['name']);
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'];
        $this->reqHandler->setParameter('notify_url',$notify_url.'/index.php?s=Wap/Product/notify');
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $this->reqHandler->setParameter('out_trade_no',$data['out_trade_no']);      
         $this->reqHandler->setParameter('callback_url',"http://".$_SERVER['HTTP_HOST'].U('/Wap/Product/my/',array('token'=>$token,'wecha_id'=>$wecha_id)));  
        $this->reqHandler->setParameter('mch_create_ip',$_SERVER['REMOTE_ADDR']);
        $this->reqHandler->setParameter('total_fee',intval($product_info['price'])*100);
        
        $this->reqHandler->createSign();//创建签名
        $parameters = $this->reqHandler->getAllParameters();
        $xml = Utils::toXml($parameters);
        $res = $this->api_notice_increment($url,$xml);
       $obj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
       $obj = (array)$obj;
       if($obj['status']!=0){
       
       
          // $this->error('支付发生异常请重新再试');
       }
	   $url = "https://pay.swiftpass.cn/pay/jspay?token_id=".$obj['token_id']."&showwxpaytitle=1";
	   header("location:".$url);
	}
	//购物车商品加量
	public function ajaxUpdateCart(){
		$carts=$this->_getCart();
		$g_id=intval($_GET['id']);
		$g_count=intval($_GET['count']);
		if ($carts){
			foreach ($carts as $k=>$c){
				if ($g_id==$k){
					$carts[$k]['count']=$g_count;
				}
			}
		}
		$_SESSION['session_cart_products']=serialize($carts);
		$calCartInfo=$this->calCartInfo();
		echo $calCartInfo[0].'|'.$calCartInfo[1];
	}
	
	//删除购物车商品
	public function deleteCart(){
		$products=array();
		$ids=array();
		$carts=$this->_getCart();
		foreach ($carts as $k=>$c){
			$i=0;
			if ($c){
				$productid=$k;
				$price=$c['price'];
				$count=$c['count'];
				//
				if ($this->_get('id')!=$productid){
					$products[$productid]=array('price'=>$price,'count'=>$count);
				}
				$i++;
			}
		}
		$_SESSION['session_cart_products']=serialize($products);
		$this->redirect('Product/cart',array('token'=>$_GET['token'],'wecha_id'=>$_GET['wecha_id']));
	}
	
	//购物车商品支付
	public function orderCart(){
		
		//读取用户信息	
		$userinfo_model=M('Userinfo');
		$thisUser=$userinfo_model->where(array('wecha_id'=>$this->wecha_id,'token'=>$this->token))->find();
		$this->assign('thisUser',$thisUser);
		
		//是否要支付
		$alipay_config_db=M('Alipay_config');
		$alipayConfig=$alipay_config_db->where(array('token'=>session('token')))->find();
		$this->assign('alipayConfig',$alipayConfig);
		//
		if (IS_POST){
			$row=array();
			$carts=$this->_getCart();
			//
			$allCartInfo=$this->calCartInfo($carts);
			$totalFee=$allCartInfo[1];
			//
			$cartsCount=0;

			$productids=array();
			foreach ($carts as $k=>$c){
				array_push($productids,$k);
			}

			//
			$normalCart=array();
			$productsByKey=array();

			$orderName='';
			
			if (count($productids)){
				$products=$this->product_model->where(array('id'=>array('in',$productids)))->select();

				if ($products){
					$t=0;
					foreach ($products as $p){
						$productsByKey[$p['id']]=$p;
						if ($t==0){
							$orderName=$p['name'];
						}
						$t++;
					}
				}

				foreach ($carts as $k=>$c){
					$thisProduct=$productsByKey[$k];
					$normalCart[$k]=$c;
					$cartsCount++;
				}
			}

			$orderid=time();
			$row['orderid']=$orderid;
			$orderid=$row['orderid'];
			//
			$row['truename']=$this->_post('truename');
			$row['tel']=$this->_post('tel');
			$row['address']=$this->_post('address');
			$row['token']=$this->token;
			$row['wecha_id']=$this->wecha_id;
			if (!$row['wecha_id']){
				$row['wecha_id']='null';
			}
			//
			$buytimestamp=$this->_post('buytimestamp');//购买时间
			if ($buytimestamp){
				$row['year']=date('Y',$buytimestamp);
				$row['month']=date('m',$buytimestamp);
				$row['day']=date('d',$buytimestamp);
				$row['hour']=$this->_post('hour');
			}

			$row['time'] = time();
			//分别加入3类订单
			$orderids=array();//用于存储插入的各类订单id
			$product_cart_model=M('product_cart');
			if ($cartsCount){

				if (count($normalCart)){
					$calCartInfo=$this->calCartInfo($normalCart);
					$row['total']=$calCartInfo[0];
					$row['price']=$calCartInfo[1];
					//
					$row['diningtype']=0;
					$row['buytime']='';
					$row['tableid']=0;
					$row['info']=serialize($normalCart);
					//
					$row['groupon']=0;
					$row['dining']=0;
					$normal_rt=$product_cart_model->add($row);
					$orderids['normal']=$normal_rt;
				}
				
			}

			if ($normal_rt){
				$product_model=M('product');
				$product_cart_list_model=M('product_cart_list');
				$crow=array();
				if ($cartsCount){
					foreach ($carts as $k=>$c){
						$crow['cartid']=intval($orderids[$c['type']]);
						$crow['productid']=$k;
						$crow['price']=$c['price'];
						$crow['total']=$c['count'];
						$crow['wecha_id']=$row['wecha_id'];
						$crow['token']=$row['token'];
						$crow['time']=$time;
						$product_cart_list_model->add($crow);
						$product_model->where(array('id'=>$k))->setInc('salecount',$c['count']);
					}
				}
				$_SESSION['session_cart_products']='';
				//保存个人信息
				if ($_POST['saveinfo']){
					$userRow=array('tel'=>$row['tel'],'truename'=>$row['truename'],'address'=>$row['address']);
					if ($thisUser){
						$userinfo_model->where(array('id'=>$thisUser['id']))->save($userRow);
					}else {
						$userRow['token']=$this->token;
						$userRow['wecha_id']=$this->wecha_id;
						$userRow['wechaname']='';
						$userRow['qq']=0;
						$userRow['sex']=-1;
						$userRow['age']=0;
						$userRow['birthday']='';
						$userRow['info']='';
						//
						$userRow['total_score']=0;
						$userRow['sign_score']=0;
						$userRow['expend_score']=0;
						$userRow['continuous']=0;
						$userRow['add_expend']=0;
						$userRow['add_expend_time']=0;
						$userRow['live_time']=0;
						$userinfo_model->add($userRow);
					}
				}
				
			}


			// 增加 发送短信
/*$info=M('Wxuser')->where(array('token'=>$this->token))->find();
$phone=$info['phone'];

$user=$info['smsuser'];//短信平台帐号
$pass=md5($info['smspassword']);//短信平台密码
$smsstatus=$info['smsstatus'];//短信平台状态

$content = $this->sms();

if ($smsstatus == 1) {
    if ($content) {
        $smsrs = file_get_contents('http://api.smsbao.com/sms?u='.$user.'&p='.$pass.'&m='.$phone.'&c='.urlencode($content));
        //$log = file_get_contents('http://www.test.com/test.php?u=' . $user . '&p=' . $pass . '&m=' . $phone . '&test=' . urlencode($content));
    }
}
*/
// 结束

// 增加 发送邮件
/*
$email=$info['email'];
$emailuser=$info['emailuser'];
$emailpassword=$info['emailpassword'];
$emailstatus=$info['emailstatus'];

if ($emailstatus == 1) {
    if ($content) {
        date_default_timezone_set('PRC');
        require_once 'class.phpmailer.php';
        //include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded
        $mail = new PHPMailer();
        $body = $content;
        $mail->IsSMTP();
        // telling the class to use SMTP
        $mail->Host = 'smtp.qq.com';
        // SMTP server
        $mail->SMTPDebug = '1';
        // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth = true;
        // enable SMTP authentication
        $mail->Host = 'smtp.qq.com';
        // sets the SMTP server
        $mail->Port = 25;
        // set the SMTP port for the GMAIL server
        $mail->Username = $emailuser;
        // SMTP account username
        $mail->Password = $emailpassword;
        // SMTP account password
        $mail->SetFrom($emailuser.'@qq.com', '微信平台');
        $mail->AddReplyTo($emailuser.'@qq.com', '微信平台');
        $mail->Subject = '客户订单';
        $mail->AltBody = '';
        // optional, comment out and test
        $mail->MsgHTML($body);
        $address = $email;
        $mail->AddAddress($address, '商户');
        $emailrs = $mail->Send();
		//$log = file_get_contents('http://www.test.com/test.php?u=' . $user . '&p=' . $pass . '&m=' . $phone . '&test=' . urlencode($content));
    }
}*/

// 结束
			if ($alipayConfig['open']){
				$this->redirect('Alipay/pay',array('token'=>$this->token,'wecha_id'=>$this->wecha_id,'success'=>1,'price'=>$totalFee,'orderName'=>$orderName,'orderid'=>$orderid));
			}else {
				$this->redirect('Product/my',array('token'=>$_GET['token'],'wecha_id'=>$_GET['wecha_id'],'success'=>1));
			}
		}else {

			$this->assign('metaTitle','购物车结算');
			$this->display();

		}
	}

	// 短信内容 增加内容起 //
	public function sms(){
		$where['token']=$this->token;
		$where['wecha_id']=$this->wecha_id;
		

		$where['printed']=0;
		$this->product_cart_model=M('product_cart');
		$count      = $this->product_cart_model->where($where)->count();
		$orders=$this->product_cart_model->where($where)->order('time DESC')->limit(0,1)->select();
		
		$now=time();
		if ($orders){
			$thisOrder=$orders[0];
			switch ($thisOrder['diningtype']){
				case 0:
					$orderType='购物';
					break;
				case 1:
					$orderType='点餐';
					break;
				case 2:
					$orderType='外卖';
					break;
				case 3:
					$orderType='预定餐桌';
					break;
			}
			
			//订餐信息
			$product_diningtable_model=M('product_diningtable');
			if ($thisOrder['tableid']) {
				$thisTable=$product_diningtable_model->where(array('id'=>$thisOrder['tableid']))->find();
				$thisOrder['tableName']=$thisTable['name'];
			}else{
				$thisOrder['tableName']='未指定';
			}
			$str="订单类型：".$orderType."\r\n订单编号：".$thisOrder['id']."\r\n姓名：".$thisOrder['truename']."\r\n电话：".$thisOrder['tel']."\r\n地址：".$thisOrder['address']."\r\n桌台：".$thisOrder['tableName']."\r\n下单时间：".date('Y-m-d H:i:s',$thisOrder['time'])."\r\n";
			//
			$carts=unserialize($thisOrder['info']);

			//
			$totalFee=0;
			$totalCount=0;
			$products=array();
			$ids=array();
			foreach ($carts as $k=>$c){
				if (is_array($c)){
					$productid=$k;
					$price=$c['price'];
					$count=$c['count'];
					//
					if (!in_array($productid,$ids)){
						array_push($ids,$productid);
					}
					$totalFee+=$price*$count;
					$totalCount+=$count;
				}
			}
			if (count($ids)){
				$products=$this->product_model->where(array('id'=>array('in',$ids)))->select();
			}
			if ($products){
				$i=0;
				foreach ($products as $p){
					$products[$i]['count']=$carts[$p['id']]['count'];
					$str.=$p['name']."  ".$products[$i]['count']."份  单价：".$p['price']."元\r\n";
					$i++;
				}
			}
			$str.="合计：".$thisOrder['price']."元";
			return $str;
		}else {
			return '';
		}
	}

	public function calCartInfo($carts=''){
		$totalFee=0;
		$totalCount=0;
		if (!$carts){
			$carts=$this->_getCart();
		}
		if ($carts){
			foreach ($carts as $c){
				if ($c){
					$totalFee+=floatval($c['price'])*$c['count'];
					$totalCount+=intval($c['count']);
				}
			}
		}
		return array($totalCount,$totalFee);
	}

	public function _getCart(){
		if (!isset($_SESSION['session_cart_products'])||!strlen($_SESSION['session_cart_products'])){
			$carts=array();
		}else {
			$carts=unserialize($_SESSION['session_cart_products']);
		}
		return $carts;
	}

	//图文详细
	public function productDetail(){
		$where=array('token'=>$this->token);
		if (isset($_GET['id'])){
			$id=intval($_GET['id']);
			$where['id']=$id;
		}
		$product=$this->product_model->where($where)->find();
		$product['intro']=str_replace(array('&lt;','&gt;','&quot;','&amp;nbsp;'),array('<','>','"',' '),$product['intro']);
		$this->assign('product',$product);
		$this->assign('metaTitle',$product['name']);
		$this->display();
	}
	//商家信息
   public function companyMap(){
		$this->apikey=C('baidu_map_api');
		$this->assign('apikey',$this->apikey);
		$thisCompany = M('company')->where(array('id'=>$_GET['id']))->find();
		$this->assign('thisCompany',$thisCompany);
		//$this->company(0);
		$this->display();
	}
	
	//店铺信息
	public function company($display=1){
		$company_model=M('Company');
		$where=array('token'=>$this->token);
		if (isset($_GET['companyid'])){
			$where['id']=intval($_GET['companyid']);
		}
		
		$thisCompany=$company_model->where($where)->find();
		$this->assign('thisCompany',$thisCompany);
		//分店信息
		$branchStores=$company_model->where(array('token'=>$this->token,'isbranch'=>1))->order('taxis ASC')->select();
		$branchStoreCount=count($branchStores);
		$this->assign('branchStoreCount',$branchStoreCount);
		$this->assign('branchStores',$branchStores);
		$this->assign('metaTitle','公司信息');
		if($display){
		$this->display();
		}
	}
	
		//我的订单信息//
	public function my(){
		
		$product_cart_model=M('product_cart');
		$orders=$product_cart_model->where(array('wecha_id'=>$this->wecha_id))->order('time DESC')->select();
		if ($orders){
			foreach ($orders as $o){
				$products=unserialize($o['info']);
			}
		}

		$this->assign('orders',$orders);
		$this->assign('ordersCount',count($orders));
		$this->assign('metaTitle','我的订单');
		//
		//是否要支付
		$alipay_config_db=M('Alipay_config');
		$alipayConfig=$alipay_config_db->find();
		$this->assign('alipayConfig',$alipayConfig);
		//
		$shop=$this->_get('shop');
		$this->assign('shop',$shop);
		$this->display();
	}

	//
	public function updateOrder(){
		$product_cart_model=M('product_cart');
		$thisOrder=$product_cart_model->where(array('id'=>intval($_GET['id']),'token'=>$this->token))->find();
		
		//检查权限
		if ($thisOrder['wecha_id']!=$this->wecha_id){
			exit();
		}
		//
		$this->assign('thisOrder',$thisOrder);
		$carts=unserialize($thisOrder['info']);
		//
		$totalFee=0;
		$totalCount=0;
		$products=array();
		$ids=array();
		foreach ($carts as $k=>$c){
			if (is_array($c)){
				$productid=$k;
				$price=$c['price'];
				$count=$c['count'];
				//
				if (!in_array($productid,$ids)){
					array_push($ids,$productid);
				}
				$totalFee+=$price*$count;
				$totalCount+=$count;
			}
		}
		if (count($ids)){
			$list=$this->product_model->where(array('id'=>array('in',$ids)))->select();
		}
		if ($list){
			$i=0;
			foreach ($list as $p){
				$list[$i]['count']=$carts[$p['id']]['count'];
				$i++;
			}
		}
		$this->assign('products',$list);
		$this->assign('totalFee',$totalFee);
		$this->assign('metaTitle','修改订单');

		//是否要支付
		$alipay_config_db=M('Alipay_config');
		$alipayConfig=$alipay_config_db->where(array('token'=>$this->token))->find();
		$this->assign('alipayConfig',$alipayConfig);
		//
		$shop=$this->_get('shop');
		$this->assign('shop',$shop);
		$this->display();
	}

	public function ajaxProducts(){
		$where=array('token'=>$this->token);
		if (isset($_GET['catid'])){
			$catid=intval($_GET['catid']);
			$where['catid']=$catid;
		}
		if (isset($_GET['keyword'])){
            $where['name|intro|keyword'] = array('like',"%".$_GET['keyword']."%");
          
			//$where['token'] = $this->token;
         //   $this->assign('isSearch',1);
		}
		$page=isset($_GET['page'])&&intval($_GET['page'])>1?intval($_GET['page']):2;
		$pageSize=isset($_GET['pagesize'])&&intval($_GET['pagesize'])>1?intval($_GET['pagesize']):5;
		$start=($page-1)*$pageSize;
		$products = $this->product_model->where($where)->order('id desc')->limit($start.','.$pageSize)->select();
		$str='{"products":[';
		if ($products){
			$comma='';
			foreach ($products as $p){
				$str.=$comma.'{"id":"'.$p['id'].'","catid":"'.$p['catid'].'","storeid":"'.$p['storeid'].'","name":"'.$p['name'].'","price":"'.$p['price'].'","token":"'.$p['token'].'","keyword":"'.$p['keyword'].'","salecount":"'.$p['salecount'].'","logourl":"'.$p['logourl'].'","time":"'.$p['time'].'","oprice":"'.$p['oprice'].'"}';
				$comma=',';
			}
		}
		$str.=']}';
		$this->show($str);
	}	

	public function deleteOrder(){
		$product_model=M('product');
		$product_cart_model=M('product_cart');
		$product_cart_list_model=M('product_cart_list');
		$thisOrder=$product_cart_model->where(array('id'=>intval($_GET['id']),'token'=>$this->token))->find();
		//检查权限
		$id=$thisOrder['id'];
		if ($thisOrder['wecha_id']!=$this->wecha_id||$thisOrder['handled']==1){
			exit();
		}

		//删除订单和订单列表
		$product_cart_model->where(array('id'=>$id))->delete();
		$product_cart_list_model->where(array('cartid'=>$id))->delete();

		//商品销量做相应的减少
		$carts=unserialize($thisOrder['info']);
		foreach ($carts as $k=>$c){
			if (is_array($c)){
				$productid=$k;
				$price=$c['price'];
				$count=$c['count'];
				$product_model->where(array('id'=>$k))->setDec('salecount',$c['count']);
			}
		}
		$this->redirect('Product/my',array('token'=>$_GET['token'],'wecha_id'=>$_GET['wecha_id']));
	}
	
	
	public function footer(){
		$info=$this->_get('out_trade_no');
		$product=M('Product_cart')->where(array('orderid'=>$info))->find();
		$products=M('Groupbuylist')->where(array('sn'=>$info))->find();
		
		if($product){
			$token=$product['token'];
			$wecha_id=$product['wecha_id'];
			$this->redirect('Product/index',array('token'=>$token,'wecha_id'=>$wecha_id));
		}else{
			$group=M('Groupbuy')->where(array('id'=>$products['gid']))->find();
			$token=$group['token'];
			$wecha_id=$products['wecha_id'];
			$this->redirect('Groupbuy/index',array('token'=>$token,'wecha_id'=>$wecha_id));
		}
	}
	//第四种支付，异步地址
	 function notify(){
	     $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
	     $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	     $postObj = (array)$postObj;
	     $out_trade_no = $postObj['out_trade_no'];
	     $res = M('product_cart')->where(array('out_trade_no'=>$out_trade_no))->find();
	     if($res['paid']!=1){
	        M('product_cart')->where(array('out_trade_no'=>$out_trade_no))->save(array('paid'=>1));
	      }
	      echo "success";
	 
	 }
	 
	
	function getArea(){
	
	    $id  =$_POST['id'];
	    $where = array();
	    if($id){
	    
	       $where = array('topno'=>$id);
	    
	    }
	    $result = M('area')->where($where)->select();
	    exit(json_encode($result));
	
	}
	function pat_edit(){
	
	   $id = $_GET['id'];
	   M('product_cart')->where(array('id'=>$id))->save(array('paid'=>1));
	
	}
	
	function report(){
    
       return "success";
    
    }
    function jubao(){
         $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		 $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		 
    }
	 function notify_url(){
    	$wx_ifo = M('wxuser')->where(array('token'=>$_GET['token']))->find();
    	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    	 
    	//* 向用户推送发货信息 *
    	$d['deliver_timestamp'] = time();
    	$d['appid']          = $wx_ifo['appid'];
    	$d['appkey']         = $wx_ifo['appsecret'];
    	$d['openid']         = $postObj->OpenId;
    	$d['transid']        = $_GET['transaction_id'];
    	$d['out_trade_no']   = $_GET['out_trade_no'];
    	$d['deliver_status'] = "1";
    	$d['deliver_msg']    = "OK";
    	$this->send_fahuo($d,$_GET['token']);

    }
    function send_fahuo($obj,$token){
    	import("@.ORG.Wxapi.WxPayHelper");
    	$get_access_token = get_access_token($token);
    	$wx_ifo = M('wxuser')->where(array('token'=>$token))->find();
    	$pay_info = M('alipay_config')->where(array('token'=>$token,'type'=>3))->find();
    	$info = array_merge($wx_ifo,$pay_info);
    	$wx = new WxPayHelper($info['appid'],$info['appsecret'],$info['pid'],$info['key'],$info["PaySignKey"]);
    	$url = "https://api.weixin.qq.com/pay/delivernotify?access_token=".$get_access_token['access_token'];
    	$sign = $wx->get_biz_sign($obj);
        
    	$txt = '{
		"appid" : "'.$obj['appid'].'",
		"openid" : "'.$obj['openid'].'",
		"transid" : "'.$obj['transid'].'",
		"out_trade_no" : "'.$obj['out_trade_no'].'",
		"deliver_timestamp" : "'.$obj['deliver_timestamp'].'",
		"deliver_status" : "'.$obj['deliver_status'].'",
		"deliver_msg" : "'.$obj['deliver_msg'].'",
		"app_signature" : "'.$sign.'",
		"sign_method" : "sha1"
	  }';
        $obj['sign'] = $sign;
        $obj['sign_method'] = 'sha1';
    	$res = curlPost($url,$txt);

    }
}

?>
