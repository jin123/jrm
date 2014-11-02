<?php
class GetContentAction extends Action
{
	public function index()
	{
		$list = M('drugs')->field('XYWYID')->order('XYWYID desc')->limit('1')->find();
		$ii = $list['XYWYID']+1;
		if($ii=='')
		{
			$ii = 1;
		}
		$dd = $ii+50;
		echo '<script language="JavaScript">';
		echo 'function myrefresh(){';
		echo 'window.location.reload();';
		echo '}';
		echo 'setTimeout("myrefresh()",40000); //指定40秒刷新一次';
		echo '</script>';
		echo $ii.'开始时间：'.date('Y-m-d H:i:s');
		for($ii;$ii<=$dd;$ii++)
		{
			$url = 'http://yao.xywy.com/goods/'.$ii.'/manual.htm'; 
			$info = file_get_contents($url); 
			//如果出现中文乱码使用下面代码 
			//$getcontent = iconv("gb2312", "utf-8",$contents); 
			//preg_match('|<title>(.*?)<\/title>|i',$info,$m);
			preg_match('|<span class="mr20">(.*?)<\/span>|i',$info,$a);
			//echo '批准文号：'.$a[1];
			$data['LicenseID'] = $a[1];
			if($data['LicenseID']=='')
			{
				continue;
			}
			
			preg_match('|通用名称：<\/a><\/li><li>(.*?)<\/li><\/ul>|i',$info,$b);
			//echo '<br />通用名：'.$b[1];
			$data['Byname'] = $b[1];
			
			preg_match('|生产企业：<\/span>(.*?)<\/li><\/ul>|i',$info,$c);
			//echo '<br />生产厂家：'.$c[1];
			$data['Factory'] = $c[1];
			
			preg_match('|<h1 class="fl mr15 fYaHei pr">(.*?)<\/h1>|i',$info,$d);
			//echo '<br />商品名：'.$d[1];
			$data['ItemName'] = $d[1];
			
			preg_match('|功能主治：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$e);
			//echo '<br />功能主治：'.$e[1];
			$data['Function'] = $e[1];
			
			preg_match('|用法用量：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$f);
			//echo '<br />用法用量：'.$f[1];
			$data['Usage'] = $f[1];
			
			preg_match('|主要成分：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$g);
			//echo '<br />主要成分：'.$g[1];
			$data['Elements'] = $g[1];
			
			preg_match('|包装规格：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$h);
			//echo '<br />包装规格：'.$h[1];
			$data['Spec'] = $h[1];
			
			preg_match('|不良反应：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$i);
			//echo '<br />不良反应：'.$i[1];
			$data['Untoward'] = $i[1];
			
			preg_match('|注意事项：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$j);
			//echo '<br />注意事项：'.$j[1];
			$data['Attention'] = $j[1];
			
			preg_match('|禁忌：<\/a><\/li><li>(.*?)<\/li><\/ul>|is',$info,$k);
			//echo '<br />禁忌：'.$k[1];
			$data['Avoid'] = $k[1];
			
			$data['Dosage'] = '';
			
			preg_match('|<div class="fl mr5" id="typeImg"><\/div>(.*?)<\/div>|i',$info,$l);
			//echo '<br />处方药：'.$l[1];
			$IsOTC = 0;
			if($l[1]=='处方药')
			{
				$IsOTC = 1;
			}
			$data['IsOTC'] = $IsOTC;
			
			preg_match('|<div class="fl mr5" id="chinaeImg"><\/div>(.*?)<\/div>|i',$info,$m);
			//echo '<br />国产：'.$m[1];
			$IsCN = 0;
			if($m[1]=='国产')
			{
				$IsCN = 1;
			}
			$data['IsCN'] = $IsCN;
			
			preg_match('|<div class="fl mr5" id="attributeImg"><\/div>(.*?)<\/div>|i',$info,$n);
			//echo '<br />中药：'.$n[1];
			$IsTraditional = 1;
			if($n[1]=='西药')
			{
				$IsTraditional = 0;
			}
			$data['IsTraditional'] = $IsTraditional;
			
			preg_match('|<div class="fl mr5" id="medicareImg"><\/div>(.*?)<\/div>|i',$info,$o);
			//echo '<br />医保：'.$o[1];
			$IsInsurance = 1;
			if($o[1]=='非医保')
			{
				$IsInsurance = 0;
			}
			
			$data['IsInsurance'] = $IsInsurance;
			
			$data['ImagesURL'] = '';
			$data['PID'] = $this->create_guid();
			$data['CreateTime'] = date('Y-m-d H:i:s');
			$data['XYWYID'] = $ii;
			
			if($data['LicenseID']!='')
			{
				M('drugs')->add($data);
			}
		}
		echo '结束时间：'.date('Y-m-d H:i:s');
    }
	
	public function get_category()
	{
		echo '<script language="JavaScript">';
		echo 'function myrefresh(){';
		echo 'window.location.reload();';
		echo '}';
		echo 'setTimeout("myrefresh()",60000); //指定60秒刷新一次';
		echo '</script>';
		echo $ii.'开始时间：'.date('Y-m-d H:i:s');
		
		$wh['SortValue'] = 0;
		$wh['RootID'] = array('GT',0);
		$li = M('drugs_category')->where($wh)->order('CID asc')->limit('1')->find();
		$pageC = $li['Page']+1;
		$ww['CID'] = $li['CID'];
		
		$url = 'http://yao.xywy.com/jibing/'.$li['CID'].'-1.htm';
		$info = file_get_contents($url); 
		preg_match('|共<span id="totalpage">(.*?)</span>条记录|i',$info,$o);
		$pageT = Ceil($o[1]/10);
		if($pageT-$pageC>50)
		{
			$pageT = $pageC + 50;
		}
		
		for($k=$pageC;$k<=$pageT;$k++)
		{
			$url2 = 'http://yao.xywy.com/jibing/'.$li['CID'].'-'.$k.'.htm';
			$info2 = file_get_contents($url2); 
			$cc = preg_match_all('|<a target="_blank" href="\/goods\/(.*?).htm"|i',$info2,$n);
			$ss = count($n[1]);
			//echo $cc;
			//exit;
			for($i=0;$i<$ss;$i++)
			{
				$data['XYWYID'] = $n[1][$i];
				$data['CID'] = $li['CID'];
				M('drugs_to_category')->add($data);
			}
			$dt['Page'] = $k;
			M('drugs_category')->where($ww)->save($dt);
		}
		if($pageT-$pageC<50)
		{
			$dt['SortValue'] = 1;
			M('drugs_category')->where($ww)->save($dt);
		}
		echo '结束时间：'.date('Y-m-d H:i:s');
	}
	 
	/**创建全球唯一标识id*/
    public function create_guid()
	{
		$zimu = range('A','Z');
		shuffle($zimu);
		$uuid = 'RH'.$zimu[0].rand(100000000,999999999);
		return $uuid;
	}
}
?>