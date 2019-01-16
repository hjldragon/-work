<?php
set_include_path("/www/shop.jzzwlcm.com/php:/www/www.ob.com/php/:/www/public.sailing.com/php");
require_once("redis_login.php");
require_once("page_util.php");

// 测试
if($_REQUEST['send'])
{
    $token  = $_REQUEST['token']?:"";
    $order_id = $_REQUEST['order_id']?:"";

    $ret_json = NotifyWxPay($order_id, $token);

    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}

function NotifyWxPay($order_id, $token)
{
    $url = 'http://127.0.0.1:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "once",
                'param' => [
                    'topic' => "wxpay_notify@" . $token,
                    'data'=> [
                        'order_id' => $order_id,
                        'price' => 1,
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
<title>微信支付完成的通知消息</title>
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
}
</style>



<div id="app">
    <p>接收到的数据：</p>
    <pre class="msg">{{msg}}</pre>
    <hr/>
    <a :href="url" target="empty" @click="Send">发送测试数据</a> <br />
    token: <input v-model="token" disabled=disabled> <br/>
    key: <input v-model="key" disabled=disabled> <br/>
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
        token: (new URL(location.href)).searchParams.get('t') || "T1QMqzGrnEiUMBEg",
        key: (new URL(location.href)).searchParams.get('k') || "41TIpknR3nrC2Rwd",
        order_id: "SH5",
    },
    computed: {
        url(){
            return "?send=1"
                    + "&token=" + this.token
                    + "&order_id=" + this.order_id
        }
    },
    watch:{
    },
    mounted(){
        // let url = "ws://127.0.0.3:13011/websocket";
        // let url = "ws://api.jzzwlcm.com:13011/websocket";
        // let url = "ws://api.xinchihuo.com.cn:13011/websocket";
        let url = `ws://${location.hostname}:13010/websocket`;
        window.WebSock.Init(url, this.token, this.key, ()=>{

            window.WebSock.Subscribe("once",
                {
                    topic : "wxpay_notify@" + this.token,
                },
                (resp) => {
                    console.log(resp);
                    this.msg = toJson(resp);
                }
            );
        });
    },
    methods: {
        Send()
        {
            // this.msg = "";
        }
    }
});
</script>
