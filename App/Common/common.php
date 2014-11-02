<?php
/**
 * 中文截取
 * @param unknown $str
 * @param number $start
 * @param unknown $length
 * @param string $charset
 * @param string $suffix
 * @return string|unknown
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr"))
		return mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		return iconv_substr($str,$start,$length,$charset);
	}
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));
	if($suffix) return $slice."…";
	return $slice;
}

/*
 * 生成图形XML数据源文件
 *  @param array $data 数据
 * @param string $xml_name 文件名
 * @return string 路径
 */
function to_chartxml($data,$xml_name){
	if(!is_array($data)) return false;
    $arr=array('chart'=>$data);
    $xml=to_chartxml_sub($arr);
    $xml_path=DATA_PATH.$xml_name.'.xml';
    file_put_contents($xml_path,$xml);
    return $xml_path;
}

function to_chartxml_sub($data){
    if(!is_array($data)) return false;
    $xml_str='';
    foreach ($data as $key=>$value) {
    	if($value['_single']){    
    		foreach ($value as $single_k=>$single_v){     		      		  
		      if(substr($single_k,0,1)!="_"){	
		          $xml_str.="<".$key;
		          foreach ($single_v as $pro_k=>$pro_v){
		                  $pro_v=iconv("UTF-8","GB2312",$pro_v);
		                  $xml_str.=" {$pro_k}='$pro_v'";
		          } 
		          $xml_str.=" />\n";
		      }    		 
    		}    		
    	}else{
    	    $xml_str.="<".$key;
    	    if($value['_property']){
    	        foreach ($value['_property'] as $pro_k=>$pro_v){
    	            $pro_v=iconv("UTF-8","GB2312",$pro_v);
    	            $xml_str.=" {$pro_k}='{$pro_v}'";
    	        }
    	    }
    	    $xml_str.=">\n";
    		foreach ($value as $items_k=>$items_v){ 
    		    if(substr($items_k,0,1)!="_"){    		        
    		        $xml_str.=to_chartxml_sub(array("{$items_k}"=>$items_v));    		        
    		    }
    		}
    		$xml_str.="</{$key}>\n";
    	}
    }
    return $xml_str;
}