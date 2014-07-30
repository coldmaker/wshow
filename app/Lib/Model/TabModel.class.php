<?php
/**
 * File Name: PermissionModel.class.php
 * Author: Blue
 * Created Time: 2013-11-22 15:30:31
*/
class TabModel extends CommonModel{
    /**
     * 获取菜单列表
     */
    public function  getTabList($parent_id=0){
        $arrField = array('*');
        $arrMap['parent_id'] = array('eq', $parent_id);
        if($_SESSION['userInfo']['group_id'] != 1){
            //当用户为普通用户时，菜单显示方式
            $arrMap['status'] = array('eq', 2);
        }
        $arrOrder = array('sort_order');
        $tabList = $this->getList($arrField, $arrMap, $arrOrder);
        return $tabList;
    }


    /**
     * file list
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'p_id','name'=>'parent_id','type'=>'hidden'),
            array('title'=>'名称','name'=>'title','type'=>'text'),
            array('title'=>'图标','name'=>'ico','type'=>'text'),
            array('title'=>'描述','name'=>'intro','type'=>'textarea'),
            array('title'=>'内容','name'=>'info','type'=>'content'),
            array('title'=>'链接','name'=>'url','type'=>'text'),
            array('title'=>'排序','name'=>'sort_order','type'=>'number'),
            array('title'=>'显示状态','name'=>'status','type'=>'radio','data'=>array(
                array('title'=>'显示','value'=>'2'),
                array('title'=>'不显示','value'=>'1'),
            ),),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

}
