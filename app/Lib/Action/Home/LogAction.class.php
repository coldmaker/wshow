<?php
/**
 * File Name: VisitAction.class.php
 * Author: Blue
 * Created Time: 2013-11-26 11:21:07
*/
class LogAction extends WechatCommonAction{
	/**
	 * 访问列表
	 */
	public function logList(){
        $logObj = D('Log');
        $type = $this->_get('type');
        switch ($type){
            case 'visit':
                $arrMap['msgType'] = array('eq', 'visit');
                $title = '微网站访问记录';
                $left_current = 'visit';
                break;
            case 'api':
                $arrMap['msgType'] = array('neq', 'visit');
                $title = '接口访问记录';
                $left_current = 'api';
                break;
            case 'message':
                $arrMap['msgType'] = array('eq', 'text');
                $title = '消息记录';
                $left_current = 'message';
                break;
        }
		$arrMap['wechat_id'] = array('eq', $_SESSION['wechat_id']);
        //时间
        $dateList = array();
        $countList = array();
        for($i=1, $dateList['1'] = time(); $i<=10; $i++){
            if($i != 1){
                $dateList[$i] = $dateList[$i-1] - '2629743';
            }
            $arrMap['ctime'] = array('between', array($dateList[$i]-'2629743', $dateList[$i]));
            $countList[$i] = $logObj->getCount($arrMap);
            $count = $logObj->getCount($arrMap);
        }
        foreach($dateList as $k=>$v){
            $dateList[$k] = date('Y-m', $v);
        }
        $data = array(
            'labels' => array_reverse($dateList),
            'datasets' => array(
                array(
                'fillColor' => 'rgba(220,220,220,0.5)',
                'strokeColor' => 'rgba(220,220,220,1)',
                'pointColor' => 'rgba(220,220,220,1)',
                'pointStrokeColor' => '#fff',
                'data' => array_reverse($countList),
            ),
            ),
        );
        $data = json_encode($data);
		$tplData = array(
            'data' => $data,
			'title' => $title,
            'remarkUrl' => U('Admin/Follower/addRemark'),
            'delUrl'   => U('Admin/Log/doDelLog'),
			'pageHtml' => $pageHtml,
            'left_current' => $left_current,
		);
		$this->assign($tplData);
		$this->display();
	}

    /**
     * 删除
     */
    public function doDelLog(){
        $logObj = D('Log');
		$delIds = array();
		$postIds = $this->_post('id');
		if(!empty($postIds)){
			$delIds = $postIds;
		}
		$getId = $this->_get('id');
		if(!empty($getId)){
			$delIds[] = $getId;
		}
		if(empty($delIds)){
			$this->error('请选择您要删除的内容');
		}
		$map['id'] =  array('in', $delIds);
		if($logObj->where($map)->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
    }
}
 
