<?php
declare(encoding='UTF-8');
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once "WxUtil.php";
require_once "WxCfg.php";

//"touser":"OPENID",//微信模板发送（POST）
//           "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
//           "url":"http://weixin.qq.com/download",
//           "miniprogram":{
//    "appid":"xiaochengxuappid12345",
//             "pagepath":"index?foo=bar"
//           },
//           "data":{
//    "first": {
//        "value":"恭喜你购买成功！",
//                       "color":"#173177"
//                   },
//                   "keyword1":{
//        "value":"巧克力",
//                       "color":"#173177"
//                   },
//                   "keyword2": {
//        "value":"39.8元",
//                       "color":"#173177"
//                   },
//                   "keyword3": {
//        "value":"2014年9月22日",
//                       "color":"#173177"
//                   },
//                   "remark":{
//        "value":"欢迎再次购买！",
//                       "color":"#173177"
//                   }
//           }
class WxTemplate
{

function SendTemplate($openid, $info)
{
        $access_token = \Pub\Wx\Util::GetToken()->access_token;
        $template_id  = \Pub\Wx\Cfg::TEMPLATE_ID;
        $list = (object)[];

        if(!$info->wx_id)
        {
            LogDebug('no id in mgo weixin');
            return errcode::WEIXIN_NO_REBINDING;
        }
        if($info->apply_status ==ApplyStatus::APPLYPASS)
        {
            $message  = '代理商初审不通过';
        }elseif($info->apply_status == ApplyStatus::APPLYTHOUR)
        {
            $message  = '代理商初审通过';
        }elseif($info->apply_status == ApplyStatus::APPLYBUS)
        {
            $message  = '代理商工商审核已提交';
        }elseif($info->apply_status == ApplyStatus::APPLYBUSPASS)
        {
            $message  = '代理商工商审核不通过';
        }elseif($info->apply_status == ApplyStatus::APPLYBUSTHOUR)
        {
            $message  = '代理商工商审核通过';
        }else{
            $message  = '其他审核正在进行';
        }

        $first->value    =  '代理商审核进度通知';
        $keyword1->value = $message;
        $keyword2->value = $info->apply_name;
        $keyword3->value = date('Y-m-d H:i:s',time());
        $remark->value   = "欣吃货";

        $date->first     = $first;
        $date->keyword1  = $keyword1;
        $date->keyword2  = $keyword2;
        $date->keyword3  = $keyword3;
        $date->remark    = $remark;

        $main_domain       = Cfg::instance()->GetMainDomain();
        $list->touser      = $openid;
        $list->url         = 'http://platform.'.$main_domain.'/phone/#/applystatus?wx_id='.$info->wx_id;
        $list->template_id = $template_id;
        $list->data        = $date;
        $url = \Pub\Wx\Cfg::WX_URL_TEMPLATE.'?access_token='.$access_token;

        \Pub\Wx\Util::HttpPost($url, json_encode($list));

}
}

?>