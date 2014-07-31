<?php
/**
 * Gallery meta mobel
 * @author chen
 * @version 2014-03-25
 */
class GalleryMetaModel extends CommonModel
{
    /**
     * format
     */
    public function format($arrInfo, $arrField){
        if(in_array('path_name', $arrField)){
            $arrInfo['path_name'] = getPicPath($arrInfo['path'], 'm');
        }
        return $arrInfo;
    }

    /**
     * add the img
     */
    public function addImg($img_name)
    {
        if(empty($img_name)){
            return 0;
            exit;
        }
        $gallery_id = D('Gallery')->getDefaultGalleryId($img_name);
        $data['gallery_id'] = $gallery_id;
        $data['path'] = $img_name;
        $data['date_modify'] = $data['date_add'] = time();
        $img_id = $this->add($data);
        return $img_id;
    }

    /**
     * get the image
     */
    public function getImg($img_id)
    {
        if(empty($img_id)){
            return 0;
            exit;
        }
        $path = $this->where('id='.$img_id)->getField('path');
        return $path; 
    }

    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'gallery_id','name'=>'gallery_id','type'=>'hidden'),
            array('title'=>'名称','name'=>'title','type'=>'text'),
            array('title'=>'地址','name'=>'path','type'=>'path'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

}
