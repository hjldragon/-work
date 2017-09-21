<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
微信号：shizw2008
-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="./js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="./js/util.js"></script>
<script type="text/javascript">
</script>
<script type="text/javascript">
$(function() {

    // // $("#bt").click(function(){
    //     // $.getJSON(
    //     $.getScript(
    //         "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxaaceede0e7695fcf&redirect_uri=http%3A%2F%2Fwx.jzzwlcm.com%2Fnotify&response_type=code&scope=snsapi_base&state=1236&connect_redirect=1#wechat_redirect",
    //         function(resp){
    //             $("#msg").text(resp);
    //             // $("#msg").text(JSON.stringify(resp));
    //         }
    //     );

    //     // $("#msg").load("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxaaceede0e7695fcf&redirect_uri=http%3A%2F%2Fwx.jzzwlcm.com%2Fnotify&response_type=code&scope=snsapi_base&state=1236&connect_redirect=1#wechat_redirect");
    // // });



    $("#bt").click(function(){
        // $.getScript(
        //     "http://wx.jzzwlcm.com/a.php?total_fee=3",
        //     function(resp){
        //         $("#msg").text(resp);
        //         // $("#msg").text(JSON.stringify(resp));
        //     }
        // );
        // $("<a>").attr("href", "http://wx.jzzwlcm.com/wx_pay.php?total_fee=1").get(0).click();

        var param = encodeURIComponent($.Json.toStr({
            'body'         : "xxx",
            //'attach'       : "",
            'out_trade_no' : "1010000",
            'sub_mch_id'   : "1467121102",
            //'notify_url'   : 111,
            'total_fee'    : 5, // 转为分
            'openid' : "oVQGs1Imf8L2EBcn2N0DyJRKQ8pc",
        }));
        // $("<a>").attr("href", "http://wx.jzzwlcm.com/a.php?total_fee=1").get(0).click();
        $("<a>").attr("href", "http://wx.jzzwlcm.com/wx_pay.php?p=" + param).get(0).click();
    });
});
</script>
<body style="font-size: 80px;">
3
<br>
    <button id="bt" style="width:80px;height:80px;">bt</button><br>
<a href="http://wx.jzzwlcm.com/wx_pay.php?total_fee=1">xxxxxxxxxxx</a> <br><br>
    <div id="msg">empty</div>

<form action="http://wx.jzzwlcm.com/wx_pay.php?total_fee=3" method="get">
    <input type="text" name="total_fee" value="1">
    <input type="submit" name="ok">
</form>
<iframe name="wx_pay_tmp" id="wx_pay_tmp" src=""></iframe>

</body>