<?php
/*
 * [Rocky 2019-01-14]
 *  放存放消息服务相关功能
 */
namespace Pub\Vendor;
require_once("/www/public.sailing.com/php/page_util.php");

class MsgUtil
{
    // 通知自动售货机出货
    static function NotifyVendorShipment($order_id, $vendor_id)
    {
        $url = \Cfg::GetWebServerUrl(); // 注：应在当前调用本函数据的项目cfg.php中定义GetWebServerUrl()
        $ret_json_obj = \Pub\PageUtil::PostJsonEncData(
            $url,
            [ // $data
                'name' => "cmd_publish",
                'param' => json_encode([
                    'opr'   => "general",
                    'param' => [
                        'topic' => 'vendor_shipment@' . $vendor_id,
                        'data'=> [
                            'order_id' => $order_id,
                            'vendor_id' => $vendor_id,
                        ]
                    ],
                ])
            ]
        );
        return $ret_json_obj;
    }
}

