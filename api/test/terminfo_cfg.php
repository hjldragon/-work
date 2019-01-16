<?php
set_include_path("/www/shop.jzzwlcm.com/php:/www/www.ob.com/php/:/www/public.sailing.com/php");
require_once("redis_login.php");
require_once("page_util.php");

if($_REQUEST['send'])
{
    $token = $_REQUEST['token']?:"";
    $cfg = json_decode($_REQUEST['cfg']?:"{}");
    $ret_json = NotifyTerminfoCfg($token, $cfg);

    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}

function NotifyTerminfoCfg($token, $cfg)
{
    // $url = 'http://127.0.0.1:13010/wbv';
    $url = 'http://192.168.5.117:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "general",
                'param' => [
                    'topic' => "terminfo_cfg@" . $token,
                    'data' => (object)[
                        'cfg' => $cfg,
                    ],
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
<title>终端设置测试</title>
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
    max-height: 300px;
    overflow: auto;
}
.cfg {
    width: 600px;
    height: 300px;
}
</style>



<div id="app">
    <p>接收到的数据：</p>
    <pre class="msg">{{msg}}</pre>
    <hr/>
    <a :href="url" target="empty" @click="clear">发送测试数据</a> <br />
    token: <input v-model="token"> <br/>
    cfg: <textarea class="cfg" v-model="cfg">
            {
                "Log" : {
                    "Level" : 4
                }
            }
    </textarea>
    <br/>
    <iframe name="empty" width="100%" height="40"></iframe>
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
        cfg: "",
        token: "TestToken",
        key: "TestDataKey",
    },
    computed: {
        url(){
            return "?send=1"
                    + "&token=" + this.token
                    + "&cfg=" + encodeURIComponent(this.cfg)
        }
    },
    mounted(){
        // let url = "ws://127.0.0.1:13010/websocket";
        let url = "ws://192.168.5.117:13010/websocket";
        // let url = "ws://api.jzzwlcm.com:13010/websocket";
        window.WebSock.Init(url, this.token, this.key, ()=>{
            window.WebSock.Subscribe("general",
                {
                    topic : "terminfo_cfg@" + this.token,
                },
                (resp) => {
                    console.log(resp);
                    this.msg = toJson(resp);
                }
            );
        });
    },
    methods: {
        clear(){
            this.msg = "";
        }
    }
});
</script>
