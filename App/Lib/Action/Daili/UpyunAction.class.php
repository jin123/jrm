<?php
class UpyunAction extends SystemAction{
	public function upload(){
		if (!function_exists('imagecreate')){
			exit('php不支持gd库，请配置后再使用');
		}
		if(IS_POST){
			$return=$this->localUpload();
			echo '<script>location.href="/index.php?g=Daili&m=Upyun&a=upload&error='.$return['error'].'&msg='.$return['msg'].'";</script>';
		}else {
			$this->display('local');
		}
	}

	function localUpload($filetypes=''){
	    import('@.ORG.UploadFile');
		$upload = new UploadFile();
		$upload->maxSize  = intval(C('up_size'))*1024 ;
		if (!$filetypes){
			$upload->allowExts  = explode(',',C('up_exts'));
		}else {
			$upload->allowExts  = $filetypes;
		}
		//
		$token = session('logindailiName');
		$upload->savePath =  './Public/uploads/'.$token.'/';// 设置附件上传目录
		if(!is_dir($upload->savePath)){
			mkdir($upload->savePath);	
		}
		//
		if(!$upload->upload()) {// 上传错误提示错误信息
			$error=1;
			$msg=$upload->getErrorMsg();
		}else{// 上传成功 获取上传文件信息
			$error=0;
			$info =  $upload->getUploadFileInfo();

			if ($thumb==1){
				$paths=explode('/',$info[0]['savename']);
				$fileName=$paths[count($paths)-1];
				$msg = substr($upload->savePath,1).str_replace($fileName,'thumb_'.$fileName,$info[0]['savename']);
			}else {
				$msg = substr($upload->savePath,1).$info[0]['savename'];
			}
		}
		return array('error'=>$error,'msg'=>$msg);
	}
}
?>