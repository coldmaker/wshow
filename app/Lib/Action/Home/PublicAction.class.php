<?php
/**
 * 后台-首页
 * @author chen
 * @version 2014-03-14
 */
class PublicAction extends BaseAction
{
    /**
     * 页面：后台登陆
     */
    public function login()
    {
        $this->display();
    }


    /**
     * 处理：后台登陆
     */
    public function doLogin()
    {
        $name = $_POST['name'];
        $pwd = $_POST['password'];
		$userInfo = D('User')->where("name='".$name."' AND password='".md5($pwd)."'")->find();
		if(empty($userInfo)){
            echo json_encode(array('code'=>'0', 'msg'=>'用户名或密码错误'));exit;
        }
        $this->setSession($userInfo['id']);
        echo json_encode(array('code'=>'1', 'msg'=>'登录成功'));
    }

    /**
     * 首次登录后的SESSION处理工作
     */
    private function setSession($id){
        $_SESSION['uid'] = $id;
        $userInfo = D('User')->getInfoById($id);
        $userInfo = D('User')->format($userInfo, array('avatar_name'));
        $_SESSION['userInfo'] = $userInfo;
        $_SESSION['current_ip'] = get_client_ip();
        $_SESSION['current_time'] = time();
    }

    /**
     * 用户登出
     */
    public function logout() {
        $url = U('Home/Public/login');
        //存储此次用户的登录信息
		$update = array(
			'last_time' => $_SESSION['current_time'],
			'last_ip' => $_SESSION['current_ip'],
		);
		D('User')->where('id='.$_SESSION['uid'])->save($update);
        unset($_SESSION);
		session_destroy();
        $this->success('登出成功！', $url);
    }

    /**
     * 推送
     */
    public function sendMsg(){
        echo $info;
    }

}
