webpackJsonp([79],{BdVp:function(e,t,a){"use strict";(function(e){var i=a("6nXL"),l=a("P9l9"),n=a("EuEE"),s=a("R/Ky"),r=a("CW5y");t.a={data:function(){return{city_level:"",tableData:[],areaDialog:!1,listQuery:{page:1,limit:10},total:10,cityLevelOption:[{label:"全部",value:0},{label:i.i.toString(i.i.FIRST),value:i.i.FIRST},{label:i.i.toString(i.i.SECEND),value:i.i.SECEND},{label:i.i.toString(i.i.THIRD),value:i.i.THIRD}]}},created:function(){this.getAreaList()},mounted:function(){var t=this.$refs.areaSet.$refs.bodyWrapper;this.$nextTick(function(){n.a.AdjustMinHeight(t)}),e(window).resize(function(){n.a.AdjustMinHeight(t)})},components:{AreaSetDialog:s.default},methods:{getAreaList:function(){var e=this,t={get_city_list:1,page_size:this.listQuery.limit,page_no:this.listQuery.page};this.city_level&&(t.city_level=this.city_level),Object(l.v)(t).then(function(t){0===t.ret?(e.tableData=t.data.city_list,e.listQuery.page>1&&0===e.tableData.length&&(e.listQuery.page--,e.getAreaList()),e.total=t.data.total,e.tableData.forEach(function(e){e.city_level&&(e.city_levelstr=i.i.toString(e.city_level))})):e.$slnotify({message:i.N.toString(t.ret)})})},searchCityLevel:function(){this.listQuery.page=1,this.getAreaList()},openDialog:function(e){this.areaDialog=!0,this.dialogInfo=e},handleClose:function(){this.areaDialog=!1,this.getAreaList()},handleSizeChange:function(e){this.listQuery.page=1,this.listQuery.limit=e,r.a.set(this.$route,e),this.$emit("handleSizeChange",e),this.getAreaList()},handleCurrentChange:function(e){this.listQuery.page=e,this.$emit("handleCurrentChange",e),this.getAreaList()}}}}).call(t,a("L7Pj"))},cK0O:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=a("BdVp"),l={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"area-set"},[a("div",{staticClass:"search-content"},[a("span",{staticClass:"span-label"},[e._v("区域类型")]),e._v(" "),a("el-select",{attrs:{placeholder:"请选择区域类型"},on:{change:e.searchCityLevel},model:{value:e.city_level,callback:function(t){e.city_level=t},expression:"city_level"}},e._l(e.cityLevelOption,function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})}))],1),e._v(" "),a("div",{staticClass:"area-table"},[a("div",{staticClass:"change-default"},[a("el-table",{ref:"areaSet",attrs:{data:e.tableData,border:"",stripe:""}},[a("el-table-column",{attrs:{type:"index",label:"序号",align:"center",width:"50"}}),e._v(" "),a("el-table-column",{attrs:{label:"区域类型","min-width":"40%",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(t.row.city_levelstr))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"城市名称","min-width":"40%",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(t.row.city_name))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"5%",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("div",{staticClass:"detail-btn",on:{click:function(a){e.openDialog(t.row)}}},[e._v("设置")])]}}])})],1)],1)]),e._v(" "),a("div",{staticClass:"page"},[a("div",{staticClass:"pagination-content change-pagination-default"},[a("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":e.listQuery.page,"page-sizes":[10,20,40],"page-size":e.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:e.total},on:{"update:currentPage":function(t){e.$set(e.listQuery,"page",t)},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],1)]),e._v(" "),e.areaDialog?a("area-set-dialog",{attrs:{dialogInfo:e.dialogInfo},on:{handleClose:e.handleClose}}):e._e()],1)},staticRenderFns:[]};var n=function(e){a("wbIa")},s=a("C7Lr")(i.a,l,!1,n,"data-v-3d86a1ce",null);t.default=s.exports},wbIa:function(e,t){}});