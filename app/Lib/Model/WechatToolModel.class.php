<?Php
/**
 * 通用工具模型
 * @author blue
 * @version 2013-12-21
 */
class WechatToolModel extends CommonModel {

    /**
     * 格式化
     * @return array $info 格式化后的数组
     * @param array $info 格式化前的数组
     * @param array $arrFormatField 需要格式化的数组
     */
    public function format($info, $arrFormatField){
        if(in_array('useStatus', $arrFormatField)){
            $result = D('WechatRoute')->where("user_id=".$_SESSION['uid']." AND keyword='".$info['name']."'")->find();
            $info['useStatus'] = ($result) ? '1'
                : '0';
        }
        return $info;
    }

    /**
     * 字段信息
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'名称','name'=>'name','type'=>'text'),
            array('title'=>'描述','name'=>'intro','type'=>'textarea'),
            array('title'=>'函数','name'=>'function','type'=>'text'),
            array('title'=>'排序','name'=>'sort_order','type'=>'number'),
            array('title'=>'使用状态','name'=>'status','type'=>'radio','data'=>array(
                array('title'=>'使用','value'=>'1'),
                array('title'=>'不使用','value'=>'0'),
            ),),
            array('title'=>'更新时间','name'=>'date_modify','type'=>'date'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),

        );
    }

    ///////////////////////功能类函数///////////////////////////////////
    /**
     * 百度API：根据经纬度获取城市名称
     */
    public function getAddress(){
        $url = 'http://api.map.baidu.com/geocoder?location='.$_SESSION['latitude'].','.$_SESSION['longitude'].'&output=json&key=5c1da412cb98cbde54f87d45d8feda56';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        $city = $result['result']['addressComponent']['city'];
        $_SESSION['current_city'] = $city;
    }

    /**
     * 公交查询
     */
    public function bus($data){
        $city = csubstr($data, 0, 6);
        $data = csubstr($data, 6);
        preg_match('/[\x{4e00}-\x{9fa5}]+/u', $data, $arrCity);
        preg_match('/[a-zA-Z]*-?[0-9]+/', $data, $arrCode);
        $url = "http://openapi.aibang.com/bus/lines?app_key=f41c8afccc586de03a99c86097e98ccb&city=".$arrCity['0']."&q=".$arrCode['0'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        Xml_Parse_into_struct($parser, $result, $values, $tags);
        xml_parser_free($parser);
        foreach($tags as $k2=>$v2){
            foreach($values as $k1=>$v1){
                if($k2 == 'name'){
                    $bus['name'] = $values[$v2['0']]['value'];
                }elseif($k2 == 'info'){
                    $bus['info'] = $values[$v2['0']]['value'];
                }elseif($k2 == 'stats'){
                    $bus['stats'] = $values[$v2['0']]['value'];
                }
            }
        }
        $content = $bus['name'].$bus['info'].$bus['stats'];
        return D('Wx')->setText($content);
    }

    /**
     * 天气搜索
     */
    public function weather($city){
        $city = csubstr($city, 6);
        $cityCode = D('Weather')->where("`city`='".$city."'")->getField('citycode');
        $url = 'http://www.weather.com.cn/data/cityinfo/'.$cityCode.'.html';
        $header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING ,'gzip');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        //json解码为对象
        $result = json_decode($result, true);
        $content = $result['weatherinfo']['city'].' '.$result['weatherinfo']['weather'].' 最高气温'.$result['weatherinfo']['temp1'].' 最低气温'.$result['weatherinfo']['temp2'];
        return D('Wx')->setText($content);
    }

    /**
     * 美食
     */
    public function cook(){
        $arrTitle = array();
        while(empty($arrTitle)){
            $rand = rand(1, 172031);
            $url = "http://home.meishichina.com/wap.php?ac=recipe&id=".$rand."&t=1&fr=#utm_source=wap1_index_recipe_text";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);
            $pattern = '/<div class="haha">(.*)<\/h3>(.*)<\/div>/Us';
            preg_match("/<h1>(.*)<\/h1>/Us", $result, $arrTitle);
        }
        preg_match_all('/class="k">(.*)<\/p>/Us', $result, $arrInfo);
        $content = '【'.$arrTitle['1'].'】';
        foreach($arrInfo['1'] as $k=>$v){
            $content .= $v;
        }
        //$content = mb_convert_encoding($content[2], 'UTF-8', 'gbk');
        $content = strip_tags($content);
        return D('Wx')->setText($content);
    }

    /**
     * 百度翻译
     */
    public function trans($data){
        $keyword = csubstr($data, 6);
        $url = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=9peNkh97N6B9GGj9zBke9tGQ&q=".$keyword."&from=auto&to=auto";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING ,'gzip');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        //json解码为对象
        $result = json_decode($result, true);
        $content = $result['trans_result']['0']['dst'];
        return D('Wx')->setText($content);
    }

    /**
     * 聊天机器人
     */
    public function chat($content){
        $key = '92773352-3798-4737-859d-bbb5dbe77b26';
        $url = "http://sandbox.api.simsimi.com/request.p?key=".$key."&lc=ch&text=".$content;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        return D('Wx')->setText($result['response']);
    }

    /**
     * 获取百度新闻
     */
    public function baiduNews(){
        $url = 'http://cn.engadget.com/rss.xml';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $result, $values, $tags);
        xml_parser_free($parser);

        For($i=0; $i<5; $i++){
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
        return D('Wx')->setNews($newsList, 5);
    }

    /**
     * 获取新浪RSS新闻数据
     * 以文本形式输出新浪首条新闻
     */
    public function sinaNews(){
        $url = "http://rss.sina.com.cn/news/marquee/ddt.xml";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        Xml_Parse_into_struct($parser, $result, $values, $tags);
        xml_parser_free($parser);


        foreach($tags as $k2=>$v2){
            foreach($values as $k1=>$v1){
                for($i=2; $i<=6; $i++){
                    if($k2 == 'title'){
                        $arrNews[$i]['title'] = $values[$v2[$i]]['value'];
                    }elseif($k2 == 'description'){
                        $arrNews[$i]['description'] = $values[$v2[$i-1]]['value'];
                    }
                }
            }
        }
        $content = $arrNews[2]['title'].$arrNews[2]['description'];
        return D('Wx')->setText($content);
    }

    /**
     * 笑话
     */
    public function joke(){
        /*
        $rand = rand(1, 627785);
        $type = array('zz_joke', 'xd_joke');
        $type_id = array_rand($type);
        $url = 'm.haha365.com/'.$type[$type_id].'/'.$rand.'.htm';*/
        $url = "http://m.haha365.com/xd_joke/627798.htm";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        print_r();exit;
        //$pattern = '/<div class="haha">(.*)<\/h1>(.*)<\/div>/Us';
        //preg_match($pattern, $result, $arrInfo);
        $content = '【'.$arrInfo['1'].'】'.$arrInfo['2'];
        $content = mb_convert_encoding($content[2], 'UTF-8', 'gbk');
        $content = strip_tags($content);
        return D('Wx')->setText($result);
    }

    /**
     * 每日英语
     */
    public function english(){
        $rand = rand('100', '727');
        $url = 'http://wap.iciba.com/dailysentence/content/'.$rand.'#anchor';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $pattern = '/<div class="dayC" id="(.*)">(.*)<a(.*)<h2 class="cn">(.*)<\/h2>/Us';
        preg_match($pattern, $result, $content);
        $content[2] = strip_tags($content[2]);
        return D('Wx')->setText($content[2].$content[4]);
    }

    /**
     * 可乐
     */
    public function kele(){
        $rand = rand('01', '290');
        $url = 'http://www.kelepuzi.com/text/page/'.$rand.'.html';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $pattern = '/<div class="post-content">(.*)<\/p>/Us';
        preg_match($pattern, $result, $content);
        $content = strip_tags($content[1]);
        return D('Wx')->setText($content);
    }

    /**
     * 我的信息
     */
    public function memberInfo($keyword, $member_id)
    {
        $memberObj = D('Member');
        $memberInfo = $memberObj->getInfoById($member_id);
        $memberInfo = $memberObj->format($memberInfo, array('name', 'mobile'));
        $result = '您好，您的信息是：';
        $result .= '昵称：'.$memberInfo['name'].'  ';
        $result .= '手机号码：'.$memberInfo['mobile'].'  ';
        $result .= '邮箱：'.$memberInfo['email'].'  ';
        //$result .= '收藏的文章：'$memberInfo['like_count'].'  ';
        $result .= '注册时间：'.date('Y-m-d H:i', $memberInfo['date_reg'].'  ');
        return D('Wx')->setText($result);
    }

    /**
     * 我的足迹
     */
    public function memberVisit($keyword, $member_id)
    {
        $item_ids = D('MemberVisit')->where('member_id='.$member_id)->order('date_visit desc')->select();
        $map['id'] = array('in', $item_ids);
        $itemList = D('Item')->where($map)->limit(6)->select();
        return D('Wx')->setNews($itemList, count($itemList), $member_id);
    }

    /**
     * 我的收藏
     */
    public function memberLike($keyword, $member_id)
    {
        $item_ids = D('MemberEvent')->where("id=".$member_id." AND event='like'")->order('date_event desc')->getField('item_id', true);
        $map['id'] = array('in', $item_ids);
        $itemList = D('Item')->where($map)->limit(5)->select();
        return D('Wx')->setNews($itemList, 5, $member_id);
    }
}
