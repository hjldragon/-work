<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 支付密码出错统计操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");
//支付时需要您输入账户的支付密码，如果您连续输错支付密码三次，密码会被锁定，不过三小时后会自动解锁。连续的定义是一天内连续输入三次，过了一天后按第一次算起
class AccErrNumEntry extends BaseInfo
{
    public $agent_id    = null;     // 代理商ID
    public $err_num     = null;     // 当天输入密码出错数
    public $err_time    = null;     // 出错最后时间(时间戳)
    public $lastmodtime = null;     // 数据最后修改时间
    public $delete      = null;     // 0:正常, 1:已删除

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
        $this->agent_id    = $cursor['agent_id'];
        $this->err_num     = $cursor['err_num'];
        $this->err_time    = $cursor['err_time'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete      = $cursor['delete'];
    }
}

class AccErrNum extends MgoBase
{
    private function Tablename()
    {
        return 'acc_err_num';
    }
    //错误次数加1
    public function SellNumAdd($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $time = time();
        $cond = array(
            'agent_id' => (string)$agent_id
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => $time,
                'err_time'    => $time
            ),
            '$inc' => array(
                'err_num' => 1
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
    //清空次数
    public function SellNumEmpty($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $time = time();
        $cond = array(
            'agent_id' => (string)$agent_id
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => $time,
                'err_num' => 0
            )
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

    public function GetErrNumByAgent($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_id' => (string)$agent_id
        ];
        $cursor = $table->findOne($cond);
        return new AccErrNumEntry($cursor);
    }

}


?>
