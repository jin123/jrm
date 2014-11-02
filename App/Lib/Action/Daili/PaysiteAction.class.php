<?php

class PaysiteAction extends SystemAction{
	
	public function index(){
		import('@.ORG.Page');
		$wxuser_db = D('Wxuser');
       if (IS_POST) {
            if (isset($_POST['searchkey']) && trim($_POST['searchkey'])) {
                $where['username'] = array(
                    'like',
                    '%' . trim($_POST['searchkey']) . '%'
                );
            }
        }
		if($this->_get('dlid')){
			$where['dlid'] = $this->_get('dlid');
			$this->assign('dluser',$dluser);
		}
		$where['dlid'] = session('dluserId');
        $count     = $wxuser_db->where($where)->count();
        $Page      = new Page($count, 15);
        $show      = $Page->show();
        $list      = $wxuser_db->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$dlnums   = M("daili")->where(array('id' => session('dluserId')))->field('dltotal,dlnums')->find();
		
		$this->assign('dlnums',$dlnums);
		$this->assign('wxcount',$count);
		$this->assign('wxlist',$list);
        $this->assign('page', $show);
		$this->display();
	}

    function addset()
    {
		$wxuser_db = D('Wxuser');
        if (IS_POST) {
            $_POST['createtime']    = time();
			$starttime = explode('-',$_POST['starttime']);
			$_POST['starttime'] = mktime(0, 0, 0, $starttime[1], $starttime[2], $starttime[0]);
			$endtime = explode('-',$_POST['endtime']);
			$_POST['endtime'] = mktime(0, 0, 0, $endtime[1], $endtime[2], $endtime[0]);

            if (!isset($_GET['id'])) {
			
				$_POST['password'] = md5($_POST['password']);

				$randLength=6;
				$chars='abcdefghijklmnopqrstuvwxyz';
				$len=strlen($chars);
				$randStr='';
				for ($i=0;$i<$randLength;$i++){
					$randStr.=$chars[rand(0,$len-1)];
				}
				$tokenvalue=$randStr.time();
				$_POST['token'] = $tokenvalue;
				$_POST['dlid'] = session('dluserId');

				$result = $wxuser_db->where(array('username '=>$_POST['username'],'dlid='=>session('dluserId')))->find();
				
				if($result){
					$this->error('用户已经存在！');
				}
				$dlnums   = M("daili")->where(array('id' => session('dluserId')))->field('dltotal,dlnums')->find();
				$num = $dlnums['dltotal'] - $dlnums['dlnums'];
				if ($num <= 0) {
					$this->error('创建名额已满，请联系管理员！');
					return;
				}
                $wxuser_db->add($_POST);
				M('daili')->where(array('id' => session('dluserId')))->setDec('dltotal');
				M('daili')->where(array('id' => session('dluserId')))->setInc('dlnums');
            } else {
				if($_POST['password'] ==''){
					unset($_POST['password']);
				}else{
					$_POST['password'] = md5($_POST['password']);
				}
                $wxuser_db->where(array(
                    'id' => intval($_GET['id']),'dlid'=>session('dluserId')
                ))->save($_POST);
            }
            $this->success('设置成功', U('Kehu/index'));
        } else {
            if (isset($_GET['id'])) {
                $wxdata = $wxuser_db->where(array(
                    'id' => intval($_GET['id']),
                ))->find();
            }else{
            	$wxdata = array('starttime'=>time(),'endtime'=>time()+30*86400);
            }
            $this->assign('wxdata', $wxdata);
            $this->display();
        }
    }
	
    public function del()
    {
        M('wxuser')->where(array(
            'id' => intval($_GET['id']),
			'dlid'=>session('dluserId')
        ))->delete();
        $this->success('操作成功');
    }
	

}
?>