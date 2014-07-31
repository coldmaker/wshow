<?php
/**
 * Tab manage model
 * @author chen
 * @version 2014-03-29
 */
class TabAction extends HomeAction
{
    /**
     * set the breadcrumbs
     */
    private function setBCrumbs($id)
    {
        $result = $this->get_ids($id);
        $result = array_reverse($result);
        if(!empty($id)){
            $result = array_merge($result, array($id));
        }
        foreach($result as $k=>$v){
            $this->breadcrumbs[$k+1]['id'] = $v;
            $title = D('Tab')->where('id='.$v)->getField('title');
            $this->breadcrumbs[$k+1]['title'] = ($title) ? $title : '菜单列表';
            $this->breadcrumbs[$k+1]['url'] = U('Tab/tabList', array('parent_id'=>$v));
        }
    }

    /**
     * get parent_id for breadcrumbs
     */
    private function get_ids($id, $i=0)
    {
        $pid[$i] = D('Tab')->where('id='.$id)->getField('parent_id');
        if(!empty($pid[$i])){
            $pid = array_merge($pid, $this->get_ids($pid[$i],$i+1));
        }
        return $pid;
    }

    /**
     * Tab list
     */
    public function tabList()
    {
        $fields_all = D('Tab')->field_list();
        $fields = array('id','title','ico','url','sort_order','date_modify');
        $parent_id = intval($_GET['parent_id']);
        if(empty($parent_id)){$parent_id = 0;}
        $map = array('parent_id'=>$parent_id);
        $page = page(D('Tab')->getCount($map));

        $list = D('Tab')
            ->field($fields)
            ->where($map)
            ->order('sort_order')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        
        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('type'=>'ls','url'=>U('Tab/tabList',array('parent_id'=>$v['id']))),
                array('type'=>'edit','url'=>U('Tab/tabInfo',array('id'=>$v['id']))),
                array('type'=>'del','url'=>U('Tab/del',array('id'=>$v['id']))),
            );
        }
        $btn_list = array(
            array(
                'title' => '添加菜单',
                'url'   => U('Tab/tabInfo',array('parent_id'=>$parent_id)),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('Tab/del'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $fields[] = 'action_list';
        $data = array(
            'title' => '系统菜单列表',
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
        );
        if(!empty($parent_id)){
            $ids = D('Tab')->get_ids($parent_id);
            $ids = array_reverse($ids);
            foreach($ids as $k=>$v){
                $title = D('Tab')->where('id='.$v)->getField('title');
                $bread_list[$k]['title'] = ($title) ? $title : '菜单列表';
                $bread_list[$k]['url'] = U('Tab/tabList',array('parent_id'=>$v));
            }
            $title = D('Tab')->where('id='.$parent_id)->getField('title');
            $bread_list[] = array('title'=>$title,'url'=>'javascript:;','type'=>'current');
            $data['bread_list'] = $bread_list;
        }
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * tab info
     */
    public function tabInfo()
    {
        if(empty($_POST)){
            $fields_all = D('Tab')->field_list();
            $fields = array('id','parent_id','title','ico','url','sort_order','status');
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('Tab')->field($fields)->where('id='.$id)->find();
            }else{
                $parent_id = intval($_GET['parent_id']);
                $info['parent_id'] = $parent_id;
            }
            $ids = D('Tab')->get_ids($info['parent_id']);
            $ids = array_reverse($ids);
            foreach($ids as $k=>$v){
                $title = D('Tab')->where('id='.$v)->getField('title');
                $bread_list[$k]['title'] = ($title) ? $title : '菜单列表';
                $bread_list[$k]['url'] = U('Tab/tabList',array('parent_id'=>$v));
            }
            $title = D('Tab')->where('id='.$info['parent_id'])->getField('title');
            if(!empty($title)){
                $bread_list[] = array('title'=>$title,'url'=>U('Tab/tabList',array('parent_id'=>$info['parent_id'])));
            }
            $bread_list[] = array('title'=>$info['title'],'url'=>'javascript:;','type'=>'current');
            $data = array(
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'bread_list' => $bread_list,
                'title'      => '菜单信息',
                'form_url'   => U('Tab/tabInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            if(D('Tab')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'操作成功'));
            }else{
                echo json_encode(array('msg'=>'操作失败'));
            }
        }else{
            if(D('Tab')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'操作成功'));
            }else{
                echo json_encode(array('msg'=>'操作失败'));
            }
        }
    }

    /**
     * tab del
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
		$where['id'] = array('in', $delIds);
        $where['parent_id'] = array('in', $delIds);
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        if(D('Tab')->where($map)->delete()){
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
        }else{
            echo json_encode(array('msg'=>'删除失败'));
        }
    }
}
