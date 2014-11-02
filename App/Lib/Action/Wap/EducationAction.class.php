<?php

class EducationAction extends Action{

	private $wecha_id;
	public $token;	
	public function _initialize(){
		$this->token = $this->_get('token');
		$this->wecha_id = $this->_get('wecha_id');
		$where['token']=$this->token;
		$homeInfo=D('Wxuser')->where($where)->find();

		if($homeInfo['dlid']){
			$dlInfo = M('daili')->where(array('id'=>$homeInfo['dlid']))->field('copyright')->find();
			if($dlInfo['copyright']){
				list($dlInfo['copy'],$dlInfo['url']) = split("[|]", $dlInfo['copyright']);
				$this->assign('daili',$dlInfo['copyright']);
			}
		}
	}	
	//教育机构简介
	public function index(){
		$hb = M('jiaoyu_haibao');
		$where['token'] = $this->_get('token');
		$where['jid'] = $this->_get('id');
		$result = $hb->where($where)->select();
		

		$this->assign('list',$result);
		$data['id'] = $this->_get('id');
		$jg = M('jiaoyu_jigou');
		$result1 = $jg->where($data)->find();
		//dump($result1);
		//exit;
		$this->assign('list1',$result1);
		$this->display();		
	}
	//结构简介
	public function info(){
		$id = $_GET['id'];
		$jg = M('jiaoyu_jigou');
		$result = $jg->where("id=$id")->find();
		$this->assign('list',$result);
		
		$yx = M('jiaoyu_yingxiang');
		$result1 = $yx->where("jid=$id AND isno='1'")->order('time desc')->select();
		//dump($result1);
		//exit;
		$this->assign('list1',$result1);
		$this->display();
	}
	//相册
	public function photo(){
		//$jid = $GET['id'];
		$xc = M('jiaoyu_xiangce');
		$data['jid'] = $_GET['id'];
		$result = $xc->where($data)->select();
		//dump($result);
		//exit;
		$this->assign('photo',$result);
		$this->display();
	}
	public function plist(){
		$id = $_GET['id'];
		//echo $id;
		//exit;
		$xp = M('jiaoyu_xiangpian');
		$result = $xp->where("pid=$id")->select();
		//dump($result);
		//exit;
		$this->assign('photo',$result);
		$this->display();
	}
	//培训课程
	public function huxing(){
		$where['jid'] = $this->_get('id');
		
		$xq = M('jiaoyu_xiaoqu');
		$result = $xq->where($where)->select();

		$kc = M('jiaoyu_kecheng');
		$result1 = $kc->where($where)->select();
		
		foreach($result as $key=>$value){
			$result['kecheng']=array();
			foreach($result1 as $key2=>$value2){
				if($value[name]==$value2['campus']){
					$result[$key]['kecheng'][]=$value2;
				}
			}
		}
		$this->assign('list',$result);
		$this->display();
	}

	//课程详情
	public function kcxq(){
		$kc = M('jiaoyu_kecheng');
		$where['id'] = $this->_get('id');
		$result = $kc->where($where)->find();
		$this->assign('list',$result);
		$this->display();
	}
	public function addreview(){
		$id = $_GET['id'];
		$token = $_GET['token'];
		$this->assign('id',$id);
		$this->assign('token',$token);
		$this->display();
	}
	public function add(){
		$yx = M('jiaoyu_yingxiang');
		if(empty($_POST['name'])){
			$this->error('名称不能为空');
		}elseif(empty($_POST['content'])){
			$this->error('内容不能为空');
		}else{
		$data['name'] = $_POST['name'];
		$data['content'] = $_POST['content'];
		$data['jid'] = $_POST['id'];
		$data['time'] = time();
		$data['token'] = $_POST['token'];
		$result = $yx->add($data);
		$this->redirect('Education/addreview',array('id'=>$_POST['id'],'token'=>$_POST['token']));
		//$this->success('添加成功',U('Education/addreview'));
		}
	}
	//讲师风采
	public function jiangshi(){
		$where['jid'] = $this->_get('id');
		$where['token'] = $this->_get('token');
		$result = M('jiaoyu_jiangshi')->where($where)->select();
		$this->assign('list',$result);
		$this->display();
	}
}
?>