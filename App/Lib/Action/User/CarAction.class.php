<?php
class CarAction extends CommonAction{
	//显示关键字回复页
	/* public function index(){
		$token = $_SESSION['token'];
		$mod = M('Car_keyword');
		$list = $mod->where(array('token'=>$token))->find();
		if($list){
			$this->assign('list',$list);
		}
		$this->display();
	} */
	
	//汽车管理
	public function index(){
		$token = $_SESSION['token'];
		$mod = M('Car_car')->where(array('token'=>$token))->select();
		$this->assign('list',$mod);
		$this->display('Car/car');
	}
	
	//汽车添加
	public function carset(){

		$where['id'] = $this->_get('id');
		$where['token'] = session('token');
		$result = M('Car_car')->where($where)->find();
		if(IS_POST){
			if ($result == null) {
				$this->all_insert('Car_car');
            } else {
				$this->all_save('Car_car');
            }	
		}else{
			$this->assign('list',$result);
			$this->display();
		}		
	}
	
	/* //汽车修改
	public function cared(){
		$id = $_GET['id'];
		$mod = M('Car_car')->where(array('id'=>$id))->find();
		$this->assign('list',$mod);
		$this->display();
	}
	//执行修改
	public function cardd(){
		$id = $_POST['id'];
		$mod = M('Car_car');
		if($mod->save($_POST)){
			$this->success('修改成功',U('Car/car'));
		}else{
			$this->error('修改失败');
		}
	}

	//执行添加
	public function caraded(){
		$mod = M('Car_car');
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/car'));
		}else{
			$this->error('添加失败');
		}
	} */
	
	//删除汽车
	public function cardel(){
		$id = $_GET['id'];
		$mod = M('Car_car');
		if($mod->where(array('id'=>$id))->delete()){
			$this->success('删除成功',U('Car/car'));
		}else{
			$this->error('删除失败');
		}
	}
	
	//重置关键字回复操作
	public function keyed(){
		$token = $_SESSION['token'];
		$li = M('Car_keyword');
		$mod = $li->where(array('token'=>$token))->find();
		if($mod){
			$list = $li->where(array('token'=>$token))->save($_POST);
			if($list){
				$this->success('修改成功',U('Car/index'));
			}else{
				$this->error('修改失败');
			}
		}else{
			if($li->add($_POST)){
				$this->success('设置成功',U('Car/index'));
			}else{
				$this->error('设置失败');
			}
		}
	}
	
	//品牌显示
	public function pinpai(){
		$pid = $_GET['id'];
		$token = $_SESSION['token'];
		$list = M('Car_pinpai')->where(array('token'=>$token,'uid'=>$pid))->select();
		$this->assign('pid',$pid);
		$this->assign('list',$list);
		$this->display();
	}
	
	//品牌添加
	public function ppadd(){
		$pid = $_GET['pid'];
		$this->assign('pid',$pid);
		$this->display();
	}
	
	//执行添加
	public function ppaded(){
		$pid = $_POST['uid'];
		$mod = M('Car_pinpai');
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/pinpai',array('id'=>$pid)));
		}else{
			$this->error('添加失败');
		}
	}
	
	//品牌修改
	public function edit(){
		$mod = M('Car_pinpai');
		$list = $mod->where(array('id'=>$_GET['id']))->find();
		$this->assign('vo',$list);
		$this->display();
	}
	
	//执行修改
	public function ed(){
		$id = $_POST['id'];
		$pid = $_POST['pid'];
		$mod = M('Car_pinpai');
		if($mod->where(array('id'=>$id))->save($_POST)){
			$this->success('修改成功',U('Car/pinpai',array('id'=>$pid)));
		}else{
			$this->error('修改失败');
		}
	}
	//品牌删除
	public function del(){
		$mod = M('Car_pinpai');
		$pid = $_GET['pid'];
		$list = $mod->where(array('id'=>$_GET['id']))->delete();
		if($list){
			$this->success('删除成功',U('Car/pinpai',array('id'=>$pid)));
		}else{
			$this->error('删除失败');
		}
	}
	
	//车系显示
	public function chexi(){
		import('@.ORG.Page');
	    $chexi=M('Car_chexi')->where('pid='.$_GET['id']);
	    $count=$chexi->count();
		$page=new Page($count,25);
		$show = $page->show();
		$name = $chexi->alias('A')
				->join('tp_car_pinpai as B on A.pid=B.id')
				->order('A.sort desc')
				->field('A.id,A.name as cname,A.pic,A.sort,B.name as pname,B.id as pid,B.uid')
				->where('A.pid='.$_GET['id'])
				->limit($page->firstRow.','.$page->listRows)->select();
		$pid = $_GET['id'];
		$this->assign('pid',$pid);
		$this->assign('count', $count);
		$this->assign('list',$name);
		$this->assign('page',$show);
		$this->display();
	}
	
	//车系添加
	public function chexiadd(){
		$mod = M('Car_pinpai');
		$list = $mod->where('id='.$_GET['pid'])->find();
		$this->assign('pid',$pid);
		$this->assign("vo",$list);
		$this->display();
	}
	
	//执行车系添加
	public function cxinsert(){
		$mod = M('Car_chexi');
		$li = $mod->add($_POST);
		if($li){
			$lis = $mod->where(array('id'=>$li))->find();
			$pid = $lis['pid'];
			$this->success('添加成功',U('Car/chexi',array('id'=>$pid)));
		}else{
			$this->error('添加失败');
		}
	}
	
	//车系修改
	public function cxedit(){
		$mod = M('Car_chexi')->find($_GET['id']);
		$this->assign("vo",$mod);
		$this->display();
	}
	
	//执行车系修改
	public function cxupdate(){
		$pid = $_POST['pid'];
		$mod = M('Car_chexi');
		if($mod->where(array('id'=>$_POST['id']))->save($_POST)){
			$this->success('修改成功',U('Car/chexi',array('id'=>$pid)));
		}else{
			$this->error('修改失败');
		}
	}
		
	//车系删除操作
	public function cxdel(){
		$mod = M('Car_chexi');
		$pid = $_GET['pid'];
		$list = $mod->where(array('id'=>$_GET['id']))->delete();
		if($list){
			$this->success('删除成功',U('Car/chexi',array('id'=>$pid)));
		}else{
			$this->error('删除失败');
		}
	}
	//车型显示
	public function cxing(){
		import('@.ORG.Page');
	    $chexi=M('Car_chexing')->where('pid='.$_GET['id']);
	    $count=$chexi->count();
		$page=new Page($count,25);
		$show = $page->show();
		$mod = M('Car_chexing');
		$list = $mod->alias('A')
		->join("tp_car_chexi AS B ON A.pid=B.id")
		->join("tp_car_pinpai AS C ON B.pid=C.id")
		->field("A.nk,A.name as cname,A.bj,A.sort,A.id,B.name as xname,B.id as pid,B.uid,C.name as pname")
		->where('A.pid='.$_GET['id'])
		->select();
		$pid = $_GET['id'];
		$this->assign('pid',$pid);
		$this->assign('page',$show);
		$this->assign('info',$list);
		$this->display();
	}
	
	//车型添加
	public function xingadd(){
		$mo = M('Car_chexi');
		$list = $mo->where(array('id'=>$_GET['id']))->find();
		$ppid = $list['pid'];//品牌id
		$mod = M('Car_pinpai');
		$lists = $mod->where('id='.$ppid)->find();
		$this->assign("list",$list);
		$this->assign("lists",$lists);
		$this->display();
	}
	
	//执行车型添加
	public function xinginsert(){
		$pid = $_POST['pid'];
		$mod = M('Car_chexing');
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/cxing',array('id'=>$pid)));
		}else{
			$this->error('添加失败');
		}
	}
	
	//前台logo选择
	public function logo(){
		$uid = $this->_get('id');
		$token = $_SESSION['token'];
		$mod = M('Car_logo');
		$res = $mod->where(array('uid'=>$uid))->find();
		$this->assign('uid',$uid);
		$this->assign('token',$token);
		$this->assign('list',$res);
		$this->display();
	}
	
	//处理logo
	public function logoed(){
		$uid = $_POST['uid'];
		$id = $_POST['id'];
		$res = M('Car_logo');
		$mod = $res->where(array('uid'=>$uid))->find;
		if($id){
			if($res->where(array('id'=>$id))->save($_POST)){
				$this->success('修改成功',U('Car/logo',array('id'=>$uid)));
			}else{
				$this->error('修改失败');
			}
		}else{
			if($res->add($_POST)){
				$this->success('设置成功',U('Car/logo',array('id'=>$uid)));
			}else{
				$this->error('设置失败');
			}
		}
	}
	//车型修改
	public function cxinged(){
		$id = $_GET['id'];
		$vo = M('Car_chexing')->where(array('id'=>$id))->find();
		$this->assign('vo',$vo);
		$pid = $_GET['pid'];
		$this->assign('pid',$pid);
		$this->display();
	}
	
	//执行修改
	public function carxx(){
		$id = $_POST['id'];
		$pid = $_POST['pid'];
		$mod = M('Car_chexing');
		if($mod->where(array('id'=>$_POST['id']))->save($_POST)){
			$this->success('修改成功',U('Car/cxing',array('id'=>$pid)));
		}else{
			$this->error('修改失败');
		}
	}
	
	//车型删除
	public function cxd(){
		$id = $_GET['id'];
		$pid = $_GET['pid'];
		$mod = M('Car_chexing');
		$list = $mod->where(array('id'=>$id))->delete();
		if($list){
			$this->success('删除成功',U('Car/cxing',array('id'=>$pid)));
		}else{
			$this->error('删除失败');
		}
	}
	//显示全景图
	public function cxingt(){
		$xid =$_GET['id'];
		$mod = M('Car_chexing_full_view');
		$list = $mod->where('xid='.$xid)->select();
		$this->assign('list',$list);
		$this->assign("xid",$xid);
		$this->display();
	}
	
	//添加全景图
	public function tadd(){
		$pid = $_GET['pid'];
		$this->assign('pid',$pid);
		$this->display();
	}
	
	//执行添加
	public function taddd(){
		$xid = $_POST['xid'];
		$mod = M('Car_chexing_full_view');
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/cxingt',array('id'=>$xid)));
		}else{
			$this->error('添加失败');
		}
	}
	//修改全景图
	public function tedit(){
		$id = $_GET['id'];
		$pid = $_GET['pid'];
		$mod = M('Car_chexing_full_view')->where(array('id'=>$id))->find();
		if($mod){
			$this->assign('vo',$mod);
		}
		$this->assign('pid',$pid);
		$this->display();
	}
	
	//执行修改
	public function ttedd(){
		$xid = $_POST['xid'];
		$mod = M('Car_chexing_full_view');
		if($mod->where(array('id'=>$_POST['id']))->save($_POST)){
			$this->success('修改成功',U('Car/cxingt',array('id'=>$xid)));
		}else{
			$this->error('修改失败');
		}
	}
	
	//删除全景图
	public function td(){
		$id = $_GET['id'];
		$pid = $_GET['pid'];
		$mod = M('Car_chexing_full_view');
		$list = $mod->where(array('id'=>$id))->delete();
		if($list){
			$this->success('删除成功',U('Car/cxingt',array('id'=>$pid)));
		}else{
			$this->error('删除失败');
		}
	}
	
	//显示销售
	public function xs(){
		$token = $_SESSION['token'];
		$id = $_GET['id'];
		$mod = M('Car_xiaoshou')->where(array('token'=>$token,'uid'=>$id))->select();
		$this->assign('uid',$id);
		$this->assign('list',$mod);
		$this->display();
	}
	
	//添加销售
	public function xsadd(){
		$uid = $_GET['uid'];
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//执行销售添加
	public function xsaddd(){
		$mod = M('Car_xiaoshou');
		$uid = $_POST['uid'];
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/xs',array('id'=>$uid)));
		}else{
			$this->error('添加失败');
		}
	}
	
	//修改销售
	public function xsedit(){
		$uid = $_GET['uid'];
		$id = $_GET['id'];
		$mod = M('Car_xiaoshou')->where(array('id'=>$id))->find();
		$this->assign('uid',$uid);
		$this->assign('vo',$mod);
		$this->display();
	}
	
	//执行修改
	public function xsedits(){
		$uid = $_POST['uid'];
		$id = $_POST['id'];
		$mod = M('Car_xiaoshou');
		if($mod->where(array('id'=>$id))->save($_POST)){
			$this->success('修改成功',U('Car/xs',array('id'=>$uid)));
		}else{
			$this->error('修改失败');
		}
	}
	//删除销售
	public function xsdel(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		if(M('Car_xiaoshou')->where(array('id'=>$id))->delete()){
			$this->success('删除成功',U('Car/xs',array('id'=>$uid)));
		}else{
			$this->error('删除失败');
		}
	}
	
	//显示保养
	public function by(){
		$uid = $_GET['id'];
		import('@.ORG.Page');
	    $chexi=M('Car_yuyue_baoyang')->where(array('uid'=>$uid));
	    $count=$chexi->count();
		$page=new Page($count,25);
		$show = $page->show();
		$mod = M('Car_yuyue_baoyang');
		$list = $mod->alias('A')
		->join("tp_car_pinpai AS B ON A.pid=B.id")
		->join("tp_car_chexi AS C ON A.xid=C.id")
		->join("tp_car_chexing AS D ON A.xxid=D.id")
		->field("A.mileage,A.num,A.year,A.type,A.ytime,A.qtime,A.description,A.status,A.id,B.name as pname,C.name as cname,D.name as dname")
		->where("A.uid=$uid")
		->select();
		$this->assign('uid',$uid);
		$this->assign('page',$show);
		$this->assign("list",$list);
		$this->display();
	}
	
	//添加预约
	public function byadd(){
		$mo = M('Car_pinpai');
		$li =$mo->where(array('uid'=>$_GET['uid']))->select();
		$mod = M('Car_chexi');
		$list = $mod->where(array('uid'=>$_GET['uid']))->select();
		$m  = M('Car_chexing');
		$x = $m->where(array('uid'=>$_GET['uid']))->select();
		$uid = $_GET['uid'];
		$this->assign('uid',$uid);
		$this->assign("list",$list);
		$this->assign("li",$li);
		$this->assign('x',$x);
		$this->display();
	}
	
	//执行添加
	public function byed(){
		$uid = $_POST['uid'];
		$mod = M('Car_yuyue_baoyang');
		if($mod->add($_POST)){
			$this->success('添加成功',U('Car/by',array('id'=>$uid)));
		}
	}
	
	//显示预约用户信息
	public function chuli(){
		$uid = $_GET['id'];
		$mod = M('Car_yuyue_baoyang');
		$list =$mod->where('id='.$_GET['id'])->find();
		$this->assign("list",$list);
		$this->assign("id",$uid);
		$this->display();
	}
	
	//显示预约用户信息
	public function cledit(){
		$uid = $_GET['id'];
		$mod = M('Car_yuyue_baoyang');
		$list = $mod->where('id='.$_GET['id'])->find();
		$this->assign('uid',$uid);
		$this->assign("list",$list);
		$this->display();
	}
	
	//执行预约处理
	public function yychuli(){
		$uid = $_POST['uid'];
		$mod = M('Car_yuyue_baoyang');
		if($mod->where('id='.$_POST['id'])->save($_POST)){
			$this->success('修改成功',U("Car/chuli",array('id'=>$uid)));
		}else{
			$this->error('修改失败');
		}
	}
	
	//显示试驾
	public function sj(){
		$uid = $_GET['id'];
		import('@.ORG.Page');
	    $chexi=M('Car_yuyue_shijia')->where(array('uid'=>$_GET['id']));
	    $count=$chexi->count();
		$page=new Page($count,25);
		$show = $page->show();
		$mod = M('Car_yuyue_shijia');
		$list = $mod->alias('A')
		->join("tp_car_chexi AS B ON A.xid=B.id")
		->join("tp_car_district AS C ON A.address=C.id")
		->field("A.id,A.address,A.yjtime,A.budget,A.name as uname,A.tel,A.email,A.static,B.name as xname,C.name as cname")
		->where(array('A.uid'=>$uid))
		->select();
		$this->assign('uid',$uid);
		$this->assign("page",$show);
		$this->assign("list",$list);
		$this->display();
	}
	
	//显示处理试驾页面
	public function sjedit(){
		$uid = $_GET['uid'];
		$mod = M('Car_yuyue_shijia');
		$list = $mod->where('id='.$_GET['id'])->find();
		$this->assign('uid',$uid);
		$this->assign("vo",$list);
		$this->display();
	}
	
	//执行试驾处理
	public function sjed(){
		$uid = $_POST['uid'];
		$mod = M('Car_yuyue_shijia');
		if($mod->where('id='.$_POST['id'])->save($_POST)){
			$this->success('修改成功',U("Car/sj",array('id'=>$uid)));
		}else{
			$this->error('修改失败');
		}
	}
	
	//车主关怀界面显示
	public function gh(){
		$uid = $_GET['id'];
		$mod = M('Car_guanhuai');
	    $token = $_SESSION['token'];
	    $list = $mod->where(array('token'=>$token))->find();
		$this->assign('uid',$uid);
		$this->assign('list',$list);
		$this->display();
	}
	//执行关怀修改
	public function ghed(){
		$uid = $_POST['uid'];
		if($_POST['id']==''){
			$mod = M('Car_guanhuai');
			if($mod->add($_POST)){
				$this->success('设置成功',U('Car/gh',array('id'=>$uid)));
			}else{
				$this->error('设置失败');
			}
		}else{
			$mod = M('Car_guanhuai');
			if($mod->where(array('id'=>$_POST['id']))->save($_POST)){
				$this->success('重置成功',U('Car/gh',array('id'=>$uid)));
			}else{
				$this->error('重置失败');
			}
		}
	}
	
	//显示实用工具
	public function tool(){
		$uid = $_GET['id'];
		$mod = M('Car_tool');
		$token = $_SESSION['token'];
		$list = $mod->where(array('token'=>$token))->find();
		$this->assign('vo',$list);
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//执行实用工具状态修改
	public function tooled(){
		if($_POST['id']){
			$uid = $_POST['uid'];
			$mod = M('Car_tool');
			$list = $mod->where(array('id'=>$_POST['id']))->save($_POST);
			if($list){
				$this->success('重置成功',U('Car/tool',array('id'=>$uid)));
			}else{
				$this->error('重置失败');
			}
		}else{
			if(M('Car_tool')->add($_POST)){
				$this->success('设置成功',U('Car/tool',array('id'=>$uid)));
			}else{
				$this->error('设置失败');
			}
		}
	}
	
	//公共显示类文件
	public function cheche($name){
		import('@.ORG.Page');
		$this->name=M($name);
		$count= $this->name->count();
		$page=new Page($count,25);
		$show   = $page->show();
		$list= $this->name->order('sort desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	
	//公共添加类文件
	 public function cheadd($car_name,$name){	
			
		$data=D($car_name);
		if(IS_POST){
			if($data->create()!=false){
				 if($id=$data->add()){
						$data1['pid']=$id;
						$data1['module']='car';
						$data1['keyword']=$this->_post('keyword');
						M('Car_Keyword')->add($data1);
					    $this->success('创建成功',U("Car/$name"));
					}else{
						$this->error('服务器繁忙,请稍候再试');
					}
				}else{
					$this->error($data->getError());
				}
		}else{
			$this->display();	
		}	
	}
	
	// 公共修改类文件
    public function cheedit($car_name,$name,$hanhui){
      $data= M($car_name);
      $id=$_REQUEST['id'];
      $where['id']=$_REQUEST['id'];  
		  if (IS_POST) {
				  if($data->create()!=false){
					 if($data->where($where)->save($_POST)){
						$data1['pid']=$id;
						$data1['module']='car';
						$data1['keyword']=$_REQUEST['keyword'];
						M('Keyword')->save($data1);
						$this->success('修改成功',U("Car/$name"));
					 }else{
						$this->error('服务器繁忙,请稍候再试');
						}
				  }else{
					 $this->error($data->getError());
				  }   
		 }else{
			$list=$data->where($where)->find();	
			$this->assign('vo',$list);
			$this->display("$hanhui");
		  }
    }
	
	 //公共删除类文件
	public function chedel($car_name,$name){
	 	$where['id']=$_REQUEST['id'];  
    	$data= M("$car_name"); 
    	$dele=$data->where($where)->delete();
    	if($car_name){
    		$this->success('删除成功',U("Car/$name"));
    	}else{
    	    $this->error('删除失败');
    	}
    }
	
	//留言显示界面
	public function liuyan(){
		$uid = $this->_get('id');
		$mod = M('Car_wish')->where(array('uid'=>$uid))->select();
		$this->assign('list',$mod);
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//处理留言
	public function wished(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		$vo = M('Car_wish')->where(array('id'=>$id))->find();
		$this->assign('vo',$vo);
		$this->assign('uid',$uid);
		$this->display();
	}
	
	//执行留言处理
	public function wishedd(){
		$uid = $_GET['uid'];
		$id = $_POST['id'];
		if(M('Car_wish')->where(array('id'=>$id))->save($_POST)){
			$this->success('处理成功',U('Car/liuyan',array('id'=>$uid)));
		}else{
			$this->error('处理失败');
		}
	}
	
	//删除留言
	public function wishdel(){
		$uid = $_GET['uid'];
		if(M('Car_wish')->where(array('id'=>$_GET['id']))->delete()){
			$this->success('删除成功',U('Car/liuyan',array('id'=>$uid)));
		}else{
			$this->error('删除失败');
		}
	}
}