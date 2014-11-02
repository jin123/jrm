<?php
/**
 *微站栏目  by lic
**/
class LinkAction extends CommonAction{
	public function _initialize(){
        $this->assign('modules',$this->modules = array(
            //基础
            'base'=>'基础',
            'tu'=>'文章',
            'photo'=>'相册',

            //营销
            'selfform'=>'预定',
            'coupon'=>'优惠劵',
            'vote'=>'投票',
            'panorama'=>'360全景',
        	'shake'=>'摇一摇',
        	'wxscreen'=>'微信墙',
        	'lottery'=>'大转盘',
        	'guajiang'=>'刮刮卡',
        		

            //行业
            'product'=>'商城',
        	'member_card'=>'会员卡',
        	'estate'=>'房产',
        	'survey'=>'调研',
        	'education'=>'教育',
        	'medical'=>'医疗',
        	'groupbuy'=>'团购',
        	'wedding'=>'喜帖',
        	'car'=>'汽车',
        	

            //'edu'=>'教育',
            //'car'=>'汽车',
            //'tour'=>'旅游',
            ));
        $this->assign('mysite',$this->mysite = array('token'=>session('token'),'site_url'=>C('site_url'),'wechat_id'=>0));
        parent::_initialize();
    }

    public function index(){
		$db=D('Link');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('sorts desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}

	
	//获取所有模块产生的url   by lic
	public function insert(){
    	$type = $this->_get('type');
        if($type&&in_array($type,array_keys($this->modules))){
            A('Link')->$type();
            exit;
        }
        $this->display();
    	// MODULE_NAME maybe
    	//自动回复 areply   
    	//公司信息 company map,walk,bus,drive    
    	//优惠劵 Coupon  
    	//刮奖 Guajiang
    	//首页 Index
    	//留言 Liuyan
    	//大转盘 Lottery
    	//Map,maplist,content?id=
    	//360  Panorama,item?id=,
    	//Photo plist
    	//Product  products,product,productDetail?id=,companyMap,company,my,cart
    	//Userinfo
    	//Vote  show?id=
    	//Wedding
    	//Selfform  detail?id=
    	//Shake register
    	//Signscore
    	//Survey
    	//教育 Jiaoyu,jieshao,xiangce,plist,kecheng,dianping,kclist
    	//房产 Estate 
    	//汽车 Car,
        
    }

    public function base(){
        echo "<div class='new_main_liebiao mt5' style='padding:10px 0px'>";
        echo "<a data='Index/index'>站点首页</a>";
        echo "<a data='Liuyan/index'>留言</a>";
        //echo "<a data='Guajiang/index'>刮奖</a>";
        //echo "<a data='Lottery/index'>大转盘</a>";
        //echo "<a data='Signscore/index'>摇一摇</a>";
        //echo "<a data='Signscore/index'>刮刮卡</a>";
        //echo "<a data='Signscore/index'>签到连接</a>";
        echo "</div>";
        exit();
    }

    public function tu(){
        $data=M('Img');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['title|info'] = array('like',"%$key%"); 
        }

        $catid = $this->_get('catid');
        if($catid){
            $this->assign('catid',$catid);
            $where['classid'] = $catid; 
        }


        $cates = M('Classify')->where(array('token'=>session('token')))->select();
        if($cates){
            foreach ($cates as $key => $value) {
                $cats[$value['id']] = $value;
            }
            $this->assign('cats',$cats);
        }


        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:tu');
    }

    public function photo(){
       $data=M('Photo');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['title|info'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:photo');
    }


    public function coupon(){
       $data=M('Lottery');
        $where['type'] = 3;
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['title|keyword'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:coupon');
    }


    public function vote(){
       $data=M('Vote');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['title|keyword'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:vote');
    }


    public function panorama(){
       $data=M('Panorama');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['name|keyword'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:panorama');
    }


    public function wedding(){
       $data=M('Wedding');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['title|keyword'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:wedding');
    }

    public function selfform(){
       $data=M('selfform');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['name|keyword|intro'] = array('like',"%$key%"); 
        }
        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:selfform');
    }


    public function product(){
        $data=M('Product');
        $where['token'] = session('token');
        $key = $this->_get('searchkey');
        if($key){
            $this->assign('searchkey',$key);
            $where['name|intro|keyword'] = array('like',"%$key%"); 
        }

        $catid = $this->_get('catid');
        if($catid){
            $this->assign('catid',$catid);
            $where['catid'] = $catid; 
        }

        $cates = M('ProductCat')->where(array('token'=>session('token')))->select();
        if($cates){
            foreach ($cates as $key => $value) {
                $cats[$value['id']] = $value;
            }
            $this->assign('cats',$cats);
        }

        $count      = $data->where($where)->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('infos',$list);
        $this->display('Link:product'); 
    }
    //会员卡
    public function member_card(){
    	$data=M('MemberCardSet');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['cardname|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:member_card');
    }
    //房产
    public function estate(){
    	$data=M('Estate');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['title|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:estate');
    }
    //调研
    public function survey(){
    	$data=M('Survey');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:survey');
    }
    //教育
    public function education(){
    	$data=M('JiaoyuJigou');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:education');
    }
    //医疗
    public function medical(){
    	$data=M('YlHospital');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:medical');
    }
    //摇一摇
    public function shake(){
    	$data=M('Shake');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:shake');
    }
    //微信墙
    public function wxscreen(){
    	$data=M('WxscreenSet');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['tit|gjz'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:wxscreen');
    }
    
    //团购
    public function groupbuy(){
    	$data=M('Groupbuy');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|gjz'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:groupbuy');
    }
     
    //汽车
    public function car(){
    	$data=M('Car_car');
    	$where['token'] = session('token');
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['name|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:car');
    }
    
    //大转盘
    public function lottery(){
    	$data=M('Lottery');
    	$where['token'] = session('token');
    	$where['type'] = 1;
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['title|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:lottery');
    }
    
    //刮刮卡
    public function guajiang(){
    	$data=M('Lottery');
    	$where['token'] = session('token');
    	$where['type'] = 2;
    	$key = $this->_get('searchkey');
    	if($key){
    		$this->assign('searchkey',$key);
    		$where['title|keyword'] = array('like',"%$key%");
    	}
    	$count      = $data->where($where)->count();
    	$Page       = new Page($count,5);
    	$show       = $Page->show();
    	$list = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('page',$show);
    	$this->assign('infos',$list);
    	$this->display('Link:guajiang');
    }
    
    
    public function edu(){
        echo "edu modules";
    }

    public function car1(){
        echo "car modules";
    }

    public function tour(){
        echo "tour modules";
    }


	
}
?>