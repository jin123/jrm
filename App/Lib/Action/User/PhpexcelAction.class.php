<?php
class PhpexcelAction extends CommonAction{
	public function reader(){

		import('@.ORG.Spreadsheet_Excel_Reader');
		//创建对象
		$data = new Spreadsheet_Excel_Reader();
		//设置文本输出编码
		$data->setOutputEncoding('UTF-8');
		//读取Excel文件
		$data->read(".".$_POST['file']);
		for ($i =2; $i <= $data->sheets[0]['numRows']; $i++) {
		  for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
			echo $data->sheets[0]['cells'][$i][$j].' ';
		  }
		  echo '<br>';
			 $obj =  $data->sheets[0]['cells'][$i];
			 
             /* $add['cid'] = $obj[1];
			 $add['Pid'] = $obj[2];
			 $add['Price'] = $obj[11]; */
			 $url=C("api_url")."/vipinfo.aspx?page=2&cid=".$obj[1]."&Pid=".$obj[2]."&Price=".$obj[3];
			 
			 $info=$this->curlGet($url);
			 if($info=="Success"){
			 	$this->success('导入成功');
			 }else{
			 	$this->error("导入失败");
			 }
			 
		}

	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
}