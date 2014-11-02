<?php
/**
 *文本回复
**/
class Reply_infoAction extends CommonAction{

	public $reply_info_model;
	public $infoTypes;
	public function _initialize() {
		parent::_initialize();
		$this->reply_info_model=M('reply_info');
		//
		$this->infoTypes=array(
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
        $infotype = $this->_get('infotype');
		$thisInfo = $this->reply_info_model->where(array('infotype'=>$infotype,'token'=>session('token')))->find();
		if(IS_POST){
			$row['title']=$this->_post('title');
			$row['info']=$this->_post('info');
			$row['picurl']=$this->_post('picurl');
			$row['infotype']=$this->_post('infotype');
			if ($row['infotype']=='Dining'){//订餐
				$diningyuding=intval($_POST['diningyuding']);
				$diningwaimai=intval($_POST['diningwaimai']);
				if (isset($_POST['diningyuding'])){
					$row['diningyuding']=intval($_POST['diningyuding']);
				}else {
					$row['diningyuding']=0;
				}
				if (isset($_POST['diningwaimai'])){
					$row['diningwaimai']=intval($_POST['diningwaimai']);
				}else {
					$row['diningwaimai']=0;
				}
				$row['config']=serialize(array('waimaiclose'=>$diningwaimai,'yudingclose'=>$diningyuding,'yudingdays'=>intval($_POST['yudingdays'])));
			}
			if ($thisInfo){
				
				$where=array('infotype'=>$thisInfo['infotype'],'token'=>session('token'));
				$this->reply_info_model->where($where)->save($row);
				$keyword_model=M('Keyword');
				$keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST['keyword']));
				$this->success('修改成功',U('Reply_info/set',$where));
						
			}else {
				$this->all_insert('Reply_info','/set?infotype='.$infotype);
			}
		}else{
			//
			$config=unserialize($thisInfo['config']);
			$this->assign('config',$config);
			//
			$this->assign('infoType',$this->infoTypes[$infotype]);
			$this->assign('set',$thisInfo);
			$this->display();
		}
	}

}
?>