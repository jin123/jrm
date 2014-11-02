<?php
class CompanyAction extends CommonAction{
	public $isBranch;
	public $company_model;
	public function _initialize() {
		parent::_initialize();
		//是否是分店
		$this->isBranch=0;
		if (isset($_GET['isBranch'])&&intval($_GET['isBranch'])){
			$this->isBranch=1;
		}
		$this->assign('isBranch',$this->isBranch);
		$this->company_model=M('Company');
	}
	//门店列表
	public function index(){
		$where['token'] = session('token');
		$thisCompany=$this->company_model->where($where)->order('id desc')->select();
		if(IS_POST){
			if (!$thisCompany){
				if ($this->isBranch){
					$this->insert('Company','/branches?isBranch/'.$this->isBranch);
				}else {
					$this->insert('Company','/index?isBranch='.$this->isBranch);
				}
			}else {
				if($this->company_model->create()){
					$this->company_model->token = session('token');
					if($this->company_model->where($where)->save($_POST)){
						if ($this->isBranch){
							$this->success('修改成功',U('Company/branches',array('isBranch'=>$this->isBranch)));
						}else{
							$this->success('修改成功',U('Company/index',array('isBranch'=>$this->isBranch)));
						}
					}else{
						$this->error('操作失败');
					}
				}else{
					$this->error($this->company_model->getError());
				}
			}
			
		}else{
			$this->assign('set',$thisCompany);
			$this->display();
		}
	}
	function set(){
	
	         $row['title']=$this->_post('title');

			$row['info']=$this->_post('info');

			$row['picurl']=$this->_post('picurl');

			$row['infotype']=$this->_post('infotype');
	      
	       $thisInfo = M('reply_info')->where(array('infotype'=>$infotype,'token'=>session('token')))->find();
		   if ($thisInfo){
				$where=array('infotype'=>$thisInfo['infotype'],'token'=>session('token'));
				M('reply_info')->where($where)->save($row);
                echo M('reply_info')->getlastsql();
				$keyword_model=M('Keyword');
				$keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST['keyword']));
				$this->success('修改成功',U('Company/set_company_ketwork',$where));
			}else {
				$this->all_insert('Company','/set_company_ketwork?infotype='.$infotype);
			}
	
	}
	//门店关键词回复
	function mapkey(){
	     $infotype = "map";      
		$thisInfo = M('reply_info')->where(array('infotype'=>$infotype,'token'=>session('token')))->find();
		if(IS_POST){      
			$row['title']=$this->_post('title');
			$row['info']=$this->_post('info');
			$row['picurl']=$this->_post('picurl');
			$row['apiurl']=$this->_post('apiurl');
            $row['keyword'] =$this->_post('key');
			$row['infotype']="map";
             $row['token'] = session('token');
			if ($thisInfo){
				$where=array('infotype'=>$thisInfo['infotype'],'token'=>session('token'),'id'=>$thisInfo['id']);
				M('reply_info')->where($where)->save($row);
				$keyword_model=M('Keyword');
				$keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST));
				$this->success('修改成功',U('Company/mapkey',$where));return;
			}else {
			    $res =   M('reply_info')->add($row);
			   if($res){
			   $this->success('添加成功成功',U('Company/mapkey'));
			   
			   }   
			}
			return;

		}else{
			$config=unserialize($thisInfo['config']);
			$this->assign('config',$config);
			$this->assign('infoType',$this->infoTypes[$infotype]);
			$this->assign('set',$thisInfo);
		}
	    $this->display('set');
	
	}
	//更新门店
	function update(){	
		$Company= M('Company');
		$where['id'] = $this->_get('id','trim');		
		$where['token'] = session('token');
	    $result = $Company->where($where)->find();		
	    if(IS_POST){
	        if (empty($_POST['id']) || !isset($_POST['id'])){
	        
	        
	          if(trim($_POST['name'])==""){
	      
	            $this->error('名称不能为空',U('Company/update'));
	          }	
	           if(trim($_POST['shortname'])==""){
	      
	            $this->error('简称不能为空',U('Company/update'));
	          }	
	            if(trim($_POST['tel'])==""){
	      
	            $this->error('电话不能为空',U('Company/update'));
	          }	
	           if(trim($_POST['mp'])==""){
	      
	            $this->error('手机号码不能为空',U('Company/update'));
	          }
               if(trim($_POST['address'])==""){
	      
	            $this->error('地址不能为空',U('Company/update'));
	          }
	          if(trim($_POST['logourl'])==""){
	      
	            $this->error('请上传logo图片',U('Company/update'));
	          }
	          if(trim($_POST['longitude'])=="" || trim($_POST['latitude'])==""){
	      
	            $this->error('经纬度不能为空',U('Company/add_com'));
	          }
	           $this->all_insert('Company','/index');
	        }else {
			    $where['id'] = $_POST['id'];
				 unset($_POST['id']);
				$res = $Company->where($where)->save($_POST);
	            $this->success('修改成功');   
	        }
	    }else{
	        $this->assign('set',$result);
	        $this->display();
	    }		

	}
	function del(){
	   $del = M('Company')->where("id=".$_GET['id'])->delete();
	
	   if($del){
	   
	   //    $this->success('删除成功');
	    $this->success('删除成功',U('Company/index'));
	   
	   }
	   else{
	   
	     // $this->error('删除失败');
	      $this->success('删除失败',U('Company/index'));
	   
	   }
	
	}
	//添加门店
	function add_com(){
	
	    $this->display('update');
	
	}
	public function branches(){
		$branches=$this->company_model->where(array('isbranch'=>1,'token'=>session('token')))->order('taxis ASC')->select();
		$this->assign('branches',$branches);
		$this->display();
	}
	public function delete(){
		$where=array('isbranch'=>1,'id'=>intval($_GET['id']),'token'=>session('token'));
		$rt=$this->company_model->where($where)->delete();
		if($rt==true){
			$this->success('删除成功',U('Company/branches',array('isBranch'=>1)));
		}else{
			$this->error('服务器繁忙,请稍后再试',U('Company/branches',array('isBranch'=>1)));
		}
	}
}


?>