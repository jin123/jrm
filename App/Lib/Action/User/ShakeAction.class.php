<?php
class ShakeAction extends CommonAction{

	public function _initialize() {
		//$this->panorama_model=M('Panorama');
	}
	public function admin(){
		$where=array('token'=>session('token'));
		$list=M('Shake')->where($where)->order('start_time desc')->select();
		$count      = M('Shake')->where($where)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();
		//$Page = new Page($count,20);
		$listwx=M('Linkwx_set')->where($where)->find();
		$blogtime=time();
		$this->assign('list',$list);
		$this->assign('listwx',$listwx);
		$this->assign('blogtime',$blogtime);
		$this->assign('page',$show);
		$this->display();
	}
	public function record(){
		/*$where['shakeid']="1";
		$Model = new Model();
		$sql   = 'select a.id as id,a.strength as strength,b.nickname as nickname  from hy_shake_parter as a left join hy_wechater as b on a.wechater = b.wechater where a.shakeid=1 order by a.strength desc';
		$list  = $Model->query($sql);*/
		$where=array('token'=>session('token'));
		$shake_id=M('Shake')->where($where)->order('id desc')->find();
		$shake= M('Shake')->where($where)->order('id desc')->select();
		$shakeid=$shake_id['id'];
		$where_page['shakeid']=$shakeid;
		$where_page['token']=session('token');
		$count      = M('Shake_parter')->where($where_page)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();

		$list = D('Shake')->getTop($shakeid);
		$this->assign('list',$list);
		$this->assign('shake',$shake);
		$this->assign('page',$show);
		$this->display();
	}
	public function edit(){
		if(IS_POST){
			$keyword=$_REQUEST['keyword'];
			$name=$_REQUEST['name'];
			$duration=$_REQUEST['duration'];
			$start_time=$_REQUEST['start_time'];
			$delay=$_REQUEST['delay'];
			$id=$_REQUEST['id'];
			$info=$_REQUEST['info'];
			$pic=$_REQUEST['cover'];
			//$uid=$_REQUEST['uid'];
			$where['id']=trim($id);	
			$data['keyword']=trim($keyword);
			$data['name']=trim($name);
			$data['duration']=trim($duration);
			$data['start_time']=strtotime($start_time);
			$data['delay']=trim($delay);
			$data['info']=trim($info);
			$data['pic']=trim($pic);
			$list=M('Shake')->where($where)->save($data);
			if($id==null||$id==""){
				$data['token']=session('token');
				$data['create_time']=time();
				$list=M('Shake')->add($data);
				if($list===false){
					$this->error("添加活动失败");
				}else{
					$listkey=M('Shake')->order('create_time desc')->find();
					//var_dump($listkey);
					$key['token']=session('token');
					$key['keyword']=trim($keyword);
					$key['pid']=$listkey['id'];
					$key['module']="Shake";
					//var_dump($key);exit;
					$listkeyword=M('Keyword')->add($key);
					$this->success('添加活动成功',U('Shake/admin'));
				}
			}else{
				$where['token']=session('token');
				$list=M('Shake')->where($where)->save($data);
				if($list===false){
					$this->error("修改资料失败");
				}else{
					$key['keyword']=trim($keyword);
					$key['module']="Shake";
					$wherekey['pid']=trim($id);
					$wherekey['token']=session('token');
					$listkeyword=M('Keyword')->where($wherekey)->save($key);
					$this->success('修改成功',U('Shake/admin'));
				}
			}
			
		}else{
			$id=$_REQUEST['id'];
			$where['id']=$id;
			$where['token']=session('token');
			$list=M('Shake')->where($where)->find();
			$this->assign('list',$list);
			//$this->assign('blogtime',$blogtime);
			$this->display();
		}
	}
	public function delall(){
		$shakeid=$_REQUEST['shakeid'];
		$where['shakeid']=trim($shakeid);
		//var_dump($where['id']);exit;
		$list_del=M('Shake_parter')->where($where)->delete();
		if($list_del===false){
			//$this->error("删除失败");
			$result['code']="001";
			$result['info']="删除失败";
		}else{
			//$this->success('删除成功');
			$result['code']="000";
			$result['info']="删除成功";
		}
		 die(json_encode($result));
	}
	public function del(){
		$id=$_REQUEST['id'];
		//var_dump($id);exit;
		$where['id']=trim($id);
		$list_del=M('Shake_parter')->where($where)->delete();
		if($list_del===false){
			$this->error("删除失败");
		}else{
			$this->success('删除成功');
		}	
	}
	public function change(){
		$shakeid = $_REQUEST['shakeid'];
		$where_dur['shakeid']=$shakeid;
		//$list_dur=M('Shake')->where($where_dur)->find();
		//file_put_contents('123456.txt',M('Shake')->getLastSql());
		//$duration=$list_dur['duration'];

        $result['info'] = D('Shake')->getTop($shakeid);
		
		file_put_contents('123456.txt',$result['info'] );
        echo json_encode($result);
		
	}
	public function delete(){
		$id=trim($_REQUEST['id']);
		$where['id']=$id;
		$where['token']=session('token');
		$check=M('Shake')->where($where)->find();
        if($check===false)   $this->error('非法操作');
		$list_del=M('Shake')->where($where)->delete();
		if($list_del===false){
			$this->error("删除失败");
		}else{
			$wherekey['pid']=$id;
			$wherekey['token']=session('token');
			$list_key=M('Keyword')->where($wherekey)->delete();
			$this->success('删除成功');
		}	
	}
	 public function entry()
    {
		$shakeid=trim($_REQUEST['shakeid']);
		$token=session('token');
        $this->nTime = time();
        $this->shakeInfo = D('Shake')->getShakeInfo($shakeid,$token);
        if (!empty($this->shakeInfo['start_time'])&&$this->shakeInfo['start_time']<time()) {
			$this->error('活动已经结束',U('Shake/admin'));
            //$this->redirect('Shake/process', array('shakeid'=>$_GET['shakeid']));
        }
		$where['islock']="1";
		$check=M('Shake')->where($where)->find();
		if($check===false){
			$data['islock']="1";
			$whereupdate['id']=$shakeid;
			$update=M('Shake')->where($whereupdate)->save($data);
			if($update===false){
				$this->error('活动锁定失败',U('Shake/admin'));exit;
			}
		}else{
			$start_time=$check['start_time']+$check['delay'];
			if($start_time>time()&&time()>$check['start_time']){
				$n=$start_time-time();
				//$n=$n%60;
				$this->error('有活动处于锁定状态请'.$n.'秒后再试',U('Shake/admin'));exit;
			}
			$data['islock']="0";
			$updatelock=M('Shake')->where($where)->save($data);
			$dataup['islock']="1";
			$whereupdate['id']=$shakeid;
			$update=M('Shake')->where($whereupdate)->save($dataup);
			if($update===false){
				$this->error('活动锁定失败',U('Shake/admin'));exit;
			}
		}
		$this->assign('shakeinfo',$this->shakeInfo);
		$this->assign('shakeid',$shakeid);
        $this->display();
    }
	public function startShake()
    {
        $map['id'] = trim($_POST['shakeid']);
        $data['start_time'] = time();
        if (M('Shake')->where($map)->save($data)) {
            $result['success'] = 1;
        } else {
            $result['success'] = 0;
        }
        die(json_encode($result));
    }
	public function process()
    {	$shakeid=trim($_REQUEST['shakeid']);
		$token=session('token');
        $this->nTime = time();
        $this->shakeInfo = D('Shake')->getShakeInfo($shakeid,$token);
		//var_dump($this->shakeInfo);exit;
		if (!empty($this->shakeInfo['start_time'])&&$this->shakeInfo['start_time']>time()) {
			$this->error('活动尚未开始没有记录',U('Shake/admin'));
            //$this->redirect('Shake/process', array('shakeid'=>$_GET['shakeid']));
        }
		file_put_contents('process.txt',D('Shake')->getLastSql());
		$this->assign('shakeinfo',$this->shakeInfo);
		$this->assign('shakeid',$shakeid);
        $this->display();
    }
	public function getTop()
    {
        $shakeid = $_GET['shakeid'];
		$where_dur['shakeid']=$shakeid;
		$list_dur=M('Shake')->where($where_dur)->find();
		file_put_contents('123456.txt',M('Shake')->getLastSql());
		$duration=$list_dur['duration'];

        $result['info'] = D('Shake')->getTop($shakeid);
		$result['log']=$duration;
		file_put_contents('123456.txt',$result);
        echo json_encode($result);
    }
    
    public function records(){
    	$shakeid=$this->_get('shakeid');
    	//dump($shakeid);exit;
    	$where=array('token'=>session('token'));
    	//$shake_id=M('Shake')->where($where)->order('id desc')->find();
    	$shake= M('Shake')->where($where)->order('id desc')->select();
    	//$shakeid=$shake_id['id'];
    	$where_page['shakeid']=$shakeid;
    	$where_page['token']=session('token');
    	$count      = M('Shake_parter')->where($where_page)->count();
    	$Page       = new Page($count,20);
    	$show       = $Page->show();
    
    	$list = D('Shake')->getTop($shakeid);
    	$this->assign('list',$list);
    	$this->assign('shake',$shake);
    	$this->assign('page',$show);
    	$this->display('Shake/record');
    }

}


?>