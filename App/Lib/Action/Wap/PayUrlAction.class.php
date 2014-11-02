<?php
class PayUrlAction extends Action{
    public function __construct(){
    	parent::__construct();
    	import("@.ORG.classes.ResponseHandler");
    	import("@.ORG.classes.RequestHandler");
    	import("@.ORG.classes.client.ClientResponseHandler");
    	import("@.ORG.classes.client.TenpayHttpClient");
    	
    	$this->token		= $this->_get('token');
    	$this->token=$this->token?$this->token:C('token');
    	$this->assign('token',$this->token);
    	$this->wecha_id	= $this->_get('wecha_id');
    	$this->assign('wecha_id',$this->wecha_id);
    	//读取支付宝配置
    	$cft_config_db=M('Alipay_config');
    	$this->cftConfig=$cft_config_db->where(array('type'=>2))->find();
    	unset($_GET['_URL_']);
    }
    
    
    public function payNotifyUrl(){
        //log_result("进入后台回调页面");
            
        
        $partner = $this->cftConfig['pid']; //财付通商户号
        $key = $this->cftConfig['key'];//财付通密钥
        $seller_id= $this->cftConfig['pid'];
        
        
            /* 创建支付应答对象 */
        $resHandler = new ResponseHandler ();
        $resHandler->setKey ( $key );
        
        // 判断签名
        if ($resHandler->isTenpaySign ()) {
            
            // 通知id
            $notify_id = $resHandler->getParameter ( "notify_id" );
            
            // 通过通知ID查询，确保通知来至财付通
            // 创建查询请求
            $queryReq = new RequestHandler ();
            $queryReq->init ();
            $queryReq->setKey ( $key );
            $queryReq->setGateUrl ( "https://gw.tenpay.com/gateway/simpleverifynotifyid.xml" );
            $queryReq->setParameter ( "partner", $partner );
            $queryReq->setParameter ( "notify_id", $notify_id );
            
            // 通信对象
            $httpClient = new TenpayHttpClient ();
            $httpClient->setTimeOut ( 5 );
            // 设置请求内容
            $httpClient->setReqContent ( $queryReq->getRequestURL () );
            
            // 后台调用
            if ($httpClient->call ()) {
                // 设置结果参数
                $queryRes = new ClientResponseHandler ();
                $queryRes->setContent ( $httpClient->getResContent () );
                $queryRes->setKey ( $key );                
                if ($resHandler->getParameter ( "trade_mode" ) == "1") {
                    // 判断签名及结果（即时到帐）
                    // 只有签名正确,retcode为0，trade_state为0才是支付成功
                    if ($queryRes->isTenpaySign () && $queryRes->getParameter ( "retcode" ) == "0" && $resHandler->getParameter ( "trade_state" ) == "0") {
                        //log_result ( "即时到帐验签ID成功" );
                        // 取结果参数做业务处理
                        $out_trade_no = $resHandler->getParameter ( "out_trade_no" );
                        // 财付通订单号
                        $transaction_id = $resHandler->getParameter ( "transaction_id" );
                        // 金额,以分为单位
                        $total_fee = $resHandler->getParameter ( "total_fee" );
                        // 如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
                        $discount = $resHandler->getParameter ( "discount" );
                        //log_result ( "即时到帐后台回调开始out_trade_no:{$out_trade_no}&transaction_id:{$transaction_id}&total_fee:{$total_fee}&discount:{$discount}");
                        // ------------------------------
                        // 处理业务开始
                        // ------------------------------
                        
                        // 处理数据库逻辑
                        // 注意交易单不要重复处理
                        // 注意判断返回金额
						
                        //$this->pay_success($out_trade_no,$transaction_id,$total_fee,$discount);
                        // ------------------------------
                        // 处理业务完毕
                        // ------------------------------
                        //log_result ( "即时到帐后台回调成功" );
                       // echo "success";
                    } else {
                        // 错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                        // echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes-> getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                        //log_result ( "即时到帐后台回调失败" );
                        echo "fail";
                    }
                } elseif ($resHandler->getParameter ( "trade_mode" ) == "2") 

                {
                    // 判断签名及结果（中介担保）
                    // 只有签名正确,retcode为0，trade_state为0才是支付成功
                    if ($queryRes->isTenpaySign () && $queryRes->getParameter ( "retcode" ) == "0") {
                        //log_result ( "中介担保验签ID成功" );
                        // 取结果参数做业务处理
                        $out_trade_no = $resHandler->getParameter ( "out_trade_no" );
                        // 财付通订单号
                        $transaction_id = $resHandler->getParameter ( "transaction_id" );
                        
                        // ------------------------------
                        // 处理业务开始
                        // ------------------------------
                        
                        // 处理数据库逻辑
                        // 注意交易单不要重复处理
                        // 注意判断返回金额
                        
                        //log_result ( "中介担保后台回调，trade_state=" . $resHandler->getParameter ( "trade_state" ) );
                        switch ($resHandler->getParameter ( "trade_state" )) {
                            case "0" : // 付款成功
                                break;
                            case "1" : // 交易创建
                                
                                break;
                            case "2" : // 收获地址填写完毕
                                
                                break;
                            case "4" : // 卖家发货成功
                                
                                break;
                            case "5" : // 买家收货确认，交易成功
                                
                                break;
                            case "6" : // 交易关闭，未完成超时关闭
                                
                                break;
                            case "7" : // 修改交易价格成功
                                
                                break;
                            case "8" : // 买家发起退款
                                
                                break;
                            case "9" : // 退款成功
                                
                                break;
                            case "10" : // 退款关闭
                                
                                break;
                            default :
                                // nothing to do
                                break;
                        }
                        
                        // ------------------------------
                        // 处理业务完毕
                        // ------------------------------
                        echo "success";
                    } else 

                    {
                        // 错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                        // echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes-> getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                        //log_result ( "中介担保后台回调失败" );
                        echo "fail";
                    }
                }
                
                // 获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
                /*
                 * echo "<br>------------------------------------------------------<br>"; echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>"; echo "query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>"; echo "query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>"; echo "query reqdebug:" . $queryReq->getDebugInfo() . "<br><br>" ; echo "query resdebug:" . $queryRes->getDebugInfo() . "<br><br>";
                 */
            } else {
                // 通信失败
                echo "fail";
                // 后台调用通信失败,写日志，方便定位问题
                echo "<br>call err:" . $httpClient->getResponseCode () . "," . $httpClient->getErrInfo () . "<br>";
            }
        } else {
            echo "<br/>" . "认证签名失败" . "<br/>";
            echo $resHandler->getDebugInfo () . "<br>";
        }
        
        
    }  

    
    public function pay_success($out_trade_no,$transaction_id,$total_fee,$discount){
        //log_result ( "支付回调" );

    	$wecha_id = $this->wecha_id;
    	$token    = $this->token;
    	$info=M('Groupbuylist')->where(array('sn'=>$out_trade_no))->find();
    	$infos=M('product_cart')->where(array('orderid'=>$out_trade_no))->find();
    	
    	if($info){
    		$product_cart_model = M ( 'Groupbuylist' );
    		$userinfo_model = M('Userinfo');
    		$sn=$out_trade_no;
    		$thisOrder=$product_cart_model->where(array('sn'=>$out_trade_no))->find();
    		if(!$thisOrder) echo "fail";
    		if($thisOrder['fustatus']==1){exit('success');}
    		$userinfo_model->where(array('wecha_id'=>$wecha_id))->setInc('total_score',$total_fee);
    		$userinfo_model->where(array('wecha_id'=>$wecha_id))->setInc('buy_score',$total_fee);
    		$result=$product_cart_model->where(array('id'=>$thisOrder['id']))->save(array('time'=>time(),'fustatus'=>1));
    		
    		if($result){
    			$this->_after_pay_success($sn);
    			echo 'success';
    		}else{
    			echo 'fail';
    		}
    	}else{
    		$product_cart = M ( 'Product_cart' );
    		$userinfo_model = M('Userinfo');
    		$orderid=$out_trade_no;
    		$thisOrder=$product_cart->where(array('orderid'=>$out_trade_no))->find();
    		if(!$thisOrder) echo "fail";
    		if($thisOrder['paid']==1){exit('success');}
    		$userinfo_model->where(array('wecha_id'=>$wecha_id))->setInc('total_score',$total_fee);
    		$userinfo_model->where(array('wecha_id'=>$wecha_id))->setInc('buy_score',$total_fee);
    		$result=$product_cart->where(array('id'=>$thisOrder['id']))->save(array('time'=>time(),'paid'=>1));
    		
    		if($result){
    			$this->_after_pay_succes($orderid);
    			echo 'success';
    		}else{
    			echo 'fail';
    		}
    	}               
    }
    
    
    public function payReturnUrl(){
        $partner = $this->cftConfig['pid']; //财付通商户号
        $key = $this->cftConfig['key'];//财付通密钥
        $seller_id= $this->cftConfig['pid'];
        //dump(111);exit;
        /* 创建支付应答对象 */
        $resHandler = new ResponseHandler();
        $resHandler->setKey($key);
        //----------------------------------------
        //判断签名
        if($resHandler->isTenpaySign()) {
  			
            //通知id
            $notify_id = $resHandler->getParameter("notify_id");
            //商户订单号
            $out_trade_no = $resHandler->getParameter("out_trade_no");
            //财付通订单号
            $transaction_id = $resHandler->getParameter("transaction_id");
            //金额,以分为单位
            $total_fee = $resHandler->getParameter("total_fee");
            //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
            $discount = $resHandler->getParameter("discount");
            //支付结果
            $trade_state = $resHandler->getParameter("trade_state");
            //交易模式,1即时到账
            $trade_mode = $resHandler->getParameter("trade_mode");
        
        
            if("1" == $trade_mode ) {
                if( "0" == $trade_state){
                	$this->pay_success($out_trade_no,$transaction_id,$total_fee,$discount);
                	$this->assign('title','购买成功');
                	//echo "<pre>";
                	$this->assign('get',$_GET);
                	//var_dump($_GET);
                    $this->display('Cft/pay_success');
                    	
        //            echo "<br/>" . "即时到帐支付成功" . "<br/>";
        
                } else {
                    //当做不成功处理
                    echo "<br/>" . "购买失败" . "<br/>";
                }
            }elseif( "2" == $trade_mode  ) {
                if( "0" == $trade_state) {
        
                    $this->assign('title','购买成功');
                    $this->display('Cft/pay_success');
                    	
                    //echo "<br/>" . "中介担保支付成功" . "<br/>";
        
                } else {
                    //当做不成功处理
                    echo "<br/>" . "购买失败" . "<br/>";
                }
            }
        
        } else {
            echo "<br/>" . "认证签名失败" . "<br/>";
            echo $resHandler->getDebugInfo() . "<br>";
        }
    } 

     
    public function _after_pay_success($sn){
    	$wecha_id = $this->wecha_id;
    	$token    = $this->token;
    	
    	$datas=new Model();
    	$info=$datas->table('tp_groupbuy a,tp_groupbuylist b')
    	->field('a.*,b.*')
    	->where(array('a.id=b.gid','b.wecha_id'=>$wecha_id,'b.sn'=>$sn))
    	->find();
    	
    	$da=M('Property');
		$_POST['title']='微团购';
    	$_POST['name']=$info['name'];
    	$_POST['price']  =$info['jg'];
    	$_POST['shoptel']  =$info['sjtel'];
    	$_POST['username']=$info['usname'];
    	$_POST['total']  =$info['tgnum'];
    	$_POST['tel']=$info['tel'];
    	$_POST['addr']  =$info['addr'];
    	$_POST['indentsn']=$info['sn'];
    	$_POST['wecha_id']=$wecha_id;
    	$_POST['token']=$token;	
    	$da->add($_POST);
    	
    }
    
    public function _after_pay_succes($orderid){
    	$wecha_id = $this->wecha_id;
    	$token    = $this->token;
    	 
    	$info=M('Product_cart')->where(array('wecha_id'=>$wecha_id,'orderid'=>$orderid))->find();
    	 
    	$da=M('Property');
		$_POST['title']='微商城';
    	//$_POST['name']=$info['name'];
    	$_POST['price']  =$info['price'];
    	//$_POST['shoptel']  =$info['sjtel'];
    	$_POST['username']=$info['truename'];
    	$_POST['total']  =$info['total'];
    	$_POST['tel']=$info['tel'];
    	$_POST['addr']  =$info['address'];
    	$_POST['indentsn']=$info['orderid'];
    	$_POST['wecha_id']=$wecha_id;
    	$_POST['token']=$token;
    	$da->add($_POST);
    	 
    }
    
    
    
    
    
}