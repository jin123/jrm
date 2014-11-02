<?php
	class CftAction extends Action{
	public $token;
	public $wecha_id;
	public $alipayConfig;
	public function __construct(){
		parent::__construct();
		$this->token		= $this->_get('token');
		$this->token=$this->token?$this->token:C('token');
		$this->assign('token',$this->token);
		$this->wecha_id	= $this->_get('wecha_id');
		$this->assign('wecha_id',$this->wecha_id);
		//读取支付宝配置
		$this->cftConfig=M('Alipay_config')->where(array('open'=>1,'type'=>2))->find();
		//dump($this->cftConfig);exit;
	}
	public function pay(){
		import("@.ORG.classes.RequestHandler");
		$shop=$this->_get('shop');
		$sn=$this->_get('sn');
		$single_orderid=$this->_get('single_orderid');

		if($shop == 2){
			$name=$this->_get('ordername');
			$single_orderid=$this->_get('single_orderid');
		 	$product_cart = M ( 'Product_cart' );
			$thisOrder=$product_cart->where(array('id'=>$single_orderid))->find();
			if(!$thisOrder){echo '未找到订单';exit;}
			
			$selects=$product_cart->where(array('id'=>$single_orderid))->find();
			$total_fee= floatval($selects['price'])*100;
			$product_fee= floatval($selects['price'])/$selects['total'];
			$desc=$name;
			$out_trade_no=$selects['orderid'];
			$trade_mode=1;
			$bank_type_value=$selects['paid'];

		}else{
			$product_cart_model = M ( 'Groupbuylist' );
			$thisOrder=$product_cart_model->where(array('sn'=>$sn))->find();
			if(!$thisOrder){echo '未找到订单';exit;}
			
			$select = $product_cart_model->table('tp_groupbuy a,tp_groupbuylist b')
			->field('a.*,b.*')
			->where(array('a.id=b.gid','b.sn'=>$sn /* ,'b.wecha_id'=>$wecha_id */))
			->find();
			$total_fee= floatval($select['jg'])*100*$select['tgnum'];
			$product_fee= floatval($select['jg']);
			$desc=$select['name'];
			$out_trade_no=$select['sn'];
			$trade_mode=1;
			$bank_type_value=$select['fustatus'];
		}
		

		//tenpay_config.php配置
		$spname="财付通双接口测试";
		$partner = $this->cftConfig['pid']; //财付通商户号
		$key = $this->cftConfig['key'];//财付通密钥
		$seller_id= $this->cftConfig['pid'];//卖家商户号
		$return_url = rtrim(C('site_url'), '/').U('Wap/PayUrl/payReturnUrl');			//显示支付结果页面,*替换成payReturnUrl.php所在路径
		$notify_url = rtrim(C('site_url'), '/').U('Wap/PayUrl/payNotifyUrl');			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
		/**
		$return_url和$notify_url 可以自己弄出去2014-4-21
		*/
		/* 创建支付请求对象 */
		$reqHandler = new RequestHandler();
		$reqHandler->init();
		$reqHandler->setKey($key);
		$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
		
		
		
		//----------------------------------------
		//设置支付参数 
		//----------------------------------------
		$reqHandler->setParameter("partner", $partner);
		$reqHandler->setParameter("out_trade_no", $out_trade_no);
		$reqHandler->setParameter("total_fee", $total_fee);  //总金额
		$reqHandler->setParameter("return_url", $return_url);
		$reqHandler->setParameter("notify_url", $notify_url);
		$reqHandler->setParameter("body", $desc);               //商品描述
		$reqHandler->setParameter("bank_type", 'default');  	  //银行类型，默认为财付通
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
		$reqHandler->setParameter("fee_type", "1");               //币种
		$reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）

		//系统可选参数
		$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
		$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
		$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

		//业务可选参数
		$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
		$reqHandler->setParameter("product_fee", "");        	  //商品费用
		$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
		$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
		$reqHandler->setParameter("time_expire", "");             //订单失效时间
		$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
		$reqHandler->setParameter("goods_tag", "");               //商品标记
		$reqHandler->setParameter("trade_mode",$trade_mode);      //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
		$reqHandler->setParameter("transport_desc","");           //物流说明
		$reqHandler->setParameter("trans_type","1");              //交易类型
		$reqHandler->setParameter("agentid","");                  //平台ID
		$reqHandler->setParameter("agent_type","0");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
		$reqHandler->setParameter("seller_id",$seller_id);         //卖家的商户号


       $reqHandler->doSend();
		//请求的URL
		$reqUrl = $reqHandler->getRequestURL();

		//获取debug信息,建议把请求和debug信息写入日志，方便定位问题
		/**/
		$debugInfo = $reqHandler->getDebugInfo();
	    	$action=$reqHandler->getGateUrl();
	    	//var_dump($reqUrl);
		//submit按钮控件请不要含有name属性
		//var_dump($reqUrl);exit;
		//$sHtml = "<script>window.location.href='".$reqUrl."';</script>";
		//echo $sHtml;
	}
}
?>
