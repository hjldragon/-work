(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-1874d4ba"],{"081b":function(t,e,i){"use strict";i.r(e);var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"vendor-goods-list"}},[i("base-header",{attrs:{"head-title":"商品库存"}}),i("div",{staticClass:"gray-bg"}),t._m(0),i("div",{staticClass:"van-hairline--bottom"}),i("div",{staticClass:"goods-content flex-box"},[i("div",{staticClass:"goods-cate flex-none"},t._l(t.goodsList,function(e){return i("div",{key:e.category_id,staticClass:"cate-item",class:{active:t.currentCateId===e.category_id},on:{click:function(i){t.cateClickHandle(e)}}},[t._v("\n                "+t._s(e.category_name)+"("+t._s(e.all_num)+")\n                "),t.currentCateId===e.category_id?i("div",{staticClass:"active-bar"}):t._e()])}),0),i("div",{staticClass:"goods-list item-grow"},t._l(t.goodsList,function(e){return i("div",{key:e.category_id},[t.currentCateId===e.category_id?i("div",t._l(e.goods_list,function(e){return i("div",{key:e.goods_id,staticClass:"goods-item"},[i("div",{staticClass:"flex-box"},[i("div",{staticClass:"goods-name item-grow"},[t._v(t._s(e.vendor_goods_name)+" "+t._s(e.goods_spec))]),i("div",{staticClass:"goods-stock-num flex-none"},[t._v(t._s(e.goods_stock))])]),i("div",{staticClass:"van-hairline--bottom"})])}),0):t._e()])}),0)])],1)},n=[function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"goods-title flex-box"},[i("div",{staticClass:"goods-name item-grow"},[t._v("商品")]),i("div",{staticClass:"goods-num flex-none"},[t._v("数量")])])}],a=(i("eee6"),i("1f43")),c=i("4ec3"),s=i("a041"),l=i("9975"),d={components:{BaseHeader:a["a"]},data:function(){return{goodsList:[],currentCateId:"0"}},created:function(){this.getList()},methods:{getList:function(){var t=this,e={get_oa_goods:1,shop_id:l["a"].getShopid()};Object(c["l"])(e).then(function(e){0===e.ret?(t.goodsList=e.data.list||[],t.goodsList.map(function(t){return"全部"===t.category_name&&(t.category_id="0"),t.goods_list=t.goods_list||[],t})):t.$toast({message:s["f"].toString(e.ret),duration:2e3})})},cateClickHandle:function(t){this.currentCateId=t.category_id}}},r=d,g=(i("431c"),i("25c1")),A=Object(g["a"])(r,o,n,!1,null,"5a4511ef",null);A.options.__file="list.vue";e["default"]=A.exports},"1f43":function(t,e,i){"use strict";var o=function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("div",{staticClass:"header flex-box cross-center",class:{"border-gray":t.showBorder}},[o("div",{staticClass:"h-left"},[t._t("left",[o("img",{staticClass:"back icon",attrs:{src:i("336c")},on:{click:t.goBack}})])],2),o("div",{staticClass:"h-center item-grow"},[t._t("center",[o("span",[t._v(t._s(t.headTitle))])])],2),o("div",{staticClass:"h-right"},[t._t("right",[o("img",{staticClass:"home icon",attrs:{src:i("fbf5")},on:{click:t.goHome}})])],2)])},n=[],a=(i("ff66"),i("ea23"),i("dbff"),{props:{showBorder:{type:Boolean,default:!0},headTitle:{type:String,default:""}},methods:{goBack:function(){this.$router.go(-1)},goHome:function(){this.$router.push("/")}}}),c=a,s=(i("652a"),i("25c1")),l=Object(s["a"])(c,o,n,!1,null,"613235bf",null);l.options.__file="index.vue";e["a"]=l.exports},"336c":function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAiCAYAAABStIn6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQyQ0VGQUEzMEU3ODExRTlCMjQ3RjBFQ0VBM0NENTA2IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQyQ0VGQUE0MEU3ODExRTlCMjQ3RjBFQ0VBM0NENTA2Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDJDRUZBQTEwRTc4MTFFOUIyNDdGMEVDRUEzQ0Q1MDYiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDJDRUZBQTIwRTc4MTFFOUIyNDdGMEVDRUEzQ0Q1MDYiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7vXRF4AAABu0lEQVR42qSW3UcEURjGd7aUIhGR7iMREV1EdFNSe1EpIl2kLdmLWiVFSktardYmXaToQ1LR101JiYguIqI/I1LSh+jjeXmGcezOzjn78rP7zBw/Y8457xwrEAj4MqhOsAoOszOQdIBdkCX//YaSNrBDyR8Im4hawB7IoSQENnVFTeDIIQmDFbmhI2pwSKRGwZJ906uoHpyAPOZxkHAO8CKqA6cgn3kKxNRB6US1iiQCZpMNdBPVgDNQwDwHZlINTiWqBhegkHkBTLo9ejJRlSJZBGPpXqQqqqSkiHkZjHiZVqeoAlyBYmbZjENceJ5F5YpkHQx6ldiiMkpKeG0LDOhIbJGs2FJmaQt94Ed3J4so15Hfwa9JX/GztzwxBzlTlonoETSCZ14LqRtSZ9YeKHtlHgZxE5HUvSKThRg1EUndgWbwxjzBHa8tkrplX/5gnibaIqkbRRbh02mLpK5BK/hkjrpt4HQd8hK0g2/mOGdUWyR1rsgSXGvaIh/7dhdlFld/0EQkdQy6uaEt9qteE5HUgSJbAz1yw+Q0ss+v7QYPEfL7ZXoa2Qb9bH4imzcV+fgk8sJf5KD1L8AA571YoT4wQfAAAAAASUVORK5CYII="},"431c":function(t,e,i){"use strict";var o=i("6031"),n=i.n(o);n.a},5956:function(t,e,i){},6031:function(t,e,i){},"652a":function(t,e,i){"use strict";var o=i("5956"),n=i.n(o);n.a},9975:function(t,e,i){"use strict";i.d(e,"a",function(){return o});var o={getShopid:function(){return window.Store.GetGlobalData("SHOPID")},setShopid:function(t){return window.Store.SetGlobalData("SHOPID",t)},delShopid:function(){return window.Store.DeleteGlobalData("SHOPID")},getUserid:function(){return window.Store.GetGlobalData("USERID")},setUserid:function(t){return window.Store.SetGlobalData("USERID",t)},delUserid:function(){return window.Store.DeleteGlobalData("USERID")},getPlatformid:function(){return window.Store.GetGlobalData("PLATFORMID")},setPlatformid:function(t){return window.Store.SetGlobalData("PLATFORMID",t)},delPlatformid:function(){return window.Store.DeleteGlobalData("PLATFORMID")},getUseName:function(){return window.Store.GetGlobalData("USENAME")},setUseName:function(t){return window.Store.SetGlobalData("USENAME",t)},delUseName:function(){return window.Store.DeleteGlobalData("USENAME")}}},fbf5:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA1CAYAAAAd84i6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQ4QkFBMjczMEU3ODExRTk4QTc3RjU4N0ZCMzk5RDJGIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQ4QkFBMjc0MEU3ODExRTk4QTc3RjU4N0ZCMzk5RDJGIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDhCQUEyNzEwRTc4MTFFOThBNzdGNTg3RkIzOTlEMkYiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDhCQUEyNzIwRTc4MTFFOThBNzdGNTg3RkIzOTlEMkYiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4IiOyyAAAEg0lEQVR42uyaaWxMURTH7xhKm0gT+wdLCAmh8YG0wSfUXrShVCyNNXYidmqJXYLEHqRIQ0qtVQSNEIkQiYQhElGpLbHFFh2MVP1P5kxy3Lwx03kzb96jJ/klvbevr/f/3j33nnPuc2VkZCiLrB4YBrJBO9AAPAfXwH7wxIpB1LZILD3VXaCl1t8MpIJ5YDdYCH7EciC1LBC7ChQbiNUf/GxwHTRysuANYCVwcfsXOA7GgSFgLXgvrk8DF0GyEwUvBYtF+yXoAUaCAnAO5LE/nxHXdQWnQKKTBE8H60S7DHQHtwyu/QSGgyOirxcoBG4nCB4Ddmpvtj948Ze/qQQTQInooyl/ULiDLQUPBYfEIN+BAWFuOT6e7jdE31iw1a6Ce/GCFJiGn8Eg8KAa9/CCweC+6JsLltlNcBovQgli4JngTgT3ogeVrs0KWs2n2UVwCrgEksTUHMERVKRGrtCH/T9gO8DoeAtuC0rFvkmLTy44H4UHWQ4GsnjFrpLPfXER3BxcBU24XcXTrjCK64KHfbqC2+QyRbyfWyq4MYttIfoWcBIQbbvNW5SP20k8g1KsEkzT9wpHSHJR2RLDqI0e7ih2mcAYStmlYio4iWPdztpikmdBEkLh5lR2HcWudIldKyaCyX/Ogm6i7zCYo6yzA2CJaLcBF0DDaAt282KULvpOg4niiVtlm8BGbVs8L7ZF04JdvB1kiT7y4RzhU1YbZWL7tMCnWAQ+pgRv5/w1YDc5ivKp+FkVZ2QnRF9vzrjcZgSvATNF+x5v/F4Vf6vkyKtU9A3nUpErEsHzwXLRfszh3mdlH/NxhnZb9E0B66srmP5os2g/5ynzTtnPvDzrPKKPKi2LwhWco02Ltyz2pbKvfWDRZaKP6mmTQwnO4L3VLcov6cqimrFJC1RW3ojdZY/y18INBfcEx8TSXsE38Cjn2BNtnaEXd5SLE38ITuVAQua0Q7TFwCnm0XaSBC5OpAUEU6RSouW02RywO9VusgaZYZHGFBK8ldO9wIaey1GL041i7AkiGqQTjS0k+Iu4aIb6sz4cqGp0dIDA9ow00jJLtCvoTGcS+6qHUz9p6dzn5jdfYFOxWSLMzNJmKK3Uz0AnKlDQG/7IQcZFgxvR6labl/h+Nn67vXk9IvoGmd6k8WOo41JXkJ8jsQ6gi0Ea9w3cBQ9N3LtWuPmBFefDdZT/yCRUifWk8h/TfI/lYKw4H56vwqsnU0S0ItaDseINT9TCvzfa72m7aCWSlqVOFkxRTmvRHmgQqtLvn/LPVJuizyBeO3VKJ2r/w+j7jR8G1QxH+7C0umHMsu//kuCvYVyT/C8JjleVM26Cjezb/ybY978JVjWCawQ7R3CC0wSbTQkTnSa4frxW02qaDEfrmUkeyrXAfzxnPOHGu00i2HOpPv6qGmLpC4BM0X5hRjB9ZrCOk3gafL4Fe+4hk5FckZkpTWc1i6OUwfwKki15VfS+gl+tQnzqGE4+THVrqjlRCZcqf00jGAgJ2hYkE/qp/J88LQnlf0GMCgqPwF5wOdTFvwUYAB3B6Cr79F1BAAAAAElFTkSuQmCC"}}]);