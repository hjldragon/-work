<?php
/*
 * 用户绑定邮件文件
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("redis_id.php");
require_once ("mgo_shop.php");
//点击绑定邮箱成功
$_=$_REQUEST;
function BindEmailSucceed(&$resp)
{
    $_ = $GLOBALS['_'];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    $userid  = $_['userid'];
    $passwd  = $_['passwd'];
    $mgo     = new DaoMongodb\Shop;
    $shop    = $mgo->GetShopById($shop_id);

    if ($passwd != $shop->mail_vali->passwd)
    {
        return errcode::MAIL_CODE_ERR;
    }
    //验证成功后开始绑定邮箱号
    $email = $shop->mail_vali->mail;

    $shop->id     = $shop_id;
    $shop->email  = $email;
    $ret_shop     = $mgo->Save($shop);
    $entry        = new DaoMongodb\User;
    $user         = $entry->QueryById($userid);
    $user->userid = $userid;
    $user->email  = $email;
    $ret_user     = $entry->Save($user);
    if (0 != $ret_shop || 0 != $ret_user)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'bind_email' => "绑定成功",
    ];
    LogInfo("email binding successful");
    return 0;

}
$ret = -1;
$resp = (object)array();

if (isset($_['bind_email']))
{
    $ret = BindEmailSucceed($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
     'data'  => $resp,
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>