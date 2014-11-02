<?php
class SurveytkModel extends Model{
    protected $_validate = array(
            array('sid','require','活动号不能为空',Model::MUST_VALIDATE),
            array('question','require','微调研描述必须填写',Model::MUST_VALIDATE),
            array('type','require','题型必须选择',Model::MUST_VALIDATE),
            array('option1','optioncount','选项必须大于两项目',Model::MUST_VALIDATE,'callback'),
    );
    protected $_auto = array (
            array('token','getToken',Model:: MODEL_BOTH,'callback'),
            //array('uid','getUserId',Model:: MODEL_BOTH,'callback'),
    );
    function getToken(){
        return $_SESSION['token'];
    }
    function getUserId(){
    	return $_SESSION['UserId'];
    }
    function comparetime($data){     
        return strtotime($data['jssj'])>strtotime($data['kssj']);    	
    }
    function optioncount(){
        $count=0;
       for($i=1;$i<=10;$i++){
         if(!empty($_POST['option'.$i]))$count++;
       }
       return $count>1;
    }
}
?>