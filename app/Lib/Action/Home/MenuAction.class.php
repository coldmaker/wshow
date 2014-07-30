<?php
/**
 * File Name: MenuAction.class.php
 * Author: chen
 * Created Time: 2013-11-9 16:43:33
*/
class MenuAction extends HomeAction{
    /**
     * get menu list 
     */
    private function getMenuList($parent_id=0)
    {
        $arrField = array('*');
        $arrMap['user_id'] = array('eq', $_SESSION['uid']);
        $arrMap['parent_id'] = array('eq', $parent_id);
        $arrOrder = array('sort_order');
        $menuList = D('WechatMenu')->getList($arrField, $arrMap, $arrOrder);
        foreach($menuList as $k=>$v){
            $menuList[$k] = D('WechatMenu')->format($v, array('type_name'));
        }
        return $menuList;
    }

	/**
	 * 菜单列表
	 */
	public function menuList(){
        $fields_all = D('WechatMenu')->field_list();
        $fields = array('id','name','type','value','sort_order','date_modify');
        $parent_id = intval($_GET['parent_id']);
        $map = array('user_id'=>$_SESSION['uid'],'parent_id'=>$parent_id);
        $page = page(D('WechatMenu')->getCount($map));
        $list = D('WechatMenu')
            ->field($fields)
            ->where($map)
            ->order('sort_order desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('url'=>U('Menu/menuInfo',array('id'=>$v['id'])),'type'=>'edit'),
                array('url'=>U('Menu/del',array('id'=>$v['id'])),'type'=>'del'),
            );
            if(empty($parent_id)){
                $list[$k]['action_list'] = array_merge(array(array('url'=>U('Menu/menuList',array('parent_id'=>$v['id'])),'type'=>'ls')),$list[$k]['action_list']);
            }
        }
        $fields[] = 'action_list';
        $btn_list = array(
            array(
                'title' => '批量删除',
                'url'   => U('Menu/menuInfo'),
                'class' => 'danger',
                'type'  => 'form',
            ),
            array(
                'title' => '添加菜单',
                'url'   => U('Menu/menuInfo',array('parent_id'=>$parent_id)),
                'class' => 'primary',
            ),            
        );
        if(empty($parent_id)){
            $btn_list = array_merge($btn_list,array(
            array(
                'title' => '同步菜单',
                'url'   => U('Menu/createMenu'),
                'class' => 'default',
            ),
            ));
        }
        $data = array(
            'title'=>'菜单设置',
            'btn_list' => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
        );
        if(!empty($parent_id)){
            $title = D('WechatMenu')->where('id='.$parent_id)->getField('name');
            $bread_list = array(
                array('title'=>'菜单列表','url'=>U('MenuList')),
                array('title'=>$title,'url'=>'javascript:;','type'=>'current'),
            );            
            $data['bread_list'] = $bread_list;
        }
        $this->assign($data);
        $this->display('Public:list');
	}

	/**
	 * 页面：添加菜单
	 */
	public function menuInfo(){
        if(empty($_POST)){
            $fields_all = D('WechatMenu')->field_list();
            $fields = array('id','parent_id','name','type','value','sort_order');
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('WechatMenu')->field($fields)->where('id='.$id)->find();
            }else{
                $parent_id = intval($_GET['parent_id']);
                $info['parent_id'] = $parent_id;
            }
            $title = D('WechatMenu')->where('id='.$info['parent_id'])->getField('name');
            $bread_list = array(
                array('title'=>'菜单列表','url'=>U('Menu/menuList')),
                array('title'=>$title,'url'=>U('Menu/menuList',array('parent_id'=>$info['parent_id']))),
                array('title'=>$info['name'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title' => '菜单信息',
                'bread_list' => $bread_list,
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'form_url'   => U('Menu/menuInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['user_id'] = $_SESSION['uid'];
            $data['date_add'] = time();
            if(D('WechatMenu')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'添加成功'));
            }else{
                echo json_encode(array('msg'=>'添加失败'));
            }
        }else{
            if(D('WechatMenu')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('msg'=>'更新失败'));
            }
        }
	}

	/**
	 * 操作：删除菜单
	 */
	public function del(){
        $id = intval($this->_get('id'));
        $menuList = D('WechatMenu')->where('parent_id='.$id)->select();
        if(!empty($menuList)){
            foreach($menuList as $k=>$v){
                D('WechatMenu')->where('id='.$v['id'])->delete();
            }
        }
        D('WechatMenu')->where('id='.$id)->delete();
        echo json_encode(array('code'=>'1','msg'=>'删除成功'));
	}

    /**
     * 下载菜单
     */
    public function downMenu(){
        $token = D('WechatMenu')->getToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
		$ch = curl_init();//初始化curl
		curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		//将获取到的内容json解码为数组
		$menuList = json_decode($data, true);
        foreach($menuList['menu']['button'] as $k=>$v){
            $insert = array();
            $insert['parent_id'] = '0';
            $insert['user_id'] = $_SESSION['uid'];
            $insert['name'] = $v['name'];
            $insert['type'] = ($v['type']) ? $v['type'] : '0';
            $insert['value'] = ($v['url'].$v['key']) ? ($v['url'].$v['key']) : '0';
            $insert['data_modify'] = time();
            $id = D('WechatMenu')->add($insert);
            if(isset($v['sub_button'])){
                foreach($v['sub_button'] as $k2=>$v2){
                    $insert = array();
                    $insert['parent_id'] = $id;
                    $insert['user_id'] = $_SESSION['uid'];
                    $insert['name'] = $v2['name'];
                    $insert['type'] = $v2['type'];
                    $insert['value'] = ($v2['url']) ? $v2['url'] : $v2['key'];
                    $insert['date_modify'] = time();
                    D('WechatMenu')->add($insert);
                }
            }
            $url = U('Home/Menu/menuList');
            $this->success('更新菜单成功', $url);
        }
    }


	/**
	 * 操作：添加菜单
	 */
	public function createMenu(){
		$token = D('WechatMenu')->getToken();
        if(empty($token)){
            echo json_encode(array('msg'=>'获取TOKEN失败'));
            exit;
        }
		//以post方式发送菜单内容给微信服务器
		//$json = $this->array_utf8_encode_recursive($_SESSION['menuInfo']);
        $menuObj = D('WechatMenu');
        $arrField = array('id, parent_id, name, type, value');
        $arrMap['user_id'] = array('eq', $_SESSION['uid']);
        $arrMap['parent_id'] = array('eq', 0);
        //判断是否需要下载最新菜单
        $count = $menuObj->getCount($arrMap);
        if(empty($count)){
            $this->redirect('Menu/downMenu');
            exit;
        }
        $arrOrder = array('sort_order');
        $menuList = $menuObj->getList($arrField, $arrMap, $arrOrder);
        foreach($menuList as $k=>$v){
            $newList[$k]['name'] = urlencode($v['name']);
            $newList[$k]['type'] = $v['type'];
            if($v['type'] == 'view'){
                $newList[$k]['url'] = $v['value'];
            }else{
                $newList[$k]['key'] = urlencode($v['value']);
            }
            //获取子菜单
            $subMenuList = $menuObj->where('parent_id='.$v['id'])->select();
            $newSubList = array();
            if(!empty($subMenuList)){
            foreach($subMenuList as $k2=>$v2){
                $newSubList[$k2]['name'] = urlencode($v2['name']);
                $newSubList[$k2]['type'] = $v2['type'];
                if($v2['type'] == 'view'){
                    $newSubList[$k2]['url'] = $v2['value'];
                }else{
                    $newSubList[$k2]['key'] = urlencode($v2['value']);
                }
            }
            }
            $newList[$k]['sub_button'] = $newSubList;
        }
        $menuList = array('button'=>$newList);
        $json = json_encode($menuList);
		$json = urldecode($json);
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, 1);//发送一个post请求
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);//post提交的数据包
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置限制时间
		curl_setopt($ch, CURLOPT_HEADER, 0);//显示返回的header区域内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//以文件流的形式获取
		$result = curl_exec($ch);
		curl_close($ch);
        $result = json_decode($result, true);
		if($result['errcode'] === 0){
            echo json_encode(array('code'=>'1','msg'=>'菜单更新成功'));
        }else{
            echo json_encode(array('msg'=>'菜单更新失败'));
        }
 	}
}
