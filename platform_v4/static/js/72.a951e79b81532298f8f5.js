webpackJsonp([72],{"30wV":function(e,t){},ISX9:function(e,t){},"Qwa/":function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=i("IHPB"),c=i.n(n),a=i("4YfN"),s=i.n(a),l=i("5jUN"),r=i("9rMa"),o=i("6nXL"),u=i("EuEE"),p=i("6ROu"),_=i.n(p),m={components:{GoodSaleway:l.default},data:function(){return{qu_price:"",qu_stocknum:"",qu_saleprice:"",qu_saleTime:null,GoodSpecType:o.n,ProductTest:o.I,priceList:[{spec_id:"",spec_name:"1",package:"2",terminal:null,time:"",time_unit:null,price:"",stock_num:"",sale_price:"",sale_time:null}],priceListIdx:{},saleNum:"",selectSalePrice:!1,pickerOptions:{disabledDate:function(e){return e.getTime()<Date.now()-864e5}}}},computed:s()({},Object(r.d)({good_spec_list:function(e){return e.shopmall_goods.good_spec_list},good_package_list:function(e){return e.shopmall_goods.good_package_list},terminal_list:function(e){return e.shopmall_goods.terminal_list},time_list:function(e){return e.shopmall_goods.time_list},spec_price:function(e){return e.shopmall_goods.spec_price},spec_price_id:function(e){return e.shopmall_goods.spec_price_id},category_id_list:function(e){return e.shopmall_goods.category_id_list}}),Object(r.b)(["spec_type"]),{time_list_str:function(){return this.time_list.map(function(e){return e.itemStr})},showSpec:function(){return this.spec_type===o.n.SPEC||this.spec_type===o.n.SPECANDPACK},showPackage:function(){return this.spec_type===o.n.PACKAGE||this.spec_type===o.n.SPECANDPACK},showTerminal:function(){return this.spec_type===o.n.TERMINAL},showTime:function(){return this.spec_type===o.n.TERMINAL||this.spec_type===o.n.SERVERTIME}}),created:function(){this.generaTable()},watch:{spec_type:function(){var e=this;this.$nextTick(function(){e.generaTable()})},good_spec_list:function(){var e=this;this.$nextTick(function(){e.generaTable()})},good_package_list:function(){var e=this;this.$nextTick(function(){e.generaTable()})},terminal_list:function(){var e=this;this.$nextTick(function(){e.generaTable()})},category_id_list:function(e,t){var i=this;e[0]!==t[0]&&this.$nextTick(function(){i.generaTable()})},time_list:{handler:function(){var e=this;this.$nextTick(function(){e.generaTable()})},deep:!0},selectSalePrice:function(e){this.SMG_SET_SALEPRICESELECT({goods_saleprice_select:e}),e||(this.qu_saleprice="",this.qu_saleTime=null,this.priceList.forEach(function(e){e.sale_price="",e.sale_time=null}))}},methods:s()({},Object(r.c)(["SMG_SET_GOODSPRICE","SMG_SET_SALEPRICESELECT"]),{generaTable:function(){var e=this,t=[];switch(this.spec_type){case o.n.SPEC:this.good_spec_list.forEach(function(e){var i={spec_id:"",spec_name:e,package:"",terminal:null,time:"",time_unit:null,price:"",stock_num:"",sale_price:"",sale_time:null};t.push(i)});break;case o.n.PACK:this.good_package_list.forEach(function(e){var i={spec_id:"",spec_name:"",package:e,terminal:null,time:"",time_unit:null,price:"",stock_num:"",sale_price:"",sale_time:null};t.push(i)});break;case o.n.SPECANDPACK:this.good_spec_list.forEach(function(i){e.good_package_list.forEach(function(e){var n={spec_id:"",spec_name:i,package:e,terminal:null,time:"",time_unit:null,price:"",stock_num:"",sale_price:"",sale_time:null};t.push(n)})});break;case o.n.TERMINAL:this.terminal_list.forEach(function(i){e.time_list.forEach(function(e){var n={spec_id:"",spec_name:"",package:"",terminal:i,time:e.time,time_unit:e.time_unit,timestr:e.timestr,price:"",stock_num:"",sale_price:"",sale_time:null};t.push(n)})});break;case o.n.SERVERTIME:this.time_list.forEach(function(e){var i={spec_id:"",spec_name:"",package:"",terminal:0,time:e.time,time_unit:e.time_unit,timestr:e.timestr,price:"",stock_num:"",sale_price:"",sale_time:null};t.push(i)})}var i=function(t,i){t.spec_id=i.spec_id,t.price=i.price,t.sale_price=i.sale_price,t.sale_price&&(e.selectSalePrice=!0),i.sale_time&&(i.sale_time=i.sale_time.map(function(e){return"number"==typeof e?_()(1e3*e).format():e}),t.sale_time=i.sale_time),t.stock_num=i.stock_num};t.forEach(function(t){e.spec_price_id.forEach(function(n){switch(e.spec_type){case o.n.SPEC:n.spec_name&&t.spec_name===n.spec_name&&n.spec_id&&i(t,n);break;case o.n.PACK:n.package&&t.package===n.package&&n.spec_id&&i(t,n);break;case o.n.SPECANDPACK:n.spec_name&&n.package&&t.spec_name===n.spec_name&&t.package===n.package&&n.spec_id&&i(t,n);break;case o.n.TERMINAL:n.terminal&&n.time&&n.time_unit&&t.time===n.time&&t.time_unit===n.time_unit&&t.terminal===n.terminal&&n.spec_id&&i(t,n);break;case o.n.SERVERTIME:n.time&&n.time_unit&&t.time===n.time&&t.time_unit===n.time_unit&&n.spec_id&&i(t,n)}})}),this.priceList=t,this.setPrice()},generaTableIdx:function(){var e={};this.priceList.forEach(function(t){e[t.spec_id]=t}),this.priceListIdx=e},quPriceHandler:function(){var e=this;this.priceList.forEach(function(t){t.price=e.qu_price,t.stock_num=e.qu_stocknum,t.sale_price=e.qu_saleprice,t.sale_time=e.qu_saleTime&&[].concat(c()(e.qu_saleTime))}),this.setPrice()},setPrice:function(){this.SMG_SET_GOODSPRICE({spec_price:[].concat(c()(this.priceList))})},checkInputPrice:function(e,t){e[t]=u.a.clearNoNum(e[t])},checkRoundNum:function(e,t){e[t]=u.a.checkRound(e[t])},checkPrice:function(e){this[e]=u.a.clearNoNum(this[e])},checkNum:function(e){this[e]=u.a.checkRound(this[e])},handlerSalePrice:function(e,t){t.column;var i=t._self;return e("el-radio",{attrs:{label:!0},class:{sale_price_select:!0,sale_price_noselect:!i.selectSalePrice},on:{input:function(e){i.selectSalePrice=!0}},nativeOn:{click:function(){i.selectSalePrice&&(i.selectSalePrice=!1)}},props:{value:i.selectSalePrice}},["促销价（元）"])},handlerSaleTimeChange:function(e,t){t&&(_()(t[0]).format("X")<_()().format("X")&&(this.$notify({title:"提示",message:"促销时间不能小于当前时间",type:"warning"}),e.sale_time=null));this.setPrice()},handlerQuSaleTimeChange:function(e){e&&(_()(e[0]).format("X")<_()().format("X")&&(this.$notify({title:"提示",message:"促销时间不能小于当前时间",type:"warning"}),this.qu_saleTime=null))}})},d={render:function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"price-content"},[e._m(0),e._v(" "),i("div",{staticClass:"content"},[i("div",{staticClass:"edit-item clearfix good-price"},[e._m(1),e._v(" "),i("div",{staticClass:"edit-content fl"},[i("div",{staticClass:"price-quantity"},[i("el-input",{staticClass:"qu-item",attrs:{placeholder:"价格"},nativeOn:{keyup:function(t){e.checkPrice("qu_price")}},model:{value:e.qu_price,callback:function(t){e.qu_price=t},expression:"qu_price"}}),e._v(" "),i("el-input",{staticClass:"qu-item",attrs:{placeholder:"库存数"},nativeOn:{keyup:function(t){e.checkNum("qu_stocknum")}},model:{value:e.qu_stocknum,callback:function(t){e.qu_stocknum=t},expression:"qu_stocknum"}}),e._v(" "),i("el-input",{staticClass:"qu-item",attrs:{disabled:!e.selectSalePrice,placeholder:"促销价"},nativeOn:{keyup:function(t){e.checkPrice("qu_saleprice")}},model:{value:e.qu_saleprice,callback:function(t){e.qu_saleprice=t},expression:"qu_saleprice"}}),e._v(" "),i("el-date-picker",{attrs:{disabled:!e.selectSalePrice,"unlink-panels":"",editable:!1,type:"datetimerange","picker-options":e.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},on:{change:e.handlerQuSaleTimeChange},model:{value:e.qu_saleTime,callback:function(t){e.qu_saleTime=t},expression:"qu_saleTime"}}),e._v(" "),i("span",{staticClass:"confirm-btn sl-btn-bd-s-blue",on:{click:e.quPriceHandler}},[e._v("确定")])],1),e._v(" "),i("div",{staticClass:"price-table",class:{"sale-price-no-select":!e.selectSalePrice}},[i("el-table",{attrs:{id:"good-price-table",data:e.priceList,border:""}},[e.showSpec?i("el-table-column",{attrs:{prop:"spec_name",label:"规格",width:"90",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.spec_name))])]}}])}):e._e(),e._v(" "),e.showPackage?i("el-table-column",{attrs:{prop:"package",label:"套餐",width:"90",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.package))])]}}])}):e._e(),e._v(" "),e.showTerminal?i("el-table-column",{attrs:{prop:"terminal",label:"授权端",width:"120",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(e.ProductTest.toString(t.row.terminal)))])]}}])}):e._e(),e._v(" "),e.showTime?i("el-table-column",{attrs:{prop:"time",label:"时长",width:"90",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.timestr))])]}}])}):e._e(),e._v(" "),i("el-table-column",{attrs:{prop:"price",label:"价格（元）",width:"90",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("el-input",{attrs:{type:"text"},on:{change:e.setPrice},nativeOn:{keyup:function(i){e.checkInputPrice(t.row,"price")}},model:{value:t.row.price,callback:function(i){e.$set(t.row,"price",i)},expression:"scope.row.price"}})]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"stock_num",label:"库存（件）",width:"90",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("el-input",{attrs:{type:"text"},on:{change:e.setPrice},nativeOn:{keyup:function(i){e.checkRoundNum(t.row,"stock_num")}},model:{value:t.row.stock_num,callback:function(i){e.$set(t.row,"stock_num",i)},expression:"scope.row.stock_num"}})]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"sale_price",label:"促销价",width:"120",align:"center","render-header":e.handlerSalePrice},scopedSlots:e._u([{key:"default",fn:function(t){return[i("el-input",{attrs:{type:"text",disabled:!e.selectSalePrice},on:{change:e.setPrice},nativeOn:{keyup:function(i){e.checkInputPrice(t.row,"sale_price")}},model:{value:t.row.sale_price,callback:function(i){e.$set(t.row,"sale_price",i)},expression:"scope.row.sale_price"}})]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"num_no",label:"促销时间","min-width":"360",align:"center","class-name":"sale-time"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("el-date-picker",{attrs:{disabled:!e.selectSalePrice,"unlink-panels":"",editable:!1,type:"datetimerange","picker-options":e.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},on:{change:function(i){e.handlerSaleTimeChange(t.row,i)}},model:{value:t.row.sale_time,callback:function(i){e.$set(t.row,"sale_time",i)},expression:"scope.row.sale_time"}})]}}])})],1)],1)])]),e._v(" "),i("good-saleway")],1)])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("h3",{staticClass:"title change-titlerr"},[t("span",{staticClass:"sl-must"},[this._v("商品价格")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"edit-label fl"},[t("span",[this._v("批量填充")])])}]};var h=i("C7Lr")(m,d,!1,function(e){i("ISX9"),i("30wV")},"data-v-ad5247ca",null);t.default=h.exports}});