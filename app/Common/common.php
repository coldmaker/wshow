<?php
/**
 * 扩展函数库
 * @version 2013-07-11
 */

////////////////////常用函数////////////////////
/**
 * 分页
 */
function page($count, $listRows = '10', $theme = '')
{
    import('ORG.Util.Page');
    //初始化
    $page = new Page($count, $listRows);
    if ($theme == 'simple') {
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $page->setConfig('prev', '<');
        $page->setConfig('next', '>');
        $theme = "%first% %upPage% %linkPage% %downPage% %end%";
    } else {
        $page->setConfig('prev', '<<');
        $page->setConfig('next', '>>');
        $page->setConfig('first','首页');
        $page->setConfig('last','尾页');
        $theme = "%first% %prePage% %upPage% %linkPage% %downPage% %nextPage% %end%";
    }
    $page->setConfig('theme',$theme);
    return $page;
}


////////////////////字符串相关////////////////////
/**
 * 转换为安全的纯文本
 *
 * @param string  $text
 * @param boolean $parse_br    是否转换换行符
 * @param int     $quote_style ENT_NOQUOTES:(默认)不过滤单引号和双引号 ENT_QUOTES:过滤单引号和双引号 ENT_COMPAT:过滤双引号,而不过滤单引号
 * @return string|null string:被转换的字符串 null:参数错误
 */
function t($text, $parse_br = false, $quote_style = ENT_NOQUOTES)
{
    if (is_numeric($text))
        $text = (string)$text;

    if (!is_string($text))
        return null;

    if (!$parse_br) {
        $text = str_replace(array("\r","\n","\t"), ' ', $text);
    } else {
        $text = nl2br($text);
    }

    //$text = stripslashes($text);
    $text = htmlspecialchars($text, $quote_style, 'UTF-8');

    return $text;
}


////////////////////邮件相关////////////////////
/**
 * 发送邮件
 * @param    string    $toEmail    收件人的email
 * @param    string    $toName        收件人的称呼
 * @param    string    $subject    邮件主题
 * @param    string    $body        邮件内容
 * @param    string    $attachs    附件数组
 * @return    boolean/string
 */
 function sendMail($toEmail, $toName, $subject = '', $body = '', $attachs = array())
 {
    $setting = $GLOBAL['setting']['email'];
    $setting = array(
        'smtp_host'    => 'smtp.163.com',
        'smtp_port'    => '25',
        'smtp_user'    => 'phptester@163.com',
        'smtp_pass'    => 'jiweitao',
        'smtp_from_email'    => 'phptester@163.com',
        'smtp_from_name'    => '飞狗旅行',
    );
    //导入类库
    vendor('PHPMailer.class#phpmailer');
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->IsSMTP();
    //$mail->SMTPSecure = 'ssl';
    //调试模式：0=不显示，1=错误和信息， 2=只显示信息
    $mail->SMTPDdebug = 0;

    //设置验证信息
    $mail->SMTPAuth = true;
    $mail->Host = $setting['smtp_host'];
    $mail->Port = $setting['smtp_port'];
    $mail->Username = $setting['smtp_user'];
    $mail->Password = $setting['smtp_pass'];

    //设置发件人信息和回复信息
    $mail->SetFrom($setting['smtp_from_email'], $setting['smtp_from_name']);
    $mail->AddReplyTo($setting['smtp_from_email'], $setting['smtp_from_name']);

    //邮件内容
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($toEmail, $toName);

    //设置附件
    if (is_array($attachs)) {
        foreach ($attachs as $attach) {
            is_file($attach) && $mail->AddAttachment($attach);
        }
    }
    if ($mail->Send()) {
        return true;
    } else {
        return  $mail->ErrorInfo;
    }
 }

 
 
////////////////////加密、解密////////////////////
/**
 * 加密
 * @todo    算法待选择
 * @param    string    $plainText    明文
 * @return    string    $cipherText    密文
 */
function encrypt($plainText)
{
    //import('ORG.Crypt.Base64');
    //$crypt = new Base64();
    $cipherText = '';
    $cipherText = base64_encode($plainText);
    return $cipherText;
}

/**
 * 解密
 * @todo    算法待选择
 * @param    string    $ciphertext    密文
 * @return    string    $plainText    明文
 */
function decrypt($cipherText)
{
    $plainText = '';
    $plainText = base64_decode($cipherText);
    return $plainText;
}



////////////////////图片、附件上传////////////////////


/**
 * 上传图片
 */
function uploadPic()
{
    import('ORG.Net.UploadFile');
    $upload = new UploadFile();// 实例化上传类
    $upload->maxSize  = 3145728 ;// 设置附件上传大小
    $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->savePath =  './data/attach/';// 设置附件上传目录
    //子目录配置
    $upload->autoSub = true;
    $upload->subType = 'custom';
    $upload->subDir = date('Ym').'/'.date('d').'/';
    //缩略图
    $upload->thumb = true;
    $upload->thumbPrefix = '';
    $upload->thumbSuffix = '_b,_m,_s';
    $upload->thumbMaxWidth = '800, 300, 150';
    $upload->thumbMaxHeight = '800, 300, 150';
    $result = $upload->upload();
    //错误提示
    $errorMsg = $upload->getErrorMsg();
    if ($errorMsg) {
        $arrError = array(
            'code'    => 'error',
            'message' => $errorMsg,
        );
        return $arrError;
    }
    //插入到图片表中
    $tmpList = $upload->getUploadFileInfo();
    foreach ($tmpList as $k=>$v) {
        $picList[$v['key']] = $v;
    }
    return $picList;
}


/**
 * 上传附件
 */
function uploadAttach()
{
    import('ORG.Net.UploadFile');
    $upload = new UploadFile();
    //附件大小
    $upload->maxSize  = 31457280 ;
    //附件类型
    $upload->allowExts  = array('pdf', 'jpg', 'gif', 'png', 'jpeg');
    //附件路径
    $upload->savePath =  './data/attach/';// 设置附件上传目录
    $upload->autoSub = true;
    $upload->subType = 'custom';
    $upload->subDir = date('Ym').'/'.date('d').'/';
    //上传
    $result = $upload->upload();
    //上传结果
    $tmpList = $upload->getUploadFileInfo();
    foreach ($tmpList as $k=>$v) {
        $attachList[$v['key']] = $v;
    }
    return $attachList;
}


/**
 * 获取图片完整路径
 */
function getPicPath($pic, $size)
{
    $path = '';
    if (!empty($pic)) {
        $picName =  substr($pic, 0, strrpos($pic, '.'));
        $picExtension = substr($pic, strrpos($pic, '.'));
        if ($size == 'b') {
            $path = $picName.'_b'.$picExtension;
        } else if ($size == 'm') {
            $path = $picName.'_m'.$picExtension;
        } else if ($size == 's') {
            $path = $picName.'_s'.$picExtension;
        } else {
            $path = $pic;
        }
        $path = './data/attach/'.$path;
        /* todo：检测一直失败，待修复
        if (!is_file($path)) {
            echo 'nnnn';
            $path = SITE_IMG_PATH.'/nopic.png';
        }
        */
    } else {
        $path = './public/images/nopic.jpg';
    }
    return $path;
}

/**
 * 获取附件路径
 */
function getAttachPath($attach)
{
    $path = '';
    if ($attch) {
        $path = SITE_ATTCH_PATH.$path;
    }
    return $path;
}


function upload()
{
	import('ORG.Net.UploadFile');
	$upload = new UploadFile();// 实例化上传类
	$upload->maxSize  = 3145728 ;// 设置附件上传大小
	$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','swf','pdf');// 设置附件上传类型
	$upload->savePath =  './data/attach/';// 设置附件上传目录
	//子目录配置
	$upload->autoSub = true;
	$upload->subType = 'custom';
	$upload->subDir = date('Ym').'/'.date('d').'/';
	//缩略图
	$upload->thumb = true;
	$upload->thumbPrefix = '';
	$upload->thumbSuffix = '_s';
	$upload->thumbMaxWidth = '800, 300, 150';
	$upload->thumbMaxHeight = '800, 300, 150';
	$result = $upload->upload();
	//插入到图片表中
	$tmpList = $upload->getUploadFileInfo();
	foreach ($tmpList as $k=>$v) {
		$fileList[$v['key']] = $v;
	}
	return $fileList;
}


////////////////////环境相关////////////////////

/**
 * 获取来路地址
 */
function getReferer()
{
    $referer = '';
    if (!empty($_GET['referer'])) {
        $referer = trim($_GET['referer']);
    } else {
        $referer = $_SERVER['HTTP_REFERER'];
    }
    //之前是登陆、注册页，直接跳转到个人中心
    if (strpos($referer, 'login') || strpos($referer, 'register') || $referer == '') {
        $referer = U('User/Index/index');
    }
    return $referer;
}

/**
 * 获取客户端ip地址
 */
function getClientIp()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
    return($ip);
}


/////////////////////////网络相关////////////////////



////////////////////数组相关////////////////////




////////////////////字符串相关的函数////////////////////
/**
 * 支持utf8中文字符截取
 * @author    肖飞
 * @param    string $text        待处理字符串
 * @param    int $start            从第几位截断
 * @param    int $sublen            截断几个字符
 * @param    string $code        字符串编码
 * @param    string $ellipsis    附加省略字符
 * @return    string
 */
function csubstr($string, $start = 0,$sublen=12, $ellipsis='',$code = 'UTF-8') {
    if($code == 'UTF-8') {
        $tmpstr = '';
        $i = $start;
        $n = 0;
        $str_length = strlen($string);//字符串的字节数
        while (($n+0.5<$sublen) and ($i<$str_length)) {
            $temp_str=substr($string,$i,1);
            $ascnum=Ord($temp_str);    //得到字符串中第$i位字符的ascii码
            if ($ascnum>=224) {        //如果ASCII位高与224，
                $tmpstr .= substr($string,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i=$i+3;            //实际Byte计为3
                $n++;                //字串长度计1
            }elseif ($ascnum>=192) { //如果ASCII位高与192，
                $tmpstr .= substr($string,$i,3); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i=$i+3;            //实际Byte计为2
                $n++;                //字串长度计1
            }else {                    //其他情况下，包括小写字母和半角标点符号，
                $tmpstr .= substr($string,$i,1);
                $i=$i+1;            //实际的Byte数计1个
                $n=$n+0.5;            //小写字母和半角标点等与半个高位字符宽...
            }
        }
        if(strlen($tmpstr)<$str_length ) {
            $tmpstr .= $ellipsis;//超过长度时在尾处加上省略号
        }
        return $tmpstr;
    }else {
        $strlen = strlen($string);
        if($sublen != 0) $sublen = $sublen*2;
        else $sublen = $strlen;
        $tmpstr = '';
        for($i=0; $i<$strlen; $i++) {
            if($i>=$start && $i<($start+$sublen)) {
                if(ord(substr($string, $i, 1))>129) $tmpstr.= substr($string, $i, 2);
                else $tmpstr.= substr($string, $i, 1);
            }
            if(ord(substr($string, $i, 1))>129) $i++;
        }
        if(strlen($tmpstr)<$strlen ) $tmpstr.= $ellipsis;
        return $tmpstr;
    }
}






////////////////////验证函数////////////////////

/**
 * email格式是否正确
 */
function isEmailVaild($email)
{
    return true;
}

/**
 * url格式是否正确
 */
function isUrlValid($url)
{
    return true;
}

/**
 * 手机号码格式
 */
function isMobileValid($mobile)
{
    return true;
}

/**
 * 身份证号码格式
 */
function isIdCardValid($idCard)
{
    return true;
}

/**
 * 邮编格式验证
 */
function isZipcodeValid($zipcode)
{
    return true;
}

/**
 * 昵称是否合法
 */
function isUnameVaild($uname)
{
    return true;
}
function getShort($str, $length = 40, $ext = '') {
    $str    =    htmlspecialchars($str);
    $str    =    strip_tags($str);
    $str    =    htmlspecialchars_decode($str);
    $strlenth    =    0;
    $out        =    '';
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
    foreach($match[0] as $v){
        preg_match("/[\xe0-\xef][\x80-\xbf]{2}/",$v, $matchs);
        if(!empty($matchs[0])){
            $strlenth    +=    1;
        }elseif(is_numeric($v)){
            //$strlenth    +=    0.545;  // 字符像素宽度比例 汉字为1
            $strlenth    +=    0.5;    // 字符字节长度比例 汉字为1
        }else{
            //$strlenth    +=    0.475;  // 字符像素宽度比例 汉字为1
            $strlenth    +=    0.5;    // 字符字节长度比例 汉字为1
        }

        if ($strlenth > $length) {
            $output .= $ext;
            break;
        }

        $output    .=    $v;
    }
    return $output;
}
/**
 * 删除图片
 */
function delImage($imgname)
{
	
	unlink(getPicPath($imgname, 'b'));
	unlink(getPicPath($imgname, 'm'));
	unlink(getPicPath($imgname, 's'));
	unlink(getPicPath($imgname));
}

/**
 * 订单编号
 */

function getOrderId(){
	$date = date('Ymd');
	$map['id'] = array('LIKE',"$date%");
	$current = D('OrderInfo')->where($map)->max('current');
	if($current){
	    $current +=1; 
		if($current<10){
			$exp = '0'.$current;
		}else{
			$exp = $current;
		}
	}else{
	     $current = 1;
		$exp ='01';
	}
	return array($date.$exp,$current);
}

/**
 * 输出用于测试的数据
 */
function cbug($content)
{
	if(is_array($content)){
		$content = serialize($content);
	}
    $data['user_id'] = $_SESSION['uid'];
	$data['info'] = $content;
	$data['date_add'] = time();
	D('DebugLog')->add($data);
}

/**
 * 递归搜索文件
 */

function find_all_files($dir) 
{ 
    $root = scandir($dir); 
    foreach($root as $value) { 
        if($value === '.' || $value === '..') {continue;} 
        if(is_file("$dir/$value")) {
            if(preg_match('/_[bms]{1}/', "$dir/$value")){continue;}
            else{$result[]="$dir/$value";continue;} 
        }
        foreach(find_all_files("$dir/$value") as $value) { 
            $result[]=$value; 
        } 
    } 
    return $result; 
} 
?>
