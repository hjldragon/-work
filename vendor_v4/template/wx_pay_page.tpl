<!DOCTYPE html>
<html lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<meta http-equiv="X-UA-Compatible" content="ie=edge">-->
		<script type="text/javascript" src="http://todolist.rockyshi.cn/js/vue.min.js"></script>
		<title>
		</title>
		<script>
			! function(e, t) {
				function n() {
					t.body ? t.body.style.fontSize = 12 * o + "px" : t.addEventListener("DOMContentLoaded", n)
				}

				function d() {
					var e = i.clientWidth / 10;
					i.style.fontSize = e + "px"
				}
				var i = t.documentElement,
					o = e.devicePixelRatio || 1;
				if(n(), d(), e.addEventListener("resize", d), e.addEventListener("pageshow", function(e) {
						e.persisted && d()
					}), o >= 2) {
					var a = t.createElement("body"),
						s = t.createElement("div");
					s.style.border = ".5px solid transparent", a.appendChild(s), i.appendChild(a), 1 === s.offsetHeight && i.classList.add("hairlines"), i.removeChild(a)
				}
			}(window, document);
		</script>

	</head>

	
	<body >
		<style>
			* {
				padding: 0;
				margin: 0;
			}
			
			body {
				height: 100vh;
				background: #ffffff;
			}
			
			.header {
				background-color: #fff;
				border-bottom: 0.01rem solid #e7e7e7;
				z-index: 100;
				width: 100%;
				padding-left: 0.53rem;
				height: 1.04rem;
				color: #323232;
				line-height: 1.04rem;
				overflow: hidden;
				box-sizing: border-box;
			}
			
			.user,
			.shop {
				float: left;
				width: 33.3%;
				text-align: center;
				vertical-align: middle;
			}
			/*左箭头*/
			
			.shop img {
				width: 0.74rem;
				height: 0.74rem;
				vertical-align: middle;
			}
			
			.user {
				font-size: 0.48rem;
			}
			
			.shop {
				text-align: left;
			}
			/*灰色bar*/
			
			.pay-status {
				width: 100%;
				height: 0.9rem;
				/*padding-right: 0.26rem;*/
				background: rgba(241, 241, 241, 1);
			}
			
			.status-item {
				float: left;
				width: 3.33rem;
				font-size: 0.48rem;
				color: #323232;
				height: 0.9rem;
				line-height: 0.9rem;
				text-align: center;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				box-sizing: border-box;
			}
			
			.text-left {
				width: 3rem;
				text-align: center;
			}
			
			.text-right {
				text-align: center;
			}
			
			.order-box {
				width: 100%;
				height: 6.1rem;
				padding-top: 0.26rem;
				padding-left: 0.26rem;
				padding-bottom: 0.613rem;
				background: #fff;
				text-align: center;
				position: relative;
			}
			
			.order-num {
				font-size: 0.37rem;
				color: #323232;
				margin-bottom: 0.6rem;
				position: absolute;
				left: 0.3rem;
			}
			
			.order-box img {
				width: 3.90rem;
				height: 2.5rem;
				margin-top: 0.36rem;
				/* margin-left: 2.54rem; */
			}
			
			.is-paying {
				margin-top: 0.48rem;
				font-size: 0.4rem;
				color: #323232;
				text-align: center;
			}
			
			.is-paying span {
				display: block;
				margin-bottom: 0.34rem;
			}
			
			.text-strong {
				font-size: 0.53rem;
				font-weight: 600;
			}
			
			.gray-bar {
				height: 0.26rem;
				width: 100%;
				background: #f1f1f1;
			}
			
			.advertising-box {
				width: 100%;
				height: 8.7rem;
				background: #fff;
				padding-top: 0.26rem;
				/* padding: 0.26rem; */
				text-align: center;
				box-sizing: border-box;
			}
			
			.advertising-box img {
				width: 95%;
				height: 4.13rem;
			}
			
			.advertising-text {
				font-size: 0.29rem;
				color: #989898;
				margin-top: 0.53rem;
				text-align: center;
				padding-left: 0.26rem;
			}
			
			.advertising-text span {
				color: #FB6C04;
			}
			
			.shareing {
				margin-top: 0.6rem;
				padding-left: 0.26rem;
			}
			
			.share-item {
				width: 33%;
				height: 1.8rem;
				text-align: center;
				float: left;
			}
			
			.share-item span {
				font-size: 0.4rem;
				width: 2rem;
				height: 0.373rem;
				text-align: right;
				margin-bottom: 0.25rem;
				display: block;
				padding-left: 0.26rem;
			}
			
			.share-item .img {
				margin-left: 0.8rem;
				width: 1.146rem;
				height: 1.146rem;
			}
			
			.share-item .img img {
				width: 1.146rem;
				height: 1.146rem;
			}
		</style>
	<div ref="data"  id="wxPayPage">
		
	
		<!--头部导航-->
		<div class="header">
			<div class="shop">
				<img src="./images/goback.png" />
			</div>
			<div class="user">
				订单状态
			</div>
		</div>
		<!--支付状态-->
		<div class="pay-status">
			<div class="status-item text-left">
				支付状态
			</div>
			<div class="status-item">
				<!--php echo $seat_info->seat_name;?>-->
			</div>
			<div class="status-item text-right">
					<!--?>人-->
			</div>
		</div>
		<!--支付状态-->
		<!--订单编号和图片-->
		<div class="order-box">
			<!--订单编号-->
			<div class="order-num">
				<span>
   		订单编号
   	</span>
				<span>
   		{{orderinfo.order_id}}
   	</span>
				<!--订单编号-->
			</div>
			<!--图片-->
			<img src="./images/ispaying@2x.png" />

			<!--正在支付-->
			<div class="is-paying">
				<span class="text-strong">
   		正在支付 ...
   	</span>
				<span>
   		金额  ￥ {{orderinfo.payable_fee}}
   	</span>
			</div>
			<!--正在支付-->
		</div>
		
		<!--广告图  + 分享-->
		<div class="advertising-box">
			<!--广告图-->
			<img src="./images/surebanner@2x.png" />
			
		</div>
</div>

	
	<script type="text/javascript">
	(function(){
	if(window.PayCall)
	{
	    return;
	}
	
	window.PayCall = new Vue({
	    el: '#wxPayPage',
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
	    	alert(123);
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
	                       window.location.href = this.orderinfo.pay_success_url;
	                    }
	                    // get_brand_wcpay_request:cancel 支付过程中用户取消
	                    // get_brand_wcpay_request:fail 支付失败
	                    else if('get_brand_wcpay_request:cancel' == res.err_msg)
	                    {
	                        this.pay_fail = true;
	                        this.msg = "<font color='red'>支付被取消</font>";
	                          window.location.href = this.orderinfo.pay_cancel_url;
	                    }
	                    else if('get_brand_wcpay_request:fail' == res.err_msg)
	                    {
	                        this.pay_fail = true;
	                         window.location.href = this.orderinfo.pay_cancel_url;
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
	</body>
</html>