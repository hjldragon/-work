<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 餐桌位置表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class SelfhelpEntry extends BaseInfo
{
    public $selfhelp_id     = null; // 自助点餐机的唯一标识
    public $selfhelp_name   = null; // 自助点餐机名
    public $userid          = null; // 绑定用户id(一个账号只能绑定一个账号)
    public $shop_id         = null; // 绑定店铺id
    public $is_using        = null; // 是否启用(1.启用,0.未启用)
    public $using_type      = null; // 启用类型(1.联动启用(pc,pad有订单数据显示),2.独立启用(PAD没有订单数据显示))
    public $is_print        = null; // 是否支持打印(1.支持,0.不支持)
    public $is_wx_send      = null; // 是否微信模版发送(1.支持,0.不支持)
    public $remark          = null; // 备注
    public $lastmodtime     = null; // 数据最后修改时间
    public $delete          = null; // 0:正常, 1:已删除

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    private function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->selfhelp_id   = $cursor['selfhelp_id'];
        $this->selfhelp_name = $cursor['selfhelp_name'];
        $this->userid        = $cursor['userid'];
        $this->shop_id       = $cursor['shop_id'];
        $this->is_using      = $cursor['is_using'];
        $this->using_type    = $cursor['using_type'];
        $this->is_print      = $cursor['is_print'];
        $this->is_wx_send    = $cursor['is_wx_send'];
        $this->remark        = $cursor['remark'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->delete        = $cursor['delete'];

    }
}
class Selfhelp extends MgoBase
{
    protected function Tablename()
    {
        return 'selfhelp';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'selfhelp_id' => (string)$info->selfhelp_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->selfhelp_name)
        {
            $set["selfhelp_name"] = (string)$info->selfhelp_name;
        }

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }

        if(null !== $info->userid)
        {
            $set["userid"] = (int)$info->userid;
        }

        if(null !== $info->is_using)
        {
            $set["is_using"] = (int)$info->is_using;
        }

        if(null !== $info->using_type)
        {
            $set["using_type"] = (int)$info->using_type;
        }

        if(null !== $info->is_print)
        {
               $set["is_print"] = (int)$info->is_print;
        }

        if(null !== $info->is_wx_send)
        {
            $set["is_wx_send"] = (int)$info->is_wx_send;
        }

        if(null !== $info->remark)
        {
            $set["remark"] = (string)$info->remark;
        }

        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

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

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetExampleById($selfhelp_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'selfhelp_id' => (string)$selfhelp_id,
            'delete'      => 0,
        ];
        $cursor = $table->findOne($cond);
        return new SelfhelpEntry($cursor);
    }

    public function GetExampleList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $org_id = $filter['example_name'];
            if(isset($example_name))
            {
                $cond['example_name'] = (string)$example_name;
            }
        }

        $cursor = $table->find($cond, ["_id"=>0])->sort(["_id"=>1]);
        return DeptInfo::ToList($cursor);
    }

    public function SetUnBinding($selfhelp_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());


        $cond = array(
            'selfhelp_id' => (string)$selfhelp_id
        );
        $value = array(
            '$set' => array(
                'userid'      => (int)0,
                'delete'      => 1,
                'lastmodtime' => time()
            )
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));

        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetByUserId($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid' => (int)$userid,
            'delete' => 0,
        ];
        $cursor = $table->findOne($cond);
        return new SelfhelpEntry($cursor);
    }

    public function UnBindingShopId($selfhelp_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());


        $cond = array(
            'selfhelp_id' => (string)$selfhelp_id
        );
        $value = array(
            '$set' => array(
                'shop_id'     => "",
                'lastmodtime' => time()
            )
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));

        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}






?>
