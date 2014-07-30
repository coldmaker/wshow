<?php
/**
 * 微网站统一控制器
 * @author chen
 * @version 2014-03-03
 */
class IndexAction extends MobileAction
{
    /**
     * 首页控制函数
     */
    public function index()
    {
        $siteInfo = $this->getSiteInfo();
        $this->assign('bannerList', $this->getImgList($siteInfo['banner_id'])); $this->display(ucfirst($_SESSION['m_theme']).':index');
    }

    /**
     * 内页控制函数
     */
    public function item()
    {
        $itemInfo = $this->getItemInfo($_GET['id']);
        $data = $this->getItemList($_GET['id']);

        if(!empty($_SESSION['m_member_id'])){
            D('MemberVisit')->add(array('member_id'=>$_SESSION['m_member_id'],'item_id'=>$_GET['id'],'date_visit'=>time()));
        }
        $this->assign('info', $itemInfo);
        $this->assign('list', $data['list']);
        $this->assign('nav', $this->getNav());
        $this->assign('page', $data['page']);
        $this->display($this->getRelTpl($itemInfo['template_name']));
    }

    /**
     * show the news push content
     */
    public function push()
    {
        $pushInfo = D('WechatNewsMeta')->getInfoById($_GET['id']);
        $pushInfo['cover_name'] = getPicPath(D('GalleryMeta')->getImg($pushInfo['cover'], 'm'));
        $pushInfo['intro'] = $pushInfo['description'];
        $pushInfo['info'] = htmlspecialchars_decode($pushInfo['content']);
        $pushInfo['date_add_text'] = date('Y-m-d H:i', $pushInfo['date_add']);

        $this->assign('info', $pushInfo);
        $this->display($this->getRelTpl('detail'));
    }

    /**
     * show the api data
     */
    public function api()
    {
        $id = $this->_get('id', 'intval');
        $article_id = $this->_get('article_id', 'intval');
        $itemInfo = D('Item')->where('id='.$id)->find();
        $url = htmlspecialchars_decode($itemInfo['api']);
        $info = $this->getApiInfo($url, $article_id);
        $data = $this->getApiList($url, $article_id, $info['count'], $id);
        $this->assign('info', $info);
        $this->assign('list', $data['list']);
        $this->assign('nav', $this->getNav());
        $this->assign('page', $data['page']);
        $this->display($this->getRelTpl($info['tpl']));
    }

    /**
     * 标记为喜欢
     */
    public function like()
    {
        $id = D('MemberCol')->add(array('member_id'=>$_SESSION['m_member_id'],'item_id'=>$_GET['id'],'date_col'=>time()));
        echo $id;
    }

    /**
     * 添加评论操作
     */
    public function message()
    {
        if(empty($_SESSION['m_member_id'])){
            $this->error('对不起，请使用微信访问本网站');
            exit;
        }
        $data = $this->_post();
        $data['member_id'] = $_SESSION['m_member_id'];
        $data['date_msg'] = time();
        $result = D('MemberMsg')->add($data);
        if(!empty($result)){
            $this->success('留言成功');
        }else{
            $this->error('留言失败');
        }
    }

    /**
     * 会员操作
     */
    public function action()
    {
        if(empty($_SESSION['m_member_id'])){
            echo json_encode(array('code'=>'4'));
            exit;
        }
        $data = $_POST;

        //判断24小时内是否执行了同样的操作
        $map['member_id'] = array('eq', $_SESSION['m_member_id']);
        $map['action'] = array('eq', $data['action']);
        $map['date_create'] = array('gt',  (time() - '86400'));
        $count = D('MemberAction')->getCount($map);
        //$check = D('MemberAction')->where($map)->find();

        if($count > '2'){
            echo json_encode(array('code'=>'2'));
            exit;
        }

        //获取抽奖结果ID 
        $result_prize_id = $this->lottery($_GET['id']);

        //获取扩展字段ID
        $ext_id = D('Ext')->where("res_type='item' AND lable='prize_count' AND res_id=".$_GET['id'])->getField('id');
        $prize_chance = D('ExtVal')->where('res_id='.$result_prize_id.' AND ext_id='.$ext_id)->getField('value');

        if(empty($prize_chance)){
            //如果奖品数量为空
            $data['item_id'] = '0';
        }else{
            $data['item_id'] = $result_prize_id;
        }

        $data['member_id'] = $_SESSION['m_member_id'];
        $data['date_create'] = time();
        $result = D('MemberAction')->add($data);
        if(!empty($result)){

            if(!empty($data['item_id'])){
                //如果操作结果不为空
                $result_data = D('Item')->where('id='.$data['item_id'])->find();


                //减少数量
                D('ExtVal')->where('res_id='.$data['item_id'].' AND ext_id='.$ext_id)->setDec('value');


                echo json_encode(array('code'=>'1','result'=>$result_data['title'],'prize_type'=>$result_data['sort_order'],'sn'=>str_pad($result,'6','0',STR_PAD_LEFT)));
            }else{
                echo json_encode(array('code'=>'0'));
            }
        }else{
            echo json_encode(array('code'=>'0'));
        }
    }

    /**
     * 抽奖
     */
    private function lottery($id)
    {
        $ids = D('Item')->where('parent_id='.$id)->order('sort_order')->getField('id', true);
        $list = array();
        $left = '1000';
        foreach($ids as $k=>$v){

            //概率
            $ext_id = D('Ext')->where("res_type='item' AND lable='prize_count' AND res_id=".$id)->getField('id');
            $prize_chance = D('ExtVal')->where('res_id='.$v.' AND ext_id='.$ext_id)->getField('value');
            
            for($i=0; $i< $prize_chance; $i++){
                $list[] = $v;
            }
            $left -= $prize_chance;
        }
        if($left != 0){
            for($i=0;$i<$left;$i++){
                $list[] = '0';
            }
        }
        $result = array_rand($list);
        return $list[$result];
    }

    /**
     * 设置会员信息
     */
    public function setMemberInfo()
    {
        if(empty($_SESSION['m_member_id'])){
            echo json_encode(array('code'=>'4'));
            exit;
        }

        $data = $_POST;
        $result = D('Member')->where('id='.$_SESSION['m_member_id'])->save($data);
        if(!empty($result)){
            echo json_encode(array('code'=>'1','msg'=>'操作成功'));
        }else{
            echo json_encode(array('code'=>'0','msg'=>'操作失败'));
        }
    }
}
