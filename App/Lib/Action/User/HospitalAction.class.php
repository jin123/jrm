<?php
class HospitalAction extends CommonAction{
	public function hospitallist(){
		$yl_hospital=M('Yl_hospital');
		$id=$this->_get('id');
		$this->assign('hid',$id);
		$token=session('token');
		$count = $yl_hospital->where(array('token'=>$token))->count();
		$Page  = new Page($count,12);
		$show  = $Page->show();
		$list  = $yl_hospital->where(array('token'=>$token))->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		$this -> assign('list',$list);
		$this -> assign('page',$show);
		$this->display('Medical/hospitallist');
	}
	public function hospital(){
	    if (IS_POST){	
			$this->all_insert('Yl_hospital','/hospitallist');
		}else{		
			$this -> display('Medical/sethospital');
		}
	}
	public function edit(){
		if (IS_POST){
		    $this->all_save('Yl_hospital','/hospitallist');
		}else{		    
		    $yl_hospital=M('Yl_hospital');
		    $id=$this->_get('id');
		    $data=$yl_hospital->where(array('id'=>$id,'token'=>session('token')))->find();
		    $this->assign('vo',$data);
		    $this -> display('Medical/sethospital');
		}
	}
	public function delhospital(){
		$yl_hospital=M('Yl_hospital');
		$check = $yl_hospital->field('id')->where(array('id'=>$this->_get('id'),'token'=>session('token')))->find();
		if ($check == false){$this->error('服务器繁忙');}
		if (empty($_POST['set'])){		    
			if ($yl_hospital->where(array('id'=>$check['id'],'token'=>session('token')))->delete()){
			    M('Keyword')->where(array('pid'=>$check['id'],'module'=>'Yl_hospital','token'=>session('token')))->delete();
				$this->success('操作成功');
			}else{
				$this->error('服务器繁忙,请稍后再试');
			}
		}
	}
	public function hospitalset(){
	    $reply_info_model=M('reply_info');
	    $infoTypes=array('type'=>'Hospital','name'=>'微医疗','keyword'=>'微医疗','url'=>'/index.php?g=Wap&m=Medical&a=medical');
	    $infotype = 'Hospital';
	    $thisInfo = $reply_info_model->where(array('infotype'=>$infotype,'token'=>session('token')))->find();
	    if(IS_POST){
	        $_POST['config']=serialize(array('homeimage'=>$this->_post('homeimage')));
	        if ($thisInfo){	    
	            $where=array('infotype'=>$thisInfo['infotype'],'token'=>session('token'));
	            $reply_info_model->where($where)->save($_POST);
	            $keyword_model=M('Keyword');
	            $keyword_model->where(array('token'=>session('token'),'pid'=>$thisInfo['id'],'module'=>'Reply_info'))->save(array('keyword'=>$_POST['keyword']));
	            $this->success('修改成功');   
	        }else {
	            $this->all_insert('Reply_info','/hospitalset');
	        }
	    }else{
	        $config=unserialize($thisInfo['config']);
	        $this->assign('config',$config);
	        $this->assign('infoType',$infoTypes);
	        $this->assign('set',$thisInfo);
	        $this->display('Medical/hospitalset');
	    }		
	}
}