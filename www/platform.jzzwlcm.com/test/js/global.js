/*
 *
 * QQ:15586350 [rockyshi 2014-09-06]
 */
$(function() {
    // 用户登录信息（注，在登录成功后也会调用）
    var timer = setInterval(function(){
        if(null == Public || !Public.LoginInfo)
        {
            return;
        }
        $("#id_user_info_1409935859").html(Public.LoginInfo.Str())
                                     .find(".tips")
                                     .TitleToTips();
        clearInterval(timer);
    }, 1000);
});
