<?php
class AdviceAction extends Action
{
    public $token;
    public $pid;

    public function _initialize()
    {
		$this->wecha_id=$this->_get('wecha_id');
        $this->token = $this->_get('token');
		$this->pid = $this->_get('pid');
        $this->assign('token', $this->token);
        $this->assign('pid', $this->pid);
    }
    public function index()
    {
			$pid = $this->pid;
			//echo $pid;
			$token = $this->token;
			$api=M('Diymen_set')->where(array('token'=>$token))->find();
			$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$json=json_decode($this->curlGet($url_get));
			$access_token = $json->access_token;
			$openid =  $this->wecha_id;
			$this->assign('openid',$openid);
			$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
			$json2=json_decode($this->curlGet($url));
			$name = $json2->nickname;
			$data = M('advice_set');
			$ly_data = $data->where('id='.$this->pid)->find();
		
			$ly = M('advice');
			$mres = $ly->where(array('pid'=>$this->pid,'wecha_id'=>$openid,'isval'=>'1'))->order('id desc')->select();
			foreach($mres as $key=>$val){
			    $where['replay_id'] = $val['id'];
			    $relay = M('replay')->where($where)->order('id desc')->limit(2)->select();
			    $mres[$key]['replay'] = $relay;
			
			}
			//dump($ly_data);exit;
			$this->assign('ly_data', $ly_data);
			$this->assign('mres', $mres);
			$this->assign('name', $name);
			$this->assign('pid', $pid);
            $this->display();
    }

	    public function add()
    {
		$wecha_id=$this->_get('wecha_id');
    	//echo "<pre>";
    	//var_dump($_REQUEST);
			if(!IS_AJAX) halt('页面不存在');
			$name = I('name');
			$msg = I('msg');
			$pid = I('pid');
			$tel=I('tel');
			$email=I('email');
			//echo $tel;exit;
			$issh = M('advice_set')->where('id='.$pid)->getField('issh');
			if($issh==1){
				$isval = 0;
			}else{
				$isval = 1;
			}
			$data = array(
            'pid' => $pid, 
            'name' => $name, 
            'time' => time(), 
            'msg' => $msg, 
			'isval' => $isval, 
			'wecha_id'=>$wecha_id,
			'tel'=>$tel,
			'email'=>$email,
            );
			//dump($data);exit;
    		M('advice')->data($data)->add(); 
			
    }


	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}

   
}
?>