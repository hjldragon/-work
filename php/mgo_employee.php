<?php
/*
 * [Rocky 2017-06-17 20:46:34]
 * 员工表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");

class EmployeeEntry
{
    // public $employee_id = null;     // 员工id
    public $userid             = null;     // 用户id(同用户表)
    public $shop_id            = null;     // 餐馆店铺id
    public $real_name          = null;     // 员工姓名
    public $phone              = null;     // 手机号
    public $employee_no        = null;     // 员工工号
    public $duty               = null;     // 员工职务
    public $section            = null;     // 所属部门
    public $permission         = null;     // 员工权限(-->const.php-->EmployeePermission)
    public $lastmodtime        = null;     // 数据最后修改时间
    public $delete             = null;     // 0:正常, 1:已删除
    public $health_certificate = null;     // 健康证
    public $remark             = null;     // 备注
    public $is_freeze          = null;     // 0:正常, 1:已冻结

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

        // $this->employee_id = $cursor['employee_id'];
        $this->userid             = $cursor['userid'];
        $this->shop_id            = $cursor['shop_id'];
        $this->real_name          = $cursor['real_name'];
        $this->phone              = $cursor['phone'];
        $this->employee_no        = $cursor['employee_no'];
        $this->duty               = $cursor['duty'];
        $this->section            = $cursor['section'];
        $this->permission         = $cursor['permission'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
        $this->health_certificate = $cursor['health_certificate'];
        $this->remark             = $cursor['remark'];
        $this->is_freeze          = $cursor['is_freeze'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class Employee
{
    private function Tablename()
    {
        return 'employee';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$info->userid
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->real_name)
        {
            $set["real_name"] = (string)$info->real_name;
        }
        if(null !== $info->phone)
        {
            $set["phone"] = (string)$info->phone;
        }
        if(null !== $info->employee_no)
        {
            $set["employee_no"] = (string)$info->employee_no;
        }
        if(null !== $info->duty)
        {
            $set["duty"] = (int)$info->duty;
        }
        if(null !== $info->permission)
        {
            foreach($info->permission as $k => &$v)
            {
                $v = (int)$v;
            }
            $set["permission"] = $info->permission;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->section)
        {
            $set["section"] = (string)$info->section;
        }
        if(null !== $info->health_certificate)
        {
            $set["health_certificate"] = (string)$info->health_certificate;
        }
        if(null !== $info->remark)
        {
            $set["remark"] = (string)$info->remark;
        }
        if(null !== $info->is_freeze)
        {
            $set["is_freeze"] = (int)$info->is_freeze;
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

    public function BatchDelete($userid_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($userid_id_list as $i => &$item)
        {
            $item = (int)$item;
        }

        $set = array(
            "delete" => 1,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'userid' => ['$in' => $userid_id_list]
        ];
        LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetEmployeeById($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid' => (int)$userid,
        ];
        $cursor = $table->findOne($cond);
        return new EmployeeEntry($cursor);
    }


    public function GetEmployeeList($shop_id, $filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id
        ];
        if(null != $filter)
        {
            $userid = $filter['userid'];
            if(!empty($userid))
            {
                $cond['userid'] = (int)$userid;
            }
        }

        $cursor = $table->find($cond, ["_id"=>0]);
        return EmployeeEntry::ToList($cursor);
    }
    // 冻结/启用
    public function BatchFreeze($userid,$type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $set = array(
            "is_freeze" => (int)$type,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'userid' => $userid
        ];
        LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}


?>
