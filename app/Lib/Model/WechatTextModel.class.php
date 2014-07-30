<?php
/**
 * File Name: TextModel.class.php
 * Author: Blue
 * Created Time: 2013-11-16 14:21:30
*/
class WechatTextModel extends CommonModel{
	/**
	 * 首页方法
	 */
	public function format($arrInfo, $arrFormatField){
        if(in_array('keyword', $arrFormatField)){
            $routeInfo = D('WechatRoute')->getRoute('text', $arrInfo['id']);
            $arrInfo['keyword'] = $routeInfo['keyword'];
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
            array('title'=>'user_id','name'=>'user_id','type'=>'hidden'),
            array('title'=>'route_id','name'=>'route_id','type'=>'hidden'),
            array('title'=>'关键字','name'=>'keyword','type'=>'text'),
            array('title'=>'内容','name'=>'content','type'=>'content'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

    /**
     * update the text info
     */
    public function updateText($text)
    {
        $textObj = D('WechatText');
        $text['date_modify'] = time();
        $id = $text['id'];
        if(empty($id)){
            $text['user_id']  = $_SESSION['uid'];
            $text['date_add'] = time();
            $id = $textObj->add($text);
        }else{
            $textObj->save($text);
        }
        return $id;
    }


}
