<?php
/**
 * Member wechat push model
 * @author chen
 * @version 2014-04-10
 */
class MemberPushModel extends CommonModel
{
    /**
     * add the wechat message
     */
    public function addWechatMessage($arrPost, $member_id)
    {
        if(!empty($member_id)){
        switch ($arrPost['MsgType']){
        case 'event':
            $content = $arrPost['Event'];
            break;
        case 'text':
            $content = $arrPost['Content'];
            break;
        default :
            $content = '未知';
            break;
        }
        $insert = array(
            'member_id' => $member_id,
            'type'     => $arrPost['MsgType'],
            'info'     => $content,
            'date_push' => time(),
        );
        $id = $this->add($insert);
        return $id;
        }
    }

}
