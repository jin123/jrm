<?php 
function strExists($haystack, $needle)
{
	return !(strpos($haystack, $needle) === FALSE);
}
class IndexAction extends Action{

	private $tpl;	//微信公共帐号信息
	private $info;	//分类信息

	private $wecha_id;
	public $company;
	public $token;
	public $weixinUser;
	public $homeInfo;
	public $jszc;
	
	public function _initialize(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")&&!isset($_GET['show'])) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$this->token=$this->_get('token','trim');
		$where['token']=$this->token;
		
		$tpl=D('Moban')->where($where)->find();
		$this->weixinUser=$tpl;
		
		$homeInfo=D('Wxuser')->where($where)->find();
		$this->homeInfo=$homeInfo;
		
		
		
		if (isset($_GET['wecha_id'])&&$_GET['wecha_id']){
			$_SESSION['wecha_id']=$_GET['wecha_id'];
			$this->wecha_id=$this->_get('wecha_id');
		}
		if (isset($_SESSION['wecha_id'])){
			$this->wecha_id=$_SESSION['wecha_id'];
		}
		$isrecycle=0;
		$info=M('Classify')->where(array('token'=>$this->_get('token'),'status'=>1,'isrecycle'=>$isrecycle))->order('sorts desc')->select();
		
		$info=$this->convertLinks($info);//加外链等信息
		$maplist = M('Company')->where(array('token'=>$this->_get('token'),'isbranch'=>0))->find();
		$this->assign('maplist',$maplist);

		$this->info=$info;
		$this->tpl=$tpl;
		$this->assign('token',$this->token);
		$this->assign('homeInfo',$homeInfo);

		if($homeInfo['dlid']){
			$dlInfo = M('daili')->where(array('id'=>$homeInfo['dlid']))->field('copyright,jszc')->find();
			if($dlInfo['copyright']&&$dlInfo['jszc']){
				list($dlInfo['copy'],$dlInfo['url']) = split("[|]", $dlInfo['copyright'],$dlInfo['jszc']);
				$this->assign('daili',$dlInfo['copyright']);
				$this->assign('jszc',$dlInfo['jszc']);
			}
		}
	}
	
	function test(){
	
	   $this->display();
	
	}
	public function classify(){
		$this->assign('info',$this->info);
		$this->display($this->tpl['tpltypename']);
	}
	
	public function index(){
		$this->assign('copyright',$copyright = array('name'=>'阿里微微','url'=>'http://www.alivv.net'));
		$where['token']=$this->_get('token');
		$where['type']=0;
		//$where['isrecycle']=0;
		//dump($where);
		//$where['status']=1;
		$flash=M('Flash')->where($where)->select();
		$flash=$this->convertLinks($flash);

		$count=count($flash);
		$this->assign('flash',$flash);
		//dump($flash);exit;
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->assign('info',$this->info);
		$this->assign('tpl',$this->tpl);
		$data = M('wxuser')->where(array('token'=>$this->token))->find();
		$this->assign('data',$data);
    // var_dump($this->tpl['tpltypename']);
		$this->display($this->tpl['tpltypename']);
	}
	
	public function lists(){
	
		$where['token']=$this->_get('token','trim');
		$where['isrecycle']=0;
		$db=D('Img');	
		if($_GET['p']==false){
			$page=1;
		}else{
			$page=$_GET['p'];			
		}	
			
		$where['classid']=$this->_get('classid','intval');
		$count=$db->where($where)->count();	
		$pageSize=8;	
		
		$pagecount=ceil($count/$pageSize);
	//	dump($this->tpl['tpllistname']);
		if($page > $count){$page=$pagecount;}
		if($page >=1){$p=($page-1)*$pageSize;}
		if($p==false){$p=0;}
	
		$res=$db->where($where)->order('createtime DESC')->limit("{$p},".$pageSize)->select();
		
		$res=$this->convertLinks($res);
		$this->assign('page',$pagecount);
		$this->assign('p',$page);
		$this->assign('info',$this->info);
			
		$this->assign('tpl',$this->tpl);
		$this->assign('res',$res);
	
		if ($count==1){
	//	dump($res[0]['id']);
			$this->content($res[0]['id']);
			
			exit();
		}
		//
			
		$this->display($this->tpl['tpllistname']);
	}
	public function listss(){
		$where['token']=$this->_get('token','trim');
		$where['isrecycle']=-1;
		$db=D('Img');
		if($_GET['p']==false){
			$page=1;
		}else{
			$page=$_GET['p'];
		}
		$where['classid']=$this->_get('classid','intval');
		$count=$db->where($where)->count();
		$pageSize=8;
		$pagecount=ceil($count/$pageSize);
		if($page > $count){$page=$pagecount;}
		if($page >=1){$p=($page-1)*$pageSize;}
		if($p==false){$p=0;}
		$res=$db->where($where)->order('createtime DESC')->limit("{$p},".$pageSize)->select();
		$res=$this->convertLinks($res);
		$this->assign('page',$pagecount);
		$this->assign('p',$page);
		$this->assign('info',$this->info);
	
		$this->assign('tpl',$this->tpl);
		$this->assign('res',$res);
		if ($count==1){
			$this->content($res[0]['id']);
			exit();
		}
		$this->display($this->tpl['tpllistname']);
	}
	
	public function content($contentid=0){
		$db=M('Img');
		$where['token']=$this->_get('token','trim');
		$where['isrecycle']=0;
		if (!$contentid){
			$contentid=intval($_GET['id']);
		}
		$where['id']=array('neq',$contentid);
		$lists=$db->where($where)->limit(5)->order('uptatetime')->select();
		//dump($lists);exit;
		$where['id']=$contentid;
		$res=$db->where($where)->find();
		$this->assign('info',$this->info);	//分类信息
		$this->assign('lists',$lists);		//列表信息
		$this->assign('res',$res);			//内容详情;
		$this->assign('tpl',$this->tpl);				//微信帐号信息
		$this->display($this->tpl['tplcontentname']);
	}
	public function contents($contentid=0){
		$db=M('Img');
		$where['token']=$this->_get('token','trim');
		$where['isrecycle']=-1;
		if (!$contentid){
			$contentid=intval($_GET['id']);
		}
		$where['id']=array('neq',$contentid);
		$lists=$db->where($where)->limit(5)->order('uptatetime')->select();
		//dump($lists);exit;
		$where['id']=$contentid;
		$res=$db->where($where)->find();
		$this->assign('info',$this->info);	//分类信息
		$this->assign('lists',$lists);		//列表信息
		$this->assign('res',$res);			//内容详情;
		$this->assign('tpl',$this->tpl);				//微信帐号信息
		$this->display($this->tpl['tplcontentname']);
	}
	
	public function flash(){
		$where['token']=$this->_get('token','trim');
		$where['type']=0;
		$flash=M('Flash')->where($where)->select();
		$count=count($flash);
		$this->assign('flash',$flash);
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->display('ty_index');
	}
	/**
	 * 获取链接
	 *
	 * @param unknown_type $url
	 * @return unknown
	 */
	 
	public function getLink($url){
		$urlArr=explode(' ',$url);
		$urlInfoCount=count($urlArr);
		if ($urlInfoCount>1){
			$itemid=intval($urlArr[1]);
		}
		$link=str_replace(array('{wechat_id}','{siteUrl}'),array($this->wecha_id,C('site_url')),$url);
		if (!strpos($url,'wecha_id=')){
			if (strpos($url,'?')){
				$link=$link.'&wecha_id='.$this->wecha_id;
			}else {
				$link=$link.'?wecha_id='.$this->wecha_id;
			}
		}

		return $link;
	}

	public function convertLinks($arr){
		$i=0;
		foreach ($arr as $a){
			if ($a['url']){
				$arr[$i]['url']=$this->getLink($a['url']);
			}
			$i++;
		}
		return $arr;
	}

}

