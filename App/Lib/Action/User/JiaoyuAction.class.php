<?php
/**
* 教育机构
**/
class JiaoyuAction extends CommonAction
{
    public function index()
    {
        $db = M('Jiaoyu');
        $where['token'] = session('token');
        $count = $db->where($where)->count();
        $page=new Page($count,25);
        $jiaoyu=$db->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page',$page->show());     
        $this->assign('jiaoyu',$jiaoyu);
        $this->display();
    }

   public function jiaoyuSet()
    {
        $db = M('Jiaoyu');
        $where['token'] = session('token');
        $where['id'] = $this->_get('id');
        $jy_data = $db->where($where)->find();
        
        $panorama = M('Panorama')->where(array('token'=>session('token')))->field('id as pid,name,keyword')->select();
        $this->assign('panorama', $panorama);
        
        $classify = M('Classify')->where(array('token'=>session('token')))->field('id as cid,name')->select();
        $this->assign('classify', $classify);
        
        $selfform = M('Selfform')->where(array('token'=>session('token')))->field('id as sid,name')->select();
        $this->assign('selfform', $selfform);

        $member_card = M('Member_card_set')->where(array('token'=>session('token')))->field('id as mid,cardname')->select();
        $this->assign('member_card', $member_card);

        if (IS_POST) {
            if ($jy_data == null) {   

                $this->all_insert('Jiaoyu');

            } else {
                $this->all_save('Jiaoyu');
                
            }
        } else {
            $this->assign('jy_data', $jy_data);
            $this->display();
        }
    }
    
    public function del()
    {
        $Jiaoyu = M('Jiaoyu');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $Jiaoyu->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $Jiaoyu->where($where)->delete();
            if ($isok) {
                $where = array('pid'=>$check['id']);
                M('jiaoyu_son')->where($where)->delete();
                M('jiaoyu_housetype')->where($where)->delete();
                M('jiaoyu_album')->where($where)->delete();
                M('jiaoyu_expert')->where($where)->delete();
                M('keyword')->where(array('pid'=>$check['id'],'token'=>session('token')))->delete();
                $this->success('删除成功', U('Jiaoyu/index'));
            } else {
                $this->error('删除失败', U('Jiaoyu/index'));
            }
        }
    }
    
    public function son()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('Jiaoyu')->where(array('token'=>session('token')))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);
                
        $jiaoyu_son = M('Jiaoyu_son');
        $where = array('token' => session('token'),'pid'=>$pid);
        $count = $jiaoyu_son->where($where)->count();
        $page=new Page($count,25);
        $son_data=$jiaoyu_son->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->order('sort desc')->select();
        $this->assign('page',$page->show());
        $this->assign('son_data',$son_data);
        $this->display();
    }

    public function son_add()
    {
        $pid = (int) $this->_get('pid');
        $jiaoyu = M('Jiaoyu')->field('id')->where(array('token'=>session('token'),'id'=>$pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}        
        $this->assign('pid',$jiaoyu['id']);

        $t_son = M('Jiaoyu_son');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $t_son->where($where)->find();

        if ($check != null) {
          $this->assign('son', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                $_POST['token'] = session('token');
                if ($t_son->add($_POST)) {
                    $this->success('添加成功', U('Jiaoyu/son',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('token' => session('token'), 'id' => $this->_post('id'));
                if ($t_son->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/son',array('pid'=>$jiaoyu['id'])));
                } else {
                    $this->error('操作失败');
                }
            }
        }else
        {
         $this->display();
        }
    }


    public function son_del()
    {
        $t_son = M('Jiaoyu_son');
        $id = (int) $this->_get('id');
        $pid = (int) $this->_get('pid');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $t_son->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $t_son->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Jiaoyu/son',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Jiaoyu/son',array('pid'=>$pid)));
            }
        }
    }

    public function housetype()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('Jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);
                
        
        $t_housetype = M('Jiaoyu_housetype');
        $where = array('token' => session('token'),'pid'=>$jiaoyu['id']);
        $count = $t_housetype->where($where)->count();
        $page=new Page($count,25);
        $housetype=$t_housetype->where($where)->order('createtime DESC')->limit($page->firstRow.','.$page->listRows)->order('sort desc')->select();
        $this->assign('page',$page->show());
        
        foreach ($housetype as $k => $v) {
            $son_type[] = M('Jiaoyu_son')->where(array('id' => $v['son_id']))->field('id as sid,title')->find();
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
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);
                
        $t_housetype = M('Jiaoyu_housetype');
        $id = (int) $this->_get('id');
        $where = array('id' => $id, 'token' =>session('token'));
        $check = $t_housetype->where($where)->find();
        
        $son_data = M('Jiaoyu_son')->where(array(
        'token' => session('token'),'pid' => $pid
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
                    $this->success('添加成功', U('Jiaoyu/housetype',array('pid' =>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array( 'id' => $this->_get('id'));
                if ($t_housetype->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/housetype',array('pid' =>$jiaoyu['id'])));
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
        $housetype = M('Jiaoyu_housetype');
        $id = (int) $this->_get('id');
        $pid = (int) $this->_get('pid');
        $where = array('id' => $id, 'token' => session('token'));
        $check = $housetype->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $housetype->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Jiaoyu/housetype',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Jiaoyu/housetype',array('pid    '=>$pid)));
            }
        }
    }

    public function album()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);
        
        $Photo = M('Photo');
        $t_album = M('Jiaoyu_album');
        $album = $t_album->where(array('token' => session('token'),'pid'=>$jiaoyu['id']))->field('id,poid')->select();
        foreach ($album as $k => $v) {
            $list_photo[] = $Photo->where(array('token' => session('token'), 'id' => $v['poid']))->order('id desc')->find();
        }
        $this->assign('album', $list_photo);
        $this->display();
    }

    public function album_add()
    {
        
        $pid = $this->_get('pid');
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);

        $po_data = M('Photo');
        $list = $po_data->where(array(
            'token' => session('token')
        ))->field('id,title')->select();
        
        $this->assign('photo', $list);
        $t_album = M('Jiaoyu_album');
        $poid = (int) $this->_get('poid');
        
        $check = $t_album->where(array(
            'poid' => $poid,'pid'=>$jiaoyu['id']
        ))->find();
        
        $this->assign('album', $check);
        
        if (IS_POST) {
            if ($check == NULL) {
                $check_ex = $t_album->where(array('token' => session('token'), 'poid' => $this->_post('poid'),'pid' => $this->_post('pid')))->find();
                if ($check_ex) {
                    $this->error('您已经添加过该相册，请勿重复添加。');
                    die;
                }
                $_POST['token'] = session('token');              
                if($_POST['poid']==0){
                    $this->error('请选择相册');
                    die;
                }
                if ($t_album->add($_POST)) {
                    $this->success('添加成功', U('Jiaoyu/album',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('token' => session('token'), 'id' => $this->_post('id'));
                if ($t_album->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/album',array('pid'=>$jiaoyu['id'])));
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
        $album = M('Jiaoyu_album');
        $poid = $this->_get('poid');
        $pid = $this->_get('pid');
        $where = array('poid' => $poid,'pid'=>$pid);
        $check = $album->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $album->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Jiaoyu/album',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Jiaoyu/album',array('pid'=>$pid)));
            }
        }
    }
    
    
/*    public function impress()
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
*/    
    public function expert()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);
        
        $t_expert = M('Jiaoyu_expert');
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
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);     
        
        $t_expert = M('Jiaoyu_expert');
        $id = $this->_get('id');
        $where = array('id' => $id,'pid'=>$jiaoyu['id']);
        $check = $t_expert->where($where)->find();
        if ($check != null) {
            $this->assign('expert', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                if ($t_expert->add($_POST)) {
                    $this->success('添加成功', U('Jiaoyu/expert',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('id' => $this->_post('id'));
                if ($t_expert->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/expert',array('pid'=>$jiaoyu['id'])));
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
        $expert = M('Jiaoyu_expert');
        $id = $this->_get('id');
        $pid = $this->_get('pid');
        $where = array('id' => $id,'pid'=>$pid);
        $check = $expert->where($where)->find();
        if ($check == null) {
            $this->error('操作失败');
        } else {
            $isok = $expert->where($where)->delete();
            if ($isok) {
                $this->success('删除成功', U('Jiaoyu/expert',array('pid'=>$pid)));
            } else {
                $this->error('删除失败', U('Jiaoyu/expert',array('pid'=>$pid)));
            }
        }
    }
    public function haibao()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);    
        $haibao = M('Jiaoyu_haibao');
        $where = array('pid' => $pid);
        $check = $haibao->where($where)->find();

        if ($check != null) {
          $this->assign('haibao', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                if ($haibao->add($_POST)) {
                    $this->success('添加成功', U('Jiaoyu/haibao',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('id' => $this->_post('id'));
                if ($haibao->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/haibao',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }
       
        $this->display();
    }
    public function daohang()
    {
        $pid = $this->_get('pid');
        $jiaoyu = M('jiaoyu')->where(array('token'=>session('token'),'id' => $pid))->find();
        if(!$jiaoyu){$this->error('非法参数！');}
        $this->assign('pid',$jiaoyu['id']);    
        $daohang = M('Jiaoyu_daohang');
        $where = array('pid' => $pid);
        $check = $daohang->where($where)->find();

        if ($check != null) {
          $this->assign('daohang', $check);
        }
        if (IS_POST) {
            if ($check == null) {
                if ($daohang->add($_POST)) {
                    $this->success('添加成功', U('Jiaoyu/daohang',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                $wh = array('id' => $this->_post('id'));
                if ($daohang->where($wh)->save($_POST)) {
                    $this->success('修改成功', U('Jiaoyu/daohang',array('pid'=>$jiaoyu['id'])));
                    die;
                } else {
                    $this->error('操作失败');
                }
            }
        }
       
        $this->display();
    }
    public function reservation()
    {
        $this->display();
    }
}
?>