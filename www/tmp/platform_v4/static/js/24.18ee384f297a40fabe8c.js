webpackJsonp([24,116,125],{"3ju+":function(e,t){},Juqs:function(e,t){},"R+ef":function(e,t){},cE0n:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a={props:{shopinfo:{type:Object}},data:function(){return{}}},i={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"order-shopinfo"},[s("div",{staticClass:"title"},[e._v("商品信息")]),e._v(" "),0!==e.shopinfo.list.length?s("div",{staticClass:"table-content"},[s("table",{staticClass:"table"},[e._m(0),e._v(" "),e._l(e.shopinfo.list,function(t,a){return s("tr",{key:a},[s("td",{staticClass:"noborder food-name"},[e._v("\n                "+e._s(t.goods_name)+"\n              ")]),e._v(" "),s("td",{staticClass:"noborder"},[e._v("¥ "+e._s(t.goods_price))]),e._v(" "),s("td",{staticClass:"noborder"},[e._v(e._s(t.goods_num))]),e._v(" "),s("td",[e._v("¥ "+e._s(t.goods_price_sum))])])}),e._v(" "),s("tr",[s("td",{staticClass:"noborder food-name",attrs:{colspan:"3"}},[e._v("\n                  商品总数: "+e._s(e.shopinfo.listNum)+"\n              ")]),e._v(" "),s("td",[e._v("\n                  总计: ¥ "+e._s(e.shopinfo.listTotalPrice)+"\n              ")])]),e._v(" "),s("tr",{staticClass:"last"},[s("td",{staticClass:"clearfix",attrs:{colspan:"4"}},[s("div",{staticClass:"td-title fl"},[e._v("备注:")]),e._v(" "),s("div",{staticClass:"remark-content fl"},[e._v(e._s(e.shopinfo.remark))])])])],2)]):e._e()])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("tr",[t("th",{staticStyle:{width:"37%"}},[this._v("名称")]),this._v(" "),t("th",{staticStyle:{width:"21%"}},[this._v("单价")]),this._v(" "),t("th",{staticStyle:{width:"21%"}},[this._v("数量")]),this._v(" "),t("th",{staticStyle:{width:"21%"}},[this._v("价格")])])}]};var r=s("C7Lr")(a,i,!1,function(e){s("3ju+")},"data-v-55129df8",null);t.default=r.exports},nKWa:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=s("P9l9"),i={props:{evaluateList:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{handlerReplyClcik:function(e){var t=this,s={evaluation_to:1,content:e.to_content,to_id:e.id};Object(a.L)(s).then(function(e){0===e.ret&&(t.$emit("on-success"),t.$slnotify({message:"回复成功"}))})}}},r={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"order-goods-evalute"},[s("div",{staticClass:"info-title"},[e._v("客户评价")]),e._v(" "),e._l(e.evaluateList,function(t){return s("div",{key:t.id,staticClass:"info-content"},[s("div",{staticClass:"name"},[e._v(e._s(t.goods_name))]),e._v(" "),s("div",{staticClass:"content"},[e._v(e._s(t.content))]),e._v(" "),t.to_id?s("div",{staticClass:"to-content"},[e._v("\n        回复："+e._s(t.to_content)+"\n      ")]):s("div",[s("el-input",{model:{value:t.to_content,callback:function(s){e.$set(t,"to_content",s)},expression:"item.to_content"}}),e._v(" "),s("span",{staticClass:"reply-btn",on:{click:function(s){e.handlerReplyClcik(t)}}},[e._v("回复")])],1)])})],2)},staticRenderFns:[]};var o=s("C7Lr")(i,r,!1,function(e){s("R+ef")},"data-v-6e2a515a",null);t.default=o.exports},t0K2:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=s("cE0n"),i=s("nKWa"),r=s("6ROu"),o=s.n(r),n=s("yU9W"),l=s("P9l9"),_=s("a2vD"),d=s("6nXL"),v={components:{OrderGoodsinfo:a.default,OrderEvaluate:i.default,DeliverDialog:n.default},data:function(){return{orderid:"",orderFrom:"",orderStatus:null,pay_status:2,invoiceStatus:1,GoodsOrderStatus:d.r,no_deliver:0,baseinfo:{orderTime:{label:"下单时间",value:""},orderStatus:{label:"订单状态",value:"未支付"}},nopayinfo:{pay_time:{label:"支付时间",value:""},pay_status:{label:"支付状态",value:"未支付"},pay_price_all:{label:"订单金额",value:""},pay_price_waiver:{label:"减免",value:"￥ 0.00"},border:!0,pay_price_able:{label:"应收",value:""}},payinfo:{pay_time:{label:"支付时间",value:""},pay_status:{label:"支付状态",value:"已支付"},pay:{label:"支付方式",value:""},pay_price_all:{label:"订单金额",value:""},pay_price_waiver:{label:"减免",value:"￥ 0.00"},border:!0,pay_price_able:{label:"应收",value:""},pay_price_really:{label:"实收",value:""}},shopinfo:{list:[{goods_id:"222",goods_name:"点餐机",goods_price:222,goods_num:1,goods_price_sum:33}],listNum:0,listTotalPrice:0,remark:"暂无"},express_info:{ep_status:{label:"配送状态",value:"未发货"},ep_company:{label:"快递公司",value:"--"},ep_number:{label:"快递单号",value:"--"},ep_freight:{label:"运费",value:""},ep_accept_addressstr:{label:"收件地址",value:""},ep_accept_people:{label:"收件人",value:""},ep_accept_phone:{label:"收件人电话",value:""},ep_send_addressstr:{label:"发件地址",value:""},ep_send_people:{label:"发件人",value:""},ep_send_phone:{label:"发件人电话",value:""}},invoice_info:{invoice_status:{label:"发票状态",value:"1231"},invoice_title:{label:"发票抬头",value:"1231"},invoice_content:{label:"发票内容",value:"明细"},invoice_taxnum:{label:"税号",value:"1234",hide_taxnum:!1}},visiableDeliverDialog:!1,evaluateList:[]}},created:function(){this.orderid=this.$route.query.orderid||"",this.getOrderInfo()},methods:{getOrderInfo:function(){var e=this,t={get_order_info:1,goods_order_id:this.orderid};Object(l.N)(t).then(function(t){if(0===t.ret){var s=t.data.info,a=s.deliver_address||{},i=s.order_address||{},r=s.invoice||{},n=s.goods_list||[],l=s.pay_way;e.no_deliver=s.no_deliver;var _=r.title_type||1;e.orderStatus=s.order_status,e.invoiceStatus=s.invoice_status,e.orderFrom=d.q.toString(s.goods_order_from),e.baseinfo.orderStatus.value=d.r.toString(e.orderStatus),e.baseinfo.orderTime.value=o()(1e3*s.order_time).format("YYYY-MM-DD HH:mm:ss"),e.orderStatus===d.r.WAITPAY||e.orderStatus===d.r.CLOSE?(e.pay_status=1,e.nopayinfo.pay_time.value="--",e.nopayinfo.pay_price_all.value="￥ "+s.order_fee,e.nopayinfo.pay_price_able.value="￥ "+s.order_fee):(e.pay_status=2,e.payinfo.pay_time.value=o()(1e3*s.pay_time).format("YYYY-MM-DD HH:mm:ss"),e.payinfo.pay.value=d.s.toString(s.pay_way),e.payinfo.pay_price_all.value="￥ "+s.order_fee,e.payinfo.pay_price_able.value="￥ "+s.order_fee,e.payinfo.pay_price_really.value="￥ "+s.order_fee),e.orderStatus===d.r.WAITPAY||e.orderStatus===d.r.WAITDELIVER||e.orderStatus===d.r.CLOSE?(e.express_info.ep_status.value="未发货",e.express_info.ep_company.value="--",e.express_info.ep_number.value="--",e.getDefaultAddress()):1===e.no_deliver?(e.express_info.ep_status.value="已发货",e.express_info.ep_company.value="--",e.express_info.ep_number.value="--",e.getDefaultAddress()):(e.express_info.ep_status.value="已发货",e.express_info.ep_company.value=s.express_company_name,e.express_info.ep_number.value=s.express_num,e.express_info.ep_send_addressstr.value=""+i.province+i.city+i.area+i.address,e.express_info.ep_send_people.value=i.name,e.express_info.ep_send_phone.value=i.phone,e.orderStatus!==d.r.WAITRECEIVED&&(e.express_info.ep_status.value="已签收")),e.express_info.ep_freight.value=s.freight_price||"免运费",e.express_info.ep_accept_addressstr.value=""+a.province+a.city+a.area+a.address,e.express_info.ep_accept_people.value=a.name,e.express_info.ep_accept_phone.value=a.phone,e.invoice_info.invoice_status.value=d.p.toString(e.invoiceStatus),e.invoice_info.invoice_title.value=r.invoice_title||"--",e.invoice_info.invoice_taxnum.value=r.duty_paragraph||"--",e.invoiceStatus!==d.p.NO&&e.invoiceStatus!==d.p.NEED||(e.invoice_info.invoice_content.value="--"),1===_&&(e.invoice_info.invoice_taxnum.hide_taxnum=!0);var v=0,c=0;n.forEach(function(e){v+=e.goods_num,l===d.s.WEIXIN?c+=e.goods_price_sum:l===d.s.BALANCE&&(c+=e.rebates_price_sum,e.goods_price=e.rebates_price,e.goods_price_sum=e.rebates_price_sum)}),e.shopinfo.list=n,e.shopinfo.listNum=v,e.shopinfo.listTotalPrice=c,e.evaluateList=s.evaluate_list||[]}})},getDefaultAddress:function(){var e=this,t={address_list:1,address_type:1,uid:_.a.getPlatformid()};Object(l.a)(t).then(function(t){if(0===t.ret){var s=(t.data.info||[]).filter(function(e){return 1===e.is_default})[0]||{};e.express_info.ep_send_addressstr.value=""+s.province+s.city+s.area+s.address,e.express_info.ep_send_people.value=s.name,e.express_info.ep_send_phone.value=s.phone}else e.$slnotify({message:d.X.toString(t.ret)})})},goOrderManage:function(){this.$router.push("/official/shopmall/order")},goOrderExpress:function(){1!==this.no_deliver&&this.$router.push({path:"/officialmanage/orderexpress",query:{orderid:this.orderid}})},goOrderManageStatus:function(){var e="0";switch(this.orderStatus){case 1:e="1";break;case 2:e="2";break;case 3:e="3";break;case 5:e="4";break;case 9:e="5"}this.$router.push({path:"/official/shopmall/order",query:{status:e}})},deliverDialogHandler:function(){this.deliverOrderid="",this.visiableDeliverDialog=!1}}},c={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"goods-order-detail"},[s("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:">"}},[s("el-breadcrumb-item",{nativeOn:{click:function(t){return e.goOrderManage(t)}}},[e._v("订单管理")]),e._v(" "),s("el-breadcrumb-item",{nativeOn:{click:function(t){return e.goOrderManageStatus(t)}}},[e._v(e._s(e.GoodsOrderStatus.toString(e.orderStatus)))]),e._v(" "),s("el-breadcrumb-item",[e._v("订单详情")])],1),e._v(" "),s("div",{staticClass:"content-box"},[s("h3",{staticClass:"change-titlerr"},[e._v("订单详情")]),e._v(" "),s("div",{staticClass:"content-detail"},[s("div",{staticClass:"item clearfix"},[s("div",{staticClass:"fl order-id"},[e._m(0),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(e.orderid))])])]),e._v(" "),s("div",{staticClass:"fr"},[e._m(1),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(e.orderFrom))])])])]),e._v(" "),s("div",{staticClass:"order-base"},e._l(e.baseinfo,function(t,a){return s("div",{key:a,staticClass:"item"},[s("div",{staticClass:"item-left"},[s("span",[e._v(e._s(t.label))])]),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(t.value))])])])})),e._v(" "),s("div",{staticClass:"pay-info"},[s("div",{staticClass:"info-title"},[e._v("支付信息")]),e._v(" "),s("div",{staticClass:"info-content"},[e._l(e.nopayinfo,function(t,a){return 1===e.pay_status?s("div",{key:a,staticClass:"item"},[s("div",{staticClass:"item-left"},[s("span",[e._v(e._s(t.label))])]),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(t.value))])]),e._v(" "),"border"===a?s("div",{staticClass:"border"}):e._e()]):e._e()}),e._v(" "),e._l(e.payinfo,function(t,a){return 2===e.pay_status?s("div",{key:a,staticClass:"item"},[s("div",{staticClass:"item-left"},[s("span",[e._v(e._s(t.label))])]),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(t.value))])]),e._v(" "),"border"===a?s("div",{staticClass:"border"}):e._e()]):e._e()})],2)]),e._v(" "),s("order-goodsinfo",{attrs:{shopinfo:e.shopinfo}}),e._v(" "),s("div",{staticClass:"express-info"},[s("div",{staticClass:"info-title"},[e._v("快递信息")]),e._v(" "),s("div",{staticClass:"info-content"},[e._l(e.express_info,function(t,a){return s("div",{key:a,staticClass:"item"},[s("div",{staticClass:"item-left"},[s("span",[e._v(e._s(t.label))])]),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(t.value))])])])}),e._v(" "),s("div",{directives:[{name:"show",rawName:"v-show",value:(e.orderStatus===e.GoodsOrderStatus.SUCCESS||e.orderStatus===e.GoodsOrderStatus.WAITEVALUATE||e.orderStatus===e.GoodsOrderStatus.WAITRECEIVED)&&1!==e.no_deliver,expression:"(orderStatus === GoodsOrderStatus.SUCCESS || orderStatus === GoodsOrderStatus.WAITEVALUATE || orderStatus === GoodsOrderStatus.WAITRECEIVED) && no_deliver !== 1"}],staticClass:"item"},[e._m(2),e._v(" "),s("div",{staticClass:"item-right item-right--blue"},[s("span",{on:{click:e.goOrderExpress}},[e._v("点击查看物流信息")])])])],2)]),e._v(" "),s("div",{staticClass:"invoice-info"},[s("div",{staticClass:"info-title"},[e._v("发票信息")]),e._v(" "),s("div",{staticClass:"info-content"},e._l(e.invoice_info,function(t,a){return t.hide_taxnum?e._e():s("div",{key:a,staticClass:"item"},[s("div",{staticClass:"item-left"},[s("span",[e._v(e._s(t.label))])]),e._v(" "),s("div",{staticClass:"item-right"},[s("span",[e._v(e._s(t.value))])])])}))]),e._v(" "),s("order-evaluate",{directives:[{name:"show",rawName:"v-show",value:e.orderStatus===e.GoodsOrderStatus.SUCCESS,expression:"orderStatus === GoodsOrderStatus.SUCCESS"}],attrs:{"evaluate-list":e.evaluateList},on:{"on-success":e.getOrderInfo}}),e._v(" "),s("div",{staticClass:"btn-group"},[e.orderStatus===e.GoodsOrderStatus.WAITDELIVER?s("div",{staticClass:"sl-btn-bd-b-blue",on:{click:function(t){e.visiableDeliverDialog=!0}}},[e._v("发货")]):e._e()])],1)]),e._v(" "),s("deliver-dialog",{attrs:{"visiable-deliver-dialog":e.visiableDeliverDialog,"deliver-orderid":e.orderid},on:{"on-close":e.deliverDialogHandler,"on-success":e.getOrderInfo}})],1)},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"item-left"},[t("span",[this._v("订单号")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"item-left"},[t("span",[this._v("订单来源")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"item-left"},[t("span",[this._v("物流跟踪")])])}]};var u=s("C7Lr")(v,c,!1,function(e){s("xPhI"),s("Juqs")},"data-v-997e5748",null);t.default=u.exports},xPhI:function(e,t){}});