<?php
class WechaterModel extends Model
{
    public $errMsg;

    public function hasRegister($wechater)
    {
        $map['wechater'] = $wechater;
        $result = M('Wechater')->where($map)->find();
		//file_put_contents('wechater.txt',M('Wechater')->getLastSql());
		//var_dump($result);exit;
        if (!$result) {
            return false; 
        } else {
            if (empty($result['nickname']) or empty($result['tel'])) {
                return false;
            } else {
                return true;
            }
        }
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
    public function updateInfo($data)
    {
        $map['wechater'] = $_POST['openid'];
		//$map['wechater'] = "ov42Fjmodj5MvXz1d6uKWqyE2sx0";
		//获取用户基本信息
		$where['token'] = $_POST['token'];
		$listwx=M('Diymen_set')->where($where)->find();
		$appid=$listwx['appid'];
		$appsecret=$listwx['appsecret'];
		$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
		$json=json_decode($this->curlGet($url_get));
		$access_token = $json->access_token;
		$openid =  $map['wechater'];
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$json2=json_decode($this->curlGet($url));
		//var_dump($json2);exit;
		$nickname = $json2->nickname;//用户昵称
		$sex = $json2->sex;//用户性别				
		$city = $json2->city;//用户所在城市
		$province = $json2->province;//用户所在省份
		$country = $json2->country;//用户所在国家
        $headimgurl = $json2->headimgurl;//用户头像
		$subscribe_time = $json2->subscribe_time;//用户关注时间
		$tel = $_POST['tel'];
		$info['wechater']=$map['wechater'];
		$info['nickname']=$nickname;
		$info['tel']=$tel;
		$info['headimgurl']=$headimgurl;
		$info['sex']=$sex;
		$info['city']=$city;
		$info['province']=$province;
		$info['country']=$country;
		$info['subscribe_time']=$subscribe_time;
		
		$result = $this->where($map)->find();
		//var_dump($result);exit;
        if (!$result) {
            $data['wechater'] = $wechater;
            if ($this->add($info)) {
                return true;
            } else {
                $this->errMsg = '新增注册信息失败';
                return false;
            }
        } else {
            if ($this->where($map)->save($info)) {
                return true;
            } else {
                $this->errMsg = '修改个人信息失败';
                return false;
            }
        }
    }
}
