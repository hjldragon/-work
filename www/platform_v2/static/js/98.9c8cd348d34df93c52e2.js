webpackJsonp([98],{"5e1O":function(t,e){},xecK:function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=s("IHPB"),c=s.n(i),a=s("4YfN"),o=s.n(a),l=s("9rMa"),n={components:{TagEditor:s("y/5j").default},data:function(){return{type:1,specList:[],specDialog:!1,sepcObj:{name:"编辑规格标签",value:"spec",limitNum:30,limitWord:5,limitSelect:5}}},computed:o()({},Object(l.d)({good_spec_list:function(t){return t.shopmall_goods.good_spec_list},good_package_list:function(t){return t.shopmall_goods.good_package_list}})),methods:o()({},Object(l.c)(["SMG_SET_GOODSPCE","SMG_SET_GOODPACKAGE"]),{openSpecDialog:function(){this.type=1,this.specList=[].concat(c()(this.good_spec_list)),this.specDialog=!0},openPackDialog:function(){this.type=2,this.specList=[].concat(c()(this.good_package_list)),this.specDialog=!0},hideSpecDialog:function(){this.specDialog=!1},getSpec:function(t){this.specList=[],1===this.type?this.SMG_SET_GOODSPCE({good_spec_list:[].concat(c()(t))}):2===this.type&&this.SMG_SET_GOODPACKAGE({good_package_list:[].concat(c()(t))}),this.type=0}})},_={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"spec-content"},[s("h3",{staticClass:"title change-titlerr"},[t._v("商品规格")]),t._v(" "),s("div",{staticClass:"content"},[s("div",{staticClass:"edit-item clearfix good-spec"},[t._m(0),t._v(" "),s("div",{staticClass:"edit-content fl clearfix"},[t._l(t.good_spec_list,function(e,i){return s("div",{key:i,staticClass:"btn-item fl"},[t._v("\n                    "+t._s(e)+"\n                ")])}),t._v(" "),s("div",{staticClass:"sl-btn-bd-s-blue",on:{click:t.openSpecDialog}},[t._v("编辑")])],2)]),t._v(" "),s("div",{staticClass:"edit-item clearfix good-package"},[t._m(1),t._v(" "),s("div",{staticClass:"edit-content fl clearfix"},t._l(t.good_package_list,function(e,i){return s("div",{key:i,staticClass:"btn-item fl"},[t._v("\n                    "+t._s(e)+"\n                ")])})),t._v(" "),s("div",{staticClass:"sl-btn-bd-s-blue",on:{click:t.openPackDialog}},[t._v("编辑")])])]),t._v(" "),s("tag-editor",{attrs:{dialogVisible:t.specDialog,selectArr:t.specList,tagObj:t.sepcObj},on:{"on-close":t.hideSpecDialog,"on-change":t.getSpec}})],1)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"edit-label fl"},[e("span",{staticClass:"sl-must"},[this._v("规格")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"edit-label fl"},[e("span",[this._v("套餐")])])}]};var d=s("C7Lr")(n,_,!1,function(t){s("5e1O")},"data-v-6bcabb7d",null);e.default=d.exports}});