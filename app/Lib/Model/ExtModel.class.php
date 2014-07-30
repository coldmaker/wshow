<?php
/**
 * 扩展字段模型类
 * @author chen
 * @version 2014-03-14
 */
class ExtModel extends CommonModel
{
    /**
     * 获取字段列表和值
     */
    public function getExtVal($fid,$id, $type){
        $map = array('res_id'=>$fid,'res_type'=>$type);
        $list = $this->where($map)->order('sort_order')->select();
        foreach($list as $k=>$v){
            $map = array('res_id'=>$id,'ext_id'=>$v['id']);
            $ext_val = D('ExtVal')->where($map)->find();
            $list[$k]['ext_id'] = $v['id'];
            $list[$k]['id'] = $ext_val['id'];
            $list[$k]['value'] = $ext_val['value'];
        }
        return $list;
    }

    /**
     * update val
     * @param array2 $extList
     * @param int $res_id
     * @return int $id
     */
    public function updateExtVal($list, $res_id)
    {
        foreach($list as $k=>$v){
            $id = $v['id'];
            $v['date_modify'] = time();
            if(empty($id)){
                $v['res_id'] = $res_id;
                $v['date_add'] = time();
                $id = D('ExtVal')->add($v);
            }else{
                D('ExtVal')->save($v);
            }
        }
    }


    /**
     * 字段列表
     */
    public function field_list()
    {
        return array(
            array('title'=>'ID','name'=>'id','type'=>'hidden'),
            array('title'=>'资源类型','name'=>'res_type','type'=>'text'),
            array('title'=>'资源编号','name'=>'res_id','type'=>'text'),
            array('title'=>'名称','name'=>'title','type'=>'text'),
            array('title'=>'类型','name'=>'type','type'=>'text'),
            array('title'=>'标记','name'=>'lable','type'=>'text'),
            array('title'=>'排序','name'=>'sort_order','type'=>'number'),
            array('title'=>'操作','name'=>'action_list','type'=>'action_list'),
        );
    }

}
