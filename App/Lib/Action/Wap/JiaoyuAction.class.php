<?php
class JiaoyuAction extends Action
{
    public $token;
    public $wecha_id;
    public $pid;

    public function _initialize()
    {
        $this->token    = $this->_get('token');
        $this->wecha_id = $this->_get('wecha_id');
		$this->pid = $this->_get('pid');
        $this->assign('token', $this->token);
        $this->assign('wecha_id', $this->wecha_id);
        $this->assign('wecha_id', $this->pid);
		$where = array('id'=>$this->pid,'token'=>$this->token);
        $get_ids = M('Jiaoyu')->where($where)->field('res_id,classify_id')->find();
        $this->assign('rid', $get_ids['res_id']);
    }
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($agent, "icroMessenger")) {
        }
        $data  = M("Jiaoyu");
        $this->token = $this->_get('token');
		$where = array('id'=>$this->pid,'token'=>$this->token);
        $where2 = array('pid'=>$this->pid);
        $jy_data = $data->where($where)->find();
        $haibao = M("jiaoyu_haibao")->where($where2)->find();
        $daohang = M("jiaoyu_daohang")->where($where2)->find();
        $this->assign('jy_data', $jy_data);
        $this->assign('haibao', $haibao);
        $this->assign('daohang', $daohang);
        $this->assign('pid', $this->pid);
        $this->display();
    }
    public function jieshao()
    {
        $data  = M("Jiaoyu");
        $where = array('id'=>$this->pid);
        $jy_data = $data->where($where)->find();
        $this->assign('jy_data', $jy_data);
        $this->display();
    }
    public function xiangce()
    {
        $data  = M("Jiaoyu_album");
        $where = array('pid'=>$this->pid);
        $info = array();
        $xiangce = $data->where($where)->select();
        foreach ($xiangce as $value) {
            $info[]=M('Photo')->where(array('id'=>$value[poid]))->find();
        }
        $this->assign('info',$info);
        $this->display();
    }
    public function plist()
    {
        $photo_list=M('Photo_list')->where(array('pid'=>$this->_get('id')))->select();
        $this->assign('photo',$photo_list);
        $this->display();
    }
    public function kecheng()
    {
        $data  = M("Jiaoyu_housetype");
        $where = array('pid'=>$this->pid);
        $kecheng = $data->where($where)->select();
        foreach ($kecheng as $k => $v) {
            $son_type[] = M('Jiaoyu_son')->where(array('id' => $v['son_id']))->field('id as sid,title')->find();
        }
        foreach ($son_type as $key => $value) {
            foreach ($value as $k => $v) {
                $kecheng[$key][$k] = $v;
            }
        }
        $this->assign('scenery',$kecheng);
        $this->display();
    }
        public function dianping()
    {
        $data  = M("Jiaoyu_expert");
        $where = array('pid'=>$this->pid);
        $dianping = $data->where($where)->select();
        
        $this->assign('dianping',$dianping);
        $this->display();
    }
    public function kclist()
    {

        $photo_list = M('Jiaoyu_housetype')->where(array('id'=>$this->_get('id')))->field('type1,type2,type3,type4')->find();
       // var_dump($photo_list);exit;
        $this->assign('photo',$photo_list);
        $this->display();
    }
    
}
?>