<?php
/**
 * 内容模型类
 * @author chen
 * @version 2014-03-12
 */
class ItemModel extends CommonModel
{
    /**
     * 格式化
     * @param array $arrInfo
     * @param array $arrFormatFile
     * @return array $arrInfo
     */
    public function format($arrInfo, $arrFormatField){
        if(in_array('cover_name', $arrFormatField)){
            $arrInfo['cover_name'] = getPicPath(D('GalleryMeta')->getImg($arrInfo['cover']), 's');
        }
        if(in_array('template_name', $arrFormatField)){
            $arrInfo['template_name'] = D('ThemeTpl')->where('id='.$arrInfo['template_id'])->getField('spell');
        }
        if(in_array('ext', $arrFormatField)){
            $arrInfo['ext'] = D('Ext')->getExtList('item', $arrInfo['id']);
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
            array('title'=>'组名','name'=>'parent_id','type'=>'hidden'),
            array('title'=>'标题','name'=>'title','type'=>'text'),
            array('title'=>'封面','name'=>'cover','type'=>'sel_img'),
            array('title'=>'描述','name'=>'intro','type'=>'textarea'),
            array('title'=>'内容','name'=>'info','type'=>'content'),
            array('title'=>'显示模板','name'=>'template_id','type'=>'select','data'=>D('ThemeTpl')->getTplList()),
            array('title'=>'使用状态','name'=>'status','type'=>'radio','data'=>array(
                array('title'=>'使用','value'=>'1'),
                array('title'=>'不使用','value'=>'0'),
            ),),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
            array('title'=>'子属性','name'=>'ext_list','type'=>'ext_list'),
            array('title'=>'属性','name'=>'ext_info','type'=>'ext_info'),
        );
    }

}
