<?php
/**
 * File Name: ThemeModel.class.php
 * Author: chen
 * Created Time: 2013-11-21 13:56:30
*/
class ThemeModel extends CommonModel{
	/**
	 * 格式化
	 */
	public function format($info, $arrFormatField){
		//时间
        if(in_array('date_add_text', $arrFormatField)){
            $info['date_add_text'] = date('Y-m-d H:i', $info['date_add']);
        }
		if(in_array('date_modify_text', $arrFormatField)){
			$info['date_modify'] = date('Y-m-d H:i', $info['date_modify']);
		}
		//效果图
		if(in_array('cover_name', $arrFormatField)){
			$info['cover_name'] = getPicPath(D('GalleryMeta')->getImg($info['cover']));
		}
		//类型
		if(in_array('type_name', $arrFormatField)){
			$info['type_name'] = '深蓝';
		}
		return $info;
	}

    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'名称','name'=>'name','type'=>'text'),
            array('title'=>'标记','name'=>'spell','type'=>'text'),
            array('title'=>'描述','name'=>'intro','type'=>'textarea'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }
} 
