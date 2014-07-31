<?php
/**
 * ext action model
 * @author chen
 * @version 2014-03-20
 */
class ExtAction extends HomeAction
{
    /**
     * ext list
     */
    public function extList()
    {
        $ext_obj = D('Ext');
        $res_type = trim($_REQUEST['res_type']);
        $res_id = intval($_REQUEST['res_id']);

        $fields_all = $ext_obj->field_list();
        $fields = array('id','title','type','sort_order');
        $map = array('res_type'=>$res_type,'res_id'=>$res_id);

        $ext_list = $ext_obj->field($fields)->where($map)->order('sort_order')->select();

        foreach($ext_list as $k=>$v){
            $ext_list[$k]['action_list'] = array(
                array('type'=>'edit','url'=>U('Ext/extInfo',array('res_type'=>$res_type,'res_id'=>$res_id))),
                array('type'=>'del','url'=>U('Ext/del',array('res_type'=>$res_type,'res_id'=>$v['id']))),
            );
        }
        $fields[] = 'action_list';
        $data = array(
            'res_type'   => $res_type,
            'res_id'     => $res_id,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $ext_list,
        );
        $this->assign($data);
        $this->display('Public:ext_list');
    }

    /**
     * ext info
     */
    public function extInfo()
    {
        $extObj = D('Ext');
        if(empty($_POST)){
            $id = $this->_get('id', 'intval');
            if(!empty($id)){
                $extInfo = $extObj->getInfoById($id);
                $this->assign('extInfo', $extInfo);
                $res_type = $extInfo['res_type'];
                $res_id = $extInfo['res_id'];
            }else{
                $res_type = $this->_get('res_type');
                $res_id = $this->_get('res_id');
            }
            $this->assign('res_type', $res_type);
            $this->assign('res_id', $res_id);
            $this->assign('infoUrl', U('Home/Ext/extInfo'));
            $this->display();
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            $result = $extObj->add($data);
        }else{
            $extObj->save($data);
            $result = $data['id'];
        }
        echo $result;
    }

    /**
     * get ext value list
     */
    public function getExtValueList()
    {
        $extObj = D('Ext');
        $res_type = trim($_REQUEST['res_type']);
        $res_id = intval($_REQUEST['res_id']);
        $res_sub_id = intval($_REQUEST['res_sub_id']);
        $arrField = array();
        $arrMap['res_type'] = array('eq', $res_type);
        $arrMap['res_id'] = array('eq', $res_id);
        $arrOrder = array('sort_order');
        $extList = $extObj->getList($arrField, $arrMap, $arrOrder);
        foreach($extList as $k=>$v){
            $valInfo = D('ExtVal')->where('ext_id='.$v['id'].' AND res_id='.$res_sub_id)->find();
            $extList[$k]['val_id'] = $valInfo['id'];
            $extList[$k]['value'] = $valInfo['value'];
        }
        $this->assign('extList', $extList);
        $this->display();
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
			$this->error('请选择您要删除的数据');
		}
		$map['id'] = array('in', $delIds);
		D('Ext')->where($map)->delete();
		$this->success('删除成功');
    }
}
