<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mart/mgo_address.php");
use \Pub\Mongodb as Mgo;

function SaveAddressinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $address_id   = $_['address_id'];
    $address_type = $_['address_type'];
    $uid          = $_['uid'];
    $address      = $_['address'];
    $province     = $_['province'];
    $city         = $_['city'];
    $area         = $_['area'];
    $phone        = $_['phone'];
    $name         = $_['name'];
    $is_default   = $_['is_default'];

    $mongodb = new Mgo\Address;
    if(!$address_id)
    {
        if(!$address_type || !$uid)
        {
            LogErr("param err");
            return errcode::PARAM_ERR;
        }
        $address_id = \DaoRedis\Id::GenAddressId();
        $ctime = time();
    }
    else
    {
        $address_info = $mongodb->GetAddressById($address_id);
        $uid          = $address_info->uid;
        $address_type = $address_info->address_type;
    }
    $info = $mongodb->GetDefaultAddress($uid, $address_type);
    if(!$info->address_id)
    {
        $is_default = 1;
    }
    $entry = new Mgo\AddressEntry;
    $entry->address_id   = $address_id;
    $entry->address_type = $address_type;
    $entry->uid          = $uid;
    $entry->address      = $address;
    $entry->province     = $province;
    $entry->city         = $city;
    $entry->area         = $area;
    $entry->phone        = $phone;
    $entry->name         = $name;
    $entry->is_default   = $is_default;
    $entry->ctime        = $ctime;
    $ret = $mongodb->Save($entry);
    LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 设置新地址为默认，原来默认地址取消
    if($info->address_id && $info->address_id != $address_id && $is_default == 1)
    {
        $data = new Mgo\AddressEntry;
        $data->address_id = $info->address_id;
        $data->is_default = 0;
        $ret = $mongodb->Save($data);
        LogDebug($data);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }


    $resp = (object)array(
    );

    LogInfo("save ok");
    return 0;
}

//删除
function DeleteAddress(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $address_id_list = json_decode($_['address_id_list']);
    if(!$address_id_list)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb     = new Mgo\Address;
    $info        = $mongodb->BatchDelete($address_id_list);

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
if(isset($_['address_save']))
{
    $ret = SaveAddressinfo($resp);
}
elseif(isset($_['address_del']))
{
    $ret = DeleteAddress($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


