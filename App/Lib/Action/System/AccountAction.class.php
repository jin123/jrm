<?php

class AccountAction extends SystemAction{
	
	public function index(){
		import('@.ORG.Page');
		$wxuser_db = D('Outrecords');
       if (IS_POST) {
            if (isset($_POST['searchkey']) && trim($_POST['searchkey'])) {
                $where['title'] = array(
                    'like',
                    '%' . trim($_POST['searchkey']) . '%'
                );
            }
        }
		if($this->_get('gid')){
			$where['gid'] = $this->_get('gid');
			$this->assign('dluser',$dluser);
		}
		$where['gid'] = session('dluserId');
        $count     = $wxuser_db->where($where["id"])->count();
        $Page      = new Page($count, 15);
        $show      = $Page->show();
        $list      = $wxuser_db->where($where["id"])->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$dlnums   = M("daili")->where(array('id' => session('dluserId')))->field('dltotal,dlnums')->find();
		
		$this->assign('dlnums',$dlnums);
		$this->assign('wxcount',$count);
		$this->assign('wxlist',$list);
        $this->assign('page', $show);
		$this->display();
	}

   
	
    public function del()
    {
        M('Outrecords')->where(array(
            'id' => intval($_GET['id']),
			//'uid'=>session('dluserId')
        ))->delete();
        $this->success('操作成功');
    }
	

}
?>