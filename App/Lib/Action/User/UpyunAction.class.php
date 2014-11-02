<?php
class UpyunAction extends CommonAction{
	public function upload(){
		if (!function_exists('imagecreate')){
			exit('php不支持gd库，请配置后再使用');
		}
		if(IS_POST){
			$type = isset($_GET['upload_type'])?$_GET['upload_type']:'pic';
			$return=$this->localUpload('',$type);
			
			echo '<script>location.href="/index.php?g=User&m=Upyun&a=upload&error='.$return['error'].'&msg='.$return['msg'].'";</script>';
		}else {
			$this->display('local');
		}
	}
public function upload_wexin(){

		if (!function_exists('imagecreate')){

			exit('php不支持gd库，请配置后再使用');

		}
		if(IS_POST){
			$type = isset($_GET['upload_type'])?$_GET['upload_type']:'pic';

			$return=$this->localUpload('',$type);
			
             
			echo '<script>location.href="/index.php?g=User&m=Upyun&a=upload_wexin&error='.$return['error'].'&msg='.$return['msg'].'";</script>';

		}else {

			$this->display('upload_wexin');

		}

	}
	function localUpload($filetypes='',$type="pic"){
	    import('@.ORG.UploadFile');
		$upload = new UploadFile();
		$upload->maxSize  = intval(C('up_size'))*1024 ;
			$up_exts = C('up_exts');
		if (!$filetypes){
			$upload->allowExts  = explode(',',$up_exts[$type]);
		}else {
			$upload->allowExts  = $filetypes;
		}
		//
		$ymd = date("Ymd");
		$token = session('token');
		$upload->savePath =  './Public/uploads/'.$token.'/';// 设置附件上传目录
		if(!is_dir($upload->savePath)){
			mkdir($upload->savePath);	
		}
		$upload->savePath .= $ymd.'/';
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