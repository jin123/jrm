<?php
class CommonAction extends Action{
	public function __construct(){
		parent::__construct();
		
	}
    public function api_notice_increment($url, $data)
    {
        $ch= curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }
	 public function save_userinfo($wecha_id,$data){
             $userinfo = M('userinfo')->where(array('wecha_id'=>$wecha_id))->find();
             if(!$userinfo){           
                $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$openinfo['access_token']."&openid=".$openinfo['openid']."&lang=zh_CN";
                $user= json_decode($this->curl_get($info_url),true);
                $data['wecha_id'] = $wecha_id;
                $data['created'] = time();
                $data['wechaname'] = $data['nickname'];
                $id = M('userinfo')->add($data);
             }
    }
     function curl_get($url){
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
	public function userinfo($get,$token){
	 $code = $get['code'];
	 $wxinfo = M('wxuser')->where(array('token'=>$token))->find();
	 $get_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$wxinfo['appid']."&secret=".$wxinfo['appsecret']."&code=".$code."&grant_type=authorization_code";
	 $get_info = $this->curl_get($get_url);
     $token_info = json_decode($get_info,true);
     $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$token_info['access_token']."&openid=".$token_info['openid']."&lang=zh_CN";
     $userinfo = json_decode($this->curl_get($info_url),true);
     return $userinfo;
	}
}

?>
