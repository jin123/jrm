<?php
class GroupbuyAction extends CommonAction{
	function __construct(){
		parent::__construct();
	}
	
	//列表
	public function index(){
		$data  = D('Groupbuy');
		$where=array();
		$where['token']=session('token');
		$count = $data->where($where)->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();	
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this->assign('token',session('token'));
		$this -> display();
	}
	
	//添加微团购
	public function add(){
		if (IS_POST){
            $_POST['keyword']=$_POST['gjz'];
            $_POST['kssj']=strtotime($_POST['kssj']);
            $_POST['jssj']=strtotime($_POST['jssj']);            
		   $this->all_insert('Groupbuy');
		}else{
		    $this -> display();
		}			
	}
	
	//修改
	public function set(){
	    
		$data   = D('Groupbuy');
		$id     = trim($this->_get('id'));
		$where['id'] = $id;

		if (IS_POST){
		    $_POST['id']=$id;
		    $_POST['keyword']=$_POST['gjz'];
		    $_POST['kssj']=strtotime($_POST['kssj']);
		    $_POST['jssj']=strtotime($_POST['jssj']);
		    
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
            $this->all_save();
            
		}else{	
		    $list   = $data->where($where)->find();
		    $this -> assign('vo',$list);
			$this->assign('set',$check);
			$this->display('Groupbuy/add');
		}
		
	}
	
	//删除
	public function delete(){
		$check = M('Groupbuy')->field('id')->where(array('id'=>$this->_get('id')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Groupbuy')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	
	//批量删除
	public function del_list(){
		$groupbuy=D('Groupbuy');
		$getid=$this->_request('id');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$groupbuy->where(array('id'=>array('IN',$getids)))->delete();
		if($result){
		    D('Groupbuylist')->where(array('gid'=>array('IN',$getids)))->delete();
		    M('keyword')->where(array('pid'=>array('IN',$getids),'token'=>session('token')))->delete();
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	
	//微团购活动
	public function groupbuylist(){
		$id     = trim($this->_get('id'));

		$data = D('Groupbuylist');
		$where['gid'] = $id;
		$count = $data->where($where)->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where($where)->order("id desc")
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
					
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Groupbuy/buylist');
	}
	//删除
	public function deletes(){
		$check = M('Groupbuylist')->field('id')->where(array('id'=>$this->_get('id')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Groupbuylist')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	

	
	public function order(){
		$data=M('Groupbuylist');
		$id=$this->_get('id');
		$orders=$data->order('ctime DESC')->where(array('id'=>$id))->find();
		
		
		$datas  = D('Logistics');
		$wher['listid'] = $id;
		$lists  = $datas->where($wher)->find();
		$this->assign('o',$lists);

		$this->assign('data',$orders);
		$this->display('Groupbuy/order');
	}
	
	public function logistics(){
		$data=M('Logistics');
		$id  = trim($this->_get('id'));
		$where['id']=$id;
		$logistics=$data->where($where)->find();
		$this->assign('data',$logistics);
		
		if(!empty($logistics['logisticgs'])&& !empty($logistics['logisticsn'])){
			$data=array($logistics['logisticgs'],$logistics['logisticsn']);
			$kuaidi=$this->kuaidi($data);
			$kuaidi=str_replace("\n", "<br/>", $kuaidi);
		}else {
			$kuaidi='请先添写订单号';
		}
		$this->assign("kuaidi",$kuaidi);
		$this->display('Groupbuy/logistics');
	}
	
	/**
	 * 快递查询
	 * @param Array $data 快递公司|快递单号
	 * @return string
	 */
	public function kuaidi($data){
		$data = array_merge($data);
		$str  = file_get_contents('http://www.weinxinma.com/api/index.php?m=Express&a=index&name=' . $data[0] . '&number=' . $data[1]);
		return $str;
	}
	
	public function orderset(){
		$data   = D('Groupbuylist');
		$where['id'] = trim($this->_post('id'));
		
		$datas  = D('Logistics');
		$wher['listid'] = $this->_post('id');
		
		if (IS_POST){		    
		    
			$check = $data->where($where)->find();	
					
			if ($check == false )$this->error('非法操作');

			$_POST['uptime']=time();
			
			if ($data -> create($_POST)){
				if ($data->where($where)->save()){				    
				    $checks = $datas->field('id')->where($wher)->find();
				    $_POST['listid'] = $_POST['id'];
				    if ($checks == false ){
				        $datas->add($_POST);
				    }else{
				        $datas->where($wher)->save($_POST);
				    }
					$this->success('修改成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}			
			
		}else{
		  $this->display('Groupbuy/order');
		}
	}
	
	
	
	
}