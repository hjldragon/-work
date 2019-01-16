<?php
set_include_path("/www/shop.jzzwlcm.com/php:/www/www.ob.com/php/");
require_once("redis_login.php");
require_once("page_util.php");

if($_REQUEST['send'])
{
    $shop_id = $_REQUEST['shop_id']?:"ABC";
    $news_id = $_REQUEST['news_id']?:"666";

    $ret_json = NotifyNewsChange($shop_id, $news_id);

    echo "<pre>\n";
    var_dump($ret_json);
    exit(0);
}

// 公告变动通知
function NotifyNewsChange($shop_id, $news_id)
{
    $url = 'http://127.0.0.1:13010/wbv';
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "general",
                'param' => [
                    'topic' => "news@" . $shop_id,
                    'data'=> [
                        'news_id' => $news_id,
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
<title>公告消息</title>
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
    <p>接收到的公告数据：</p>
    <pre class="msg">{{msg}}</pre>
    <hr/>
    <a :href="url" target="empty">发送测试数据</a> <br />
    shop_id: <input v-model="shop_id"> <br/>
    news_id: <input v-model="news_id"> <br/>
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
        news_id: "666",
    },
    computed: {
        url(){
            return "?send=1"
                    + "&token=" + this.token
                    + "&shop_id=" + this.shop_id
                    + "&news_id=" + this.news_id
        }
    },
    mounted(){
        // let url = "ws://127.0.0.1:13010/websocket";
        let url = "ws://api.jzzwlcm.com:13010/websocket";
        window.WebSock.Init(url, this.token, this.key, ()=>{

            window.WebSock.Subscribe("general",
                {
                    topic : "news@" + this.shop_id,
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
