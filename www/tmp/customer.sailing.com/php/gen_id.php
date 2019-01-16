<?php
/*
 * [Rocky 2017-05-03 16:15:48]
 * 取id
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("redis_id.php");


function GenId(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $type = $_['type'];
    $id = 0;

    if("userid" == $type)
    {
        $id = \DaoRedis\Id::GenUserId();
    }
    elseif("food" == $type)
    {
        $id = \DaoRedis\Id::GenFoodId();
    }
    elseif("category" == $type)
    {
        $id = \DaoRedis\Id::GenCategoryId();
    }
    elseif("printer" == $type)
    {
        $id = \DaoRedis\Id::GenPrinterId();
    }
    elseif("shop" == $type)
    {
        $id = \DaoRedis\Id::GenShopId();
    }
    elseif("order" == $type)
    {
        $id = \DaoRedis\Id::GenOrderId();
    }
    else
    {
        LogErr("param err, type:$type");
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'id' => $id
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if($_["genid"])
{
    $ret = GenId($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
