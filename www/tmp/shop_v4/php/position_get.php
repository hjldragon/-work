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
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
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

function GetPositionList(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_POSITION);
    $_         = $GLOBALS["_"];
    $is_start  = $_['is_start'];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = \Cache\Login::GetShopId();
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::USER_NOLOGIN;
    }

    $mgo      = new \DaoMongodb\Position;
    $employee = new \DaoMongodb\Employee;
    if($is_start)
    {
        $list    = $mgo->GetStartPosition($shop_id);
    }else{
        $list = $mgo->GetAllPositionByShop($shop_id);
        $num      = 0;
        foreach ($list as &$v)
        {
            $employee->GetEmployeeList($shop_id,['position_id'=>$v->position_id], $num);
            $v->people_num =  $num;
        }
    }


    $resp = (object)array(
        'position_list' => $list
    );
    //LogDebug($resp);
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
    $ret = GetPositionList($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>