<?php

class KehuAction extends SystemAction{
	
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
			$dluser = D('Daili')->where('id='.$this->_get('dlid'))->field('dluser')->find();
			$this->assign('dluser',$dluser);
		}
        $count     = $wxuser_db->where($where)->count();
        $Page      = new Page($count, 15);
        $show      = $Page->show();
        $list      = $wxuser_db->where($where)->order('createtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
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
				$_POST['email'] = $_POST['email'];
				$_POST['telephone'] = $_POST['telephone'];
				$_POST['qq'] = $_POST['qq'];
				$_POST['remark'] = $_POST['remark'];

				$randLength=6;
				$chars='abcdefghijklmnopqrstuvwxyz';
				$len=strlen($chars);
				$randStr='';
				for ($i=0;$i<$randLength;$i++){
					$randStr.=$chars[rand(0,$len-1)];
				}
				$tokenvalue=$randStr.time();
				$_POST['token'] = $tokenvalue;

				$result = $wxuser_db->where(array('username'=>$_POST['username']))->find();
				if($result){
					$this->error('用户已经存在！');
				}
                $wxuser_db->add($_POST);
            } else {
				if($_POST['password'] ==''){
					unset($_POST['password']);
				}else{
					$_POST['password'] = md5($_POST['password']);
				}
				$_POST['email'] = $_POST['email'];
				$_POST['telephone'] = $_POST['telephone'];
				$_POST['qq'] = $_POST['qq'];
				$_POST['remark'] = $_POST['remark'];
				
                $wxuser_db->where(array(
                    'id' => intval($_GET['id'])
                ))->save($_POST);
            }
            $this->success('设置成功', U('Kehu/index'));
        } else {
            if (isset($_GET['id'])) {
                $wxdata = $wxuser_db->where(array(
                    'id' => intval($_GET['id'])
                ))->find();
            }
            $this->assign('wxdata', $wxdata);
            $this->display();
        }
    }
	
    public function del()
    {
        M('wxuser')->where(array(
            'id' => intval($_GET['id'])
        ))->delete();
        $this->success('操作成功');
    }
	

}
?>