<?php
/*
 * [rockyshi 2014-08-20]
 * 登录页
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    title => '登录'
);

?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script type="text/javascript">
$(function() {
    $("#id_index_1495733175").hide();

    var relogin = false;
    if(Util.GetQueryString("logout") == 1)
    {
        relogin = true;
        Util.EncSubmit("login_save.php",
            {
                logout : 1,
            },
            function(resp){
                Public.LoginInfo.Clear();
            }
        );
    }
    Public.ShowLoginBox({relogin: relogin});
});
</script>
<body>

</body>
