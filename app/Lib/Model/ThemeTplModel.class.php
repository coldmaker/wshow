<?php
/**
 * The Template Model
 * @author chen
 * @version 2014-03-15
 */
class ThemeTplModel extends CommonModel
{
    /**
     * Get the Theme template list
     * @return array $temlist
     */
    public function getTplList()
    {
        $theme_id = D('Setting')->field('id,name')->where('user_id='.$_SESSION['uid'])->getField('theme_id');
        $map['theme_id'] = array('in', array($theme_id, '1'));
        $tplList = D('ThemeTpl')->where($map)->select();
        foreach($tplList as $k=>$v){
            $list[$k]['title'] = $v['name'];
            $list[$k]['value'] = $v['id'];
        }
        return $list;
    }

    /**
     * format the template item
     * @param array $arrInfo
     * @param array $arrFormatField
     * @return array $arrInfo
     */
    public function format($arrInfo, $arrFormatField)
    {
        if(in_array('status_name', $arrFormatField)){
            $arrInfo['status_name'] = ($arrInfo['status'] == 1) ? '使用' : '未使用';
        }
        return $arrInfo;
    }

    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'theme_id','name'=>'theme_id','type'=>'hidden'),
            array('title'=>'名称','name'=>'name','type'=>'text'),
            array('title'=>'标记','name'=>'spell','type'=>'text'),
            array('title'=>'排序','name'=>'sort_order','type'=>'number'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

}
