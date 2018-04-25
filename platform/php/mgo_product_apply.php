<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 代理商操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");

class ProductApplyEntry
{
    public $apply_id        = null;           // 申请id
    public $shop_name       = null;           // 商户名称
    public $apply_name      = null;           // 申请联系人
    public $telephone       = null;           // 联系电话
    public $address         = null;           // 地址
    public $city            = null;           // 城市
    public $province        = null;           // 省份
    public $email           = null;           // 邮箱
    public $product_status  = null;           // 试用产品(1.小程序,2.微信端,3.PC端)
    public $apply_time      = null;           // 申请时间
    public $apply_status    = null;           // 申请状态(1.申请中,0.试用中)
    public $lastmodtime     = null;           // 最后修改的时间
    public $delete          = null;           // 是否删除(0:未删除; 1:已删除)





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
        $this->shop_name       = $cursor['shop_name'];
        $this->apply_name      = $cursor['apply_name'];
        $this->telephone       = $cursor['telephone'];
        $this->address         = $cursor['address'];
        $this->province        = $cursor['province'];
        $this->city            = $cursor['city'];
        $this->email           = $cursor['email'];
        $this->product_status  = $cursor['product_status'];
        $this->apply_status    = $cursor['apply_status'];
        $this->apply_time      = $cursor['apply_time'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
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

class ProductApply
{
    private function Tablename()
    {
        return 'product_apply';
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

        if (null !== $info->shop_name) {
            $set["shop_name"] = (string)$info->shop_name;
        }
        if (null !== $info->apply_name) {
            $set["apply_name"] = (string)$info->apply_name;
        }
        if (null !== $info->telephone) {
            $set["telephone"] = (string)$info->telephone;
        }
        if (null !== $info->address) {
            $set["address"] = (string)$info->address;
        }
        if (null !== $info->province) {
            $set["province"] = (string)$info->province;
        }
        if (null !== $info->city) {
            $set["city"] = (string)$info->city;
        }
        if (null !== $info->email) {
            $set["email"] = (string)$info->email;
        }
        if (null !== $info->product_status) {
            $set["product_status"] = $info->product_status;
        }
        if (null !== $info->apply_status) {
            $set["apply_status"] = (int)$info->apply_status;
        }
        if (null !== $info->apply_time) {
            $set["apply_time"] = (int)$info->apply_time;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
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
            if(!empty($apply_status))
            {
                $cond['apply_status'] = (int)$apply_status;
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

}


?>
