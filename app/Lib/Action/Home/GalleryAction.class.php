<?php
/**
 * 图库管理
 * @author chen
 * @version 2014-03-24
 */
class GalleryAction extends HomeAction
{
    /**
     * 获取图片地址
     */
    public function getImgSrc()
    {
        $id = intval($_GET['id']);
        $size = trim($_GET['size']);
        if(empty($id)){
            echo './Public/img/empty.jpg';
            exit;
        }
        if(empty($size)){
            $size = 's';
        }
        $path = D('GalleryMeta')->where('id='.$id)->getField('path');
        echo getPicPath($path, $size);
    }
    
    /**
     * upload the image
     */
    public function uploadImage()
    {

		if(isset($_FILES)){
			$picList = uploadPic();
			if($picList['code'] != 'error'){
				$id = D('GalleryMeta')->addImg($picList['myFile']['savename']);
			}
            if(!empty($id)){
                echo $id;
            }else{
                //echo 'upload image field';
                echo '图片上传失败';
            }

		}else{
            echo 'img is null';
        }
    }


   /*
     * get the gallery list for add new img
     */
    public function getGalleryList()
    {
        $galleryList = D('Gallery')->where('user_id='.$_SESSION['uid'])->select();
        $html = '';
        foreach($galleryList as $k=>$v){
            $html .= "<option value='".$v['id']."'>".$v['title']."</option>";
        }
        echo $html;
    }

    /**
     * show the gallery meta
     */
    public function showImgList()
    {
        $imgObj = D('GalleryMeta');
        $gallery_id = $this->_post('gallery_id', 'intval');
        if(empty($gallery_id)){
            $gallery_id = D('Gallery')->getDefaultGalleryId();
        }
        $arrField = array();
        $arrMap['gallery_id'] = array('eq', $gallery_id);
        $arrOrder = array();
        $imgList = $imgObj->getList($arrField, $arrMap, $arrOrder);
        foreach($imgList as $k=>$v){
            $imgList[$k] = $imgObj->format($v, array('path_name'));
        }
        $this->assign('imgList', $imgList);
        $this->display('Public:img_list');
    }

    /**
     * scan user img
     */
    public function lostImgList()
    {
        $dir = './data/attach';
        $allImgs = find_all_files($dir);
        $sqlImgs = D('GalleryMeta')->getField('path', true);
        foreach($sqlImgs as $k=>$v){
            $sqlImgs[$k] = getPicPath($v);
        }
        $result = array_diff($allImgs, $sqlImgs);
        $data = array(
            'imgList' => $result,
            'lostDelUrl' => U('Home/Gallery/lostDel'),
        );
        $this->assign($data);
        $this->display();
    }

    /**
     * lost img delete
     */
    public function lostDel()
    {
        $delImgs = array();
		$postImgs = $this->_post('img');
		if (!empty($postImgs)) {
			$delImgs = $postImgs;
		}
		$getImg = trim($this->_get('img'));
		if (!empty($getImg)) {
			$delImgs[] = $getImg;
		}
		if (empty($delImgs)) {
			$this->error('请选择您要删除的数据');
		}
        foreach($delImgs as $k=>$v){
            $imgInfo = pathinfo($v);
            foreach(array('_b', '_m', '_s') as $k2=>$v2){
                $delImgs[] = $imgInfo['dirname'].'/'.basename($imgInfo['basename'], '.jpg').$v2.'.'.$imgInfo['extension'];
            }
        }
        foreach($delImgs as $k=>$v){
            unlink($v);
        }
        $this->success('删除成功');
    }

    /**********************图库管理*******************************/

    /**
     * 获取图库列表
     */
    public function galleryList()
    {
        $fields_all = D('Gallery')->field_list();
        $fields = array('id','title', 'intro', 'date_modify');
        $map = array('user_id'=>$_SESSION['uid']);
        $page = page(D('Gallery')->getCount($map),10);

        $list = D('Gallery')
            ->field($fields)
            ->where($map)
            ->order('date_modify desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        foreach($list as $k=>$v){
            $list[$k]['action_list'] = array(
                array('url'=>U('Gallery/metaList',array('gallery_id'=>$v['id'])),'type'=>'ls'),
                array('url'=>U('Gallery/galleryInfo',array('id'=>$v['id'])),'type'=>'edit'),
                array('url'=>U('Gallery/galleryDel',array('id'=>$v['id'])),'type'=>'del'),
            );
        }

        $fields[] = 'action_list';
        $btn_list = array(
            array('title'=>'添加相册','class'=>'primary','url'=>U('Gallery/galleryInfo')),
            array('title'=>'批量删除','class'=>'danger','url'=>U('Gallery/galleryDel'),'type'=>'form'),
        ); 
        $data = array(
            'title'      => '相册列表',
            'form_url'   => U('Gallery/galleryInfo'),
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * add gallery
     */
    public function galleryInfo()
    {
        $this->breadcrumbs['1'] = array(
            'title' => '相册管理',
            'url' => U('Gallery/galleryList'),
        );
        $fields_all = D('Gallery')->field_list();
        $fields = array('id','title','intro');
        if(empty($_POST)){
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('Gallery')
                    ->field($fields)
                    ->where('id='.$id)
                    ->find();
            }
            $bread_list = array(
                array('title'=>'相册列表','url'=>U('Gallery/galleryList')),
                array('title'=>$info['title'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title'      => '相册设置',
                'bread_list' => $bread_list,
                'form_url'   => U('Gallery/galleryInfo'),
                'return_url' => U('Gallery/galleryList'),
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            $data['user_id'] = $_SESSION['uid'];
            if(D('Gallery')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'创建成功'));
            }else{
                echo json_encode(array('msg'=>'创建失败'));
            }
        }else{
            if(D('Gallery')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'更新失败'));
            }
        }
    }

    /**
     * gallery delete
     */
    public function galleryDel()
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
            echo json_encode(array('code'=>'0','msg'=>'请选择您要删除的数据'));
            exit;
		}
		$map['id'] = $metaMap['gallery_id'] = array('in', $delIds);
        $paths = D('GalleryMeta')->where($metaMap)->getField('path', true);
        foreach($paths as $k=>$v){
            delImage($v);
        }
        D('GalleryMeta')->where($metaMap)->delete();
        $covers = D('Gallery')->where($map)->getField('cover', true);
        foreach($covers as $k=>$v){
            delImage($v);
        }
        if(D('Gallery')->where($map)->delete()){
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
        }else{
            echo json_encode(array('code'=>'0','msg'=>'删除失败'));
        }
    }


    /*******************************图片管理******************************/
    /**
     * meta list
     */
    public function metaList()
    {
        $fields_all = D('GalleryMeta')->field_list();
        $fields = array('id','path','title','date_modify');
        $map = array('gallery_id'=>intval($_GET['gallery_id']));
        $page = page(D('GalleryMeta')->getCount($map));

        $list = D('GalleryMeta')
            ->field($fields)
            ->where($map)
            ->order('date_modify desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        foreach($list as $k=>$v){
            $list[$k]['path'] = getPicPath($v['path'], 's');
            $list[$k]['action_list'] = array(
                array('url'=>U('Gallery/metaInfo',array('id'=>$v['id'])),'type'=>'edit'),
                array('url'=>U('Gallery/metaDel',array('id'=>$v['id'])),'type'=>'del'),
            );
        }

        $title = D('Gallery')->where('id='.intval($_GET['gallery_id']))->getField('title');
        $bread_list = array(
            array('title' => '相册列表','url' => U('Gallery/galleryList')),
            array('title'=>$title,'url'=>'javascript:;','type'=>'current'),
        );
        $btn_list = array(
            array(
                'title' => '添加图片',
                'url'   => U('Gallery/metaInfo',array('gallery_id'=>intval($_GET['gallery_id']))),
                'class' => 'primary',
            ),
            array(
                'title' => '批量删除',
                'url'   => U('Gallery/metaDel'),
                'class' => 'danger',
                'type'  => 'form',
            ),
        );
        $fields[] = 'action_list';
        $data = array(
            'title'      => '图片列表',
            'form_url'   => U('Gallery/metaInfo'),
            'btn_list'   => $btn_list,
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $list,
            'page_list'  => $page->show(),
            'bread_list' => $bread_list,
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * meta info
     */
    public function metaInfo()
    {
        $fields_all = D('GalleryMeta')->field_list();
        $fields = array('id','gallery_id','title','path');
        if(empty($_POST)){
            $id = intval($_GET['id']);
            if(!empty($id)){
                $info = D('GalleryMeta')->field($fields)->where('id='.$id)->find();
            }else{
                $gallery_id = intval($_GET['gallery_id']);
                $info['gallery_id'] = $gallery_id;
            }
            
            $title = D('Gallery')->where('id='.$info['gallery_id'])->getField('title');
            $bread_list = array(
                array('title'=>'相册列表','url'=>U('Gallery/galleryList')),
                array('title'=>$title,'url'=>U('Gallery/metaList',array('gallery_id'=>$info['gallery_id']))),
                array('title'=>$info['title'],'url'=>'javascript:;','type'=>'current'),
            );
            $data = array(
                'title' => '图片信息',
                'bread_list' => $bread_list,
                'field_list' => $this->get_field_list($fields_all,$fields),
                'field_info' => $info,
                'form_url'   => U('Gallery/metaInfo'),
            );
            $this->assign($data);
            $this->display('Public:info');
            exit;
        }
        $data = $_POST;
        $data['date_modify'] = time();
		if(!empty($_FILES['path']['name'])){
			$picList = uploadPic();
			if($picList['code'] != 'error'){
				$data['path'] = $picList['path']['savename'];
			}
		}
        if(empty($data['id'])){
            $data['date_add'] = time();
            if(D('GalleryMeta')->add($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'更新失败'));
            }
        }else{
            if(D('GalleryMeta')->save($data)){
                echo json_encode(array('code'=>'1','msg'=>'更新成功'));
            }else{
                echo json_encode(array('code'=>'0','msg'=>'更新失败'));
            }
        }
    }

    /**
     * gallery meta delete
     */
    public function metaDel()
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
            echo json_encode(array('code'=>'0','msg'=>'请选择您要删除的数据'));
            exit;
		}
		$map['id'] = array('in', $delIds);
        $paths = D('GalleryMeta')->where($map)->getField('path', true);
        foreach($paths as $k=>$v){
            delImage($v);
        }
        if(D('GalleryMeta')->where($map)->delete()){
            echo json_encode(array('code'=>'1','msg'=>'删除成功'));
        }else{
            echo json_encode(array('code'=>'0','msg'=>'删除失败'));
        }
    }

}
