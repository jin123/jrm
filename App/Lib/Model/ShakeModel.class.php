<?php

class ShakeModel extends Model
{
    public function addStrength($strength, $shakeid, $wechater)
    {
	
        $map['shakeid'] = $shakeid;
        $map['wechater'] = $wechater;
		$where_dur['shakeid']=$shakeid;
		$list_dur=M('Shake')->where($where_dur)->find();
		$duration=$list_dur['duration'];
        // $map['start_time'] = $start_time;
        if ($id = M('ShakeParter')->where($map)->getField('id')) {
            unset($map);
            $map['id'] = $id;
			$list_strength=M('ShakeParter')->order('strength desc')->find();
			$strengthlog=$list_strength['strength'];
			if($strengthlog>=$duration){
				return false;
			}else{
				if (M('ShakeParter')->where($map)->setInc('strength', $strength)) {
					return true;
				} else {
					return false;
				}
			}
        } else {
			$list_strength=M('ShakeParter')->order('strength desc')->find();
			$strengthlog=$list_strength['strength'];
			if($strengthlog>=$duration){
				return false;
			}else{
				$data = $map;
				$data['strength'] = $strength;
				if (M('ShakeParter')->add($data)) {
					return true;
				} else {
					return false;
				}
			}
        }
    }

    public function getTop($shakeid, $num = 10)
    {
        $map['shakeid'] = $shakeid;
        $list = M('ShakeParter')->where($map)->order('strength desc')->limit(10)->select();
        unset($map);
        foreach ($list as &$item)
        {
            $map['wechater'] = $item['wechater'];
            $wechaterInfo = M('Wechater')->where($map)->find();
            $item = array_merge($wechaterInfo, $item);
        }
        unset($item);
        return $list;
    }

    public function getShakeInfo($shakeid,$token)
    {
        $map['id'] = $shakeid;
		$map['token'] = $token;
        return $this->where($map)->find();
    }
}