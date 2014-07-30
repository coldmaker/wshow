<?php
/**
 * Wx model
 * @author chen
 * @version 2014-03-28
 */
class WxModel extends CommonModel
{
	/**
	 * 组装text
	 */
    public function setText($content)
    {
	  $texttpl = D('WechatTpl')->where('type="text"')->getField('texttpl');
	  $content = sprintf($texttpl, $content);
	  return ($content);
	}

    /**
     * 组装xml
     * @return xml $content 组装为xml后的数据
     * @param array $newsList 需要输出的图文数组
     * @param int $count 数组的数量
     */
    public function setNews($newsList, $count, $member_id)
    {
        $texttpl = D('WechatTpl')->where('type="news"')->getField('texttpl');
        $content = "<MsgType><![CDATA[news]]></MsgType>";
        $content .= "<ArticleCount>".$count."</ArticleCount>";
        $content .= "<Articles>";
        foreach($newsList as $k=>$v){
            //判断url是否需要处理
            $result = substr_count($v['cover'], 'http://');
            if(empty($result)){
                $v['cover'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].'/', getPicPath(D('GalleryMeta')->getImg($v['cover'])));
            }
            if(empty($v['url'])){
                $v['url'] = 'http://'.$_SERVER['HTTP_HOST'].U('Mobile/Index/push', array(
                    'user'  => $this->user,
                    'member_id' => $member_id,
                    'id' => $v['id'],
                ));
            }else{
                $v['url'] .= '&member_id='.$member_id;
            }
            $content .= sprintf($texttpl, $v['title'], $v['description'], $v['cover'], $v['url']);
        }
        $content .= "</Articles>";
        return $content;
	}

    /**
     * 设置头部
     * @return xml $content 最后输出的信息
     * @param array $arrPost 用户POST提交的数据
     * @param xml $content 输出信息的BODY
     */
    public function setHeader($arrPost, $content)
    {
        $fromUsername = $arrPost['FromUserName'];
        $toUsername = $arrPost['ToUserName'];
        $time = time();
        $texttpl = M('WechatTpl')->where('type="header"')->getField('texttpl');
        $resultStr = sprintf($texttpl, $fromUsername, $toUsername, $time, $content);
        return $resultStr;
    }


}
