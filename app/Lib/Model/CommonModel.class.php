<?php
/**
 * 通用模型类
 * 该模型中定义了一些通用的方法，子类中要根据情况覆盖这些方法
 * 所有数据表对应的模型都要继承CommonMode
 * @version 2013-06-11
 */
class CommonModel extends Model
{
    ////////////////////单条信息相关////////////////////
    /**
     * 获取信息
     */
    public function getInfo($field, $map, $order = array())
    {
        $tmpInfo = $info = array();
        //字段
        if (!empty($field)) {
            $this->field($field);
        }
        //条件
        if (!empty($map)) {
            $this->where($map);
        }
        //排序
        if (!empty($order)) {
            $this->order($order);
        }
        $tmpInfo = $this->find();
        if (!empty($tmpInfo)) {
            $info = $tmpInfo;
        }
        return $info;
    }
    
    /**
     * 根据id获取信息
     */
    public function getInfoById($id, $field = '*')
    {
        $map['id'] = array('eq', $id);
        $info = $this->getInfo($field, $map);
        return $info;
    }
    
    ////////////////////列表相关////////////////////
    /**
     * 通用列表
     */
    public function getList($field, $map, $order, $offset, $length)
    {
        $list = $tmpList = array();
        //filed
        $this->field($field);
        //map
        if (!empty($map)) {
            $this->where($map);
        }
        //order
        $this->order($order);
        
        //limit判断
        if ($offset > 0) {
            $this->limit($offset, $length);
        } else {
            $this->limit($length);
        }
        
        //列表
        $tmpList = $this->select();
        if (!empty($tmpList)) {
            foreach ($tmpList as $k=>$v) {
                $list[] = $v;
            }
        }
        return $list;
    }
    
    /**
     * 统计数据
     */
    public function getCount($map)
    {
        $count = 0;
        //map
        if (!empty($map)) {
            $this->where($map);
        }
        $count = $this->count();
        return $count;
    }

    /**
     * get parent_id for breadcrumbs
     * 获取所有层级关联数据ID
     */
    public function get_ids($id,$i=0)
    {
        //获取父级数据ID
        $pid[$i] = $this->where('id='.$id)->getField('parent_id');
        if(!empty($pid[$i])){
            //如果非空，则继续合并，获取
            $pid = array_merge($pid, $this->get_ids($pid[$i],$i+1));
        }
        return $pid;
    }


	
}
?>
