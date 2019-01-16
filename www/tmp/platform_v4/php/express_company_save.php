<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 快递公司保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mart/mgo_express_company.php");
use \Pub\Mongodb as Mgo;

function SaveExpressCompanyinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $express_company_id    = $_['express_company_id'];
    $express_company_name  = $_['express_company_name'];
    $express_company_code  = $_['express_company_code'];
    $express_company_logo  = $_['express_company_logo'];
    $express_company_phone = $_['express_company_phone'];

    if(!$express_company_name)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new Mgo\ExpressCompany;
    if(!$express_company_id)
    {
        $express_company_id = \DaoRedis\Id::GenExpressCompanyId();
        $ctime = time();
    }
    $info = $mongodb->GetExpressCompanyByName($express_company_name);
    if($info->express_company_id && $info->express_company_id != $express_company_id)
    {
        LogErr("express_company_name:[$express_company_name] exist");
        return errcode::NAME_IS_EXIST;
    }
    $entry = new Mgo\ExpressCompanyEntry;
    $entry->express_company_id    = $express_company_id;
    $entry->express_company_name  = $express_company_name;
    $entry->express_company_code  = $express_company_code;
    $entry->express_company_logo  = $express_company_logo;
    $entry->express_company_phone = $express_company_phone;
    $entry->ctime                 = $ctime;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );

    LogInfo("save ok");
    return 0;
}

//删除
function DeleteExpressCompany(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $express_company_id_list = json_decode($_['express_company_id_list']);
    if(!$express_company_id_list)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb     = new Mgo\ExpressCompany;
    $info        = $mongodb->BatchDelete($express_company_id_list);

    if (0 != $ret) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;

}


$ret = -1;
$resp = (object)array();
if(isset($_['express_company_save']))
{
    $ret = SaveExpressCompanyinfo($resp);
}
elseif(isset($_['express_company_del']))
{
    $ret = DeleteExpressCompany($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


