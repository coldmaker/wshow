<?php
/**
 * RSS订阅回复 模型
 * @author chen
 * @version 2014-03-31
 */
class WechatRssModel extends CommonModel
{
    /**
     * rss push
     */
    public function getPushList($id)
    {
        $rssInfo = $this->where('id='.$id)->find();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rssInfo['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $result, $values, $tags);
        xml_parser_free($parser);

        for($i=0; $i<$rssInfo['count']; $i++){
        foreach ($tags as $k=>$v){
            if($k == 'title'){
                $newsList[$i]['title'] = $values[$v[$i+2]]['value'];
            }elseif($k == 'description'){
                $description = $values[$v[$i+1]]['value'];
                //如果简介中有图片，就将其作为图文封面
                $pattern = '/<img(.*)src="(.*)"/Us';
                preg_match($pattern, $description, $content);
                $newsList[$i]['cover'] = $content['2'];
                $newsList[$i]['description'] = $values[$v[$i+1]]['value'];
            }elseif($k == 'link'){
                $newsList[$i]['url'] = $values[$v[$i+2]]['value'];
            }
        }
        }
        return $newsList;
    }

    /**
     * 字段信息
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'标题','name'=>'title','type'=>'text'),
            array('title'=>'关键字','name'=>'keyword','type'=>'text'),
            array('title'=>'链接','name'=>'url','type'=>'url'),
            array('title'=>'数量','name'=>'count','type'=>'number'),
            array('title'=>'使用状态','name'=>'status','type'=>'radio','data'=>array(
                array('title'=>'使用','value'=>'1'),
                array('title'=>'不使用','value'=>'0'),
            ),),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),

        );
    }


}
