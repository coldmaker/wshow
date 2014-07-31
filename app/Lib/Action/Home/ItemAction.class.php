<?php
/**
 * 内容管理类
 * @author chen
 * @version 2014-03-11
 */
class ItemAction extends HomeAction
{

    /**
     * 内容列表
     */
    public function itemList()
    {
        $parent_id = intval($_GET['parent_id']);
        if(empty($parent_id)){$parent_id = 0;}

        $search = trim($_GET['search']);
        if(!empty($search)){
            $map['title'] = array('like', '%'.$search.'%');
        }
        $fields_all = D('Item')->field_list();
        $fields = array('id','title','intro','date_modify');
        $map['user_id'] = array('eq', $_SESSION['uid']);
        $map['parent_id'] = array('eq', $parent_id);
        $page = page(D('Item')->getCount($map));

        //获取文章列表
        $item_list = D('Item')->field($fields)->where($map)
            ->order('sort_order')->limit($page->firstRow, $page->listRows)->select();

        foreach($item_list as $k=>$v){
            $item_list[$k]['action_list'] = array(
                array('title'=>'管理子文章','type'=>'ls','url'=>U('Item/itemList',array('parent_id'=>$v['id']))),
                array('title'=>'编辑','type'=>'edit','url'=>U('Item/itemInfo',array('id'=>$v['id']))),
                array('title'=>'删除','type'=>'del','url'=>U('Item/del',array('id'=>$v['id']))),
            );
        }
        
        //模板赋值
        $fields[] = 'action_list';
        $btn_list = array(
            array('title'=>'添加文章','url'=>U('Item/itemInfo',array('parent_id'=>$parent_id)),'class'=>'primary'),
            array('title'=>'批量删除','url'=>U('Item/itemDel'),'class'=>'danger','type'=>'form'),
        );

        $data = array(
            'title'=>'文章列表',
            'form_url' => U('Item/itemInfo'),
            'btn_list'=> $btn_list,
            'field_list' => $this->get_field_list($fields_all, $fields),
            'field_info' => $item_list,
            'page_list' => $page->show(),
        );
        
        if(!empty($parent_id)){
            $ids = D('Item')->get_ids($parent_id);
            $ids = array_reverse($ids);
            foreach($ids as $k=>$v){
                $title = D('Item')->where('id='.$v)->getField('title');
                $bread_list[$k]['title'] = ($title) ? $title : '文章列表';
                $bread_list[$k]['url'] = U('Item/itemList',array('parent_id'=>$v));
            }
            $title = D('Item')->where('id='.$parent_id)->getField('title');
            $bread_list[] = array('title'=>$title,'url'=>'javascript:;','type'=>'current');
            $data['bread_list'] = $bread_list;
        }
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * 添加内容
     */
    public function itemInfo()
    {
        $itemObj = D('Item');
        if(empty($_POST)){
            $id = $this->_get('id', 'intval');
            $fields = array('id','parent_id','title','cover','intro','info','template_id','status');
            $fields[] = 'ext_info';
            if(!empty($id)){

                //更新显示
                $info = $itemObj->field()->where('id='.$id)->find();
                $this->assign('ext_url', U('Ext/extList',array('res_type'=>'item','res_id'=>$id)));
                $fields[] = 'ext_list';
            }else{

                //添加显示
                $info['parent_id'] = $this->_get('parent_id', 'intval');
                
            }

            $info['ext_info'] = D('Ext')->getExtVal($info['parent_id'], $id,'item');
            $this->assign('getExtValueList', U('Home/Ext/getExtValueList'));
            $fields_all = $itemObj->field_list();
            $ids = D('Item')->get_ids($info['parent_id']);
            $ids = array_reverse($ids);
            foreach($ids as $k=>$v){
                $title = D('Item')->where('id='.$v)->getField('title');
                $bread_list[$k]['title'] = ($title) ? $title : '文章列表';
                $bread_list[$k]['url'] = U('Item/itemList',array('parent_id'=>$v));
            }
            $title = D('Item')->where('id='.$info['parent_id'])->getField('title');
            if(!empty($title)){
                $bread_list[] = array('title'=>$title,'url'=>U('Item/itemList',array('parent_id'=>$info['parent_id'])));
            }
            $bread_list[] = array('title'=>$info['title'],'url'=>'javascript:;','type'=>'current');
            $data = array(
                'title'      => '文章详情',
                'bread_list' => $bread_list,
                'form_url'   => U('Item/itemInfo'),
                'field_info' => $info,
                'field_list' => $this->get_field_list($fields_all, $fields),
            );
            $this->assign($data);
            //$this->assign('extUrl', U('Home/Ext/extList'));
            $this->display('Public:info');
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        $item_id = $data['id'];
        if(empty($item_id)){
            //添加操作
            $data['user_id'] = $_SESSION['uid'];
            $data['date_add'] = time();
            if($item_id = $itemObj->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'添加失败'));
            }
        }else{
            //更新操作
            if($itemObj->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'更新失败'));
            }
        }
        //增值属性操作
        $ext_data = $_POST['ext'];
        D('Ext')->updateExtVal($ext_data, $item_id);
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
			echo json_encode(array('code'=>'0','msg'=>'请选择您要删除的数据'));
            exit;
		}
		$where['id'] = array('in', $delIds);
        $where['parent_id'] = array('in', $delIds);
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
		if(D('Item')->where($map)->delete()){
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
        }else{
            echo json_encode(array('code'=>'0','msg'=>'error'));
        }
    }
}
