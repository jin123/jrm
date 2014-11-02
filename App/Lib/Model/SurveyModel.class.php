<?php
class SurveyModel extends Model{
    protected $_validate = array(
            array('name','require','微调研名称必须填写',Model::MUST_VALIDATE),
            array('keyword','require','微调研关键字必须填写',Model::MUST_VALIDATE),
            array('kssj','require','开始时间必须填写',Model::MUST_VALIDATE),
            array('jssj','require','结束时间必须填写',Model::MUST_VALIDATE),
            array('kssj,jssj','comparetime','结束时间必须大于开始时间',Model::MUST_VALIDATE,'callback'),

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
}
?>