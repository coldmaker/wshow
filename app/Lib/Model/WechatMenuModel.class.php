<?php
/**
 * 菜单模型
 */
class WechatMenuModel extends CommonModel
{
    /**
     * 获取token
     */
    public function getToken(){
        $userInfo = D('User')->where('id='.$_SESSION['uid'])->find();
        $grant_type = 'client_credential';
        $appid = $userInfo['appid'];
        $appsecret = $userInfo['appsecrect'];
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type='.$grant_type.'&appid='.$appid.'&secret='.$appsecret;

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        //将获取到的内容json解码为类
        $result = json_decode($result);
        if($result->expires_in !== 7200){
        }
        return $result->access_token;
    }

    /**
     * format
     */
    public function format($arrInfo, $arrField)
    {
        if(in_array('type_name', $arrField)){
            $arrInfo['type_name'] = ($arrInfo['type'] == 'view') ? '链接' : '关键字';
        }
        return $arrInfo;
    }

    /**
     * 字段信息
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'parent_id','name'=>'parent_id','type'=>'hidden'),
            array('title'=>'名称','name'=>'name','type'=>'text'),
            array('title'=>'类型','name'=>'type','type'=>'radio','data'=>array(
                array('title'=>'关键字','value'=>'click'),
                array('title'=>'链接','value'=>'view'),
            )),
            array('title'=>'菜单值','name'=>'value','type'=>'text'),
            array('title'=>'排序','name'=>'sort_order','type'=>'number'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),

        );
    }

}
