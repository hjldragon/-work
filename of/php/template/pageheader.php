<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
微信号：shizw2008
-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/main.css?1494609357" type="text/css">
<title><?=$html->title?></title>
<script type="text/javascript" src="./js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="./js/jquery.cookie.js"></script>
<script type="text/javascript" src="./js/jquery.md5.js"></script>
<script type="text/javascript" src="./js/cfg.js"></script>
<script type="text/javascript" src="./js/jquery.query.js"></script>
<script type="text/javascript" src="./js/util.js?1500351741"></script>
<script type="text/javascript" src="./js/public.js?1500351748"></script>
<script type="text/javascript" src="./js/encrypt.js?1412787720"></script>
<script type="text/javascript" src="./js/PageStore.js?1418317466"></script>
<script type="text/javascript" src="./js/toast.js?1494520361"></script>
<script type="text/javascript" src="./js/global.js?1418273744"></script>
<script type="text/javascript">
</script>
<style type="text/css">
#id_menu_1410077515{
    position:absolute;
    right:0px;
    padding:1px 5px;
    margin:2px;
    /*color: #FFFF00;*/
    background:#cccccc;
    float:right;
    font-weight:bold;
    font-size:12px;
    cursor: pointer;
}
#id_menu_1410077515:hover{
    color: white;
}
#id_server_time_1453885115{
    position:absolute;
    right:2px;
    margin-top:-18px;
    margin-right: 2px;
    font-size:10px;
}
</style>
<script type="text/javascript">
// $(function() {
//         $.getJSON("get_time.php", {}, function(resp){
//             if(resp.ret < 0)
//             {
//                 return;
//             }
//             var sec = parseInt(resp.data.msec) / 1000
//             $timer = $("#id_server_time_1453885115")
//             setInterval(function(){
//                 sec++
//                 var d = Util.TimeTo(sec, "yyyy-MM-dd");
//                 var t = Util.TimeTo(sec, "hh:mm:ss");
//                 $timer.html("<span title='" + d + "'>" + t + "</span>")
//             }, 1000)
//         });
// });
</script>
</head>
<body>
<div class="hide">
<div align="right" style="position:absolute; top:2px; right:2px;" id="id_user_info_1409935859"></div>
<div align="right" style="position:absolute; top:0px; left:0px; color:#E6E6E6; font-size:6px;">
    <i>[管理系统,2015-12,QQ:15586350]</i>
</div>
<div align="left">
<a href="./">
    <img src="./logo.png" width="40" style="float:left; padding:3px; margin:3px 10px 2px 5px; background:#DBDBDB;" />
</a>
<h1 style="margin:15px 5px;"><?=$html->title?></h1>
<div id="id_server_time_1453885115"></div>
<hr id="id_separate_1410079661" style="padding:0; margin:0; size:1px; height:1px;"/>
<div id="id_menu_1410077515">菜单</div>
</div>
</div>
