<?php

/**
 * File Name: UserModel.class.php
 * Author: Blue
 * Created Time: 2013-11-15 9:02:37
*/

class UserModel extends CommonModel{


    /**
     * file list
     */
    public function field_list($list)
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'头像','name'=>'avatar','type'=>'sel_img'),
            array('title'=>'用户名','name'=>'name','type'=>'text'),
            array('title'=>'手机号码','name'=>'mobile','type'=>'tel'),
            array('title'=>'联系地址','name'=>'address','type'=>'text'),
            array('title'=>'电子邮箱','name'=>'email','type'=>'email'),
            array('title'=>'接口地址','name'=>'api_url','type'=>'disabled'),
            array('title'=>'借口凭证','name'=>'token','type'=>'text'),
            array('title'=>'APPID','name'=>'appid','type'=>'text'),
            array('title'=>'APPSECRECT','name'=>'appsecrect','type'=>'text'),
            array('title'=>'注册日期','name'=>'date_reg','type'=>'date'),
            array('title'=>'网站名称','name'=>'site_name','type'=>'text'),
            array('title'=>'网站链接','name'=>'set_url','type'=>'disabled'),
            array('title'=>'纬度','name'=>'latitude','type'=>'number'),
            array('title'=>'经度','name'=>'longitude','type'=>'number'),
            array('title'=>'幻灯片','name'=>'banner_list','type'=>'select','data'=>D('Gallery')->where('user_id='.$_SESSION['uid'])->select()),
        );
    }

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
     */
    public function getColorSpell()
    {
        $color_id = D('CmsSetting')->where('wechat_id='.$id)->getField('color_id');
        $color_spell = D('CmsThemeColor')->where('id='.$color_id)->getField('spell');
        return $color_spell;
    }
	/**
	 * 输出格式化
	 */

	public function format($info, $arrFormatField){

		//分组

		if(in_array('group_name', $arrFormatField)){

			$info['group_name'] = ($info['group_id'] == 1) ? '管理员' : '普通会员';

		}

		//时间

		if(in_array('data_log_text', $arrFormatField)){

			$info['data_log_text'] = date('Y-m-d H:i', $info['data_log']);

		}

		//头像

		if(in_array('avatar_name', $arrFormatField)){

			$info['avatar_name'] = getPicPath(D('GalleryMeta')->getImg($info['avatar']), 's');
		}

        //api_url
        if(in_array('url', $arrFormatField)){
            $info['url'] = 'http://'.$_SERVER['HTTP_HOST'].U('Home/Wx/wxapi', array('user'=>$info['name']));
        }

        //set_url
        if(in_array('set_url', $arrFormatField)){
            $user_name = D('User')->where('id='.$info['id'])->getField('name');
            $info['set_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Mobile&user='.$user_name;
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

}

