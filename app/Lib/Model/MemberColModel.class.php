<?php
/**
 * member event model
 * @author chen
 * @version 2014-03-18
 */
class MemberColModel extends CommonModel
{
    /**
     * add the event
     */
    public function addEvent($member_id, $event, $item_id, $item_name)
    {
        $data = array(
            'member_id' => $member_id,
            'event' => $event,
            'item_id' => $item_id,
            'item_name' => $item_name,
            'date_event' => time(),
        );
        if(!empty($member_id)){
            $event_id = $this->add($data);
                if(!empty($event_id)){
                    D('Item')->where('id='.$item_id)->setInc($event);
            }
            return $event_id;
        }
    }
}
