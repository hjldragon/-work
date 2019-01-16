<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 代理商充值统计
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class StatPayRecordEntry extends BaseInfo
{
    public $day                     = null;  // 日期(如: int(20170622))
    public $platform_id             = null;  // 平台ID
    public $agent_type              = null;  // 代理类型(1:区域，2:行业)
    public $money                   = null;  // 充值金额
    public $lastmodtime             = null;  // 数据最后修改时间
    public $delete                  = null;  // 0:正常, 1:已删除

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->day                     = $cursor['day'];
        $this->platform_id             = $cursor['platform_id'];
        $this->agent_type              = $cursor['agent_type'];
        $this->money                   = $cursor['money'];
        $this->lastmodtime             = $cursor['lastmodtime'];
        $this->delete                  = $cursor['delete'];

    }

}

class StatPayRecord extends MgoBase
{
    private function Tablename()
    {
        return 'stat_pay_record_byday';
    }

    public function SellNumAdd($platform_id, $day, $agent_type, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'day'         => (int)$day,
            'agent_type'  => (int)$agent_type,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'money' => (float)$num['money'],
            ),
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
        return $ret;
    }

    public function GetFoodStatByDay($platform_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'platform_id' => (string)$platform_id,
            'day'         => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatPayRecordEntry($cursor);
    }

    public function GetFoodStatByTime($platform_id, $start, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'platform_id' => (string)$platform_id,
            'day' => [
                '$gte' => (int)$start,
                '$lte' => (int)$day
            ]
        ];
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'               => null,
                    'all_money'  => ['$sum' => '$money']
                ],
            ],
        ];
        //LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            return $all_list['result'][0];
        } else {
            return null;
        }
    }

    public function QueryByDayAll($filter=null ,$platform_id, &$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'delete'      => array('$ne'=>1)
        );

        if (null != $filter) {
            $day = $filter['day'];
            if (null !== $day) {
                $cond['day'] = (int)$day;
            }
            $agent_type = $filter['agent_type'];
            if (null !== $agent_type) {
                $cond['agent_type'] = (int)$agent_type;
            }
            $begin_day = $filter['begin_day'];
            $end_day   = $filter['end_day'];
            if (!empty($begin_day)) {
                $cond['day'] = [
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
                    '_id'    => null,
                    'money'  => ['$sum' => '$money'],
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
        return StatPayRecordEntry::ToList($ret);
    }
}


?>
