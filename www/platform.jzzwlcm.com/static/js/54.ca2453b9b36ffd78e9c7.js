webpackJsonp([54],{"5zFf":function(t,e){},ByMM:function(t,e){},NJaj:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=i("hHbu"),s={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{style:{height:t.height},attrs:{id:"positionManagement"}},[a("el-table",{attrs:{data:t.tableData,height:t.tableHeight,stripe:""}},[a("el-table-column",{attrs:{label:"职位",prop:"pl_position_name","min-width":"12.5%",align:"center"}}),t._v(" "),a("el-table-column",{attrs:{label:"权限","min-width":"75%",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return t._l(e.row.arrRight,function(i,s){return a("span",[t._v(t._s(i)+"\n          "),s<e.row.arrRight.length-1?a("span",[t._v(" ， ")]):t._e()])})}}])}),t._v(" "),a("el-table-column",{attrs:{label:"操作","min-width":"12.5%",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return 0!==e.row.is_edit?[a("span",{staticClass:"text-blue",attrs:{size:"small",type:"text"},on:{click:function(i){t.editMethods(e.row)}}},[t._v("编辑\n        ")]),t._v(" "),a("span",{staticClass:"text-red",attrs:{size:"small",type:"text"},on:{click:function(i){t.handleDelete(e.row)}}},[t._v("删除\n        ")])]:void 0}}])})],1),t._v(" "),a("div",{staticClass:"page"},[a("div",{staticClass:"pagination-content change-pagination-default"},[a("el-pagination",{staticClass:"page-el",attrs:{"current-page":t.listQuery.page,"page-sizes":[10,20,40],"page-size":t.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.$set(t.listQuery,"page",e)}}})],1)]),t._v(" "),t.isListEmpty?a("div",{staticClass:"data-empty"},[0==t.searchNone?a("div",{staticClass:"img"},[a("img",{attrs:{src:i("A+eB")}}),t._v(" "),a("div",{staticClass:"text-center"},[t._v("暂无数据...")])]):t._e(),t._v(" "),1==t.searchNone?a("div",{staticClass:"img"},[a("img",{attrs:{src:i("Fd8H")}}),t._v(" "),a("div",{staticClass:"text-center"},[t._v("暂无搜索数据...")])]):t._e()]):t._e(),t._v(" "),t.isOperate?a("operate-dialog",{attrs:{operateText:t.operateText,operateSuccessTxt:t.operateSuccessTxt,isShowRedBtn:t.isShowRedBtn,operateFailTxt:t.operateFailTxt},on:{dialogClose:t.dialogClose,confimOper:t.confimOper}}):t._e()],1)},staticRenderFns:[]};var n=function(t){i("ByMM"),i("5zFf")},l=i("C7Lr")(a.a,s,!1,n,"data-v-e33d0f5a",null);e.default=l.exports},hHbu:function(t,e,i){"use strict";(function(t){var a=i("ihY9"),s=i("HPzi"),n=i("+vDi"),l=i("swMD"),o=i("6nXL"),r=i("CW5y");e.a={data:function(){return{isOperate:!1,isShowRedBtn:!1,tableData:[],testList:[],tableHeight:0,height:0,operateSuccessTxt:"",operateFailTxt:"",operateText:"",total:50,isListEmpty:!1,searchNone:!1,listQuery:{page:1,limit:10}}},created:function(){var e=this;this.tableHeight=document.documentElement.clientHeight-200,this.height=document.documentElement.clientHeight-140+"px",t(window).resize(function(){e.tableHeight=document.documentElement.clientHeight-200,e.height=document.documentElement.clientHeight-140+"px"}),this.listQuery.limit=r.a.get(this.$route),this.initData()},components:{operateDialog:n.a},methods:{initData:function(){var t=this;Object(a.c)({get_pl_position_list:1,page_size:1e5,page_no:1}).then(function(e){if(0===e.ret){var i=e.data.position_list;i&&i.length>0?(t.testList=i,t.testList=Object(s.a)(t.testList),l.a.getItem("off_page_size")?(t.listQuery.limit=l.a.getItem("off_page_size"),t.tableData=Object(s.d)(t.testList,t.listQuery.limit,t.listQuery.page)):t.tableData=Object(s.d)(t.testList,10,t.listQuery.page),t.total=i.length,t.isListEmpty=!1):(t.tableData=[],t.total=0,t.isListEmpty=!0)}else t.$slnotify({message:o.C.toString(e.ret)})})},editMethods:function(t){this.$router.push({path:"/organziteList/editPositionDetail",query:{pathName:"/organzite/list",activIndex:"2",positionId:t.pl_position_id}})},handleDelete:function(t){this.operateData=t,this.operateText="确定删除此条目？",this.isShowRedBtn=!0,this.isOperate=!this.isOperate},dialogClose:function(){this.operateText="",this.operateSuccessTxt="",this.operateFailTxt="",this.isOperate=!1},confimOper:function(){var t=this,e={del_pl_position:1,pl_position_id:this.operateData.pl_position_id};Object(a.d)(e).then(function(e){0===e.ret?(t.operateSuccessTxt="删除成功",t.initData()):-30039===e.ret?t.operateFailTxt="该条目下包含数据，不能被删除":t.$slnotify({message:o.C.toString(e.ret)})})},handleSizeChange:function(t){this.listQuery.limit=t,l.a.setItem("off_page_size",this.listQuery.limit),this.tableData=Object(s.d)(this.testList,this.listQuery.limit,this.listQuery.page),r.a.set(this.$route,t)},handleCurrentChange:function(t){this.listQuery.page=t,this.tableData=Object(s.d)(this.testList,this.listQuery.limit,this.listQuery.page)}}}}).call(e,i("L7Pj"))}});