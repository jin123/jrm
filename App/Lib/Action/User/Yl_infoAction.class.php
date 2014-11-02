<?php
/**
 *文本回复
**/
class Yl_infoAction extends CommonAction{

	public $reply_info_model;
	public $infoTypes;
	public function _initialize() {
		parent::_initialize();
		$this->reply_info_model=M('reply_info');
		//
		$this->infoTypes=array(
		'Medical'=>array('type'=>'Medical','name'=>'微医疗','keyword'=>'微医疗','url'=>'/index.php?g=Wap&m=Medical&a=medical'),
		'Groupon'=>array('type'=>'Groupon','name'=>'团购','keyword'=>'团购','url'=>'/index.php?g=Wap&m=Groupon&a=grouponIndex'),
		'Dining'=>array('type'=>'Dining','name'=>'订餐','keyword'=>'订餐','url'=>'/index.php?g=Wap&m=Product&a=dining&dining=1'),
		'Shop'=>array('type'=>'Shop','name'=>'商城','keyword'=>'商城','url'=>'/index.php?g=Wap&m=Product&a=cats'),
		'panorama'=>array('type'=>'panorama','name'=>'全景','keyword'=>'全景','url'=>'/index.php?g=Wap&m=Product&a=cats'),
		);
		//是否是餐饮
		if (isset($_GET['infotype'])&&$_GET['infotype']=='Dining'){
			$this->isDining=1;
		}else {
			$this->isDining=0;
		}
		$this->assign('isDining',$this->isDining);
	}
	public function set(){
  		if(IS_POST){
  			$infotype = $this->_post('infotype');
  			$thisInfo = $this->reply_info_model->where(array('infotype'=>$infotype,'token'=>session('token')))->find();

			if ($row['infotype']=='Dining'){//订餐
				$diningyuding=intval($_POST['diningyuding']);
				$diningwaimai=intval($_POST['diningwaimai']);
				if (isset($_POST['diningyuding'])){
					$_POST['diningyuding']=intval($_POST['diningyuding']);
				}else {
					$_POST['diningyuding']=0;
				}
				if (isset($_POST['diningwaimai'])){
					$_POST['diningwaimai']=intval($_POST['diningwaimai']);
				}else {
					$_POST['diningwaimai']=0;
				}
				$_POST['config']=serialize(array('waimaiclose'=>$diningwaimai,'yudingclose'=>$diningyuding,'yudingdays'=>intval($_POST['yudingdays'])));
			}else{
				$_POST['diningyuding']=0;
				$_POST['diningwaimai']=0;
			}
			if ($thisInfo){
				
				$where=array('infotype'=>$thisInfo['infotype'],'token'=>session('token'));
				$this->reply_info_model->where($where)->save($_POST);
				$keyword_model=M('Keyword');
				$keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST['keyword']));
				$this->success('修改成功',U('Yl_info/set',$where));
						
			}else {
				$this->all_insert('Reply_info','/set?infotype='.$infotype);
			}
		}else{
			$infotype = $this->_get('infotype');
			$thisInfo = $this->reply_info_model->where(array('infotype'=>$infotype,'token'=>session('token')))->find();
				
			//
			$config=unserialize($thisInfo['config']);
			$this->assign('config',$config);
			//
			$this->assign('infoType',$this->infoTypes[$infotype]);
			$this->assign('set',$thisInfo);
			$this->display('Medical/set');
		}
	}

}
?>