<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 用户表操作类
 */
declare(encoding='UTF-8');

namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");


# t_user
class UserEntry
{
    public $userid       = null;  //用户id
    public $ctime        = null;  //创建时间
    public $delete       = null;  //0:未删除; 1:已删除
    public $phone        = null;  //手机号
    public $birthday     = null;  //用户生日
    public $usernick     = null;  //用户昵称
    public $user_avater  = null;  //用户头像
    public $sex          = null;  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    public $lastmodtime  = null;    


    
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
        $this->userid      = $cursor['userid'];
        $this->ctime       = $cursor['ctime'];
        $this->phone       = $cursor['phone'];
        $this->birthday    = $cursor['birthday'];
        $this->delete      = $cursor['delete'];
        $this->usernick    = $cursor['usernick'];
        $this->user_avater = $cursor['user_avater'];
        $this->sex         = $cursor['sex'];
        $this->lastmodtime = $cursor['lastmodtime'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new UserEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class User
{
    private function Tablename()
    {
        return 'user';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$info->userid
        );
        $set = [
            "userid" => (int)$info->userid,
        ];
        if (null !== $info->lastmodtime) {
            $set["lastmodtime"] = (int)$info->lastmodtime;
        } else {
            $set["lastmodtime"] = time();
        }
        if (null !== $info->usernick) {
            $set["usernick"] = (string)$info->usernick;
        }
        if (null !== $info->user_avater) {
            $set["user_avater"] = (string)$info->user_avater;
        }
        if ((int)$info->ctime > 0) {
            $set["ctime"] = (int)$info->ctime;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->phone) {
            $set["phone"] = (string)$info->phone;
        }
        if (null !== $info->birthday) {
            $set["birthday"] = (string)$info->birthday;
        }
        if (null !== $info->usernick) {
            $set["usernick"] = (string)$info->usernick;
        }
        if (null !== $info->user_avater) {
            $set["user_avater"] = (string)$info->user_avater;
        }
        if (null !== $info->sex) {
            $set["sex"] = (int)$info->sex;
        }
        $value = array(
            '$set' => $set
        );
        
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function IsExist($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $or = [];
        if ($filter['userid']) {
            array_push($or, [
                'userid' => $filter['userid']
            ]);
        }
        if ($filter['username']) {
            array_push($or, [
                'username' => $filter['username']
            ]);
        }
        if ($filter['phone']) {
            array_push($or, [
                'phone' => $filter['phone']
            ]);
        }
        $cond = [
            'delete' => ['$ne' => 1],
            '$or' => $or,
        ];
        LogDebug($cond);
        $cursor = $table->findOne($cond, ['userid' => 1, 'username' => 1, 'phone' => 1]);
        if ($cursor && $cursor['userid']) {
            return new UserEntry($cursor);
        }
        return null;
    }

    // 返回 UserEntry
    public function QueryById($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$userid
        );

        $ret = $table->findOne($cond);

        return new UserEntry($ret);
    }
    // 返回 UserEntry
    public function QueryByPhone($phone)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'phone' => (int)$phone,
            'delete' => ['$ne' => 1],
        );

        $ret = $table->findOne($cond);

        return new UserEntry($ret);
    }

    // 返回 UserEntry
    public function QueryByName($username)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'username' => $username,
            'delete' => array('$ne' => 1)
        );
        $ret = $table->findOne($cond);
         LogDebug($ret);
        return new UserEntry($ret);
    }

    public function GetUserList()
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array('delete' => array('$ne' => 1));
        $cursor = $table->find($cond, array("_id" => 0));
        //return iterator_to_array($cursor);
        return UserEntry::ToList($cursor);
    }
   
}


?>