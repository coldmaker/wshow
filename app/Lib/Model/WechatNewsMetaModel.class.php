<?php
/**
 * news meta model
 * @author vhen
 * @version 2014-03-17
 */
class WechatNewsMetaModel extends CommonModel
{
    /**
     * format action
     * @param array $arrInfo
     * @param array @arrFormatField
     * @return array $arrInfo
     */
    public function format($arrInfo, $arrFormatField)
    {
        if(in_array('cover_name', $arrFormatField)){
            $arrInfo['cover_name'] = getPicPath(D('GalleryMeta')->getImg($arrInfo['cover']));
        }
        return $arrInfo;
    }

    /**
     * field_list
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'news_id','name'=>'news_id','type'=>'hidden'),
            array('title'=>'route_id','name'=>'route_id','type'=>'hidden'),
            array('title'=>'关键字','name'=>'keyword','type'=>'hidden'),
            array('title'=>'标题','name'=>'title','type'=>'text'),
            array('title'=>'图片','name'=>'cover','type'=>'sel_img'),
            array('title'=>'描述','name'=>'description','type'=>'textarea'),
            array('title'=>'内容','name'=>'content','type'=>'content'),
            array('title'=>'链接地址','name'=>'url','type'=>'url'),
            array('title'=>'更新时间','name'=>'date_modify'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

    /**
     * update the meta action
     */
    public function updateMeta($meta, $news_id)
    {
        $metaObj = D('WechatNewsMeta');
        $meta['date_modify'] = time();
        if(empty($meta['id'])){
            $meta['news_id'] = $news_id;
            $meta['date_add'] = time();
            $result = $metaObj->add($meta);
        }else{
            $result = $metaObj->save($meta);
        }
        return $result;
    }


}
