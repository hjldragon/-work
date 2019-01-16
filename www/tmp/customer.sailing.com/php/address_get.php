<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取消息类别信息
 */
require_once("current_dir_env.php");
require_once("const.php");
require_once("mgo_address.php");
//$_=$_REQUEST;


function GetAddressList(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_['userid'];
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb     = new \DaoMongodb\Address;
    $list        = $mongodb->GetListByUser($userid);

    $resp = (object)array(
        'info' => $list
    );
    LogInfo("--ok--");
    return 0;
}

function GetAddressInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $address_id = $_['address_id'];
    if(!$address_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb     = new \DaoMongodb\Address;
    $info        = $mongodb->GetAddressById($address_id);

    $resp = (object)array(
        'info' => $info
    );
    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();

if(isset($_["address_list"]))
{
    $ret = GetAddressList($resp);
}
elseif(isset($_["address_info"]))
{
    $ret = GetAddressInfo($resp);
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
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
