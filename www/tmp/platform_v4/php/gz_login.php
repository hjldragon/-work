<?php
ob_start();
require_once("current_dir_env.php");
require_once("const.php");
require_once("cache.php");
require_once("cfg.php");
ob_end_clean();
//代理商申请跳转公众号获取id发送推送消息页面
if(!$_REQUEST['wx_id'] && !$_REQUEST['debug'])
{
    if(PageUtil::IsWeixin())
    {
        $url = Cfg::instance()->GetWxUrlAddr() . "/wx_userinfo.php?src=3";
    }
//    elseif(PageUtil::IsAlipay())//<<<<<支付宝还未使用
//    {
//        $url = Cfg::instance()->GetAliipayUrlAddr() . "/alipay_userinfo.php?{$_SERVER['QUERY_STRING']}";
//    }
    header("Location: $url");
    exit();
}


function IndexOne()
{

        $main_domain = Cfg::instance()->GetMainDomain();
        $url = "http://platform.$main_domain/phone/#/agentapply?wx_id=".$_REQUEST['wx_id'];
        header("Location: $url");
        exit();
}
function IndexTwo()
{

    $main_domain = Cfg::instance()->GetMainDomain();
    $url = "http://platform.$main_domain/phone/#/applystatus?wx_id=".$_REQUEST['wx_id'];
    header("Location: $url");
    exit();
}
function alt($msg){
echo <<<eof
<script>
alert("$msg");
</script>
eof;
exit(0);
}


if(isset($_REQUEST['status']))
{
    $ret = IndexTwo();
}else{
    $ret = IndexOne();
}


?>