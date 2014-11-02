<?php
class EstateAction extends Action
{
    public $token;
    public $wecha_id;
    public $pid;

    public function _initialize()
    {
        $this->token    = $this->_get('token');
        $this->wecha_id = $this->_get('wecha_id');
		$this->pid = $this->_get('pid');
        $this->assign('token', $this->token);
        $this->assign('wecha_id', $this->wecha_id);
        $this->assign('wecha_id', $this->pid);
		$where = array('id'=>$this->pid,'token'=>$this->token);
        $get_ids = M('Estate')->where($where)->field('res_id,classify_id')->find();
        $this->assign('rid', $get_ids['res_id']);
    }
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (!strpos($agent, "icroMessenger")) {
        }
        $data        = M("Estate");
        $this->token = $this->_get('token');
		$where = array('id'=>$this->pid,'token'=>$this->token);
        $es_data     = $data->where($where)->find();
        $this->assign('es_data', $es_data);
        $this->display();
    }
    public function news()
    {
        $data        = M("Estate");
        $this->token = $this->_get('token');
        $cid         = $data->getField('classify_id');
        if ($cid != null) {
            $t_classify = M('Classify');
            $where      = array(
                'id' => $cid
            );
            $classify   = $t_classify->where($where)->find();
        }
        $t_img  = M('Img');
        $where  = array(
            'classid' => $classify['id'],
        );
        $imgtxt = $t_img->where($where)->field('id as mid,title,pic,createtime')->select();
        $this->assign('imgtxt', $imgtxt);
        $this->assign('classify', $classify);
        $this->display();
    }
    public function newlist()
    {
        $mid    = (int) $this->_get('mid');
        $t_img  = M('Img');
        $where  = array(
            'id' => $mid,
        );
        $imgtxt = $t_img->where($where)->find();
        $this->assign('imgtxt', $imgtxt);
        $this->display();
    }
    public function housetype()
    {
        $t_housetype = M('Estate_housetype');
        $housetype   = $t_housetype->where($where)->order('sort desc')->select();
        foreach ($housetype as $k => $v) {
            $son_type[] = M("Estate_son")->where(array(
                'id' => $v['son_id']
            ))->field('id as sid,title')->find();
        }
        foreach ($son_type as $key => $value) {
            foreach ($value as $k => $v) {
                $housetype[$key][$k] = $v;
            }
        }
        $this->assign('housetype', $housetype);
        $data        = M("Estate");
        $this->token = $this->_get('token');
        $es_data     = $data->field('title,house_banner,panorama_id')->find();
        $this->assign('es_data', $es_data);
        $this->display();
    }
    public function album()
    {
        $Photo      = M("Photo");
        $photo_list = M('Photo_list');
        $t_album    = M('Estate_album');
        $album      = $t_album->field('id,poid')->select();
        foreach ($album as $k => $v) {
            $list_photo  = $Photo->where(array(
                'id' => $v['poid']
            ))->field('id')->find();
            $photolist[] = $photo_list->where(array(
                'pid' => $list_photo['id']
            ))->select();
        }
        $this->assign('photolist', $photolist);
        $this->assign('album', $list_photo);
        $this->display();
    }
    public function show_album()
    {
        $t_housetype = M('Estate_housetype');
        $id          = (int) $this->_get('id');
        $where       = array(
            'id' => $id
        );
        $housetype   = $t_housetype->where($where)->order('sort desc')->find();
        $data        = M("Estate");
        $this->token = $this->_get('token');
        $where       = array(
            'token' => $this->token
        );
        $es_data     = $data->where($where)->field('id,title')->find();
        if (!empty($es_data)) {
            $housetype = array_merge($housetype, $es_data);
        }
        $this->assign('housetype', $housetype);
        $this->display();
    }
    public function impress()
    {
		$where = array('pid'=>$this->pid);
        /*$t_impress = M('Estate_impress');
        $impress   = $t_impress->where($where)->order('sort desc')->select();
        $this->assign('impress', $impress);*/
        $t_expert = M('Estate_expert');
        $expert   = $t_expert->where($where)->order('sort desc')->select();
        $this->assign('expert', $expert);
        $this->display();
    }
    public function impress_add()
    {
        $t_impress = M('Estate_impress');
        $t_imp_add = M("Estate_impress_add");
        $imp_id    = (int) $this->_post('imp_id');
        $wecha_id  = $this->_post('wecha_id');
        $where     = array(
            'imp_id' => $imp_id,
            'wecha_id'
        );
        $check     = $t_imp_add->where($where)->find();
        $data      = array(
            'imp_id' => $imp_id,
            'msg' => $wecha_id
        );
        echo json_encode($data);
        exit;
        if ($check != null) {
            $data = array(
                'success' => 1,
                'msg' => "谢谢您的赞。"
            );
            echo json_encode($data);
            exit;
        }
        if ($id = $t_imp_add->add($_POST)) {
            $t_impress->where(array(
                'id' => $imp_id
            ))->setInc('comment');
            $data = array(
                'success' => 1,
                'msg' => "谢谢您的赞。"
            );
            echo json_encode($data);
            exit;
        } else {
            $data = array(
                'success' => 0,
                'msg' => "点赞失败，请再来一次吧。"
            );
            echo json_encode($data);
            exit;
        }
    }
    public function aboutus()
    {
        $company = M('Company');
        $about   = $company->find();
        $this->assign('about', $about);
        $this->display();
    }
}
?>