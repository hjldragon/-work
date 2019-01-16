<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 版本配置表
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");


class EnvEntry
{
    public $env_id            = null;     // id(主键)
    public $appid             = null;     // appid
    public $secret            = null;     // secret
    public $mch_id            = null;     //
    public $key               = null;     // key值
    public $publickey         = null;     // 公钥
    public $privatekey        = null;     // 私钥
    public $env_name          = null;     // 配置项目
    public $lastmodtime       = null;     // 最后登录时间
    public $delete            = null;     // 0:正常, 1:已删除

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
        $this->env_id         = $cursor['env_id'];
        $this->appid          = $cursor['appid'];
        $this->secret         = $cursor['secret'];
        $this->mch_id         = $cursor['mch_id'];
        $this->key            = $cursor['key'];
        $this->publickey      = $cursor['publickey'];
        $this->privatekey     = $cursor['privatekey'];
        $this->env_name       = $cursor['env_name'];
        $this->delete         = $cursor['delete'];
        $this->lastmodtime    = $cursor['lastmodtime'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class Env
{
    private function Tablename()
    {
        return 'env';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'env_id' => (int)$info->env_id
        );

        $set = array(
            "env_id"      => (string)$info->env_id,
            "lastmodtime" => (null === $info->lastmodtime) ? time() : (int)$info->lastmodtime,
            "delete"      => 0
        );

        if(null !== $info->appid)
        {
            $set["appid"] = (string)$info->appid;
        }
        if(null !== $info->secret)
        {
            $set["secret"] = (string)$info->secret;
        }
        if(null !== $info->mch_id)
        {
            $set["mch_id"] = (string)$info->mch_id;
        }
        if(null !== $info->key)
        {
            $set["key"] = (string)$info->key;
        }
        if(null !== $info->publickey)
        {
            $set["publickey"] = (string)$info->publickey;
        }
        if(null !== $info->privatekey)
        {
            $set["privatekey"] = (string)$info->privatekey;
        }
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryByEnvId($env_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'env_id' => (int)$env_id,
        );

        $ret = $table->findOne($cond);
        return new EnvEntry($ret);
    }
}


?>
