<?php
    class PanoramaModel extends Model{
    protected $_validate = array(
            array('name','require','名称不能为空',1),
            array('keyword','require','价格不能为空',1)
     );
}

?>