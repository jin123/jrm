<?php
class OrderinfoAction extends CommonAction{
	public function index(){
		$this->display();
	}
	public function derive(){
	    $order = M('product_cart');		    	
	    $ktime = strtotime($_POST['ktime']);		    	
	    $jtime = $_POST['jtime']?strtotime($_POST['jtime'])+86400:null;

	    $hubby_get=$this->_post('hubby');
	    $hubby=array();
	    foreach ($hubby_get as $value){
	        $hubby[$value]=1;
	    }
	    unset($hubby_get);
	    $prefix = C ( 'DB_PREFIX' );
	    $product_cart_list_model = M ('product_cart_list');
	    
	    

	    if($hubby['name'] || $hubby['total'] || $hubby['price']){
	    	$multiline=1;
	    }else{
	        $multiline=0;
	    }
	    
	    $map['A.token']=session('token');
	    $ktime	&& $map['A.time'][]  = array('egt',$ktime);
	    $jtime	&& $map['A.time'][]  = array('elt',$jtime);
        $cart=$order->alias('A')->field('orderid,time,price,address,truename,tel,sent,paid,groupon,logistics,logisticsid')->where($map)->select();
        
        if($multiline){
	        $cart_list=$order->alias('A')
	        ->join("{$prefix}product_cart_list AS B ON A.id=B.cartid")
	        ->join(" {$prefix}product AS C ON b.productid=C.id ")
	        ->field('A.orderid,C.name,B.total,C.price as cprice')
	        ->where($map)
	        ->select(); 
        }

	    
        $title_arr = array ();
        if($hubby['id']) $title_arr[]="订单号";
        if($hubby['time']) $title_arr[]="下单时间";
        if($hubby['name']) $title_arr[]="商品名称";
        if($hubby['total']) $title_arr[]="商品数量";
        if($hubby['price']) $title_arr[]="商品价格";
        if($hubby['cartprice']) $title_arr[]="订单金额";
        if($hubby['address']) $title_arr[]="收货地址";
        if($hubby['truename']) $title_arr[]="收货人姓名";
        if($hubby['tel']) $title_arr[]="收货人联系方式";
        if($hubby['sent']) $title_arr[]="订单状态";
        if($hubby['paid']) $title_arr[]="付款状态";
        if($hubby['groupon']) $title_arr[]="分类";
        if($hubby['logistics']) $title_arr[]="快递公司";
        if($hubby['logisticsid']) $title_arr[]="快递单号";               
        
	    
	    $table="<table border=\"1\">";
	    if (!empty($title_arr)){
	        $table.="<tr style=\" background:#09F; color:#fff;\">";
	        foreach ($title_arr as $k => $v) {
	            $table.="<th>{$v}</th>";
	        }	
	        $table.="</tr>";
	    }
	    

	    foreach ($cart_list as $value) {
	        $cart_list_by_orderid[$value['orderid']][]=$value;
	    }
	    
	    $sent=array('0'=>"未发货",'1'=>"已发货");
	    $paid=array('0'=>"未付款",'1'=>"已付款");
	    $groupon=array('0'=>'购物',"1"=>'点餐',"2"=>'外卖',"3"=>'预定餐桌');
	    
	    foreach($cart as $key => $val){	    
	        if($multiline){
		        $count=count($cart_list_by_orderid[$val['orderid']]);
		        
		        $name=$cart_list_by_orderid[$val['orderid']][0]['name'];
		        $total=$cart_list_by_orderid[$val['orderid']][0]['total'];
		        $cprice=$cart_list_by_orderid[$val['orderid']][0]['cprice'];
		        $item_all_price=$total*$cprice;
		        $time=date("Y-m-d H:m:s",$val['time']);
	        }else{
	            $count=1;
	        }
	        
	        $table.="<tr>";
	        if($hubby['id']) $table.="<td rowspan=\"{$count}\">{$val['orderid']}</td>";
	        if($hubby['time']) $table.="<td rowspan=\"{$count}\">{$time}</td>";
	        if($hubby['name']) $table.="<td>{$name}</td>";
	        if($hubby['total']) $table.="<td>{$total}</td>";
	        if($hubby['price']) $table.="<td>{$item_all_price}</td>";
	        if($hubby['cartprice']) $table.="<td rowspan=\"{$count}\">{$val['price']}</td>";
	        if($hubby['address']) $table.="<td rowspan=\"{$count}\">{$val['address']}</td>";
	        if($hubby['truename']) $table.="<td rowspan=\"{$count}\">{$val['truename']}</td>";
	        if($hubby['tel']) $table.="<td rowspan=\"{$count}\">{$val['tel']}</td>";
	        if($hubby['sent']) $table.="<td rowspan=\"{$count}\">{$sent[$val['sent']]}</td>";
	        if($hubby['paid'])$table.="<td rowspan=\"{$count}\">{$paid[$val['paid']]}</td>";
	        if($hubby['groupon'])$table.="<td rowspan=\"{$count}\">{$groupon[$val['groupon']]}</td>";
	        if($hubby['logistics'])$table.="<td rowspan=\"{$count}\">{$val['logistics']}</td>";
	        if($hubby['logisticsid'])$table.="<td rowspan=\"{$count}\">{$val['logisticsid']}</td>";
	        $table.="</tr>";
	        if($multiline && $count>1){		            
	        	for($i=1;$i<$count;$i++){
	        	    $table.="<tr>";
	        	    $name=$cart_list_by_orderid[$val['orderid']][$i]['name'];
	        	    $total=$cart_list_by_orderid[$val['orderid']][$i]['total'];
	        	    $cprice=$cart_list_by_orderid[$val['orderid']][$i]['cprice'];
	        	    $item_all_price=$total*$cprice;
                    if($hubby['name']) $table.="<td>{$name}</td>";
    		        if($hubby['total']) $table.="<td>{$total}</td>";
    		        if($hubby['price']) $table.="<td>{$item_all_price}</td>";
    		        $table.="</tr>";
	        	}
	        	
	        }
	    }
	    $table.="</table>";
	    $this->tableToExcel($table,date('YmdHis'));
	}
	public function tableToExcel($table,$name='report'){
        $str = iconv("UTF-8","GBK",$str);
        $name = iconv("UTF-8","GBK",$name);
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
	    header('Content-Length: '.strlen($table));
	    header("Content-type:application/vnd.ms-excel");
	    header("Content-Disposition:filename={$name}.xls");
	    echo $table; 
	}
}