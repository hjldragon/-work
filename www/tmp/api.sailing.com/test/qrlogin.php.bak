<?php
set_include_path("/www/shop.jzzwlcm.com/php:/www/www.ob.com/php/");
require_once("page_util.php");

if($_REQUEST['login1'])
{
    // 使用签名加密形式发送
    $url = 'http://127.0.0.1:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "once",
                'param' => [
                    'topic' => "login_qrcode@T1J1ZlKaZLQnH7Lt",
                    'data'=> [
                        'login_time' => time(),
                        'login_ret' => 1,
                    ]
                ],
            ])
        ],
        [ // $opt
            'encmode' => "aes",
        ]
    );
    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}
if($_REQUEST['login2'])
{
    // 使用签名加密形式发送
    $url = 'http://127.0.0.1:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "login_qrcode",
                'param' => [
                    'token' => "T1J1ZlKaZLQnH7Lt",
                    'data'=> [
                        'login_time' => time(),
                        'login_ret' => 2,
                    ]
                ],
            ])
        ],
        [ // $opt
            'encmode' => "aes",
        ]
    );
    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>扫码测试</title>
<script type="text/javascript" src="./js/md5.min.js"></script>
<script type="text/javascript" src="./js/aes.min.js"></script>
<script type="text/javascript" src="./js/pad-zeropadding.js"></script>
<script type="text/javascript" src="./js/jsencrypt.min.js"></script>
<script type="text/javascript" src="./js/crypt.js"></script>
<script type="text/javascript" src="./js/vue.min.js"></script>
<script type="text/javascript" src="./js/websocket.js"></script>
<body>

<style type="text/css">
.cur_user,
.group_passwd,
.last_user {
    width: 5rem;
    margin-right: 1rem;
}
.msg {
    display: inline-block;
    background: #bbbbbb;
    margin: 1rem;
    padding: 0.5rem;
    font-size: 2rem;
}
.head {
    margin: 0.3rem;
}
.chat .content {
    border: 1px solid #bbbbbb;
    min-width: 40rem;
    min-height: 20rem;
    padding: 0.5rem;
    font-size: 1.8rem;
}
</style>



<div id="app">
    （注：侦听接收到数据后，侦听失效，需要再次调用侦听，才能接收下次的数据）
    <br/>
    <input type="button" @click="login1" value="侦听登录返回的数据（旧）">
    <input type="button" @click="login2" value="侦听登录返回的数据（新）">
    <div>{{msg}}</div>
    <hr/>
    <a href="?login1=1" target="empty">发旧登录请求</a> &nbsp;&nbsp;
    <a href="?login2=1" target="empty">发新登录请求</a>
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
    },
    mounted(){
        // let url = "ws://127.0.0.1:13010/websocket";
        let url = "ws://120.24.40.134:13010/websocket";
        window.WebSock.Init(url, "T1J1ZlKaZLQnH7Lt", "Ib2jGen9h4kPZ2Zx", ()=>{
        });
    },
    methods: {
        login1(){
            this.time = (new Date).getTime();
            window.WebSock.Subscribe("once",
                {
                    topic : "login_qrcode@T1J1ZlKaZLQnH7Lt",
                },
                (resp) => {
                    console.log(resp);
                    this.msg = toJson(resp);
                }
            );
        },
        login2(){
            this.time = (new Date).getTime();
            window.WebSock.Subscribe("login_qrcode",
                {
                    token : "T1J1ZlKaZLQnH7Lt",
                },
                (resp) => {
                    console.log(resp);
                    this.msg = toJson(resp);
                }
            );
        },
    }
});
</script>
