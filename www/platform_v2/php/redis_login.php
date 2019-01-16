<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * login表操作类
 */
declare(encoding='UTF-8');
namespace DaoRedis;
require_once("db_pool.php");
require_once("redis_public.php");


# t_login
class LoginEntry
{
    public $token       = null;
    public $userid      = null;     // 用户id
    public $username    = null;     // 用户名
    public $shop_id     = null;     // 餐馆店铺id
    public $key         = null;     //
    public $login       = null;     // 是否已登录，1:已登录, 2:未登录
    public $phone_code  = null;     // 手机验证码
    public $code_time   = null;     // 手机验证码过期时间
    public $page_code   = null;     // 页面验证码
    public $phone       = null;     // 输入手机号
    public $openid      = null;
    public $platform_id = null;     // 运营平台id
    public $agent_id    = null;     // 代理商id


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
        $this->token       = $cursor['token'];
        $this->userid      = $cursor['userid'];
        $this->username    = $cursor['username'];
        $this->shop_id     = $cursor['shop_id'];
        $this->key         = $cursor['key'];
        $this->login       = $cursor['login'];
        $this->phone_code  = $cursor['phone_code'];
        $this->code_time   = $cursor['code_time'];
        $this->page_code   = $cursor['page_code'];
        $this->phone       = $cursor['phone'];
        $this->openid      = $cursor['openid'];
        $this->platform_id = $cursor['platform_id'];
        $this->agent_id    = $cursor['agent_id'];
    }
};

class Login
{
    private function Tablename()
    {
        return DB_LOGIN; // 注意各个表使用序号
    }

    public function Save($entry)
    {
        if(!$entry || null === $entry->token)
        {
            LogDebug($entry);
            LogErr("param err");
            return -1;
        }
        $db = \DbPool::GetRedis($this->Tablename());
        $data = [];

        $data["token"] = $entry->token;

        if(null !== $entry->userid)
        {
            $data["userid"] = $entry->userid;
        }
        if(null !== $entry->username)
        {
            $data["username"] = $entry->username;
        }
        if(null !== $entry->shop_id)
        {
            $data["shop_id"] = $entry->shop_id;
        }
        if(null !== $entry->key)
        {
            $data["key"] = $entry->key;
        }
        if(null !== $entry->login)
        {
            $data["login"] = $entry->login;
        }
        if(null !== $entry->phone_code)
        {
            $data["phone_code"] = $entry->phone_code;
        }
        if(null !== $entry->code_time)
        {
            $data["code_time"] = $entry->code_time;
        }
        if(null !== $entry->page_code)
        {
            $data["page_code"] = $entry->page_code;
        }
        if(null !== $entry->phone)
        {
            $data["phone"] = $entry->phone;
        }
        if(null !== $entry->openid)
        {
            $data["openid"] = $entry->openid;
        }
        if(null !== $entry->platform_id)
        {
            $data["platform_id"] = $entry->platform_id;
        }
        if(null !== $entry->agent_id)
        {
            $data["agent_id"] = $entry->agent_id;
        }
        $ret = $db->hmset($entry->token, $data);

        if($ret < 0)
        {
            LogErr("hmset err");
            return $ret;
        }
        // 正确
        //$db->expire($entry->token, 60*60*24*7); // 暂时去掉，后台不过期
        return 0;
    }

    public function SaveKey($token, $key)
    {
        $db = \DbPool::GetRedis($this->Tablename());
        $ret = $db->hset("${token}", "key", $key);
        if($ret < 0)
        {
            LogErr("hset err");
            return $ret;
        }
        return 0;
    }

    public function Get($token)
    {
        try
        {
            $db = \DbPool::GetRedis($this->Tablename());
            $info = new LoginEntry();
            $ret = $db->hgetall($token);
            LogDebug("token:[$token]");
            return new LoginEntry($ret);
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
