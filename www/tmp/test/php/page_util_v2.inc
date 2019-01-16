<?php
/*
 * 关于页面类操作工具代码
 * [rockyshi 2014-08-20]
 *
 */
namespace Pub;
require_once("errcode.php");
require_once("crypt.php");

class PageUtil{

// 输出到前端（默认输出后中止运行）
static function HtmlOut($ret, $data, $opt=[])
{
    $exit = true;
    if(isset($opt['exit']))
    {
        $exit = $opt['exit'];
    }

    $html_out_callback = null;
    if(isset($opt['html_out_callback']))
    {
        $html_out_callback = $opt['html_out_callback'];
    }
    else if(isset($GLOBALS['html_out_callback']))
    {
        $html_out_callback = $GLOBALS['html_out_callback'];
    }

    $crypt = "";
    if(isset($opt['crypt']))
    {
        $crypt = $opt['crypt'];
    }

    $out = [
        "ret" => $ret,
        "msg" => \errcode::toString($ret),
        // "data" => $data,
        // 'crypt' => 1, // 是加密数据标记
        // 'data'  => PageUtil::EncRespData(json_encode($resp))
    ];
    if(1 == $crypt)
    {
        $out['crypt'] = 1;
        $out['data'] = PageUtil::EncRespData(json_encode($resp));
    }
    else
    {
        $out['data'] = $data;
    }
    // 直接到回调中，由回调处理；
    if(is_callable($html_out_callback))
    {
        $html_out_callback($ret, $data, $opt);
        return;
    }
    // 直接输出
    echo json_encode($out);
    if($exit)
    {
        exit(0);
    }
    return $out;
}

// 输出模板
// 注: $param会在$template文件内部做为变量使用
static function TemplateOut($template, $param, $opt=[])
{
    $opt = (object)$opt;
    // 不输出，返回解析后的模板内容
    if(0 === $opt->out)
    {
        ob_start();
        include($template);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    // 直接输出到客户端
    include($template);
    return "";
}

// 使用协商随机密码加密返回到前台的数据
static function EncRespData($data)
{
    return Crypt::encode(\Cache\Login::GetKey(), $data);
}

}