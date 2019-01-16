<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 微信用户表操作类
 */
declare(encoding='UTF-8');

namespace DaoMongodb;
require_once("/www/shop.sailing.com/php/db_pool.php");
require_once("const.php");



class WeixinEntry
{
    public $id            = null;  //
    public $userid        = null;  // 用户id
    public $headimgurl    = null;  // 微信头像
    public $nickname      = null;  // 微信昵称
    public $openid        = null;  //
    public $sex           = null;  // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    public $city          = null;  // 用户所在城市
    public $country       = null;  // 用户所在国家
    public $province      = null;  // 用户所在省份
    public $delete        = null;
    public $lastmodtime   = null;
    public $src           = null;  // 绑定来源:1客户端,2商户端
    public $srctype       = null;  // 微信数据绑定来源:1.pc端,2.app端,3.收银端



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
        $this->headimgurl  = $cursor['headimgurl'];
        $this->nickname    = $cursor['nickname'];
        $this->openid      = $cursor['openid'];
        $this->sex         = $cursor['sex'];
        $this->city        = $cursor['city'];
        $this->country     = $cursor['country'];
        $this->province    = $cursor['province'];
        $this->delete      = $cursor['delete'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->src         = $cursor['src'];
        $this->srctype     = $cursor['srctype'];
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



class Weixin
{
    private function Tablename()
    {
        return 'weixin';
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
        if (null !== $info->headimgurl) {
            $set["headimgurl"] = (string)$info->headimgurl;
        }
        if (null !== $info->nickname) {
            $set["nickname"] = (string)$info->nickname;
        }
        if (null !== $info->sex) {
            $set["sex"] = (int)$info->sex;
        }
        if (null !== $info->openid) {
            $set["openid"] = (string)$info->openid;
        }
        if (null !== $info->city) {
            $set["city"] = (string)$info->city;
        }
        if (null !== $info->country) {
            $set["country"] = (string)$info->country;
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
        if (null !== $info->srctype) {
            $set["srctype"] = (int)$info->srctype;
        }


        $value = array(
            '$set' => $set
        );
        LogDebug($set);

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryByOpenId($openid, $src, $srctype=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'openid' => (string)$openid,
            'src'    => (int)$src
        );
        if(null !== $srctype)
        {
            $cond['srctype'] = $srctype;
        }

        $ret = $table->findOne($cond);
        return new WeixinEntry($ret);
    }

    public function QueryByUserId($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$userid
        );

        $ret = $table->findOne($cond);
        return new WeixinEntry($ret);
    }

    public function UserIdAndSrctype($userid, $srctype, $src)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid'    => (int)$userid,
            'srctype'   => (int)$srctype,
            'src'       => (int)$src
        );

        $ret = $table->findOne($cond);
        return new WeixinEntry($ret);
    }

    public function QueryByUserIdSrc($userid, $src)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid' => (int)$userid,
            'src'    => (int)$src
        );

        $ret = $table->findOne($cond);
        return new WeixinEntry($ret);
    }

    public function QueryById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'id' => (string)$id
        );

        $ret = $table->findOne($cond);
        return new WeixinEntry($ret);
    }

}
?>