webpackJsonp([36],{333:function(t,o,i){"use strict";function e(t){i(448)}Object.defineProperty(o,"__esModule",{value:!0});var s=i(407),n=i(450),a=i(2),d=e,r=Object(a.a)(s.a,n.a,n.b,!1,d,"data-v-86787836",null);o.default=r.exports},407:function(t,o,i){"use strict";var e=i(9),s=i.n(e),n=i(210),a=i(7),d=i(28),r=i(45),f=(i(4),i(46)),l=(i(29),i(6)),c=i(30);o.a={props:["searchFoodList"],components:{addMinusBtn:n.a,Spec:r.default,MoveDot:c.a,shopcar:f.default},data:function(){return{openId:null,dialogFood:null,menuinfo:window.$data.data.menuinfo,imgbase_url:"./php",is_Vip:0,isShowSpecDialog:!1,elleft:0,elbottom:0,isShowDot:[],windowHeight:0,receiveInCart:!1}},computed:s()({},Object(a.d)(["cart_list","notPayNum"]),{IsVipCustomer:function(){return l.j},FoodType:function(){return l.b},IsAccessory:function(){return l.d},shopCart:function(){return s()({},this.cart_list)},foodList:function(){if(this.searchFoodList&&this.searchFoodList.length>0)return this.searchFoodList}}),created:function(){this.is_Vip=window.$data.data.custominfo.is_vip},mounted:function(){this.windowHeight=window.innerHeight},methods:{minusCountMethods:function(t,o,i){this.foodList[o].num=(this.shopCart[i]||{}).num,d.a.getMenuCount(this.shopCart,this.collectList)},showMoveDot:function(t,o,i){this.foodList[o].num=this.shopCart[i].num,d.a.getMenuCount(this.shopCart,this.foodList),this.isShowDot.push(!0),this.elleft=event.target.getBoundingClientRect().left,this.elbottom=event.target.getBoundingClientRect().bottom},hideDot:function(){this.isShowDot=this.isShowDot.map(function(t){return!1}),this.receiveInCart||(this.receiveInCart=!0)},dotInCart:function(){this.receiveInCart=!1},addCount:function(t,o,i){this.ADD_NOSPEC({category_id:t,food_id:o}),this.foodList[i].num=this.shopCart[o].num,d.a.getMenuCount(this.shopCart,this.foodList),this.isShowDot.push(!0),this.elleft=event.target.getBoundingClientRect().left,this.elbottom=event.target.getBoundingClientRect().bottom},getNum:function(t,o){return d.a.setTypeNum(t,o).speNum},showSpcDialog:function(t,o){var i=this,e=this.foodList.find(function(t){return i.openId=o,t.food_id===o});this.dialogFood=e,this.isShowSpecDialog=!0},hideSpecDialog:function(){this.isShowSpecDialog=!1},goFoodDetail:function(t,o){this.$router.push({path:"good/foodDetail",query:{foodid:t+"#"+o}})},addEvent:function(){var t=this;this.$refs.cartContainer.addEventListener("animationend",function(){t.dotInCart()}),this.$refs.cartContainer.addEventListener("webkitAnimationEnd",function(){t.dotInCart()})}}}},448:function(t,o,i){var e=i(449);"string"==typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);i(324)("fc85b412",e,!0,{})},449:function(t,o,i){o=t.exports=i(323)(!1),o.push([t.i,"li[data-v-86787836],ul[data-v-86787836]{padding:0;list-style:none}.float-left[data-v-86787836]{float:left}.float-right[data-v-86787836]{float:right}.text-line[data-v-86787836]{text-decoration:line-through;padding-left:.13333rem}.text-gray[data-v-86787836]{font-size:.29333rem;color:#989898;font-family:PingFang-SC-Medium}.food-list .food-item[data-v-86787836]{height:2.13333rem;margin-bottom:.53333rem;padding-bottom:.48rem}.food-list .food-item .food-img[data-v-86787836]{float:left;width:1.81333rem;height:1.81333rem;border-radius:.13333rem}.food-list .food-item .food-img img[data-v-86787836]{width:100%;height:100%;border-radius:.13333rem}.food-list .food-item .food-info[data-v-86787836]{width:7.33333rem;margin-left:.24rem}.food-list .food-item .food-info .food-name[data-v-86787836]{padding:0;margin:0;font-size:.4rem;color:#323232;height:.48rem;line-height:.44rem;font-family:PingFang-SC-Bold;font-weight:700;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.food-list .food-item .food-info .food-detail[data-v-86787836]{font-size:.29333rem;color:#989898;font-family:PingFang-SC-Medium}.food-list .food-item .food-info .food-detail .detail-item[data-v-86787836]{margin-right:.4rem}.food-list .food-item .food-info .discount-bord[data-v-86787836]{height:.49333rem;line-height:.49333rem}.food-list .food-item .food-info .discount-bord .board-item[data-v-86787836]{display:block;border:.01333rem solid #ff6f06;background-color:#fff7f0;width:1.2rem;height:.46667rem;font-size:.29333rem;color:#ff6f06;border-radius:.05333rem;text-align:center}.food-list .food-item .food-info .discount-bord .no-board[data-v-86787836]{height:.36rem}.food-list .food-item .food-info .unit-price[data-v-86787836]{font-size:.32rem;color:#ff3637}.food-list .food-item .food-info .unit-price .text-red[data-v-86787836]{font-weight:600;font-family:PingFang SC}.food-list .food-item .food-info .unit-price .text-price[data-v-86787836]{font-size:.45333rem;color:#ff3637}.food-list .food-item .btn-warp[data-v-86787836]{margin-top:-.6rem;margin-right:.13333rem}.food-list .food-item .specification[data-v-86787836]{margin-right:.13333rem;margin-top:-.62667rem;position:relative}.food-list .food-item .specification .spe[data-v-86787836]{display:inline-block;position:absolute;right:0;top:0;width:1.70667rem;height:.69333rem;border-radius:.34rem;line-height:.69333rem;font-size:.37333rem;color:#fff;font-weight:400;text-align:center;background:#ff6f06}.food-list .food-item .specification .cirle[data-v-86787836]{position:absolute;right:0;top:-.2rem;min-width:.4rem;height:.4rem;background:#ff3637;border-radius:.2rem;line-height:.4rem;text-align:center;font-size:.32rem;color:#fff;padding-left:.08rem;padding-right:.08rem}",""])},450:function(t,o,i){"use strict";i.d(o,"a",function(){return e}),i.d(o,"b",function(){return s});var e=function(){var t=this,o=t.$createElement,e=t._self._c||o;return e("div",{attrs:{id:"food-list-page"}},[t.isShowSpecDialog?e("spec",{attrs:{openId:t.openId},on:{click:t.hideSpecDialog,"on-hidden":t.hideDot}}):t._e(),t._v(" "),e("ul",{staticClass:"food-list"},t._l(t.foodList,function(o,s){return e("li",{key:o.food_id,staticClass:"food-item"},[e("div",{staticClass:"food-img float-left",on:{click:function(i){t.goFoodDetail(o.food_id,o.category_id)}}},[o.food_img_list&&o.food_img_list.length>0?e("img",{attrs:{src:t.imgbase_url+"/img_get.php?img=1&height=136&width=136&imgname="+o.food_img_list[0]}}):e("img",{attrs:{src:i(209),alt:"缺省图"}})]),t._v(" "),e("div",{staticClass:"food-info float-left"},[e("p",{staticClass:"food-name",on:{click:function(i){t.goFoodDetail(o.food_id,o.category_id)}}},[t._v(t._s(o.food_name))]),t._v(" "),e("p",{staticClass:"food-detail"},[e("span",{staticClass:"detail-item"},[t._v("月售\n            "),e("span",[t._v(t._s(o.food_num_mon)+" ")])]),t._v(" "),e("span",{staticClass:"detail-item"},[t._v("赞\n            "),e("span",[t._v(" "+t._s(o.praise_num))])])]),t._v(" "),e("div",{staticClass:"discount-bord"},[void 0!==o.discount?e("span",{staticClass:"board-item"},[t._v("促销价")]):t.is_Vip!==t.IsVipCustomer.NO&&void 0!==o.vip?e("span",{staticClass:"board-item"},[t._v("会员价")]):void 0!==o.festival?e("span",{staticClass:"board-item"},[t._v("节日价")]):e("div",{staticClass:"no-board"})]),t._v(" "),o.type===t.IsAccessory.YES?e("p",{staticClass:"unit-price"},[e("span",{staticClass:"text-red"},[t._v("¥")]),t._v(" "),e("span",{staticClass:"text-red text-price"},[t._v(t._s(o.food_price))])]):e("p",{staticClass:"unit-price"},[e("span",{staticClass:"text-red"},[t._v("¥")]),t._v(" "),e("span",{staticClass:"text-red text-price"},[t._v(t._s(o.unitPrice))]),t._v(" "),void 0!==o.unitOriginal&&(2===o.food_price.type||void 0!==o.food_price.festival_price||void 0!==o.food_price.discount_price||t.is_Vip!==t.IsVipCustomer.NO&&void 0!==o.vip)?e("span",{staticClass:"text-gray text-line"},[t._v("¥\n            "),e("span",[t._v(t._s(o.unitOriginal))])]):t._e()]),t._v(" "),e("div",{directives:[{name:"show",rawName:"v-show",value:0==o.spec.length||0!==o.spec.length&&null==o.spec[o.spec.length-1].list,expression:" food.spec.length==0||(food.spec.length!==0&&food.spec[food.spec.length-1].list==null)"}],staticClass:"btn-warp  float-right"},[e("add-minus-btn",{attrs:{food:o,categoryId:o.category_id,foodId:o.food_id,foodIdx:s,menuinfo:t.menuinfo},on:{showMoveDot:t.showMoveDot,minusCountMethods:t.minusCountMethods}})],1),t._v(" "),e("div",{directives:[{name:"show",rawName:"v-show",value:o.spec.length>0&&null!==o.spec[o.spec.length-1].list&&o.spec[o.spec.length-1].list.length>0,expression:"food.spec.length>0&&(food.spec[food.spec.length-1].list!==null&&food.spec[food.spec.length-1].list.length>0)"}],staticClass:"specification float-right"},[e("span",{staticClass:"spe",on:{click:function(i){t.showSpcDialog(o.category_id,o.food_id)}}},[t._v("选规格")]),t._v(" "),t.getNum(o,t.cart_list)>0?e("span",{staticClass:"cirle"},[t._v(t._s(t.getNum(o,t.cart_list)))]):t._e()]),t._v(" "),o.type!==t.IsAccessory.YES&&t.is_Vip===t.IsVipCustomer.NO&&o.beComeVip?e("p",{staticClass:"text-gray"},[t._v("成为会员尊享\n          "),e("span",[t._v("¥\n            "),e("span",[t._v(t._s(o.beComeVip))])])]):t._e()])])})),t._v(" "),t.isShowDot?e("move-dot",{staticClass:"animate",attrs:{elleft:t.elleft,elbottom:t.elbottom,isShowDot:t.isShowDot,windowHeight:t.windowHeight},on:{"on-hidden":t.hideDot}}):t._e(),t._v(" "),t.foodList&&t.foodList.length>0?e("shopcar",{attrs:{isFromHome:!1,receiveInCart:t.receiveInCart},on:{"on-receiveInCart":t.dotInCart}}):t._e()],1)},s=[]}});