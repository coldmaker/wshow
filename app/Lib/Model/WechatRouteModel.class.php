<?php
/**
 * 全局资源路由表
 * @author blue
 * @version 2013-12-24
 */
class WechatRouteModel extends CommonModel{
    /**
     * 判断关键字是否被占用
     * @return boolen 如何关键字已经被占用，则返回false，否则返回true
     * @keyword string $keyword 关键字
     */
    public function checkKeyword($keyword, $id=0){
        //$keyword = mb_convert_encoding($keyword, 'utf8', 'gbk');
        if(empty($keyword)){
            return true;
            exit;
        }
        $keywordList = D('WechatRoute')->where("obj_type='common'")->getField('keyword' ,true);
        if(in_array($keyword, $keywordList)){
            return false;
            exit;
        }
        $arrMap['keyword'] = array('eq', $keyword);
        $resultId = D('WechatRoute')->where($arrMap)->getField('obj_id');
        if(empty($resultId)){
            return true;
        }elseif($resultId == $id){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取关键字
     * @return string $keyword 关键字
     * @param string $obj_type 资源类型
     * @param int $obj_id 资源ID
     */
    public function getRoute($obj_type, $obj_id){
        $routeObj = D('WechatRoute');
        $arrMap = array(
            'user_id' => $_SESSION['uid'],
            'obj_type' => $obj_type,
            'obj_id'   => $obj_id,
        );
        $routeInfo = $routeObj->where($arrMap)->find();
        return $routeInfo;
    }

    /**
     * update the route info
     * add and edit action
     */
    public function updateRoute($obj_type, $obj_id, $route)
    {
        $routeObj = D('WechatRoute');
        $route['user_id'] = $_SESSION['uid'];
        $route['obj_type'] = $obj_type;
        $route['obj_id'] = $obj_id;
        $route['date_modify'] = time();
        if(empty($route['id'])){
            $route['date_add'] = time();
            $routeObj->add($route);
        }else{
            $routeObj->save($route);
        }
    }


    /**
     * 路由表更新
     * @return boolen
     * @param string $obj_type 资源类型
     * @param int $obj_id 资源ID
     * @param string $keyword 关键字
     */
    public function editRoute($update){
        $routeObj = D('WechatRoute');
        $update = array(
            'id'        => $update['id'],
            'keyword'   => $update['keyword'],
            'date_modify'     => time(),
        );
        $routeObj->save($update);
    }

    /**
     * 删除路由表记录
     * @return boolen
     * @param string $obj_type 资源类型
     * @param array $map 资源ID数组
     */
    public function delRoute($obj_type, $map){
        $routeObj = D('WechatRoute');
        $map['user_id'] = $_SESSION['uid'];
        $map['obj_type'] = $obj_type;
        $routeObj->where($map)->delete();
    }

    /**
     * file list
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'关键字','name'=>'keyword','type'=>'text'),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

}
