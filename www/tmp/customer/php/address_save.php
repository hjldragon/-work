<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("mgo_address.php");
require_once("redis_id.php");
require_once("const.php");
 

function SaveAddressinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
   
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    
    $address_id     = $_['address_id'];
    $userid         = $_['userid'];
    $name           = $_['name'];
    $sex            = $_['sex'];
    $phone          = $_['phone'];
    $address_region = $_['address_region'];
    $address_num    = $_['address_num'];
    $province       = $_['province'];
    $city           = $_['city'];
    $area           = $_['area'];
    $mongodb = new \DaoMongodb\Address;
    if(!$address_id)
    {
        $address_id = \DaoRedis\Id::GenAddressId();
    }
    else
    {   
        $data['userid'] = $userid;
        $data['address_region'] = $address_region;
        $data['area'] = $area;
        $info = $mongodb->GetAddress($data);
        if($info->address_id)
        {
            LogErr("address repeat");
            return errcode::ADDRESS_REPEAT;
        }
    }
    $entry = new \DaoMongodb\AddressEntry;
    $entry->address_id     = $address_id;
    $entry->userid         = $userid;
    $entry->name           = $name;
    $entry->sex            = $sex;
    $entry->phone          = $phone;
    $entry->ctime          = time();
    $entry->address_region = $address_region;
    $entry->address_num    = $address_num;
    $entry->province       = $province;
    $entry->city           = $city;
    $entry->area           = $area;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

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
    $address_id = $_['address_id'];
    if(!$address_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb     = new \DaoMongodb\Address;
    $info        = $mongodb->Delete($address_id);

    if (0 != $ret) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;

}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveAddressinfo($resp);
}
elseif(isset($_['del']))
{
    $ret = DeleteAddress($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


