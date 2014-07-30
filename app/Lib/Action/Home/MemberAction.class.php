<?php
/**
* member management action
* @author chen
* @version 2014-03-18
*/
class MemberAction extends HomeAction
{
    /**
     * get the member list
    */
    public function memberList()
    {
        $map = array('user_id'=>$_SESSION['uid']);
        $fields = array('id','name','mobile','date_login');
        $page = page(D('Member')->getCount($map));

        $member_list = D('Member')->field($fields)->where($map)->order('date_login desc')->limit($page->firstRow,$page->listRows)->select();

        $fields_all = D('Member')->field_list();
        $data = array(
            'title' => '会员列表',
            'field_list' => $this->get_field_list($fields_all,$fields),
            'field_info' => $member_list,
            'page_list' => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
* view the member info
*/
    public function memberInfo()
    {
        $memberObj = D('Member');
        $member_id = $this->_get('member_id', 'intval');
        $memberInfo = $memberObj->getInfoById($member_id);
        $memberInfo = $memberObj->format($memberInfo, array('avatar_name'));
        $data = array(
            'memberInfo' => $memberInfo,
        );
        $this->assign($data);
        $this->display();
    }

    /**
     * member action list
     */
    public function actList()
    {
        $map = array('user_id', $_SESSION['uid']);
        $fields = array('id','member_id','action','date_create');
        $page = page(D('MemberAction')->getCount($map));
        
        $action_list = D('MemberAction')->field($fields)->where($map)->order('date_create')->limit($page->firstRow,$page->listRows)->select();
        $fields_all = D('MemberAction')->field_list();
        $data = array(
            'title' => '用户行为表',
            'field_list' => $this->get_field_list($fields_all, $fields),
            'field_info' => $action_list,
            'page_list'  => $page->show(),
        );
        $this->assign($data);
        $this->display('Public:list');
    }

    /**
     * 导出EXCEL表格
     */
    public function exportExcel()
    {
        /** Include PHPExcel */
        require_once './core/PHPExcel/PHPExcel.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("chen")
							 ->setLastModifiedBy("chen")
							 ->setTitle("会员信息列表")
							 ->setSubject("会员信息列表")
							 ->setDescription("会员信息列表");
        $memberList = D('Member')->where('user_id='.$_SESSION['uid'])
            ->field('id,wechat_id,name,mobile,email,date_reg,date_login')
            ->order('date_login desc')
            ->select();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '会员ID')
            ->setCellValue('B1', '微信号')
            ->setCellValue('C1', '昵称')
            ->setCellValue('D1', '手机号码')
            ->setCellValue('E1', '邮箱')
            ->setCellValue('F1', '注册时间')
            ->setCellValue('G1', '上次访问时间');
        foreach($memberList as $k=>$v){
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.($k+2), $v['id'])
                ->setCellValue('B'.($k+2), $v['wechat_id'])
                ->setCellValue('C'.($k+2), $v['name'])
                ->setCellValue('D'.($k+2), $v['mobile'])
                ->setCellValue('E'.($k+2), $v['email'])
                ->setCellValue('F'.($k+2), date('Y-m-d H:i',$v['date_reg']))
                ->setCellValue('G'.($k+2), date('Y-m-d H:i',$v['date_login']));
        }
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('会员信息列表');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="会员列表'.date('md').'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * member count chart
     */
    public function memberChart()
    {
        for($i=1, $dateList['1'] = time(); $i<=10; $i++){
            if($i != 1){
                $dateList[$i] = $dateList[$i-1] - '2629743';
            }
        }
        $lostMap['type'] = array('eq', 'event');
        $lostMap['info'] = array('eq', 'unsubscribe');
        $dateList = array_reverse($dateList);
        foreach($dateList as $k=>$v){
            $newMap['date_reg']
                = $lostMap['date_push']
                = array('between', array($v, $v+'2629743'));
            $countMap['date_reg'] = array('lt', $v);
            $newList[$k] = D('Member')->getCount($newMap);
            $lostList[$k] = D('MemberPush')->getCount($lostMap) - $lostList[$k];
            $countList[$k] = D('Member')->getCount($countMap);
            $dateList[$k] = date('Y-m', $v);
        }
    	$this->assign('dateList', json_encode($dateList));
    	$this->assign('newList', json_encode($newList));
    	$this->assign('lostList', json_encode($lostList));
    	$this->assign('countList', json_encode($countList));
		$this->display();
    }

    /**
     * push chart
     */
    public function pushChart()
    {
        $keywordList = D('WechatRoute')->where('user_id='.$_SESSION['uid'])->getField('keyword', true);
        $memberIds = D('Member')->where('user_id='.$_SESSION['uid'])->getField('id', true);
        foreach($keywordList as $k=>$v){
            $map['member_id'] = array('in', $memberIds);
            $map['info'] = array('eq', $v);
            $countList[$k] = D('MemberPush')->where($map)->count();
        }
        $this->assign('keywordList', json_encode($keywordList));
        $this->assign('countList', json_encode($countList));
        $this->display();
    }
}
