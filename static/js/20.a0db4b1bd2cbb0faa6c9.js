webpackJsonp([20],{"4gfU":function(t,e,a){"use strict";function n(t){a("koBf")}Object.defineProperty(e,"__esModule",{value:!0});var i=a("m/78"),l=a("upr2"),s=a("o7Pn"),o=n,c=s(i.a,l.a,o,"data-v-1ccf6162",null);e.default=c.exports},O3OP:function(t,e,a){"use strict";(function(t){a.d(e,"a",function(){return l});var n=a("EuEE"),i=a("6nXL"),l=(a("2HEv"),{getTableData:function(t,e){n.a.DataEncSubmit("seat_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},changeTableData:function(t,e){n.a.DataEncSubmit("seat_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},shallowRefresh:function(t){router.replace({path:"/refresh",query:{name:t}})},getQrCode:function(t,e){n.a.DataEncSubmit("img_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getId:function(t,e){n.a.DataEncSubmit("gen_id.php",t,function(t){e&&"function"==typeof e&&e(t)})},search:function(t,e,a){var n=[],l=new RegExp(t,"g"),s=new RegExp(e,"g");if(t=e==i.b.code[0])return n=a;if(e==i.b.code[0]!=t){for(var o=0;o<a.length;o++)null!=l.exec(a[o].seat_id)&&n.push(a[o]);return n}if(t==i.b.code[0]!=e){for(var o=0;o<a.length;o++)null!=s.exec(a[o].seat_type)&&n.push(a[o]);return n}for(var o=0;o<a.length;o++)null!=l.exec(a[o].seat_id)&&null!=s.exec(a[o].seat_type)&&n.push(a[o]);return n},getTotal:function(t){for(var e=1,a=0;a<t.length;a++)e++;return this.total=e,this.total},getPageList:function(t,e,a){t.filter(function(t,n){return n<e*a&&n>=e*(a-1)})},getTableEditor:function(t,e){n.a.DataEncSubmit("seat_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},changePage:function(e,a,n){for(var i in e)e[i]=Number(e[i]+1),a==n?t(".el-checkbox__input").eq(e[i]).addClass("is-checked"):t(".el-checkbox__input").eq(e[i]).removeClass("is-checked")},editTagArr:function(t,e){n.a.DataEncSubmit("shopinfo_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getTagArr:function(t,e){n.a.DataEncSubmit("shopinfo_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getBackstageName:function(t){return"SEAT_REGION"==t?t="shop_seat_region":"SEAT_TYPE"==t?t="shop_seat_type":"TABLE_TYPE"==t&&(t="shop_seat_shape"),t}})}).call(e,a("tra3"))},WRSm:function(t,e,a){"use strict"},hewp:function(t,e,a){e=t.exports=a("BkJT")(!0),e.push([t.i,'.left[data-v-1ccf6162]{float:left}.right[data-v-1ccf6162]{float:right}.clearfix[data-v-1ccf6162]:after{content:"";display:block;clear:both}[data-v-1ccf6162]{padding:0;margin:0}.table_list[data-v-1ccf6162]{height:710px;background:#fff;padding:20px 20px 0}.el-select[data-v-1ccf6162]{width:160px;height:34px;margin-right:10px}.search[data-v-1ccf6162]{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between}.btn button[data-v-1ccf6162]{width:100px;height:34px;border-radius:3px;background:#fff}.btn .blue[data-v-1ccf6162]{margin-right:10px;color:#4877e7;border:1px solid #4877e7}.btn .red[data-v-1ccf6162]{margin-right:0;color:#e7487e;border:1px solid #e7487e}.blue[data-v-1ccf6162]{font-size:14px;color:#4877e7}.red[data-v-1ccf6162]{font-size:14px;color:#e7487e}.btng span[data-v-1ccf6162]{width:29px;height:19px;margin-right:20px;-webkit-box-sizing:border-box;box-sizing:border-box}.table_info[data-v-1ccf6162]{padding-top:20px;-webkit-box-sizing:border-box;box-sizing:border-box}.table_info .el-table-column[data-v-1ccf6162]{padding:0}.pagination-container[data-v-1ccf6162]{margin:50px 0 0 689px;font-family:MicrosoftYaHei;font-size:12px;color:#666;letter-spacing:.43px}.pageSize[data-v-1ccf6162]{font-size:12px;width:70px;height:34px;margin-right:10px}.el-table .cell.img[data-v-1ccf6162]{width:60px;height:60px}div.el-select.el-input[data-v-1ccf6162]{font-size:12px}.jump[data-v-1ccf6162]{font-size:12px;margin-right:10px}.jump .el-input[data-v-1ccf6162]{font-size:12px;width:80px;height:34px}.jump .el-button[data-v-1ccf6162]{font-size:12px;width:30px;height:34px;background:#f3f3f3}.messageBox[data-v-1ccf6162]{width:100%;height:100%;position:absolute;top:0;left:0;background:rgba(0,0,0,.5);z-index:9993}.deleteMessage[data-v-1ccf6162]{z-index:9995;background:#fff;width:540px;height:264px;margin:300px 609px}.messageHeader[data-v-1ccf6162]{width:540px;height:40px;background:#5a8cff;color:#fff;font-size:16px;text-align:center;line-height:40px}.tableInfor[data-v-1ccf6162]{margin:69px 130px}.deleteMessage .btn[data-v-1ccf6162]{margin-left:130px}.showQR[data-v-1ccf6162]{background:rgba(0,0,0,.5);position:absolute;top:0;left:0;width:100%;height:100%;z-index:9990}.close_btn[data-v-1ccf6162]{width:100px;height:34px;position:absolute;top:720px;left:950px;z-index:9991}',"",{version:3,sources:["E:/ordering/www/shop/html/src/modules/table/view/index.vue"],names:[],mappings:"AACA,uBACE,UAAY,CACb,AACD,wBACE,WAAa,CACd,AACD,iCACE,WAAY,AACZ,cAAe,AACf,UAAY,CACb,AACD,kBACE,UAAW,AACX,QAAU,CACX,AACD,6BACE,aAAc,AACd,gBAAoB,AACpB,mBAA0B,CAC3B,AACD,4BACE,YAAa,AACb,YAAa,AACb,iBAAmB,CACpB,AACD,yBACE,oBAAqB,AACrB,oBAAqB,AACrB,aAAc,AACd,yBAA0B,AACtB,sBAAuB,AACnB,6BAA+B,CACxC,AACD,6BACE,YAAa,AACb,YAAa,AACb,kBAAmB,AACnB,eAAoB,CACrB,AACD,4BACE,kBAAmB,AACnB,cAAe,AACf,wBAA0B,CAC3B,AACD,2BACE,eAAkB,AAClB,cAAe,AACf,wBAA0B,CAC3B,AACD,uBACE,eAAgB,AAChB,aAAe,CAChB,AACD,sBACE,eAAgB,AAChB,aAAe,CAChB,AACD,4BACE,WAAY,AACZ,YAAa,AACb,kBAAmB,AACnB,8BAA+B,AACvB,qBAAuB,CAChC,AACD,6BACE,iBAAkB,AAClB,8BAA+B,AACvB,qBAAuB,CAChC,AACD,8CACE,SAAW,CACZ,AACD,uCACE,sBAAuB,AACvB,2BAA4B,AAC5B,eAAgB,AAChB,WAAe,AACf,oBAAuB,CACxB,AACD,2BACE,eAAgB,AAChB,WAAY,AACZ,YAAa,AACb,iBAAmB,CACpB,AACD,qCACE,WAAY,AACZ,WAAa,CACd,AACD,wCACE,cAAgB,CACjB,AACD,uBACE,eAAgB,AAChB,iBAAmB,CACpB,AACD,iCACE,eAAgB,AAChB,WAAY,AACZ,WAAa,CACd,AACD,kCACE,eAAgB,AAChB,WAAY,AACZ,YAAa,AACb,kBAAoB,CACrB,AACD,6BACE,WAAY,AACZ,YAAa,AACb,kBAAmB,AACnB,MAAO,AACP,OAAQ,AACR,0BAA+B,AAC/B,YAAc,CACf,AACD,gCACE,aAAc,AACd,gBAAoB,AACpB,YAAa,AACb,aAAc,AACd,kBAAoB,CACrB,AACD,gCACE,YAAa,AACb,YAAa,AACb,mBAAoB,AACpB,WAAa,AACb,eAAgB,AAChB,kBAAmB,AACnB,gBAAkB,CACnB,AACD,6BACE,iBAAmB,CACpB,AACD,qCACE,iBAAmB,CACpB,AACD,yBACE,0BAA+B,AAC/B,kBAAmB,AACnB,MAAO,AACP,OAAQ,AACR,WAAY,AACZ,YAAa,AACb,YAAc,CACf,AACD,4BACE,YAAa,AACb,YAAa,AACb,kBAAmB,AACnB,UAAW,AACX,WAAY,AACZ,YAAc,CACf",file:"index.vue",sourcesContent:["\n.left[data-v-1ccf6162] {\n  float: left;\n}\n.right[data-v-1ccf6162] {\n  float: right;\n}\n.clearfix[data-v-1ccf6162]:after {\n  content: '';\n  display: block;\n  clear: both;\n}\n*[data-v-1ccf6162] {\n  padding: 0;\n  margin: 0;\n}\n.table_list[data-v-1ccf6162] {\n  height: 710px;\n  background: #FFFFFF;\n  padding: 20px 20px 0 20px;\n}\n.el-select[data-v-1ccf6162] {\n  width: 160px;\n  height: 34px;\n  margin-right: 10px;\n}\n.search[data-v-1ccf6162] {\n  display: -webkit-box;\n  display: -ms-flexbox;\n  display: flex;\n  -webkit-box-pack: justify;\n      -ms-flex-pack: justify;\n          justify-content: space-between;\n}\n.btn button[data-v-1ccf6162] {\n  width: 100px;\n  height: 34px;\n  border-radius: 3px;\n  background: #FFFFFF;\n}\n.btn .blue[data-v-1ccf6162] {\n  margin-right: 10px;\n  color: #4877E7;\n  border: 1px solid #4877E7;\n}\n.btn .red[data-v-1ccf6162] {\n  margin-right: 0px;\n  color: #E7487E;\n  border: 1px solid #E7487E;\n}\n.blue[data-v-1ccf6162] {\n  font-size: 14px;\n  color: #4877E7;\n}\n.red[data-v-1ccf6162] {\n  font-size: 14px;\n  color: #E7487E;\n}\n.btng span[data-v-1ccf6162] {\n  width: 29px;\n  height: 19px;\n  margin-right: 20px;\n  -webkit-box-sizing: border-box;\n          box-sizing: border-box;\n}\n.table_info[data-v-1ccf6162] {\n  padding-top: 20px;\n  -webkit-box-sizing: border-box;\n          box-sizing: border-box;\n}\n.table_info .el-table-column[data-v-1ccf6162] {\n  padding: 0;\n}\n.pagination-container[data-v-1ccf6162] {\n  margin: 50px 0 0 689px;\n  font-family: MicrosoftYaHei;\n  font-size: 12px;\n  color: #666666;\n  letter-spacing: 0.43px;\n}\n.pageSize[data-v-1ccf6162] {\n  font-size: 12px;\n  width: 70px;\n  height: 34px;\n  margin-right: 10px;\n}\n.el-table .cell.img[data-v-1ccf6162] {\n  width: 60px;\n  height: 60px;\n}\ndiv.el-select.el-input[data-v-1ccf6162] {\n  font-size: 12px;\n}\n.jump[data-v-1ccf6162] {\n  font-size: 12px;\n  margin-right: 10px;\n}\n.jump .el-input[data-v-1ccf6162] {\n  font-size: 12px;\n  width: 80px;\n  height: 34px;\n}\n.jump .el-button[data-v-1ccf6162] {\n  font-size: 12px;\n  width: 30px;\n  height: 34px;\n  background: #F3F3F3;\n}\n.messageBox[data-v-1ccf6162] {\n  width: 100%;\n  height: 100%;\n  position: absolute;\n  top: 0;\n  left: 0;\n  background: rgba(0, 0, 0, 0.5);\n  z-index: 9993;\n}\n.deleteMessage[data-v-1ccf6162] {\n  z-index: 9995;\n  background: #FFFFFF;\n  width: 540px;\n  height: 264px;\n  margin: 300px 609px;\n}\n.messageHeader[data-v-1ccf6162] {\n  width: 540px;\n  height: 40px;\n  background: #5A8CFF;\n  color: white;\n  font-size: 16px;\n  text-align: center;\n  line-height: 40px;\n}\n.tableInfor[data-v-1ccf6162] {\n  margin: 69px 130px;\n}\n.deleteMessage .btn[data-v-1ccf6162] {\n  margin-left: 130px;\n}\n.showQR[data-v-1ccf6162] {\n  background: rgba(0, 0, 0, 0.5);\n  position: absolute;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  z-index: 9990;\n}\n.close_btn[data-v-1ccf6162] {\n  width: 100px;\n  height: 34px;\n  position: absolute;\n  top: 720px;\n  left: 950px;\n  z-index: 9991;\n}\n"],sourceRoot:""}])},koBf:function(t,e,a){var n=a("hewp");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);a("8bSs")("386ddd15",n,!0)},"m/78":function(t,e,a){"use strict";(function(t){var n=a("3cXf"),i=a.n(n),l=a("O3OP"),s=(a("WRSm"),a("a2vD"));a("swMD");e.a={data:function(){return{downLoad:"",deleted:!1,deletedNo:"",showQr:!1,no:"",table:"",total:10,tableData:[],tableList:[],testList:[],multipleSelection:[],selectIndex:{},listQuery:{page:1,limit:20,sort:"+id"}}},methods:{selectItem:function(t){this.multipleSelection=t},getSelectedIds:function(){var t=[];return this.multipleSelection.forEach(function(e){t.push(e.seat_id)}),t},confirmDelete:function(t){this.deleted=!0,this.deletedNo=t.seat_id},confirm:function(){var t=this;this.deleted=!1,l.a.changeTableData({seat_delete:1,seat_id_list:i()([this.deletedNo])},function(e){setTimeout(function(){l.a.shallowRefresh(t.$route.name)},50)})},selectRow:function(t,e){var a=this,n=[];this.tableList.forEach(function(e,i){for(var l=0;l<a.tableList.length;l++)e==t[l]&&n.push(i)}),this.selectIndex[this.listQuery.page]=n},cancel:function(){this.deleted=!1},deleteDatasInBtnGroup:function(){var t=this;if(!this.multipleSelection.length)return void alert("你还没有勾选任何选项");l.a.changeTableData({seat_delete:1,seat_id_list:i()(this.getSelectedIds())},function(e){setTimeout(function(){l.a.shallowRefresh(t.$route.name)},50)})},getQR:function(e,a){var n=s.a.getShopinfo();this.showQr=!0;l.a.getQrCode({shop_id:n.shopinfo.shop_id,seat_id:a.seat_id},function(e){t(".QR").html('<img src="http://www.ob.com:8080/php/img_get.php?get_seat_qrcode=1&shop_id=4&seat_id=4" class="img"/>'),t(".img").css({position:"absolute",left:"800px",top:"300px",height:"400px",width:"400px"})})},closeQr:function(){this.showQr=!1},handleFilter:function(){var t=this;l.a.getTableData({get_seat_list:1},function(e){t.getTotal(),t.tableList=l.a.search(t.no,t.table,e.data.seatlist).filter(function(e,a){return a<t.listQuery.limit*t.listQuery.page&&a>=t.listQuery.limit*(t.listQuery.page-1)})})},getTotal:function(){for(var t=0,e=0;e<this.testList.length;e++)t++;return this.total=t,this.total},handleSizeChange:function(t){var e=this;this.listQuery.limit=t,localStorage.setItem("itemNum",this.listQuery.limit),this.tableList=this.testList.filter(function(t,a){return a<e.listQuery.limit*e.listQuery.page&&a>=e.listQuery.limit*(e.listQuery.page-1)}),this.getTotal()},handleCurrentChange:function(e){var a=this;this.tableList=this.testList.filter(function(t,e){return e<a.listQuery.limit*a.listQuery.page&&e>=a.listQuery.limit*(a.listQuery.page-1)}),this.listQuery.page=e,this.getTotal();var n=this.selectIndex[e];if(void 0!==n)for(var i in this.selectIndex[e]){var l=Number(this.selectIndex[e][i]+1);t(".el-checkbox__input").eq(l).addClass("is-checked")}},handleDownload:function(){if(!this.multipleSelection.length)return void alert("你还没有勾选任何选项");t(".downLoad").attr("qrcode","download"),this.downLoad="http://www.ob.com:8080/php/img_get.php?batch_export_seat_qrcode=1&seat_list="+i()(this.getSelectedIds())+"&shop_id='4'"}},created:function(){var t=this;null!=localStorage.getItem("itemNum")&&(this.listQuery.limit=Number(localStorage.getItem("itemNum"))),l.a.getTableData({get_seat_list:1},function(e){t.tableData=e.data.seatlist,t.tableList=e.data.seatlist,t.testList=e.data.seatlist,t.getTotal(),t.tableList=t.testList.filter(function(e,a){return a<t.listQuery.limit*t.listQuery.page&&a>=t.listQuery.limit*(t.listQuery.page-1)})})},mounted:function(){}}}).call(e,a("tra3"))},upr2:function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"table_list"},[a("div",{staticClass:"search"},[a("div",{staticClass:"btn"},[a("el-select",{attrs:{"allow-create":"",filterable:"",placeholder:"名称或编号"},model:{value:t.no,callback:function(e){t.no=e},expression:"no"}},[a("el-option",{attrs:{label:"全部",value:"全部"}}),t._v(" "),t._l(t.tableData,function(t){return a("el-option",{key:t.seat_id,attrs:{label:t.seat_id,value:t.seat_id}})})],2),t._v(" "),a("el-select",{attrs:{"allow-create":"",filterable:"",placeholder:"餐桌类型"},model:{value:t.table,callback:function(e){t.table=e},expression:"table"}},[a("el-option",{attrs:{label:"全部",value:"全部"}}),t._v(" "),t._l(t.tableData,function(t){return a("el-option",{key:t.seat_type,attrs:{label:t.seat_type,value:t.seat_type}})})],2),t._v(" "),a("button",{staticClass:"blue search_button",on:{click:t.handleFilter}},[t._v("\n\t\t\t\t搜索\n\t\t\t")])],1),t._v(" "),a("div",{staticClass:"btn"},[a("router-link",{attrs:{to:{path:"/table/menu"}}},[a("button",{staticClass:"blue"},[t._v("\n\t\t\t\t\t订购餐牌\n\t\t\t\t")])]),t._v(" "),a("router-link",{attrs:{to:{path:"/table/edit"}}},[a("button",{staticClass:"blue create_button"},[t._v("\n\t\t\t\t\t新建\n\t\t\t\t")])]),t._v(" "),a("button",{staticClass:"blue export_button",on:{click:t.handleDownload}},[t.multipleSelection.length?t._e():a("span",[t._v("导出二维码")]),t._v(" "),t.multipleSelection.length?a("a",{staticClass:"downLoad",attrs:{href:t.downLoad}},[t._v("导出二维码")]):t._e()]),t._v(" "),a("button",{staticClass:" red delete_button",on:{click:function(e){t.deleteDatasInBtnGroup()}}},[t._v("\n\t\t\t\t删除\n\t\t\t")])],1)]),t._v(" "),a("div",{staticClass:"table_info"},[a("el-table",{ref:"tableList",staticStyle:{width:"100%"},attrs:{data:t.tableList,stripe:"",height:"550"},on:{"selection-change":t.selectItem,select:t.selectRow}},[a("el-table-column",{attrs:{type:"selection",width:"35"}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_id",label:"编号",align:"center",width:"180",sortable:"","default-sort":{prop:"date",order:"descending"}}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_name",label:"餐桌台号",align:"center",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_region",label:"餐桌区域",align:"center",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_type",label:"餐桌类型",align:"center",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_shape",label:"桌型",align:"center",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"seat_size",label:"可供就餐人数",align:"center",width:"180",sortable:"","default-sort":{prop:"date",order:"descending"}}}),t._v(" "),a("el-table-column",{attrs:{prop:"price",label:"餐位费",align:"center",width:"180",sortable:"","default-sort":{prop:"date",order:"descending"}}}),t._v(" "),a("el-table-column",{staticClass:"code",attrs:{label:"二维码",align:"center",width:"180"},inlineTemplate:{render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("span",{staticClass:"blue",attrs:{type:"text",size:"small"},on:{click:function(e){t.getQR(t.$index,t.row)}}},[a("span",[t._v("点击预览")])])},staticRenderFns:[]}}),t._v(" "),a("el-table-column",{attrs:{label:"操作",align:"center",width:"190"},inlineTemplate:{render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"btng"},[a("span",{staticClass:"blue",attrs:{type:"text",size:"small"}},[a("router-link",{attrs:{to:{path:"/table/edit",query:{id:t.row.seat_id}}}},[t._v("\n\t\t\t\t编辑\n\t\t\t\t")])],1),t._v(" "),a("span",{staticClass:"red",attrs:{type:"text",size:"small"},on:{click:function(e){t.confirmDelete(t.row)}}},[t._v("\n\t\t\t\t删除\n\t\t\t")])])},staticRenderFns:[]}})],1),t._v(" "),a("div",{staticClass:"pagination-container"},[a("el-pagination",{attrs:{small:"","current-page":t.listQuery.page,"page-sizes":[20,30,40,50],"page-size":t.listQuery.limit,layout:"sizes, prev, pager, next, jumper",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.listQuery.page=e}}})],1)],1),t._v(" "),t.deleted?a("div",{staticClass:"messageBox"},[a("div",{staticClass:"deleteMessage"},[a("div",{staticClass:"messageHeader"},[t._v("删除提醒")]),t._v(" "),a("p",{staticClass:"tableInfor"},[t._v("确定删除餐桌"),a("span",[t._v(t._s(t.deletedNo)+"号")]),t._v("的信息？")]),t._v(" "),a("div",{staticClass:"btn"},[a("el-button",{on:{click:t.confirm}},[t._v("确定")]),t._v(" "),a("el-button",{on:{click:t.cancel}},[t._v("取消")])],1)])]):t._e(),t._v(" "),t.showQr?a("div",{staticClass:"showQR"},[a("span",{staticClass:"QR"}),t._v(" "),a("button",{staticClass:"red close_btn",on:{click:t.closeQr}},[t._v("关闭")])]):t._e()])},i=[],l={render:n,staticRenderFns:i};e.a=l}});
//# sourceMappingURL=20.a0db4b1bd2cbb0faa6c9.js.map