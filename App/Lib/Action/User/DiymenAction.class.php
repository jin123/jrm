<?php
/**
 *自定义菜单
**/
class DiymenAction extends CommonAction{

	public function index(){
		$where['token'] = session('token');
		$data=M('Diymen_set')->where($where)->find();
		if(IS_POST){
			if($data==false){
				$this->all_insert('Diymen_set');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Diymen_set');
			}
		}else{
			$this->assign('diymen',$data);
			$class=M('Diymen_class')->where(array('pid'=>0,'token'=>session('token')))->order('sort asc')->select();
			foreach($class as $key=>$vo){
				$c=M('Diymen_class')->where(array('pid'=>$vo['id'],'token'=>session('token')))->order('sort asc')->select();
				$class[$key]['class']=$c;
			}
			$this->assign('class',$class);
			$this->display();
		}
	}


	public function  class_add(){
		if(IS_POST){
			$_POST['id'] = $this->_get('id');
			$_POST['url'] = $this->_post('url','trim');
			$_POST['keyword'] = $this->_post('keyword','trim');
			$this->all_insert('Diymen_class','/index');
		}else{
			$class=M('Diymen_class')->where(array('pid'=>0,'token'=>session('token')))->order('sort desc')->select();
			$this->assign('class',$class);
			$this->display();
		}
	}
	
	public function  class_del(){
		$class=M('Diymen_class')->where(array('pid'=>$this->_get('id'),'token'=>session('token')))->order('sort desc')->find();
		if($class==false){
			$back=M('Diymen_class')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->delete();
			if($back==true){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('请删除该分类下的子分类');
		}


	}
	
	public function  class_edit(){
		if(IS_POST){
			$_POST['id'] = $this->_get('id');
			$_POST['url'] = $this->_post('url','trim');
			$_POST['keyword'] = $this->_post('keyword','trim');
			
			if($_POST['id']==$_POST['pid'])
			{
				$this->error('您所操作的数据不正确！');	
			}
			$this->all_save('Diymen_class','/index');
		}else{
			$data=M('Diymen_class')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->find();
			if($data==false){
				$this->error('您所操作的数据对象不存在！');
			}else{
				$class=M('Diymen_class')->where(array('pid'=>0,'token'=>session('token')))->order('sort desc')->select();
				//dump($class);
				$this->assign('class',$class);
				$this->assign('show',$data);
			}
			$this->display();
		}

	}

	
	
	
	public function  class_send(){
		if(IS_GET){
			$api=M('wxuser')->where(array('token'=>session('token')))->find();
			
			$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$json=json_decode($this->curlGet($url_get));
	
			if($api['appid']==false||$api['appsecret']==false){$this->error('必须先填写【AppId】【 AppSecret】');exit;}
			$data = '{"button":[';
	
			$class=M('Diymen_class')->where(array('pid'=>0,'token'=>session('token')))->limit(3)->order('sort desc')->select();
			$kcount=M('Diymen_class')->where(array('pid'=>0,'token'=>session('token')))->limit(3)->order('sort desc')->count();
		/* 	$ks=intval($kcount);
			if($ks>3){
				$this->error('1级菜单最多只能开启3个');
			} */
			$k=1;
			foreach($class as $key=>$vo){
				//主菜单
	
				$data.='{"name":"'.$vo['title'].'",';
				$c=M('Diymen_class')->where(array('pid'=>$vo['id'],'token'=>session('token')))->limit(5)->order('sort desc')->select();
				$count=M('Diymen_class')->where(array('pid'=>$vo['id'],'token'=>session('token')))->limit(5)->order('sort desc')->count();
				//dump($vo['url']);
				//子菜单
				/* $kk=intval($count);
				if($kk>5){
					$this->error('2级菜单最多只能开启5个');
				} */
				if($c!=false){
					$data.='"sub_button":[';
				}else{
					if($vo['keyword'] !='' && $vo['url'] !='')
					{
						$data.='"type":"view","key":"'.$vo['title'].'","url":"'.$vo['url'].'"';
						
					}
					if(empty($vo['url']))
					{
						$data.='"type":"click","key":"'.$vo['title'].'"';
					}
					if(empty($vo['keyword']))
					{
						$data.='"type":"view","key":"'.$vo['title'].'","url":"'.$vo['url'].'"';
						
					}
				}
				
				$i=1;
				foreach($c as $voo){
		
					
					if($i==$count){
						if($voo['keyword'] !='' && $voo['url'] !='')
						{
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$voo['url'].'"}';								
						}
						if(empty($voo['keyword']))
						{
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$voo['url'].'"}';
						}
						if(empty($voo['url']))
						{
							$data.='{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"}';
						}
					}else{
						if($voo['keyword'] !='' && $voo['url'] !='')
						{
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$voo['url'].'"},';													}
						if(empty($voo['keyword']))
						{
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$voo['url'].'"},';
						}if(empty($voo['url']))
						{
							$data.='{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"},';
						}
					
					}
					$i++;
				}
				if($c!=false){
					$data.=']';
				}
	
				if($k==$kcount){
					$data.='}';
				}else{
					$data.='},';
				}
				$k++;
			}
			$data.=']}';
			file_get_contents('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$json->access_token);
	
			$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$json->access_token;
			if($this->api_notice_increment($url,$data)==false){
				$this->error('操作失败');
			}else{
				$this->success('操作成功');
			}
			exit;
		}else{
			$this->error('非法操作');
		}
	}
	
	
	
	
	
	function class_cancel(){
		if(IS_GET){
			$api=M('Diymen_set')->where(array('token'=>session('token')))->find();
			//dump($api);
			$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$json=json_decode($this->curlGet($url_get));
			$this->curlGet('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$json->access_token);
			$this->success('操作成功');
		}

	}	
	
	function api_notice_increment($url, $data){
		$ch = curl_init();
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
		}else{

			return true;
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


}
?>