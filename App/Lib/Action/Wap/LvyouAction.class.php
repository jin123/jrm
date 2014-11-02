<?php
class LvyouAction extends Action
{
    public function index()
    {
    	header("Content-Type: text/html; charset=UTF-8");
    	/*$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}*/
		$data  = D('Lvyou_poster'); 
		$poster=$data->order('id desc')->select();
		$all=$data->field('id,pic1')->order('id desc')->select();
		   $pics=array();
	     	foreach($all as $va){
		       $pics['pic1']=$va['pic1'];

		 	}
		 $pic1=implode(',',$pics);
		 $alls=explode(',',$pic1);
		 $this->assign('alls',$alls);
		
		if($poster==false){$this->error('服务器繁忙');}
		$this->assign('poster',$poster);
		$this->display('Lvyou/index');
    }
    
    //旅游区简介
    public function lvyou_index(){
    	$lvyou=M('Lvyou_intro')->order('id desc')->select();
		//if($lvyou==false){ }
		$this->assign('lvyou',$lvyou);
		$this->display('Lvyou/lvyou_index');
    }
    
 
    //景点
    public function scenery(){
    	
    	$data = new Model();
		$scenery = $data->table('tp_lvyou_scenery a, tp_lvyou_scenic b')
					 ->where('a.scenicid = b.id')
					 ->field('a.*,b.name,b.id as bid')
					 ->order('a.sort desc' )
					 ->select();
		$this->assign('scenery',$scenery);
		$this->display('Lvyou/scenery');
    }
    
    //景点图片
    public function piclist(){
    	$id=$this->_get('id');
    	$where['id']=$id;
    	$piclist=M('Lvyou_scenery')->where($where)->order('id desc')->select();
    	if($piclist==false){ }
		$this->assign('piclist',$piclist);
		$this->display('Lvyou/piclist');
    }
    
    //风景相册
    public function plist(){
		/*$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}*/
		//$token = $this->_get('token');
		$info=M('Photo')->field('title')->where(array('id'=>5))->find();
		$photo_list=M('Photo_list')->where(array('pid'=>5,'status'=>1))->select();
		$this->assign('info',$info);
		$this->assign('photo',$photo_list);
		$this->display('Photo/plist');
		
	
	}
   
    
    //印象点评
    public function comment(){
    	$comment=M('Lvyou_comment')->order('id desc')->select();
		//if($comment==false){ }
		$this->assign('comment',$comment);
		$comment1=M('Lvyou_impress')->where("isshow='1'")->order('id desc')->select();
		//if($comment==false){ }
		$this->assign('comment1',$comment1);
    	$this->display('Lvyou/impress');	
    }
    
    //预约门票
    public function yuyue(){  	
    		$intro=M('Lvyou_intro')->order('id desc')->select();
    		$this->assign('intro',$intro);
    		//var_dump($intro);exit;
    		$yuyue=M('Lvyou_yuyue')->order('id desc')->select();
    		$this->assign('yuyue',$yuyue);
	    	if (IS_POST){
				$name   = trim($this->_post('name'));
				$tel    = trim($this->_post('tel'));
				$date   = trim(strtotime($this->_post('date')));
				$zdyzd  = trim($this->_post('zdyzd'));
				$zdyxlk = trim($this->_post('zdyxlk'));
				$remark = trim($this->_post('remark'));
				$times  = time();
				
				$data = D('Lvyou_yuyue');
				$where['name']   = $name;
				$where['tel']    = $tel;
				$where['date']   = $date;
				$where['times']  = $times;
				$where['zdyzd']  = $zdyzd;
				$where['zdyxlk'] = $zdyxlk;
				$where['remark'] = $remark;
		    	if ($data -> create()){
						$result = $data->add($where);
						if ($result){
							$this->success('预约成功',U('Lvyou/dingdan'));
						}else{
							$this->error('服务器繁忙,请稍候再试');
						}
					}else{
						$this->error($data->getError());
					}
	    	}else{
	    		$this->display('Lvyou/yuyue');
			}
		
    }
     
    //我的订单
    public function dingdan(){
    	$yuyue=M('Lvyou_yuyue')->order('id desc')->select();
    	$m=M('Lvyou_yuyue');
    	$count=$m->count();
    	$Page  = new Page($count,12);
		$show  = $Page->show();	
		$this -> assign('page',$show);
		if($comment==false){ }
		$this->assign('yuyue',$yuyue);
		$this->assign('count',$count);
    	$this->display('Lvyou/dingdan');
    }
    
    public function edit(){ 
    	$yuyue=M('Lvyou_yuyue')->order('id desc')->select(); 
    	$m=M('Lvyou_yuyue');
    	$count=$m->count();   
    	$this->assign('count',$count);   
		$ddset=D('Lvyou_yuyue');
		$id     = trim($this->_get('id'));
		$data=$ddset->find($id);
		if($data){
			$this->data=$data;
		}else{
			$this->error("数据连接错误");
		}
		$this->assign('vo',$data);
		$this->display('Lvyou/ddset');
	}
    
    //修改订单
    public function ddset(){
    	$yuyue=M('Lvyou_yuyue')->order('id desc')->select();   	
    	$this->assign('yuyue',$yuyue);
    	$ddset=D('Lvyou_yuyue');
    	$id     = trim($this->_post('id'));
	    if($_POST){
			if($ddset->create()){
				$result=$ddset->where('id='.$id)->save();
				if($result){
					$this->success('内容修改成功',U('Lvyou/dingdan'));
				}else{
					$this->error('内容修改失败');
				}
			}else{
				$this->error($ddset->getError());
			}
		}
    }
    
    
    
    
    
    
   //风景详情
  // public function detail(){
      // $this->display('Lvyou/detail');
  // }
	
}