<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("mgo_spec.php");
require_once("redis_id.php");
require_once("const.php");



 

function SaveSpecinfo(&$resp,$info=[])
{
    
    $_ = $GLOBALS["_"];
    LogDebug($_);
    
    if(!$_ && !$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    
    if(!PageUtil::LoginCheck() && !$info)
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }
    if($info){
        $spec_id = (string)$info['spec_id'];
        $food_id = (string)$info['food_id'];
        $title   = (string)$info['title'];
        $list    = $info['list'];
        $type    = (int)$info['type'];
        $is_use  = (int)$info['is_use'];
    }else{
        $spec_id = (string)$_['spec_id'];
        $food_id = (string)$_['food_id'];
        $title   = (string)$_['title'];
        $list    = json_decode($_['list']);
        $type    = (int)$_['type'];
        $is_use  = (int)$_['is_use'];
    }

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
                    //'default'           => (int)$item->default,
                    'is_use'            => (int)$item->is_use
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
    $entry->is_use        = $is_use;
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



$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveSpecinfo($resp);
}



$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


