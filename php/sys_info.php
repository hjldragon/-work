<?php
/*
 * [rockyshi 2014-10-04]
 * 取系统信息
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");


function GetSysInfo(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // ob_start();
    // phpinfo();
    // $info = ob_get_contents();
    // ob_end_clean();
    $info = array(
        now         => Util::TimeTo(0, $format='%Y-%m-%d %H:%M:%S'),
        php_version => PHP_VERSION,
        os          => php_uname(),
        host_ip     => Util::GetHostIp()
    );

    $info = str_replace(array("Array", "stdClass Object", "(", ")\n", "\n\n", "\n"),
                        array("", "", "", "", "\n", "</li>\n<li>"),
                        print_r($info, 1) . print_r($_SERVER, 1));
    $resp = (object)array(
        info => $info
    );
    LogInfo("--ok--");
    return 0;
}

$_ = PageUtil::DecSubmitData();
LogDebug($_);
$ret = 0;
$resp = null;
$ret =  GetSysInfo($resp);
$html = json_encode((object)array(
    ret   => $ret,
    // data  => $resp
    crypt => 1, // 是加密数据标记
    data  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
