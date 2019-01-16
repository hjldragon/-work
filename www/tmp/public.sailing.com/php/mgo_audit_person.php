<?php
/*
 * [Rocky 2018-01-07 23:12:07]
 * 部门表
 */
///declare(encoding='UTF-8');
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

//工商信息审核人
class AuditPersonEntry extends BaseInfo
{
    public $audit_id                     = null;    //审核id
    public $shop_id                      = null;    //店铺id
    public $agent_id                     = null;    //代理商id
    public $shop_name_status             = null;    //商户名审核状态(1.通过,0.不通过)
    public $shop_name_reason             = null;    //商户名称不通过原因
    public $agent_name_status            = null;    //代理商名审核状态(1.通过,0.不通过)
    public $agent_name_reason            = null;    //代理商名称不通过原因
    public $legal_person_status          = null;    //法人代表审核状态(1.通过,0.不通过)
    public $legal_person_reason          = null;    //法人代表审核不通过原因
    public $legal_card_status            = null;    //法人身份证审核状态(1.通过,0.不通过)
    public $legal_card_reason            = null;    //法人身份证审核不通过原因
    public $legal_card_photo_status      = null;    //法人身份照片审核状态(1.通过,0.不通过)
    public $legal_card_photo_reason      = null;    //法人身份照片审核不通过原因
    public $business_num_status          = null;    //营业执照注册号审核状态(1.通过,0.不通过)
    public $business_num_reason          = null;    //营业执照注册号审核不通过原因
    public $business_date_status         = null;    //营业期限审核状态(1.通过,0.不通过)
    public $business_date_reason         = null;    //营业期限审核不通过原因
    public $business_photo_status        = null;    //营业执照照片审核状态(1.通过,0.不通过)
    public $business_photo_reason        = null;    //营业执照照片审核不通过原因
    public $repast_permit_num_status     = null;    //餐饮许可编号审核状态(1.通过,0.不通过)
    public $repast_permit_num_reason     = null;    //餐饮许可编号审核不通过原因
    public $repast_permit_photo_status   = null;    //餐饮许可扫描证件审核状态(1.通过,0.不通过)
    public $repast_permit_photo_reason   = null;    //餐饮许可扫描证件审核不通过原因
    public $taxpayer_num_reason          = null;    //纳税人识别号审核不通过原因
    public $taxpayer_num_status          = null;    //纳税人识别号审核状态(1.通过,0.不通过)
    public $business_scope_reason        = null;    //经营范围不通过原因
    public $business_scope_status        = null;    //经营范围件审核状态(1.通过,0.不通过)
    public $ag_employee_id               = null;    //代理商职员id
    public $ag_position_id               = null;    //代理商职位名称(固定角色id值)
    public $platformer_id                = null;    //平台员工id
    public $pl_position_id               = null;    //平台员工职位名称(固定角色id值)
    public $audit_code                   = null;    //审核状态是否通过(1.通过,0.不通过)
    public $audit_time                   = null;    //审核时间
    public $delete                       = null;    //
    public $lastmodtime                  = null;    //



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
        $this->audit_id                     = $cursor['audit_id'];
        $this->agent_id                     = $cursor['agent_id'];
        $this->shop_name_status             = $cursor['shop_name_status'];
        $this->shop_name_reason             = $cursor['shop_name_reason'];
        $this->agent_name_status            = $cursor['agent_name_status'];
        $this->agent_name_reason            = $cursor['agent_name_reason'];
        $this->legal_person_status          = $cursor['legal_person_status'];
        $this->legal_person_reason          = $cursor['legal_person_reason'];
        $this->legal_card_status            = $cursor['legal_card_status'];
        $this->legal_card_reason            = $cursor['legal_card_reason'];
        $this->legal_card_photo_status      = $cursor['legal_card_photo_status'];
        $this->legal_card_photo_reason      = $cursor['legal_card_photo_reason'];
        $this->business_num_status          = $cursor['business_num_status'];
        $this->business_num_reason          = $cursor['business_num_reason'];
        $this->business_date_status         = $cursor['business_date_status'];
        $this->business_date_reason         = $cursor['business_date_reason'];
        $this->business_photo_status        = $cursor['business_photo_status'];
        $this->business_photo_reason        = $cursor['business_photo_reason'];
        $this->repast_permit_num_status     = $cursor['repast_permit_num_status'];
        $this->repast_permit_num_reason     = $cursor['repast_permit_num_reason'];
        $this->repast_permit_photo_status   = $cursor['repast_permit_photo_status'];
        $this->repast_permit_photo_reason   = $cursor['repast_permit_photo_reason'];
        $this->taxpayer_num_status          = $cursor['taxpayer_num_status'];
        $this->taxpayer_num_reason          = $cursor['taxpayer_num_reason'];
        $this->business_scope_status        = $cursor['business_scope_status'];
        $this->business_scope_reason        = $cursor['business_scope_reason'];
        $this->ag_employee_id               = $cursor['ag_employee_id'];
        $this->ag_position_id               = $cursor['ag_position_id'];
        $this->platformer_id                = $cursor['platformer_id'];
        $this->pl_position_id               = $cursor['pl_position_id'];
        $this->audit_code                   = $cursor['audit_code'];
        $this->audit_time                   = $cursor['audit_time'];
        $this->lastmodtime                  = $cursor['lastmodtime'];
        $this->delete                       = $cursor['delete'];
    }
}

class AuditPerson extends MgoBase
{
    protected function Tablename()
    {
        return 'audit_person';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'audit_id' => (string)$info->audit_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );
        if(null !== $info->ag_employee_id)
        {
            $set["ag_employee_id"] = (string)$info->ag_employee_id;
        }
        if(null !== $info->platformer_id)
        {
            $set["platformer_id"] = (string)$info->platformer_id;
        }


        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }


        if(null !== $info->shop_name_status)
        {
            $set["shop_name_status"] = (int)$info->shop_name_status;
        }
        if(null !== $info->shop_name_reason)
        {
            $set["shop_name_reason"] = (string)$info->shop_name_reason;
        }

        if(null !== $info->agent_name_status)
        {
            $set["agent_name_status"] = (int)$info->agent_name_status;
        }
        if(null !== $info->agent_name_reason)
        {
            $set["agent_name_reason"] = (string)$info->agent_name_reason;
        }

        if(null !== $info->legal_person_status)
        {
            $set["legal_person_status"] = (int)$info->legal_person_status;
        }
        if(null !== $info->legal_person_reason)
        {
            $set["legal_person_reason"] = (string)$info->legal_person_reason;
        }

        if(null !== $info->legal_card_status)
        {
            $set["legal_card_status"] = (int)$info->legal_card_status;
        }
        if(null !== $info->legal_card_reason)
        {
            $set["legal_card_reason"] = (string)$info->legal_card_reason;
        }

        if(null !== $info->legal_card_photo_status)
        {
            $set["legal_card_photo_status"] = (int)$info->legal_card_photo_status;
        }
        if(null !== $info->legal_card_photo_reason)
        {
            $set["legal_card_photo_reason"] = (string)$info->legal_card_photo_reason;
        }

        if(null !== $info->business_num_status)
        {
            $set["business_num_status"] = (int)$info->business_num_status;
        }
        if(null !== $info->business_num_reason)
        {
            $set["business_num_reason"] = (string)$info->business_num_reason;
        }

        if(null !== $info->business_date_status)
        {
            $set["business_date_status"] = (int)$info->business_date_status;
        }
        if(null !== $info->business_date_reason)
        {
            $set["business_date_reason"] = (string)$info->business_date_reason;
        }

        if(null !== $info->business_photo_status)
        {
            $set["business_photo_status"] = (int)$info->business_photo_status;
        }
        if(null !== $info->business_photo_reason)
        {
            $set["business_photo_reason"] = (string)$info->business_photo_reason;
        }

        if(null !== $info->repast_permit_num_status)
        {
            $set["repast_permit_num_status"] = (int)$info->repast_permit_num_status;
        }
        if(null !== $info->repast_permit_num_reason)
        {
            $set["repast_permit_num_reason"] = (string)$info->repast_permit_num_reason;
        }

        if(null !== $info->repast_permit_photo_status)
        {
            $set["repast_permit_photo_status"] = (int)$info->repast_permit_photo_status;
        }
        if(null !== $info->repast_permit_photo_reason)
        {
            $set["repast_permit_photo_reason"] = (string)$info->repast_permit_photo_reason;
        }


        if(null !== $info->business_scope_status)
        {
            $set["business_scope_status"] = (int)$info->business_scope_status;
        }
        if(null !== $info->business_scope_reason)
        {
            $set["business_scope_reason"] = (string)$info->business_scope_reason;
        }

        if(null !== $info->ag_position_id)
        {
            $set["ag_position_id"] = (string)$info->ag_position_id;
        }

        if(null !== $info->pl_position_id)
        {
            $set["pl_position_id"] = (string)$info->pl_position_id;
        }

        if(null !== $info->audit_code)
        {
            $set["audit_code"] = (int)$info->audit_code;
        }

        if(null !== $info->audit_time)
        {
            $set["audit_time"] = (int)$info->audit_time;
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

    public function GetInfoByShopId($shop_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$shop_id, "example_id");
        return ExampleInfo::ToObj($cursor);
    }

    public function GetExampleList($filter=null)
    {
        // ...
    }

    public function GetAuditList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $shop_id = $filter['shop_id'];
            if(isset($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $agent_id = $filter['agent_id'];
            if(isset($agent_id))
            {
                $cond['agent_id'] = (string)$agent_id;
            }
        }

        $cursor = $table->find($cond, ["_id"=>0])->sort(["_id"=>1]);
        return AuditPersonEntry::ToList($cursor);
    }

    public function DeleteByShop($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
        ];
        $value = array(
            '$set' => array(
                'delete'      => 1,
                "lastmodtime" => time()
            )
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
}
