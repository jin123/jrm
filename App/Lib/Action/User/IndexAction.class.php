<?php

// 公众号管理
class IndexAction extends CommonAction {
    public function index(){
		$this->display();
    }

    public function welcome(){
    	$this->display();
    }

    public function left(){
    	$where['token'] = session('token');
    	$homeInfo=D('Wxuser')->where($where)->find();
    	$this->assign('homeInfo',$homeInfo);

    	if($homeInfo['dlid']){
			$dlInfo = M('daili')->where(array('id'=>$homeInfo['dlid']))->find();
			if($dlInfo['copyright']){
				list($dlInfo['copy'],$dlInfo['url']) = split("[|]", $dlInfo['copyright']);
				$this->assign('daili',$dlInfo);
			}
		}
    	$this->display();
    }

    public function wait(){
    	echo 'waiting...';
    }

    //一键初始化栏目  by lic
    public function init(){
    	$token = session('token');
    	if(IS_POST){
//企业简介、企业动态、在线预约、微商城、优惠券、微相册、微留言、微会员、微投票
//moban  eiiaeq1393932525

//企业简介
$introid = M('Img')->add(array('title'=>'企业简介','keyword'=>'简介','info'=>'企业简介','type'=>1,'token'=>$token));
$introurl = C('site_url')."/index.php?s=/Wap/Index/Content/id/".$introid."/token/".$token;
M('Classify')->add(array('name'=>'企业简介','token'=>$token,'status'=>1,'sorts'=>9,'url'=>$introurl));



//动态
$nid = M('Classify')->add(array('name'=>'企业动态','token'=>$token,'status'=>1,'sorts'=>8));
$introid = M('Img')->add(array('title'=>'企业动态','keyword'=>'','classid'=>$nid,'classname'=>'企业动态','info'=>'这里是一条动态信息','type'=>1,'token'=>$token));



//在线预约
$sid = M('Selfform')->add(array('name'=>'在线预约','content'=>'内容简介','time'=>C('time'),'token'=>$token));
if($sid){
	$data['name'] = "在线预约";
	$data['token'] = $token;
	$data['url'] = C('site_url')."/index.php?s=/Wap/Selfform/index/id/".$sid."/token/".$token;
	$data['status'] = 1;
	$data['sorts'] = 7;
	M('SelfformInput')->add(array(
		'formid'=>$sid,
		'displayname'=>'姓名',
		'fieldname'=>'name',
		'inputtype'=>'text',
		'options'=>'',
		'require'=>'1',
		'regex'=>'',
		'taxis'=>'1'
		));
	M('SelfformInput')->add(array(
		'formid'=>$sid,
		'displayname'=>'手机号',
		'fieldname'=>'phone',
		'inputtype'=>'text',
		'options'=>'',
		'require'=>'1',
		'regex'=>'/^13[0-9]{9}$|^15[0-9]{9}$|^18[0-9]{9}$/',
		'taxis'=>'2'
		));
	M('Classify')->add($data);
}


//微商城
$cid = M('ProductCat')->add(array('name'=>'默认分类','token'=>$token));
M('Product')->add(array('name'=>'我的产品','token'=>$token,'catid'=>$cid));
$shopurl = C('site_url')."/index.php?s=/Wap/Product/index/token/".$token;
M('Classify')->add(array('name'=>'微商城','token'=>$token,'status'=>1,'sorts'=>6,'url'=>$shopurl));



//优惠券
$lid = M('Lottery')->add(array(
	'title'=>'优惠劵',
	'startpicurl' =>'/Public/images/lottery/activity-lottery-start.jpg',
	'txt'=>'兑奖请联系我们',
	'sttxt'=>'兑奖请联系我们',
	'statdate'=>time(),
	'enddate'=>time()+86400*3,
	'info'=>'亲，请点击进入刮刮卡抽奖活动页面，祝您好运哦！',
	'endtitle'=>'活动已经结束了',
	'endpicurl'=>'/Public/images/guajiang/activity-scratch-card-end.jpg',
	'endinfo'=>'亲，活动已经结束，请继续关注我们的后续活动哦。',
	'fist'=>'优惠1',
	'fistnums'=>10,
	'type'=>3,
	'status'=>1,
	'canrqnums'=>1,
	'token'=>$token));
$couponurl = C('site_url')."/index.php?s=/Wap/Coupon/index/id/".$lid."/token/".$token;
M('Classify')->add(array('name'=>'优惠劵','token'=>$token,'status'=>1,'sorts'=>5,'url'=>$couponurl));



//微相册

M('Photo')->add(array('title'=>'企业相片','token'=>$token,'type'=>1));
$photourl = C('site_url')."/index.php?s=/Wap/Photo/index/token/".$token;
M('Classify')->add(array('name'=>'微相册','token'=>$token,'status'=>1,'sorts'=>4,'url'=>$photourl));



//微留言
$liuyanurl = C('site_url')."/index.php?s=/Wap/Liuyan/index/token/".$token;
M('Classify')->add(array('name'=>'微留言','token'=>$token,'status'=>1,'sorts'=>3,'url'=>$liuyanurl));



//微会员
$carid = M('MemberCardSet')->add(array(
	'cardname'=>'会员卡',
	'keyword'=>'会员卡',
	'logo'=>'/Public/images/cart_info/logo-card.png',
	'bg'=>'/Public/images/card/card_bg17.png',
	'diybg'=>'/Public/images/card/card_bg17.png',
	'msg'=>'微时代会员卡，方便携带收藏，永不挂失',
	'numbercolor'=>'#000000',
	'vipnamecolor'=>'#121212',
	'token'=>$token));
//echo M('MemberCardSet')->getLastSql();exit;
$memberurl = C('site_url')."/index.php?s=/Wap/Card/get_card/cardid/".$carid."/token/".$token;
M('Classify')->add(array('name'=>'微会员','token'=>$token,'status'=>1,'sorts'=>2,'url'=>$memberurl));



//微投票
$vid = M('Vote')->add(array(
	'title'=>'这里是投票',
	'keyword'=>'投票',
	'info'=>'这里是投票',
	'statdate'=>time(),
	'enddate'=>time()+86400*3,
	'display'=>1,
	'token'=>$token));
M('VoteItem')->add(array('vid'=>$vid,'item'=>'A 选项','rank'=>1));
M('VoteItem')->add(array('vid'=>$vid,'item'=>'B 选项','rank'=>1));
$voteurl = C('site_url')."/index.php?s=/Wap/Vote/index/id/".$vid."/token/".$token;
M('Classify')->add(array('name'=>'微投票','token'=>$token,'status'=>1,'sorts'=>1,'url'=>$voteurl));


$this->success('一键设置完成，建议您预览您的站点查看效果。',U('Index/welcome'));
exit;

    	}
    	$this->display();
    }

    public function clearsite(){
    	$token = session('token');
    	if(!$token) $this->error('token 丢失');
    	
    	M('Classify')->where(array('token'=>$token))->delete();
    	M('Img')->where(array('token'=>$token))->delete();
    	M('Selfform')->where(array('token'=>$token))->delete();
    	M('ProductCat')->where(array('token'=>$token))->delete();
    	M('Product')->where(array('token'=>$token))->delete();
    	M('Lottery')->where(array('token'=>$token))->delete();
    	M('MemberCardSet')->where(array('token'=>$token))->delete();
    	M('Vote')->where(array('token'=>$token))->delete();
    	M('Photo')->where(array('token'=>$token))->delete();

    	$this->success('清空所有栏目完成',U('Index/init'));
		exit;
    }

	public function edit(){
		$wxuser = D('Wxuser');
		$data = $wxuser->where(array('token'=>session('token')))->find();
		if(IS_POST){
			$where['token'] =session('token');
			$where['id'] = $data['id'];
			$wxuser->where($where)->save($_POST);
			$this->success('设置成功！');	
		}else{
		$this->assign('data',$data);
		$this->display();
		}
	}
	
	public function insert(){
		$file=$this->_post('files');
		unset($_POST['files']);

		if($this->update_config($_POST,CONF_PATH.$file)){
			$this->success('操作成功',U('Index/edit'));
		}else{
			$this->success('操作失败',U('Index/edit'));
		}		
	}
	
	/*private function update_config($config, $config_file = '') {
		!is_file($config_file) && $config_file = CONF_PATH . 'web.php';
		if (is_writable($config_file)) {
			//$config = require $config_file;
			//$config = array_merge($config, $new_config);
			//dump($config);EXIT;
			file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
			@unlink(RUNTIME_FILE);
			return true;
		} else {
			
			return false;
		}
	}*/
	
	public function baseinfo(){
		$db=M('wxuser');
		$data=$db->find();
		$this->assign('dinfo',$data);
		$this->display();
	}
	
	public function pwdsave(){
		if(session('verify') != md5($_POST['verify'])) {
			$this->error('验证码错误！');
		}
		if(empty($_POST['userpwd']) || $_POST['userpwd'] != $_POST['userpwdok']) {
			$this->error('请确认新密码！');
		}
		$where['pwd'] =  $this->_post('oldpwd','md5');
		$result = M('admin')->where($where)->find();
		if(!$result){
			$this->error('原始密码错误，请确认新密码！');
		}
		$data['pwd'] =  $this->_post('userpwd','md5');
		$id .= 'id ='.$this->_post('id');
		$admin =M('admin')->where($id)->save($data);
		if($admin){
			$this->success('修改密码成功！');
		}else{
			$this->error('修改密码失败！');
		}
	}
	
	public function api(){
		$this->assign('token',session('token'));
		$this->display();
	}
	
}