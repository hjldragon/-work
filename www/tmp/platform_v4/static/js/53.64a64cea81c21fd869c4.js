webpackJsonp([53,141],{"/fBf":function(t,e){},GAzx:function(t,e){},dQ6Y:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s={props:{shopinfo:{type:Object}},data:function(){return{}}},i={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"order-shopinfo"},[a("div",{staticClass:"title"},[t._v("商品信息")]),t._v(" "),0!==t.shopinfo.list.length?a("div",{staticClass:"table-content"},[a("table",{staticClass:"table"},[t._m(0),t._v(" "),t._l(t.shopinfo.list,function(e,s){return a("tr",{key:s},[a("td",{staticClass:"noborder food-name"},[t._v("\n                "+t._s(e.goods_name)+"\n              ")]),t._v(" "),a("td",{staticClass:"noborder"},[t._v("¥ "+t._s(e.goods_price))]),t._v(" "),a("td",{staticClass:"noborder"},[t._v(t._s(e.goods_num))]),t._v(" "),a("td",{staticClass:"noborder"},[t._v(t._s(e.aisle_name))]),t._v(" "),a("td",[t._v("¥ "+t._s(e.goods_price_sum))])])}),t._v(" "),a("tr",[a("td",{staticClass:"noborder food-name",attrs:{colspan:"4"}},[t._v("\n                  商品总数: "+t._s(t.shopinfo.listNum)+"\n              ")]),t._v(" "),a("td",[t._v("\n                  总计: ¥ "+t._s(t.shopinfo.listTotalPrice)+"\n              ")])])],2)]):t._e()])},staticRenderFns:[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("tr",[a("th",{staticStyle:{width:"28%"}},[t._v("名称")]),t._v(" "),a("th",{staticStyle:{width:"18%"}},[t._v("单价")]),t._v(" "),a("th",{staticStyle:{width:"18%"}},[t._v("数量")]),t._v(" "),a("th",{staticStyle:{width:"18%"}},[t._v("出货货道")]),t._v(" "),a("th",{staticStyle:{width:"18%"}},[t._v("价格")])])}]};var r=a("C7Lr")(s,i,!1,function(t){a("GAzx")},"data-v-28580536",null);e.default=r.exports},fyDF:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=a("dQ6Y"),i=a("6ROu"),r=a.n(i),o=a("P9l9"),l=a("6nXL"),n={components:{OrderGoodsinfo:s.default},data:function(){return{orderid:"",vendor_id:"",orderStatus:null,pay_status:2,VendorPayWay:l.T,VendorOrderStatus:l.S,baseinfo:{orderTime:{label:"下单时间",value:""},orderStatus:{label:"订单状态",value:"未支付"}},nopayinfo:{pay_time:{label:"支付时间",value:""},pay_status:{label:"支付状态",value:"未支付"},pay_price_all:{label:"订单金额",value:""},border:!0,pay_price_able:{label:"应收",value:""}},payinfo:{pay_time:{label:"支付时间",value:""},pay_status:{label:"支付状态",value:"已支付"},pay:{label:"支付方式",value:""},pay_price_all:{label:"订单金额",value:""},border:!0,pay_price_able:{label:"应收",value:""},pay_price_really:{label:"实收",value:""}},shopinfo:{list:[{goods_id:"222",goods_name:"点餐机",goods_price:222,goods_num:1,goods_price_sum:33}],listNum:0,listTotalPrice:0}}},created:function(){this.orderid=this.$route.query.orderid||"",this.orderid&&this.getOrderInfo()},methods:{getOrderInfo:function(){var t=this,e={get_order_info:1,vendor_order_id:this.orderid};Object(o._19)(e).then(function(e){if(console.log("订单详情 res=>",e),0===e.ret){var a=e.data.info,s=a.goods_list||[];t.orderStatus=a.order_status,t.vendor_id=a.vendor_id,t.baseinfo.orderStatus.value=l.S.toString(t.orderStatus),t.baseinfo.orderTime.value=r()(1e3*a.order_time).format("YYYY-MM-DD HH:mm:ss"),t.orderStatus===l.S.UNPAID?(t.pay_status=1,t.nopayinfo.pay_time.value="--",t.nopayinfo.pay_price_all.value="￥ "+a.order_fee,t.nopayinfo.pay_price_able.value="￥ "+a.order_fee):(t.pay_status=2,t.payinfo.pay_time.value=r()(1e3*a.pay_time).format("YYYY-MM-DD HH:mm:ss"),t.payinfo.pay.value=l.T.toString(a.pay_way),t.payinfo.pay_price_all.value="￥ "+a.order_fee,t.payinfo.pay_price_able.value="￥ "+a.order_fee,t.payinfo.pay_price_really.value="￥ "+a.paid_price);var i=0;s.forEach(function(t){i+=t.goods_num,t.goods_price_sum=t.goods_num*t.goods_price}),t.shopinfo.list=s,t.shopinfo.listNum=i,t.shopinfo.listTotalPrice=a.order_fee}})}}},_={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"vendor-order-goods-detail"},[a("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:">"}},[a("el-breadcrumb-item",[t._v("设备管理")]),t._v(" "),a("el-breadcrumb-item",{attrs:{to:{path:"/official/vendor/order"}}},[t._v("订单管理")]),t._v(" "),a("el-breadcrumb-item",[t._v("订单详情")])],1),t._v(" "),a("div",{staticClass:"content-box"},[a("h3",{staticClass:"change-titlerr"},[t._v("订单详情")]),t._v(" "),a("div",{staticClass:"content-detail"},[a("div",{staticClass:"item clearfix"},[a("div",{staticClass:"fl order-id"},[t._m(0),t._v(" "),a("div",{staticClass:"item-right"},[a("span",[t._v(t._s(t.orderid))])])]),t._v(" "),a("div",{staticClass:"fr vendor-id"},[t._m(1),t._v(" "),a("div",{staticClass:"item-right"},[a("span",[t._v(t._s(t.vendor_id))])])])]),t._v(" "),a("div",{staticClass:"order-base"},t._l(t.baseinfo,function(e,s){return a("div",{key:s,staticClass:"item"},[a("div",{staticClass:"item-left"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right"},[a("span",[t._v(t._s(e.value))])])])})),t._v(" "),a("div",{staticClass:"pay-info"},[a("div",{staticClass:"info-title"},[t._v("支付信息")]),t._v(" "),a("div",{staticClass:"info-content"},[t._l(t.nopayinfo,function(e,s){return 1===t.pay_status?a("div",{key:s,staticClass:"item"},[a("div",{staticClass:"item-left"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right"},[a("span",[t._v(t._s(e.value))])]),t._v(" "),"border"===s?a("div",{staticClass:"border"}):t._e()]):t._e()}),t._v(" "),t._l(t.payinfo,function(e,s){return 2===t.pay_status?a("div",{key:s,staticClass:"item"},[a("div",{staticClass:"item-left"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right"},[a("span",[t._v(t._s(e.value))])]),t._v(" "),"border"===s?a("div",{staticClass:"border"}):t._e()]):t._e()})],2)]),t._v(" "),a("order-goodsinfo",{attrs:{shopinfo:t.shopinfo}})],1)])],1)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item-left"},[e("span",[this._v("订单号")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item-left"},[e("span",[this._v("设备编号")])])}]};var d=a("C7Lr")(n,_,!1,function(t){a("/fBf")},"data-v-00f208fc",null);e.default=d.exports}});