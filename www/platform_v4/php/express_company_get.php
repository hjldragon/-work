<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取快递公司信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mart/mgo_express_company.php");
use \Pub\Mongodb as Mgo;

//快递公司详情
function GetExpressCompanyInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $express_company_id = (string)$_['express_company_id'];
    if(!$express_company_id)
    {
        LogErr("no express_company_id");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\ExpressCompany;
    $info = $mgo->GetExpressCompanyById($express_company_id);

    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetExpressCompanyList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\ExpressCompany;
    $list = $mgo->GetExpressCompanyList();
    $resp = (object)array(
        'list' => $list
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["express_company_info"]))
{
    $ret = GetExpressCompanyInfo($resp);
}
elseif(isset($_["express_company_list"]))
{
    $ret = GetExpressCompanyList($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
