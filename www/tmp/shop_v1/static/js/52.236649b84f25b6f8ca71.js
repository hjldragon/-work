webpackJsonp([52,73],{"+Y3c":function(t,e,i){e=t.exports=i("UTlt")(!1),e.push([t.i,"#addStaff .add-btn.el-button,#addStaff .saveBtn.el-button{width:160px;height:40px;color:#4877e7;font-size:14px}#addStaff .cancelBtn.el-button{width:160px;height:40px;margin-left:60px;font-size:14px}#addStaff .getInfor .el-button{min-width:120px}#addStaff .getInfoAble.el-button{border:1px solid #4877e7;cursor:pointer;color:#4877e7;background:#fff;margin-left:45%;margin-top:20px}#addStaff .add-btn.el-button{margin-left:5px;min-width:100px;padding:5px;border-radius:4px}#addStaff .el-input-group__append{height:30px}#addStaff .el-input-group__append .add-btn-test.el-button{background:#4877e7}#addStaff .el-input-group__append .add-btn-test.el-button,#addStaff .el-input-group__append .bg-gray.el-button{margin:0;margin-right:5px;width:110px;height:25px;padding:5px;color:#fff;border-radius:4px;-webkit-box-sizing:border-box;box-sizing:border-box}#addStaff .el-input-group__append .bg-gray.el-button{background:#d8d8d8}#addStaff .getInfo.el-button{border:1px solid #d8d8d8;cursor:pointer;color:#fff;background:#d8d8d8;margin-left:45%;margin-top:20px}#addStaff .el-button{width:160px;height:40px;border:1px solid #4877e7;color:#4877e7;text-align:center;border-radius:5px;font-size:12px}#addStaff .el-input-group__append{border-color:transarent}#addStaff .phone .el-input__inner{border-right:0}#addStaff .el-input__inner{border-radius:0}#addStaff .el-input{width:320px;height:34px}#addStaff .inforCard .el-form-item{margin-bottom:35px}#addStaff .el-button.is-disabled,#addStaff .el-button.is-disabled:focus,#addStaff .el-button.is-disabled:hover{background:#d8d8d8;border:1px solid #d8d8d8}#addStaff .el-form-item .el-input-group__append input:focus{border-color:#d8d8d8}#addStaff .el-form-item.is-error .el-input-group__append{border-color:#e7487e}#addStaff .is-focus{border-color:#4877e7}#addStaff .el-form-item .is-error .el-input.el-input__inner{border-color:#e7487e}#addStaff .cant-click.el-button{width:160px;height:40px;color:#fff;background:#d8d8d8;border:1px solid #d8d8d8;cursor:pointer}#addStaff .el-input-group__append{border-radius:0;border-left:0;padding-right:0}#addStaff .el-form-item input.el-input-inner{height:34px}#addStaff .el-input.el-input-group.el-input-group--append.phone:focus{border-color:#4877e7}#addStaff .from_card input.el-input-inner{height:34px}#addStaff .el-input-group__append,#addStaff .el-input-group__prepend{background:#fff;cursor:pointer}#addStaff .el-input .el-input__inner{height:34px}",""])},"0elz":function(t,e,i){"use strict";function n(t){i("6/5z"),i("cLsg")}Object.defineProperty(e,"__esModule",{value:!0});var a=i("4W2H"),o=i("K1FP"),s=i("QAAC"),r=n,d=Object(s.a)(a.a,o.a,o.b,!1,r,"data-v-0df52b1c",null);e.default=d.exports},"3/ol":function(t,e,i){"use strict";var n=i("Yp/U"),a=i("6nXL");e.a={props:["isShowDialog","allDepartName"],data:function(){return{dialongInputValue:"",isDialogVisible:!1,departNameArr:[]}},watch:{isShowDialog:function(t){!0===t&&(this.isDialogVisible=!0)}},created:function(){this.departNameArr=this.allDepartName},methods:{addConfirm:function(){var t=this;if(""===this.dialongInputValue)return void this.$slnotify({message:"部门名称不能为空"});if(this.dialongInputValue.length>6)return void this.$slnotify({message:"部门名称不超过6个字符"});if(-1!==this.departNameArr.indexOf(this.dialongInputValue))return void this.$slnotify({message:"不能添加已经存在的部门名称"});var e={department_save:1,department_name:this.dialongInputValue};n.a.modifyDepartInfo(e,function(e){if(0===e.ret){var i={};i.name=t.dialongInputValue,i.id=e.data.department_id,t.$emit("on-close",i),t.dialongInputValue="",t.isDialogVisible=!1}else-20047===e.ret?t.$slnotify({message:"不能添加已经存在的部门名称"}):t.$slnotify({message:a.D.toString(e.ret)})})},addCancel:function(){this.dialongInputValue="",this.handleClose()},handleClose:function(){this.dialongInputValue="",this.$emit("on-close"),this.isDialogVisible=!1}}}},"4W2H":function(t,e,i){"use strict";(function(t){var n=i("4YfN"),a=i.n(n),o=i("Yp/U"),s=i("5g5b"),r=i("a2vD"),d=i("EuEE"),u=i("swMD"),l=i("6nXL"),f=i("a3/h"),c=i("9rMa");e.a={data:function(){var t=this;return{imgbase_url:"./php",shopName:"",isShowLoginNum:!1,dutyForm:[],departForm:[],inputPhone:"",inputTest:"",testCode:"",identyImg:"",allDepartName:[],allDutyName:[],isAddNewDepartment:!1,getTestNumBtn:"获取验证码",phoneArr:[],isAddedDuty:!1,addedDuty:"",form1:{},form:{real_name:"",identity:"",sex:"",is_weixin:"",position_name:"",department_name:"",department_id:"",position_id:"",user_id:""},invited:!1,isDisable:!0,cantGetTestNum:!1,success:!1,GetInfo:!1,isCanSave:!1,saveFormData:{},saveHyImg:"",rules:{inputPhone:[{required:!0,validator:function(e,i,n){var a=/^\d{11}$/;t.form1.inputPhone?!1===a.test(t.form1.inputPhone)?n(new Error("请输入正确格式的手机号码")):n():n(new Error("请输入电话号码"))},trigger:"blur,change"}],inputTest:[{required:!0,validator:function(e,i,n){t.form1.inputTest?t.form1.inputTest&&6!==t.form1.inputTest.length?n(new Error("验证码不正确")):n():n(new Error("请输入验证码"))},trigger:"input,change"}]},newRules:{position_name:[{required:!0,validator:function(e,i,n){""===t.form.position_name?(t.isCanSave=!1,n(new Error("请选择职位"))):(t.isCanSave=!0,n())},trigger:"change,click,blur"}],department_name:[{required:!0,validator:function(e,i,n){""===t.form.department_name?(t.isCanSave=!1,n(new Error("请选择部门"))):(t.isCanSave=!0,n())},trigger:"click,change,blur"}]},usedObj:{},loginNum:0,loginAutho:null,loginArr:[],loginObj:{},loginData:[{name:"掌柜通",check:!1,totalNum:2,nowUse:0},{name:"商家运营平台",check:!1,totalNum:2,nowUse:0},{name:"智能收银机",check:!1,totalNum:2,nowUse:0},{name:"平板智能点餐机",check:!1,totalNum:2,nowUse:0},{name:"自助点餐机",check:!1,totalNum:2,nowUse:0}]}},created:function(){this.getAllDepartName(),this.getAllDutyName(),this.getPhoneArr(),this.getShopData(),this.getEdtedInfor();var t=r.a.getShopid();this.isShowLoginNum=s.a.canSeeLogin(this.isShowLoginNum,t)},components:{newDepartment:f.default},watch:{showBanner:function(){var t=this;this.$nextTick(function(){var e=t.$refs.staform,i=e.style.height;d.a.AdjustHeight(e);var n=e.style.height;e.style.height=i,setTimeout(function(){e.style.transition="height 0.28s ease-out"},50),setTimeout(function(){e.style.height=n},50)})}},computed:a()({showBanner:function(){return this.$store.state.layout.showBanner}},Object(c.b)({ACS:function(t){return t.permission.sysPermis},SHOP_K:function(t){return t.permission.SHOP_K}})),mounted:function(){this.adjustHeight()},methods:{adjustHeight:function(){var t=this;this.$nextTick(function(){var e=t.$refs.staform;d.a.AdjustHeight(e)})},getEdtedInfor:function(){var t=u.a.getItem("STAFF-INFOR"),e=u.a.getItem("INVIT-STAFF");t&&(this.form1.inputPhone=t.inputPhone,this.form1.inputTest=t.inputTest,this.GetInfo=!0,this.isDisable=!1,this.success=!0,this.$route.query.positionName?(e&&(this.form.real_name=e.real_name,this.form.identity=e.identity,this.form.sex=e.sex,this.form.is_weixin=e.is_weixin,this.form.user_id=e.user_id,this.form.department_name=e.department_name,this.form.department_id=e.department_id,this.saveFormData.userid=e.user_id),this.form.position_name=null!==this.$route.query.positionName?this.$route.query.positionName:e.position_name,this.$route.query.positionId&&(this.form.position_id=this.$route.query.positionId),console.log(this.$route.query.positionId)):e&&(this.form.real_name=e.real_name,this.form.identity=e.identity,this.form.sex=e.sex,this.form.is_weixin=e.is_weixin,this.form.user_id=e.user_id,this.form.position_name=e.position_name,this.form.department_name=e.department_name,this.form.position_id=e.position_id,this.form.department_id=e.department_id,this.saveFormData.userid=e.user_id),this.checkFormInput())},getShopData:function(){var t=this,e=r.a.getShopid(),i={get_shopinfo_base:1,shop_id:e};o.a.getShopData(i,function(e){t.shopName=e.data.shopinfo.shop_name})},checkFormInput:function(){var t=this;this.$refs.form&&this.$refs.form.validate(function(e){e&&""!==t.form.position_name&&""!==t.form.department_name?t.isCanSave=!0:t.isCanSave=!1})},isInvited:function(t,e){var i=this;t.forEach(function(t){t===e&&(i.invited=!0)})},getTestNum:function(){var t=this;if(""===this.form1.inputPhone||void 0===this.form1.inputPhone)return void this.$slnotify({message:"请输入电话号码"});if(!0!==/^\d{11}$/.test(this.form1.inputPhone))return!1;var e={get_invite_phone_code:1,phone:this.form1.inputPhone};o.a.getPhoneTestNew(e,function(e){0===e.ret?t.setIntervalMethods():-20050===e.ret?t.$slnotify({message:"该号码还未注册"}):t.$slnotify({message:l.D.toString(e.ret)})}),setTimeout(function(){t.invited=!1},2e3)},checkPhoneCode:function(t){this.form1.inputTest.length>5?this.checkInputTest(t):(this.GetInfo=!1,this.isDisable=!0,this.success=!1)},checkInputTest:function(t){var e=this;if(""===this.form1.inputPhone||""===this.form1.inputTest)return void this.$slnotify({message:"请填写登录手机号以及获得的验证码"});this.$refs[t].validate(function(t){if(t){e.isDisable=!1;var i={get_user_info:1,phone:e.form1.inputPhone,phone_code:e.form1.inputTest};o.a.getStaffList(i,function(t){0===t.ret?(e.loginArr=t.data.user_info.answer||[],e.isDisable,e.success=!0,e.saveHyImg=t.data.user_info.health_certificate,e.saveFormData=t.data.user_info,e.form.user_id=e.saveFormData.userid):-20042===t.ret?(e.success=!1,e.isDisable,e.$slnotify({message:"输入的验证码不正确"})):-30030===t.ret?(e.success=!1,e.isDisable,e.$slnotify({message:"验证码超时，请重新获取验证码"})):(e.$slnotify({message:l.D.toString(t.ret)}),e.GetInfo=!1,e.success=!1)})}else e.GetInfo=!1,e.success=!1})},addBlueLine:function(){t(".el-input-group__append").addClass("is-focus")},notBlueLine:function(){t(".el-input-group__append").removeClass("is-focus")},getCountInfor:function(t){this.success&&!this.GetInfo&&(this.identyImg=this.saveHyImg,this.form.real_name=this.saveFormData.real_name,this.form.identity=this.saveFormData.identity,this.form.sex=this.saveFormData.sex,this.form.is_weixin=this.saveFormData.is_weixin,this.form.user_id=this.saveFormData.user_id,this.form.position_name="",this.form.department_name="",this.GetInfo=!0,this.checkInputTest(t))},checkPositionDepart:function(t){var e=this;this.$refs[t].validate(function(t){e.isCanSave=!!t})},addNewDepartment:function(){if(!this.ACS[this.SHOP_K.ADD_DEPARTMENT])return this.$slnotify({message:"操作权限不足"});this.isAddNewDepartment=!0},addDuty:function(){if(!this.ACS[this.SHOP_K.ADD_POSITION])return this.$slnotify({message:"操作权限不足"});sessionStorage.setItem("lastDuty",this.allDutyName[this.allDutyName.length-1]),this.isAddedDuty=!0,u.a.setItem("STAFF-INFOR",this.form1),u.a.setItem("INVIT-STAFF",this.form),this.$router.push({path:"/staff/editDuty",query:{infor:this.allDutyName,pathName:"/staff/invitStaff"}})},setIntervalMethods:function(){var t=this,e="",i=60;this.cantGetTestNum=!0,this.getTestNumBtn=i+"秒后重新获取",e=setInterval(function(){0===i?(clearInterval(e),t.cantGetTestNum=!1,t.getTestNumBtn="重新获取验证码",i=60):(t.getTestNumBtn=i+"秒后重新获取",i--)},1e3)},getAllDepartName:function(){var t=this,e={get_department_list:1};o.a.getDepartList(e,function(e){0===e.ret&&(t.departForm=e.data.department_list,e.data.department_list.forEach(function(e){if(-1===t.allDepartName.indexOf(e.department_name)){var i={};i.label=e.department_name,i.value=e.department_id,t.allDepartName.push(i)}}))})},getAllDutyName:function(){var t=this,e={get_position_list:1,is_start:1};o.a.getDutyList(e,function(e){t.dutyForm=e.data.position_list,e.data.position_list.forEach(function(e){if(-1===t.allDutyName.indexOf(e.position_name)){var i={};i.label=e.position_name,i.value=e.position_id,t.allDutyName.push(i)}})})},changePosition:function(t){this.form.position_name&&(this.form.position_id=this.form.position_name),this.checkPositionDepart(t)},changeDepart:function(t){this.form.department_name&&(this.form.department_id=this.form.department_name),this.checkPositionDepart(t)},saveInvitStaff:function(t){var e=this;null!==sessionStorage.getItem("lastDuty")&&sessionStorage.removeItem("lastDuty"),this.checkPositionDepart(t),this.$refs[t].validate(function(t){if(t){if(!0!==e.GetInfo)return e.$slnotify({message:"请先获取员工信息"}),!1;e.form&&void 0!==e.form&&""!==e.form1.inputPhone&&""!==e.form1.inputTest&&""!==e.form.position_name&&""!==e.form.department_name?(e.loginObj.save_authorize=1,o.a.saveLginInfo(e.loginObj,e.saveStaffEdit)):e.$slnotify({message:"请填写完整信息"})}})},saveStaffEdit:function(){var t={shop_employee_save:1,user_id:this.saveFormData.userid,department_id:this.form.department_id,position_id:this.form.position_id};o.a.getUserInfor(t,this.goBack)},getPhoneArr:function(){var t=this,e=r.a.getShopinfo();this.phoneArr.push(e.userinfo.phone),o.a.getStaffList({get_employee_list:1},function(e){e.data.employee_list.forEach(function(e){t.phoneArr.push(e.phone)})})},getLoginData:function(){var t=this,e={get_shop_authorize:1};o.a.getLoginInfo(e,function(e){0===e.ret&&(t.loginData[1].totalNum=null!==e.data.info.pc_num?e.data.info.pc_num:"--",t.loginData[1].nowUse=null!==e.data.info.used_pc_num?e.data.info.used_pc_num:"--",t.loginData[3].totalNum=null!==e.data.info.pad_num?e.data.info.pad_num:"--",t.loginData[3].nowUse=null!==e.data.info.used_pad_num?e.data.info.used_pad_num:"--",t.loginData[2].totalNum=null!==e.data.info.cashier_num?e.data.info.cashier_num:"--",t.loginData[2].nowUse=null!==e.data.info.used_cashier_num?e.data.info.used_cashier_num:"--",t.loginData[0].totalNum=null!==e.data.info.app_num?e.data.info.app_num:"--",t.loginData[0].nowUse=null!==e.data.info.used_app_num?e.data.info.used_app_num:"--",t.loginData[4].totalNum=null!==e.data.info.machine_num?e.data.info.machine_num:"--",t.loginData[4].nowUse=null!==e.data.info.used_machine_num?e.data.info.used_machine_num:"--",t.usedObj.used_pc_num=e.data.info.used_pc_num||"",t.usedObj.used_pad_num=e.data.info.used_pad_num||"",t.usedObj.used_cashier_num=e.data.info.used_cashier_num||"",t.usedObj.used_app_num=e.data.info.used_app_num||"",t.usedObj.used_machine_num=e.data.info.used_machine_num||"",t.loginObj.used_pc_num=e.data.info.used_pc_num||"",t.loginObj.used_pad_num=e.data.info.used_pad_num||"",t.loginObj.used_machine_num=e.data.info.used_machine_num||"",t.loginObj.used_cashier_num=e.data.info.used_cashier_num||"",t.loginObj.used_app_num=e.data.info.used_app_num||"",t.loginArr&&t.loginArr.length>0&&(t.loginArr.indexOf(l.k.PC)&&(t.loginData[1].check=!0),t.loginArr.indexOf(l.k.PAD)&&(t.loginData[3].check=!0),t.loginArr.indexOf(l.k.CASHREGISTER)&&(t.loginData[2].check=!0),t.loginArr.indexOf(l.k.APP)&&(t.loginData[0].check=!0),t.loginArr.indexOf(l.k.MACHAIN)&&(t.loginData[0].check=!0)))})},handelChangeSwitch:function(t,e){isNaN(t.nowUse)||isNaN(t.totalNum)||t.nowUse===t.totalNum?(!1===e.target.checked&&(t.nowUse=Number(t.nowUse-1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-16))),e.target.checked=!1,t.check=!1):!1===t.check?t.nowUse<t.totalNum?(t.check=!0,e.target.checked=!0,t.nowUse=Number(t.nowUse+1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho+16))):e.target.checked=!1:(t.check=!1,t.nowUse>0?(e.target.checked=!1,t.nowUse=Number(t.nowUse-1),t.name===this.loginData[1].name&&(this.loginObj.used_pc_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-1)),t.name===this.loginData[3].name&&(this.loginObj.used_pad_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-2)),t.name===this.loginData[2].name&&(this.loginObj.used_cashier_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-4)),t.name===this.loginData[0].name&&(this.loginObj.used_app_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-8)),t.name===this.loginData[4].name&&(this.loginObj.used_machine_num=t.nowUse||0,this.loginAutho=Number(this.loginAutho-16))):t.nowUse=0)},canselInvitStaff:function(t){this.isAddNewDepartment=!1,this.$refs[t].resetFields(),this.goBack()},hideDialog:function(t){if(t){this.form.department_name=t.name,this.form.department_id=t.id;var e={};e.label=this.form.department_name,e.value=this.form.department_id,this.allDepartName.push(e),this.checkFormInput()}this.isAddNewDepartment=!1},goBack:function(){null!==sessionStorage.getItem("lastDuty")&&sessionStorage.removeItem("lastDuty"),u.a.removeItem("STAFF-INFOR"),u.a.removeItem("INVIT-STAFF"),u.a.removeItem("LOGEIN-DATA"),this.$router.push("/staffmenu/staff")}}}}).call(e,i("L7Pj"))},"5g5b":function(t,e,i){"use strict";i.d(e,"b",function(){return l}),i.d(e,"a",function(){return f});var n=i("3cXf"),a=i.n(n),o=i("6nXL"),s=i("IvJb"),r=i("a2vD"),d=i("EuEE"),u=i("rUdh"),l={initTreeData:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];return e=e.map(function(e){return s.default.set(e,"canEditor",!1),s.default.set(e,"addIcon",!0),s.default.set(e,"editorIcon",!0),s.default.set(e,"deleteIcon",!0),s.default.set(e,"isShowBtn",!1),s.default.set(e,"isActive",!1),s.default.set(e,"breakshow",!1),s.default.set(e,"allShow",!1),s.default.set(e,"isExpand",!0),s.default.set(e,"isFolder",!1),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.initTreeData(e.employee_list),e})},addTitleToTree:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e={};return s.default.set(e,"department_name","部门管理"),s.default.set(e,"employee_list",t),s.default.set(e,"isThree",!0),s.default.set(e,"isActive",!0),s.default.set(e,"department_id","0"),e},generateKey:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],i=arguments[1];return e=e.map(function(e,n){return e.key=i+"-"+n.toString(),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.generateKey(e.employee_list,e.key),e})},getKeylength:function(t){if(!d.a.isEmpty(t)){return t.split("-").length}},selectIcon:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];e=e.map(function(e){var i=t.getKeylength(e.key);2===i?(e.addIcon=!0,e.editorIcon=!1,e.deleteIcon=!1,e.isExpand=!0,e.isActive=!0):3===i?(e.addIcon=!1,e.editorIcon=!0,e.deleteIcon=!0,e.isExpand=!1,e.isFolder=!0):4===i&&(e.addIcon=!1,e.editorIcon=!1,e.deleteIcon=!1),e.hasOwnProperty("employee_list")&&e.employee_list.length>0&&t.selectIcon(e.employee_list)})},saveDepartName:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=[];return function t(i){i.forEach(function(i){i.hasOwnProperty("department_name")&&"部门管理"!==i.department_name&&e.push(i.department_name),i.hasOwnProperty("employee_list")&&i.employee_list.length>0&&t(i.employee_list)})}(t),e},treeDataById:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e={};return function t(i){i.forEach(function(i){i.hasOwnProperty("department_id")?e[i.department_id]=i:i.hasOwnProperty("employee_id")&&(e[i.employee_id]=i),i.hasOwnProperty("employee_list")&&i.employee_list.length>0&&t(i.employee_list)})}(t),e},changeSelecte:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};for(var i in e)e.hasOwnProperty(i)&&(e[i].isActive=t===i);return e},getDutyArr:function(t){var e=[];return t.forEach(function(t){-1===e.indexOf(t.position_name)&&e.push(t.position_name)}),e},getDutyDepartId:function(t,e){var i;return t.forEach(function(t){void 0!==t.position_name?t.position_name===e&&(i=t.position_id):t.department_name===e&&(i=t.department_id)}),i},search:function(t,e,i,n){var a=[],s=new RegExp(t,"g"),r=new RegExp(e,"g");return n.forEach(function(d){t&&t!==o.B.code[0]||""!==e||""!==i?null!==s.exec(d.position_name)&&""===e&&""===i?a.push(d):null!==s.exec(d.position_name)&&null!==r.exec(d.real_name)&&""===i?a.push(d):null!==s.exec(d.position_name)&&null!==r.exec(d.real_name)&&d.phone.indexOf(i)>-1?a.push(d):null!==s.exec(d.position_name)&&d.phone.indexOf(i)>-1&&""===e?a.push(d):(!t||t===o.B.code[0])&&null!==r.exec(d.real_name)&&d.phone.indexOf(i)>-1?a.push(d):(!t||t===o.B.code[0])&&""===e&&d.phone.indexOf(i)>-1?a.push(d):t&&t!==o.B.code[0]||null===r.exec(d.real_name)||""!==i||a.push(d):a=n}),a},getTotal:function(t){if(void 0!==t&&t.length>0){for(var e=0,i=0;i<t.length;i++)e++;return e}},getPageList:function(t,e,i){if(void 0!==t&&t.length>0){return t.filter(function(t,n){return n<e*i&&n>=e*(i-1)})}},isRight:function(t){return t.forEach(function(t){var e,i=[],n=[];for(var a in o.O)void 0!==t.position_permission?(e=t.position_permission&o.O[a],0!==e&&(n.push(o.O.code[e]),i.push(e)),t.arrId=i,t.arrRight=n,t.arrId[0]===o.O.ALLBACKSTAGE&&t.arrId[1]===o.O.ALLWEB?t.arrRight=["全部"]:t.arrId[0]===o.O.ALLBACKSTAGE&&t.arrId[1]===o.O.ALLWEB&&t.arrId.length>2?t.arrRight=["全部"]:t.arrId[0]===o.O.ALLWEB&&t.arrId.length>1&&(t.arrRight=["点餐收银全部权限"]),t.arrRight.forEach(function(t){t===o.O.code[o.O.ALLWEB]&&(t="")})):(t.arrId=0,t.arrRight=o.B.code[0])}),t},showALLPosition:function(t){if(isNaN(t)){var e=!0;for(var i in t)void 0!==t[i]&&0===t[i]&&(e=!1);return e}},positionById:function(t){var e=JSON.parse(a()(t)),i={};for(var n in e){var o=e[n].list;o&&o.length>0&&o.forEach(function(t){i[t.id]=t})}return i},showEveryPosition:function(t){var e=this.positionById(u.a);if(isNaN(t)){var i=[];for(var n in t)void 0!==t[n]&&1===t[n]&&i.push(e[n].name);return i}},judgeStaffList:function(t){var e=[];for(var i in o.O)t.forEach(function(t){t===o.O.code[o.O[i]]&&e.push(o.O[i])});return e}},f={getTotal:function(t){if(void 0!==t&&t.length>0){for(var e=0,i=0;i<t.length;i++)e++;return e}},getPageList:function(t,e,i){var n=[];return void 0!==t&&t.length>0?n=t.filter(function(t,n){return n<e*i&&n>=e*(i-1)}):n},canSeeLogin:function(t,e){var i=r.a.getShopinfo();return i.shopinfo&&i.shopinfo.length>0&&i.shopinfo.forEach(function(i){e===i.shop_id&&i.employee_is_admin===o.H.YES&&(t=!0)}),t}}},"6/5z":function(t,e,i){var n=i("GE8Q");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);i("FIqI")("52f07e30",n,!0,{})},"96j5":function(t,e,i){var n=i("F0tS");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);i("FIqI")("3a506bea",n,!0,{})},F0tS:function(t,e,i){e=t.exports=i("UTlt")(!1),e.push([t.i,"#addNewDepartment .dialog-title[data-v-cb160080]{width:540px;height:40px;font-size:16px;color:#fff;background-color:#5a8cff;text-align:center;line-height:40px}#addNewDepartment .dialog-content[data-v-cb160080]{width:540px;height:264px}#addNewDepartment .category .content-left[data-v-cb160080]{width:80px;height:34px;line-height:34px;margin-left:60px}#addNewDepartment .category .content-right[data-v-cb160080]{width:320px;height:34px}#addNewDepartment .button-group[data-v-cb160080]{text-align:center;margin-top:60px}#addNewDepartment .button-group div[data-v-cb160080]{width:160px;height:40px;border-radius:4px;line-height:40px;text-align:center;border:1px solid #5a8cff;color:#5a8cff;cursor:pointer;display:inline-block}#addNewDepartment .button-group .ok-btn[data-v-cb160080]{background-color:#5a8cff;color:#fff;cursor:pointer;margin-right:60px}",""])},GE8Q:function(t,e,i){e=t.exports=i("UTlt")(!1),e.push([t.i,'.staffCard[data-v-0df52b1c]{background:#fff;-webkit-box-shadow:0 2px 4px 0 #becaeb;box-shadow:0 2px 4px 0 #becaeb}.from_card[data-v-0df52b1c]{width:80%;padding:48px 0 40px;margin-left:133px}.inforCard[data-v-0df52b1c]{width:100%;margin-bottom:30px}.page_title[data-v-0df52b1c]{width:100%;height:16px;font-size:12px;color:#333;margin-bottom:10px;padding-left:5px;cursor:pointer}.form_title[data-v-0df52b1c]{width:100%;height:40px;font-size:14px;color:#666;line-height:40px;background-color:#f6f8fc;padding-left:14px}.img[data-v-0df52b1c]{width:100px;height:50px}.checkItem[data-v-0df52b1c]{display:inline-block;margin-right:40px}.checkItem .itemNum[data-v-0df52b1c]{display:block;margin-left:10px}.pinpo[data-v-0df52b1c]{font-size:12px;color:#9b9b9b}.blue[data-v-0df52b1c]{font-size:12px;color:#4877e7;cursor:pointer}.btnGroup[data-v-0df52b1c]{margin-top:100px;height:100px;text-align:center}.invited[data-v-0df52b1c]{position:absolute;top:60%;left:50%;width:300px;height:100px;line-height:100px;text-align:center;color:#fff;border-radius:4px;background:rgba(0,0,0,.5)}.getInfor[data-v-0df52b1c]{margin-bottom:10px}.switch-label[data-v-0df52b1c]{position:relative;cursor:pointer}.switch-label[data-v-0df52b1c]:before{content:"\\5F00";left:7px}.switch-label[data-v-0df52b1c]:after,.switch-label[data-v-0df52b1c]:before{position:absolute;z-index:10;color:#fff;bottom:3px;font-size:12px;cursor:pointer}.switch-label[data-v-0df52b1c]:after{content:"\\5173";right:7px}.mui-switch[data-v-0df52b1c]{width:48px;height:24px;position:relative;border:1px solid #dfdfdf;background-color:#d8d8d8;-webkit-box-shadow:#dfdfdf 0 0 0 0 inset;box-shadow:inset 0 0 0 0 #dfdfdf;border-radius:12px;border-top-left-radius:12px;border-top-right-radius:12px;border-bottom-left-radius:12px;border-bottom-right-radius:12px;background-clip:content-box;display:inline-block;-webkit-appearance:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;outline:none;cursor:pointer}.mui-switch[data-v-0df52b1c]:before{content:"";width:24px;height:24px;position:absolute;top:-1px;left:0;border-radius:12px;border-top-left-radius:12px;border-top-right-radius:12px;border-bottom-left-radius:12px;border-bottom-right-radius:12px;background-color:#fff;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.4);box-shadow:0 1px 3px rgba(0,0,0,.4)}.mui-switch[data-v-0df52b1c]:checked{border-color:#7ed321;-webkit-box-shadow:#7ed321 0 0 0 16px inset;box-shadow:inset 0 0 0 16px #7ed321;background-color:#7ed321;cursor:pointer}.mui-switch[data-v-0df52b1c]:checked:before{left:24px}.mui-switch.mui-switch-animbg[data-v-0df52b1c]{-webkit-transition:background-color .4s ease;transition:background-color .4s ease}.mui-switch.mui-switch-animbg[data-v-0df52b1c]:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-animbg[data-v-0df52b1c]:checked{-webkit-box-shadow:#dfdfdf 0 0 0 0 inset;box-shadow:inset 0 0 0 0 #dfdfdf;background-color:#7ed321;-webkit-transition:border-color .4s,background-color .4s ease;transition:border-color .4s,background-color .4s ease}.mui-switch.mui-switch-animbg[data-v-0df52b1c]:checked:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-anim[data-v-0df52b1c]{-webkit-transition:border .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),box-shadow .4s cubic-bezier(0,0,0,1);transition:border .4s cubic-bezier(0,0,0,1),box-shadow .4s cubic-bezier(0,0,0,1),-webkit-box-shadow .4s cubic-bezier(0,0,0,1)}.mui-switch.mui-switch-anim[data-v-0df52b1c]:before{-webkit-transition:left .3s;transition:left .3s}.mui-switch.mui-switch-anim[data-v-0df52b1c]:checked{-webkit-box-shadow:#7ed321 0 0 0 16px inset;box-shadow:inset 0 0 0 16px #7ed321;background-color:#7ed321;-webkit-transition:border .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease;transition:border .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease;transition:border .4s ease,box-shadow .4s ease,background-color 1.2s ease;transition:border .4s ease,box-shadow .4s ease,background-color 1.2s ease,-webkit-box-shadow .4s ease}.mui-switch.mui-switch-anim[data-v-0df52b1c]:checked:before{-webkit-transition:left .3s;transition:left .3s}',""])},K1FP:function(t,e,i){"use strict";i.d(e,"a",function(){return n}),i.d(e,"b",function(){return a});var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"addStaff"}},[i("div",{staticClass:"page_title"},[i("span",{on:{click:t.goBack}},[t._v("员工管理")]),t._v(" "),i("span",{staticClass:"blue"},[t._v(">")]),t._v(" "),i("span",{staticClass:"blue"},[t._v("邀请员工")])]),t._v(" "),i("div",{ref:"staform",staticClass:"staffCard"},[i("div",{staticClass:"form_title"},[t._v("邀请员工")]),t._v(" "),i("div",{staticClass:"from_card"},[i("el-form",{ref:"form",attrs:{model:t.form1,"label-width":"100px",rules:t.rules}},[i("el-form-item",{attrs:{label:"店铺名称"}},[i("span",[t._v(t._s(t.shopName))])]),t._v(" "),i("el-form-item",{attrs:{label:"登录手机号",prop:"inputPhone"}},[i("el-input",{attrs:{placeholder:"请输入电话号码"},model:{value:t.form1.inputPhone,callback:function(e){t.$set(t.form1,"inputPhone",e)},expression:"form1.inputPhone"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"登录验证码",prop:"inputTest"}},[i("el-input",{staticClass:"phone",attrs:{placeholder:"请输入验证码"},on:{input:function(e){t.checkPhoneCode("form")},focus:t.addBlueLine,blur:t.notBlueLine},model:{value:t.form1.inputTest,callback:function(e){t.$set(t.form1,"inputTest",e)},expression:"form1.inputTest"}},[!1===t.cantGetTestNum?i("el-button",{staticClass:"add-btn-test",attrs:{slot:"append",disabled:t.cantGetTestNum},on:{click:t.getTestNum},slot:"append"},[t._v(t._s(t.getTestNumBtn))]):i("el-button",{staticClass:"bg-gray",attrs:{slot:"append",disabled:t.cantGetTestNum},slot:"append"},[t._v(t._s(t.getTestNumBtn))])],1)],1)],1),t._v(" "),i("div",{staticClass:"getInfor"},[0==t.success?i("el-button",{staticClass:"getInfo",attrs:{disabled:t.isDisable}},[t._v("读取账号信息")]):t._e(),t._v(" "),t.success?i("el-button",{staticClass:"getInfoAble",attrs:{disabled:t.isDisable},on:{click:function(e){t.getCountInfor("form")}}},[t._v("读取账号信息")]):t._e()],1)],1),t._v(" "),i("div",{staticClass:"form_title"},[t._v("员工信息")]),t._v(" "),i("div",{staticClass:"from_card"},[t.GetInfo?i("div",{staticClass:"inforCard"},[i("el-form",{ref:"newForm",attrs:{model:t.form,"label-width":"100px",rules:t.newRules}},[i("el-form-item",{attrs:{label:"姓名"}},[i("span",[t._v(t._s(t.form.real_name))])]),t._v(" "),i("el-form-item",{attrs:{label:"职位",prop:"position_name"}},[i("el-select",{attrs:{placeholder:"职位"},on:{change:function(e){t.changePosition("newForm")}},model:{value:t.form.position_name,callback:function(e){t.$set(t.form,"position_name",e)},expression:"form.position_name"}},t._l(t.allDutyName,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})})),t._v(" "),i("el-button",{staticClass:"add-btn",on:{click:t.addDuty}},[t._v("新增职位")])],1),t._v(" "),i("el-form-item",{attrs:{label:"部门",prop:"department_name"}},[i("el-select",{attrs:{placeholder:"部门"},on:{change:function(e){t.changeDepart("newForm")}},model:{value:t.form.department_name,callback:function(e){t.$set(t.form,"department_name",e)},expression:"form.department_name"}},t._l(t.allDepartName,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})})),t._v(" "),i("el-button",{staticClass:"add-btn",on:{click:t.addNewDepartment}},[t._v("新增部门")])],1),t._v(" "),i("el-form-item",{attrs:{label:"身份证"}},[t.form.identity&&""!==t.form.identity?i("span",[t._v(t._s(t.form.identity))]):i("span",[t._v("未上传")])]),t._v(" "),i("el-form-item",{attrs:{label:"性别"}},[1===t.form.sex?i("span",[t._v("男")]):t._e(),t._v(" "),0===t.form.sex?i("span",[t._v("女")]):t._e(),t._v(" "),0!==t.form.sex&&1!==t.form.sex?i("span",[t._v("保密")]):t._e()]),t._v(" "),i("el-form-item",{attrs:{label:"健康证扫描件"}},t._l(t.identyImg,function(e){return t.identyImg&&null!==t.identyImg&&t.identyImg.length>0?i("img",{staticClass:"img",attrs:{src:t.imgbase_url+"/img_get.php?img=1&height=100&width=100&imgname="+e}}):i("span",[t._v("未上传")])})),t._v(" "),i("el-form-item",{attrs:{label:"绑定微信"}},[1===t.form.is_weixin?i("span",[t._v("绑定")]):t._e(),t._v(" "),0===t.form.is_weixin?i("span",[t._v("未绑定")]):t._e()])],1)],1):t._e()]),t._v(" "),i("div",{staticClass:"btnGroup"},[!0===t.isCanSave?i("el-button",{staticClass:"saveBtn",on:{click:function(e){t.saveInvitStaff("newForm")}}},[t._v("保存")]):i("el-button",{staticClass:"saveBtn cant-click",on:{click:function(e){t.checkPositionDepart("newForm")}}},[t._v("保存")]),t._v(" "),i("el-button",{staticClass:"cancelBtn",on:{click:function(e){t.canselInvitStaff("form")}}},[t._v("取消")])],1)]),t._v(" "),i("new-department",{attrs:{isShowDialog:t.isAddNewDepartment,allDepartName:t.allDepartName},on:{"on-close":t.hideDialog}})],1)},a=[]},"Yp/U":function(t,e,i){"use strict";i.d(e,"a",function(){return a});var n=i("EuEE"),a={getDepartList:function(t,e){n.a.DataEncSubmit("department_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},modifyDepartInfo:function(t,e){n.a.DataEncSubmit("department_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getStaffList:function(t,e){n.a.DataEncSubmit("employee_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getDepartStaffList:function(t,e){n.a.DataEncSubmit("department_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},modifyStaffInfo:function(t,e){n.a.DataEncSubmit("user_info.php",t,function(t){e&&"function"==typeof e&&e(t)})},getPhoneTest:function(t,e){n.a.DataEncSubmit("login_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getPhoneTestNew:function(t,e){n.a.DataEncSubmit("employee_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getUserInfor:function(t,e){n.a.DataEncSubmit("employee_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},editDutyRight:function(t,e){n.a.DataEncSubmit("position_save.php",t,function(t){e&&"function"==typeof e&&e(t)})},getDutyList:function(t,e){n.a.DataEncSubmit("position_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getShopData:function(t,e){n.a.DataEncSubmit("shopinfo_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},getLoginInfo:function(t,e){n.a.DataEncSubmit("shop_get.php",t,function(t){e&&"function"==typeof e&&e(t)})},saveLginInfo:function(t,e){n.a.DataEncSubmit("shop_save.php",t,function(t){e&&"function"==typeof e&&e(t)})}}},"a3/h":function(t,e,i){"use strict";function n(t){i("96j5"),i("i1rR")}Object.defineProperty(e,"__esModule",{value:!0});var a=i("3/ol"),o=i("rXtN"),s=i("QAAC"),r=n,d=Object(s.a)(a.a,o.a,o.b,!1,r,"data-v-cb160080",null);e.default=d.exports},cLsg:function(t,e,i){var n=i("+Y3c");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);i("FIqI")("f28f97ec",n,!0,{})},i1rR:function(t,e,i){var n=i("qwru");"string"==typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);i("FIqI")("539d7032",n,!0,{})},qwru:function(t,e,i){e=t.exports=i("UTlt")(!1),e.push([t.i,"#addNewDepartment .el-input{width:320px;margin-bottom:30px}#addNewDepartment .el-input__inner{border-radius:0;height:34px}#addNewDepartment .el-input__inner:focus,#addNewDepartment .el-input__inner:hover{border:1px solid #bfcbd9}#addNewDepartment .el-button{width:160px;height:40px;border:1px solid #5a8cff;color:#5a8cff;border-radius:5px;font-size:14px;cursor:pointer}#addNewDepartment .el-dialog{top:20%}#addNewDepartment .category-dialog .el-dialog__header{width:540px;padding:0;margin-bottom:60px}#addNewDepartment .el-dialog__body{padding:0;height:200px}",""])},rXtN:function(t,e,i){"use strict";i.d(e,"a",function(){return n}),i.d(e,"b",function(){return a});var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"addNewDepartment"}},[i("el-dialog",{staticClass:"category-dialog",attrs:{width:"540px",visible:t.isDialogVisible,"show-close":!1}},[i("div",{staticClass:"dialog-title",attrs:{slot:"title"},slot:"title"},[t._v("添加部门")]),t._v(" "),i("div",{staticClass:"dialog-content"},[i("div",{staticClass:"category clearfix"},[i("div",{staticClass:"content-left left"},[i("span",[t._v("部门名称")])]),t._v(" "),i("div",{staticClass:"content-right left"},[i("el-input",{staticClass:"left",attrs:{placeholder:"请输入内容"},model:{value:t.dialongInputValue,callback:function(e){t.dialongInputValue=e},expression:"dialongInputValue"}})],1)]),t._v(" "),i("div",{staticClass:"button-group clearfix"},[i("div",{staticClass:"ok-btn",on:{click:t.addConfirm}},[t._v("保存")]),t._v(" "),i("div",{staticClass:"cancel-btn",on:{click:t.addCancel}},[t._v("取消")])])])])],1)},a=[]}});