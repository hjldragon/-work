webpackJsonp([106],{Jetu:function(t,s){},cE0n:function(t,s,i){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var e={props:{shopinfo:{type:Object}},data:function(){return{}}},a={render:function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"order-shopinfo"},[i("div",{staticClass:"title"},[t._v("商品信息")]),t._v(" "),0!==t.shopinfo.list.length?i("div",{staticClass:"table-content"},[i("table",{staticClass:"table"},[t._m(0),t._v(" "),t._l(t.shopinfo.list,function(s,e){return i("tr",{key:e},[i("td",{staticClass:"noborder food-name"},[t._v("\n                "+t._s(s.goods_name)+"\n              ")]),t._v(" "),i("td",{staticClass:"noborder"},[t._v("¥ "+t._s(s.goods_price))]),t._v(" "),i("td",{staticClass:"noborder"},[t._v(t._s(s.goods_num))]),t._v(" "),i("td",[t._v("¥ "+t._s(s.goods_price_sum))])])}),t._v(" "),i("tr",[i("td",{staticClass:"noborder food-name",attrs:{colspan:"3"}},[t._v("\n                  商品总数: "+t._s(t.shopinfo.listNum)+"\n              ")]),t._v(" "),i("td",[t._v("\n                  总计: ¥ "+t._s(t.shopinfo.listTotalPrice)+"\n              ")])]),t._v(" "),i("tr",{staticClass:"last"},[i("td",{staticClass:"clearfix",attrs:{colspan:"4"}},[i("div",{staticClass:"td-title fl"},[t._v("备注:")]),t._v(" "),i("div",{staticClass:"remark-content fl"},[t._v(t._s(t.shopinfo.remark))])])])],2)]):t._e()])},staticRenderFns:[function(){var t=this.$createElement,s=this._self._c||t;return s("tr",[s("th",{staticStyle:{width:"37%"}},[this._v("名称")]),this._v(" "),s("th",{staticStyle:{width:"21%"}},[this._v("单价")]),this._v(" "),s("th",{staticStyle:{width:"21%"}},[this._v("数量")]),this._v(" "),s("th",{staticStyle:{width:"21%"}},[this._v("价格")])])}]};var n=i("C7Lr")(e,a,!1,function(t){i("Jetu")},"data-v-55129df8",null);s.default=n.exports}});