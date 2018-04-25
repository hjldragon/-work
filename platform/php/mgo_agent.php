<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 代理商操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");


class AgentBusiness
{
    public $company_name            = null;    //企业名称
    public $legal_person            = null;    //法人代表
    public $legal_card              = null;    //法人身份证
    public $legal_card_photo        = null;    //法人身份照片
    public $business_num            = null;    //营业执照注册号
    public $business_date           = null;    //营业期限
    public $business_photo          = null;    //营业执照照片
    public $business_scope          = null;    //经营范围

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
        $this->company_name           = $cursor['company_name'];
        $this->legal_person           = $cursor['legal_person'];
        $this->legal_card             = $cursor['legal_card'];
        $this->legal_card_photo       = $cursor['legal_card_photo'];
        $this->business_num           = $cursor['business_num'];
        $this->business_date          = $cursor['business_date'];
        $this->business_photo         = $cursor['business_photo'];
        $this->business_scope         = $cursor['business_scope'];
    }

}

class AgentEntry
{
    public $agent_id        = null;           // 代理商id
    public $parent_id       = null;           // 父级id
    public $agent_type      = null;           // 代理类型(1:区域，2:行业)
    public $agent_level     = null;           // 代理级别(1:一级，2:二级，3:三级)
    public $agent_name      = null;           // 代理商名称
    public $telephone       = null;           // 联系电话
    public $ctime           = null;           // 创建时间
    public $lastmodtime     = null;           // 最后修改的时间
    public $delete          = null;           // 是否删除(0:未删除; 1:已删除)
    public $email           = null;           // 邮箱
    public $address         = null;           // 地址
    public $agent_province  = null;           // 省
    public $agent_city      = null;           // 市
    public $agent_area      = null;           // 区
    public $from            = null;           // 来源
    public $from_salesman   = null;           // 所属销售
    public $is_freeze       = null;           // 是否冻结(0:否，1:是)
    public $agent_business  = null;           // 工商信息
    public $business_status = null;           // 工商信息认证状态(0:未提交,1:认证中,2:认证通过,3:认证不通过)
    public $business_time   = null;           // 工商信息审核时间
    public $bs_submit_time  = null;           // 工商信息提交时间


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
        $this->agent_id        = $cursor['agent_id'];
        $this->parent_id       = $cursor['parent_id'];
        $this->agent_type      = $cursor['agent_type'];
        $this->agent_level     = $cursor['agent_level'];
        $this->agent_name      = $cursor['agent_name'];
        $this->telephone       = $cursor['telephone'];
        $this->ctime           = $cursor['ctime'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
        $this->email           = $cursor['email'];
        $this->address         = $cursor['address'];
        $this->agent_province  = $cursor['agent_province'];
        $this->agent_city      = $cursor['agent_city'];
        $this->agent_area      = $cursor['agent_area'];
        $this->from            = $cursor['from'];
        $this->from_salesman   = $cursor['from_salesman'];
        $this->is_freeze       = $cursor['is_freeze'];
        $this->agent_business  = new AgentBusiness($cursor['agent_business']);
        $this->business_status = $cursor['business_status'];
        $this->business_time   = $cursor['business_time'];
        $this->bs_submit_time  = $cursor['bs_submit_time'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new agentEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }

    //  const PROP_IS_ADMIN = 1; // 是管事员
}

class Agent
{
    private function Tablename()
    {
        return 'agent';
    }

    public function Save(&$info)
    {
        if (!$info->agent_id)
        {
            LogErr("param err:" . json_encode($info));
            return \errcode::PARAM_ERR;
        }
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$info->agent_id
        );

        $set = array(
            "agent_id" => (string)$info->agent_id,
            "lastmodtime" => time()
        );

        if (null !== $info->parent_id) {
            $set["parent_id"] = (string)$info->parent_id;
        }
        if (null !== $info->agent_type) {
            $set["agent_type"] = (int)$info->agent_type;
        }
        if (null !== $info->agent_level) {
            $set["agent_level"] = (int)$info->agent_level;
        }
        if (null !== $info->agent_name) {
            $set["agent_name"] = (string)$info->agent_name;
        }
        if (null !== $info->telephone) {
            $set["telephone"] = (string)$info->telephone;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
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
        if (null !== $info->agent_province) {
            $set["agent_province"] = (string)$info->agent_province;
            if (null !== $info->agent_city) {
                $set["agent_city"] = (string)$info->agent_city;
            }
            else
            {
                $set["agent_city"] = '';
            }
            if (null !== $info->agent_area) {
                $set["agent_area"] = (string)$info->agent_area;
            }
            else
            {
                $set["agent_area"] = '';
            }
        }
        
        if (null !== $info->from) {
            $set["from"] = (string)$info->from;
        }
        if (null !== $info->from_salesman) {
            $set["from_salesman"] = (string)$info->from_salesman;
        }
        if (null !== $info->is_freeze) {
            $set["is_freeze"] = (int)$info->is_freeze;
        }
        if (null !== $info->agent_business){
            $set["agent_business"] = new ShopBusiness([
                'company_name'           => (string)$info->agent_business->company_name,
                'legal_person'           => (string)$info->agent_business->legal_person,
                'legal_card'             => (string)$info->agent_business->legal_card,
                'legal_card_photo'       => $info->agent_business->legal_card_photo,
                'business_date'          => $info->agent_business->business_date,
                'business_num'           => (string)$info->agent_business->business_num,
                'business_photo'         => (string)$info->agent_business->business_photo,
                'business_scope'         => (string)$info->agent_business->business_scope,
            ]);
        }
        if (null !== $info->business_status) {
            $set["business_status"] = (int)$info->business_status;
        }
        if ((int)$info->business_time > 0) {
            $set["business_time"] = (int)$info->business_time;
        }
        if ((int)$info->bs_submit_time > 0) {
            $set["bs_submit_time"] = (int)$info->bs_submit_time;
        }
        //LogDebug($set);
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
    public function QueryById($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$agent_id,
            'delete'   => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new AgentEntry($ret);
    }

    // 返回 agentEntry
    public function QueryByOpenidShopid($openid, $shop_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'openid' => $openid,
            'shop_id' => $shop_id,
            //'delete' => array('$ne'=>1)
        );

        $ret = $table->findOne($cond);
        return new AgentEntry($ret);
    }

    public function GetAgentByCity($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'agent_type' => 1
        ];
        $agent_area = $filter['agent_area'];
        $agent_city = $filter['agent_city'];
        $agent_province = $filter['agent_province'];
        if(!empty($agent_area))
        {
            $cond['agent_area']     = (string)$agent_area;
            $cond['agent_city']     = (string)$agent_city;
            $cond['agent_province'] = (string)$agent_province;
        }
        else
        {
            $cond['agent_area'] = ['$in'=> [null,"",0]];
            
            if(!empty($agent_city))
            {
                $cond['agent_city']     = (string)$agent_city;
                $cond['agent_province'] = (string)$agent_province;
            }
            else
            {
                $cond['agent_city'] = ['$in'=> [null,"",0]];
                
                if(!empty($agent_province))
                {
                    $cond['agent_province'] = (string)$agent_province;
                }
            }
        }
        $cursor = $table->findOne($cond);
        return new AgentEntry($cursor);
    }

    public function GetAgentList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        //搜索功能
        if(null != $filter)
        {
            $agent_type = $filter['agent_type'];
            if(!empty($agent_type))
            {
                $cond['agent_type'] = (int)$agent_type;
            }
            $agent_level = $filter['agent_level'];
            if(!empty($agent_level))
            {
                $cond['agent_level'] = (int)$agent_level;
            }
            $agent_name = $filter['agent_name'];
            if(!empty($agent_name))
            {
                $cond['agent_name'] = new \MongoRegex("/$agent_name/");
            }
            $business_status = $filter['business_status'];
            if(!empty($business_status))
            {
                $cond['business_status'] = (int)$business_status;
            }

            $agent_province = $filter['agent_province'];
            if(!empty($agent_province))
            {
                $cond['agent_province'] = (string)$agent_province;
            }
            $agent_city = $filter['agent_city'];
            if(!empty($agent_city))
            {
                $cond['agent_city'] = (string)$agent_city;
            }
            $agent_area = $filter['agent_area'];
            if(!empty($agent_area))
            {
                $cond['agent_area'] = (string)$agent_area;
            }
            
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['ctime'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }

            $begin_bs_time = $filter['begin_bs_time'];
            $end_bs_time   = $filter['end_bs_time'];
            if(!empty($begin_bs_time))
            {
                $cond['business_time'] = [
                    '$gte' => (int)$begin_bs_time,
                    '$lte' => (int)$end_bs_time
                ];
            }

            $begin_bsub_time = $filter['begin_bsub_time'];
            $end_bsub_time   = $filter['end_bsub_time'];
            if(!empty($begin_bsub_time))
            {
                $cond['bs_submit_time'] = [
                    '$gte' => (int)$begin_bsub_time,
                    '$lte' => (int)$end_bsub_time
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
        return AgentEntry::ToList($cursor);
    }

    public function BatchDeleteById($agent_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($agent_id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $cond = array(
            'agent_id' => array('$in' => $agent_id_list)
        );
        $set = array(
            "lastmodtime" => time(),
            "delete"      => 1
        );
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

    public function GetAgentByType($agent_type,&$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_type' => (int)$agent_type,
            'delete'     => array('$ne'=>1)
        );
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AgentEntry::ToList($cursor);
    }

    public function GetAllAgent($filter = null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = ['delete' => ['$ne' => 1]];
        if (null != $filter) {
            $agent_id = $filter['agent_id'];
            if (!empty($agent_id)) {
                $cond['agent_id'] = (string)$agent_id;
            } else {
                $agent_name = $filter['agent_name'];
                if (!empty($agent_name)) {
                    $cond['agent_name'] = new \MongoRegex("/$agent_name/");
                }
            }
        }
        $cursor = $table->find($cond, ["_id" => 0])->sort(["agent_name" => 1]);
        return AgentEntry::ToList($cursor);
    }

    public function GetByBusinessStatus($business_status, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'business_status' => (int)$business_status,
            'delete'          => ['$ne' => 1]
        );

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return  AgentEntry::ToList($cursor);
    }
}


?>
