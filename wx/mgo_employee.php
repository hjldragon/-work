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
    public $employee_id        = null;     // 员工id
    public $userid             = null;     // 用户id(同用户表)
    public $shop_id            = null;     // 餐馆店铺id
    public $real_name          = null;     // 员工姓名
    public $phone              = null;     // 手机号
    public $position_id        = null;     // 员工职务id
    public $department_id      = null;     // 所属部门id
    public $permission         = null;     // 员工权限(-->const.php-->EmployeePermission)
    public $lastmodtime        = null;     // 数据最后修改时间
    public $delete             = null;     // 0:正常, 1:已删除
    public $is_freeze          = null;     // 0:正常, 1:已冻结
    public $is_admin           = null;     // 是否是管理员(0:员工,1:系统管理员)
    public $remark             = null;     // 备注
    public $entry_time         = null;     // 创建时间
    public $authorize          = null;     // 登录授权(位运算1:商家运营平台 ,2:平板智能点餐机,4:智能收银机,8:掌柜通,16.自助点餐机)
    //public $identity           = null;     // 身份证号
    //public $sex                = null;     // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    //public $email              = null;     // 用户邮箱
    //public $health_certificate = null;     // 健康证
    //public $is_weixin          = null;     // 是否绑定微信(0:没绑定,1:绑定)


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

        $this->employee_id        = $cursor['employee_id'];
        $this->userid             = $cursor['userid'];
        $this->shop_id            = $cursor['shop_id'];
        $this->real_name          = $cursor['real_name'];
        $this->phone              = $cursor['phone'];
        $this->position_id        = $cursor['position_id'];
        $this->department_id      = $cursor['department_id'];
        $this->permission         = $cursor['permission'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
        $this->remark             = $cursor['remark'];
        $this->is_freeze          = $cursor['is_freeze'];
        $this->entry_time         = $cursor['entry_time'];
        $this->is_admin           = $cursor['is_admin'];
        $this->authorize          = $cursor['authorize'];
//        $this->identity           = $cursor['identity'];
//        $this->sex                = $cursor['sex'];
//        $this->email              = $cursor['email'];
//        $this->is_weixin          = $cursor['is_weixin'];
//        $this->health_certificate = $cursor['health_certificate'];
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
            'employee_id' => (string)$info->employee_id
        );

        $set = array(
            'employee_id' => (string)$info->employee_id,
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
        if(null !== $info->position_id)
        {
            $set["position_id"] = (string)$info->position_id;
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
        if(null !== $info->department_id)
        {
            $set["department_id"] = (string)$info->department_id;
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
        if (null !== $info->authorize) {
            $set["authorize"] = (int)$info->authorize;
        }
//        if(null !== $info->identity)
//        {
//            $set["identity"] = (string)$info->identity;
//        }
//        if(null !== $info->sex)
//        {
//            $set["sex"] = (int)$info->sex;
//        }
//        if (null !== $info->email) {
//            $set["email"] = (string)$info->email;
//        }
//        if (null !== $info->is_weixin) {
//            $set["is_weixin"] = (int)$info->is_weixin;
//        }
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

    public function BatchDelete($employee_id,$shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
//
//        foreach($userid_id_list as $i => &$item)
//        {
//            $item = (int)$item;
//        }

        $set = array(
            "delete"      => 1,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            //'userid' => ['$in' => $userid_id_list]
            "shop_id" => $shop_id,
            'employee_id'  => $employee_id
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

    public function GetEmployeeById($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'  => ['$ne'=>1],
            'userid' => (int)$userid,
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        return EmployeeEntry::ToList($cursor);
    }

    public function GetEmployeeInfo($shop_id, $employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id'     => (string)$shop_id,
            'employee_id' => (string)$employee_id,
            'delete'      => ['$ne' => 1]
        ];
        $cursor = $table->findOne($cond);
        return new EmployeeEntry($cursor);
    }
    public function GetEmployeeId($shop_id,$employee_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id'       => (string)$shop_id,
            'real_name'     => (string)$employee_name,
            'delete'        => ['$ne' => 1],
        ];

        $cursor = $table->findOne($cond);
        return new EmployeeEntry($cursor);
    }
    public function GetEmployeeByName($shop_id, $employee_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id'       => (string)$shop_id,
            'real_name'     => new \MongoRegex("/$employee_name/"),
            'delete'        => ['$ne' => 1],
        ];

        $cursor = $table->find($cond, ["_id" => 0])->sort(["employee_id" => 1]);
        return EmployeeEntry::ToList($cursor);
    }
    public function GetEmployeeByPhone($shop_id,$phone)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'phone'   => (string)$phone,
            'delete'  => ['$ne' => 1],
        ];
        $cursor = $table->findOne($cond);
        return new EmployeeEntry($cursor);
    }
    public function GetEmployeeList($shop_id, $filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
            'is_admin' => ['$ne' => 1],
            'shop_id'  => (string)$shop_id,
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
    //通过部门ID获取所有员工
    public function GetDepartmentEmployee($shop_id,$department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id,
            'department_id'=>(string)$department_id
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        return EmployeeEntry::ToList($cursor);
    }

    public function QueryByUserId($shop_id,$userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$shop_id,
            'userid'  => (int)$userid,
            'delete'  => ['$ne' => 1],
        );

        $ret = $table->findOne($cond);

        return new EmployeeEntry($ret);
    }

    public function QueryByShopId($userid, $shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid'  => (int)$userid,
            'shop_id' => (string)$shop_id,
            'delete'  => ['$ne' => 1],
        );
        $ret = $table->findOne($cond);
        return new EmployeeEntry($ret);
    }
    // 冻结/启用
    public function BatchFreeze($employee_id,$shop_id, $is_freeze)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'employee_id' => $employee_id
        ];
        $set = array(
            "is_freeze"   => (int)$is_freeze,
            "shop_id"     => (string)$shop_id,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        LogDebug($cond);
        LogDebug($value);
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
        return EmployeeEntry::ToList($cursor);
    }

    public function GetAdminByUserId($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'   => ['$ne'=>1],
            'is_admin' => 1,
            'userid' => (int)$userid,
        ];
        $cursor = $table->findOne($cond);
        return new EmployeeEntry($cursor);
    }
}


?>
