$(function(){
window.Toast = new function(){
    var $msg = $("");
    var queue = [];
    var showing = false;
    var tlen = 3000; // 显示时长

    this.Show = function(str){
        queue.push(str)
        DoShow();
    }

    function DoShow()
    {
        if(showing)
        {
            return;
        }
        var str = queue.shift();
        if(!str)
        {
            return;
        }
        // 重复消息显示的一重复数
        var repeat = 0;
        for(var i=0; i<queue.length; i++)
        {
            if(queue[i] == str)
            {
                queue.shift();
                i--;
                repeat++;
                continue;
            }
        }
        if(repeat > 0)
        {
            str = str + " [" + repeat + "]";
        }
        if(queue.length > 5)
        {
            tlen = 1000;
        }
        else if(queue.length > 30)
        {
            tlen = 300;
        }
        else if(queue.length > 100)
        {
            tlen = 100;
        }
        else
        {
            tlen = 3000;
        }
        showing = true;
        $msg.html(str).center().css({top:'0px', 'z-index':Util.GetTimestamp()}).show().fadeOut(tlen, function(){
            showing = false;
            DoShow();
        });
    }

    function Init()
    {
        $msg = $("<div id='id_1470210831'></div>").appendTo($("body")).css({
            position   : "absolute",
            top        : '0px',
            background : '#f1e783',
            color      : '#008D1A',
            padding    : '0.2rem 0.4rem 0.2rem 0.4rem'
        }).hide();
    }

    Init();
}
});
