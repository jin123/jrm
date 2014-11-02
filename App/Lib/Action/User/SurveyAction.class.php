<?php
class SurveyAction extends CommonAction{
	function __construct(){
		parent::__construct();
	}
	
	//列表
	public function index(){
		$data  = D('survey'); 
		$where=array('token'=>session('token'));
		$count = $data->where($where)->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();	
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this -> display();
	}
	
	//添加微调研
	public function add(){
		if (IS_POST){	
			$this->all_insert('Survey');
		}else{		
			$this -> display();
		}
	}
	
	//微调研修改
	public function set(){
		$db   = D('Survey');
		$id     = trim($this->_get('id'));
		$where['id'] = $id;
		$where['token']=session('token');
		$list   = $db->where($where)->find();
		
		if (IS_POST){ 
			$check = $db->where($where)->find();
			if ($check == false)$this->error('非法操作');
			$_POST['id']=$_GET['id'];
			$this->all_save('Survey');			
		}else{	
			$this -> assign('vo',$list);	
			$this->display('Survey/set');
		}
	}
	
	//微调研删除
	public function delete(){
		$check = M('survey')->field('id')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('survey')->where(array('id'=>$check['id']))->delete()){
			    M('Keyword')->where(array('pid'=>$this->_get('id'),'module'=>'Survey','token'=>session('token')))->delete();
			    M('surveytk')->where(array('sid'=>$check['id']))->delete();
			    M('Survey_replay')->where(array('cid'=>$check['id']))->delete();
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	//批量删除
	public function del_list(){
		$survey=D('survey');
		$getid=$this->_request('id');	
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$survey->where(array('id'=>array('IN',$getids),'token'=>session('token')))->delete();
		if($result){
		    M('Keyword')->where(array('pid'=>array('IN',$getids),'module'=>'Survey','token'=>session('token')))->delete();
		    M('surveytk')->where(array('sid'=>array('IN',$getids),'token'=>session('token')))->delete();
		    M('Survey_replay')->where(array('cid'=>array('IN',$getids),'token'=>session('token')))->delete();
			$this->success('成功');
		}else{
			$this->error('失败');
		}
	}
	
	//微调研题库
	public function surveytk(){
		//$status = trim($this->_get('status'));
		$id     = trim($this->_get('id'));
		$data  = D('surveytk');
		$where['sid'] = $id;
		$list  = $data->where($where)->order("id desc")->select(); 
		$this -> assign('list',$list);
		$this->assign('thisId',$id);
		$this -> display();
	}
	
	//微调研题库设置
	public function surveytkadd(){
	    $id = trim($this->_get('id'));
		if (IS_POST){
		$data  = D('Surveytk');
		//$_POST['sid'] = $id;
			if ($data -> create()){
				$result = $data->add();
				if ($result){
					$this->success('微调研题库设置成功',U('Survey/surveytk',array('id'=>$_POST['sid'])));
				}else{
					$this->error('服务器繁忙,请稍候再试');
				}
			}else{
				$this->error($data->getError());
			}			
		}else{
		    $this->assign('sid',$id);
			$this -> display();
		}
	}
	
	//修改微调研题库
	public function surveytkset(){
		$data   = D('Surveytk');		
		$id     = trim($this->_request('id'));
		$where['id'] = $id;
		$where['token']=session('token');
		$list   = $data->where($where)->find();		
		$this -> assign('vo',$list);		
		if (IS_POST){
			$check = $data->where($where)->find();
			if ($check == false)$this->error('非法操作');
			if ($data -> create()){
				if ($data->where($where)->save()){				    
					$this->success('修改成功',U('Survey/surveytk',array('id'=>$_POST['sid'])));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
			}
		}else{	
		    $this->assign('sid',$list['sid']);
			$this -> display('Survey/surveytkadd');
		}
	}
	
	//删除微调研题库设置
	public function surveytkdel(){
		$check = M('surveytk')->field('id')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){
			if (M('surveytk')->where(array('id'=>$check['id'],'token'=>session('token')))->delete()){
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	
	//批量删除微调研题库
	public function deltklist(){
		$surveytk=D('surveytk');
		$getid=$this->_request('id');	
		if(!$getid){
			$this->error('没有选中记录');
		}
		if(!is_array($getid)){$getids=explode(',',$getid);}else{$getids=$getid;}
		$result=$surveytk->where(array('id'=>array('IN',$getids),'token'=>session('token')))->delete();
		if($result){
			$this->success('成功');
		}else{
			$this->error('失败');
		}
	}
	
	//数据监测（微调研活动）
	public function surveyhdlist(){
		$id     = trim($this->_get('id'));
		$tel =trim($this->_get('tel'));
		$kssj =trim($this->_get('kssj'));
		$jssj =trim($this->_get('jssj'));

		$tel && $where['tel'] = $tel;
		$kssj && $where['ctime'][] = array('gt',strtotime($kssj));
		$jssj && $where['ctime'][] = array('lt',strtotime($jssj));
		$data = D('Survey_replay');
		$where['cid'] = $id;		
		$where['token']=session('token');
		
		$count = $data->where($where)->count();
		$Page  = new Page($count,25);
		$show  = $Page->show();
		$this -> assign('page',$show);
		$list   = $data->where($where)->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();		
		$this -> assign('list',$list);
		$this->assign('id',$id);
		
		$this -> display('Survey/surveylist');
		
	}
	//个人数据详情
	public function surveydetail(){
	    $id     = trim($this->_get('id'));
	    $db = D('Survey_replay');
	    $where=array('id'=>$id,'token'=>session('token'));
	    $data   = $db->where($where)->order("id desc")->find();
	    $this -> assign('data',$data);
	    
	    $r_ans=json_decode($data['ans']);
	    
	    
	    $surveytk=M('Surveytk')->where(array('sid'=>$data['cid']))->select();
	    foreach ($r_ans as $key=>$val){
	        $ans=explode('@',$val);
	        $str_ans='';
	        foreach ($ans as $ans_k=>$ans_v){
	            $str_ans.=$surveytk[$key]["option{$ans_v}"].',';	            
	        }
	        $surveytk[$key]['str_ans']=substr($str_ans, 0,-1);
	    }
	      foreach ($surveytk as $key=>$val){
	         $surveytk[$key]['str_ans']= $surveytk[$key]['str_ans']?$val['str_ans']:"未回答";
	      }

	    $this->assign('surveytk',$surveytk);
	    $this->display();
	}
	//查看数据比例
	public function surveychart(){
	    $id     = trim($this->_get('id'));
	    $survey_db = D('Survey');
	    $where=array('id'=>$id,'token'=>session('token'));
	    $survey   = $survey_db->where($where)->find();
	    $this->assign('survey',$survey);
	    $surveytk_db = D('Surveytk');
	    $where=array('sid'=>$id,'token'=>session('token'));    
	    $surveytk = $surveytk_db->where($where)->select();
	    
	    
	    $survey_replay_db = D('Survey_replay');
	    $where=array('cid'=>$id,'token'=>session('token'));
	    $survey_replay=$survey_replay_db->where($where)->select();
	   // var_dump($survey_replay);
	    $ans_data=array();
	   foreach ($surveytk as $key=>$value){
	       //$ans_data[$key+1]
	       for($i=1;$i<=10;$i++){
	           if(empty($value['option'.$i]))continue;
	           $ans_data[$key+1]['title']=$value['question'];
	           $ans_data[$key+1][$i]['title']=$value['option'.$i];	           
	       }
	   }
	    //[题号][选项]['number']==人数
	    foreach ($survey_replay as $replay_value){
	    	$ans=json_decode($replay_value['ans']);
	    	$n=1;
	    	foreach ($ans as $ans_v){
	    	    $opts=explode('@',$ans_v);
	    	    foreach ($opts as $opt_v){
	    	        $ans_data[$n][$opt_v]['number']++;
	    	    }
	    	    $n++;
	    	}	       
	    }
	    foreach ($ans_data as $key=>$value){	        
	        $chart=array();
	        $chart['_property']=array(
	                'caption'=>$key."、".$value['title'],
	                'bgColor'=>'bce3ff',
	                'baseFontSize'=>'12',
	                //'baseFontColor'=>'3336a9',
	        );
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
	        
	        
	        $set=array();
	        $set['_single']=true;
	        foreach ($value as $opt_k=>$opt_v){
	            if($opt_k=='title')continue;
	            $item=array();
	            $item['label']=$opt_v['title'];
	            $item['value']=(int)$opt_v['number'];
	            $set[]=$item;
	        }
	        $chart['set']=$set;
	        $xml_name=to_chartxml($chart, 'wdy'.session('token').$key);
	        $ans_data[$key]['xml_name']=$xml_name;
	    }
	    $this->assign('ans_data',$ans_data);
	    
	    $this->assign('count',count($survey_replay));
		$this->display();
	}
}