webpackJsonp([38],{279:function(e,i,t){"use strict";function n(e){c||t(443)}Object.defineProperty(i,"__esModule",{value:!0});var a=t(392),o=t(445),r=t(37),c=!1,s=n,l=Object(r.a)(a.a,o.a,o.b,!1,s,"data-v-2200051c",null);l.options.__file="src\\pages\\pay\\children\\company.vue",i.default=l.exports},392:function(e,i,t){"use strict";var n=t(5),a=t(11);i.a={data:function(){return{flag1:!1,invoice_id:"",invoice:{type:1,componay_title:"",tax_num:"",company_tel:"",company_address:"",bank_name:"",bank_num:"",personal_title:"",isSave:!1,personal_tel:"",email:"",personal_address:"",invoceType:""}}},props:["typeC","invoiceId","order_id"],created:function(){this.initData(),this.invoceType=this.typeC},methods:{initData:function(){var e=this;this.invoice_id=this.invoiceId,n.a.EncSubmit("./php/invoice_get.php",{get_invoice_info:1,userid:window.$data.userid},function(i){var t=i.data.invoice_info;t&&(e.invoice.company_title=t.eleunitinvoice.invoice_title||"",e.invoice.tax_num=t.eleunitinvoice.duty_paragraph||"",e.invoice.company_tel=t.eleunitinvoice.phone||"",e.invoice.company_email=t.eleunitinvoice.email||"",e.invoice.personal_title=t.eleindinvoice.invoice_title||"",e.invoice.personal_tel=t.eleindinvoice.phone||"",e.invoice.email=t.eleindinvoice.email||"")})},confirm:function(){this.flag1=!0},save:function(){var e=this;if(!this.invoice.company_title)return void alert("请输入抬头名称");if(localStorage.setItem("company_title",this.invoice.company_title),!this.invoice.tax_num)return void alert("请输入税号");var i=a.q.ELE_C,t={save:1,userid:window.$data.userid,type:i,duty_paragraph:this.invoice.tax_num,invoice_title:this.invoice.company_title,phone:this.invoice.company_tel,email:this.invoice.company_email};n.a.EncSubmit("./php/invoice_save.php",t,function(t){if(void 0!==e.invoiceId&&""!==e.invoiceId)var n="/createdOrder";else var n="/order";e.$router.push({path:n,query:{type:i,payNow:"yes",order_id:e.order_id}})})},save1:function(){var e=this,i=/^1[3|4|5|7|8][0-9]\d{8}$/;if(!this.invoice.personal_title)return void alert("请输入抬头名称");if(localStorage.setItem("personal_title",this.invoice.personal_title),!i.test(this.invoice.personal_tel))return void alert("电话号码不合法");if(!this.invoice.email)return void alert("电子邮箱");var t=a.q.ELE_P,o={save:1,userid:window.$data.userid,type:t,invoice_title:this.invoice.personal_title,phone:this.invoice.personal_tel,email:this.invoice.email};n.a.EncSubmit("./php/invoice_save.php",o,function(i){if(void 0!==e.invoiceId&&""!==e.invoiceId)var n="/createdOrder";else var n="/order";e.$router.push({path:n,query:{type:t,order_id:e.order_id}})})}}}},443:function(e,i,t){var n=t(444);"string"==typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);t(36)("4f94b43e",n,!1,{})},444:function(e,i,t){i=e.exports=t(35)(!1),i.push([e.i,'\n.bgcolor-white[data-v-2200051c] {\n  background: white;\n  margin-top: 0.13333rem;\n  font-size: 0.4rem;\n  color: #393939;\n  font-family: "PingFang SC";\n}\n.elec[data-v-2200051c] {\n  height: 7.66667rem;\n}\n.text-gray[data-v-2200051c] {\n  /*color: #8A8A8A;*/\n  font-size: 0.4rem;\n  width: 70%;\n}\n.iName[data-v-2200051c] {\n  padding-left: 0.26667rem;\n  height: 1.30667rem;\n  line-height: 1.30667rem;\n}\n.iName span[data-v-2200051c]:nth-child(1) {\n  margin-right: 0.53333rem;\n}\n.iName span[data-v-2200051c] {\n  display: inline-block;\n  width: 20%;\n}\n.dash-line[data-v-2200051c] {\n  margin: 0;\n  border-bottom: 0.05333rem dashed #f1f1f1;\n  width: 90%;\n  margin-left: 0.33333rem;\n}\n.gray-container[data-v-2200051c] {\n  width: 100%;\n  height: 1.01333rem;\n  line-height: 1.01333rem;\n}\n.text-gray img[data-v-2200051c] {\n  width: 0.53333rem;\n  height: 0.53333rem;\n  position: absolute;\n  right: 2.13333rem;\n  top: 5.6rem;\n}\n.gray-container .text-gray[data-v-2200051c] {\n  width: 100%;\n  font-size: 0.34667rem;\n  padding-left: 0.26667rem;\n  color: #8a8a8a;\n}\n.input-container[data-v-2200051c]:last-of-type {\n  margin-bottom: 0.4rem;\n}\n.confirm-paper[data-v-2200051c] {\n  width: 100%;\n  height: 1.17333rem;\n  line-height: 0.90667rem;\n  font-size: 0.34667rem;\n  color: #393939;\n  font-family: "PingFang SC";\n  padding-left: 2.58667rem;\n  position: relative;\n}\n.confirm-paper img[data-v-2200051c] {\n  width: 0.53333rem;\n  height: 0.53333rem;\n  position: absolute;\n  top: 0.18667rem;\n  left: 2.05333rem;\n}\n.color-red[data-v-2200051c] {\n  color: #ff6f06;\n}\n.save[data-v-2200051c],\n.save1[data-v-2200051c] {\n  height: 1.17333rem;\n  width: 8.72rem;\n  background: linear-gradient(60deg, #fe9f39, #ff6f06);\n  color: #fefefe;\n  font-size: 0.48rem;\n  font-family: "PingFang SC";\n  margin: auto;\n  border-radius: 0.08rem;\n  text-align: center;\n  line-height: 1.17333rem;\n  margin-top: 0.74667rem;\n}\n',""])},445:function(e,i,t){"use strict";t.d(i,"a",function(){return n}),t.d(i,"b",function(){return a});var n=function(){var e=this,i=e.$createElement,t=e._self._c||i;return t("div",["company"==e.typeC?t("div",[t("div",{staticClass:"input-container bgcolor-white"},[t("p",{staticClass:"iName"},[t("span",[e._v("抬头名称")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.company_title,expression:"invoice.company_title"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"请输入准确的抬头名称 （必填）"},domProps:{value:e.invoice.company_title},on:{input:function(i){i.target.composing||e.$set(e.invoice,"company_title",i.target.value)}}})]),e._v(" "),t("p",{staticClass:"dash-line"}),e._v(" "),t("p",{staticClass:"iName"},[t("span",[e._v("税号")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.tax_num,expression:"invoice.tax_num"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"请输入税号或社会信用代码"},domProps:{value:e.invoice.tax_num},on:{input:function(i){i.target.composing||e.$set(e.invoice,"tax_num",i.target.value)}}})])]),e._v(" "),t("div",{staticClass:"input-container bgcolor-white"},[t("p",{staticClass:"iName"},[t("span",[e._v("电话号码")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.company_tel,expression:"invoice.company_tel"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"请输入电话号码 "},domProps:{value:e.invoice.company_tel},on:{input:function(i){i.target.composing||e.$set(e.invoice,"company_tel",i.target.value)}}})]),e._v(" "),t("p",{staticClass:"dash-line"}),e._v(" "),t("p",{staticClass:"iName"},[t("span",[e._v("电子邮箱")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.company_email,expression:"invoice.company_email"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"提供邮箱地址以接收电子发票"},domProps:{value:e.invoice.company_email},on:{input:function(i){i.target.composing||e.$set(e.invoice,"company_email",i.target.value)}}})])]),e._v(" "),t("div",{staticClass:"save",on:{click:e.save}},[t("span",[e._v("保存发票抬头")])])]):e._e(),e._v(" "),"company"!=e.typeC?t("div",[t("div",{staticClass:"input-container bgcolor-white"},[t("p",{staticClass:"iName"},[t("span",[e._v("抬头名称")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.personal_title,expression:"invoice.personal_title"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"请输入准确的抬头名称 （必填）"},domProps:{value:e.invoice.personal_title},on:{input:function(i){i.target.composing||e.$set(e.invoice,"personal_title",i.target.value)}}})])]),e._v(" "),t("div",{staticClass:"input-container bgcolor-white"},[t("p",{staticClass:"iName"},[t("span",[e._v("电话号码")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.personal_tel,expression:"invoice.personal_tel"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"请输入电话号码 "},domProps:{value:e.invoice.personal_tel},on:{input:function(i){i.target.composing||e.$set(e.invoice,"personal_tel",i.target.value)}}})]),e._v(" "),t("p",{staticClass:"dash-line"}),e._v(" "),t("p",{staticClass:"iName"},[t("span",[e._v("电子邮箱")]),t("input",{directives:[{name:"model",rawName:"v-model",value:e.invoice.email,expression:"invoice.email"}],staticClass:"text-gray",attrs:{type:"text",placeholder:"提供邮箱地址以接收电子发票"},domProps:{value:e.invoice.email},on:{input:function(i){i.target.composing||e.$set(e.invoice,"email",i.target.value)}}})])]),e._v(" "),t("div",{staticClass:"save1",on:{click:e.save1}},[t("span",[e._v("保存发票抬头")])])]):e._e(),e._v(" "),"company"==e.typeC?t("div",{staticClass:"elec",staticStyle:{width:"100%"}}):e._e(),e._v(" "),"company"!=e.typeC?t("div",{staticStyle:{width:"100%",height:"340px"}}):e._e()])},a=[];n._withStripped=!0}});