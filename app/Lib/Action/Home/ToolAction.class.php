<?php
/**
 * 微应用操作类
 * @author chen
 * @version 2013-12-27
 */
class ToolAction extends HomeAction {
    /**
     * 获取应用列表
     */
    public function toolList(){
        $fields_all = D('WechatTool')->field_list();
        $fields = array('id','name','intro','date_modify');
        $map = array();
        if($_SESSION['uid'] != 1){
            $map['status'] = array('eq', 1);
        }
        $page = page(D('WechatTool')->getCount($map));

        $list = D('WechatTool')
            ->field($fields)
            ->where($map)
            ->order('sort_order desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
        
        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('url'=>U('Tool/toolInfo',array('id'=>$v['id'])),'type'=>'edit'),
                array('url'=>U('Tool/del',array('id'=>$v['id'])),'type'=>'del'),
            );
        }
        $btn_list = array(
            array('title'=>'添加工具','class'=>'primary','url'=>U('Tool/toolInfo')),
            array('title'=>'批量删除','class'=>'danger','url'=>U('Tool/del'),'type'=>'form'),
        );
        $fields[] = 'action_list';
        $data = array(
            'title'      => '工具列表',
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * toolInfo
     */
    public function toolInfo()
    {
        $fields = array('id','name','intro','function','status','sort_order');
        if(empty($_POST)){
            $fields_all = D('WechatTool')->field_list();
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('WechatTool')->field($fields)->where('id='.$id)->find();
            }
            $bread_list = array(
                array('title'=>'工具列表','url'=>U('Tool/toolList')),
                array('title'=>$info['name'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title' => '工具信息',
                'bread_list' => $bread_list,
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'form_url'   => U('Tool/toolInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            if(D('WechatTool')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('msg'=>'添加失败'));
            }
        }else{
            if(D('WechatTool')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('msg'=>'更新失败'));
            }
        }
    }

    /**
     * 使用微应用
     */
    public function useTool(){
        $toolObj = D('WechatTool');
        $routeObj = D('WechatRoute');
        $id = intval($this->_get('id'));
        $tool_name = $toolObj->where('id='.$id)->getField('name');
        $route_id = $routeObj->where("user_id=".$_SESSION['uid']." AND keyword='".$tool_name."'")->getField('id');
        if(!empty($route_id)){
            $result = $routeObj->where('id='.$route_id)->delete();
        }else{
            $result = $routeObj->updateRoute('tool', $id, array('keyword'=>$tool_name));
        }
        $this->success('操作成功');
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
		$map['id'] = array('in', $delIds);
		D('WechatTool')->where($map)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
    }

}
