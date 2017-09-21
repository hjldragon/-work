<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("mgo_spec.php");
require_once("redis_id.php");
require_once("const.php");



 

function SaveSpecinfo(&$resp)
{
    
    $_ = $GLOBALS["_"];
    LogDebug($_);
    //var_dump(json_decode($_['list']));die;
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $spec_id       = (string)$_['spec_id'];
    $food_id       = (string)$_['food_id'];
    $title         = (string)$_['title'];
    $list          = json_decode($_['list']);
    $type          = (int)$_['type'];


    if(!$spec_id)
    {
        $spec_id = \DaoRedis\Id::GenSpecId();
    }
    
    $data = [];
    if(null != $list) {
        foreach($list as $i => $item)
        {
            $id = $item->id;
            if(!$id)
            {
                $id = \DaoRedis\Id::GenSubSpecId();
            }

            if(IsPrice::YES == $type){
                array_push($data, new \DaoMongodb\SpecList([
                    'id'                => (string)$id,
                    'title'             => (string)$item->title,
                    'original_price'    => (float)$item->original_price,
                    'discount_price'    => (float)$item->discount_price,
                    'vip_price'         => (float)$item->vip_price,
                    'festival_price'    => (float)$item->festival_price,
                    'default'           => (int)$item->default,
                ]));
            }else{
                array_push($data, new \DaoMongodb\SpecList([
                    'id'        => (string)$id,
                    'title'     => (string)$item->title,
                ]));
            }
        }
    }


    $entry = new \DaoMongodb\SpecEntry;

    $mongodb = new \DaoMongodb\Spec;

    $entry->spec_id       = $spec_id;
    $entry->food_id       = $food_id;
    $entry->title         = $title;
    $entry->list          = $data;
    $entry->type          = $type;
    $entry->delete        = 0;
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

// function GetList(&$resp){
//     $_ = $GLOBALS["_"];
//     LogDebug($_);
//     if(!$_)
//     {
//         LogErr("param err");
//         return errcode::PARAM_ERR;
//     }
//     $id = $_['id'];

//     $mgo = new \DaoMongodb\Spec;
//     $info = $mgo->GetSpecPriceById($id);

//      $resp = (object)array(
//         'info' => $info
//     );
//     LogDebug($resp);
//     LogInfo("--ok--");
//     return 0;
// }
// 

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveSpecinfo($resp);
}

// if(isset($_['tt'])){
   
//     $ret = GetList($resp);
// }

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


