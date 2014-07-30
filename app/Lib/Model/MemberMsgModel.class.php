<?php
/**
 * member msg model
 * @author chen
 * @version 2014-03-18
 */
class MemberMsgModel extends CommonModel
{

    /**
     * format the data
     * @param array $arrInfo
     * @param array @arrFormatField
     * @return array $arrInfo
     */
    public function format($arrInfo, $arrFormatField)
    {
        if(in_array('member_name', $arrFormatField)){
            $member_name = D('Member')->where('id='.$arrInfo['member_id'])->getField('name');
            $arrInfo['member_name'] = ($member_name) ? $member_name : '无';
        }
        if(in_array('mobile_name', $arrFormatField)){
            $arrInfo['mobile_name'] = ($arrInfo['mobile']) ? $arrInfo['mobile'] : '未知';
        }
        return $arrInfo;
    }
}
