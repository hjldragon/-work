/*
 *
 * QQ:15586350 [rockyshi 2014-09-06]
 */
$(function() {

    // 用户登录信息
    $("#id_user_info_1409935859").html(Public.LoginInfo.Str());

    // 全局菜单
	window.menu = new function()
	{
	    var THIS = this;
	    var defval = [
                {value:"index", title:"首页"},
                {value:"uiedit", title:"界面设置"},
                {value:"useredit", title:"用户编辑"},
                {value:"logout", title:"用户退出"}
	    ];

        // 由各个页面定制
        // func返回: [{value:"goods_manage.php", title:"货品管理"},...]
        // 如：
        //    return window.menu.GetDefMenuItem().concat([
        //            {value:"goods_manage.php", title:"货品管理", click_func:function(){} }
        //    ]);
	    THIS.SetMenu = null;

	    THIS.GetDefMenuItem = function()
	    {
	        return defval;
	    }

	    // 延迟加载
	    setTimeout(function(){
	        var menu_item = defval

            if($.isFunction(THIS.SetMenu))
            {
                menu_item = THIS.SetMenu();
            }

            // 通用菜单
            new Util.Menu($("#id_menu_1410077515"),
            			  menu_item,
            			  function(data){
                            if(data.value == "index")
                            {
                                location.href = "switch.php";
                            }
                            else if(data.value == "logout")
                            {
                                window.Store.SetGlobalData("userid", "");
                                location.href = "login.php";
                            }
                            // else if(data.value == "useredit")
                            // {
                            //     $.Box.open('', "id_useredit_box", {username: window.PageStore.GetLastLoginUserName()});
                            // }
                            // else if(data.value == "uiedit")
                            // {
                            //     // 由各个页面决定是否有此功能
                            //     if($.isFunction(window.UiEdit))
                            //     {
                            //         window.UiEdit();
                            //     }
                            //     else
                            //     {
                            //         $.Box.open('', "id_msg_box", {"msg":"当前页面没有定制显示项", "bt":{"hide_relogin":true}});
                            //     }
                            // }
                            else if($.isFunction(data.value))
                            {
                                data.value(data);
                            }
                            else
                            {
            			    	location.href = data.value;
                            }
        			    	return false;
        			      },
        			      {
        			      	align_r: true	// 菜单右对齐
        			      }
            );
	    }, 3000);
	}
});
