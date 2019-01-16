<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * login表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("/www/shop.sailing.com/php/db_pool.php");


# t_login
class LoginEntry
{
    public $id          = 0;   // 登录id
    public $userid      = 0;   // 用户id
    public $ip          = "";  // 登录ip
    public $login_time  = 0;   // 登录时间
    public $logout_time = 0;   // 退出时间
    public $ctime       = 0;   // 创建时间
    public $mtime       = 0;   // 修改时间
    public $delete      = null;// 0:未删除; 1:已删除

    function __construct()
    {
        // parent::__construct();
    }
};

class Login
{
    private function Tablename()
    {
        return 'login';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$info->userid
        );

        $set = array(
            "userid"    => (int)$info->userid,
            "mtime"     => time()
        );
        if("" != $info->id)
        {
            $set["id"] = (string)$info->id;
        }
        if("" != $info->ip)
        {
            $set["ip"] = (string)$info->ip;
        }
        if((int)$info->login_time > 0)
        {
            $set["login_time"] = (int)$info->login_time;
        }
        if((int)$info->logout_time > 0)
        {
            $set["logout_time"] = (int)$info->logout_time;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

        $ret = $table->update($cond, $value, array('upsert'=>true));
        LogDebug("ret:$ret");
        return 0;
    }

    // 返回 LoginEntry
    public function QueryByKey($key)
    {
        $db = DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'key' => $key
        );

        $ret = $table->findOne($cond);
        $entry = new LoginEntry();
        $entry->id          = $ret['id'];
        $entry->userid      = $ret['userid'];
        $entry->key         = $ret['key'];
        $entry->ip          = $ret['ip'];
        $entry->login_time  = $ret['login_time'];
        $entry->logout_time = $ret['logout_time'];
        $entry->rand_passwd = $ret['rand_passwd'];
        $entry->ctime       = $ret['ctime'];
        $entry->mtime       = $ret['mtime'];
        $entry->delete      = $ret['delete'];
        return $entry;
    }
}


?>
