<?php
/**
 * member model
 * @warning if the new guest use another's link to visit the site,
 * then the system cann't check if the current member , how to ?
 * @author chen
 * @version 2014-03-18
 */
class MemberModel extends CommonModel
{
    /**
     * get the member_id
     * @param int $wechat_id
     * @return int member_id
     */
    public function getMemberIdByWechatId($user_id, $wechat_id)
    {
        $map['user_id'] = array('eq', $user_id);
        $map['wechat_id'] = array('eq', $wechat_id);
        $result = $this->where($map)->find();
        if(empty($result)){
            $data = array(
                'user_id' => $user_id,
                'wechat_id' => $wechat_id,
                'name' => $wechat_id,
                'date_reg' => time(),
                'date_login' => time(),
            );
            $member_id = $this->add($data);
        }else{
            $this->where($map)->setField('date_login', time());
            $member_id = $result['id'];
        }
        return $member_id;
    }

    /**
     * format action
     */
    public function format($arrInfo, $arrFormat)
    {
        if(in_array('name', $arrFormat)){
            $arrInfo['name'] = ($arrInfo['name']) ? $arrInfo['name'] : '无';
        }
        if(in_array('mobile', $arrFormat)){
            $arrInfo['mobile'] = ($arrInfo['mobile']) ? $arrInfo['mobile'] : '无';
        }
        if(in_array('avatar_name', $arrFormat)){
            $arrInfo['avatar_name'] = getPicPath(D('GalleryMeta')->getImg($arrInfo['avatar']), 'm');
        }
        return $arrInfo;
    }

    /**
     * file list
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'姓名','name'=>'name','type'=>'text'),
            array('title'=>'手机号码','name'=>'mobile','type'=>'tel'),
            array('title'=>'地址','name'=>'address','type'=>'text'),
            array('title'=>'访问时间','name'=>'date_login','type'=>'date'),
        );
    }



}
