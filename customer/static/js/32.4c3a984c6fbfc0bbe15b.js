webpackJsonp([32],{354:function(t,e,a){"use strict";function r(t){a(573)}Object.defineProperty(e,"__esModule",{value:!0});var i=a(498),n=a(575),o=a(2),s=r,m=Object(o.a)(i.a,n.a,n.b,!1,s,"data-v-4016e532",null);e.default=m.exports},498:function(t,e,a){"use strict";var r=a(9),i=a.n(r),n=a(44),o=a(7);e.a={components:{VHeader:n.a},data:function(){return{remark_txt:"",tagArr:["不吃辣","微辣","中辣","特辣","不吃香菜","不吃葱","不吃蒜","不吃生姜"],chooseTagArr:[]}},created:function(){this.remark_txt=this.$route.query.remark},computed:i()({},Object(o.d)(["food_remark"])),methods:i()({},Object(o.c)(["GET_FOOD_REMARK"]),{showCantInputTip:function(){if(this.remark_txt.length>=50)return this.$slnotify({message:"最多可输入50字~"})},chooseTag:function(t){var e=this.remark_txt+" "+t+" ";if(this.remark_txt.length>=50||e.length>=50)return this.remark_txt=this.remark_txt,this.$slnotify({message:"最多可输入50字~"});this.remark_txt=e},goToOrder:function(){this.GET_FOOD_REMARK({food_remark:this.remark_txt}),this.$router.go(-1)}})}},573:function(t,e,a){var r=a(574);"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);a(324)("37396a1f",r,!0,{})},574:function(t,e,a){e=t.exports=a(323)(!1),e.push([t.i,".remark-title[data-v-4016e532]{background-color:#fff;border-bottom:1px solid #e7e7e7;z-index:100;position:fixed;top:0;width:10rem;padding:0 .53333rem;height:1.17333rem;font-size:0;line-height:1.17333rem;overflow:hidden}.remark-title .back[data-v-4016e532],.remark-title .shop[data-v-4016e532],.remark-title .user[data-v-4016e532]{float:left;width:33.3333%}.remark-title .back img[data-v-4016e532],.remark-title .shop img[data-v-4016e532],.remark-title .user img[data-v-4016e532]{width:.74667rem;height:.74667rem;vertical-align:middle}.remark-title .user[data-v-4016e532]{font-size:.48rem;text-align:center}.remark-title .shop[data-v-4016e532]{font-size:.42667rem;text-align:right}.remark-content[data-v-4016e532]{position:relative;font-size:.4rem;color:#323232;background:#fafafa;border:.01333rem solid #d6d6d6;margin:1.41333rem .4rem .72rem;border-radius:.13333rem;width:9.2rem;height:2.89333rem}.remark-content #txt-area[data-v-4016e532]{width:9.14667rem;height:2.8rem;background:#fafafa;padding:.4rem;font-family:PingFang-SC-Medium;line-height:.53333rem}.remark-content .txt-num[data-v-4016e532]{position:absolute;bottom:.4rem;right:.4rem}.tag-content[data-v-4016e532]{padding-left:.38667rem;font-size:.4rem;font-family:PingFang-SC-Medium;font-weight:500;color:#989898;line-height:.53333rem}.tag-content .tag-box[data-v-4016e532]{margin-top:.42667rem}.tag-content .tag-box .tag-item[data-v-4016e532]{display:inline-block;font-size:.4rem;color:#323232;font-family:PingFang-SC-Medium;font-weight:500;padding:.18667rem;margin:0 .29333rem .29333rem 0;background:hsla(0,4%,95%,0);border:.01333rem solid #acacac}",""])},575:function(t,e,a){"use strict";a.d(e,"a",function(){return r}),a.d(e,"b",function(){return i});var r=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{staticClass:"remark-page"},[r("div",{staticClass:"remark-title"},[r("div",{staticClass:"back",on:{click:function(e){t.$router.go(-1)}}},[r("img",{attrs:{src:a(208)}})]),t._v(" "),r("div",{staticClass:"user"},[t._v("\n            订单备注\n        ")]),t._v(" "),r("div",{staticClass:"shop",on:{click:t.goToOrder}},[t._v("\n            完成\n        ")])]),t._v(" "),r("div",{staticClass:"remark-content"},[r("textarea",{directives:[{name:"model",rawName:"v-model",value:t.remark_txt,expression:"remark_txt"}],ref:"txtbox",attrs:{name:"",id:"txt-area",maxlength:"50",placeholder:"请输入口味、偏好等要求"},domProps:{value:t.remark_txt},on:{input:[function(e){e.target.composing||(t.remark_txt=e.target.value)},t.showCantInputTip]},nativeOn:{keyup:function(e){return t.deleteInput(e)}}}),t._v(" "),r("div",{staticClass:"txt-num"},[t._v(t._s(t.remark_txt.length)+"/50")])]),t._v(" "),r("div",{staticClass:"tag-content"},[r("span",{staticClass:"tag-title"},[t._v("快捷标签")]),t._v(" "),r("div",{staticClass:"tag-box"},t._l(t.tagArr,function(e,a){return r("span",{key:a,staticClass:"tag-item",on:{click:function(a){t.chooseTag(e)}}},[t._v(t._s(e))])}))])])},i=[]}});