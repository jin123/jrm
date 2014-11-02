<?php
class MedicalAction extends Action{
	public $token;
	public $pid;
	
	public function _initialize()
	{
		$this->token = $this->_get('token');
		$this->pid = $this->_get('pid');
		$this->hid = $this->_get('hid');
		$this->assign('token', $this->token);
		$this->assign('pid', $this->pid);
		$this->assign('hid', $this->hid);
	}
	
	//医院分类
	public function medical(){
		$yl_hospital=M('Yl_hospital');
		$token=$this->_get('token');
		$this->assign('token',$token);
		$wecha_id=$this->_get('wecha_id');
		$this->assign('wecha_id',$wecha_id);
		$list  = $yl_hospital->where(array('token'=>$token,'wecha_id'=>$wecha_id))->select();
		$this -> assign('vo',$list);
		

		$thisinfo = M('reply_info')->where(array('infotype'=>'Hospital','token'=>$token))->find();
		$this->assign('config',unserialize($thisinfo['config']));
		
		
		$this->display('Medical/medical');
	}
	
	//医院栏目
	public function index(){
		$token=$this->_get('token');
		$this->assign('token',$token);
		$wecha_id=$this->_get('wecha_id');
		$this->assign('wecha_id',$wecha_id);
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$hospital=M('Yl_hospital');
		$thumb=$hospital->where(array('id'=>$hid,'token'=>$token))->field('thumb as backgroundimage')->find();
		
		
		$yl_colunm=M('Yl_colunm');
		$data=$yl_colunm->where(array('hid'=>$hid,'token'=>$token))->select();
		$this->assign('data',$data);
		$this->assign('thumb',$thumb);
		$this->display('Medical/index');
	}
	
	
	//医院简介
	public function hospital(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$yl_hospital=M('Yl_hospital');
		$data=$yl_hospital->where(array('id'=>$hid))->field('id,content,thumb')->find();
		$this->assign('data',$data);
		$this->display('Medical/hospital');
	}
	
	//科室
	public function department(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		$yl_department=M('Yl_department');
		$data=$yl_department->field('id,name,thumb')->where(array('hid'=>$hid,'token'=>$token))->select();
		$this->assign('data',$data);
		$this->display('Medical/department');
	}
	
	//各科室简介
	public function departcontent(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$yl_department=M('Yl_department');
		$id=$this->_get('id');
		$data=$yl_department->field('id,hid,content,thumb')->where(array('id'=>$id,'hid'=>$hid,'token'=>$token))->find();
		$this->assign('data',$data);
		$this->display('Medical/departcontent');
	}
	
	//挂号预约
	public function yuyue(){
	    //我的挂号统计
	    $token=$this->_get('token');
	    $wecha_id=$this->_get('wecha_id');
	    $this->assign('token',$token);
	    $this->assign('wecha_id',$wecha_id);
	    $hid=$this->_get('hid');
	    $this->assign('hid',$hid);
	    
		$yl_hospital=M('Yl_hospital');
		$data=$yl_hospital->where(array('id'=>$hid,'token'=>$token))->field('id,tel,address,jd,wd')->find();
		$this->assign('data',$data);
		$yl_department=M('Yl_department');
		$list=$yl_department->where(array('hid'=>$hid,'token'=>$token))->field('id,name,officials')->select();
		
// 		$list_arr=array();
// 		foreach ($list as $value){
// 		    in_array(trim($value['name']),$list_arr) || $list_arr[]=$value['name'];
// 		}
		
        $this->assign('list',$list);

		//dump($list['name']);exit;

		$yl_order=M('Yl_order');
		//php获取今日开始时间戳和结束时间戳
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$order=$yl_order->where(array('ordertime'=>(array('egt',$beginToday)),'hid'=>$hid,'token'=>$token,'wecha_id'=>$wecha_id))->count();
		$this->assign('order',$order);
				
		$yl_order=D('Yl_order');
		$wecha_id=$this->_get('wecha_id');
		$token=$this->_get('token');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		
		$orderdepart=$this->_post('orderdepart');
		$yldepart=$yl_department->where(array("name"=>$orderdepart))->field('id')->find();
		$this->assign('yldepart',$yldepart);
		$orders=$yl_order->where(array('departid'=>$yldepart['id']))->count();
		$number=$this->_post('time').$yldepart['id'].$orders['count']+1;
		$this->assign('number',$number);

		
		//预约选择日期时间范围（当天到一个月之后的今天）
		$mindate=date('Y-m-d H:i:s',time());
		$this->assign('mindate',$mindate);
		$Y=date(Y);
		$m=date(m);
		$d=date(d);
		$maxdate=date('Y-m-d H:i:s', mktime(0,0,0,$m+1,$d,$Y));
		$this->assign('maxdate',$maxdate);
		$this->display('Medical/yuyue');
	}
	
	public function yuyue_post(){
	    $token=$this->_get('token');
	    $wecha_id=$this->_get('wecha_id');
	    $hid=$this->_get('hid');
	    $this->assign('hid',$hid);
	    $yl_order=M('Yl_order');
	    $ordertime=strtotime($this->_post('ordertime'));
	    $ylorder=$yl_order->where(array('hid'=>$hid,"ordertime"=>$ordertime,'wecha_id'=>$wecha_id,'token'=>$token))->find();
	    
	    if($ylorder == null){	        
	        if(IS_POST){
	            $_POST['departid']=$this->_post('orderdepart');
            
	            $_POST['orderdepart']=M('Yl_department')->where(array('id'=>$_POST['departid'],'token'=>$token))->getField('name');
	            $_POST['orderexpert']=$this->_post('orderexperts');
	    
	            $_POST['ordertime']=strtotime($this->_post('ordertime'));
	            $_POST['token']=$token;
	            $_POST['wecha_id']=$wecha_id;
	    
	            $hid_format=sprintf("%02d",$this->_post('hid') );
	            $departid_format=sprintf("%02d", $_POST['departid']);
	    
	            $departid=$_POST['departid'];
	    
	            $orderformat=date('Ymd',$_POST['ordertime']);
	            //每科室每天预约人数统计
	            $count=$yl_order->where("`departid`={$_POST['departid']} AND FROM_UNIXTIME(`ordertime`,'%Y%m%d')='{$orderformat}'")->count();
	            $count_format=sprintf("%02d", $count+1);
	            if($count_format<=30){           //判断预约人数是否超过30
	                	
	                $_POST['number']=$hid_format.$departid_format.date('Ymd',$_POST['ordertime']).$count_format;
	    
	                $_POST['ctime']=time();
	    
	                if($yl_order->create()){
	                    $result=$yl_order->add($_POST);
	                    //echo $yl_order->getLastSql();exit;
	                    if($result){
	                        $result=array('error'=>0,'msg'=>'恭喜您，预约成功,请牢记您的预约号码'.$_POST['number']);
	                    }else{
	                        $result=array('error'=>2,'msg'=>'很抱歉，您预约失败');
	                    }
	                }else{
	                    $result=array('error'=>3,'msg'=>$yl_order->getError());
	                }
	                die(json_encode($result));
	            }else{
	                $result=array('error'=>5,'msg'=>'很抱歉，今天的预约已满，请明天再来吧');
	                die(json_encode($result));
	            }
	        }
	        	
	    }else{
	        $result=array('error'=>4,'msg'=>'很抱歉，此日期已有您的预约');
	        die(json_encode($result));
	    }
	}
	
	
	//我的预约
	public function myorder(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		//php获取今日开始时间戳和结束时间戳
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$yl_order=M('Yl_order');
		$data=$yl_order->where(array('ordertime'=>(array('egt',$beginToday)),'hid'=>$hid,'token'=>$token,'wecha_id'=>$wecha_id))->order('id DESC')->select();
		$this->assign('data',$data);
		$this->display('Medical/detail');
	}

	//康复案例列表
	public function recovery(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$yl_example=M('Yl_example');
		$data=$yl_example->where(array('hid'=>$hid,'token'=>$token))->order('id DESC')->select();
		$this->assign('data',$data);
		$this->display('Medical/recovery');
	}
	//康复案例内容
	public function r_content(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$this->assign('token',$token);
		$this->assign('wecha_id',$wecha_id);
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$yl_example=M('Yl_example');
		$id=$this->_get('id');
		$data=$yl_example->field('id,content,thumb')->where(array('id'=>$id,'hid'=>$hid,'token'=>$token))->find();
		$this->assign('data',$data);
		$this->display('Medical/r_content');
	}
	
	//科室->专家
	public function partchange(){
	    
	    $token=$this->_request('token');

		$name=trim($this->_request('name'));
				
		$yl_department=M('Yl_department');
		
		$list=$yl_department->where("`token`='{$token}' AND TRIM(`name`)='{$name}'")->field('id,officials')->select();
        
		die(json_encode($list));
	}

	//留言
	public function message()
	{
		$pid = $this->pid;
		$token = $this->token;
		$hid=$this->_get('hid');
		$wecha_id=$this->_get('wecha_id');
		$api=M('Diymen_set')->where(array('token'=>$token))->find();
		$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
		$json=json_decode($this->curlGet($url_get));
		$access_token = $json->access_token;
		$openid =  $this->wecha_id;
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$json2=json_decode($this->curlGet($url));
		$name = $json2->nickname;
		$data = M('Yl_liuyan_set');
		$ly_data = $data->where(array('hid'=>$hid,'token'=>$token))->find();
		$data = $data->where(array('hid'=>$hid,'token'=>$token))->field('id')->find();
		
		$ly = M('Yl_liuyan');
		$mres = $ly->where(array('pid'=>$data['id'],'isval'=>'1'))->order('id desc')->select();
		$this->assign('ly_data', $ly_data);
		$this->assign('mres', $mres);
		$this->assign('name', $name);
		$this->assign('pid', $ly_data['id']);
		$this->assign('hid', $hid);
		$this->assign('wecha_id', $wecha_id);
		$this->display('Medical/liuyanuser');
	}
	
	public function add()
	{
		if(!IS_AJAX) halt('页面不存在');
		$name = $this->_post('name');
		$msg =  $this->_post('msg');
		$pid = $this->_post('pid');
		$wecha_id=$this->_post('wecha_id');
		$issh = M('Yl_liuyan_set')->where('id='.$pid)->getField('issh');
		if($issh==1){
			$isval = 0;
		}else{
			$isval = 1;
		}
		$data = array(
				'pid' => $pid,
				'wecha_id' => $wecha_id,
				'name' => $name,
				'time' => time(),
				'msg' => $msg,
				'isval' => $isval,
					
		);		 
		if(M('Yl_liuyan')->data($data)->add()){
		    $result=array('error'=>0,'issh'=>$issh);
			die(json_encode($result));
		}else{
		    $result=array('error'=>1);
		    die(json_encode($result));
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
	
	
}