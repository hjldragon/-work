<?php
/*
自动售货机的设备管理
 */
namespace Pub\Vendor\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class FaultEntry extends \Pub\Mongodb\BaseInfo
{
    public $fault_id           = null;           // 故障id
    public $vendor_id          = null;           // 设备id
    public $shop_id            = null;           // 店铺Id
    public $fault_time         = null;           // 故障时间
    public $deal_time          = null;           // 处理时间
    public $send_time          = null;           // 派单时间
    public $deal_name          = null;           // 处理人姓名(产品定义:店铺处理人是所有售货机负责人不是员工,平台又是所有员。此处存名字)
    public $name_phone         = null;           // 处理人电话
    public $deal_status        = null;           // 是否处理(1.未处理,2.处理中,3.处理完成)
    public $deal_remark        = null;           // 处理描述
    public $is_send            = null;           // 是否派单(1.派单,0.待派单)
    public $is_inform          = null;           // 是否通知(1.通知,0.未通知)
    public $is_reminder        = null;           // 是否催单(1.已催单,0.未催单)
    public $lastmodtime        = null;           // 最后修改的时间
    public $delete             = null;           // 是否删除(0:未删除; 1:已删除)


    function __construct($cursor = null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }

        $this->fault_id        = $cursor['fault_id'];
        $this->vendor_id       = $cursor['vendor_id'];
        $this->shop_id         = $cursor['shop_id'];
        $this->is_send         = $cursor['is_send'];
        $this->fault_time      = $cursor['fault_time'];
        $this->deal_time       = $cursor['deal_time'];
        $this->send_time       = $cursor['send_time'];
        $this->deal_name       = $cursor['deal_name'];
        $this->name_phone      = $cursor['name_phone'];
        $this->deal_status     = $cursor['deal_status'];
        $this->is_inform       = $cursor['is_inform'];
        $this->is_reminder     = $cursor['is_reminder'];
        $this->deal_remark     = $cursor['deal_remark'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class Fault extends \Pub\Mongodb\MgoBase
{
    private function Tablename()
    {
        return 'fault_deal';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'fault_id'   => (string)$info->fault_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->vendor_id)
        {
            $set["vendor_id"] = (string)$info->vendor_id;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->is_send)
        {
            $set["is_send"] = (int)$info->is_send;
        }
        if(null !== $info->fault_time)
        {
            $set["fault_time"] = (int)$info->fault_time;
        }
        if(null !== $info->deal_time)
        {
            $set["deal_time"] = (int)$info->deal_time;
        }
        if(null !== $info->send_time)
        {
            $set["send_time"] = (int)$info->send_time;
        }
        if(null !== $info->deal_name)
        {
            $set["deal_name"] = (string)$info->deal_name;
        }
          if(null !== $info->name_phone)
          {
              $set["name_phone"] = (string)$info->name_phone;
          }
        if(null !== $info->deal_status)
        {
            $set["deal_status"] = (int)$info->deal_status;
        }
        if(null !== $info->deal_remark)
        {
            $set["deal_remark"] = (string)$info->deal_remark;
        }
        if(null !== $info->is_reminder)
        {
            $set["is_reminder"] = (int)$info->is_reminder;
        }
        if(null !== $info->is_inform)
        {
            $set["is_inform"] = (int)$info->is_inform;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    //获取列表数据
    public function GetAllList($filter=null, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $deal_status = $filter['deal_status'];
            if(!empty($deal_status))
            {
                $cond['deal_status'] = (int)$deal_status;
            }
            $is_send = $filter['is_send'];
            if(null != $is_send)
            {
                $cond['is_send'] = (int)$is_send;
            }
        }
        if(empty($sortby)){
            $sortby['fault_time'] = -1;
        }

        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return FaultEntry::ToList($cursor);
    }

    public function QueryById($fault_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'fault_id'  => (string)$fault_id,
            'delete'    => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new FaultEntry($ret);
    }

    //获取列表数据
    public function GetList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $vendor_id_list = $filter['vendor_id_list'];
            if(!empty($vendor_id_list))
            {
                foreach($vendor_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["vendor_id"] = ['$in' => $vendor_id_list];
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $deal_status = $filter['deal_status'];
            if(!empty($deal_status))
            {
                $cond['deal_status'] = (int)$deal_status;
            }
            $is_send = $filter['is_send'];
            if(null != $is_send)
            {
                $cond['is_send'] = (int)$is_send;
            }
        }
        if(empty($sortby)){
            $sortby['fault_time'] = -1;
        }

        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return FaultEntry::ToList($cursor);
    }

}


?>
