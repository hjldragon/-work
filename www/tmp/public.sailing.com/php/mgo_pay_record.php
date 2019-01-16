<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 餐桌位置表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class PayRecordEntry extends BaseInfo
{
    public $record_id       = null; // 充值订单id
    public $agent_id        = null; // 代理商id
    public $ctime           = null; // 充值时间
    public $record_money    = null; // 充值金额
    public $pay_money       = null; // 实际支付金额
    public $pay_way         = null; // 支付方式(0.待确定,1.支付宝,2.微信,3.网银支付)
    public $pay_status      = null; // 支付状态(0.待充值,1.充值成功,2.充值失败)
    public $pay_time        = null; // 支付时间
    public $delete          = null; // 0:正常, 1:已删除
    public $lastmodtime     = null; //

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    private function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->record_id     = $cursor['record_id'];
        $this->agent_id      = $cursor['agent_id'];
        $this->ctime         = $cursor['ctime'];
        $this->record_money  = $cursor['record_money'];
        $this->pay_money     = $cursor['pay_money'];
        $this->pay_way       = $cursor['pay_way'];
        $this->pay_status    = $cursor['pay_status'];
        $this->pay_time      = $cursor['pay_time'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->delete        = $cursor['delete'];

    }
}
class PayRecord extends MgoBase
{
    protected function Tablename()
    {
        return 'pay_record';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'record_id' => (string)$info->record_id,
            'agent_id'  => (string)$info->agent_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }

        if(null !== $info->record_money)
        {
            $set["record_money"] = (float)$info->record_money;
        }

        if(null !== $info->pay_money)
        {
            $set["pay_money"] = (float)$info->pay_money;
        }

        if(null !== $info->pay_way)
        {
            $set["pay_way"] = (int)$info->pay_way;
        }

        if(null !== $info->pay_status)
        {
            $set["pay_status"] = (int)$info->pay_status;
        }

        if(null !== $info->pay_time)
        {
            $set["pay_time"] = (int)$info->pay_time;
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

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetInfoByRecordId($record_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'record_id' => (string)$record_id,
            'delete'   => 0,
        ];
        $cursor = $table->findOne($cond);
        return new PayRecordEntry($cursor);
    }

    public function GetPayRecordList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $agent_id = $filter['agent_id'];
            if(isset($agent_id))
            {
                $cond['agent_id'] = (string)$agent_id;
            }
            $pay_status = $filter['pay_status'];
            if(isset($pay_status))
            {
                $cond['pay_status'] = (int)$pay_status;
            }
            $pay_way = $filter['pay_way'];
            if(isset($pay_way))
            {
                $cond['pay_way'] = (int)$pay_way;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return PayRecordEntry::ToList($cursor);
    }

    public function QueryByDayAll($filter=null, &$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'delete'      => array('$ne'=>1)
        );

        if (null != $filter) {
            $pay_status = $filter['pay_status'];
            if (null !== $pay_status) {
                $cond['pay_status'] = (int)$pay_status;
            }
            $agent_id = $filter['agent_id'];
            if (null !== $agent_id) {
                $cond['agent_id'] = (string)$agent_id;
            }
            $begin_day = $filter['begin_day'];
            $end_day   = $filter['end_day'];
            if (!empty($begin_day)) {
                $cond['pay_time'] = [
                    '$gte' => (int)$begin_day,
                    '$lte' => (int)$end_day,
                ];
            }
        }
        //聚合条件算出
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'           => null,
                    'pay_money'     => ['$sum' => '$pay_money'],
                    'record_money'  => ['$sum' => '$record_money'],
                ],
            ],
        ];
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $num_all = $all_list['result'][0];
        } else {
            $num_all = null;
        }
        $ret = $table->find($cond);
        return PayRecordEntry::ToList($ret);
    }
}






?>
