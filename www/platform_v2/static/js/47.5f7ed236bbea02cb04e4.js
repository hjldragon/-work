webpackJsonp([47],{"1sqV":function(t,e,s){"use strict";(function(t){var i=s("P9l9"),n=s("a2vD"),a=(s("6nXL"),s("EuEE")),r=s("r2UX"),o=s("6ROu"),l=s.n(o);e.a={components:{EmptyTable:r.a},data:function(){return{sendTime:null,selectCompany:"",companyOption:[],list:[],emptyType:1,multipleSelection:[],total:100,listQuery:{page:1,limit:10},isSort:!1,sortLabel:"",sortOrder:1,pickerOptions:n.c}},computed:{isListEmpty:function(){return 0===this.list.length}},created:function(){this.getList(),this.getCategoryList()},mounted:function(){var e=this.$refs.billFreghtListTableAdjust.$refs.bodyWrapper;this.$nextTick(function(){a.a.AdjustMinHeight(e)}),t(window).resize(function(){a.a.AdjustMinHeight(e)})},methods:{getList:function(){var t=this,e={get_order_list:1,page_size:this.listQuery.limit,page_no:this.listQuery.page,order_status_list:[3,4,5]};if(this.sendTime){var s=this.sendTime[0],n=this.sendTime[1];e.deliver_begin_time=parseInt(l()(s).format("X")),e.deliver_end_time=parseInt(l()(n).format("X"))}this.selectCompany&&(e.express_company_id=this.selectCompany),this.isSort&&(e.sortby=this.sortLabel,e.sort=this.sortOrder),Object(i.K)(e).then(function(e){0===e.ret&&(t.list=e.data.list||[],t.total=e.data.total,t.list=t.list.map(function(t){return t.deliver_time&&(t.deliver_time_str=l()(1e3*t.deliver_time).format("YYYY-MM-DD HH:mm:ss")),t}))})},search:function(){this.emptyType=2,this.listQuery.page=1,this.getList()},sort:function(t){this.sortLabel=t.prop,"descending"===t.order?(this.isSort=!0,this.sortOrder=-1):"ascending"===t.order?(this.isSort=!0,this.sortOrder=1):this.isSort=!1,this.getList()},handleSizeChange:function(t){this.listQuery.limit=t,this.getList()},handleCurrentChange:function(t){this.listQuery.page=t,this.getList()},goOrderInfo:function(t){this.$router.push({path:"/officialmanage/orderdetail",query:{orderid:t}})},getCategoryList:function(){var t=this;Object(i.z)({express_company_list:1}).then(function(e){if(0===e.ret){t.companyOption=e.data.list||[];t.companyOption.unshift({express_company_id:"",express_company_name:"全部"})}})}}}}).call(e,s("L7Pj"))},F1VY:function(t,e){},b2Di:function(t,e){},rFqa:function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=s("1sqV"),n={render:function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"bill-freght"},[s("div",{staticClass:"search-content clearfix"},[s("div",{staticClass:"search-item fl"},[s("span",{staticClass:"label-title"},[t._v("物流公司")]),t._v(" "),s("el-select",{staticClass:"change-input",attrs:{placeholder:"全部"},model:{value:t.selectCompany,callback:function(e){t.selectCompany=e},expression:"selectCompany"}},t._l(t.companyOption,function(t){return s("el-option",{key:t.express_company_id,attrs:{label:t.express_company_name,value:t.express_company_id}})}))],1),t._v(" "),s("div",{staticClass:"search-item fl"},[s("span",{staticClass:"label-title"},[t._v("发货时间")]),t._v(" "),s("el-date-picker",{attrs:{"unlink-panels":"",editable:!1,type:"datetimerange","picker-options":t.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:t.sendTime,callback:function(e){t.sendTime=e},expression:"sendTime"}})],1),t._v(" "),s("div",{staticClass:"sl-btn-bd-search fl",on:{click:t.search}},[t._v("搜索")])]),t._v(" "),s("div",{staticClass:"table-content change-default-table change-default"},[s("el-table",{ref:"billFreghtListTableAdjust",attrs:{data:t.list,stripe:""},on:{"sort-change":t.sort}},[s("el-table-column",{attrs:{prop:"num_no",label:"序号",width:"55",align:"center","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",[t._v(t._s(e.$index+1))])]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"1",label:"订单编号","min-width":"80",align:"center",sortable:"true","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",[t._v(t._s(e.row.goods_order_id))])]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"4",label:"发货时间","min-width":"80",align:"center",sortable:"true","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",[t._v(t._s(e.row.deliver_time_str))])]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"express_company_name",label:"物流公司","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",[t._v(t._s(e.row.express_company_name))])]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"goods_name",label:"物流单号","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",[t._v(t._s(e.row.express_num))])]}}])}),t._v(" "),s("el-table-column",{attrs:{prop:"goods_name",label:"操作","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[s("span",{staticClass:"blue-text",on:{click:function(s){t.goOrderInfo(e.row.goods_order_id)}}},[t._v("查看")])]}}])})],1),t._v(" "),t.isListEmpty?s("empty-table",{attrs:{type:t.emptyType}}):t._e()],1),t._v(" "),s("div",{staticClass:"pagination-contentrr change-pagination-default"},[s("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":t.listQuery.page,"page-sizes":[10,20,40],"page-size":t.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.$set(t.listQuery,"page",e)}}})],1)])},staticRenderFns:[]};var a=function(t){s("b2Di"),s("F1VY")},r=s("C7Lr")(i.a,n,!1,a,"data-v-9bb98120",null);e.default=r.exports}});