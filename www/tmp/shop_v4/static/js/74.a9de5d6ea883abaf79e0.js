webpackJsonp([74,97],{"3/ol":function(t,e,n){"use strict";var i=n("Yp/U"),a=n("6nXL");e.a={props:["isShowDialog","allDepartName"],data:function(){return{dialongInputValue:"",isDialogVisible:!1,departNameArr:[]}},watch:{isShowDialog:function(t){!0===t&&(this.isDialogVisible=!0)}},created:function(){this.departNameArr=this.allDepartName},methods:{addConfirm:function(){var t=this;if(""===this.dialongInputValue)return void this.$slnotify({message:"部门名称不能为空"});if(this.dialongInputValue.length>6)return void this.$slnotify({message:"部门名称不超过6个字符"});if(-1!==this.departNameArr.indexOf(this.dialongInputValue))return void this.$slnotify({message:"不能添加已经存在的部门名称"});var e={department_save:1,department_name:this.dialongInputValue};i.a.modifyDepartInfo(e,function(e){if(0===e.ret){var n={};n.name=t.dialongInputValue,n.id=e.data.department_id,t.$emit("on-close",n),t.dialongInputValue="",t.isDialogVisible=!1}else-20047===e.ret?t.$slnotify({message:"不能添加已经存在的部门名称"}):t.$slnotify({message:a.K.toString(e.ret)})})},addCancel:function(){this.dialongInputValue="",this.handleClose()},handleClose:function(){this.dialongInputValue="",this.$emit("on-close"),this.isDialogVisible=!1}}}},"5g5b":function(t,e,n){"use strict";n.d(e,"b",function(){return d}),n.d(e,"a",function(){return c});var i=n("3cXf"),a=n.n(i),o=n("6nXL"),s=n("+VlJ"),r=n("a2vD"),l=n("EuEE"),u=n("rUdh"),d={initTreeData:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];return e=e.map(function(e){return s.default.set(e,"canEditor",!1),s.default.set(e,"addIcon",!0),s.default.set(e,"editorIcon",!0),s.default.set(e,"deleteIcon",!0),s.default.set(e,"isShowBtn",!1),s.default.set(e,"isActive",!1),s.default.set(e,"breakshow",!1),s.default.set(e,"allShow",!1),s.default.set(e,"isExpand",!0),s.default.set(e,"isFolder",!1),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.initTreeData(e.employee_list),e})},addTitleToTree:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e={};return s.default.set(e,"department_name","部门管理"),s.default.set(e,"employee_list",t),s.default.set(e,"isThree",!0),s.default.set(e,"isActive",!0),s.default.set(e,"department_id","0"),e},generateKey:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],n=arguments[1];return e=e.map(function(e,i){return e.key=n+"-"+i.toString(),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.generateKey(e.employee_list,e.key),e})},getKeylength:function(t){if(!l.a.isEmpty(t)){return t.split("-").length}},selectIcon:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];e=e.map(function(e){var n=t.getKeylength(e.key);2===n?(e.addIcon=!0,e.editorIcon=!1,e.deleteIcon=!1,e.isExpand=!0,e.isActive=!0):3===n?(e.addIcon=!1,e.editorIcon=!0,e.deleteIcon=!0,e.isExpand=!1,e.isFolder=!0):4===n&&(e.addIcon=!1,e.editorIcon=!1,e.deleteIcon=!1),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.selectIcon(e.employee_list)})},saveDepartName:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=[];return function t(n){n.forEach(function(n){n.hasOwnProperty("department_name")&&"部门管理"!==n.department_name&&e.push(n.department_name),n.hasOwnProperty("employee_list")&&n.employee_list.length>0&&t(n.employee_list)})}(t),e},treeDataById:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e={};return function t(n){n.forEach(function(n){n.hasOwnProperty("department_id")?e[n.department_id]=n:n.hasOwnProperty("employee_id")&&(e[n.employee_id]=n),n.hasOwnProperty("employee_list")&&n.employee_list.length>0&&t(n.employee_list)})}(t),e},changeSelecte:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};for(var n in e)e.hasOwnProperty(n)&&(e[n].isActive=t===n);return e},getDutyArr:function(t){var e=[];return t.forEach(function(t){-1===e.indexOf(t.position_name)&&e.push(t.position_name)}),e},getDutyDepartId:function(t,e){var n;return t.forEach(function(t){void 0!==t.position_name?t.position_name===e&&(n=t.position_id):t.department_name===e&&(n=t.department_id)}),n},search:function(t,e,n,i){var a=[],s=new RegExp(t,"g"),r=new RegExp(e,"g");return i.forEach(function(l){t&&t!==o.I.code[0]||""!==e||""!==n?null!==s.exec(l.position_name)&&""===e&&""===n?a.push(l):null!==s.exec(l.position_name)&&null!==r.exec(l.real_name)&&""===n?a.push(l):null!==s.exec(l.position_name)&&null!==r.exec(l.real_name)&&l.phone.indexOf(n)>-1?a.push(l):null!==s.exec(l.position_name)&&l.phone.indexOf(n)>-1&&""===e?a.push(l):(!t||t===o.I.code[0])&&null!==r.exec(l.real_name)&&l.phone.indexOf(n)>-1?a.push(l):(!t||t===o.I.code[0])&&""===e&&l.phone.indexOf(n)>-1?a.push(l):t&&t!==o.I.code[0]||null===r.exec(l.real_name)||""!==n||a.push(l):a=i}),a},getTotal:function(t){if(void 0!==t&&t.length>0){for(var e=0,n=0;n<t.length;n++)e++;return e}},getPageList:function(t,e,n){if(void 0!==t&&t.length>0){return t.filter(function(t,i){return i<e*n&&i>=e*(n-1)})}},isRight:function(t){return t.forEach(function(t){var e,n=[],i=[];for(var a in o.V)void 0!==t.position_permission?(e=t.position_permission&o.V[a],0!==e&&(i.push(o.V.code[e]),n.push(e)),t.arrId=n,t.arrRight=i,t.arrId[0]===o.V.ALLBACKSTAGE&&t.arrId[1]===o.V.ALLWEB?t.arrRight=["全部"]:t.arrId[0]===o.V.ALLBACKSTAGE&&t.arrId[1]===o.V.ALLWEB&&t.arrId.length>2?t.arrRight=["全部"]:t.arrId[0]===o.V.ALLWEB&&t.arrId.length>1&&(t.arrRight=["点餐收银全部权限"]),t.arrRight.forEach(function(t){t===o.V.code[o.V.ALLWEB]&&(t="")})):(t.arrId=0,t.arrRight=o.I.code[0])}),t},showALLPosition:function(t){if(isNaN(t)){var e=!0;for(var n in t)void 0!==t[n]&&0===t[n]&&(e=!1);return e}},positionById:function(t){var e=JSON.parse(a()(t)),n={};for(var i in e){var o=e[i].list;o&&o.length>0&&o.forEach(function(t){n[t.id]=t})}return n},showEveryPosition:function(t){var e=this.positionById(u.a);if(isNaN(t)){var n=[];for(var i in t)void 0!==t[i]&&1===t[i]&&n.push(e[i].name);return n}},judgeStaffList:function(t){var e=[];for(var n in o.V)t.forEach(function(t){t===o.V.code[o.V[n]]&&e.push(o.V[n])});return e}},c={getTotal:function(t){if(void 0!==t&&t.length>0){for(var e=0,n=0;n<t.length;n++)e++;return e}},getPageList:function(t,e,n){var i=[];return void 0!==t&&t.length>0?i=t.filter(function(t,i){return i<e*n&&i>=e*(n-1)}):i},canSeeLogin:function(t,e){var n=r.a.getShopinfo();return n.shopinfo&&n.shopinfo.length>0&&n.shopinfo.forEach(function(n){e===n.shop_id&&n.employee_is_admin===o.O.YES&&(t=!0)}),t}}},"96j5":function(t,e,n){var i=n("F0tS");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);n("FIqI")("3a506bea",i,!0,{})},"9cF1":function(t,e,n){"use strict";function i(t){n("uQUf"),n("p4Ff")}Object.defineProperty(e,"__esModule",{value:!0});var a=n("VDbd"),o=n("LB+E"),s=n("QAAC"),r=i,l=Object(s.a)(a.a,o.a,o.b,!1,r,"data-v-e9415534",null);e.default=l.exports},F0tS:function(t,e,n){e=t.exports=n("UTlt")(!1),e.push([t.i,"#addNewDepartment .dialog-title[data-v-cb160080]{width:540px;height:40px;font-size:16px;color:#fff;background-color:#5a8cff;text-align:center;line-height:40px}#addNewDepartment .dialog-content[data-v-cb160080]{width:540px;height:264px}#addNewDepartment .category .content-left[data-v-cb160080]{width:80px;height:34px;line-height:34px;margin-left:60px}#addNewDepartment .category .content-right[data-v-cb160080]{width:320px;height:34px}#addNewDepartment .button-group[data-v-cb160080]{text-align:center;margin-top:60px}#addNewDepartment .button-group div[data-v-cb160080]{width:160px;height:40px;border-radius:4px;line-height:40px;text-align:center;border:1px solid #5a8cff;color:#5a8cff;cursor:pointer;display:inline-block}#addNewDepartment .button-group .ok-btn[data-v-cb160080]{background-color:#5a8cff;color:#fff;cursor:pointer;margin-right:60px}",""])},GlEb:function(t,e,n){e=t.exports=n("UTlt")(!1),e.push([t.i,"#editStaffInfor .el-button{width:80px;height:30px;border:1px solid #4877e7;color:#4877e7;border-radius:5px;font-size:12px;cursor:pointer}#editStaffInfor .el-button.disabledBtn{width:160px;height:40px;font-size:14px;border:1px solid #d8d8d8;cursor:pointer;color:#fff;margin:0 20px 0 0;background:#d8d8d8}#editStaffInfor .el-input{height:34px}#editStaffInfor .el-button.saveBtn{width:160px;height:40px;font-size:14px;color:#fff;margin:0 20px 0 30%;border:none;background:#4877e7}#editStaffInfor .cancelBtn.el-button{width:160px;height:40px;font-size:14px;color:#4877e7;margin:0 20px 0 0}#editStaffInfor .el-form-item__label{opacity:.57;font-size:14px;color:#000}#editStaffInfor .el-form-item__content{font-size:14px;color:#333}",""])},"LB+E":function(t,e,n){"use strict";n.d(e,"a",function(){return i}),n.d(e,"b",function(){return a});var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"editStaffInfor"}},[n("div",{staticClass:"page_title"},[n("router-link",{attrs:{to:{path:"/staffmenu/staff",query:{activeIndex:"1",departId:t.$route.query.departId}}}},[n("span",[t._v("员工管理")])]),t._v(" "),n("span",{staticClass:"blue"},[t._v(">")]),t._v(" "),n("span",{staticClass:"blue"},[t._v("编辑员工信息")])],1),t._v(" "),n("div",{ref:"staform",staticClass:"staffCard"},[n("div",{staticClass:"form_title"},[t._v("编辑员工信息")]),t._v(" "),n("div",{staticClass:"from_card"},[n("el-form",{ref:"form",attrs:{model:t.form1,"label-width":"100px",rules:t.rules}},[n("el-form-item",{attrs:{label:"工号"}},[n("span",[t._v(t._s(t.form1.employee_id))])]),t._v(" "),n("el-form-item",{attrs:{label:"店铺名称"}},[n("span",[t._v(t._s(t.form.shop_name))])]),t._v(" "),n("el-form-item",{attrs:{label:"职位",prop:"position_name"}},[n("el-select",{attrs:{placeholder:"职位"},on:{change:function(e){t.changeIsDisable("form")}},model:{value:t.form1.position_name,callback:function(e){t.$set(t.form1,"position_name",e)},expression:"form1.position_name"}},t._l(t.allDutyName,function(t){return n("el-option",{key:t,attrs:{label:t.allDutyName,value:t}})}),1),t._v(" "),n("el-button",{staticClass:"add-btn",on:{click:t.addDuty}},[t._v("新增职位")])],1),t._v(" "),n("el-form-item",{attrs:{label:"部门",prop:"department_name"},on:{change:function(e){t.changeIsDisable("form")}}},[n("el-select",{attrs:{placeholder:"部门"},model:{value:t.form1.department_name,callback:function(e){t.$set(t.form1,"department_name",e)},expression:"form1.department_name"}},t._l(t.allDepartName,function(t){return n("el-option",{key:t,attrs:{label:t,value:t}})}),1),t._v(" "),n("el-button",{staticClass:"add-btn",on:{click:t.addNewDepartment}},[t._v("新增部门")])],1),t._v(" "),n("el-form-item",{attrs:{label:"手机号"}},[n("span",[t._v(t._s(t.form2.phone))])]),t._v(" "),n("el-form-item",{attrs:{label:"姓名"}},[n("span",[t._v(t._s(t.form2.real_name))])]),t._v(" "),n("el-form-item",{attrs:{label:"身份证"}},[t.form2.identity?n("span",[t._v(t._s(t.form2.identity))]):n("span",[t._v("未填写")])]),t._v(" "),n("el-form-item",{attrs:{label:"性别"}},[t.form2.sex?n("span",[t._v(t._s(t.staffSex))]):n("span",[t._v("保密")])]),t._v(" "),n("el-form-item",{attrs:{label:"健康证扫描件"}},[t._l(t.identyImg,function(e){return t.identyImg&&null!==t.identyImg&&t.identyImg.length>0?n("img",{staticClass:"img",attrs:{src:t.imgbase_url+"/img_get.php?img=1&height=100&width=100&imgname="+e}}):t._e()}),t._v(" "),n("span",{directives:[{name:"show",rawName:"v-show",value:0===t.identyImg.length,expression:"identyImg.length===0"}]},[t._v("未上传")])],2),t._v(" "),n("el-form-item",{attrs:{label:"绑定微信"}},[1===t.form2.is_weixin?n("span",[t._v("绑定")]):0==t.form2.is_weixin?n("span",[t._v("未绑定")]):n("span",[t._v("--")])])],1)],1),t._v(" "),n("div",{staticClass:"btnGroup"},[0==t.isDisabled?n("el-button",{staticClass:"saveBtn",on:{click:function(e){t.saveEditStaff("form")}}},[t._v("保存")]):t._e(),t._v(" "),1==t.isDisabled?n("el-button",{staticClass:"disabledBtn"},[t._v("保存")]):t._e(),t._v(" "),n("el-button",{staticClass:"cancelBtn",on:{click:function(e){t.cancelEditStaff("form")}}},[t._v("取消")])],1)]),t._v(" "),n("new-department",{attrs:{isShowDialog:t.isAddNewDepartment,allDepartName:t.allDepartName},on:{"on-close":t.hideDialog}})],1)},a=[]},VDbd:function(t,e,n){"use strict";(function(t){var i=n("4YfN"),a=n.n(i),o=n("Yp/U"),s=n("5g5b"),r=n("a2vD"),l=n("6nXL"),u=n("a3/h"),d=n("EuEE"),c=n("9rMa");e.a={data:function(){return{imgbase_url:"./php",isShowLoginNum:!1,dutyForm:[],departForm:[],allDepartName:[],allDutyName:[],form1:{},form2:{},isBind:0,identyImg:[],identyImgOne:"",isDisabled:!1,shopId:"",addedDuty:"",form:{},isAddNewDepartment:!1,rules:{position_name:[{required:!0,message:"请选择职位",trigger:"change"}],department_name:[{required:!0,message:"请选择部门",trigger:"change"}]},usedObj:{},loginAutho:0,loginArr:[],loginObj:{},loginData:[{name:"掌柜通",check:!1,totalNum:2,nowUse:1},{name:"商家运营平台",check:!1,totalNum:2,nowUse:1},{name:"智能收银机",check:!1,totalNum:2,nowUse:1},{name:"平板智能点餐机",check:!1,totalNum:2,nowUse:1},{name:"自助点餐机",check:!1,totalNum:2,nowUse:1}]}},components:{newDepartment:u.default},created:function(){this.shopId=r.a.getShopid(),this.initData(),this.getAllDepartName(),this.getAllDutyName(),this.isShowLoginNum=s.a.canSeeLogin(this.isShowLoginNum,this.shopId)},watch:{showBanner:function(){var t=this;this.$nextTick(function(){var e=t.$refs.staform,n=e.style.height;d.a.AdjustHeight(e);var i=e.style.height;e.style.height=n,setTimeout(function(){e.style.transition="height 0.28s ease-out"},50),setTimeout(function(){e.style.height=i},50)})}},computed:a()({showBanner:function(){return this.$store.state.layout.showBanner},staffSex:function(){return l.c.toString(this.form2.sex)}},Object(c.c)({ACS:function(t){return t.permission.sysPermis},SHOP_K:function(t){return t.permission.SHOP_K}})),mounted:function(){this.adjustHeight()},methods:{adjustHeight:function(){var t=this;this.$nextTick(function(){var e=t.$refs.staform;d.a.AdjustHeight(e)})},initData:function(){var t=this;this.employee_id=this.$route.query.employeeId;var e={get_employee_info:1,shop_id:this.shopId,employee_id:this.employee_id};o.a.getStaffList(e,function(e){t.loginArr=e.data.employee_info.authorize,t.form=e.data,e.data.userinfo.health_certificate&&e.data.userinfo.health_certificate.constructor===Array?t.identyImg=e.data.userinfo.health_certificate:e.data.userinfo.health_certificate&&"string"==typeof e.data.userinfo.health_certificate&&(t.identyImg[0]=e.data.userinfo.health_certificate),t.form1=e.data.employee_info,t.form2=e.data.userinfo})},saveEditStaff:function(t){var e=this;this.$refs[t].validate(function(t){t&&(e.loginObj.used_pc_num||e.loginObj.used_pad_num||e.loginObj.used_cashier_num||e.loginObj.used_app_num||e.loginObj.used_machine_num||(e.loginAutho=0),e.loginObj.save_authorize=1,o.a.saveLginInfo(e.loginObj,e.saveStaffEdit))})},saveStaffEdit:function(){var t=s.b.getDutyDepartId(this.dutyForm,this.form1.position_name),e=s.b.getDutyDepartId(this.departForm,this.form1.department_name),n={save_employee_info:1,employee_id:this.form1.employee_id,department_id:e,position_id:t};o.a.getUserInfor(n,this.goBack)},addNewDepartment:function(){if(!this.ACS[this.SHOP_K.ADD_DEPARTMENT])return this.$slnotify({message:"操作权限不足"});this.isAddNewDepartment=!0},addDuty:function(){if(!this.ACS[this.SHOP_K.ADD_POSITION])return this.$slnotify({message:"操作权限不足"});sessionStorage.setItem("lastDuty",this.allDutyName[this.allDutyName.length-1]),this.$router.push({path:"/staff/editDuty",query:{all:this.allDutyName,pathName:"/staff/edit",employeeId:this.$route.query.employeeId,departId:this.$route.query.departId}})},getAllDepartName:function(){var t=this,e={get_department_list:1};o.a.getDepartList(e,function(e){0===e.ret&&(t.departForm=e.data.department_list,e.data.department_list.forEach(function(e){-1===t.allDepartName.indexOf(e.department_name)&&t.allDepartName.push(e.department_name)}))})},getAllDutyName:function(){var t=this,e={get_position_list:1,is_start:1};o.a.getDutyList(e,function(e){t.dutyForm=e.data.position_list,e.data.position_list.forEach(function(e){-1===t.allDutyName.indexOf(e.position_name)&&t.allDutyName.push(e.position_name)}),t.addedDuty=t.allDutyName[t.allDutyName.length-1];var n=sessionStorage.getItem("lastDuty");null!==n&&n!==t.addedDuty&&(t.form1.position_name=t.addedDuty)})},changeIsDisable:function(t){this.isCanClick(t)},isCanClick:function(t){var e=this;this.$refs[t].validate(function(t){t&&(e.isDisabled=!1)})},getLoginData:function(){var e=this,n={get_shop_authorize:1};o.a.getLoginInfo(n,function(n){0===n.ret&&(e.loginData[1].totalNum=null!==n.data.info.pc_num?n.data.info.pc_num:"--",e.loginData[1].nowUse=null!==n.data.info.used_pc_num?n.data.info.used_pc_num:"--",e.loginData[3].totalNum=null!==n.data.info.pad_num?n.data.info.pad_num:"--",e.loginData[3].nowUse=null!==n.data.info.used_pad_num?n.data.info.used_pad_num:"--",e.loginData[4].totalNum=null!==n.data.info.machine_num?n.data.info.machine_num:"--",e.loginData[4].nowUse=null!==n.data.info.used_machine_num?n.data.info.used_machine_num:"--",e.loginData[2].totalNum=null!==n.data.info.cashier_num?n.data.info.cashier_num:"--",e.loginData[2].nowUse=null!==n.data.info.used_cashier_num?n.data.info.used_cashier_num:"--",e.loginData[0].totalNum=null!==n.data.info.app_num?n.data.info.app_num:"--",e.loginData[0].nowUse=null!==n.data.info.used_app_num?n.data.info.used_app_num:"--",e.usedObj.used_pc_num=n.data.info.used_pc_num||"",e.usedObj.used_pad_num=n.data.info.used_pad_num||"",e.usedObj.used_cashier_num=n.data.info.used_cashier_num||"",e.usedObj.used_app_num=n.data.info.used_app_num||"",e.usedObj.used_machine_num=n.data.info.used_machine_num||"",e.loginObj.used_pc_num=n.data.info.used_pc_num||"",e.loginObj.used_pad_num=n.data.info.used_pad_num||"",e.loginObj.used_machine_num=n.data.info.used_machine_num||"",e.loginObj.used_cashier_num=n.data.info.used_cashier_num||"",e.loginObj.used_app_num=n.data.info.used_app_num||"",e.loginAutho=0,e.loginArr&&e.loginArr.length>0?(-1!==e.loginArr.indexOf(l.m.PC)&&(e.loginData[1].check=!0,t(".chckinput").eq(1).check=!0,e.loginAutho=Number(e.loginAutho+1)),-1!==e.loginArr.indexOf(l.m.PAD)&&(e.loginData[3].check=!0,t(".chckinput").eq(3).check=!0,e.loginAutho=Number(e.loginAutho+2)),-1!==e.loginArr.indexOf(l.m.CASHREGISTER)&&(e.loginData[2].check=!0,t(".chckinput").eq(2).check=!0,e.loginAutho=Number(e.loginAutho+4)),-1!==e.loginArr.indexOf(l.m.APP)&&(e.loginData[0].check=!0,t(".chckinput").eq(0).check=!0,e.loginAutho=Number(e.loginAutho+8)),-1!==e.loginArr.indexOf(l.m.MACHAIN)&&(e.loginData[4].check=!0,t(".chckinput").eq(4).check=!0,e.loginAutho=Number(e.loginAutho+16))):e.loginAutho=0)})},handelChangeSwitch:function(t,e){isNaN(t.nowUse)||isNaN(t.totalNum)||t.nowUse===t.totalNum?(!1===e.target.checked&&(t.nowUse=Number(t.nowUse-1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-16))),e.target.checked=!1,t.check=!1):!1===t.check?t.nowUse<t.totalNum?(t.check=!0,e.target.checked=!0,t.nowUse=Number(t.nowUse+1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+16))):e.target.checked=!1:(t.check=!1,t.nowUse>0?(e.target.checked=!1,t.nowUse=Number(t.nowUse-1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-16))):t.nowUse=0)},cancelEditStaff:function(t){this.$refs[t].resetFields(),sessionStorage.removeItem("lastDuty"),this.goBack()},hideDialog:function(t){t&&(this.form1.department_name=t.name,this.form1.department_id=t.id),t.name?this.allDepartName.push(t.name):this.allDepartName.push(t),this.getAllDepartName(),this.isAddNewDepartment=!1},goBack:function(){sessionStorage.removeItem("lastDuty"),this.$router.push({path:"/staffmenu/staff",query:{departId:this.$route.query.departId}})}}}}).call(e,n("L7Pj"))},"Yp/U":function(t,e,n){"use strict";n.d(e,"a",function(){return a});var i=n("EuEE"),a={getDepartList:function(t,e){i.a.DataEncSubmit("department_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},modifyDepartInfo:function(t,e){i.a.DataEncSubmit("department_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getStaffList:function(t,e){i.a.DataEncSubmit("employee_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getDepartStaffList:function(t,e){i.a.DataEncSubmit("department_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},modifyStaffInfo:function(t,e){i.a.DataEncSubmit("user_info.php",t,function(t){e&&"function"==typeof e&&e(t)})},getPhoneTest:function(t,e){i.a.DataEncSubmit("login_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getPhoneTestNew:function(t,e){i.a.DataEncSubmit("employee_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getUserInfor:function(t,e){i.a.DataEncSubmit("employee_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},editDutyRight:function(t,e){i.a.DataEncSubmit("position_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getDutyList:function(t,e){i.a.DataEncSubmit("position_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getShopData:function(t,e){i.a.DataEncSubmit("shopinfo_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getLoginInfo:function(t,e){i.a.DataEncSubmit("shop_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},saveLginInfo:function(t,e){i.a.DataEncSubmit("shop_save.php",t,function(t){e&&"function"==typeof e&&e(t)})}}},ZplB:function(t,e,n){e=t.exports=n("UTlt")(!1),e.push([t.i,'.staffCard[data-v-e9415534]{background:#fff;-webkit-box-shadow:0 2px 4px 0 #becaeb;box-shadow:0 2px 4px 0 #becaeb}.from_card[data-v-e9415534]{width:600px;padding:48px 0 40px;margin-left:133px}.page_title[data-v-e9415534]{width:100%;height:16px;font-size:12px;color:#333;margin-bottom:14px}.page_title a[data-v-e9415534]{color:#333}.form_title[data-v-e9415534]{width:100%;height:40px;font-size:14px;color:#666;line-height:40px;background-color:#f6f8fc;padding-left:14px}.img[data-v-e9415534]{width:200px;height:120px;margin-right:10px}.checkItem[data-v-e9415534]{display:inline-block;margin-right:40px}.checkItem .itemNum[data-v-e9415534]{display:block;margin-left:10px}.pinpo[data-v-e9415534]{font-size:12px;color:#9b9b9b}.add-btn[data-v-e9415534]{margin-left:14px;line-height:8px;padding-left:15px;cursor:pointer}.blue[data-v-e9415534]{font-size:12px;color:#4877e7;cursor:pointer}.btnGroup[data-v-e9415534]{padding-left:14.5%;padding-bottom:40px}.switch-label[data-v-e9415534]{position:relative;cursor:pointer}.switch-label[data-v-e9415534]:before{content:"\\5F00";left:7px}.switch-label[data-v-e9415534]:after,.switch-label[data-v-e9415534]:before{position:absolute;z-index:10;color:#fff;bottom:3px;font-size:12px;cursor:pointer}.switch-label[data-v-e9415534]:after{content:"\\5173";right:7px}.mui-switch[data-v-e9415534]{width:48px;height:24px;position:relative;border:1px solid #dfdfdf;background-color:#d8d8d8;-webkit-box-shadow:#dfdfdf 0 0 0 0 inset;box-shadow:inset 0 0 0 0 #dfdfdf;border-radius:12px;border-top-left-radius:12px;border-top-right-radius:12px;border-bottom-left-radius:12px;border-bottom-right-radius:12px;background-clip:content-box;display:inline-block;-webkit-appearance:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;outline:none;cursor:pointer}.mui-switch[data-v-e9415534]:before{content:"";width:24px;height:24px;position:absolute;top:-1px;left:0;border-radius:12px;border-top-left-radius:12px;border-top-right-radius:12px;border-bottom-left-radius:12px;border-bottom-right-radius:12px;background-color:#fff;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.4);box-shadow:0 1px 3px rgba(0,0,0,.4)}.mui-switch[data-v-e9415534]:checked{border-color:#7ed321;-webkit-box-shadow:#7ed321 0 0 0 16px inset;box-shadow:inset 0 0 0 16px #7ed321;background-color:#7ed321;cursor:pointer}.mui-switch[data-v-e9415534]:checked:before{left:24px}.mui-switch.mui-switch-animbg[data-v-e9415534]{-webkit-transition:background-color .4s ease;transition:background-color .4s ease}.mui-switch.mui-switch-animbg[data-v-e9415534]:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-animbg[data-v-e9415534]:checked{-webkit-box-shadow:#dfdfdf 0 0 0 0 inset;box-shadow:inset 0 0 0 0 #dfdfdf;background-color:#7ed321;-webkit-transition:border-color .4s,background-color .4s ease;transition:border-color .4s,background-color .4s ease}.mui-switch.mui-switch-animbg[data-v-e9415534]:checked:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-anim[data-v-e9415534]{-webkit-transition:border .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),box-shadow .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1)}.mui-switch.mui-switch-anim[data-v-e9415534]:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-anim[data-v-e9415534]:checked{-webkit-box-shadow:#7ed321 0 0 0 16px inset;box-shadow:inset 0 0 0 16px #7ed321;background-color:#7ed321;-webkit-transition:border .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease;transition:border .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease;transition:border .4s ease,box-shadow .4s ease,background-color 1.2s ease;transition:border .4s ease,box-shadow .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease}.mui-switch.mui-switch-anim[data-v-e9415534]:checked:before{-webkit-transition:left .3s;transition:left .3s}',""])},"a3/h":function(t,e,n){"use strict";function i(t){n("96j5"),n("i1rR")}Object.defineProperty(e,"__esModule",{value:!0});var a=n("3/ol"),o=n("rXtN"),s=n("QAAC"),r=i,l=Object(s.a)(a.a,o.a,o.b,!1,r,"data-v-cb160080",null);e.default=l.exports},i1rR:function(t,e,n){var i=n("qwru");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);n("FIqI")("539d7032",i,!0,{})},p4Ff:function(t,e,n){var i=n("GlEb");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);n("FIqI")("9875506c",i,!0,{})},qwru:function(t,e,n){e=t.exports=n("UTlt")(!1),e.push([t.i,"#addNewDepartment .el-input{width:320px;margin-bottom:30px}#addNewDepartment .el-input__inner{border-radius:0;height:34px}#addNewDepartment .el-input__inner:focus,#addNewDepartment .el-input__inner:hover{border:1px solid #bfcbd9}#addNewDepartment .el-button{width:160px;height:40px;border:1px solid #5a8cff;color:#5a8cff;border-radius:5px;font-size:14px;cursor:pointer}#addNewDepartment .el-dialog{top:20%}#addNewDepartment .category-dialog .el-dialog__header{width:540px;padding:0;margin-bottom:60px}#addNewDepartment .el-dialog__body{padding:0;height:200px}",""])},rXtN:function(t,e,n){"use strict";n.d(e,"a",function(){return i}),n.d(e,"b",function(){return a});var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"addNewDepartment"}},[n("el-dialog",{staticClass:"category-dialog",attrs:{width:"540px",visible:t.isDialogVisible,"show-close":!1}},[n("div",{staticClass:"dialog-title",attrs:{slot:"title"},slot:"title"},[t._v("添加部门")]),t._v(" "),n("div",{staticClass:"dialog-content"},[n("div",{staticClass:"category clearfix"},[n("div",{staticClass:"content-left left"},[n("span",[t._v("部门名称")])]),t._v(" "),n("div",{staticClass:"content-right left"},[n("el-input",{staticClass:"left",attrs:{placeholder:"请输入内容"},model:{value:t.dialongInputValue,callback:function(e){t.dialongInputValue=e},expression:"dialongInputValue"}})],1)]),t._v(" "),n("div",{staticClass:"button-group clearfix"},[n("div",{staticClass:"ok-btn",on:{click:t.addConfirm}},[t._v("保存")]),t._v(" "),n("div",{staticClass:"cancel-btn",on:{click:t.addCancel}},[t._v("取消")])])])])],1)},a=[]},uQUf:function(t,e,n){var i=n("ZplB");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);n("FIqI")("1a6177c6",i,!0,{})}});