<?php
/**
 * @公用控制器
 */
class CommonAction extends Action{
	//初始化页面
	public function _initialize(){
		if (!isset($_SESSION['UserId'])|| (time() - $_SESSION['lastLoginTime'] > 3600))
		{
			$this->redirect('Login/index');	
			
		}
		else
		{	
			$this->assign('token',session('token'));
			$_SESSION['lastLoginTime'] = time();
		}
	}
	
    protected function all_insert($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
			$db->token = session('token');
            $id = $db->add($data);
            if ($id) {
                $m_arr = array(
                    'Img',
                    'Text',
                    'Voiceresponse',
                    'Ordering',
                    'Lottery',
                    'Host',
                    'Product',
                    'Selfform',
                    'Panorama',
                    'Wedding',
                    'Vote',
					'Estate',
					'Survey',
					'Liuyan_set',
					'Survey',
					'Groupbuy',
					'jiaoyu_jigou',
					'Yl_hospital',
					'Reply_info',
                );
                if (in_array($name, $m_arr)) {
                    $data['pid']     = $id;
                    $data['module']  = $name;
                    $data['keyword'] = $_POST['keyword'];
					$data['token'] = session('token');
                    M('Keyword')->add($data);
                }
             //   var_dump(U(MODULE_NAME . $back));die;
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
	
    protected function insert($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
			$db->token = session('token');
            $id = $db->add();
            if ($id == true) {
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
	
    protected function save($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->save();
            if ($id == true) {
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
	
    protected function all_save($name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->create() === false) {
            $this->error($db->getError());
        } else {
            $id = $db->save();
            if ($id) {
                $m_arr = array(
                    'Img',
                    'Text',
                    'Voiceresponse',
                    'Ordering',
                    'Lottery',
                    'Host',
                    'Product',
                    'Selfform',
                    'Panorama',
                    'Wedding',
                    'Vote',
					'Estate',
					'Survey',
					'Liuyan_set',
					'Survey',
					'Groupbuy',
					'jiaoyu_jigou',
					'Yl_hospital',
					'Reply_info',
                );
                if (in_array($name, $m_arr)) {
                    $data['pid']    = $_POST['id'];
                    $data['module'] = $name;
					$data['token']  = session('token');
                    $da['keyword']  = $_POST['keyword'];
                    M('Keyword')->where($data)->save($da);
                }
                $this->success('操作成功', U(MODULE_NAME . $back));
            } else {
                $this->error('操作失败', U(MODULE_NAME . $back));
            }
        }
    }
	
    protected function all_del($id, $name = '', $back = '/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db   = D($name);
        if ($db->delete($id)) {
            $this->ajaxReturn('操作成功', U(MODULE_NAME . $back));
        } else {
            $this->ajaxReturn('操作失败', U(MODULE_NAME . $back));
        }
    }
	
}
?>