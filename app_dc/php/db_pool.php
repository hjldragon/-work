<?php
/*
 * DB连接池管理类
 * [rockyshi 2014-03-27 10:40:51]
 *
 */
require_once("cfg.php");
require_once("Log.php");

class DbPool
{
    // 业务数据库（mongodb）
    static public function GetMongoDb()
    {
        static $db = NULL;
        if(NULL == $db)
        {
            try
            {
                $host    = Cfg::instance()->db->mongodb->host;
                $port    = Cfg::instance()->db->mongodb->port;
                $user    = Cfg::instance()->db->mongodb->user;
                $passwd  = Cfg::instance()->db->mongodb->passwd;
                $dbname  = Cfg::instance()->db->mongodb->dbname;
                $str = "mongodb://";
                if(!empty($user) && !empty($passwd))
                {
                    $str .= "$user:$passwd@";
                }
                if(!empty($host))
                {
                    $str .= "$host";
                }
                if(!empty($port))
                {
                    $str .= ":$port";
                }
                if(!empty($dbname))
                {
                    $str .= "/$dbname";
                }
                LogDebug("mongodb:[$str]");
                //$mongo = new Mongo("mongodb://$user:$passwd@$host:$port/$dbname");
                if(class_exists("Mongo"))
                {
                    $mongo = new Mongo($str);
                    $db = $mongo->selectDB($dbname);
                }
                else
                {
                    $mongo = new MongoDB\Driver\Manager($str);
                    $db = $mongo;
                }
                //LogDebug($mongo);
            }
            catch(Exception $e)
            {
                LogErr($e->getMessage());
                return NULL;
            }
            LogDebug("get db connect ok");
        }
        return $db;
    }

    static public function GetRedis($table)
    {
        static $db = [];
        $t = $db[$table];
        if(isset($t))
        {
            return $t;
        }
        try
        {
            $host = Cfg::instance()->db->redis->host;
            if(!$host)
            {
                $host = '127.0.0.1';
            }
            $port = Cfg::instance()->db->redis->port;
            if(!$port)
            {
                $port = 6379;
            }

            LogDebug("redis connect to:[$host:$port]");

            $redis = new Redis();
            $redis->connect($host, $port);
            $redis->select($table);
            $db[$table] = $redis;
            LogDebug("get redis connect ok");
            return $redis;
        }
        catch(RedisException $e)
        {
            LogErr($e->getMessage());
        }
        catch(Exception $e)
        {
            LogErr($e->getMessage());
        }
        LogDebug("get redis connect err");
        return new Redis();
    }
};


?>
