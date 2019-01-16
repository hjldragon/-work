<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 代理商操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");

class AgentApplyEntry
{
    public $apply_id        = null;           // 申请id
    public $agent_type      = null;           // 代理类型(1:区域，2:行业)
    public $agent_level     = null;           // 代理级别(1:一级，2:二级，3:三级)
    public $agent_province  = null;           // 省
    public $agent_city      = null;           // 市
    public $agent_area      = null;           // 区
    public $wx_id           = null;           // 微信表中的id
    public $apply_name      = null;           // 申请联系人
    public $telephone       = null;           // 联系电话
    public $company         = null;           // 公司名称
    public $apply_time      = null;           // 申请时间
    public $lastmodtime     = null;           // 最后修改的时间
    public $delete          = null;           // 是否删除(0:未删除; 1:已删除)
    public $email           = null;           // 邮箱
    public $address         = null;           // 地址
    public $apply_status    = null;           // 申请状态(0:待处理,1:已处理)
    public $pl_employee_id  = null;           // 处理人id
    public $deal_message    = null;           // 处理信息
    public $deal_time       = null;           // 更进时间
    public $purpose         = null;           // 意向程度(1.高,2.中,3.低)




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

        $this->apply_id        = $cursor['apply_id'];
        $this->agent_type      = $cursor['agent_type'];
        $this->agent_level     = $cursor['agent_level'];
        $this->agent_province  = $cursor['agent_province'];
        $this->agent_city      = $cursor['agent_city'];
        $this->agent_area      = $cursor['agent_area'];
        $this->wx_id           = $cursor['wx_id'];
        $this->apply_name      = $cursor['apply_name'];
        $this->telephone       = $cursor['telephone'];
        $this->apply_time      = $cursor['apply_time'];
        $this->company         = $cursor['company'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
        $this->email           = $cursor['email'];
        $this->address         = $cursor['address'];
        $this->apply_status    = $cursor['apply_status'];
        $this->pl_employee_id  = $cursor['pl_employee_id'];
        $this->deal_message    = $cursor['deal_message'];
        $this->deal_time       = $cursor['deal_time'];
        $this->purpose         = $cursor['purpose'];

    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new agentApplyEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }

}

class AgentApply
{
    private function Tablename()
    {
        return 'agent_apply';
    }

    public function Save(&$info)
    {
        if (!$info->apply_id)
        {
            LogErr("param err:" . json_encode($info));
            return \errcode::PARAM_ERR;
        }
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'apply_id' => (string)$info->apply_id
        );

        $set = array(
            "apply_id" => (string)$info->apply_id,
            "lastmodtime" => time()
        );

        if (null !== $info->agent_type) {
            $set["agent_type"] = (int)$info->agent_type;
        }
        if (null !== $info->agent_level) {
            $set["agent_level"] = (int)$info->agent_level;
        }
        if (null !== $info->agent_province) {
            $set["agent_province"] = (string)$info->agent_province;
        }
        if (null !== $info->agent_city) {
            $set["agent_city"] = (string)$info->agent_city;
        }
        if (null !== $info->agent_area) {
            $set["agent_area"] = (string)$info->agent_area;
        }
        if (null !== $info->wx_id) {
            $set["wx_id"] = (string)$info->wx_id;
        }

        if (null !== $info->apply_name) {
            $set["apply_name"] = (string)$info->apply_name;
        }
        if (null !== $info->telephone) {
            $set["telephone"] = (string)$info->telephone;
        }
        if (null !== $info->apply_time) {
            $set["apply_time"] = (int)$info->apply_time;
        }
        if (null !== $info->company) {
            $set["company"] = (string)$info->company;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->email) {
            $set["email"] = (string)$info->email;
        }
        if (null !== $info->address) {
            $set["address"] = (string)$info->address;
        }
        if (null !== $info->apply_status) {
            $set["apply_status"] = (int)$info->apply_status;
        }
        if (null !== $info->pl_employee_id) {
            $set["pl_employee_id"] = (string)$info->pl_employee_id;
        }
        if (null !== $info->deal_message) {
            $set["deal_message"] = (string)$info->deal_message;
        }
        if (null !== $info->deal_time) {
            $set["deal_time"] = (int)$info->deal_time;
        }
        if (null !== $info->purpose) {
            $set["purpose"] = (int)$info->purpose;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . $ret['ok']);
        } catch (\MongoCursorException $e) {

            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    // 返回 agentEntry
    public function GetAgentApplyList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        //搜索功能
        if(null != $filter)
        {
            $apply_status = $filter['apply_status'];
            if(null != $apply_status)
            {
                $cond['apply_status'] = (int)$apply_status;
            }
            $telephone = $filter['telephone'];
            if(null != $telephone)
            {
                $cond['telephone'] = (string)$telephone;
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['apply_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AgentApplyEntry::ToList($cursor);
    }

    public function BatchChangeById($apply_id_list, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($apply_id_list as $i => &$id)
        {
            $id = (string)$id;
        }

        $cond = array(
            'apply_id' => array('$in' => $apply_id_list)
        );
         if(1 == $type)
         {
             $set = array(
                 "lastmodtime" => time(),
                 "delete"      => 1,
             );
         }
         if(2 == $type)
         {
             $set = array(
                 "lastmodtime"  => time(),
                 "apply_status" => 1,
             );
         }
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryByParam($telephone, $company)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'telephone' => (string)$telephone,
            'company'   => (string)$company,
             'delete'   => 0,
        );

        $ret = $table->findOne($cond);
        return new AgentApplyEntry($ret);
    }

    public function GetInfoById($apply_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'apply_id' => (string)$apply_id,
            'delete'   => ['$ne'=>1]
        ];
        $cursor = $table->findOne($cond);
        return new AgentApplyEntry($cursor);
    }

    public function GetInfoByWxIdList($filter=null,$sortby=[])
    {
        $db    = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond  = [
            'delete'  => ['$ne'=>1],
        ];
        if (null != $filter)
        {
            $wx_id = $filter['wx_id'];
            if (null !== $wx_id)
            {
                $cond['wx_id'] = (string)$wx_id;
            }

        }
        if (empty($sortby))
        {
            $sortby['_id'] = -1;
        }
        LogDebug($cond);
        $field["_id"] = 0;
        $cursor       = $table->find($cond,$field)->sort($sortby);
        return AgentApplyEntry::ToList($cursor);
    }
}


?>
