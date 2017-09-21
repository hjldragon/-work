/*
 * 页面存储
 * QQ:15586350 [rockyshi 2014-04-02]
 */
(function($) {
window.Store = new function()
{
    var THIS = this;
    // var sCurPage = "X" + $.md5(window.location.pathname) + "_";
    var sCurPage = window.location.pathname;

    THIS.GetPageData = function(key, defval)
    {
        key = "page#" + sCurPage + "#" + key;
        var val = window.localStorage.getItem(key);
        return (!Util.IsDefine(val) || null === val || "" === val) ? defval : val;
    }

    THIS.SetPageData = function(key, value)
    {
        key = "page#" + sCurPage + "#" + key;
        window.localStorage.setItem(key, value);
    }

    THIS.GetGlobalData = function(key, defval)
    {
        key = "global#" + key;
        var val = window.localStorage.getItem(key);
        return (!Util.IsDefine(val) || val === "") ? defval : val;
    }

    THIS.SetGlobalData = function(key, value)
    {
        key = "global#" + key;
        window.localStorage.setItem(key, value);
    }
}

// 将废弃（请使用上面的window.Store）
window.PageStore = new function()
{
    var THIS = this;
    var sCurPage = window.location.pathname;

    var oStoreData = {
        global: {},     // 存放全局数据
        page  : {}      // 存放各页面数据
    };

    // 加载数据
    function Load()
    {
    }

    // 保存数据
    function Save()
    {
    }

    function Init()
    {
        $(window).unload(function(){
            //window.name = $.Json.toStr(oStoreData);
            window.localStorage.data = $.Json.toStr(oStoreData);
        });

        try
        {
            //oStoreData = $.Json.toObj(window.name);
            var data = $.Json.toObj(window.localStorage.data);
            if(data && data.global && data.page)
            {
                oStoreData = data;
            }
        }
        catch(e)
        {
            Util.Alert(e.stack);
        }
    }

    THIS.GetData = function()
    {
        return oStoreData;
    }

    THIS.GetCurPageData = function()
    {
        if(!oStoreData.page[sCurPage])
        {
            oStoreData.page[sCurPage] = {};
        }
        return oStoreData.page[sCurPage];
    }

    // 取登录信息
    THIS.GetLoginInfo = function()
    {
        if(!oStoreData.global.login)
        {
            oStoreData.global.login = {};
        }
        return oStoreData.global.login;
    }
    // 设置登录信息
    THIS.SetLoginInfo = function(info)
    {
        if(!oStoreData.global.login)
        {
            oStoreData.global.login = {};
        }
        Util.IsDefine(info.userid)      && (oStoreData.global.login.userid = info.userid);
        Util.IsDefine(info.username)    && (oStoreData.global.login.username = info.username);
        Util.IsDefine(info.key)         && (oStoreData.global.login.key = info.key);
        Util.IsDefine(info.login_ip)    && (oStoreData.global.login.login_ip = info.login_ip);
        Util.IsDefine(info.login_time)  && (oStoreData.global.login.login_time = info.login_time);
        Util.IsDefine(info.rand_passwd) && (oStoreData.global.login.rand_passwd = info.rand_passwd);
    }
    // 取登录用户id
    THIS.GetLoginUserId = function()
    {
        if(!THIS.CheckLogin())
        {
            return "";
        }
        var info = THIS.GetLoginInfo();
        return info.userid || "";
    }
    // 取登录用户名
    THIS.GetLoginUserName = function()
    {
        if(!THIS.CheckLogin())
        {
            return "";
        }
        var info = THIS.GetLoginInfo();
        return info.username || "";
    }
    // 最近登录的用户名
    THIS.GetLastLoginUserName = function()
    {
        var info = THIS.GetLoginInfo();
        return info.username || "";
    }
    // 取登录后的标识key
    THIS.GetLoginKey = function()
    {
        if(!THIS.CheckLogin())
        {
            return "";
        }
        var info = THIS.GetLoginInfo();
        return info.key || "";
    }
    // 取登录后前、后台商定的数据传输加密密码
    THIS.GetLoginRandPasswd = function()
    {
        if(!THIS.CheckLogin())
        {
            return "";
        }
        var info = THIS.GetLoginInfo();
        return info.rand_passwd || "";
    }
    THIS.GetLoginInfoString = function()
    {
        if(THIS.CheckLogin())
        {
            var info = THIS.GetLoginInfo();
            var str  = "用户ID　：" + info.userid + "\n"
                     + "登录IP　：" + info.login_ip + "\n"
                     + "登录时间：" + Util.TimeTo(info.login_time, "yyyy-MM-dd hh:mm  ")
                     + "";
            return "<font color=blue title='" + str +"'>" + info.username + "</font>，迎欢您！";
        }
        else
        {
            return "<a href='login.php'><font color=red>未登录</font></a>";
        }
    }
    // 已登录，返回true
    THIS.CheckLogin = function()
    {
        var info = THIS.GetLoginInfo();
        return info.userid  && info.username && info.key;
    }
    // 取rsakey
    THIS.RsaKey = function(rsakey)
    {
        if(!oStoreData.global.rsa)
        {
            oStoreData.global.rsa = {};
        }
        if(rsakey)
        {
            oStoreData.global.rsa.key = rsakey;
            return;
        }
        return oStoreData.global.rsa.key || null;
    }

    Init();
    $("#msg").val("init OK");
}
})(jQuery);