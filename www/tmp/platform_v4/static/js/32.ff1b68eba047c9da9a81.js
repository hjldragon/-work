webpackJsonp([32,142,143],{"3bVT":function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=s("lRWX"),r={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"goodsorder-express-info"},[s("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:">"}},[s("el-breadcrumb-item",{nativeOn:{click:function(t){return e.goOrderManage(t)}}},[e._v("订单管理")]),e._v(" "),s("el-breadcrumb-item",{nativeOn:{click:function(t){return e.goOrderManageStatus(t)}}},[e._v(e._s(e.GoodsOrderStatus.toString(e.orderStatus)))]),e._v(" "),s("el-breadcrumb-item",{nativeOn:{click:function(t){return e.goOrderDetail(t)}}},[e._v("订单详情")]),e._v(" "),s("el-breadcrumb-item",[e._v("物流信息")])],1),e._v(" "),s("div",{staticClass:"content-box"},[s("h3",{staticClass:"change-titlerr"},[e._v("物流信息")]),e._v(" "),s("div",{staticClass:"content-detail"},[s("div",{staticClass:"express-table"},[s("div",{staticClass:"accept-title"},[e._v("物流跟踪")]),e._v(" "),s("div",{ref:"acceptContent",staticClass:"accept-content"},[e._l(e.tracesList,function(t,a){return s("express-info-item",{key:a,attrs:{accept:t,first:0===a,active:a===e.tracesList.length-1}})}),e._v(" "),0===e.tracesList.length?s("span",{staticClass:"notrace"},[e._v("暂无物流信息")]):e._e()],2),e._v(" "),s("div",{staticClass:"order-info flex-box"},[s("div",{staticClass:"goods-info"},e._l(e.goodsList,function(e,t){return s("express-goods-item",{key:t,staticClass:"goods-item",attrs:{goods:e}})}),1),e._v(" "),s("div",{staticClass:"express-info"},[s("div",{staticClass:"express-info-item"},[s("span",[e._v("运单单号："+e._s(e.express_num))]),e._v(" "),s("span",[e._v("物流公司: "+e._s(e.express_company_name))]),e._v(" "),s("span",[e._v("客服电话："+e._s(e.express_company_phone))])]),e._v(" "),s("div",{staticClass:"express-info-item"},[s("span",[e._v("收货地址："+e._s(e.order_address.province)+e._s(e.order_address.city)+e._s(e.order_address.area)+e._s(e.order_address.address)+"  "+e._s(e.order_address.name)+"  "+e._s(e.order_address.phone))])])])])])])])],1)},staticRenderFns:[]};var i=function(e){s("HcLJ")},o=s("C7Lr")(a.a,r,!1,i,"data-v-303ea37c",null);t.default=o.exports},"A/fg":function(e,t){},GO5a:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a={props:{goods:{type:Object,default:function(){return{goods_img_str:"",goods_name:"11212112点餐具 点餐机餐机餐机餐机",goods_price:"231231",goods_num:"123"}}}}},r={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"express-goods-item flex-box cross-center"},[s("div",{staticClass:"goods__img"},[s("img",{attrs:{src:e.goods.goods_img_str}})]),e._v(" "),s("div",{staticClass:"goods__name"},[e._v(e._s(e.goods.goods_name))]),e._v(" "),s("div",{staticClass:"goods__price"},[e._v("¥ "+e._s(e._f("numFix")(e.goods.goods_price)))]),e._v(" "),s("div",{staticClass:"goods_num"},[e._v(e._s(e.goods.goods_num))])])},staticRenderFns:[]};var i=s("C7Lr")(a,r,!1,function(e){s("KZNr")},"data-v-27bd0440",null);t.default=i.exports},HcLJ:function(e,t){},KZNr:function(e,t){},ckWV:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a={props:{accept:{type:Object,default:function(){return{AcceptStation:"【平湖营业点】的收件员【王武卫】已收件",AcceptTime:"2018-07-20 00:30:25",acceptDay:"2018-07-20",acceptWeek:"周一",acceptTimeStr:"00:30:25",isShowDay:!0}}},active:{type:Boolean,default:!1},first:{type:Boolean,default:!1}},mounted:function(){var e=this.$refs.acceptInfo,t=this.$refs.lineVertical,s=this.$refs.info.clientHeight;s>30&&(e.style.height=s+10+"px",t.style.height=s+"px")}},r={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{ref:"acceptInfo",staticClass:"accept-item",class:{"accept-itme--active":e.active}},[e.first?s("div",{staticClass:"item__dot--first"}):e._e(),e._v(" "),s("div",{staticClass:"item__dot"}),e._v(" "),s("div",{ref:"lineVertical",staticClass:"item__line--vertical"}),e._v(" "),s("span",{staticClass:"item__day"},[s("span",{directives:[{name:"show",rawName:"v-show",value:e.accept.isShowDay,expression:"accept.isShowDay"}]},[e._v(e._s(e.accept.acceptDay))])]),e._v(" "),s("span",{staticClass:"item__week"},[s("span",{directives:[{name:"show",rawName:"v-show",value:e.accept.isShowDay,expression:"accept.isShowDay"}]},[e._v(e._s(e.accept.acceptWeek))])]),e._v(" "),s("span",{staticClass:"item__time"},[e._v(e._s(e.accept.acceptTimeStr))]),e._v(" "),s("span",{ref:"info",staticClass:"item__info"},[e._v(e._s(e.accept.AcceptStation))])])},staticRenderFns:[]};var i=s("C7Lr")(a,r,!1,function(e){s("A/fg")},"data-v-2856c0b0",null);t.default=i.exports},lRWX:function(e,t,s){"use strict";(function(e){var a=s("ckWV"),r=s("GO5a"),i=s("6ROu"),o=s.n(i),c=s("P9l9"),n=s("6nXL"),d=s("EuEE"),_={0:"周日",1:"周一",2:"周二",3:"周三",4:"周四",5:"周五",6:"周六"};t.a={components:{ExpressInfoItem:a.default,ExpressGoodsItem:r.default},data:function(){return{orderStatus:"",orderid:"",GoodsOrderStatus:n.r,goodsList:[],tracesList:[],express_num:"",express_company_name:"",express_company_phone:"",order_address:{}}},created:function(){this.orderid=this.$route.query.orderid||"",this.getOrderInfo(),this.getExpress()},mounted:function(){var t=this.$refs.acceptContent;this.$nextTick(function(){d.a.AdjustMinHeight(t)}),e(window).resize(function(){d.a.AdjustMinHeight(t)})},methods:{getOrderInfo:function(){var e=this,t={get_order_info:1,goods_order_id:this.orderid};Object(c.N)(t).then(function(t){if(0===t.ret){var s=t.data.info;e.goodsList=s.goods_list||[],e.order_address=s.order_address||{},e.express_num=s.express_num,e.express_company_name=s.express_company_name,e.express_company_phone=s.express_company_phone,e.orderStatus=s.order_status,e.goodsList.forEach(function(e){e.goods_img_fir=(e.goods_img||[])[0]||"",e.goods_img_str="./php/img_get.php?img=1&width=200&height=200&imgname="+e.goods_img_fir})}})},getExpress:function(){var e=this,t={get_express:1,goods_order_id:this.orderid};Object(c.N)(t).then(function(t){if(0===t.ret){var s=t.data.info;e.tracesList=s.Traces||[],e.tracesList.forEach(function(t,s){var a=t.AcceptTime.split(" ");t.acceptDay=a[0],t.acceptTimeStr=a[1];var r=o()(t.acceptDay).day();(t.acceptWeek=_[r],s>0)?e.tracesList[s-1].acceptDay===t.acceptDay?t.isShowDay=!1:t.isShowDay=!0:0===s&&(t.isShowDay=!0)})}})},goOrderManage:function(){this.$router.push("/official/shopmall/order")},goOrderManageStatus:function(){var e="0";switch(this.orderStatus){case 1:e="1";break;case 2:e="2";break;case 3:e="3";break;case 5:e="4";break;case 9:e="5"}this.$router.push({path:"/official/shopmall/order",query:{status:e}})},goOrderDetail:function(){this.$router.push({path:"/officialmanage/orderdetail",query:{orderid:this.orderid}})}}}}).call(t,s("L7Pj"))}});