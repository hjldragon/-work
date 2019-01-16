<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 支付宝用户表操作类
 */
declare(encoding='UTF-8');

namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");



class AlipayEntry
{   
    public $id            = null;  // 
    public $userid        = null;  // 用户id
    public $avatar        = null;  // 支付宝头像
    public $nickname      = null;  // 支付宝昵称
    public $alipay_id     = null;  // 
    public $sex           = null;  // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知 
    public $city          = null;  // 用户所在城市
    public $province      = null;  // 用户所在省份
    public $delete        = null;
    public $lastmodtime   = null;
    public $src           = null;  // 绑定来源:1客户端,2商户端

 

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
        $this->id          = $cursor['id'];
        $this->userid      = $cursor['userid'];
        $this->avatar      = $cursor['avatar'];
        $this->nickname    = $cursor['nickname'];
        $this->alipay_id   = $cursor['alipay_id'];
        $this->sex         = $cursor['sex'];
        $this->city        = $cursor['city'];
        $this->province    = $cursor['province'];
        $this->delete      = $cursor['delete'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->src         = $cursor['src'];
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



class Alipay
{
    private function Tablename()
    {
        return 'alipay';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'id' => (string)$info->id
        );

        $set = [
            "id" => (string)$info->id,
        ];
        if (null !== $info->userid) {
            $set["userid"] = (int)$info->userid;
        } 
        if (null !== $info->avatar) {
            $set["avatar"] = (string)$info->avatar;
        }
        if (null !== $info->nickname) {
            $set["nickname"] = (string)$info->nickname;
        }
        if (null !== $info->sex) {
            $set["sex"] = (int)$info->sex;
        }
        if (null !== $info->alipay_id) {
            $set["alipay_id"] = (string)$info->alipay_id;
        }
        if (null !== $info->city) {
            $set["city"] = (string)$info->city;
        }
        if (null !== $info->province) {
            $set["province"] = (string)$info->province;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->lastmodtime) {
            $set["lastmodtime"] = $info->lastmodtime;
        }
        if (null !== $info->src) {
            $set["src"] = (int)$info->src;
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

    public function QueryByAlipayid($alipay_id, $src)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        
        $cond = array(
            'alipay_id' => (string)$alipay_id,
            'src'    => (int)$src,
            'delete' => ['$ne' => 1]
        );

        $ret = $table->findOne($cond);
        return new AlipayEntry($ret);
    }

    public function QueryByUserId($userid)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        
        $cond = array(
            'userid' => (int)$userid
        );

        $ret = $table->findOne($cond);
        return new AlipayEntry($ret);
    }

}
?>