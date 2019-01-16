<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 员工登录信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoRedis;
require_once("db_pool.php");
require_once("redis_public.php");


# t_login
class EmployeeEntry
{
    public $employee_id = null;     //  员工id
    public $token       = null;     // 登录设备token


    function __construct($cursor=null)
    {
        $this->FromRedis($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromRedis($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->employee_id = $cursor['employee_id'];
        $this->token       = $cursor['token'];
        }
};

class Employee
{
    private function Tablename()
    {
        return DB_EMPLOYEE; // 注意各个表使用序号
    }

    public function Save($entry)
    {
        if(!$entry || null === $entry->employee_id)
        {
            LogDebug($entry);
            LogErr("param err");
            return -1;
        }
        $db = \DbPool::GetRedis($this->Tablename());
        $data = [];

        $data["employee_id"] = $entry->employee_id;

        if(null !== $entry->token)
        {
            $data["token"] = $entry->token;
        }
        $ret = $db->hmset($entry->employee_id, $data);
        if($ret < 0)
        {
            LogErr("hmset err");
            return $ret;
        }
        // 正确
        //$db->expire($entry->token, 60*60*24*7); // 暂时去掉，后台不过期
        return 0;
    }

    public function Get($employee_id)
    {
        try
        {
            $db = \DbPool::GetRedis($this->Tablename());
            $info = new EmployeeEntry();
            $ret = $db->hgetall($employee_id);
            LogDebug("employee_id:[$employee_id]");
            return new EmployeeEntry($ret);
        }
        catch(RedisException $e)
        {
            LogErr($e->getMessage());
        }
        catch(Exception $e)
        {
            LogErr($e->getMessage());
        }
    }
}


?>
