<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 用户表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");


class CustomerEntry
{
    public $customer_id    = null;           // 顾客用户id
    public $userid         = null;           // 用户id
    public $shop_id        = null;           // 餐馆店铺id
    public $phone          = null;           // 手机号(用户名)
    public $is_vip         = null;           // 是否会员(0:未知,1:是)
    public $openid         = null;           // 微信openid
    //public $property     = null;           // 用户属性(位字段，1bit:管理员)
    public $ctime          = null;           // 创建时间
    public $mtime          = null;           // 修改时间
    public $lastmodtime    = null;           // 最后修改的时间
    public $delete         = null;           // 是否删除(0:未删除; 1:已删除)
    public $weixin_account = null;           // 微信账号
    public $vip_level      = null;           // 会员等级（暂未开放)
    public $remark         = null;           // 备注
    public $sex            = null;           // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
    public $usernick       = null;           // 用户昵称
    public $user_avater    = null;           // 用户头像
    public $birthday       = null;           // 用户生日


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
        $this->customer_id    = $cursor['customer_id'];
        $this->userid         = $cursor['userid'];
        $this->shop_id        = $cursor['shop_id'];
        $this->phone          = $cursor['phone'];
        $this->is_vip         = $cursor['is_vip'];
        $this->openid         = $cursor['openid'];
        //$this->property     = $cursor['property'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->ctime          = $cursor['ctime'];
        $this->mtime          = $cursor['mtime'];
        $this->delete         = $cursor['delete'];
        $this->weixin_account = $cursor['weixin_account'];
        $this->vip_level      = $cursor['vip_level'];
        $this->remark         = $cursor['remark'];
        $this->sex            = $cursor['sex'];
        $this->usernick       = $cursor['usernick'];
        $this->user_avater    = $cursor['user_avater'];
        $this->birthday       = $cursor['birthday'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new CustomerEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }

    //  const PROP_IS_ADMIN = 1; // 是管事员
}

class Customer
{
    private function Tablename()
    {
        return 'customer';
    }

    public function Save(&$info)
    {
        if (!$info->customer_id)
        {
            LogErr("param err:" . json_encode($info));
            return \errcode::PARAM_ERR;
        }
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'customer_id' => (string)$info->customer_id
        );

        $set = array(
            "customer_id" => (string)$info->customer_id,
            "lastmodtime" => time()
        );
        if (null !== $info->userid) {
            $set["userid"] = (int)$info->userid;
        }
        if (null !== $info->phone) {
            $set["phone"] = (string)$info->phone;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->is_vip) {
            $set["is_vip"] = (int)$info->is_vip;
        }
        if (null !== $info->openid) {
            $set["openid"] = $info->openid;
        }
        if (null !== $info->property) {
            $set["property"] = (int)$info->property;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
        }
        if (null !== $info->mtime) {
            $set["mtime"] = (int)$info->mtime;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->weixin_account) {
            $set["weixin_account"] = (string)$info->weixin_account;
        }
        if (null !== $info->vip_level) {
            $set["vip_level"] = (int)$info->vip_level;
        }
        if (null !== $info->remark) {
            $set["remark"] = (string)$info->remark;
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
        
        LogDebug($set);
        $value = array(
            '$set' => $set
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . $ret['ok']);
        } catch (\MongoCursorException $e) {

            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    // 返回 CustomerEntry
    public function QueryById($customer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'customer_id' => (string)$customer_id,
            'delete' => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new CustomerEntry($ret);
    }

    // 返回 CustomerEntry
    public function QueryByOpenidShopid($openid, $shop_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'openid' => $openid,
            'shop_id' => $shop_id,
            //'delete' => array('$ne'=>1)
        );

        $ret = $table->findOne($cond);
        return new CustomerEntry($ret);
    }

    // 返回 CustomerEntry
    public function QueryByOpenid($openid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'openid' => $openid,
            'delete' => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        // LogDebug("[$ret]");
        return new CustomerEntry($ret);
    }

    public function GetCustomerList($shop_id, $filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id
        ];
        //搜索功能
        if(null != $filter)
        {
            $customer_id = $filter['customer_id'];
            if(!empty($customer_id))
            {
                $cond['customer_id'] = (string)$customer_id;
            }
            $phone = $filter['phone'];
            if(!empty($phone))
            {
                $cond['phone'] = new \MongoRegex("/$phone/");
            }
            $nickname = $filter['nickname'];
            if(!empty($nickname))
            {
                $cond['nickname'] = new \MongoRegex("/$nickname/");
            }
            $sex = $filter['sex'];
            if(!empty($sex))
            {
                $cond['sex'] = (int)$sex;
            }
            // $userid_list = $filter['userid_list'];
            // if(!empty($phone))
            // {
            //     $cond['userid'] = ['$in' => $userid_list];
            // }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        
        LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return CustomerEntry::ToList($cursor);
    }

    public function BatchDeleteById($customer_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($customer_id_list as $i => &$id)
        {
            $id = (int)$id;
        }

        $cond = array(
            'customer_id' => array('$in' => $customer_id_list)
        );

         LogDebug($cond);

        $set = array(
            "mtime"     => time(),
            "delete"    => 1
        );

        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
}


?>
