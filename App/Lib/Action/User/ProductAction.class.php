<?php
/**
 *微商铺
**/
class ProductAction extends CommonAction{
	public $token;
	public $product_model;
	public $product_cat_model;
	public function _initialize() {
		parent::_initialize();
		$this->token = session('token');
		$this->assign('token',$this->token);
	}
	public function index(){
		$product_model=M('Product');
		$catid=intval($this->_get('catid'));
		if(!empty($catid)){
			$where['catid'] = $catid;
		}
		$where['token'] = session('token');
        if(IS_POST){
            $key = $this->_post('Name');
            if(empty($key)){
                $this->error("关键词不能为空");
            }
          //  $map['token'] = session('token'); 
            $where=" token="."'".session('token')."'";
            
           // $map['name|intro|keyword'] = array('like',"%$key%"); 
            $where.= " AND ".$_POST['post']['field']." like "."'%".$key."%'";
            $list = $product_model->where($where)->select(); 
            $count      = $product_model->where($where)->count();       
            $Page       = new Page($count,20);
        	$show       = $Page->show();
        }else{
        	$count      = $product_model->where($where)->count();
        	$Page       = new Page($count,20);
        	$show       = $Page->show();
        	$list = $product_model->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
		$this->assign('page',$show);		
		$this->assign('list',$list);
		$this->assign('isProductPage',1);
		
		$this->display();		
	}
	//操作商品上架和下架
	function edit_status(){
	   $db=M('Product');
       $data['status'] = $_GET['status'];
       $edit = $db->where(array('id'=>$_GET['id']))->save($data);
	   if($edit){
	   
	       $this->success('操作成功',U('Product/index',array('token'=>session('token'))));
	   
	   }
	   else{
	   
	   
	    $this->success('操作失败',U('Product/index',array('token'=>session('token'))));
	   }
	
	}
	public function cats(){ 

		$db=M('Product_cat');
		$where=array('token'=>session('token'));
        if(IS_POST){
            $key = $this->_post('searchkey');
            if(empty($key)){
                $this->error("关键词不能为空");
            }
        	$Page       = new Page($count,20);

            $map['name|des'] = array('like',"%$key%"); 
			$map['token'] = session('token');
            $list = $db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select(); 

            $count      = $db->where($map)->count();       
            $Page       = new Page($count,20);
        	$show       = $Page->show();
        }else{
        	$count      = $db->where($where)->count();
        	$Page       = new Page($count,20);
        	$list = $db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        	$show       = $Page->show();
			//echo $db->getLastSql();
        }
		$this->assign('page',$show);		
		$this->assign('list',$list);

		$this->display();		
		
	}

	public function catAdd(){ 
		if(IS_POST){
			$this->insert('Product_cat','/cats');
		}else{
			$this->display('catSet');
		}
	}

	public function catDel(){
        $id = $this->_get('id');
        if(IS_GET){
            $where=array('id'=>$id);
            $data=M('Product_cat');
            $check=$data->where($where)->find();
            if($check==false)   $this->error('非法操作');
            $product_model=M('Product');
            $productsOfCat=$product_model->where(array('catid'=>$id,'token'=>session('token')))->find();
            if (count($productsOfCat)){
            	$this->error('本分类下有商品，请删除商品后再删除分类',U('Product/index',array('catid'=>$productsOfCat['catid'])));
            }
            $back=$data->where($wehre)->delete();
            if($back==true){
            	if (!$this->isDining){
                $this->success('操作成功',U('Product/cats'));
            	}else {
            		$this->success('操作成功',U('Product/cats'));
            	}
            }else{
                 $this->error('服务器繁忙,请稍后再试',U('Product/cats'));
            }
        }        
	}
	
	public function catSet(){
        $id = $this->_get('id'); 
		$checkdata = M('Product_cat')->where(array('id'=>$id,'token'=>session('token')))->find();
		if(empty($checkdata)){
            $this->error("没有相应记录.您现在可以添加.",U('Product/catAdd'));
        }
		if(IS_POST){ 
            $data=D('Product_cat');
            $where=array('id'=>$this->_post('id'),'token'=>session('token'));
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($data->create()){
				if($data->where($where)->save($_POST)){
					if (!$this->isDining){
						$this->success('修改成功',U('Product/cats'));
					}else {
						$this->success('修改成功',U('Product/cats'));
					}
					
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{ 
			$this->assign('set',$checkdata);
			$this->display();	
		
		}
	}

	public function add(){ 
		if(IS_POST){
		   
		    $oprice = $_POST['oprice'];
		    if(strlen($oprice)>10){
		        $this->error('请填写合理规价');		    
		    }
			$this->all_insert('Product','/index?dining='.$this->isDining);
		}else{
			//分类
			$db=M('Product_cat');
			$catWhere=array('parentid'=>0,'token'=>session('token'));
			if ($this->isDining){
				$catWhere['dining']=1;
			}else {
				$catWhere['dining']=0;
			}
			$cats=$db->where($catWhere)->select();
			if (!$cats){
				 $this->error("请先添加分类",U('Product/catAdd',array('dining'=>$this->isDining)));
				 exit();
			}
			$this->assign('cats',$cats);
			$catsOptions=$this->catOptions($cats,0);
			$this->assign('catsOptions',$catsOptions);
			//
			$this->assign('isProductPage',1);
			$this->display('set');
		}
	}
	/**
	 * 商品类别ajax select
	 *
	 */
	public function ajaxCatOptions(){
		$parentid=intval($_GET['parentid']);
		$data=M('Product_cat');
		$catWhere=array('parentid'=>$parentid,'token'=>session('token'));
		$cats=$data->where($catWhere)->select();
		$str='';
		if ($cats){
			foreach ($cats as $c){
				$str.='<option value="'.$c['id'].'">'.$c['name'].'</option>';
			}
		}
		$this->show($str);
	}
	public function set(){
        $id = $this->_get('id'); 
        $product_model=M('Product');
        $product_cat_model=M('Product_cat');
		$checkdata = $product_model->where(array('id'=>$id))->find();
		if(empty($checkdata)){
            $this->error("没有相应记录.您现在可以添加.",U('Product/add'));
        }
		if(IS_POST){ 
            $where=array('id'=>$this->_post('id'),'token'=>session('token'));
			$check=$product_model->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($product_model->create()){
				if($product_model->where($where)->save($_POST)){
					$this->success('修改成功',U('Product/index',array('token'=>session('token'),'dining'=>$this->isDining)));
					$keyword_model=M('Keyword');
					$keyword_model->where(array('token'=>session('token'),'pid'=>$this->_post('id'),'module'=>'Product'))->save(array('keyword'=>$this->_post('keyword')));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($product_model->getError());
			}
		}else{
			//分类
			$catWhere=array('parentid'=>0,'token'=>session('token'));
			if ($this->isDining){
				$catWhere['dining']=1;
			}
			$cats=$product_cat_model->where($catWhere)->select();
			$this->assign('cats',$cats);
			
			$thisCat=$product_cat_model->where(array('id'=>$checkdata['catid']))->find();
			$childCats=$product_cat_model->where(array('parentid'=>$thisCat['parentid']))->select();
			$this->assign('thisCat',$thisCat);
			$this->assign('parentCatid',$thisCat['parentid']);
			$this->assign('childCats',$childCats);
			$this->assign('isUpdate',1);
			$catsOptions=$this->catOptions($cats,$checkdata['catid']);
			$childCatsOptions=$this->catOptions($childCats,$thisCat['id']);
			$this->assign('catsOptions',$catsOptions);
			$this->assign('childCatsOptions',$childCatsOptions);
			//
			$this->assign('set',$checkdata);
			$this->assign('isProductPage',1);
			$this->display();	
		
		}
	}
	
	//商品类别下拉列表
	public function catOptions($cats,$selectedid){
		$str='';
		if ($cats){
			foreach ($cats as $c){
				$selected='';
				if ($c['id']==$selectedid){
					$selected=' selected';
				}
				$str.='<option value="'.$c['id'].'"'.$selected.'>'.$c['name'].'</option>';
			}
		}
		return $str;
	}
	
	public function del(){
		$product_model=M('Product');
        $id = $this->_get('id');
        if(IS_GET){                              
            $where=array('id'=>$id,'token'=>session('token'));
            $check=$product_model->where($where)->find();
            if($check==false)   $this->error('非法操作');

            $back=$product_model->where($wehre)->delete();
            if($back==true){
            	$keyword_model=M('Keyword');
            	$keyword_model->where(array('pid'=>$id,'module'=>'Product','token'=>session('token')))->delete();
                $this->success('操作成功',U('Product/index',array('dining'=>$this->isDining)));
            }else{
                 $this->error('服务器繁忙,请稍后再试',U('Product/index'));
            }
        }        
	}

	public function orders(){
		$product_cart_model=M('product_cart');

		$where=array('token'=>session('token'));

		if(IS_POST){
			$key = $this->_post('searchkey');
			if(empty($key)){
				$this->error("关键词不能为空");
			}

			$where['truename|address'] = array('like',"%$key%");
			$orders = $product_cart_model->where($where)->select();
			$count      = $product_cart_model->where($where)->limit($Page->firstRow.','.$Page->listRows)->count();
			$Page       = new Page($count,20);
			$show       = $Page->show();
		}else {
			if (isset($_GET['handled'])){
				$where['handled']=intval($_GET['handled']);
			}
			$count      = $product_cart_model->where($where)->count();
			$Page       = new Page($count,20);
			$show       = $Page->show();
			$orders=$product_cart_model->where($where)->order('time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		}

		$unHandledCount=$product_cart_model->where(array('token'=>session('token'),'handled'=>0))->count();
		$this->assign('unhandledCount',$unHandledCount);
     //   echo "<pre>"; var_dump($orders);die;
		$this->assign('orders',$orders);
		$this->assign('page',$show);
		$this->display();

	}
	
	public function orderInfo(){
		$this->product_model=M('Product');
		$this->product_cat_model=M('Product_cat');
		$product_cart_model=M('Product_cart');
		
		$thisOrder=$product_cart_model->where(array('id'=>intval($_GET['id']),'token'=>session('token')))->find();
		///echo "<pre>";
		///var_dump($thisOrder);
		if (IS_POST){
			if (intval($_POST['sent'])){
				$_POST['handled']=1;
			}
			$product_cart_model->where(array('id'=>$thisOrder['id'],'token'=>session('token')))->save(array('sent'=>intval($_POST['sent']),'paid'=>intval($_POST['paid']),'logistics'=>$_POST['logistics'],'logisticsid'=>$_POST['logisticsid'],'handled'=>1));
			
			$this->success('修改成功',U('Product/orderInfo',array('id'=>$thisOrder['id'])));
		}else {
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
				$list=$this->product_model->where(array('id'=>array('in',$ids),'token'=>session('token')))->select();
			}
			if ($list){
				$i=0;
				foreach ($list as $p){
				$list[$i]['total']=$carts[$p['id']]['count'];
				/*	$list[$i]['count']=$carts[$p['id']]['count'];
					$list[$i]['count']=$carts[$p['id']]['oprice'];
					$list[$i]['count']=$carts[$p['id']]['product_code'];
					$list[$i]['count']=$carts[$p['id']]['factory'];*/
					$i++;
				}
			}
			$this->assign('products',$list);
			$this->assign('totalFee',$totalFee);
			$this->display();
		}
	}
	
	public function deleteOrder(){
		$product_model=M('product');
		$product_cart_model=M('product_cart');
		$product_cart_list_model=M('product_cart_list');
		$thisOrder=$product_cart_model->where(array('id'=>intval($_GET['id']),'token'=>session('token')))->find();

		//检查权限
		$id=$thisOrder['id'];
		//
		//删除订单和订单列表
		$product_cart_model->where(array('id'=>$id,'token'=>session('token')))->delete();
		$product_cart_list_model->where(array('cartid'=>$id,'token'=>session('token')))->delete();
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
		$this->success('操作成功',$_SERVER['HTTP_REFERER']);
	}	
	 function transportation(){
	 
	 
	     //$data= M('product_transet')->where(array('token'=>session('token'))->select();	
          $list = M('product_transet')->where(array('token'=>session('token')))->select();		  
         $this->assign('list',$list);
	     $this->display();	
	 
	 
	 
	 }

	 function transet_del(){
	  $list = M('product_transet')->where(array('id'=>$_GET['id']))->delete();
	  $this->success('操作成功',U('/User/Product/transportation'));
	 }
	 function transet(){
	     $data = array();
		 if(isset($_GET['id'])){
		    $data = M('product_transet')->where(array('id'=>$_GET['id']))->find();
		 }
		 if(IS_POST){
		  
		     if(isset($_GET['id'])){
			 
			    M('product_transet')->where(array('id'=>$_GET['id']))->save($_POST);
			 }
			 else{
			 $_POST['token'] = session('token');
			  M('product_transet')->add($_POST);
			 }
		    $this->success('操作成功',U("User/Product/transportation"));
		    return;
		 }
		 $area_info =  M('area')->where("topno=0")->select();
		  $this->assign('area_info',$area_info);
		 $this->assign('set',$data);
	     $this->display();
	 
	 
	 }
}
?>