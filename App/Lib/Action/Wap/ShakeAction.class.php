<?php
class ShakeAction extends Action{

	public function _initialize() {
		$this->token = $this->_get('token');
		$this->wecha_id	= $this->_get('wecha_id');
		$this->shakeid =$this->_get('shakeid');
	}

	public function index(){

		if (!D('Wechater')->hasRegister($this->wecha_id)) {
				$this->assign('wecha_id',$this->wecha_id);
				$this->assign('token',$this->token);
                $this->display('register');
                exit();
        }
		$this->nTime = time();
        $map['shakeid'] = $this->shakeid;
        $map['wechater'] = $this->wecha_id;
		//var_dump($map['shakeid']);exit;
        $strength = M('ShakeParter')->where($map)->getField('strength');
        $this->strength = $strength?$strength:0;

		$shakeInfo = D('Shake')->getShakeInfo($this->shakeid,$this->token);

		$this->assign('shakeInfo',$shakeInfo);
		$this->assign('strength',$this->strength);
		$this->assign('nTime',$this->nTime);
		$this->assign('wecha_id',$this->wecha_id);
		$this->assign('shakeid',$this->shakeid);
		$this->assign('token',$this->token);
        $this->display();

	}
	public function register()
    {
        if (IS_POST) {
            $wechaterModel = D('Wechater');
            $_POST['updatetime']=time();
            $info=$wechaterModel->where(array('wechater'=>$_POST['openid']))->save($_POST);
            if ($info) {
            	//$wechaterModel->updateInfo($_POST)
                $result['success'] = 1;
            } else {
                $result['success'] = 0;
                $result['errmsg'] = $wechaterModel->getLastSql();
            }
            echo json_encode($result);
        } else {
			$this->assign('wecha_id',$this->wecha_id);
			$this->assign('token',$this->token);
            $this->display();
        }
    }
	public function addStrength()
    {
        $strength = trim($_POST['strength'])?trim($_POST['strength']):0;
        $shakeid = trim($_POST['shakeid']);
        // $start_time = $_POST['start_time'];
        $wechater = trim($_POST['wechater']);
        if (D('Shake')->addStrength($strength, $shakeid, $wechater)) {
            $result['success'] = 1;
        } else {
            $result['success'] = 0;
        }
        echo json_encode($result);
    }
	public function isStart()
    {
        $shakeInfo = D('Shake')->getShakeInfo(trim($_REQUEST['shakeid']),trim($_REQUEST['token']));
		file_put_contents('shakeinfosql.txt',D('Shake')->getLastSql());
        $startTime = $shakeInfo['start_time'];
        $duration = $shakeInfo['duration'];
		//var_dump($duration);
        $nTime = time();
		//
		$where['shakeid']=trim($_REQUEST['shakeid']);
		$list_delay=M('Shake')->where($where)->find();
		$delay=$list_delay['delay'];
		
		//var_dump($shakeInfo);EXIT;
//var_dump($nTime);var_dump($startTime + $duration);exit;
//var_dump($startTime);var_dump($startTime + $duration);exit;
        if (empty($startTime) or $nTime < $startTime) {
            $result['start'] = 1;
        } elseif ($nTime > $startTime + $delay ) {//+ $duration
            $result['start'] = 3;
        } else {
            $result['start'] = 2;
        }
		$result['duration']=$duration;
        die(json_encode($result));
    }
}


?>