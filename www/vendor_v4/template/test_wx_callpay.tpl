<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<script type="text/javascript" src="http://todolist.rockyshi.cn/js/vue.min.js"></script>
<title>支付页演示</title>
</head>
<body>
<style type="text/css">
#id_1532688965 {
    width: 100%;
    top: initial!important;
    left: 0px;
    z-index: 1030;
    bottom: 0;
    border-radius: initial!important;
    height: 100%;
}
</style>
<div id="id_1532688965">
    <div class="data" ref="data">
        <div class="line ordering_success">
            	订单已提交！
        </div>

        <div class="line">
            <span class="title">订单号</span>
            {{orderinfo.order_id}}
        </div>

        <div class="line">
            <span class="title">金额(元)</span>
            {{orderinfo.payable_fee}}
        </div>

        <div>{{msg}}</div>
    </div>
</div>


<script type="text/javascript">
(function(){
if(window.PayCall)
{
    return;
}

window.PayCall = new Vue({
    el: '#id_1532688965',
    data: {
        box : null,
        orderinfo : [<?=json_encode($param)?>][0],
        pay_fail : false,
        msg : "",
    },
    created(){
        console.log(JSON.stringify(this.orderinfo));
    },
    mounted(){
        if(this.orderinfo.pay_param)
        {
            this.WxPay();
        }
    },
    computed: {
    },
    watch:{
    },
    methods: {
        Open(){
        },
        Ok(){
        },
        Close(){
        },
        WxPay()
        {
            if(typeof WeixinJSBridge == "undefined")
            {
                if(document.addEventListener)
                {
                    document.addEventListener('WeixinJSBridgeReady', this.DoWxPay, false);
                }
                else if(document.attachEvent)
                {
                    document.attachEvent('WeixinJSBridgeReady', this.DoWxPay);
                    document.attachEvent('onWeixinJSBridgeReady', this.DoWxPay);
                }
            }
            else
            {
                this.DoWxPay();
            }
        },
        DoWxPay(){
            if(typeof WeixinJSBridge == "undefined")
            {
                this.msg = "<font color='red'>请在微信中打开</font>";
                return;
            }
            if(!this.orderinfo.pay_param)
            {
                this.msg = "<font color='red'>支付数据出错</font>";
                return;
            }
            this.msg = "";
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest',
                this.orderinfo.pay_param,
                res => {
                    if('get_brand_wcpay_request:ok' == res.err_msg)
                    {
                        this.pay_fail = false;
                        this.msg = "<font color='green'>支付成功</font>";
                    }
                    // get_brand_wcpay_request:cancel 支付过程中用户取消
                    // get_brand_wcpay_request:fail 支付失败
                    else if('get_brand_wcpay_request:cancel' == res.err_msg)
                    {
                        this.pay_fail = true;
                        this.msg = "<font color='red'>支付被取消</font>";
                    }
                    else if('get_brand_wcpay_request:fail' == res.err_msg)
                    {
                        this.pay_fail = true;
                        this.msg = "<font color='red'>支付出错</font>";
                    }
                    else
                    {
                        this.msg = "<font color='red'>" + res.err_msg + "</font>";
                    }
                }
            );
        },
    },
});


})();
</script>
