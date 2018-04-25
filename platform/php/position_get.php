<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_position.php");
require_once("permission.php");

Permission::PageCheck();
// $_=$_REQUEST;
//获取一个职位及权限
function GetPositionInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $position_id = $_['position_id'];
    $shop_id     = \Cache\Login::GetShopId();
    if(!$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    $mgo         = new \DaoMongodb\Position;
    $info        = $mgo->GetPositionById($shop_id, $position_id);
    $permission  = PermissionSplit($info->position_permission);
    $resp = (object)[
        'position_info' => $info,
        'permission'    => $permission,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//位运算分解
function PermissionSplit($n) {
    $n   |= 0;
    $pad = 0;
    $arr = [];
    while ($n)
    {
        if ($n & 1) array_push($arr, 1 << $pad);
        $pad++;
        $n >>= 1;
    }
    return $arr;
}

function GetPositionNameList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = \Cache\Login::GetShopId();
        if(!$shop_id)
        {
            return errcode::USER_NOLOGIN;
        }

    $mgo  = new \DaoMongodb\Position;
    $list = $mgo->GetPositionByShop($shop_id);
//    foreach ($list as $v)
//    {
//        $list['position_permission']=[];
//        $permission  = PermissionSplit($v->position_permission);
//    }

    $resp = (object)array(
        'position_list' => $list

    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["get_position_info"]))
{
    $ret = GetPositionInfo($resp);
}
elseif(isset($_["get_position_list"]))
{
    $ret = GetPositionNameList($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>