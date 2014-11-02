<?php
class ReferAction extends Action
{

	public $selfform_value_model;

	public function __construct(){
		$this->selfform_value_model=M('Selfform_value');
	}


	public function subapi(){
		if (IS_POST){
			$wx_token=C('wx_token');
			$apitoken=$this->_post('apitoken');
			if($wx_token != $apitoken) exit;
			$fields=array();
			foreach ($_POST as $key=>$val){
				$fields[$key]=$val;
			}
			$row['values']=serialize($fields);
			$row['formid']=3;
			$row['wecha_id']="site_web";
			$row['time']=time();
			$id=$this->selfform_value_model->add($row);
			if($id){
				return true;
			}else{
				return false;
			}
		}
	}
}
?>