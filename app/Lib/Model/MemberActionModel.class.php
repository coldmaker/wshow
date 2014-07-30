<?php
/**
 * 会员操作模型类
 * @author chen
 * @version 2014-06-09
 */
class MemberActionModel extends CommonModel
{

    /**
     * file list
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'会员','name'=>'member_id','type'=>'hidden'),
            array('title'=>'行为','name'=>'action','type'=>'text'),
            array('title'=>'时间','name'=>'date_create','type'=>'date'),
        );
    }


}
