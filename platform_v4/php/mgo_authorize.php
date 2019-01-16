<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 登录授权表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");



class AuthorizeEntry
{
    //public $authorize_id     = null;    // 授权id
    public $shop_id          = null;    // 店铺id
    public $pc_num           = null;    // 商家运营平台名额
    public $pad_num          = null;    // 平板智能点餐机登录名额
    public $cashier_num      = null;    // 智能收银机登录名额
    public $app_num          = null;    // 掌柜通登录名额
    public $machine_num      = null;    // 自助点餐机登录名额
    public $used_pc_num      = null;    // 已分配的商家运营平台登录名额
    public $used_pad_num     = null;    // 已分配的平板智能点餐机登录名额
    public $used_cashier_num = null;    // 已分配的智能收银机登录名额
    public $used_app_num     = null;    // 已分配的掌柜通登录名额
    public $used_machine_num = null;    // 已分配的自助点餐机登录名额
    public $delete           = null;    // 0:未删除; 1:已删除
    public $lastmodtime      = null;    // 最后修改时间
   

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
        //$this->authorize_id     = $cursor['authorize_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->pc_num           = $cursor['pc_num'];
        $this->pad_num          = $cursor['pad_num'];
        $this->cashier_num      = $cursor['cashier_num'];
        $this->app_num          = $cursor['app_num'];
        $this->machine_num      = $cursor['machine_num'];
        $this->used_pc_num      = $cursor['used_pc_num'];
        $this->used_pad_num     = $cursor['used_pad_num'];
        $this->used_cashier_num = $cursor['used_cashier_num'];
        $this->used_app_num     = $cursor['used_app_num'];
        $this->delete           = $cursor['delete'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->used_machine_num = $cursor['used_machine_num'];
    }

    public static function ToList($cursor)
    {
        $lists = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($lists, $entry);
        }
        return $lists;
    }
}

class Authorize
{
    private function Tablename()
    {
        return 'authorize';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            //'authorize_id' => (string)$info->authorize_id,
            'shop_id'      => (string)$info->shop_id
        );

        $set = array(
            //"authorize_id" => (string)$info->authorize_id,
            "shop_id"      => (string)$info->shop_id,
        );

        if(null !== $info->pc_num)
        {
            $set["pc_num"] = (int)$info->pc_num;
        }

        if(null !== $info->pad_num)
        {
            $set["pad_num"] = (int)$info->pad_num;
        }
        
        if(null !== $info->cashier_num)
        {
            $set["cashier_num"] = (int)$info->cashier_num;
        }
        if(null !== $info->app_num)
        {
            $set["app_num"] = (int)$info->app_num;
        }
        if(null !== $info->used_pc_num)
        {
            $set["used_pc_num"] = (int)$info->used_pc_num;
        }

        if(null !== $info->used_pad_num)
        {
            $set["used_pad_num"] = (int)$info->used_pad_num;
        }
        
        if(null !== $info->used_cashier_num)
        {
            $set["used_cashier_num"] = (int)$info->used_cashier_num;
        }
        if(null !== $info->used_app_num)
        {
            $set["used_app_num"] = (int)$info->used_app_num;
        }
        if(null !== $info->used_machine_num)
        {
            $set["used_machine_num"] = (int)$info->used_machine_num;
        }
        if(null !== $info->machine_num)
        {
            $set["machine_num"] = (int)$info->machine_num;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $set['lastmodtime'] = time();
        
        $value = array(
            '$set' => $set
        );
        
        try
        {
            $ret = $table->update($cond, $value, ['upsert'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }

        return 0;
    }

    
    public function GetAuthorizeById($authorize_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'authorize_id' => (string)$authorize_id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new AuthorizeEntry($cursor);
    }

    //查找店铺授权
    public function GetAuthorizeByShop($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'shop_id' => (string)$shop_id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new AuthorizeEntry($cursor);
    }


}



?>
