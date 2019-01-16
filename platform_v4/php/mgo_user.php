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
    public $userid             = null;   // 用户id
    public $username           = null;   // 用户名(登陆名)
    public $password           = null;   // 密码
    public $question           = null;   // 问题
    public $answer             = null;   // 答案
    public $passwd_prompt      = null;   // 密码提示
    public $ctime              = null;   // 创建时间
    public $lastmodtime        = null;   // 修改时间
    public $delete             = null;   // 0:未删除; 1:已删除
    public $phone              = null;   // 手机号
    public $identity           = null;   // 身份证号
    public $usernick           = null;   // 用户昵称
    public $user_avater        = null;   // 用户头像
    public $birthday           = null;   // 用户生日
    public $sex                = null;   // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    public $email              = null;   // 用户邮箱
    public $is_weixin          = null;   // 是否绑定微信(0:没绑定,1:绑定)
    public $health_certificate = null;   // 健康证
    public $real_name          = null;   // 真实姓名
    public $src                = null;   // 用户平台类型:1客户端,2商户端,3运营端

    // // 具体业务数据


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
        $this->userid             = $cursor['userid'];
        $this->username           = $cursor['username'];
        $this->password           = $cursor['password'];
        $this->question           = $cursor['question'];
        $this->answer             = $cursor['answer'];
        $this->passwd_prompt      = $cursor['passwd_prompt'];
        $this->ctime              = $cursor['ctime'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
        $this->user_avater        = $cursor['user_avater'];
        $this->phone              = $cursor['phone'];
        $this->identity           = $cursor['identity'];
        $this->usernick           = $cursor['usernick'];
        $this->birthday           = $cursor['birthday'];
        $this->sex                = $cursor['sex'];
        $this->email              = $cursor['email'];
        $this->is_weixin          = $cursor['is_weixin'];
        $this->health_certificate = $cursor['health_certificate'];
        $this->real_name          = $cursor['real_name'];
        $this->src                = $cursor['src'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new UserEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }

};

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
            'userid' => (int)$info->userid,
        );
        $bit = null;
        $set = [
            "userid"      => (int)$info->userid,
            "lastmodtime" => (int)time(),
        ];

        if(null !== $info->key)
        {
            $set["key"] = (string)$info->key;
        }
        if(null !== $info->username)
        {
            $set["username"] = (string)$info->username;
        }
        if(null !== $info->password)
        {
            $set["password"] = (string)$info->password;
        }
        if(null !== $info->question)
        {
            $set["question"] = (string)$info->question;
        }
        if(null !== $info->answer)
        {
            $set["answer"] = (string)$info->answer;
        }
        if(null !== $info->passwd_prompt)
        {
            $set["passwd_prompt"] = (string)$info->passwd_prompt;
        }
        if((int)$info->ctime > 0)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->phone)
        {
            $set["phone"] = (string)$info->phone;
        }
        if(null !== $info->identity)
        {
            $set["identity"] = (string)$info->identity;
        }
        if (null !== $info->user_avater) {
            $set["user_avater"] = (string)$info->user_avater;
        }
        if (null !== $info->usernick) {
            $set["usernick"] = (string)$info->usernick;
        }
        if (null !== $info->birthday) {
            $set["birthday"] = (int)$info->birthday;
        }
        if (null !== $info->sex) {
            $set["sex"] = (int)$info->sex;
        }
         if (null !== $info->email) {
             $set["email"] = (string)$info->email;
         }
        if (null !== $info->is_weixin) {
            $set["is_weixin"] = (int)$info->is_weixin;
        }
        if (null !== $info->health_certificate) {
            $set["health_certificate"] = (string)$info->health_certificate;
        }
        if (null !== $info->real_name) {
            $set["real_name"] = (string)$info->real_name;
        }
        if (null !== $info->src) {
            $set["src"] = (int)$info->src;
        }
        $value = array(
            '$set' => $set
        );
        if($bit)
        {
            $value['$bit'] = $bit;
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

    public function IsExist($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $or = [];
        if($filter['userid'])
        {
            array_push($or, [
                'userid' => $filter['userid']
            ]);
        }
        if($filter['username'])
        {
            array_push($or, [
                'username' => $filter['username']
            ]);
        }
        if($filter['phone'])
        {
            array_push($or, [
                'phone' => $filter['phone']
            ]);
        }
        $cond = [
            'delete'=> ['$ne'=>1],
            '$or' => $or,
        ];
        LogDebug($cond);
        $cursor = $table->findOne($cond, ['userid'=>1, 'username'=>1, 'phone'=>1]);
        if($cursor && $cursor['userid'])
        {
            return new UserEntry($cursor);
        }
        return null;
    }

    public function QueryUser($username, $phone, $password_md5, $src)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete' => ['$ne' => 1],
            'src'    => (int)$src,
            '$or' => [
                ["username" => (string)$username],
                ["phone"    => (string)$phone]
            ]
        ];

        $cursor = $table->findOne($cond, ["_id"=>0]);

        if($password_md5 == md5($cursor['password']) && $cursor['password'])
        {
            return new UserEntry($cursor);
        }

        return null;
    }

    public function QueryByPass($userid, $password)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'=> ['$ne'=>1],
            "userid" => (int)$userid,
            "password" => (string)$password
        ];
        $cursor = $table->findOne($cond, ["_id"=>0]);
        if($cursor && $cursor['userid'])
        {
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
    public function QueryByName($username)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'username' => $username,
            'delete' => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        // LogDebug($ret);
        return new UserEntry($ret);
    }

    // 返回 UserEntry
    public function QueryByPhone($phone, $src)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'phone'  => (string)$phone,
            'src'    => (int)$src,
            'delete' => ['$ne' => 1],
        );

        $ret = $table->findOne($cond);

        return new UserEntry($ret);
    }
    public function QueryByEmail($email)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'phone' => (string)$email,
            'delete' => ['$ne' => 1],
        );

        $ret = $table->findOne($cond);

        return new UserEntry($ret);
    }

    public function GetUserList($filter = null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array('delete'=> array('$ne'=>1));
        if (null != $filter) {
            $nickname = $filter['nickname'];
            if (!empty($nickname)) {
                $cond['nickname'] = new \MongoRegex("/$nickname/i");
            }

            $sex = $filter['sex'];
            if (null !== $sex) {
                $cond['sex'] = (int)$sex;
            }
            $phone = $filter['phone'];
            if (!empty($phone)) {
                $cond['phone'] = new \MongoRegex("/$phone/i");
            }

        }
        $cursor = $table->find($cond, array("_id"=>0));
        //return iterator_to_array($cursor);
        return UserEntry::ToList($cursor);
    }

    public function BatchDeleteById($user_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($user_id_list as $i => &$id)
        {
            $id = (int)$id;
        }

        $cond = array(
            'userid' => array('$in' => $user_id_list)
        );

        // LogDebug($cond);

        $set = array(
            "mtime"     => time(),
            "delete"    => 1
        );

        $value = array(
            '$set' => $set
        );
        $opt = array(
            'upsert'   => true,
            'multiple' => true
        );

        $info = $table->update($cond, $value, $opt);
        return 0;
    }
}


?>
