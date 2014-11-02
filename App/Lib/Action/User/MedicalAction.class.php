<?php
class MedicalAction extends CommonAction{
	function __construct(){
		parent::__construct();
	}
	
	//添加栏目
	public function colunm(){
		$yl_hospital=M('Yl_hospital');
		$data = D('Yl_colunm');
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$_POST['token']=session('token');
		if (IS_POST){
			if ($data -> create()){
				$result = $data->add($_POST);
				if ($result){
					$this->success('栏目设置成功',U('Medical/colunmlist',array('hid'=>$hid)));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}	
		}else{
			$hid=$this->_get('hid');
			$this->assign('hid',$hid);
			$this -> display('Medical/colunm');
		}
	}

	public function edit(){
		$data = D('Yl_colunm');
		$id     = trim($this->_get('id'));
		$token=session('token');
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$list   = $data->where(array('hid'=>$hid,'id'=>$id,'token'=>$token))->find();
		$this->assign('vo',$list);
		//$this->display('Medical/colunm');
	
		$data = D('Yl_colunm');
		$where['id']=$this->_post('id');
		$where['token']=session('token');
		$hid=$this->_post('hid');
		$this->assign('hid',$hid);
		$_POST['updatetime']=time();
		if (IS_POST){
			if ($data -> create()){
				$result=$data->where($where)->save($_POST);
				if($result){
					$this->success('修改成功',U('Medical/colunmlist',array('hid'=>$hid)));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{
			$hid=$this->_get('hid');
			$this->assign('hid',$hid);
		}
		$this->display('Medical/colunm');
	}
	
	//医院栏目列表
	public function colunmlist(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token=session('token');
		$data = M('Yl_colunm');
		$count = $data->where(array('hid'=>$hid,'token'=>$token))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where(array('hid'=>$hid,'token'=>$token))->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Medical/colunmlist');
	}
	
	//删除栏目
	public function colunmdel(){
		$hid=$this->_get('id');
		$this->assign('hid',$hid);
		$token=session('token');
		$data = M('Yl_colunm');
		$check = $data->field('id')->where(array('hid'=>$hid,'token'=>$token,'id'=>$this->_get('id')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if ($data->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	
	
	
	//科室设置
	public function department(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token = session('token');
		$data  = D('Yl_department');
		$count = $data->where(array('hid'=>$hid,'token'=>$token))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where(array('hid'=>$hid,'token'=>$token))->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Medical/department');
	}
	
	//添加科室
	public function departadd(){
		if (IS_POST){
			$data = D('Yl_department');			
			$_POST['token']=session('token');
			$_POST['officials']=implode(',', $_POST['officials']);
			if ($data -> create()){
				$result = $data->add($_POST);
				if ($result){
					$this->success('科室设置成功',U('Medical/department',array('hid'=>$_POST['hid'])));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}
		}else{
		    $hid=$this->_get('hid');
		    $this->assign('hid',$hid);
		    $this->display('Medical/departadd');
		}
	}
	
	//修改科室
	public function departset(){
		$data   = D('Yl_department');
		$id = trim($this->_get('id'));
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token=session('token');

		
		$where=array('id'=>$id,'hid'=>$hid,'token'=>$token);
		if (IS_POST){
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['officials']=implode(',', $_POST['officials']);
			if ($data -> create()){
				$_POST['updatetime']=time();
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('Medical/department',array('hid'=>$hid)));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{		
		    $list   = $data->where($where)->find();
		    $list['officials']=explode(',', $list['officials']);
		    $this -> assign('vo',$list);		   
		    $this->display('Medical/departadd');
		}
	}
	
	//删除科室
	public function departdel(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token=session('token');
		$check = M('Yl_department')->field('id')->where(array('hid'=>$hid,'token'=>$token,'id'=>$this->_get('id')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Yl_department')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	//批量删除科室
	public function departdel_list(){
		$department=D('Yl_department');
		$token=session('token');
		$getid=$this->_request('id');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$department->where(array('id'=>array('IN',$getids),'token'=>$token))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	//预约列表
	public function yuyue(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$data = M('Yl_order');
		$where['token']=session('token');
		$where['hid']=$hid;
		$count = $data->where($where)->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Medical/yuyueselect');
	}
	
	//预约查询
	public function yuyueselect(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$model=M();
// 		$rs=$model->query('show full columns from tp_yl_order');
// 		var_dump($rs);
// 		$unarr=array('id','hid','sex','token','wecha_id','departid','age','addr','ordercount','orderremarks','updatetime','seedoctor');
// 		foreach ($rs as $key=>$val){
// 			if(in_array($val['Field'], $unarr)){
// 				unset($rs[$key]);
// 			}			
// 		}
		$rs=array(
		        array('Type'=>'string','Field'=>'uname','Comment'=>'患者姓名'),
		        array('Type'=>'string','Field'=>'number','Comment'=>'预约号'),
		        array('Type'=>'phone','Field'=>'phone','Comment'=>'联系电话'),
		        array('Type'=>'time','Field'=>'ordertime','Comment'=>'预约日期'),
		        array('Type'=>'string','Field'=>'orderdepart','Comment'=>'预约科室')
		);
		$this->assign('rs',$rs);

		$data = M('Yl_order');
// 		$where['token'] = session('token');
// 		$where['hid']=$hid;
        $where="`token`='{$_SESSION['token']}' AND hid={$hid}";
		$types = explode(',', $this->_get('types') );
		$this->assign('types',$types[1]);
		$keywords = $this->_get('keywords');
		$this->assign('keywords',$keywords);
		if($keywords && $types[0]=='time'){
		    $where.=" AND FROM_UNIXTIME(`{$types[1]}`,'%Y-%m-%d')='{$keywords}'";
		}elseif($keywords){  
		    $where.=" AND `{$types[1]}` like '%{$keywords}%'";
    		
		}
		$count = $data->where($where)->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		
		$list = $data->where($where)
					 ->limit($Page->firstRow.','.$Page->listRows)
					 ->order("id desc")
					 ->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Medical/yuyueselect');
	}
	
	//是否就诊
	public function checksee(){
		$type=M('Yl_order');
		$id=$this->_get('id');
		$token = session('token');
		if (empty ($id)) {
			$this->error ( '参数错误' );
			die();
		}
		if(isset($_GET['seedoctor'])){
			$check=$_GET['seedoctor'];
		}else{
			$result=$type->field('seedoctor')->where(array('id'=>$id,'token'=>$token))->find();
			$check=$result['seedoctor'];
		}
		$data=array();
		if($check){
			$data['seedoctor']=0;
		}else{
			$data['seedoctor']=1;
		}
		$result=$type->where(array('id'=>$id,'token'=>$token) )->save($data);
		header('Location:'.$_SERVER['HTTP_REFERER']);
	}
	
	//删除预约
	public function sel_del(){
		$token = session('token');
		$check = M('Yl_order')->field('id')->where(array('id'=>$this->_get('id'),'token'=>$token))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Yl_order')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	//批量删除预约记录
	public function yuyueselectdel(){
		$order=D('Yl_order');
		$getid=$this->_request('id');
		$token = session('token');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$order->where(array('id'=>array('IN',$getids),'token'=>$token))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	//预约统计
	public function yuyuecount(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token = session('token');
		//php获取今日开始时间戳和结束时间戳
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		//php获取昨日起始时间戳和结束时间戳
		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		//php获取本月起始时间戳和结束时间戳
		if(date('m')>1){
			$beginThismonth=mktime(0,0,0,date('m')-1,date('d'),date('Y'));
		}else{
			$beginThismonth=mktime(0,0,0,12,date('d'),date('Y')-1);
		}
		$endThismonth=mktime(23,59,59,date('m'),date('d'),date('Y'));
	
		$data=D('Yl_order');
		$token=session('token');
		$count=$data->where(array('ctime'=>array(array('egt',$beginToday),array('elt',$endToday)),'hid'=>$hid,'token'=>$token))->count(); 
		$counts=$data->where(array('ctime'=>array(array('egt',$beginYesterday),array('elt',$endYesterday)),'hid'=>$hid,'token'=>$token))->count();
		$sum=$data->where(array('ctime'=>array(array('egt',$beginThismonth),array('elt',$endThismonth)),'hid'=>$hid,'token'=>$token))->count();

		$cont=$data->where(array('ordertime'=>array(array('egt',$beginToday),array('elt',$endToday)),'hid'=>$hid,'token'=>$token,'seedoctor'=>1))->count();
		$conts=$data->where(array('ordertime'=>array(array('egt',$beginYesterday),array('elt',$endYesterday)),'hid'=>$hid,'token'=>$token,'seedoctor'=>1))->count();
		$sums=$data->where(array('ordertime'=>array(array('egt',$beginThismonth),array('elt',$endThismonth)),'hid'=>$hid,'token'=>$token,'seedoctor'=>1))->count();
	
		$this->assign('count',$count);
		$this->assign('counts',$counts);
		$this->assign('sum',$sum);
		$this->assign('cont',$cont);
		$this->assign('conts',$conts);
		$this->assign('sums',$sums);
		$this -> assign('beginThismonth',$beginThismonth);
		$this -> assign('endThismonth',$endThismonth);

		$chart=array();
		$Model=M();
		$prefix=C('DB_PREFIX');
		$token=session('token');
		$where="{$beginThismonth}<=`ctime` AND `ctime` <={$endThismonth} AND  `token`='{$token}' AND `hid`='{$hid}'";
		$result=$Model->query("SELECT FROM_UNIXTIME(`ctime`,'%Y-%m-%d') as `time` , COUNT(*) as `count` FROM {$prefix}yl_order where {$where} GROUP BY FROM_UNIXTIME(`ctime`,'%Y-%m-%d')");
	
		$result_timearr=array();
		foreach ($result as $value){
			$result_timearr["{$value['time']}"]=$value['count'];
		}
	
		$style=array();
		$style['_single']=true;
		$style[]=array( 'name'=>'myCaptionFont', 'type'=>'font', 'ont'=>'微软雅黑,宋体,Arial,sans-serif' ,'size'=>'14', 'color'=>'3336a9',);
		$definition['style']=$style;
		$styles['definition']=$definition;
		$apply=array();
		$apply['_single']=true;
		$apply[]=array('toObject'=>'Caption','styles'=>'myCaptionFont');
		$application['apply']=$apply;
		$styles['application']=$application;
		$chart['styles']=$styles;
	
	
	
		$set['_single']=true;
		for($i=$beginThismonth,$n=0;$i<$endThismonth+86400*1;$i+=86400){
			$item_time=date("Y-m-d",$i);
			$item=array();
			if($n%6==0){
				$item['label']=$item_time;
			}
			$item['value']=(int)$result_timearr["{$item_time}"];
			$set[]=$item;
			$n++;
		}
	
			
		$chart['_property']=array(
				'caption'=>'最近一个月新增预约趋势图',
				'subcaption'=>'',
				'xAxisName'=>'',
				'yAxisName'=>' ',
				'yAxisMinValue'=>'-1',
				'numberPrefix'=>'',
				'showValues'=>'-1',
				'alternateHGridColor'=>'b6dfff',
				'alternateHGridAlpha'=>'20',
				'divLineColor'=>'FCB541',
				'divLineAlpha'=>'50',
				'canvasBorderColor'=>'666666',
				'baseFontColor'=>'666666',
				'lineColor'=>'3336a9',
				'bgColor'=>'ffffff',
		);
	
		$Model=M();
		$prefix=C('DB_PREFIX');
		$token=session('token');
		$where="{$beginThismonth}<=`ctime` AND `ctime` <={$endThismonth} AND  `token`='{$token}' AND  `hid`='{$hid}'";
		$result=$Model->query("SELECT FROM_UNIXTIME(`ctime`,'%Y-%m-%d') as `time` , COUNT(*) as `count` FROM {$prefix}yl_order where {$where} GROUP BY FROM_UNIXTIME(`ctime`,'%Y-%m-%d')");
	
		$result_timearr=array();
		foreach ($result as $value){
			$result_timearr["{$value['time']}"]=$value['count'];
		}
	
		$set=array();
		$set['_single']=true;
		for($i=$beginThismonth,$n=0;$i<$endThismonth+86400*1;$i+=86400){
			$item_time=date("Y-m-d",$i);
			$item=array();
			if($n%6==0){
				$item['label']=$item_time;
			}
			$item['value']=(int)$result_timearr["{$item_time}"];
			$set[]=$item;
			$n++;
		}
		$chart['set']=$set;
		$memberChart_xml=to_chartxml($chart,'memberChart_xml');
		$this->assign('memberChart_xml',$memberChart_xml);
	
	
		$where="{$beginThismonth}<=`ordertime` AND `ordertime` <={$endThismonth} AND `seedoctor`=1 AND `token`='{$token}' AND `hid`='{$hid}'";
		$result=$Model->query("SELECT FROM_UNIXTIME(`ordertime`,'%Y-%m-%d') as `time` , COUNT(*) as `count` FROM {$prefix}yl_order where {$where} GROUP BY FROM_UNIXTIME(`ordertime`,'%Y-%m-%d')");
		echo $Model->getLastSql();
	   
		$result_timearr=array();
		foreach ($result as $value){
			$result_timearr["{$value['time']}"]=$value['count'];
		}
	
		
		$chart['_property']=array(
				'caption'=>'最近一个月就诊趋势图',
				'subcaption'=>'',
				'xAxisName'=>'',
				'yAxisName'=>' ',
				'yAxisMinValue'=>'-1',
				'numberPrefix'=>'',
				'showValues'=>'-1',
				'alternateHGridColor'=>'b6dfff',
				'alternateHGridAlpha'=>'20',
				'divLineColor'=>'FCB541',
				'divLineAlpha'=>'50',
				'canvasBorderColor'=>'666666',
				'baseFontColor'=>'666666',
				'lineColor'=>'ff7900',
				'bgColor'=>'ffffff',
		);
		
		$style=array();
		$style['_single']=true;
		$style[]=array( 'name'=>'myCaptionFont', 'type'=>'font', 'ont'=>'微软雅黑,宋体,Arial,sans-serif' ,'size'=>'14', 'color'=>'ff7900',);
		$definition['style']=$style;
		$styles['definition']=$definition;
		$apply=array();
		$apply['_single']=true;
		$apply[]=array('toObject'=>'Caption','styles'=>'myCaptionFont');
		$application['apply']=$apply;
		$styles['application']=$application;
		$chart['styles']=$styles;
		
		$set=array();
		$set['_single']=true;
		for($i=$beginThismonth,$n=0;$i<$endThismonth+86400*1;$i+=86400){
			$item_time=date("Y-m-d",$i);
			$item=array();
			if($n%6==0){
				$item['label']=$item_time;
			}
			$item['value']=(int)$result_timearr["{$item_time}"];
			$set[]=$item;
			$n++;
		}
		$chart['set']=$set;
		$snConsumeChart_xml=to_chartxml($chart, 'snConsumeChart_xml');
		$this->assign('snConsumeChart_xml',$snConsumeChart_xml);
		$this -> display('Medical/yuyuecount');
	}
	
	//康复案例设置
	public function recovery(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token = session('token');
		$data  = D('Yl_example');
		$count = $data->where(array('hid'=>$hid,'token'=>$token))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where(array('hid'=>$hid,'token'=>$token))->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display('Medical/recovery');
	}
	
	//添加康复案例
	public function recoveryadd(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		if (IS_POST){
			$data = D('Yl_example');
			$_POST['token'] = session('token');
			$_POST['kftime']=strtotime($this->_post('kftime'));
			if ($data -> create($_POST)){
				$result = $data->add();
				if ($result){
					$this->success('案例添加成功',U('Medical/recoveryadd',array('hid'=>$hid)));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}
		}else{
			$this -> display('Medical/recoveryadd');
		}
	}
	
	//修改
	public function recoveryset(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$data   = D('Yl_example');
		$id     = trim($this->_get('id'));
		$where['token'] = session('token');
		$where['id'] = $id;
		$where['hid']=$hid;
		$list   = $data->where($where)->find();
		$this -> assign('vo',$list);
		//dump($list);exit;
		if (IS_POST){
			$_POST['updatetime']=time();
			$_POST['kftime']=strtotime($this->_post('kftime'));
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			if ($data -> create()){
				//dump($_POST);exit;
				if ($data->where($where)->save($_POST)){
					$this->success('修改成功',U('Medical/recovery',array('hid'=>$hid)));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{
			$this->assign('set',$check);
			$this->display('Medical/recoveryadd');
		}
	}
	
	//删除康复案例
	public function recoverydel(){
		$hid=$this->_get('hid');
		$this->assign('hid',$hid);
		$token = session('token');
		$check = M('Yl_example')->field('id')->where(array('hid'=>$hid,'token'=>$token,'id'=>$this->_get('id')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('Yl_example')->where(array('id'=>$check['id']))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	//批量删除
	public function recoverydel_list(){
		$example=D('Yl_example');
		$getid=$this->_request('id');
		$token = session('token');
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$example->where(array('id'=>array('IN',$getids),'token'=>$token))->delete();
		if($result){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	
}
	