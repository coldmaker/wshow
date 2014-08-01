<?php
/**
 * File Name: UserAction.class.php
 * Author: chen
 * Created Time: 2013-11-11 17:45:17
*/
class UserAction extends HomeAction{

	/**
	 * 会员列表
	 */
	public function userList(){
        $fields_all = D('User')->field_list();
        $fields = array('id','name','mobile','date_reg');
        $page = page(D('User')->getCount());

        $list = D('User')->field($fields)->order('date_reg desc')->limit($page->firstRow,$page->listRows)->select();
        $btn_list = array(
            array(
                'title' => '添加用户',
                'url'   => U('User/add'),
                'class' => 'primary',
            ),
        );
        $data = array(
            'title'      => '用户列表',
            'field_info' => $list,
            'field_list' => $this->get_field_list($fields_all, $fields),
            'btn_list'   => $btn_list,
            'page_list'  => $page->show(),
        );
		$this->assign($data);
		$this->display("Public:list");
	}

	/**
	 * 用户生成操作
	 */
	public function add(){
        $data = $this->_post();
        if(empty($data)){
            $this->display();
            exit;
        }
		$userObj = D('User');
		$password = mt_rand();
		$data['password'] = md5($password);
		$data['group_id'] = '2';
        $data['date_reg'] = $data['date_log'] = time();
		$userObj->add($data);
		echo ('用户名:'.$data['name']);
        echo ('<br>');
		echo ('密码:'.$password);
	}

	/**
	 * 会员登录后获取到的自身信息
	 */
	public function basic(){
		$data = $this->_post();
		$userObj = D('User');
        if(empty($data)){
		    $id = $_SESSION['uid'];
            $fields = array('id','avatar','name','mobile','address','email','token','appid','appsecrect','site_name','latitude','longitude','banner_id');
		    $userInfo = $userObj->field($fields)->where('id='.$id)->find();
            $fields_all = $userObj->field_list();
            //array_merge 合并数组
            $fields = array_merge($fields,array('banner_list','set_url','api_url'));
            $userInfo = $userObj->format($userInfo, array('api_url', 'avatar_name', 'set_url'));
            $tpl_data = array(
                'title'=>'基本信息',
                'form_url'=>U('User/basic'),
                'field_info'=>$userInfo,
                'field_list' => $this->get_field_list($fields_all, $fields),
            );
            $this->assign($tpl_data);
		    $this->display('Public:info');
            exit;
        }
		if(!empty($_FILES['pic']['name'])){
			$picList = uploadPic();
			if($picList['code'] != 'error'){
				$data['avatar'] = D('GalleryMeta')->addImg($picList['pic']['savename']);
			}
		}
        $result = $userObj->save($data);
        if(empty($result)){
            echo json_encode(array('code'=>'0','msg'=>'更新错误'));
        }else{
            $_SESSION['userInfo'] = D('User')->where('id='.$_SESSION['uid'])->find();
            file_put_contents('message.log','您的信息已更新');
            echo json_encode(array('code'=>'1','msg'=>'更新成功'));
        }
	}

    /**
     * 更新密码
     */
	public function password() {
        if(empty($_POST)){
            $tpl_data = array(
                'title'=>'修改密码',
                'form_url'=>U('User/password'),
                'field_list' => array(
                    array('title'=>'原始密码','name'=>'oldpwd','type'=>'password'),
                    array('title'=>'新密码','name'=>'newpwd','type'=>'password'),
                    array('title'=>'确认密码','name'=>'repwd','type'=>'password'),
                ),
            );
            $this->assign($tpl_data);
            $this->display('Public:info');
            exit;
        }
		$userObj = D('User');
		$map['id'] = $_SESSION['uid'];
		$map['password'] = md5($_POST['oldpwd']);
        if(empty($_POST['newpwd'])){
            echo json_encode(array('msg'=>'新密码不能为空'));
        }elseif($_POST['newpwd'] != $_POST['repwd']){
            echo json_encode(array('msg'=>'两次输入的密码不一致'));
        }elseif(!$userObj->where($map)->find()){
            echo json_encode(array('msg'=>'原始密码输入错误'));
		}else{
            $password = md5($_POST['newpwd']);
            if($userObj->where('id='.$_SESSION['uid'])->setField('password', $password)){
                echo json_encode(array('code'=>'1', 'msg'=>'密码修改成功'));
            }else{
                echo json_encode(array('msg'=>'密码修改失败'));
            }
         }
    }

    /**
     * 删除用户
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
		D('User')->where($map)->delete();
		$this->success('删除成功');
    }

    /*******************分组管理****************************/
    /**
     * group list
     */
    public function groupList()
    {
        $fields_all = D('UserGroup')->field_list();
        $groupList = $groupObj->getList($arrField, $arrMap, $arrOrder);
        foreach($groupList as $k=>$v){
            $groupList[$k]['count'] = D('User')->getCount(array('group_id'=>$v['id']));
        }
        $data = array(
            'groupList' => $groupList,
            'groupInfoUrl' => U('Home/User/groupInfo'),
        );
        $this->assign($data);
        $this->display();
    }

    /**
     * gruop info
     */
    public function groupInfo()
    {
        $groupObj = D('UserGroup');
        if(empty($_POST)){
            $group_id = $this->_get('group_id', 'intval');
            if(!empty($group_id)){
                $groupInfo = $groupObj->getInfoById($group_id);
                $this->assign('groupInfo', $groupInfo);
            }
            $this->assign('groupInfoUrl', U('Home/User/groupInfo'));
            $this->display();
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            $groupObj->add($data);
        }else{
            $groupObj->save($data);
        }
        $this->success('操作成功');
    }
}
