webpackJsonp([39],{KXhf:function(t,e,A){"use strict";var n=function(){var t=this,e=t.$createElement,A=t._self._c||e;return A("div",{attrs:{id:"taste-set"}},[t._m(0),t._v(" "),A("div",{staticClass:"content"},[t._l(t.specList,function(e,n){return A("div",{key:n,staticClass:"taste clearfix"},[A("div",{staticClass:"item-title food-left left"},[A("el-checkbox",{model:{value:e.checked,callback:function(t){e.checked=t},expression:"item.checked"}}),t._v(" "),A("span",[t._v(t._s(e.title))])],1),t._v(" "),A("ul",{staticClass:"item-content food-right left"},[t._l(e.list,function(e,n){return A("li",{key:e.index,staticClass:"left"},[t._v(t._s(e.title))])}),t._v(" "),A("li",{staticClass:"editor left"},[t._v("编辑")])],2)])}),t._v(" "),A("div",{staticClass:"add-btn"},[t._v("\n            + 添加属性\n        ")])],2)])},i=[function(){var t=this,e=t.$createElement,A=t._self._c||e;return A("h3",{staticClass:"title"},[A("span",[t._v("口味设置")]),t._v(" "),A("span",{staticClass:"tip"},[t._v("（注：可自定义新属性，只能同时选择两个口味设置属性在前端显示，每个口味前端界面最多显示四个标签）")])])}],d={render:n,staticRenderFns:i};e.a=d},SbQn:function(t,e,A){"use strict";e.a={data:function(){return{specList:[{food_id:"",spec_id:"",spec_sort:"",title:"辣度",type:2,list:[{default:0,id:"505",title:"特辣"},{default:0,id:"506",title:"微辣"},{default:0,id:"506",title:"中辣"}],checked:!0},{food_id:"",spec_id:"",spec_sort:"",title:"盐暑",type:2,list:[{default:0,id:"505",title:"少盐"},{default:0,id:"506",title:"适中"},{default:0,id:"506",title:"多盐"},{default:0,id:"506",title:"解暑"}],checked:!0},{food_id:"",spec_id:"",spec_sort:"",title:"口味",type:2,list:[{default:0,id:"505",title:"酸"},{default:0,id:"506",title:"甜"},{default:0,id:"506",title:"苦"}],checked:!1}]}}}},ZF7S:function(t,e,A){e=t.exports=A("BkJT")(!0),e.push([t.i,'.left[data-v-1d099d08]{float:left}.right[data-v-1d099d08]{float:right}.clearfix[data-v-1d099d08]:after{content:"";display:block;clear:both}#taste-set .title[data-v-1d099d08]{font-size:14px;color:#666;background-color:#f6f8fc;height:40px;line-height:40px;padding-left:14px}#taste-set .title .tip[data-v-1d099d08]{font-size:12px;color:#9b9b9b}#taste-set .content[data-v-1d099d08]{font-size:14px;color:#000;background-color:#fff;padding:40px 0}.taste[data-v-1d099d08]{height:30px;line-height:30px;margin-bottom:10px}.taste .item-content li[data-v-1d099d08]{width:80px;height:30px;background:#d8d8d8;border-radius:2px;margin-right:10px;line-height:30px;text-align:center}.taste .item-content li.editor[data-v-1d099d08]{width:60px;height:30px;border:1px solid #4877e7;border-radius:2px;color:#4877e7;background-color:#fff;cursor:pointer}.add-btn[data-v-1d099d08]{font-size:14px;color:#4877e7;width:150px;height:34px;border:1px solid #4877e7;text-align:center;line-height:34px;border-radius:2px;cursor:pointer;margin-left:234px}.food-left[data-v-1d099d08]{width:220px;text-align:right;margin-right:14px}.food-left span[data-v-1d099d08]{opacity:.5708}.food-right[data-v-1d099d08]{font-size:14px;color:#666;min-width:1020px}',"",{version:3,sources:["E:/ordering/www/shop/html/src/modules/merchandise/view/foodEditor/tasteinfoSet.vue"],names:[],mappings:"AACA,uBACE,UAAY,CACb,AACD,wBACE,WAAa,CACd,AACD,iCACE,WAAY,AACZ,cAAe,AACf,UAAY,CACb,AACD,mCACE,eAAgB,AAChB,WAAe,AACf,yBAA0B,AAC1B,YAAa,AACb,iBAAkB,AAClB,iBAAmB,CACpB,AACD,wCACI,eAAgB,AAChB,aAAe,CAClB,AACD,qCACE,eAAgB,AAChB,WAAY,AACZ,sBAAuB,AACvB,cAAgB,CACjB,AACD,wBACE,YAAa,AACb,iBAAkB,AAClB,kBAAoB,CACrB,AACD,yCACI,WAAY,AACZ,YAAa,AACb,mBAAoB,AACpB,kBAAmB,AACnB,kBAAmB,AACnB,iBAAkB,AAClB,iBAAmB,CACtB,AACD,gDACI,WAAY,AACZ,YAAa,AACb,yBAA0B,AAC1B,kBAAmB,AACnB,cAAe,AACf,sBAAuB,AACvB,cAAgB,CACnB,AACD,0BACE,eAAgB,AAChB,cAAe,AACf,YAAa,AACb,YAAa,AACb,yBAA0B,AAC1B,kBAAmB,AACnB,iBAAkB,AAClB,kBAAmB,AACnB,eAAgB,AAChB,iBAAmB,CACpB,AACD,4BACE,YAAa,AACb,iBAAkB,AAClB,iBAAmB,CACpB,AACD,iCACI,aAAgB,CACnB,AACD,6BACE,eAAgB,AAChB,WAAY,AACZ,gBAAkB,CACnB",file:"tasteinfoSet.vue",sourcesContent:["\n.left[data-v-1d099d08] {\n  float: left;\n}\n.right[data-v-1d099d08] {\n  float: right;\n}\n.clearfix[data-v-1d099d08]:after {\n  content: '';\n  display: block;\n  clear: both;\n}\n#taste-set .title[data-v-1d099d08] {\n  font-size: 14px;\n  color: #666666;\n  background-color: #F6F8FC;\n  height: 40px;\n  line-height: 40px;\n  padding-left: 14px;\n}\n#taste-set .title .tip[data-v-1d099d08] {\n    font-size: 12px;\n    color: #9B9B9B;\n}\n#taste-set .content[data-v-1d099d08] {\n  font-size: 14px;\n  color: #000;\n  background-color: #fff;\n  padding: 40px 0;\n}\n.taste[data-v-1d099d08] {\n  height: 30px;\n  line-height: 30px;\n  margin-bottom: 10px;\n}\n.taste .item-content li[data-v-1d099d08] {\n    width: 80px;\n    height: 30px;\n    background: #D8D8D8;\n    border-radius: 2px;\n    margin-right: 10px;\n    line-height: 30px;\n    text-align: center;\n}\n.taste .item-content li.editor[data-v-1d099d08] {\n    width: 60px;\n    height: 30px;\n    border: 1px solid #4877E7;\n    border-radius: 2px;\n    color: #4877E7;\n    background-color: #fff;\n    cursor: pointer;\n}\n.add-btn[data-v-1d099d08] {\n  font-size: 14px;\n  color: #4877E7;\n  width: 150px;\n  height: 34px;\n  border: 1px solid #4877E7;\n  text-align: center;\n  line-height: 34px;\n  border-radius: 2px;\n  cursor: pointer;\n  margin-left: 234px;\n}\n.food-left[data-v-1d099d08] {\n  width: 220px;\n  text-align: right;\n  margin-right: 14px;\n}\n.food-left span[data-v-1d099d08] {\n    opacity: 0.5708;\n}\n.food-right[data-v-1d099d08] {\n  font-size: 14px;\n  color: #666;\n  min-width: 1020px;\n}\n"],sourceRoot:""}])},"p/1n":function(t,e,A){var n=A("ZF7S");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);A("8bSs")("7d838eed",n,!0)},t5IW:function(t,e,A){"use strict";function n(t){A("p/1n")}Object.defineProperty(e,"__esModule",{value:!0});var i=A("SbQn"),d=A("KXhf"),a=A("o7Pn"),o=n,l=a(i.a,d.a,o,"data-v-1d099d08",null);e.default=l.exports}});
//# sourceMappingURL=39.e2ac46e25ef0cd927ffd.js.map