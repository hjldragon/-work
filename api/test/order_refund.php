<?php
set_include_path("/www/shop.jzzwlcm.com/php:/www/www.ob.com/php/:/www/public.sailing.com/php");
require_once("redis_login.php");
require_once("page_util.php");

if($_REQUEST['send'])
{
    $shop_id  = $_REQUEST['shop_id']?:"ABC";
    $order_id = $_REQUEST['order_id']?:"123";

    $ret_json = NotifyOrderRefund($shop_id, $order_id);

    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}

// 发送订单退款通知
function NotifyOrderRefund($shop_id, $order_id)
{
    $url = 'http://127.0.0.1:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "general",
                'param' => [
                    'topic' => "order_refund@" . $shop_id,
                    'data'=> [
                        'order_id' => $order_id,
                    ]
                ],
            ])
        ]
    );
    return $ret_json;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>退款推送测试</title>
<script type="text/javascript" src="./js/md5.min.js"></script>
<script type="text/javascript" src="./js/aes.min.js"></script>
<script type="text/javascript" src="./js/pad-zeropadding.js"></script>
<script type="text/javascript" src="./js/jsencrypt.min.js"></script>
<script type="text/javascript" src="./js/crypt.js"></script>
<script type="text/javascript" src="./js/vue.min.js"></script>
<script type="text/javascript" src="./js/websocket.js"></script>
<body>

<style type="text/css">
.msg {
    border: 1px solid #bbbbbb;
    padding: 0.5rem;
    font-size: 1.6rem;
    margin: 0;
}
</style>



<div id="app">
    <p>接收到的退款订单数据：</p>
    <pre class="msg">{{msg}}</pre>
    <hr/>
    <a :href="url" target="empty">发送测试数据</a> <br />
    shop_id: <input v-model="shop_id"> <br/>
    order_id: <input v-model="order_id"> <br/>
    <br/>
    <iframe name="empty" width="100%" height="200"></iframe>
</div>



<script type="text/javascript">
function toJson(obj)
{
    return JSON.stringify(obj, "", "    ");
}

const vm = new Vue({
    el: '#app',
    data: {
        msg : "[空]",
        token: "T1J1ZlKaZLQnH7Lt",
        key: "b9EkNUJivcKPF3vw",
        shop_id: "1001",
        order_id: "888",
    },
    computed: {
        url(){
            return "?send=1"
                    + "&token=" + this.token
                    + "&shop_id=" + this.shop_id
                    + "&order_id=" + this.order_id
        }
    },
    mounted(){
        // let url = "ws://127.0.0.1:13010/websocket";
        let url = "ws://api.jzzwlcm.com:13010/websocket";
        window.WebSock.Init(url, this.token, this.key, ()=>{

            window.WebSock.Subscribe("general",
                {
                    topic : "order_refund@" + this.shop_id,
                },
                (resp) => {
                    console.log(resp);
                    this.msg = toJson(resp);
                }
            );
        });
    },
});
</script>
