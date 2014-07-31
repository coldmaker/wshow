<?php
/**
 *  RSS 订阅回复 控制器
 *  @author chen
 *  @version 2014-03-31
 */
class RssAction extends HomeAction
{
    /**
     * rss list
     */
    public function rssList()
    {
        $fields_all = D('WechatRss')->field_list();
        $fields = array('id','title','url','count','status','date_modify');
        $map = array('user_id'=>$_SESSION['uid']);
        $page = page(D('WechatRss')->getCount($map));
        $list = D('WechatRss')
            ->field($fields)
            ->where($map)
            ->order('date_modify desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        
        foreach($list as $k=>$v){
            $routeInfo = D('WechatRoute')->getRoute('rss', $v['id']);
            $list[$k]['keyword'] = $routeInfo['keyword'];
            $list[$k]['action_list'] = array(
                array('url'=>U('Rss/rssInfo',array('id'=>$v['id'])),'type'=>'edit'),
                array('url'=>U('Rss/del',array('id'=>$v['id'])),'type'=>'del'),
            );
        }
        $fields[] = 'action_list';
        $fields[] = 'keyword';
        $btn_list = array(
            array(
                'title' => '添加订阅回复',
                'url'   => U('Rss/rssInfo'),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('Rss/del'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $data = array(
            'title' => 'RSS订阅回复',
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'btn_list'   => $btn_list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * rssInfo
     */
    public function rssInfo()
    {
        if(empty($_POST)){
            $fields_all = D('WechatRss')->field_list();
            $fields = array('id','title','url','count','status');
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('WechatRss')->field($fields)->where('id='.$id)->find();
                $route_info = D('WechatRoute')->getRoute('rss',$id);
                $info = array_merge($info,$route_info);
            }
            $fields[] = 'keyword';
            $bread_list = array(
                array('title'=>'订阅回复列表','url'=>U('Rss/rssList')),
                array('title'=>$info['title'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title' => '订阅回复信息',
                'bread_list' => $bread_list,
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'form_url'   => U('Rss/rssInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        if(!is_numeric($data['count'])){
            echo json_encode(array('msg'=>'请输入数字'));exit;
        }elseif(($data['count'] < 1) OR ($data['count'] > 8)){
            echo json_encode(array('msg'=>'数字应在1-8之间'));exit;
        }
        if(!D('WechatRoute')->checkKeyword($data['keyword'], $data['id'])){
            echo json_encode(array('msg'=>'关键字不可用'));exit;
        }
        $data['date_modify'] = time();
        $data['url'] = htmlspecialchars_decode($data['url']);
        if(empty($data['id'])){
            $data['user_id'] = $_SESSION['uid'];
            $data['date_add'] = time();
            $obj_id = D('WechatRss')->add($data);
        }else{
            D('WechatRss')->save($data);
            $obj_id = $data['id'];
        }
        if(!empty($obj_id)){
            D('WechatRoute')->updateRoute('rss', $obj_id, $data);
            echo json_encode(array('code'=>'1','msg'=>'操作成功'));
        }else{
            echo json_encode(array('msg'=>'操作失败'));
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        $delIds = array();
		$postIds = $this->_post('id');
		if (!empty($postIds)) {
			$delIds = $postIds;
		}
		$getId = intval($this->_get('id'));
		if (!empty($getId)) {
			$delIds[] = $getId;
		}
		if (empty($delIds)) {
            echo json_encode(array('msg'=>'请选择您要删除的数据'));
            exit;
		}
		$map['id'] = $routeMap['obj_id'] = array('in', $delIds);
        D('WechatRoute')->delRoute('rss', $routeMap);
        D('WechatRss')->where($map)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
    }
}
