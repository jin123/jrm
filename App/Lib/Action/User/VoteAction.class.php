<?php

class VoteAction extends CommonAction
{
    public function index()
    {
		$where['token'] = session('token'); 
        $count = M('Vote')->where($where)->count();
		$page=new Page($count,25);
        $list  = M('Vote')->where($where)->order('id DESC')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('count', $count);
		$this->assign('list', $list);
		$this->assign('page',$page->show());
        $this->display();
    }
    public function totals()
    {
        $id       = $this->_get('id');
        $t_vote   = M('Vote');
        $t_record = M('Vote_record');
        $where    = array('id' => $id);
        $vote     = $t_vote->where($where)->find();
        if (empty($vote)) {
            exit('非法操作');
        }
        $vote_item = M('Vote_item')->where('vid=' . $vote['id'])->select();
        $this->assign('count', $t_record->where(array('vid' => $id))->count());

        $total       = $t_record->where('vid=' . $id)->count('touched');
        $item_count  = M('Vote_item')->where('vid=' . $id)->select();
        foreach ($item_count as $k => $value) {
            $vote_item[$k]['per'] = (number_format(($value['vcount'] / $total), 4)) * 100;
        }
        $this->assign('vote_item', $vote_item);
        $this->assign('vote', $vote);
        $this->display();
    }
    public function add()
    {
        $this->assign('type', $this->_get('type'));
        if (IS_POST) {
            $adds = $_REQUEST['add'];
            if (empty($adds)) {
                $this->error('投票选项你还没有填写');
                exit;
            }
            foreach ($adds as $ke => $value) {
                foreach ($value as $k => $v) {
                    $item_add[$k][$ke] = $v;
                }
            }
            $db              = D('Vote');
            $_POST['id']       = $this->_get('id');
            $_POST['type']     = $this->_get('type');
            $_POST['statdate'] = strtotime($this->_post('statdate'));
            $_POST['enddate']  = strtotime($this->_post('enddate'));
            $_POST['cknums']   = $this->_post('cknums');
            $_POST['display']  = $this->_post("display");
            $_POST['info']     = strip_tags($this->_post("info"));
            $_POST['picurl']   = $this->_post("picurl");
            $_POST['title']    = $this->_post("title");
            $_POST['keyword']  = $this->_post('keyword');

            $t_item            = M('Vote_item');
            if ($db->create() != false) {
				$db->token = session('token');
                if ($id = $db->add()) {
                    foreach ($item_add as $k => $v) {
                        $data2['vid']    = $id;
                        $data2['item']   = $v['item'];
                        $data2['rank']   = $v['rank'];
                        $data2['vcount'] = $v['vcount'];
                        if ($_POST['type'] == 'img') {
                            $data2['startpicurl'] = $v['startpicurl'];
                            $data2['tourl']       = $v['tourl'];
                        }
                        $t_item->add($data2);
                    }
                    $data1['pid']     = $id;
                    $data1['module']  = 'Vote';
                    $data1['token']  = session('token');
                    $data1['keyword'] = trim($_POST['keyword']);
                    M('Keyword')->add($data1);
                    $this->success('添加成功', U('Vote/index'));
                } else {
                    $this->error('服务器繁忙,请稍候再试');
                }
            } else {
                //$this->error($data->getError());
            }
        } else {
            $this->display();
        }
    }
    public function del()
    {
        $type   = $this->_get('type');
        $id     = $this->_get('id');
        $vote   = M('Vote');
        $where   = array('id' => $id,'type' => $type,'token'=>session('token'));
        $result = $vote->where($where)->find();
        if ($result) {
            $vote->where('id=' . $result['id'])->delete();
            M('Vote_item')->where('vid=' . $result['id'])->delete();
            M('Vote_record')->where('vid=' . $result['id'])->delete();
            $where = array('pid' => $result['id'],'module' => 'Vote');
            M('Keyword')->where($where)->delete();
            $this->success('删除成功', U('Vote/index'));
        } else {
            $this->error('非法操作！');
        }
    }
    public function setinc()
    {
        $id    = $this->_get('id');
        $where = array('id' => $id,'token'=>session('token'));
        $check = M('Vote')->where($where)->find();
        if ($check == false)
            $this->error('非法操作');

        if ($check['status'] == 0) {
            $data = M('Vote')->where($where)->save(array('status' => 1));
            $tip  = '恭喜你,活动已经开始';
        } else {
            $data = M('Vote')->where($where)->save(array('status' => 0));
            $tip  = '设置成功,活动已经结束';
        }
        if ($data != false) {
            $this->success($tip);
        } else {
            $this->error('设置失败');
        }
    }
    public function setdes()
    {
        $id    = $this->_get('id');
        $where = array('id' => $id,'token'=>session('token'));
        $check = M('Vote')->where($where)->find();
        if ($check == false)
            $this->error('非法操作');
        $data = M('Vote')->where($where)->setDec('status');
        if ($data != false) {
            $this->success('活动已经结束');
        } else {
            $this->error('服务器繁忙,请稍候再试');
        }
    }
    public function edit()
    {
        $this->assign('type', $this->_get('type'));
        if (IS_POST) {
            $db              = D('Vote');
            $_POST['id']       = $this->_get('id');
            $_POST['type']     = $this->_get('type');
            $_POST['statdate'] = strtotime($this->_post('statdate'));
            $_POST['enddate']  = strtotime($this->_post('enddate'));
            $_POST['cknums']   = $this->_post('cknums');
            $_POST['display']  = $this->_post("display");
            $_POST['info']     = strip_tags($this->_post("info"));
            $_POST['picurl']   = $this->_post("picurl");
            $_POST['title']    = $this->_post("title");
            $where             = array('id' => $_POST['id'],'token'=>session('token'));
            $check             = $db->where($where)->find();

            if ($check == false)$this->error('非法操作');

            if (empty($_REQUEST['add'])) {
                $this->error('投票选项必须填写');
                exit;
            }
			
            $t_item = M('Vote_item');
            $datas  = $_REQUEST['add'];
            foreach ($datas as $ke => $value) {
                foreach ($value as $k => $v) {
                    $item_add[$k][$ke] = $v;
                }
            }
            $isnull = $t_item->where('vid=' . $_POST['id'])->find();
            foreach ($item_add as $k => $v) {
                $i_id['id'] = $v['id'];
                if ($i_id['id'] != '') {
                    $data2['item']   = $v['item'];
                    $data2['rank']   = $v['rank'];
                    $data2['vcount'] = $v['vcount'];
					$data2['awardurl'] = $v['awardurl'];
                    if ($this->_get('type') == 'img') {
                        $data2['startpicurl'] = $v['startpicurl'];
                        $data2['tourl']       = $v['tourl'];
                    }
                    $t_item->where('id=' . $i_id['id'])->save($data2);
                }else{
					
					$data2['vid']    = $_POST['id'];
					$data2['item']   = $v['item'];
					$data2['rank']   = $v['rank'];
					$data2['vcount'] = $v['vcount'];
					$data2['awardurl'] = $v['awardurl'];
					if ($_POST['type'] == 'img') {
						$data2['startpicurl'] = $v['startpicurl'];
						$data2['tourl']       = $v['tourl'];
					}
					if(!empty($data2['item'])){
						$t_item->add($data2);
					}
				}
            }

            if ($db->create()) {
                if ($db->where($where)->save($_POST)) {
                    $data1['pid']    = $_POST['id'];
                    $data1['module'] = 'Vote';
                    $data1['token']  = session('token');
                    $da['keyword']   = trim($_POST['keyword']);
                    M('Keyword')->where($data1)->save($da);
                    $this->success('修改成功', U('Vote/index'));
				} else {
				    $this->success('修改成功', U('Vote/index'));
				}
            } else {
                $this->error($db->getError());
            }
        } else {
            $id    = $this->_get('id');
            $where = array('id' => $id,'token'=>session('token'));
            $db  = M('Vote');
            $check = $db->where($where)->find();
            if ($check == false)
                $this->error('非法操作');
            $vo    = $db->where($where)->find();
            $items = M('Vote_item')->where('vid=' . $id)->select();
			$itemcount = M('Vote_item')->where('vid=' . $id)->count();
			$this->assign('itemcount',$itemcount);
            $this->assign('items', $items);
            $this->assign('vo', $vo);
            $this->display('edit');
        }
    }
	
	public function vote_del(){
		$id    = $this->_get('id');
		$where = array('id' => $id);
        $db  = M('Vote_item');
        $check = $db->where($where)->find();
		$vote = M('Vote')->where("id = ".$check['vid'])->find();

		if ($check == false)$this->error('非法操作');	
	
		if(D('Vote_item')->where($where)->delete()){
			$this->success('操作成功',U('Vote/edit',array('type'=>$vote['type'],'id'=>$vote['id'])));
		}else{
			$this->error('操作失败',U('Vote/edit',array('type'=>$vote['type'],'id'=>$vote['id'])));
		}
	}
	
}
?>
