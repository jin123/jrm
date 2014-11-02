<?php
class GetImageAction extends Action
{
	public function index()
	{
		echo '<script language="JavaScript">';
		echo 'function myrefresh(){';
		echo 'window.location.reload();';
		echo '}';
		echo 'setTimeout("myrefresh()",60000); //指定60秒刷新一次';
		echo '</script>';
		echo '开始时间：'.date('Y-m-d H:i:s');
		
		$where['ImagesURL']='';
		$list = M('drugs')->field('XYWYID')->where($where)->order('XYWYID asc')->limit('50')->select();
		$currentID = '';
		for($ii=0;$ii<=count($list);$ii++)
		{
			$url = 'http://yao.xywy.com/goods/'.$list[$ii]['XYWYID'].'.htm'; 
			$info = file_get_contents($url); 
			preg_match('|<img class="fl" src="(.*?)" jqimg="|i',$info,$a);
			$data['ImagesURL'] = $this->getImage($a[1],$list[$ii]['XYWYID'],'/Public/uploads/');
			$where['XYWYID'] = $list[$ii]['XYWYID'];
			M('drugs')->where($where)->save($data);
			$currentID = $list[$ii]['XYWYID'];
		}
		echo '<br />'.$currentID.'结束时间：'.date('Y-m-d H:i:s');
	}
	/*
	*php实现下载远程图片到本地
	*@param $url       string      远程文件地址
	*@param $filename  string      保存后的文件名（为空时则为随机生成的文件名，否则为原文件名）
	*@param $dirName   string      文件保存的路径（路径其余部分根据时间系统自动生成）
	*@return           string        返回文件的保存路径
	*/
	public function getImage($url, $filename, $dirName)
	{
		if($url == ''){return false;}
		//获取文件原文件名
		$defaultFileName = basename($url);
		//获取文件类型
		$suffix = substr(strrchr($url,'.'), 1);
		
		//设置保存后的文件名
		//$filename = $filename == '' ? time().rand(0,9).'.'.$suffix : $defaultFileName;
		$filename = $filename == '' ? $defaultFileName : $filename.'.'.$suffix;
		
		$dirName = $dirName.date('Y', time()).date('m', time()).date('d',time()).'/';
		//设置文件保存路径
		$dir = $_SERVER['DOCUMENT_ROOT'].$dirName;
		
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);
		}
		$data = file_get_contents($url);//获取远程文件资源
		//保存文件
		$res = fopen($dir.$filename,'a');
		fwrite($res,$data);
		fclose($res);
		return $dirName.$filename;
	}
}
?>