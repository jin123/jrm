<?php 

class CarAction extends Action{
    public $wecha_id;
	public $token;
    public function __construct(){
		parent::__construct();
        $this->wecha_id = $this->_get('wecha_id');
        $this->assign('wecha_id', $this->wecha_id);
        $this->token = $this->_get('token');
    }
	
	public  function index(){
		$wecha_id=$this->wecha_id;
		$token=$this->token;
		$id = $this->_get('id');
		$mod = M('Car_car')->where(array('id'=>$id))->find();
		$mo = M('Car_logo')->where(array('uid'=>$id))->find();

		$this->assign('vo',$mo);
		$this->assign('list',$mod);
		$this->assign('id',$id);
		$this->display();
	}
	
	//关于我们
	public  function jianjie(){
		$uid = $this->_get('uid');
		$token = $_SESSOION['token'];
		$mod = M('Car_car');
		$list = $mod->where(array('uid'=>$uid))->find();
		$this->assign('list',$list);
		$this->display();
		
	}
	
	//车型浏览
	public  function chexing(){
	 $uid = $this->_get('uid');
	 $mod = M('Car_pinpai');
	 $list = $mod->where(array('uid'=>$uid))->select();
	 $this->assign('uid',$uid);
	 $this->assign("list",$list);
	 $this->display();
	}
	//车系
	public function chexinglist(){
	  $uid = $this->_get('uid');
	  $pid = $this->_get('id');
	  $data=M('car_chexi');
	  $where['pid']=$_GET['id'];
	  $names=$data->where($where)->order('sort desc')->select();
	  $this->assign('uid',$uid);
	  $this->assign('pid',$pid);
	  $this->assign('names',$names);
	  $this->display();
	}
	
	public function chexingxq(){
		$pid = $_GET['id'];//车系id
		$ppid = $_GET['pid'];//品牌id
		$uid = $_GET['uid'];//车城id
		$data = M('Car_chexing');
		$list = $data->where('pid='.$pid)->find();
		$this->assign('pid',$pid);
		$this->assign('uid',$uid);
		$this->assign('ppid',$ppid);
		$this->assign("list",$list);
		$this->display();		
	}
	
	public function item(){
		$xid = $_GET['id'];
		$mod = M('Car_chexing_full_view');
		$list = $mod->where('xid='.$xid)->find();
		$this->assign("list",$list);
		$this->display();
	}
	
	//实用工具
	public  function gongju(){
		$uid = $this->_get('uid');
		$mod = M('Car_tool')->where(array('uid'=>$uid))->find();
		$this->assign('list',$mod);
		$this->display();
		
	}
	
	
	public  function xiaoshou(){
		$uid = $this->_get('uid');
		$name=M('Car_xiaoshou');
		  $shouqian=$name->where(array('type'=>1,'uid'=>$uid))->select();
		  $shouqians=$name->where(array('type'=>2,'uid'=>$uid))->select();
		  $this->assign('va',$shouqian);
		  $this->assign('vas',$shouqians);
		  $this->display();
		
	}
	
	//最新汽车
	public  function zuixin(){
		$uid = $this->_get('uid');
		$mod = M('Car_pinpai')->where(array('uid'=>$uid))->order('sort desc')->select();
		$this->assign('va',$mod);
		$this->display();
	}
	//最新汽车个体显示
	public  function zuixingxq(){
		$name=M('car_pinpai');
		$where['id']=$_GET['id'];
		$list=$name->where($where)->find();
		$this->assign('list',$list);
		$this->display();
		
	}
	
	//预约试驾
 /*  public  function shijia(){
	 $token = $_SESSION['token'];
		
		$mod = M('Car_yuyue_shijia')->where(array('token'=>$token))->find();
		//如果用户预约过
		if($mod){
			$res = M('Car_yuyue_shijia');
			$name = $res->alias('A')
			->join('tp_car_pinpai as B on A.pid=B.id')
			->join('tp_car_chexi as C on A.xid=C.id')
			->join('tp_car_chexing as D on A.xxid=D.id')
			->join('tp_car_district as E on A.address=E.id')
			->field('A.id,A.name as aname,A.yjtime,A.budget,A.tel,A.email,A.bz,B.id as pid,B.name as bname,C.id as xid,C.name as cname,D.id as xxid,D.name as dname,E.id as eid,E.name as ename')
			->where("A.token='{$token}'")
			->find();
			$this->assign('list',$name);
			if($_GET['issue']==3){
				$this->error('您已经预约过，请不要重复预约');
			}
		//如果用户是选择好车型提交预约信息的
		}elseif(($_GET['id']!="") && ($_GET['issue']==2)){  
			$id = $_GET['id'];
			$esa = M('Car_chexing');
			$esb = $esa->alias('A')
			->join('tp_car_pinpai as B on A.ppid=B.id')
			->join('tp_car_chexi as C on A.pid=C.id')
			->field('A.id as xxid,A.name,B.id as pid,B.name as bname,C.id as xid,C.name as cname')
			->where('A.id='.$id)
			->find();
			$esh = M('Car_district')->field('id,name')->select();
			$this->assign('esh',$esh);
			$this->assign('esc',$esb);
			if($_GET['issue']==3){
				$mod = M('Car_yuyue_shijia');
				$data['uid'] = $userid;
				$list = $mod->data($data)->add($_POST);
				if($list){
					$ss = M('Car_yuyue_shijia');
					$se = $ss->where('id='.$list)->find();
					$this->assign('list',$se);
					$this->success('添加成功',U('Car/shijia',array('id'=>$id,'ppid'=>$ppid,'uid'=>$uid)));
				}
			}
		//如果用户没有预约过
		}else{
			$esd = M('Car_pinpai')->field('id,name')->select();
			$ese = M('Car_chexi')->field('id,name')->select();
			$esf = M('Car_chexing')->field('id,name')->select();
			$esh = M('Car_district')->field('id,name')->select();
			$this->assign('esd',$esd);
			$this->assign('ese',$ese);
			$this->assign('esf',$esf);
			$this->assign('esh',$esh);
			if($_GET['issue']==3){
				$mod = M('Car_yuyue_shijia');
				$data['uid'] = $userid;
				$list = $mod->data($data)->add($_POST);
				if($list){
					$ss = M('Car_yuyue_shijia');
					$se = $ss->where('id='.$list)->find();
					$this->assign('list',$se);
					$this->success('添加成功',U('Car/shijia',array('id'=>$id,'ppid'=>$ppid,'uid'=>$uid)));
				}
			}
		}
		if($_GET['issue']==4){
			$token= $_SESSION['token'];
				$ke = M('Car_yuyue_shijia');
				$k =$ke->where(array('token'=>$token))->find();
				if($k){
					switch($k['static']){
						case '0':
							$this->error('您的预约正在审核');
							break;
						case '1':
							$this->error('您成功预约，我们会在最早的时间联系您，请您保持手机畅通');
							break;
						case '2':
							$this->error('对不起，由于您提供的资料有误，您预约失败');
							break;
						}
				}else{
					$this->error('您还未预约呢！');
				}
		}
	$this->display();
	}
	*/
	public function shijia1(){
		$uid = $this->_get('id');
		$token = $_SESSION['token'];
		$list = M('Car_yuyue_shijia')->where(array('token'=>$token))->find();
		if($list){
			$pid = $list['pid'];//品牌id
			$lists = M('Car_pinpai')->where(array('id'=>$pid))->field('id,name,pic')->find();
			$xid = $list['xid'];//车系id
			$listss = M('Car_chexi')->where(array('id'=>$xid))->field('id,name')->find();
			$xxid = $list['xxid'];//车型id
			$listsss = M('Car_chexing')->where(array('id'=>$xxid))->field('id,name')->find();
			$did = $list['address'];
			$esh = M('Car_district')->where(array('id'=>$did))->field('id,name')->find();
			$this->assign('esh',$esh);
			$this->assign('lists',$lists);
			$this->assign('listss',$listss);
			$this->assign('listsss',$listsss);
			$this->assign('list',$list);
		}else{
			$mod = M('Car_chexing')->field('id,name')->select();
			$ppid = $mod['ppid'];//品牌id
			$pinpai = M('Car_pinpai')->field('id,name')->select();
			$pid = $mod['pid'];//车系id
			$chexing = M('Car_chexi')->field('id,name')->select();
			$eesh = M('Car_district')->field('id,name')->select();
			$this->assign('eesh',$eesh);
			$this->assign('mod',$mod);
			$this->assign('pinpai',$pinpai);
			$this->assign('chexing',$chexing);
		}
		$this->assign('uid',$uid);
		$this->assign('token',$token);
		$this->display();
	}
	
	public function shijia2(){
		$token = $_SESSION['token'];
		$xxid = $this->_get('id');
		$this->assign('xxxid',$xxid);
		$list = M('Car_yuyue_shijia')->where(array('token'=>$token))->find();
		$uid = $list['uid'];
		if($list){
			$pid = $list['pid'];//品牌id
			$lists = M('Car_pinpai')->where(array('id'=>$pid))->field('id,name')->find();
			$xid = $list['xid'];//车系id
			$listss = M('Car_chexi')->where(array('id'=>$xid))->field('id,name')->find();
			$xxid = $list['xxid'];//车型id
			$listsss = M('Car_chexing')->where(array('id'=>$xxid))->field('id,name,pic')->find();
			$did = $list['address'];
			$esh = M('Car_district')->where(array('id'=>$did))->field('id,name')->find();
			$this->assign('esh',$esh);
			$this->assign('lists',$lists);
			$this->assign('listss',$listss);
			$this->assign('listsss',$listsss);
			$this->assign('list',$list);
		}else{
			$mod = M('Car_chexing')->where(array('id'=>$xxid))->find();
			$ppid = $mod['ppid'];//品牌id
			$pinpai = M('Car_pinpai')->where(array('id'=>$pid))->field('id,name')->find();
			$pid = $mod['pid'];//车系id
			$chexing = M('Car_chexi')->where(array('id'=>$xid))->field('id,name')->find();
			$eesh = M('Car_district')->field('id,name')->select();
			$this->assign('eesh',$eesh);
			$this->assign('mod',$mod);
			
			$this->assign('pinpai',$pinpai);
			$this->assign('chexing',$chexing);
			
		}
		$this->assign('uid',$uid);
		$this->assign('token',$token);
		$this->display();
	}
	
	//操作试驾
	public function shijia3(){
		$uid = $_POST['xxxid'];
		$token = $_SESSION['token'];
		$mod = M('Car_yuyue_shijia')->where(array('token'=>$token))->find();
		if($mod){
			$this->error('您已经预约过，请不要重复预约');
		}else{
			$list = M('Car_yuyue_shijia')->add($_POST);
			if($list){
				$this->success('预约成功',U('Car/shijia2',array('id'=>$id)));
			}else{
				$this->error('对不起，服务器繁忙');
			}
		}
	}
	
		//操作试驾
	public function shijia6(){
		$uid = $_POST['uid'];
		$token = $_SESSION['token'];
		$mod = M('Car_yuyue_shijia')->where(array('token'=>$token))->find();
		if($mod){
			$this->error('您已经预约过，请不要重复预约');
		}else{
			$list = M('Car_yuyue_shijia')->add($_POST);
			if($list){
				$this->success('预约成功',U('Car/shijia2',array('id'=>$uid)));
			}else{
				$this->error('对不起，服务器繁忙');
			}
		}
	}
	//我的试驾
	public function shijia4(){
		$uid = $_GET['xxxid'];
		$id = $_GET['id'];
		$mod = M('Car_yuyue_shijia')->where(array('id'=>$id))->find();
		if($mod){
			switch($mod['static']){
				case '0':
					$this->success('您的预约正在审核中,请您耐心等待',U('Car/shijia2',array('id'=>$id)));
					break;
				case '1':
					$this->success('您的审核已经通过，我们会在最短时间内联系您',U('Car/shijia2',array('id'=>$id)));
					break;
				case '2':
					$this->error('很遗憾，您预约失败');
					break;
			}
		}
	}
	//我的试驾
	public function shijia5(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		$mod = M('Car_yuyue_shijia')->where(array('id'=>$id))->find();
		if($mod){
			switch($mod['static']){
				case '0':
					$this->success('您的预约正在审核中,请您耐心等待',U('Car/shijia1',array('id'=>$uid)));
					break;
				case '1':
					$this->success('您的审核已经通过，我们会在最短时间内联系您',U('Car/shijia1',array('id'=>$uid)));
					break;
				case '2':
					$this->error('很遗憾，您预约失败');
					break;
			}
		}
	}
	//城市显示
	public function load(){
		$mod = M("Car_district");
		$lis = $mod->where("upid={$_POST['upid']}")->select();
		echo json_encode($lis);
	}
	
	//车主关怀
   public  function chezhu(){
		$uid = $this->_get('uid');
		switch($_GET['issue']){
				case '1':
				case '0':
					$token = $_SESSION['token'];
					$yuyue = M('Car_yuyue_baoyang');
					$res = $yuyue->where(array('uid'=>$uid))->find();
					if($res){
						$pid = $res['pid'];
						$s = M('Car_pinpai')->where('id='.$pid)->field('id,name')->find();
						$xid = $res['xid'];
						$x = M('Car_chexi')->where('id='.$xid)->field('id,name')->find();
						$xxid = $res['xxid'];
						$xx = M('Car_chexing')->where('id='.$xxid)->field('id,name,pic')->find();
						$list = M('Car_guanhuai')->find();
						$this->assign('list',$list);
						$this->assign('s',$s);
						$this->assign('uid',$uid);
						$this->assign('x',$x);
						$this->assign('xx',$xx);
						$this->assign('res',$res);
					}else{
						$list = M('Car_guanhuai')->find();
						$this->assign('list',$list);
					}
				break;
		}
		$this->display();
	}
	
	
	public  function  chezhuxq(){
		$uid = $_GET['uid'];
		$my=M('Car_yuyue_baoyang');
		$token = $_SESSION['token'];
	    $list=$my->where(array('token'=>$token))->find();
		$this->assign('list',$list); 
			
		switch($_GET['issue']){
			case '2':
			$token = $_SESSION['token'];
			$mod = M('Car_yuyue_baoyang')->where(array('token'=>$token))->find();
			if($mod){
				$pid = $mod['pid'];
				$s = M('Car_pinpai')->where('id='.$pid)->field('id,name')->find();
				$xid = $mod['xid'];
				$x = M('Car_chexi')->where('id='.$xid)->field('id,name')->find();	
				$xxid = $mod['xxid'];
				$xx = M('Car_chexing')->where('id='.$xxid)->field('id,name,pic')->find();
				$this->assign('s',$s);//品牌
				$this->assign('x',$x);//车系
				$this->assign('xx',$xx);//车型
				$this->assign('list',$mod);
			}
			
		}
	if($_POST){
			if(empty($_POST['name'])){
				$this->error("请填写必选项。");
				exit;
			  }
		       $list=$my->data($_POST)->add();
			if($list){
				// $this->ajaxReturn($vo,'添加成功',1);
				$this->success('提交成功',U('Car/chezhu',array('issue'=>1,'id'=>$uid)));
			}
		   	
			
		}else{
		$pinpai=M('car_pinpai');
		$_GET[id]=1;
		$this->wecha_id=1;
		$pin=$pinpai->field('id,name')->select();
		$this->assign('pin',$pin);
		
		
		$xi=M('car_chexi');
	    $chexi=$xi->field('id,name')->select();
		$this->assign('chexi',$chexi);
	   
		$chexin=M('car_chexing');
	    $chexing=$chexin->field('id,name')->select();
		$this->assign('uid',$uid);
		$this->assign('chexing',$chexing);
		$this->display();
	  }
	}
	public function  chechelist($name,$where,$mit){
		//
		$name=M("$name");
		$data=$name->order('sort desc')->where("$where")->limit("$mit")->select();
		//echo $name->getLastSql();
		$this->assign('va',$data);
		$where['wid']=$this->wecha_id=1;
	     $my=M('car_my');
		$datas=$my->where($where)->find();
		$this->assign('lists',$datas);
	  if($datas!=""){
			$list=$my->where($where)->save($_POST);		
	   }else{	
			$list=$my->add($_POST);
			if($list){
				$this->success('操作成功',U('Car/chezhu'));
			}
			
	   }
		$this->display();
		
	}
	
	//
	public function chezhued(){
		$mod = M('Car_yuyue_baoyang');
		$list = $mod->where('id='.$_GET['id'])->save($_POST);
		if($list){
			$mod = M('Car_yuyue_baoyang');
			$list = $mod->where('id='.$_GET['id'])->find();
			$this->assign("list",$list);
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
	
	public function liuyan(){
	   $uid = $this->_get('uid');
	   $token = $_SESSION['token'];
	   $this->assign('arr',array(1,5));
	   $this->assign('list',M('Car_wish')->select());
	   $this->assign('uid',$uid);
	   $this->assign('token',$token);
	   $this->display();
	}
	
/*	public function handle(){
		$uid = $_GET['uid'];
		$token = $_GET['token'];
		if(!IS_POST) _404('页面不存在',U("Car/liuyan"));
		$data = array(
			'title' => I('title','','htmlspecialchars'),
			'content' => I('content','','htmlspecialchars'),
			'addtime' => time(),
			'token' =>$token,
			'uid'=>$uid
		);
			if(empty($data['content'])){
				$this->error('请填写内容');
			}
			if(M('Car_wish')->data($data)->add()){
				$this->success('发布成功',U('Car/liuyan',array('uid'=>$uid)));
			}else{
				$this->error('发布失败,请重试...');
			}
	}
	*/
	public function handle(){
		$uid = $_GET['uid'];
		$token = $_GET['token'];
		if(!IS_POST) _404('页面不存在',U("Car/liuyan",array('uid'=>$uid)));
			$data['content'] = replace_phiz($this->_post('content'));
			$data['title'] = $this->_post('title');
 			$data['addtime'] = time();
			$data['token'] = $_GET['token'];
			$data['uid'] = $_GET['uid'];
			if(M('Car_wish')->data($data)->add()){
					$this->success('发布成功',U('Car/liuyan',array('uid'=>$uid)));
				}else{
					$this->error('发布失败,请重试...');
				}
		}
}
















?>