<?php
/**
 * 前台公共控制器类
 * @author chen
 * @version 2014-03-11
 */
class HomeAction extends BaseAction
{
    public $breadcrumbs;
    /**
     * initialize
     */
    public function _initialize()
    {
        if(!isset($_SESSION['uid'])){
            $this->redirect('Public/login');
        }
        //session_destroy();
        $this->breadcrumbs[] = array(
            'title' => '首页',
            'url' => U('Home/Index/index'),
        );
        $this->assign('channel', $this->_getChannel());
        $this->assign('menu',    $this->_getMenu());
        $this->checkLoa();
    }

    /**
     * check user primission
     */
    private function checkLoa()
    {
        $group_id = $_SESSION['userInfo']['group_id'];
        $arrAction = __ACTION__;
        $result = explode('/', $arrAction);
        $map['module'] = array('eq', $result['3']);
        $map['action'] = array('eq', $result['4']);
        $loa_id = D('Loa')->where($map)->getField('id');
        if(!empty($loa_id)){
            if(!D('LoaGroup')->where('group_id='.$_SESSION['userInfo']['group_id'].' AND loa_id='.$loa_id)->find()){
                $this->error('对不起，您没有权限执行此操作!');
                exit;
            }
        }
    }
   /**
     * 头部菜单
     */
    protected function _getChannel() {
        $arrList = array();
        $tabList = D('Tab')->getTabList();
        foreach($tabList as $k=>$v){
            $arrList[$v['tag']] = $v['title'];
        }
        return $arrList;
    }

    /**
     * 左侧菜单
     */
    protected function _getMenu() {
        $tabObj = D('Tab');
        $menu = $tabObj->getTabList();
        foreach($menu as $k=>$v){
            $menu[$k]['menu'] = $tabObj->getTabList($v['id']);
            foreach($menu[$k]['menu'] as $k2=>$v2){
                $menu[$k]['menu'][$k2]['meta'] = $tabObj->getTabList($v2['id']);
                foreach($menu[$k]['menu'][$k2]['meta'] as $k3=>$v3){
                    $menu[$k]['menu'][$k2]['meta'][$k3]['url'] = U($v3['url']);
                }
            }
        }
        return $menu;
        /*
        $first_list = D('Tab')->getTabList();
        foreach($first_list as $k=>$v){
            $second_list = D('Tab')->getTabList($v['id']);
            foreach($second_list as $k2=>$v2){
                $third_list = D('Tab')->getTabList($v2['id']);
                foreach($third_list as $k3=>$v3){
                    $third_real_list[$v3['title']] = U($v3['url']);
                    $second_real_list[$v2['title']] = $third_real_list;
                    }
                $third_real_list = array();
            }
            $menu[$v['tag']] = $second_real_list;
            $second_real_list = array();
        }
        return $menu;
        */
    }

    /**
     * 获取字段属性列表 
     * @param array field_list 全部属性列表
     * @param array list 选定属性列表
     */
    public function get_field_list($field_list, $list)
    {
        $result = array();
        foreach($field_list as $k=>$v){
            if(in_array($v['name'], $list)){
                $result[] = $v;
            }
        }
        return $result;
    }
}
