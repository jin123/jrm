<?php
class PhotoAction extends Action{
	public function index(){
		/*$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}*/
		$token = $this->_get('token');
		$photo=M('Photo')->where(array('status'=>1,'token'=>$token))->order('id desc')->select();
		if($photo==false){ }
		$this->assign('photo',$photo);
		$this->display();
	}
	public function plist(){
		/*$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}*/
		$token = $this->_get('token');
		$info=M('Photo')->field('title,type')->where(array('id'=>$this->_get('id'),'token'=>$token))->find();
		$count = M('Photo_list')->where(array('pid'=>$this->_get('id'),'status'=>1,'token'=>$token))->count();
		$this->assign('info',$info);
		if($info['type']==1){
			$photo_list=M('Photo_list')->where(array('pid'=>$this->_get('id'),'status'=>1,'token'=>$token))->select();
			$this->assign('photo',$photo_list);
			$this->display();
		}else if($info['type']==2){	
			$c = ceil($count/2);
			$photo_list=M('Photo_list')->where(array('pid'=>$this->_get('id'),'status'=>1,'token'=>$token))->limit(0,$c)->select();
			$photo_list2=M('Photo_list')->where(array('pid'=>$this->_get('id'),'status'=>1,'token'=>$token))->limit($c,$c)->select();
			$this->assign('photo1',$photo_list);
			$this->assign('photo2',$photo_list2);
			$this->display('plist2');
		}else{
			$photo_list=M('Photo_list')->where(array('pid'=>$this->_get('id'),'status'=>1,'token'=>$token))->select();
			$this->assign('photo',$photo_list);
			$this->display('plist3');
		}
		
				
	
	}
}
?>