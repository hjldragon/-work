<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_login.php");
require_once("mgo_customer.php");
require_once("mgo_employee.php");

Permission::PageCheck();
//保存客户信息
function SaveCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    
    $customer_id           = $_['customer_id'];
    $is_vip                = $_['is_vip'];
    $remark                = $_['remark'];
    $mongodb               = new \DaoMongodb\Customer;
    $entry                 = new \DaoMongodb\CustomerEntry;
    $entry->customer_id    = $customer_id;
    $entry->is_vip         = $is_vip;
    $entry->mtime          = time();
    $entry->remark         = $remark;
    $ret                   = $mongodb->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    LogInfo("save ok");
    LogDebug($resp);
    return 0;
}

//删除用户信息
function DeleteCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
   
    $customer_id_list = json_decode($_['customer_id_list']);
    $mongodb = new \DaoMongodb\Customer;
    $ret = $mongodb->BatchDeleteById($customer_id_list);
    
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    
    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['customer_save']))
{
    $ret = SaveCustomer($resp);
}elseif (isset($_['del_customer']) || isset($_['del']))
{   
    $ret = DeleteCustomer($resp);
} else
{
    LogErr("param err");
    $ret = errcode::PARAM_ERR;
}
$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
