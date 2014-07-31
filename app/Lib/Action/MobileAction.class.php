<?php
/**
 * File Name: HomeCommonAction.class.php
 * Author: Blue
 * Created Time: 2013-11-15 10:59:09
*/
class MobileAction extends BaseAction
{
    private $relTplList;
	/**
	 * 判断用户
	 */
    public function _initialize()
    {
        if(!isset($_SESSION['m_user_id']) OR ($_SESSION['m_user'] !== $_GET['user'])){

            //设置m_user,m_user_id,m_theme
            $_SESSION['m_user'] = $_GET['user'];
            $_SESSION['m_user_id'] = D('User')->where("name='".$_GET['user']."'")->getField('id');
            $_SESSION['m_theme'] = $this->getThemeName();
            if(!empty($_GET['member_id'])){

                //设置m_member_id
                $_SESSION['m_member_id'] = $_GET['member_id'];
            }else{
                $_SESSION['m_member_id'] = '0';
            }
        }
        if(empty($_SESSION['m_member_id'])){
            if(!empty($_GET['member_id'])){
                //设置m_member_id
                $_SESSION['m_member_id'] = $_GET['member_id'];
            }
        }

        /* 模版的定制优先原则 */
        $this->relTplList = scandir('./app/Tpl/Mobile/'.ucfirst($_SESSION['m_theme']));

        $list_data = $this->getItemList();
        $data = array(
            'site'          => $this->getSiteInfo(),
            'menuList'      => $list_data['list'],
            'home'          => U('Mobile/Index/index', array('user'=>$_SESSION['m_user'])),
        );
        $this->assign($data);
	}

    /**
     * 获取网站设置信息
     * @return array $siteInfo 网站设置信息
     */
    protected function getSiteInfo()
    {
        $siteInfo = D('Setting')->where('user_id='.$_SESSION['m_user_id'])->find();
        $siteInfo['logo'] = getPicPath(D('GalleryMeta')->getImg($siteInfo['logo'], 'm'));
        $siteInfo = D('Setting')->format($siteInfo, array('theme_spell'));
        return $siteInfo;
    }

    /**
     * 获取栏目信息
     * @param int $id
     * @return array $itemInfo
     */
    protected function getItemInfo($id)
    {
		$itemInfo = D('Item')->where('id='.$id)->find();

        /** 使用接口 **/
        if(!empty($itemInfo['api'])){
            $this->redirect('Index/api', array('user'=>$_SESSION['m_user'],'id'=>$id));exit;
        }

        $itemInfo = D('Item')->format($itemInfo, array('template_name', 'ext'));
        $itemInfo['cover_name'] = getPicPath(D('GalleryMeta')->getImg($itemInfo['cover']), 'b');
        $itemInfo['date_add_text'] = date('Y-m-d H:i', $itemInfo['date_add']);
        $itemInfo['info'] = htmlspecialchars_decode($itemInfo['info']);
        return $itemInfo;
    }

    /**
     * 获取栏目列表
     * @param int $fid 父级栏目ID
     * @return array $catList 栏目列表
     */
    protected function getItemList($parent_id=0)
    {
        $map = array(
            'parent_id' => $parent_id,
            'user_id' => $_SESSION['m_user_id'],
            'status' => 1,
        );

        //分页
        $page = page(D('Item')->getCount($map), 15, 'simple');

        //获取列表
        $itemList = D('Item')->where($map)->order('sort_order')->limit($page->firstRow, $page->listRows)->select();

        foreach($itemList as $k=>$v){
            $itemList[$k] = D('Item')->format($v, array('ext'));
            $itemList[$k]['cover_name'] = getPicPath(D('GalleryMeta')->getImg($v['cover']));
            $itemList[$k]['url'] = U('Mobile/Index/item', array('user'=>$_SESSION['m_user'], 'id'=>$v['id']));
        }
        return array('list'=>$itemList,'page'=>$page->show());
    }

    /**
     * Get the Thene name
     * return string $themeName 主题名称
     */
    protected function getThemeName()
    {
        $theme_id = D('Setting')->where('user_id='.$_SESSION['m_user_id'])->getField('theme_id');
        $theme_name = D('Theme')->where('id='.$theme_id)->getField('spell');
        return ($theme_name) ? $theme_name : 'default';
    }

    /**
     * Get the gallery img list
     */
    protected function getImgList($gallery_id)
    {
        $imgList = D('GalleryMeta')->where('gallery_id='.$gallery_id)->select();
        foreach($imgList as $k=>$v){
            $imgList[$k]['path_name'] = getPicPath($v['path'], 'b');
        }
        return $imgList;
    }

    /**
     * get nav template
     */
    protected function getNav()
    {
        if(in_array('navigation.html', $this->relTplList)){
            $nav = ucfirst($_SESSION['m_theme']).':navigation';
        }else{
            $nav = 'Public:navigation';
        }
        return $nav;
    }

    /**
     * get theme dir
     */
    protected function getRelTpl($tplName)
    {
        if(in_array($tplName.'.html', $this->relTplList)){
            $themeDir = ucfirst($_SESSION['m_theme']);
        }else{
            $themeDir = 'Default';
        }
        return $themeDir.':'.$tplName;
    }

    /**
     * getApiData
     */
    protected function getApiInfo($url, $article_id){
        if(!empty($article_id)){
            $url .= '&article_id='.$article_id;
        }
        $info = $this->getCUrl($url);
        return $info;
    }

    protected function getApiList($url, $article_id, $count, $item_id){
        $page = page($count, 10, 'simple');
        $url .= '&article_id='.$article_id.'&type=list&start='.$page->firstRow.'&length='.$page->listRows;
        $list = $this->getCUrl($url);
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['url'] = U('Index/api', array(
                    'user'=>$_SESSION['m_user'],
                    'member_id'=>$member_id,
                    'id'=>$item_id,
                    'article_id'=>$v['id']
                ));
            }
        }
        return array('list'=>$list,'page'=>$page->show());
    }

    private function getCUrl($url){
        if(empty($url)){return 0;exit;}
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $info = json_decode($result, true);
        return $info;
    }
}
