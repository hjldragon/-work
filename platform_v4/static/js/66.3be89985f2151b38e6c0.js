webpackJsonp([66],{VFyW:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a("a3Yh"),i=a.n(n),r=a("6nXL"),l=a("GAg9"),s=a("U1Dd"),o=a("dlm/"),g=a("P9l9"),c=a("N6PA"),m={components:{imgUpload:l.a,BigImg:s.default,SetTable:o.default},data:function(){var e,t=function(e,t,a){var n=c.b.isDiscount(t);return t?isNaN(t)?a(new Error("请输入有效的折扣")):n?void a():a(new Error("请输入有效的折扣(大于0小于10，且最多保留一位小数点)")):a(new Error("请输入对应折扣"))};return{isBanner:1,isTextBtn:0,labelPosition:"right",isClickSave:!1,isEdit:!1,setData:[],clickData:{},agentFormSet:(e={agent_level:"",banner:"",city_level:"",hardware_rebates:"",software_rebates:"",supplies_rebates:"",uplevel_money:""},i()(e,"city_level",""),i()(e,"id",""),e),imgbase_url:"./php",imgSize:1024,showTips:!1,imgSrc:"",imgurl:"",img_link:"",agentLevel:"",cityLevel:"",showImg:!1,cityLevelOption:[{label:r.i.toString(r.i.AREA),value:r.i.AREA},{label:r.i.toString(r.i.INDUSTRY),value:r.i.INDUSTRY}],AgentLevelOption:[{label:r.c.toString(r.c.FIRST),value:r.c.FIRST},{label:r.c.toString(r.c.SECOND),value:r.c.SECOND},{label:r.c.toString(r.c.THREE),value:r.c.THREE}],agentRules:{city_level:[{required:!0,message:"请选择代理商类型",trigger:"change,click"}],uplevel_money:[{required:!0,validator:function(e,t,a){var n=c.b.isGreatZero(t);return t?0==t?a(new Error("代理价格(大于0,且最多保留一位小数点)")):isNaN(t)?a(new Error("请输入有效的代理价格")):n?void a():a(new Error("代理价格(大于0,且最多保留一位小数点)")):a(new Error("请输入代理价格"))},trigger:" change,blur,click"}],agent_level:[{required:!0,message:"请选择代理商级别",trigger:"change,click"}],software_rebates:[{required:!0,validator:t,trigger:"change,blur,click"}],hardware_rebates:[{required:!0,validator:t,trigger:"change,blur,click"}],supplies_rebates:[{required:!0,validator:t,trigger:"change,blur,click"}]},listQuery:{page:1,limit:10},total:10}},computed:{CityLevel:function(){return r.i}},created:function(){this.agentFormSet.banner&&(this.imgSrc=this.agentFormSet.banner),this.getSetList()},methods:{getShopphoto:function(e){this.imgurl=e,this.showImg=!0},clickImg:function(){this.showImg=!0,this.isEdit=!0},delt:function(){this.agentFormSet.banner="",this.img_link=""},checkInput:function(e){this.isClickSave&&!this.clickData&&this.$refs[e].validate(function(e){return!!e})},viewImg:function(){this.agentFormSet.banner=this.imgurl,this.showImg=!1},closeImg:function(){this.isEdit||this.clickData||(this.agentFormSet.banner="",this.isEdit=!1),this.showImg=!1},getPhoto:function(e){this.showImg=!1,this.agentFormSet.banner=e.imgSrc,this.img_link=e.img_link},saveAgentSet:function(e){var t=this;this.isClickSave=!0,this.$refs[e].validate(function(a){if(!a)return!1;if(t.agentFormSet.banner){var n={save_agent_cfg:1,agent_type:t.agentFormSet.city_level,uplevel_money:t.agentFormSet.uplevel_money,agent_level:t.agentFormSet.agent_level,software_rebates:t.agentFormSet.software_rebates,hardware_rebates:t.agentFormSet.hardware_rebates,supplies_rebates:t.agentFormSet.supplies_rebates,banner:t.agentFormSet.banner};t.agentFormSet.id&&(n.id=t.agentFormSet.id),t.img_link&&(n.url=t.img_link),Object(g.m)(n).then(function(a){0===a.ret?(t.$refs[e].resetFields(),t.$slnotify({message:"保存成功"}),t.agentFormSet.banner="",t.imgurl="",t.imgSrc="",t.img_link="",t.agentFormSet.id="",t.isClickSave=!1,t.getSetList()):t.$slnotify({message:r.X.toString(a.ret)})})}else t.$slnotify({message:"请上传banner图片"})})},getSetList:function(){var e=this,t={get_list:1,page_size:this.listQuery.limit,page_no:this.listQuery.page};Object(g.l)(t).then(function(t){0===t.ret?(e.setData=t.data.banner_list,e.listQuery.page>1&&0===e.setData.length&&(e.listQuery.page--,e.getSetList()),e.total=t.data.total,e.setData.map(function(e){e.agent_level?e.agent_level_txt=r.c.toString(e.agent_level):e.agent_level_txt="--",e.agent_type?e.agent_type_txt=r.i.toString(e.agent_type):e.agent_type_txt="--"}),e.isEdit=!1):e.$slnotify({message:r.X.toString(t.ret)})})},goToSetDetail:function(e){var t=this;this.$refs.agentFormSet.resetFields(),this.clickData=e;var a={get_cfg_info:1,id:e.id};Object(g.l)(a).then(function(e){0===e.ret?(t.agentFormSet.agent_level=e.data.info.agent_level,t.agentFormSet.city_level=e.data.info.agent_type,t.agentFormSet.uplevel_money=e.data.info.uplevel_money,t.agentFormSet.software_rebates=e.data.info.software_rebates,t.agentFormSet.supplies_rebates=e.data.info.supplies_rebates,t.agentFormSet.hardware_rebates=e.data.info.hardware_rebates,t.img_link=e.data.info.url,t.agentFormSet.id=e.data.info.id,scrollTo(0,0),e.data.info.banner&&(t.agentFormSet.banner=e.data.info.banner,t.imgurl=t.agentFormSet.banner)):t.$slnotify({message:r.X.toString(e.ret)})})},handleSizeChange:function(e){this.listQuery.page=1,this.listQuery.limit=e,this.getSetList()},handleCurrentChange:function(e){this.listQuery.page=e,this.getSetList()}}},u={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"agent-set-offici"},[a("div",{staticClass:"set-content clearfix"},[a("div",{staticClass:"set-left set-item"},[a("div",{staticClass:"set-title"},[e._v("\r\n          代理商设置\r\n        ")]),e._v(" "),a("el-form",{ref:"agentFormSet",staticClass:"set-form",attrs:{model:e.agentFormSet,rules:e.agentRules,"label-width":"135px",inline:!0,"label-position":e.labelPosition}},[a("el-form-item",{attrs:{label:"代理商类型",prop:"city_level"}},[a("el-select",{attrs:{placeholder:"请选择代理商类型"},on:{change:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.city_level,callback:function(t){e.$set(e.agentFormSet,"city_level",t)},expression:"agentFormSet.city_level"}},e._l(e.cityLevelOption,function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"代理商级别",prop:"agent_level"}},[a("el-select",{attrs:{placeholder:"请选择代理商级别"},on:{change:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.agent_level,callback:function(t){e.$set(e.agentFormSet,"agent_level",t)},expression:"agentFormSet.agent_level"}},e._l(e.AgentLevelOption,function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"代理价格",prop:"uplevel_money"}},[a("el-input",{on:{input:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.uplevel_money,callback:function(t){e.$set(e.agentFormSet,"uplevel_money",t)},expression:"agentFormSet.uplevel_money"}}),e._v(" 万\r\n          ")],1),e._v(" "),a("div",[a("el-form-item",{attrs:{label:"软件类产品折扣",prop:"software_rebates"}},[a("el-input",{on:{input:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.software_rebates,callback:function(t){e.$set(e.agentFormSet,"software_rebates",t)},expression:"agentFormSet.software_rebates"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"硬件类产品折扣",prop:"hardware_rebates"}},[a("el-input",{on:{input:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.hardware_rebates,callback:function(t){e.$set(e.agentFormSet,"hardware_rebates",t)},expression:"agentFormSet.hardware_rebates"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"耗材类产品折扣",prop:"supplies_rebates"}},[a("el-input",{on:{input:function(t){e.checkInput("agentFormSet")}},model:{value:e.agentFormSet.supplies_rebates,callback:function(t){e.$set(e.agentFormSet,"supplies_rebates",t)},expression:"agentFormSet.supplies_rebates"}})],1)],1)],1)],1)]),e._v(" "),a("div",{staticClass:"banner-set"},[e._m(0),e._v(" "),a("div",{staticClass:"second-row clearfix"},[a("span",{staticClass:"item-label left"},[e._v("图片 :")]),e._v(" "),e.agentFormSet.banner?a("div",{ref:"info",staticClass:"shopinfo left"},[a("img",{attrs:{src:e.imgbase_url+"/img_get.php?img=1&height=130&width=200&imgname="+e.agentFormSet.banner,alt:"banner图片"}}),e._v(" "),a("span",{staticClass:"text-blue",on:{click:function(t){e.clickImg()}}},[e._v("编辑")]),e._v(" "),a("span",{staticClass:"text-red",on:{click:function(t){e.delt()}}},[e._v("删除")])]):e._e(),e._v(" "),a("img-upload",{attrs:{FreedomCrop:!0,isOfficial:1,isBanner:e.isBanner,isTextBtn:e.isTextBtn,"is-shop":!0},on:{"send-image":e.getShopphoto}})],1)]),e._v(" "),e.showImg?a("big-img",{attrs:{imgSrc:e.imgurl,link:e.img_link,agentLevel:e.agentFormSet.agent_level,cityLevel:e.agentFormSet.city_level},on:{clickit:e.viewImg,closeImg:e.closeImg,getPhoto:e.getPhoto}}):e._e(),e._v(" "),a("div",{staticClass:"footer"},[a("div",{staticClass:"save-b-gradient",on:{click:function(t){e.saveAgentSet("agentFormSet")}}},[e._v("\r\n    保存\r\n  ")])]),e._v(" "),e.total&&e.total>0?a("set-table",{attrs:{setDataList:e.setData,total:e.total},on:{goToSetDetail:e.goToSetDetail,handleSizeChange:e.handleSizeChange,handleCurrentChange:e.handleCurrentChange}}):e._e()],1)},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"change-titlerr"},[t("span",[this._v("Banner设置")])])}]};var _=a("C7Lr")(m,u,!1,function(e){a("woEK"),a("jfjj")},"data-v-f846f644",null);t.default=_.exports},jfjj:function(e,t){},woEK:function(e,t){}});