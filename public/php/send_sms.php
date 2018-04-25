<?php
/*
 * [Rocky 2018-1-5 11:37:52]
 * 短信发送
 */
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("errcode.php");
class Sms
{
    static function GetSms($phone,$code)
    {
        $clapi  = new ChuanglanSmsApi();
        $msg    = '【赛领欣吃货】尊敬的用户，您本次的验证码为' . $code . '有效期5分钟。打死不要将内容告诉其他人！';
        $result = $clapi->sendSMS($phone, $msg);
        //LogDebug($result);
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                LogDebug('短信发送成功！');
            } else {
                return $output['errorMsg'] . errcode::PHONE_SEND_FAIL;
            }
        }
    }
}

?>