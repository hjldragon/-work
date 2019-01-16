<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建代理商生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_product_apply.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_user.php");
//Permission::PageCheck();
function SaveProductApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_name      = $_['shop_name'];
    $apply_name     = $_['apply_name'];
    $telephone      = $_['telephone'];
    $address        = $_['address'];
    $email          = $_['email'];
    $city           = $_['city'];
    $province       = $_['province'];
    $product_status = json_decode($_['product_status']);


    if(!$shop_name || !$apply_name || !$telephone)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $req_p = PageUtil::GetPhone($telephone);
    if(!$req_p)
    {
        LogErr('telephone is not verify');
        return errcode::PHONE_ERR;
    }
    if($email)
    {
        $req = PageUtil::GetEmail($email);
        if(!$req)
        {
            LogErr('emial is not verify');
            return errcode::EMAIL_ERR;
        }
    }


    $apply_id = \DaoRedis\Id::GenAgentApplyId();
    $entry    = new \DaoMongodb\ProductApplyEntry;
    $mgo      = new \DaoMongodb\ProductApply;
    $infos    = $mgo->QueryByParam($telephone, $shop_name);
    foreach ($infos as $v)
    {

        $product_status_all =  $v->product_status;
      foreach ($product_status as $p)
      {
          if(in_array($p,$product_status_all))
          {
              LogErr('apply is again');
              return errcode::APPLY_IS_EXIST;
          }
      }

    }

    $entry->shop_name       = $shop_name;
    $entry->apply_name      = $apply_name;
    $entry->apply_id        = $apply_id;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $entry->city            = $city;
    $entry->province        = $province;
    $entry->product_status  = $product_status;
    $entry->apply_time      = time();
    $entry->apply_status    = 0;
    $entry->delete          = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $msg = '您的账号'.$telephone.'申请已提交成功，欣吃货工作人员会尽快联系您，登录密码将通过短信的方式发送给您，请注意查收，谢谢！';
    $msg_ret = Util::SmsSend($telephone, $msg);

   if(0 != $msg_ret)
   {
       LogErr("err code".$msg_ret);
       //echo  '短信发送失败';
   }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//批量删除和改变代理商申请列表
function ChangeAgentApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $apply_id_list = json_decode($_['apply_id_list']);

    $type          = (int)$_['type'];
    if(!$type)
    {
        LogErr("type is null");
        return errcode::PARAM_ERR;
    }
    $mongodb = new \DaoMongodb\ProductApply;
    $ret     = $mongodb->BatchChangeById($apply_id_list, $type);
    if(0 != $ret)
    {
        LogErr("Change err");
        return errcode::SYS_ERR;
    }
    //处理成功自动生成账号然后自动发送短信
    $user  = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;
    $list  = $mongodb->GetApplyIdList(['apply_id_list'=>$apply_id_list]);

    foreach ($list as $id)
    {
        $telephone  = $id->telephone;
        $info = $user->QueryByPhone($id->telephone, UserSrc::SHOP);

        if($info->phone)
        {

            $msg = '您的账号'.$telephone.'已注册，请关注“赛领欣吃货”微信公众号下载掌柜通APP，创建您的店铺，客服专线：400-0020-158。';
            $msg_ret = Util::SmsSend($telephone, $msg);
            if(0 != $msg_ret)
            {
                LogErr("phone send err".$msg_ret);
            }
            LogErr("User have set up");
            return errcode::USER_HAD_REG;
        }
        $entry->userid   = \DaoRedis\Id::GenUserId();
        $entry->delete   = 0;
        $entry->password = "888888";
        $entry->phone    = $id->telephone;
        $entry->src      = UserSrc::SHOP;//用户的数据来源
        $entry->ctime    = time();
        $user_ret        = $user->Save($entry);
        if(0 != $user_ret)
        {
            LogErr("User Save err");
            return errcode::SYS_ERR;
        }

        $msg = '您已成功注册欣吃货扫码点餐系统，登录账号为：'.$telephone.'，初始密码：888888；请关注“赛领欣吃货”微信公众号下载掌柜通APP，进行店铺初始化，客服专线：400-0020-158。';
        $msg_ret = Util::SmsSend($telephone, $msg);
        if(0 != $msg_ret)
        {
            LogErr("phone send err".$msg_ret);
            //echo  '短信发送失败';
        }
    }



    $resp = (object)array(
    );
    LogInfo("change ok");
    return 0;
}
//处理产品使用申请
function DealProductApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $apply_id       = $_['apply_id'];
    $pl_employee_id = $_['pl_employee_id'];
    $deal_message   = $_['deal_message'];
    $purpose        = $_['purpose'];

    if(!$apply_id)
    {
        LogErr("no apply_id");
        return errcode::PARAM_ERR;
    }

    $entry    = new \DaoMongodb\ProductApplyEntry;
    $mgo      = new \DaoMongodb\ProductApply;
    $usermgo  = new \DaoMongodb\User;
    $userinfo = new \DaoMongodb\UserEntry;

    $entry->apply_id        = $apply_id;
    $entry->pl_employee_id  = $pl_employee_id;
    $entry->deal_message    = $deal_message;
    $entry->purpose         = $purpose;
    $entry->apply_status    = ProductApplyStatus::DEAL;
    $entry->deal_time       = time();
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //处理后意向高和中的都要发送短信并注册账号
    if($purpose != PURPOSE::LOW){

        $pr_info  = $mgo->GetInfoById($apply_id);
        $user     = $usermgo->QueryByPhone($pr_info->telephone, UserSrc::SHOP);
        if($user->userid)
        {
            $msg     = '该手机号码已注册过商户账号，无法再次申请，您可以关注“赛领欣吃货”公众号进入商城查看更多商品，感谢您对赛领欣吃货的支持。';
            $msg_ret = Util::SmsSend($pr_info->telephone, $msg);
            if(0 != $msg_ret)
            {
                LogErr("err code".$msg_ret);
                //echo  '短信发送失败';
            }
            LogErr("User have create");
            return errcode::USER_HAD_REG;
        }
        $userinfo->userid     = \DaoRedis\Id::GenUserId();
        $userinfo->phone      = $pr_info->telephone;
        $userinfo->ctime      = time();
        $userinfo->password   = '888888';
        $userinfo->src        = UserSrc::SHOP; // 运营端用户
        $userinfo->delete     = 0;
        $ret  = $usermgo->Save($userinfo);
        if(0 != $ret)
        {
            LogErr("register user err, ret=[$ret]");
            return errcode::USER_SETTING_ERR;
        }
        $msg     = '您已成功申请欣吃货商户，商户登陆账号：'.$pr_info->telephone.'，初始化密码：888888，祝您使用愉快，谢谢。';
        $msg_ret = c($pr_info->telephone, $msg);
        if(0 != $msg_ret)
        {
            LogErr("err code".$msg_ret);
            //echo  '短信发送失败';
        }
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
$ret  = -1;
$resp = (object)array();
if(isset($_['product_apply_save']))
{
    $ret = SaveProductApply($resp);

}elseif(isset($_['change_apply_status']))
{
    $ret = ChangeAgentApply($resp);
}elseif(isset($_['deal_product_apply']))
{
    $ret = DealProductApply($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
