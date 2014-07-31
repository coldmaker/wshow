<?php
/**
 * 微信功能控制器
 * @author chen
 * @version 2014-03-19
 */
class WechatAction extends HomeAction
{
    /**
     * 模拟函数
     */
    public function sim()
    {
        if(empty($_POST)){
            $this->display('Public:sim');
            exit;
        }
        $url = $_POST['url'];
        $xml = $_POST['info'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
        echo json_encode(array('result'=>htmlspecialchars($result)));
    }

    /**
     * 禁忌关键字管理
     */
    public function keywordList()
    {
        $fields_all = D('WechatRoute')->field_list();
        $fields = array('id','keyword','date_modify');
        $map = array('obj_type'=>'common');
        $page = page(D('WechatRoute')->getCount($map));
        
        $list = D('WechatRoute')
            ->field($fields)
            ->where($map)
            ->order('date_modify desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('type'=>'edit','url'=>U('Wechat/keywordInfo',array('id'=>$v['id']))),
                array('type'=>'del','url'=>U('Wechat/keywordDel',array('id'=>$v['id']))),
            );
        }

        $btn_list = array(
            array(
                'title' => '添加系统关键字',
                'url'   => U('Wechat/keywordInfo'),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('Wechat/keywordDel'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $fields[] = 'action_list';
        $data = array(
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'title'      => '系统关键字列表',
            'btn_list'   => $btn_list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * 关键字信息
     */
    public function keywordInfo()
    {
        if(empty($_POST)){
            $fields_all = D('WechatRoute')->field_list();
            $fields = array('id','keyword');
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('WechatRoute')->field($fields)->where('id='.$id)->find();
            }
            $bread_list = array(
                array('title'=>'关键字列表','url'=>U('Wechat/keywordList')),
                array('title'=>$info['keyword'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'bread_list' => $bread_list,
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'title'      => '关键字信息',
                'form_url'   => U('Wechat/keywordInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['obj_type'] = 'common';
            $data['obj_id'] = '0';
            $data['date_add'] = time();
            if(D('WechatRoute')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('msg'=>'添加失败'));
            }
        }else{
            if(D('WechatRoute')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('msg'=>'更新失败'));
            }
        }
    }

    /**
     * 关键字删除
     */
    public function keywordDel()
    {
		$routeObj = D('WechatRoute');
        //数据
        $delIds = array();
        $postIds = $this->_post('id');
        if (!empty($postIds)) {
            $delIds = $postIds;
        }
        $getId = intval($this->_get('id'));
        if (!empty($getId)) {
            $delIds[] = $getId;
        }
        //删除数据
        if (empty($delIds)) {
            echo json_encode(array('msg'=>'请选择您要删除的数据'));
            exit;
        }
		$arrMap['id'] = array('in', $delIds);
		$routeObj->where($arrMap)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
    }

}
