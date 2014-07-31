<?php
/**
 * File Name: ThemeAction.class.php
 * Author: chen
 * Created Time: 2013-11-21 13:49:38
*/
class ThemeAction extends HomeAction{
	/**
	 * 首页方法
	 */
	public function themeList(){
        $fields_all = D('Theme')->field_list();
        $fields = array('id','name','intro','date_modify');
        $page = page(D('Theme')->getCount());
        $list = D('Theme')->field($fields)->order('date_modify desc')->limit($page->firstRow,$page->listRows)->select();

        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('type'=>'ls','url'=>U('Theme/tplList',array('theme_id'=>$v['id']))),
                array('url'=>U('Theme/themeInfo',array('id'=>$v['id'])),'type' => 'edit'),
                array('url'=>U('Theme/themeDel',array('id'=>$v['id'])),'type' => 'del'),
            );
        }

        $btn_list = array(
            array(
                'title' =>'添加主题',
                'url'   =>U('Theme/themeInfo'),
                'class' =>'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('Theme/themeDel'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $fields[] = 'action_list';

        $data = array(
            'title'      => '主题列表',
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
        );
		$this->assign($data);
		$this->display('Public:list');
	}

	/**
	 * 使用主题
	 */
	public function toUse(){
		$siteObj = D('Setting');
		$id = intval($this->_get('id'));
        $arrMap['user_id'] = array('eq', $_SESSION['uid']);
		if($siteObj->where($arrMap)->setField('theme_id', $id)){
		}else{
		}
	}

    /**
     * 更新
     */
    public function themeInfo()
    {
        $themeObj = D('Theme');
        if(empty($_POST)){
            $fields_all = $themeObj->field_list();
            $fields = array('id','name','spell','intro');
            $id = intval($_GET['id']);
            $info = $themeObj->field($fields)->where('id='.$id)->find();
            $bread_list = array(
                array('title'=>'主题列表','url'=>U('Theme/themeList')),
            );
            $data = array(
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'bread_list' => $bread_list,
                'title'      => '主题信息',
                'form_url'   => U('Theme/themeInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            if($themeObj->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'添加失败'));
            }
        }else{
            if($themeObj->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'更新失败'));
            }
        }
    }

    /**
     * 删除
     */
    public function themeDel()
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
		D('Theme')->where($map)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
    }

    /*******************模版管理*******************/
    /**
     * template list
     */
    public function tplList()
    {
        $id = intval($_GET['theme_id']);
        $title = D('Theme')->where('id='.$id)->getField('name');
        $fields_all = D('ThemeTpl')->field_list();
        $fields = array('id','name','spell','sort_order','date_modify');

        $map = array('theme_id'=>$id);
        $page = page(D('ThemeTpl')->getCount($map));
        $list = D('ThemeTpl')
            ->field($fields)
            ->where($map)
            ->order('sort_order desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('type'=>'edit','url'=>U('Theme/tplInfo',array('id'=>$v['id']))),
                array('type'=>'del','url'=>U('Theme/tplDel',array('id'=>$v['id']))),
            );
        }
        $fields[] = 'action_list';
        $bread_list = array(
            array('title'=>'主题列表','url'=>U('Theme/themeList')),
            array('title'=>$title,'url'=>'javascript:;','type'=>'current'),
        );
        $btn_list = array(
            array('title'=>'添加模板','url'=>U('Theme/tplInfo',array('theme_id'=>$id)),'class'=>'primary'),
            array('title'=>'批量删除','url'=>U('Theme/tplDel'),'class'=>'danger','type'=>'form'),
        );
        $data = array(
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'bread_list' => $bread_list,
            'btn_list'   => $btn_list,
            'title'      => '模板列表',
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * add the new template
     */
    public function tplInfo()
    {
        $tplObj = D('ThemeTpl');
        if(empty($_POST)){
            $fields_all = $tplObj->field_list();
            $fields = array('id','theme_id','name','spell','sort_order');
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = $tplObj->field($fields)->where('id='.$id)->find();
            }else{
                $theme_id = intval($_GET['theme_id']);
                $info['theme_id'] = $theme_id;
            }
            $title = D('Theme')->where('id='.$info['theme_id'])->getField('name');
            $bread_list = array(
                array('title'=>'主题列表','url'=>U('Theme/themeList')),
                array('title'=>$title,'url'=>U('Theme/tplList',array('theme_id'=>$info['theme_id']))),
                array('title'=>$info['name'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'bread_list' => $bread_list,
                'title'      => '模板信息',
                'form_url'   => U('Theme/tplInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            if($tplObj->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('msg'=>'添加失败'));
            }
        }else{
            if($tplObj->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encoe(array('msg'=>'更新失败'));
            }
        }
    }

    /**
     * del the template
     */
    public function tplDel()
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
		D('ThemeTpl')->where($map)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
    }
}
 
