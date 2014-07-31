<?php
/**
 * File Name: PushAction.class.php
 * Author: chen
 * Created Time: 2013-11-9 14:23:18
*/
class NewsAction extends HomeAction{
	/**
	 * 回复图文消息素材列表
	 */
	public function newsList(){
		$newsObj = D('WechatNews');

        /********筛选出特殊的图文列表********/
        $keywordList = D('WechatRoute')->where("obj_type='common'")->getField('keyword' ,true);
        $routeMap['user_id'] = array('eq', $_SESSION['uid']);
        $routeMap['obj_type'] = array('eq', 'news');
        $routeMap['keyword'] = array('not in', $keywordList);
        $idList = D('WechatRoute')->where($routeMap)->getField('obj_id', true);
        /********结束************************/

		$map['user_id'] = array('eq', $_SESSION['uid']);
        $map['id'] = array('in', $idList);

        //分页类
		$page = page($newsObj->getCount($map), 10);

        //获取图文列表
        $fields = array('id','date_modify');
        $fields_all = $newsObj->field_list();
		$news_list = $newsObj->field($fields)->where($map)->order('date_modify desc')->limit($page->firstRow, $page->listRows)->select();

		foreach ($news_list as $k=>$v){
			$news_list[$k] = $newsObj->format($v, array('keyword'));
            $news_list[$k]['action_list'] = array(
                array('title'=>'管理子图文','type'=>'ls','url'=>U('News/metaList',array('id'=>$v['id']))),
                array('title'=>'编辑','type'=>'edit','url'=>U('News/newsInfo',array('id'=>$v['id']))),
                array('title'=>'删除','type'=>'del','url'=>U('News/delNews',array('id'=>$v['id']))),
            );

		}

        //模板赋值
        $fields = array_merge($fields, array('keyword','action_list'));
        $btn_list = array(
            array(
                'title' => '添加图文消息',
                'url'   => U('News/newsInfo'),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('News/delNews'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
		$data = array( 
            'title'=>'图文消息列表',
            'field_list'=>$this->get_field_list($fields_all, $fields),
            'field_info'=>$news_list,
            'page_list' => $page->show(),
            'btn_list'  => $btn_list,
		);
		$this->assign($data);
		$this->display('Public:list');
	}

	/**
	 * 页面：添加图文素材
	 */
	public function newsInfo(){
        if(empty($_POST)){
            $id = $this->_get('id', 'intval');
            $fields = array('id');
            if(!empty($id)){

                //编辑图文
                $news_info = D('WechatNews')->field($fields)->where('id='.$id)->find();
                $route_info = D('WechatRoute')->getRoute('news', $id);
                $news_info['route_id'] = $route_info['id'];
                $news_info['keyword'] = $route_info['keyword'];
            }
            $fields = array_merge($fields, array('route_id','keyword'));
            $fields_all = D('WechatNews')->field_list();
            $data = array(
                'title' => '图文编辑',
                'form_url'=>U('News/newsInfo'),
                'field_list'=>$this->get_field_list($fields_all, $fields),
                'field_info'=>$news_info,
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }

        //内容更新
        if(!D('WechatRoute')->checkKeyword($_POST['keyword'], $_POST['id'])){
            echo json_encode(array('msg'=>'关键字不可用'));
            exit;
        }

        //整理数据
        $news_info = array('id'=>$_POST['id']);
        $route_info = array('id'=>$_POST['route_id'],'keyword'=>$_POST['keyword']);

        //更新
        $news_id = D('WechatNews')->updateNews($news_info);
        D('WechatRoute')->updateRoute('news', $news_id, $route_info);
        echo json_encode(array('code'=>'1','msg'=>'操作成功'));
	}

	/**
	 * 图文删除
	 */
	public function delNews(){
		$newsObj = D('WechatNews');
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
		$arrMap['id'] = $arrRouteMap['obj_id'] = $arrMetaMap['news_id'] = array('in', $delIds);
		if($newsObj->where($arrMap)->delete()){
            D('WechatRoute')->delRoute('news', $arrRouteMap);
            D('WechatNewsMeta')->where($arrMetaMap)->delete();
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
		}else{
            echo json_encode(array('msg'=>'删除失败'));
		}
	}

    /*********************子图文内容管理*********************/
    /**
     * meta list
     */
    public function metaList()
    {
        $id = $this->_get('id', 'intval');
        $fields = array('id','title','description','date_modify');
        $map = array('news_id'=>$id);
        $page = page(D('WechatNewsMeta')->getCount($map));

        $meta_list = D('WechatNewsMeta')
            ->field($fields)
            ->where($map)
            ->order('sort_order')
            ->limit($page->firstRow, $page->listRows)
            ->select();

        foreach($meta_list as $k=>$v){
            $meta_list[$k] = D('WechatNewsMeta')->format($v, array('cover_name'));
            $meta_list[$k]['action_list'] = array(
                array('title'=>'编辑','type'=>'edit','url'=>U('News/metaInfo',array('id'=>$v['id']))),
                array('title'=>'删除','type'=>'del','url'=>U('News/delMeta',array('id'=>$v['id']))),
            );
        }

        $fields[] = 'action_list';
        $fields_all = D('WechatNewsMeta')->field_list();
        $btn_list = array(
            array(
                'title' => '添加子图文',
                'url'   => U('News/metaInfo',array('news_id'=>$id)),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('News/delMeta'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $route_info = D('WechatRoute')->getRoute('news', $id);        
        $bread_list = array(
            array('title'=>'图文回复列表','url'=>U('News/newsList')),
            array('title'=>$route_info['keyword'],'url'=>'javascript:;','type'=>'current'),
        );
        $data = array(
            'title'=>'子图文列表',
            'bread_list' => $bread_list,
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all, $fields),
            'field_info' => $meta_list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * meta update
     */
    public function metaInfo()
    {
        if(empty($_POST)){
            $id = $this->_get('id', 'intval');
            $news_id = $this->_get('news_id', 'intval');
            $fields = array('id','news_id','title','description','cover','url');

            //编辑
            if(!empty($id)){
                //子图文信息
                $meta_info = D('WechatNewsMeta')->field($fields)->where('id='.$id)->find();
            }else{
                $meta_info['news_id'] = $news_id;
            }

            //模板赋值
            $fields_all = D('WechatNewsMeta')->field_list();
            $route_info = D('WechatRoute')->getRoute('news',$meta_info['news_id']);
            $bread_list = array(
                array('title'=>'图文回复列表','url'=>U('News/newsList')),
                array('title'=>$route_info['keyword'],'url'=>U('News/metaList',array('id'=>$meta_info['news_id']))),
                array('title'=>$meta_info['title'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title' => '子图文编辑',
                'bread_list' => $bread_list,
                'form_url'   => U('News/metaInfo'),
                'field_list' => $this->get_field_list($fields_all, $fields),
                'field_info' => $meta_info,
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $meta_info = $this->_post();
        D('WechatNewsMeta')->updateMeta($meta_info, $meta_info['news_id']);
        echo json_encode(array('code'=>'1','msg'=>'操作成功'));
    }

	/**
	 * zi图文删除
	 */
	public function delMeta(){
		$metaObj = D('WechatNewsMeta');
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
		$arrMap['id'] = $arrRouteMap['obj_id'] = $arrMetaMap['news_id'] = array('in', $delIds);
		if($metaObj->where($arrMap)->delete()){
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
		}else{
            echo json_encode(array('msg'=>'删除失败'));
		}
	}
    /*********************文字内容管理***********************/
	/**
	 * 文字素材列表
	 */
	public function textList(){
		$map['user_id'] = array('eq', $_SESSION['uid']);
		$page = page(D('WechatText')->getCount($map));
        $fields = array('id','content','date_modify');

		$text_list = D('WechatText')->field($fields)->where($map)->order('date_modify desc')->limit($page->firstRow, $page->listRows)->select();

        foreach($text_list as $k=>$v){
            $text_list[$k] = D('WechatText')->format($v, array('keyword'));
            $text_list[$k]['action_list'] = array(
                array('type'=>'edit','url'=>U('News/textInfo',array('id'=>$v['id']))),
                array('type'=>'del','url'=>U('News/delText',array('id'=>$v['id']))),
            );
        }

        $fields = array_merge($fields, array('keyword','action_list'));
        $fields_all = D('WechatText')->field_list();
        $btn_list = array(
            array(
                'title' =>'添加文本回复',
                'url'   =>U('News/textInfo'),
                'class' => 'primary',
            ),
            array(
                'title' =>'批量删除',
                'url'   =>U('News/delText'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
		$data = array(
            'title'      => '文本回复列表',
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all, $fields),
            'field_info' => $text_list,
            'page_list'  => $page->show(),
		);
		$this->assign($data);
		$this->display('Public:list');
	}

	/**
	 * 文字素材添加页面
	 */
	public function textinfo(){
        if(empty($_POST)){
            $id = $this->_get('id', 'intval');
            $fields = array('id','content');
            if(!empty($id)){
                $text_info = D('WechatText')->field($fields)->where('id='.$id)->find();
                $route_info = D('WechatRoute')->getRoute('text', $id);
                $text_info['route_id'] = $route_info['id'];
                $text_info['keyword'] = $route_info['keyword'];
            }
            $fields = array_merge($fields, array('route_id','keyword'));
            $fields_all = D('WechatText')->field_list();
            $bread_list = array(
                array('title'=>'文本回复列表','url'=>U('News/textList')),
                array('title'=>$text_info['keyword'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title'      => '文本回复编辑',
                'bread_list' => $bread_list,
                'form_url'   => U('News/textInfo'),
                'field_list' => $this->get_field_list($fields_all, $fields),
                'field_info' => $text_info,
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $text_info = array('id'=>$_POST['id'],'content'=>$_POST['content']);
        $route_info = array('id'=>$_POST['route_id'],'keyword'=>$_POST['keyword']);

        if(!D('WechatRoute')->checkKeyword($route_info['keyword'], $text_info['id'])){
            echo json_encode(array('msg'=>'关键字不可用'));
            exit;
        }
        $obj_id = D('WechatText')->updateText($text_info);
        D('WechatRoute')->updateRoute('text', $obj_id, $route_info);
        echo json_encode(array('code'=>'1','msg'=>'操作成功'));
	}

	/**
	 * 文字素材的删除操作
	 */
	public function delText(){
		$textObj = D('WechatText');
        //数据
        $delIds = array();
        $postIds = $this->_post('id');
        if (!empty($postIds)) {
            $delIds = $postIds;
        }
        $getId = intval($this->_get('id'));
        if (!empty($getId)) {
            $delIds[] = $getId;
        }
        //删除数据
        if (empty($delIds)) {
            echo json_encode(array('msg'=>'请选择您要删除的数据'));
            exit;
        }
		$arrMap['id'] = $arrRouteMap['obj_id'] = array('in', $delIds);
		if($textObj->where($arrMap)->delete()){
            D('WechatRoute')->delRoute('text', $arrRouteMap);
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
		}else{
            echo json_encode(array('code'=>'1','msg'=>'删除失败'));
		}
	}
}
