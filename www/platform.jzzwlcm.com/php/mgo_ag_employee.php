<?php
/*
 * [Rocky 2017-06-17 20:46:34]
 * 代理商员工表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");

class AGEmployeeEntry
{
    public $ag_employee_id   = null;     // 代理商员工id
    public $userid           = null;     // 用户id(同用户表)
    public $agent_id         = null;     // 代理商id
    public $real_name        = null;     // 员工姓名
    //public $phone            = null;     // 联系电话
    public $ag_position_id   = null;     // 代理商员工职务id
    public $ag_department_id = null;     // 所属部门id
    public $permission       = null;     // 代理商员工权限(-->const.php-->EmployeePermission)
    public $lastmodtime      = null;     // 数据最后修改时间
    public $delete           = null;     // 0:正常, 1:已删除
    public $is_freeze        = null;     // 0:正常, 1:已冻结
    public $is_admin         = null;     // 是否是管理员(0:员工,1:系统管理员)
    public $remark           = null;     // 备注
    public $entry_time       = null;     // 创建时间
    public $from_record      = null;     // 来源历史输入记录
    public $salesman_record  = null;     // 所属销售历史输入记录

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

        $this->ag_employee_id   = $cursor['ag_employee_id'];
        $this->userid           = $cursor['userid'];
        $this->agent_id         = $cursor['agent_id'];
        $this->real_name        = $cursor['real_name'];
        //$this->phone            = $cursor['phone'];
        $this->ag_position_id   = $cursor['ag_position_id'];
        $this->ag_department_id = $cursor['ag_department_id'];
        $this->permission       = $cursor['permission'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        $this->remark           = $cursor['remark'];
        $this->is_freeze        = $cursor['is_freeze'];
        $this->entry_time       = $cursor['entry_time'];
        $this->is_admin         = $cursor['is_admin'];
        $this->from_record      = $cursor['from_record'];
        $this->salesman_record  = $cursor['salesman_record'];
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

class AGEmployee
{
    private function Tablename()
    {
        return 'ag_employee';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'ag_employee_id' => (string)$info->ag_employee_id
        );

        $set = array(
            //'userid' => (int)$info->userid,
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }
        if(null !== $info->real_name)
        {
            $set["real_name"] = (string)$info->real_name;
        }
        if(null !== $info->ag_employee_id)
        {
            $set["ag_employee_id"] = (string)$info->ag_employee_id;
        }
        // if(null !== $info->phone)
        // {
        //     $set["phone"] = (string)$info->phone;
        // }
        if(null !== $info->ag_position_id)
        {
            $set["ag_position_id"] = (string)$info->ag_position_id;
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
        if(null !== $info->ag_department_id)
        {
            $set["ag_department_id"] = (string)$info->ag_department_id;
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
        if (null !== $info->entry_time) {
            $set["entry_time"] = (int)$info->entry_time;
        }
        if (null !== $info->userid) {
            $set["userid"] = (int)$info->userid;
        }
        if (null !== $info->is_admin) {
            $set["is_admin"] = (int)$info->is_admin;
        }
        if (null !== $info->from_record) {
            $push["from_record"] = (string)$info->from_record;
        }
        if (null !== $info->salesman_record) {
            $push["salesman_record"] = (string)$info->salesman_record;
        }
        $value = array(
            '$set' => $set
        );
        if($push)
        {
            $value['$push'] = $push;
        }
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

    public function BatchDelete($ag_employee_id,$agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $set = array(
            "delete"      => 1,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            //'userid' => ['$in' => $userid_id_list]
            "agent_id"        => $agent_id,
            'ag_employee_id'  => $ag_employee_id
        ];
        LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
    //代理商删除后所属员工删除
    public function BatchDeleteByAgent($agent_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        foreach($agent_id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $set = array(
            "delete"      => 1,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'agent_id' => array('$in' => $agent_id_list)
        ];
        LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetEmployeeByAgentId($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'  => ['$ne'=>1],
            'agent_id' => (string)$agent_id,
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        return AGEmployeeEntry::ToList($cursor);
    }

    public function GetAdminByAgentId($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'   => ['$ne'=>1],
            'is_admin' => 1,
            'agent_id' => (string)$agent_id,
        ];
        $cursor = $table->findOne($cond);
        return new AGEmployeeEntry($cursor);
    }

    public function GetEmployeeInfo($agent_id,$ag_employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_id' => (string)$agent_id,
            'ag_employee_id'=>(string)$ag_employee_id,
            'delete' => ['$ne' => 1]
        ];
        $cursor = $table->findOne($cond);
        return new AGEmployeeEntry($cursor);
    }
   
    public function QueryByUserId($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid'  => (int)$userid,
            'delete'  => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new AGEmployeeEntry($ret);
    }
    public function GetEmployeeList($agent_id, $filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
            'is_admin' => ['$ne' => 1],
            'agent_id'  => (string)$agent_id,
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
        return AGEmployeeEntry::ToList($cursor);
    }
    //通过部门ID获取所有代理商员工
    public function GetDepartmentEmployee($agent_id,$ag_department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'agent_id' => (string)$agent_id,
            'ag_department_id'=>(string)$ag_department_id
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        return AGEmployeeEntry::ToList($cursor);
    }
   
    public function QueryById($ag_employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'ag_employee_id' => (string)$ag_employee_id,
            'delete'        => ['$ne' => 1],
        ];

        $cursor = $table->findOne($cond);
        return new AGEmployeeEntry($cursor);
    }
        
    // 冻结/启用
    public function BatchFreeze($ag_employee_id, $agent_id, $is_freeze)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'ag_employee_id' => $ag_employee_id
        ];
        $set = array(
            "is_freeze"    => (int)$is_freeze,
            "agent_id"     => (string)$agent_id,
            "lastmodtime"  => time(),
        );
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryUser($employee_no, $real_name, $phone, $email)
    {
        $db     = \DbPool::GetMongoDb();
        $table  = $db->selectCollection($this->Tablename());
        $cond   = [
            'delete' => ['$ne' => 1],
            '$or'    => [
                ["employee_no" => (string)$employee_no],
                ["real_name" => (string)$real_name],
                ["phone" => (string)$phone],
                ["email" => (string)$email],
            ],
        ];
        $cursor = $table->find($cond, ["_id" => 0]);
        return AGEmployeeEntry::ToList($cursor);
    }
    //通过部门ID获取所有员工
    public function GetDepartmentPlatformer($ag_department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'          => ['$ne'=>1],
            'ag_department_id'=>(string)$ag_department_id
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        return AGEmployeeEntry::ToList($cursor);
    }
    //通过平台部门下所有员工
    public function GetPositionAgEmployee($ag_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'          => ['$ne'=>1],
            'ag_position_id'=>(string)$ag_position_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return AGEmployeeEntry::ToList($cursor);
    }
    //可以筛选的代理商员工
    public function GetAgEmployeeList($agent_id, $filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'    => ['$ne'=>1],
            'agent_id'  => $agent_id,
            'is_admin'  => ['$ne'=>1]
        ];
        //搜索功能
        if(null != $filter)
        {
            $is_freeze = $filter['is_freeze'];
            if($is_freeze != null)
            {   
                if($is_freeze != 1)
                {
                    $cond['is_freeze'] = ['$ne'=>1];
                }
                else
                {
                    $cond['is_freeze'] = 1;
                }
                
            }
            $real_name = $filter['real_name'];
            if(!empty($real_name))
            {
                $cond['real_name'] = new \MongoRegex("/$real_name/");
            }
            $ag_department_id = $filter['ag_department_id'];
            if(!empty($ag_department_id))
            {
                $cond['ag_department_id'] = (string)$ag_department_id;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }

        //LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AGEmployeeEntry::ToList($cursor);
    }
}


?>
