<?php
/**
 * 微网设置选项
 * @author blue
 * @version 2013-10-06
 */

class SettingModel extends CommonModel {
    /**
     * 获取主题文件名
     * @return string $theme_spell 主题名称
     * @param int $id 微网ID
     */
    public function getThemeSpell($id){
        $theme_id = D('CmsSetting')->where('wechat_id='.$id)->getField('theme_id');
        $theme_spell = D('CmsTheme')->where('id='.$theme_id)->getField('spell');
        return $theme_spell;
    }

    /**
     * 获取配色文件名
     * @return string $color_spell 配色名称
     * @param int $id 微网ＩＤ
     */
    public function getColorSpell($id){
        $color_id = D('CmsSetting')->where('wechat_id='.$id)->getField('color_id');
        $color_spell = D('CmsThemeColor')->where('id='.$color_id)->getField('spell');
        return $color_spell;
    }

    /**
     * 格式化
     * @return string $info 格式化后的数组
     * @param  string $info 格式化前的数组
     * @param  string $arrFormatField 需要格式化的数据
     */
    public function format($info, $arrFormatField){
        //url
        if(in_array('url', $arrFormatField)){
            $user_name = D('User')->where('id='.$info['user_id'])->getField('name');
            $info['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Mobile&user='.$user_name;
        }

        //logo
        if(in_array('logo_name', $arrFormatField)){
            $info['logo_name'] = getPicPath(D('GalleryMeta')->getImg($info['logo']));
        }

        //主题名称
        if(in_array('theme_name', $arrFormatField)){
            $info['theme_name'] = D('Theme')->where('id='.$info['theme_id'])->getField('name');
        }

        //主题目录
        if(in_array('theme_spell', $arrFormatField)){
            $info['theme_spell'] = D('CmsTheme')->where('id='.$info['theme_id'])->getField('spell');
        }

        //配色方案
        if(in_array('color_name', $arrFormatField)){
            $info['color_name'] = D('CmsThemeColor')->where('id='.$info['color_id'])->getField('color_name');
        }
        if(in_array('color_spell', $arrFormatField)){
            $info['color_spell'] = D('CmsThemeColor')->where('id='.$info['color_id'])->getField('spell');
        }
        return $info;
    }

    /**
     * 字段属性
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'网站名称','name'=>'site_name','type'=>'text'),
            array('title'=>'网站链接','name'=>'url','type'=>'disabled'),
            array('title'=>'联系号码','name'=>'tel','type'=>'tel'),
            array('title'=>'联系地址','name'=>'address','type'=>'text'),
            array('title'=>'电子邮箱','name'=>'email','type'=>'email'),
            array('title'=>'纬度','name'=>'latitude','type'=>'number'),
            array('title'=>'经度','name'=>'longitude','type'=>'number'),
            array('title'=>'幻灯片','name'=>'banner_list','type'=>'select'),
        );
    }
}
?>
