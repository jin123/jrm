<?php

class DailiAction extends SystemAction{
	
	public function index(){
		import('@.ORG.Page');
		$daili_db = D('Daili');
       if (IS_POST) {
            if (isset($_POST['searchkey']) && trim($_POST['searchkey'])) {
                $where['dluser'] = array(
                    'like',
                    '%' . trim($_POST['searchkey']) . '%'
                );
            }
        }
        $count     = $daili_db->where($where)->count();
        $Page      = new Page($count, 15);
        $show      = $Page->show();
        $list      = $daili_db->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$this->assign('dlcount',$count);
		$this->assign('dllist',$list);
        $this->assign('page', $show);
		$this->display();
	}

    function dlset()
    {
		$daili_db = D('Daili');

        if (IS_POST) {
            $_POST['createtime']    = time();
            if (!isset($_GET['dlid'])) {
				$_POST['dlpwd'] = md5($_POST['dlpwd']);
				$result = $daili_db->where('dluser = '.$_POST['dluser'])->find();
				if($result){
					$this->error('用户已经存在！');
				}
                $daili_db->add($_POST);
            } else {
				if($_POST['dlpwd'] ==''){
					unset($_POST['dlpwd']);
				}else{
					$_POST['dlpwd'] = md5($_POST['dlpwd']);
				}
                $daili_db->where(array(
                    'id' => intval($_GET['dlid'])
                ))->save($_POST);
            }
            $this->success('设置成功', U('Daili/index'));
        } else {
            if (isset($_GET['dlid'])) {
                $dldata = $daili_db->where(array(
                    'id' => intval($_GET['dlid'])
                ))->find();
            }
            $this->assign('dldata', $dldata);
            $this->display();
        }
    }
	
    public function del()
    {
        M('Daili')->where(array(
            'id' => intval($_GET['dlid'])
        ))->delete();
        $this->success('操作成功');
    }
	

}
?>