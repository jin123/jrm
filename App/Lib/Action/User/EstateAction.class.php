<?php
/**
* 微房产
**/
class EstateAction extends CommonAction
{
	public function index()
	{
		$db = M('Estate');
		$where['token'] = session('token');
		$count = $db->where($where)->count();
		$page=new Page($count,25);
		$estate=$db->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('estate',$estate);
		$this->display();
	}

   public function estateSet()
    {
        $db = M('Estate');
		$where['token'] = session('token');
		$where['id'] = $this->_get('id');
        $es_data = $db->where($where)->find();
        
		$panorama = M('Panorama')->where(array('token'=>session('token')))->field('id as pid,name,keyword')->select();
        $this->assign('panorama', $panorama);
        
		$classify = M('Classify')->where(array('token'=>session('token')))->field('id as cid,name')->select();
        $this->assign('classify', $classify);
		
       // $reslist = M('Reservation')->field('id as rid ,title')->select();
       // $this->assign('reslist', $reslist);
        if (IS_POST) {
            if ($es_data == null) {
				$this->all_insert('Estate');
            } else {
				$this->all_save('Estate');
            }
        } else {
            $this->assign('es_data', $es_data);
            $this->display();
        }
    }
	
    public function del()
    {
        $Estate = M('Estate');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $Estate->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $Estate->where($where)->delete();
            if ($isok) {
				$where = array('pid'=>$check['id']);
				M('estate_son')->where($where)->delete();
				M('estate_housetype')->where($where)->delete();
				M('estate_album')->where($where)->delete();
				M('estate_expert')->where($where)->delete();
				M('keyword')->where(array('pid'=>$check['id'],'token'=>session('token')))->delete();
                $this->success('删除成功', U('Estate/index'));
            } else {
                $this->error('删除失败', U('Estate/index'));
            }
        }
    }
	
    public function son()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token')))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);
				
		$estate_son = M('Estate_son');
        $where = array('token' => session('token'),'pid'=>$pid);
 		$count = $estate_son->where($where)->count();
		$page=new Page($count,25);
		$son_data=$estate_son->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->order('sort desc')->select();
		$this->assign('page',$page->show());
		$this->assign('son_data',$son_data);
		$this->display();
    }

    public function son_add()
    {
        $pid = (int) $this->_get('pid');
        $estate = M('estate')->field('id')->where(array('token'=>session('token'),'id'=>$pid))->find();
		if(!$estate){$this->error('非法参数！');}		
		$this->assign('pid',$estate['id']);

        $t_son = M('Estate_son');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $t_son->where($where)->find();

        if ($check != null) {
          $this->assign('son', $check);
        }
        if (IS_POST) {
	        if ($t_son->create()) {
				if ($check == null) {
					$t_son->add();
                    $this->success('添加成功', U('Estate/son',array('pid'=>$estate['id'])));
				} else {
					$wh = array('token' => session('token'), 'id' => $this->_post('id'));
					$t_son->where($wh)->save();
                    $this->success('修改成功', U('Estate/son',array('pid'=>$estate['id'])));
				}
			}
       	}
		else
		{
			 $this->display();
		}
    }


    public function son_del()
    {
        $t_son = M('Estate_son');
        $id = (int) $this->_get('id');
        $pid = (int) $this->_get('pid');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $t_son->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $t_son->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Estate/son',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Estate/son',array('pid'=>$pid)));
            }
        }
    }

    public function housetype()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);
				
		
		$t_housetype = M('Estate_housetype');
        $where = array('token' => session('token'),'pid'=>$estate['id']);
 		$count = $t_housetype->where($where)->count();
		$page=new Page($count,25);
		$housetype=$t_housetype->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->order('sort desc')->select();
		$this->assign('page',$page->show());
		
        foreach ($housetype as $k => $v) {
            $son_type[] = M('Estate_son')->where(array('id' => $v['son_id']))->field('id as sid,title')->find();
        }
        foreach ($son_type as $key => $value) {
            foreach ($value as $k => $v) {
                $housetype[$key][$k] = $v;
            }
        }
        $this->assign('housetype', $housetype);
        $this->display();
    }
    public function housetype_add()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);
				
        $t_housetype = M('Estate_housetype');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' =>session('token'));
        $check = $t_housetype->where($where)->find();
        
		$son_data = M('Estate_son')->where(array(
		'token' => session('token')
		))->field('id as sid,title')->select();
        
		$this->assign('son_data', $son_data);
        
		$panorama = M('Panorama')->where(array(
		'token' => session('token')
		))->field('id as pid,name,keyword')->select();
		
        $this->assign('panorama', $panorama);
		
        if ($check != null) {
            $this->assign('housetype', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                $_POST['token'] = session('token');
                if ($t_housetype->add($_POST)) {
                    $this->success('添加成功', U('Estate/housetype',array('pid' =>$estate['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array( 'id' => $this->_get('id'));
                if ($t_housetype->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Estate/housetype',array('pid' =>$estate['id'])));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }else {
	        $this->display();
		}
    }
    public function housetype_del()
    {
        $housetype = M('Estate_housetype');
        $id = (int) $this->_get('id');
		$pid = (int) $this->_get('pid');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $housetype->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $housetype->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Estate/housetype',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Estate/housetype',array('pid	'=>$pid)));
            }
        }
    }

    public function album()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);
		
        $Photo = M('Photo');
        $t_album = M('Estate_album');
        $album = $t_album->where(array('token' => session('token'),'pid'=>$estate['id']))->field('id,poid')->select();
        foreach ($album as $k => $v) {
            $list_photo[] = $Photo->where(array('token' => session('token'), 'id' => $v['poid']))->order('id desc')->find();
        }
        $this->assign('album', $list_photo);
        $this->display();
    }

    public function album_add()
    {
		
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);

        $po_data = M('Photo');
        $list = $po_data->where(array(
			'token' => session('token')
		))->field('id,title')->select();
		
        $this->assign('photo', $list);
        $t_album = M('Estate_album');
        $poid = (int) $this->_get('poid');
		
        $check = $t_album->where(array(
			'poid' => $poid,'pid'=>$estate['id']
		))->find();
		
        $this->assign('album', $check);
		
        if (IS_POST) {
            if ($check == NULL) {
                $check_ex = $t_album->where(array('token' => session('token'), 'poid' => $this->_post('poid')))->find();
                if ($check_ex) {
                    $this->error('您已经添加过改相册，请勿重复添加。');
                    die;
                }
                $_POST['token'] = session('token');
                if ($t_album->add($_POST)) {
                    $this->success('添加成功', U('Estate/album',array('pid'=>$estate['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('token' => session('token'), 'id' => $this->_post('id'));
                if ($t_album->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Estate/album',array('pid'=>$estate['id'])));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }
        $this->display();
    }
    public function album_del()
    {
        $album = M('Estate_album');
        $poid = $this->_get('poid');
        $pid = $this->_get('pid');
        $where = array('poid' => $poid,'pid'=>$pid);
        $check = $album->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $album->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Estate/album',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Estate/album',array('pid'=>$pid)));
            }
        }
    }
	
	
    public function impress()
    {
        $t_impress = M('Estate_impress');
        $impress = $t_impress->order('sort desc')->select();
        $this->assign('impress', $impress);
        $this->display();
    }
    public function impress_add()
    {
        $t_impress = M('Estate_impress');
        $id = $this->_get('id');
        $where = array('id' => $id);
        $check = $t_impress->where($where)->find();
        if ($check != null) {
            $this->assign('impress', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                if ($t_impress->add($_POST)) {
                    $this->success('添加成功', U('Estate/impress'));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('id' => $this->_post('id'));
                if ($t_impress->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Estate/impress'));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }
        $this->display();
    }

    public function impress_del()
    {
        $impress = M('Estate_impress');
        $id = $this->_get('id');
        $where = array('id' => $id);
        $check = $impress->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $impress->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Estate/impress'));
            } else {
                $this->error('删除失败', U('Estate/impress'));
            }
        }
    }
	
    public function expert()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);
		
        $t_expert = M('Estate_expert');
		$where = array('token'=>session('token'),'pid'=>$pid);
		$count = $t_expert->where($where)->count();
		$page=new Page($count,25);
		$expert=$t_expert->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('expert',$expert);
        $this->display();
    }
    public function expert_add()
    {
		$pid = $this->_get('pid');
        $estate = M('estate')->where(array('token'=>session('token'),'id' => $pid))->find();
		if(!$estate){$this->error('非法参数！');}
		$this->assign('pid',$estate['id']);		
		
        $t_expert = M('Estate_expert');
        $id = $this->_get('id');
        $where = array('id' => $id,'pid'=>$estate['id']);
        $check = $t_expert->where($where)->find();
        if ($check != null) {
            $this->assign('expert', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                if ($t_expert->add($_POST)) {
                    $this->success('添加成功', U('Estate/expert',array('pid'=>$estate['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('id' => $this->_post('id'));
                if ($t_expert->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Estate/expert',array('pid'=>$estate['id'])));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }
        $this->display();
    }
    public function expert_del()
    {
        $expert = M('Estate_expert');
        $id = $this->_get('id');
        $pid = $this->_get('pid');
        $where = array('id' => $id,'pid'=>$pid);
        $check = $expert->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $expert->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Estate/expert',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Estate/expert',array('pid'=>$pid)));
            }
        }
    }
    public function reservation()
    {
        $this->display();
    }
}
?>