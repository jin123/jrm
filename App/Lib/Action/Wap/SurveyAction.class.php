<?php
/**
*微调研
**/
class SurveyAction extends Action
{
    public function __construct(){
        parent::__construct();
        $this->token= $this->_get('token');
        $this->assign('token',$this->token);
        $this->wecha_id	= $this->_get('wecha_id');
        if (!$this->wecha_id){
            $this->wecha_id=null;
        }
        $this->assign('wecha_id',$this->wecha_id);

    }
    public function index()
    {
		
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($agent, "icroMessenger")) {
           // echo '此功能只能在微信浏览器中使用';exit;
        }
		$m_wdy = M('survey');
		$id = $this->_get('id');
		$wdyres = $m_wdy->where(array('id'=>$id,'token'=>$this->token))->find();
		$wdyres['kssj']=strtotime($wdyres['kssj']);
		$wdyres['jssj']=strtotime($wdyres['jssj']);//结束时间格式转换方便后面判断是否活动结束~，如果活动结束，重定向到活动结束页面
		$nowdata = time();
		if($nowdata < $wdyres['kssj']){
			$this->redirect('/Wap/Survey/activitynotscratch',array('id'=>$id,'token'=> $this->token,'openid'=>'fromuserid'));
		}elseif($nowdata > $wdyres['jssj']){
		    $this->redirect('/Wap/Survey/activityend',array('id'=>$id,'token'=> $this->token,'openid'=>'fromuserid'));
	    }			
		$this->assign('wdy',$wdyres);
		$m_wdy_replay= M('Survey_replay');
		$yzrec=$m_wdy_replay->where(array('cid'=>$id,'wecha_id'=>$this->wecha_id))->find();
		//dump($yzrec);exit;
		if($yzrec){
		    $this->redirect('/Wap/Survey/wdyok',array('id'=>$id,'token'=> $this->token,'wecha_id'=>'fromuserid'));
		}		
        $this->display();
    }
	
	public function wdystart(){
// 		$way=M('Surveytk');
// 		$id=$_GET['id'];
// 		$list=$way->find($id);
// 		echo $way->getlastsql();
// 		$this->assign('op',$list);
// 		dump($list);

	    $id=$this->_get('id');	 
	    $userinfo_model=M('Userinfo');
	    $userinfo=$userinfo_model->where(array('wecha_id'=>$this->wecha_id,'token'=>$this->token))->find();
	    $this->assign('userinfo',$userinfo);
        if(IS_POST){
            $survey_replay_model=M('Survey_replay');
            $survey_replay=$survey_replay_model->field('id')->where(array('cid'=>$id,'token'=>$this->token,'wecha_id'=>$this->wecha_id))->find();
            $_POST['cid'] = $id;
            $_POST['token'] = $this->token;
            $_POST['wecha_id'] = $this->wecha_id;
            $_POST['ctime'] = time();
            $_POST['ans'] = json_encode(array());
            if($survey_replay){
                $result=$survey_replay_model->where(array('id'=>$survey_replay['id']))->save($_POST);
            }else{
                $result=$survey_replay_model->add($_POST);
            }
            if(!$userinfo){      
                $data=array();
                $data['token']=$this->token;
                $data['wecha_id']=$this->wecha_id;
                $data['tel']=$this->_post('tel');
                $userinfo_model->add();
            }
            if($result){
                $this->redirect('Survey/wdyans',array('cid'=>$id,'token'=>$this->token,'wecha_id'=>$this->wecha_id));
            }else{
            	$this->redirect('Survey/wdystart');
            }
        }else{
          $this->assign('id',$id);
		  $this->display();
        }
			
	}
	public function wdyans(){
		$survey_model = M('Survey');
		$cid=$this->_get('cid');
		$survey=$survey_model->find($cid);
		if($survey){
		    $this->assign('survey',$survey);
		    $survey_replay_model=M('Survey_replay');
		    $survey_replay=$survey_replay_model->where(array('cid'=>$cid,'wecha_id'=>$this->wecha_id))->find();
		    if($survey_replay){
		        $jgs = json_decode($survey_replay['ans']);
		        if(is_array($jgs)){
		            //查看是否是答题跳转进来的
		            $ans=$this->_get('ans');
		            if($ans){
		                $oncount=$this->_get('oncount');
		                $oncount=$oncount?$oncount:0;
		                $jgs[$oncount]= $ans;		     
		                $data=array('ans'=>json_encode($jgs));
		                $survey_replay_model->where(array('id'=>$survey_replay['id']))->save($data);		                
		            }		        
		            //查询题目
		            $surveytk_model = M('Surveytk');
		            $sytm = $surveytk_model->where(array('sid'=>$cid))->count();
		            if($sytm <= count($jgs)){
		                //已答完跳转到填写意见
		               $this->redirect('/Wap/Survey/wdyyj',array('cid'=>$cid,'token'=>$this->token,'wecha_id'=>$this->wecha_id));
		            }else{
		                $rk = $surveytk_model->where(array('sid'=>$cid))->limit(count($jgs).',1')->select();
		                if($rk)$trk=$rk[0];
		                $this->assign('trk',$trk);
		                $this->assign('oncount',count($jgs));
		            }
 		        }
		    }		    
		}
		$this->display();
	}
	public function wdyyj(){
	    $survey_model = M('Survey');
	    $cid=$this->_get('cid');
	    $survey=$survey_model->find($cid);
	    $this->assign('wdy',$survey);
		$this->display();
	}
	public function wdyyjadd(){
	    $survey_replay_model=M('Survey_replay');
	    $cid=$this->_post('id');
	    $data['yjfk']=$this->_post('jy');
	    $result=$survey_replay_model->where(array('cid'=>$cid,'wecha_id'=>$this->wecha_id))->save($data);
	}
	public function wdyok(){
	    $survey_model = M('Survey');
	    $cid=$this->_get('cid');
	    $survey=$survey_model->find($cid);
	    $this->assign('wdy',$survey);
	   $this->display();	   
	}
	
	public function activityend(){
		$this->display('activityend');
	}
	
	public function activitynotscratch(){
	    $this->display('activitynotscratch');
	}
}
?>