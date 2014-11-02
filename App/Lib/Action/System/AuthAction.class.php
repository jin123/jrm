<?php

class AuthAction extends SystemAction{
	
	public function index(){
		import('@.ORG.Page');
		if (IS_POST) {
			if (isset($_POST['searchkey']) && trim($_POST['searchkey'])) {
				$where['uname'] = array(
				'like',
				'%' . trim($_POST['searchkey']) . '%'
				);
			}
		}
		$count     = D('Authadmin')->where($where)->count();
		$Page      = new Page($count, 15);
		$show      = $Page->show();
		$list      = D('Authadmin')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$this->assign('dlcount',$count);
		$this->assign('list',$list);
		$this->assign('page', $show);
		$this->display();
	}

    function set(){
		if(IS_POST){
			$where['uname'] = $this->_post('uname');
			$_POST['starttime'] = strtotime($this->_post('starttime'));
			$_POST['endtime'] = strtotime($this->_post('endtime'));

			$result = D('Authadmin')->where($where)->find();
			if($result){
				$where['id'] = $this->_get('id');
				D('Authadmin')->where($where)->save($_POST);				
			}else{
				D('Authadmin')->add($_POST);
			}
            $this->success('设置成功', U('Auth/index'));
		}else{
			$where['id'] = $this->_get('id');
			$Authadmin = D('Authadmin')->where($where)->find();
			if($Authadmin['serialnum']){
				$this->assign('serialnum',$Authadmin['serialnum']);
			}else{
				$code=$this->randStr(13);//这里的数字可以换成你想生成的字符串个数
				$this->assign('serialnum',$code);
			}
			$this->assign('Authadmin',$Authadmin);
			$this->display();
		}
    }
	
	function del(){
		$where['id'] = $this->_get('id');
        M('Authadmin')->where($where)->delete();
        $this->success('操作成功');	
	}
	
	function randStr($len) {
		$s = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; //随机数字(字母)取值范围
		$max = strlen($s)-1;
		$result = '';
		for ($i=1; $i<$len; $i++) {
			if($i%4==0){
				$r = rand(0, $max);
				$result .= $s[$r].'-';
			}else{
				$r = rand(0, $max);
				$result .= $s[$r];
			}
		}
		
		return rtrim($result,'-');
	}

}
?>