(function(t){function e(e){for(var r,a,c=e[0],u=e[1],s=e[2],_=0,p=[];_<c.length;_++)a=c[_],o[a]&&p.push(o[a][0]),o[a]=0;for(r in u)Object.prototype.hasOwnProperty.call(u,r)&&(t[r]=u[r]);d&&d(e);while(p.length)p.shift()();return i.push.apply(i,s||[]),n()}function n(){for(var t,e=0;e<i.length;e++){for(var n=i[e],r=!0,a=1;a<n.length;a++){var c=n[a];0!==o[c]&&(r=!1)}r&&(i.splice(e--,1),t=u(u.s=n[0]))}return t}var r={},a={app:0},o={app:0},i=[];function c(t){return u.p+"static/js/"+({about:"about"}[t]||t)+"."+{about:"cf790421"}[t]+".js"}function u(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,u),n.l=!0,n.exports}u.e=function(t){var e=[],n={about:1};a[t]?e.push(a[t]):0!==a[t]&&n[t]&&e.push(a[t]=new Promise(function(e,n){for(var r="static/css/"+({about:"about"}[t]||t)+"."+{about:"abad2f95"}[t]+".css",o=u.p+r,i=document.getElementsByTagName("link"),c=0;c<i.length;c++){var s=i[c],_=s.getAttribute("data-href")||s.getAttribute("href");if("stylesheet"===s.rel&&(_===r||_===o))return e()}var p=document.getElementsByTagName("style");for(c=0;c<p.length;c++){s=p[c],_=s.getAttribute("data-href");if(_===r||_===o)return e()}var d=document.createElement("link");d.rel="stylesheet",d.type="text/css",d.onload=e,d.onerror=function(e){var r=e&&e.target&&e.target.src||o,i=new Error("Loading CSS chunk "+t+" failed.\n("+r+")");i.request=r,delete a[t],d.parentNode.removeChild(d),n(i)},d.href=o;var f=document.getElementsByTagName("head")[0];f.appendChild(d)}).then(function(){a[t]=0}));var r=o[t];if(0!==r)if(r)e.push(r[2]);else{var i=new Promise(function(e,n){r=o[t]=[e,n]});e.push(r[2]=i);var s,_=document.getElementsByTagName("head")[0],p=document.createElement("script");p.charset="utf-8",p.timeout=120,u.nc&&p.setAttribute("nonce",u.nc),p.src=c(t),s=function(e){p.onerror=p.onload=null,clearTimeout(d);var n=o[t];if(0!==n){if(n){var r=e&&("load"===e.type?"missing":e.type),a=e&&e.target&&e.target.src,i=new Error("Loading chunk "+t+" failed.\n("+r+": "+a+")");i.type=r,i.request=a,n[1](i)}o[t]=void 0}};var d=setTimeout(function(){s({type:"timeout",target:p})},12e4);p.onerror=p.onload=s,_.appendChild(p)}return Promise.all(e)},u.m=t,u.c=r,u.d=function(t,e,n){u.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},u.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},u.t=function(t,e){if(1&e&&(t=u(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(u.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)u.d(n,r,function(e){return t[e]}.bind(null,r));return n},u.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return u.d(e,"a",e),e},u.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},u.p="/",u.oe=function(t){throw console.error(t),t};var s=window["webpackJsonp"]=window["webpackJsonp"]||[],_=s.push.bind(s);s.push=e,s=s.slice();for(var p=0;p<s.length;p++)e(s[p]);var d=_;i.push([0,"chunk-vendors"]),n()})({0:function(t,e,n){t.exports=n("56d7")},1166:function(t,e,n){},"1be0":function(t,e,n){},4360:function(t,e,n){"use strict";n("f4b1"),n("a2f3"),n("e468");var r,a=n("329b"),o=n("f2de"),i=n("7d86"),c=(n("7847"),n("d6b9"),"ADD_CART"),u="REDUCE_CART",s="CLEAR_CART",_="SET_MENUINFO",p="SET_SEATINFO",d="SET_CUSTOMERINFO",f="SET_SHOPINFO",l="SET_FOODBYID",E="SET_MENUBYID",S="UPDATE_CART_NUM",O="SET_USERID",m="CART_SET_SPECDIALOGID",v="ADD_CARTINDEX",h="REDUCE_CARTINDEX",g="ADD_NOSPEC",R="REDUCE_NOSPEC",b="TOGGLE_PACKING",y="ADD_PACKING",N="REDUCE_PACKING",T="ADD_EATING",A="REDUCE_EATING",I="CHANGE_EATPEOPLENUM",D="CHANGE_TABLENO",P="CART_SET_ORDERPAYWAY",w="SET_ORDER_ID",C=n("9975"),k=(r={},Object(i["a"])(r,s,function(t){t.cart_list={},this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,m,function(t,e){t.specDialogId=e.specDialogId}),Object(i["a"])(r,_,function(t,e){}),Object(i["a"])(r,p,function(t,e){t.seatinfo=e.seatinfo}),Object(i["a"])(r,d,function(t,e){t.custominfo=e.custominfo}),Object(i["a"])(r,f,function(t,e){t.shopinfo=e.shopinfo}),Object(i["a"])(r,l,function(t,e){t.goodsMapId=e.goodsMapId}),Object(i["a"])(r,O,function(t,e){t.userid=e.userid}),Object(i["a"])(r,E,function(t,e){t.menuMapId=e.menuMapId}),Object(i["a"])(r,P,function(t,e){t.orderPayWay=e.orderPayWay}),Object(i["a"])(r,w,function(t,e){t.order_id=e.order_id}),Object(i["a"])(r,v,function(t,e){var n,r=e.category_id,o=e.food_id,i=e.specs,c="",u=t.cart_list,s=[],_=null;n=i.find(function(t){return 2==i[i.length-1].type?2===t.type:1===t.type}).selected,i.forEach(function(t){var e=t.spec_id+"#"+t.selected;s.push(e)});var p=[];if(i.forEach(function(t){var e={};if(t.value.length>0){var n=t.selected;c+="#"+t.value[n].id,e["title"]=t.taste,e["spec_value"]=t.value[n].title,p.push(e),2===t.type&&(_=t.value[n].title)}}),c=o+c,u[c])_&&(u[c]["weight"]=_),u[c].num++;else{var d={category_id:r,food_id:o,specs:i,num:1,eating_num:0,is_packing:!1,packing_num:0,selected:n,selectedArr:s,attribute:p,weight:_};a["a"].set(u,c,d)}u[c]&&(u[c]["eating_num"]=0,u[c]["packing_num"]=0,u[c]["is_packing"]=!1),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,h,function(t,e){e.category_id;var n=e.food_id,r=e.specs,o="",i=t.cart_list,c=null;r.forEach(function(t){var e=t.selected;t.value[e]&&(o+="#"+t.value[e].id,2===t.type&&(c=t.value[e].title))}),o=n+o,c&&(i[o]["weight"]=c),i[o]["num"]>0&&i[o].num--,0===i[o]["num"]&&a["a"].delete(i,o),i[o]&&(i[o]["eating_num"]=0,i[o]["packing_num"]=0,i[o]["is_packing"]=!1),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,g,function(t,e){var n=e.category_id,r=e.food_id,o=t.cart_list;if(o[r])o[r].num++;else{var i={category_id:n,food_id:r,num:1,eating_num:0,is_packing:!1,packing_num:0};a["a"].set(o,r,i)}o[r]&&(o[r]["eating_num"]=0,o[r]["packing_num"]=0,o[r]["is_packing"]=!1),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,R,function(t,e){e.category_id;var n=e.food_id,r=t.cart_list;r[n].num--,0===r[n]["num"]&&a["a"].delete(r,n),r[n]&&(r[n]["eating_num"]=0,r[n]["packing_num"]=0,r[n]["is_packing"]=!1),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,u,function(t,e){var n=e.shopCartId,r=t.cart_list;r[n]["num"]--,0===r[n]["num"]&&a["a"].delete(r,n),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,c,function(t,e){var n=e.shopCartId,r=t.cart_list;r[n]["num"]++,this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,S,function(t){var e=t.cart_list,n=t.menuList,r=t.goodsMapId;for(var a in r)if(r.hasOwnProperty(a)){var o=r[a];if(o.specArr&&o.specArr.length>0){var i="";o.specArr.forEach(function(t){if(t.value.length>0){var e=t.selected;i+="#"+t.value[e].id}}),i=o.food_id+i,o.cart_goods_num=C["a"].setTypeNum(o,e).speNum,o.num=o.cart_goods_num}else o.num=(e[a]||{}).num||0;o.num>=o.inventory?o.isShowGray=!0:o.isShowGray=!1}var c=0;n.forEach(function(t){var e=0,n=t.food_list;n.forEach(function(t){(isNaN(t.num)||void 0===t.num)&&(t.num=0),e+=t.num}),t.cart_cate_num=e,c+=t.cart_cate_num});var u=t.eat_people_num,s=C["a"].getTotalPrice(e,u),_=s[0];t.totalPrice=_.toFixed(2),t.shopcartAllNum=c,window.Store.SetGlobalData("shopcart",JSON.stringify(e))}),Object(i["a"])(r,b,function(t,e){var n=e.shopCartId,r=t.cart_list;r[n]["is_packing"]=!r[n]["is_packing"],!0===r[n]["is_packing"]&&0===r[n]["packing_num"]&&(r[n]["packing_num"]=1,r[n]["eating_num"]=r[n]["num"]-1),!1===r[n]["is_packing"]&&(r[n]["packing_num"]=0,r[n]["eating_num"]=r[n]["num"]),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,A,function(t,e){var n=e.shopCartId,r=t.cart_list;0!==r[n]["eating_num"]&&(r[n]["eating_num"]--,r[n]["num"]=r[n]["packing_num"]+r[n]["eating_num"],0===r[n]["num"]&&a["a"].delete(r,n),this.commit("UPDATE_CART_NUM"))}),Object(i["a"])(r,T,function(t,e){var n=e.shopCartId,r=t.cart_list;r[n]["eating_num"]++,r[n]["num"]=r[n]["packing_num"]+r[n]["eating_num"],0===r[n]["num"]&&a["a"].delete(r,n),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,N,function(t,e){var n=e.shopCartId,r=t.cart_list;0!==r[n]["packing_num"]&&(r[n]["packing_num"]--,r[n]["num"]=r[n]["packing_num"]+r[n]["eating_num"],0===r[n]["packing_num"]&&(r[n]["is_packing"]=!1),0===r[n]["num"]&&a["a"].delete(r,n),this.commit("UPDATE_CART_NUM"))}),Object(i["a"])(r,y,function(t,e){var n=e.shopCartId,r=t.cart_list;r[n]["packing_num"]++,r[n]["num"]=r[n]["packing_num"]+r[n]["eating_num"],0===r[n]["num"]&&a["a"].delete(r,n),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,I,function(t,e){var n=e.num;t.eat_people_num=n,window.Store.SetGlobalData("eat_people_num",JSON.stringify(n)),this.commit("UPDATE_CART_NUM")}),Object(i["a"])(r,D,function(t,e){var n=e.tableInfo;t.tableInfo=n,localStorage.setItem("tableInfo",JSON.stringify(n)),this.commit("UPDATE_CART_NUM")}),r),G=n("63e0"),U=n.n(G),j=n("a041");a["a"].use(o["a"]);var L={goodsMapId:{},shopcart:JSON.parse(window.Store.GetGlobalData("shopcart")||"{}"),menuList:[],displayShopcart:[],shopcartAllNum:0,totalPrice:0,seatinfo:{},custominfo:{},shopinfo:{},userid:"",specDialogId:"",cart_list:JSON.parse(window.Store.GetGlobalData("shopcart")||"{}"),eat_people_num:JSON.parse(window.Store.GetGlobalData("eat_people_num")||" 0"),tableInfo:JSON.parse(localStorage.getItem("tableInfo")||"{}"),orderPayWay:j["c"].WECHAT,order_id:""};e["a"]=new o["a"].Store({state:L,getters:void 0,actions:U.a,mutations:k})},"4ec3":function(t,e,n){"use strict";n("f4b1"),n("a2f3"),n("e468"),n("30ba");var r=n("7f43"),a=n.n(r),o=n("905c"),i=n("a041"),c=n("d39c"),u="./php",s="http://api".concat(window.full_url),_=new function(){var t=this,e=u+"/rsa_info.php";t.token=window.Store.GetGlobalData("token",""),t.data_key=window.Store.GetGlobalData("key",""),window.Store.GlobalWatch("key",function(e){console.log(e),t.data_key=e.new_value}),window.Store.GlobalWatch("token",function(e){t.token=e.new_value});var n=function(t){var e=new URLSearchParams;for(var n in t)e.append(n,t[n]);return e};a.a.defaults.timeout=3e4;var r=function(t,e,r){var o=n(e);a.a.post(t,o).then(function(t){t=t||{},t=t.data||{},t.ret=parseInt(t.ret),isNaN(t.ret)&&(t.ret=-1),t.data=t.data||{},r(t)}).catch(function(t){var e="";e=t.response?t.response.status+", "+t.response.statusText:t.message,console.error(t.stack),r({ret:-1,msg:e})})},s=function(t){r(e,{publickey:1},t)},_=function(n,a){var i=o["a"].GetRandString(16);console.log("key:"+i);var c=new JSEncrypt;c.setPublicKey(n);var u=c.encrypt(i),s={save_key:1,is_plain:1,key_enc:u,token:t.token};r(e,s,function(e){t.data_key=i,window.Store.SetGlobalData("key",i),a(e)})};t.EncSubmit=function(e,a,u,p){if(a instanceof Object){p=p||{},p.is_get_param=p.is_get_param||!1,p.encmode=p.encmode||"",u=u||function(t){},t.token||(t.token="T3"+o["a"].GetRandString(14),window.Store.SetGlobalData("token",t.token));var d=function(){var o=n(a).toString();"encrypt1"==p.encmode&&(o=Object(c["c"])(t.data_key,o));var s={token:t.token,encmode:p.encmode,data:o,userid:window.Store.GetGlobalData("USERID"),sign:c["b"].Md5(o+t.data_key)};if(p.is_get_param)return s;r(e+"?"+(new Date).getTime(),s,function(e){return i["e"].USER_NOLOGIN==e.ret?u(e):i["e"].DATA_KEY_NOT_EXIST==e.ret&&(u(e),window.Store.SetGlobalData("key","")),0===e.ret&&"1"==e.crypt&&""!==e.data&&(e.data=JSON.parse(Object(c["a"])(t.data_key,e.data)),delete e.crypt),u(e)})};t.data_key?d():s(function(t){0===t.ret?_(t.data.publickey,function(e){0===t.ret?(console.log(e),d()):u(t)}):u(t)})}},t.DataEncSubmit=function(e,n,r,a){for(var o in n)"string"!==typeof n[o]&&"number"!==typeof n[o]&&"boolean"!==typeof n[o]&&(n[o]=JSON.stringify(n[o]));return t.EncSubmit(u+"/"+e,n,function(t){r&&"function"===typeof r&&r(t)},a)}},p=new function(){var t=this,e=s+"/rsa_info.php";t.token=window.Store.GetGlobalData("token",""),t.data_key=window.Store.GetGlobalData("key",""),window.Store.GlobalWatch("key",function(e){console.log(e),t.data_key=e.new_value}),window.Store.GlobalWatch("token",function(e){t.token=e.new_value});var n=function(t){var e=new URLSearchParams;for(var n in t)e.append(n,t[n]);return e};a.a.defaults.timeout=3e4;var r=function(t,e,r){var o=n(e);a.a.post(t,o).then(function(t){t=t||{},t=t.data||{},t.ret=parseInt(t.ret),isNaN(t.ret)&&(t.ret=-1),t.data=t.data||{},r(t)}).catch(function(t){var e="";e=t.response?t.response.status+", "+t.response.statusText:t.message,console.error(t.stack),r({ret:-1,msg:e})})},u=function(t){r(e,{publickey:1},t)},_=function(n,a){var i=o["a"].GetRandString(16);console.log("key:"+i);var c=new JSEncrypt;c.setPublicKey(n);var u=c.encrypt(i),s={save_key:1,is_plain:1,key_enc:u,token:t.token};r(e,s,function(e){t.data_key=i,window.Store.SetGlobalData("key",i),a(e)})};t.EncSubmit=function(e,a,s,p){if(a instanceof Object){p=p||{},p.is_get_param=p.is_get_param||!1,p.encmode=p.encmode||"",s=s||function(t){},t.token||(t.token="T3"+o["a"].GetRandString(14),window.Store.SetGlobalData("token",t.token));var d=function(){var o=n(a).toString();"encrypt1"==p.encmode&&(o=Object(c["c"])(t.data_key,o));var u={token:t.token,encmode:p.encmode,data:o,userid:window.Store.GetGlobalData("USERID"),sign:c["b"].Md5(o+t.data_key)};if(p.is_get_param)return u;r(e+"?"+(new Date).getTime(),u,function(e){return i["e"].USER_NOLOGIN==e.ret?s(e):i["e"].DATA_KEY_NOT_EXIST==e.ret&&(s(e),window.Store.SetGlobalData("key","")),0===e.ret&&"1"==e.crypt&&""!==e.data&&(e.data=JSON.parse(Object(c["a"])(t.data_key,e.data)),delete e.crypt),s(e)})};t.data_key?d():u(function(t){0===t.ret?_(t.data.publickey,function(e){0===t.ret?(console.log(e),d()):s(t)}):s(t)})}},t.DataEncSubmit=function(e,n,r,a){for(var o in n)"string"!==typeof n[o]&&"number"!==typeof n[o]&&"boolean"!==typeof n[o]&&(n[o]=JSON.stringify(n[o]));return t.EncSubmit(s+"/"+e,n,function(t){r&&"function"===typeof r&&r(t)},a)}},d=function(){s="http://api".concat(window.full_url)},f=_;n.d(e,"a",function(){return l}),n.d(e,"b",function(){return E}),n.d(e,"c",function(){return S});var l=function(){return new Promise(function(t,e){f.DataEncSubmit("syscfg_get.php",{opr:"get_web_cfg"},function(n){0===n.ret?(window.full_url=".".concat(n.data.primary_domain),d(),t(n)):e(n)})})},E=function(t){return new Promise(function(e){p.DataEncSubmit("",t,function(t){e(t)})})},S=function(t){return new Promise(function(e){f.DataEncSubmit("/index.php",t,function(t){e(t)})})}},"56d7":function(t,e,n){"use strict";n.r(e);var r={};n.r(r),n.d(r,"numFix",function(){return b}),n.d(r,"numFixOne",function(){return y});n("b5aa");var a=n("4a1a"),o=(n("4d8f"),n("7847"),n("f4b1"),n("a2f3"),n("e468"),n("2a31")),i=n.n(o);n("babb"),n("a133"),n("cd03"),n("d6b9");Array.prototype.find||Object.defineProperty(Array.prototype,"find",{value:function(t){if(null===this)throw new TypeError('"this" is null or not defined');var e=Object(this),n=e.length>>>0;if("function"!==typeof t)throw new TypeError("predicate must be a function");var r=arguments[1],a=0;while(a<n){var o=e[a];if(t.call(r,o,a,e))return o;a++}}}),HTMLCanvasElement.prototype.toBlob||Object.defineProperty(HTMLCanvasElement.prototype,"toBlob",{value:function(t,e,n){for(var r=atob(this.toDataURL(e,n).split(",")[1]),a=r.length,o=new Uint8Array(a),i=0;i<a;i++)o[i]=r.charCodeAt(i);t(new Blob([o],{type:e||"image/png"}))}}),window.onload=function(){document.addEventListener("gesturestart",function(t){t.preventDefault()}),document.addEventListener("dblclick",function(t){t.preventDefault()}),document.addEventListener("touchstart",function(t){t.touches.length>1&&t.preventDefault()});var t=0;document.addEventListener("touchend",function(e){var n=(new Date).getTime();n-t<=300&&e.preventDefault(),t=n},!1)},Array.prototype.forEach||(Array.prototype.forEach=function(t,e){var n,r;if(null===this)throw new TypeError(" this is null or not defined");var a=Object(this),o=a.length>>>0;if("function"!==typeof t)throw new TypeError(t+" is not a function");arguments.length>1&&(n=e),r=0;while(r<o){var i;r in a&&(i=a[r],t.call(n,i,r,a)),r++}}),function(t){t.forEach(function(t){t.hasOwnProperty("remove")||Object.defineProperty(t,"remove",{configurable:!0,enumerable:!0,writable:!0,value:function(){this.parentNode.removeChild(this)}})})}([Element.prototype,CharacterData.prototype,DocumentType.prototype]);var c=n("329b"),u=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"app"}},[t.isShowLoading?n("div",{staticClass:"loading-content"},[n("van-loading",{attrs:{type:"spinner",color:"black",size:"50px"}})],1):n("router-view")],1)},s=[],_=n("7cd8"),p=n("7ea5"),d=n("f2de"),f={name:"App",computed:Object(p["a"])({},Object(d["c"])({goodsMapId:function(t){return t.goodsMapId},menuList:function(t){return t.menuList}}),{path:function(){return this.$route.path},isShowLoading:function(){return"/loading"!==this.path&&"/login"!==this.path&&("object"===Object(_["a"])(this.goodsMapId)&&"{}"===JSON.stringify(this.goodsMapId)&&!this.isNoGood)},isNoGood:function(){return 0==this.menuList.length}})},l=f,E=(n("5a01"),n("048f")),S=Object(E["a"])(l,u,s,!1,null,"1bd34d2c",null);S.options.__file="App.vue";var O=S.exports,m=n("081a");c["a"].use(m["a"]);var v=new m["a"]({routes:[{path:"/",redirect:"/login"},{path:"/login",name:"login",component:function(){return n.e("about").then(n.bind(null,"d9c9"))}},{path:"/loading",name:"loading",component:function(){return n.e("about").then(n.bind(null,"1764"))}},{path:"/good",name:"good",component:function(){return n.e("about").then(n.bind(null,"de6d"))}},{path:"/shopcart",name:"shopcart",component:function(){return n.e("about").then(n.bind(null,"1225"))}},{path:"/tablepeople",name:"tablepeople",component:function(){return n.e("about").then(n.bind(null,"adc3"))}},{path:"/orderdetail",name:"orderdetail",component:function(){return n.e("about").then(n.bind(null,"634a"))}},{path:"/orderstatus",name:"orderstatus",component:function(){return n.e("about").then(n.bind(null,"20f8"))}}]}),h=n("4360"),g=n("fade");n("a441");(function(t,e){var n=e.documentElement,r=t.devicePixelRatio||1,a=0,o=function(){var e=navigator.userAgent,r=/(iPhone|iPad|iPod)/.test(e);if(!r){var o=t.document.createElement("div");o.style.width="10rem",t.document.body.appendChild(o),t.setTimeout(function(){var e,r=parseFloat(t.getComputedStyle(o).width),i=n.clientWidth;if(r>i||r<i-10){var c=r/i;e=(a/c).toFixed(4),n.style.fontSize=e+"px"}o.remove()},100)}};function i(){e.body?e.body.style.fontSize=12*r+"px":e.addEventListener("DOMContentLoaded",i)}function c(){a=n.clientWidth/10,n.style.fontSize=a+"px",o()}if(i(),c(),t.addEventListener("resize",c),t.addEventListener("pageshow",function(t){t.persisted&&c()}),r>=2){var u=e.createElement("body"),s=e.createElement("div");s.style.border=".5px solid transparent",u.appendChild(s),n.appendChild(u),1===s.offsetHeight&&n.classList.add("hairlines"),n.removeChild(u)}})(window,document);n("1be0");var R=n("4ec3");n("5246");function b(t){var e=Number(t).toFixed(2);return e}function y(t){var e=Number(t).toFixed(1);return e}n("58e0"),n("30ba");var N=n("905c"),T=n("9975"),A=n("a041"),I=void 0,D=function(){var t=window.location.href,e=N["a"].parseQueryString(t),n=T["a"].getShopid(),r=T["a"].getLoginPhone();if(n&&"null"!=n||(n=e.shop_id||"5"),e.phone&&!r){var a=e.phone;T["a"].setLoginPhone(a)}if(e.userid){var o=e.userid;T["a"].setUserid(o)}e.ret&&e.ret<0&&!r&&I.$toast({message:A["e"].toString(e.ret),duration:2e3}),T["a"].setShopid(n);var i={opr:"food_list",shop_id:n,meal_pos:"1"},u={},s={};Object(R["b"])(i).then(function(t){if(0===t.ret){var e=t.data.menu,n=e,r=[];if(n&&n.length>0)for(var a=0;a<n.length;a++){for(var o=!0,i=0;i<r.length;i++){var _={};if(n[a].type==r[i].type){if(_.food_name=n[a].name,_.food_id=n[a].food_id,_.accessory=n[a].accessory,_.accessory_num=n[a].accessory_num,_.inventory=n[a].inventory,_.is_pack=n[a].is_pack,_.price=n[a].price,void 0!==n[a].price.price)_.orgin_price=n[a].price.price;else{void 0!==n[a].price.min_price?_.orgin_price=n[a].price.min_price:void 0!==n[a].price.mid_price?_.orgin_price=n[a].price.mid_price:void 0!==n[a].price.max_price&&(_.orgin_price=n[a].price.max_price),_.isSpec=!0;var p={taste:"份量",type:2,value:[]};if(void 0!==n[a].price.min_price){var d={taste:"小"};d["min_price"]=n[a].price.min_price,p["value"].push(d)}if(void 0!==n[a].price.mid_price){var f={taste:"中"};f["mid_price"]=n[a].price.mid_price,p["value"].push(f)}if(void 0!==n[a].price.max_price){var l={taste:"大"};l["max_price"]=n[a].price.max_price,p["value"].push(l)}n[a].attribute.push(p)}_.attribute=n[a].attribute,_.url=n[a].url,r[i].food_list.push(_),o=!1}}if(o){var E=[],S={};if(n[a].category_id=n[a].type,n[a].category_name=n[a].type_name,S.food_name=n[a].name,S.food_id=n[a].food_id,S.inventory=n[a].inventory,S.accessory_num=n[a].accessory_num,S.accessory=n[a].accessory,S.is_pack=n[a].is_pack,S.price=n[a].price,void 0!==n[a].price.price)S.orgin_price=n[a].price.price;else{void 0!==n[a].price.min_price?S.orgin_price=n[a].price.min_price:void 0!==n[a].price.mid_price?S.orgin_price=n[a].price.mid_price:void 0!==n[a].price.max_price&&(S.orgin_price=n[a].price.max_price),S.isSpec=!0;var O={taste:"份量",type:2,value:[]};if(void 0!==n[a].price.min_price){var m={taste:"小"};m["min_price"]=n[a].price.min_price,O["value"].push(m)}if(void 0!==n[a].price.mid_price){var v={taste:"中"};v["mid_price"]=n[a].price.mid_price,O["value"].push(v)}if(void 0!==n[a].price.max_price){var g={taste:"大"};g["max_price"]=n[a].price.max_price,O["value"].push(g)}n[a].attribute.push(O)}S.attribute=n[a].attribute,S.url=n[a].url,E.push(S),n[a]["food_list"]=E,r.push(n[a])}}r&&r.length>0&&(r.forEach(function(t){s[t.category_id]=t,c["a"].set(t,"cart_cate_num",0);var e=t.food_list;e&&e.length>0&&e.forEach(function(e){if(e.food_id,c["a"].set(e,"cart_goods_num",0),c["a"].set(e,"custom_cate_id",t.category_id),c["a"].set(e,"specArr",[]),c["a"].set(e,"isShowGray",!1),e.cart_goods_num>e.inventory||0==e.inventory?e.isShowGray=!0:e.isShowGray=!1,e.attribute&&e.attribute.length>0&&(e.attribute.forEach(function(t){t.value&&t.value.length>0&&e.specArr.push(t)}),e.specArr.length>0)){var n=0;e.specArr.forEach(function(t){if(c["a"].set(t,"selected",0),n++,t["spec_id"]="sp"+n,t.value&&t.value.length>0)for(var e=[],r=0;r<t.value.length;r++){2!==t.type&&(t["type"]=1),n=parseInt(n+r);var a={};"string"==typeof t.value[r]?a["title"]=t.value[r]:(a=t.value[r],a["title"]=t.value[r].taste),a["id"]=n,e.push(a)}else e=[];t.value=e});var r={};e.specArr.forEach(function(t){c["a"].set(t,"cart_spec_num",0),t.cart_spec_num>=e.foodItem?c["a"].set(e,"isShowGray",!0):c["a"].set(e,"isShowGray",!1),r[t.spec_id]=t}),e.specPriceMapId=r}u[e.food_id]=e})}),h["a"].state.menuList=r),P(u),h["a"].commit("SET_FOODBYID",{goodsMapId:u}),h["a"].commit("SET_MENUBYID",{menuMapId:s}),h["a"].commit("UPDATE_CART_NUM")}})};function P(t){var e=h["a"].state.cart_list||{};for(var n in e)if(e.hasOwnProperty(n)){var r=t[n],a=n.split("#"),o=a[0],i=t[o];if(!r&&!i){c["a"].delete(e,n);continue}}}var w=D;function C(){return k.apply(this,arguments)}function k(){return k=Object(a["a"])(regeneratorRuntime.mark(function t(){return regeneratorRuntime.wrap(function(t){while(1)switch(t.prev=t.next){case 0:return t.prev=0,t.next=3,Object(R["a"])();case 3:t.next=8;break;case 5:t.prev=5,t.t0=t["catch"](0),console.error(t.t0);case 8:new c["a"]({router:v,store:h["a"],render:function(t){return t(O)}}).$mount("#app");case 9:case"end":return t.stop()}},t,this,[[0,5]])})),k.apply(this,arguments)}i.a.polyfill(),c["a"].config.productionTip=!1,c["a"].use(g["a"]),Object.keys(r).forEach(function(t){c["a"].filter(t,r[t])}),v.beforeEach(function(t,e,n){var r=t.path,a=h["a"].state.goodsMapId||{};"/loading"!==r&&"/login"!==r&&"{}"===JSON.stringify(a)&&w(),n()}),C()},"5a01":function(t,e,n){"use strict";var r=n("1166"),a=n.n(r);a.a},"63e0":function(t,e){},"905c":function(t,e,n){"use strict";n("28f9"),n("7847"),n("4d8f");var r=n("7cd8"),a=(n("cd03"),n("30ba"),Object.prototype.toString),o=Object.prototype.hasOwnProperty,i={GetRandom:function(t,e){var n=1e8*Math.random();return Math.floor(n%(e-t+1)+t)},GetRandString:function(t,e){e=e||"0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";for(var n=e.split(""),r="",a=0;a<t;a++)r+=n[this.GetRandom(0,n.length-1)];return r},isTrue:function(t){return!0===t},isFalse:function(t){return!1===t},isUndef:function(t){return void 0===t||null===t},isDef:function(t){return void 0!==t&&null!==t},isPrimitive:function(t){"string"===typeof t||"number"===typeof t||Object(r["a"])(t)},isObject:function(t){return null!==t&&"object"===Object(r["a"])(t)},isPlainObject:function(t){return"[object Object]"===a.call(t)},isRegExp:function(t){return"[object RegExp]"===a.call(t)},isValidArrayIndex:function(t){var e=parseFloat(String(t));return e>=0&&Math.floor(e)===e&&isFinite(t)},toString:function(t){return null===t?"":"object"===Object(r["a"])(t)?JSON.stringify(t,null,2):String(t)},toNumber:function(t){var e=parseFloat(t);return isNaN(e)?t:e},remove:function(t,e){if(t.length){var n=t.indexOf(e);if(n>-1)return t.splice(n,1)}},hasOwn:function(t,e){return o.call(t,e)},toArray:function(t,e){e=e||0;var n=t.length-e,r=new Array(n);while(n--)r[n]=t[n+e];return r},extend:function(t,e){for(var n in e)t[n]=e[n];return t},toObject:function(t){for(var e={},n=0;n<t.length;n++)t[n]&&i.extend(e,t[n]);return e},looseEqual:function(t,e){if(t===e)return!0;var n=i.isObject(t),r=i.isObject(e);if(!n||!r)return!n&&!r&&String(t)===String(e);try{var a=Array.isArray(t),o=Array.isArray(e);if(a&&o)return t.length===e.length&&t.every(function(t,n){return i.looseEqual(t,e[n])});if(a||o)return!1;var c=Object.keys(t),u=Object.keys(e);return c.length===u.length&&c.every(function(n){return i.looseEqual(t[n],e[n])})}catch(s){return!1}},looseIndexOf:function(t,e){for(var n=0;n<t.length;n++)if(i.looseEqual(t[n],e))return n;return-1},getDateDiff:function(t,e){var n=60,r=60*n,a=24*r,o=30*a,i=e-t;if(!(i<0)){var c,u=i/o,s=i/(7*a),_=i/a,p=i/r,d=i/n;return c=u>=1?parseInt(u)+"月后":s>=1?parseInt(s)+"周后":_>=1?parseInt(_)+"天后":p>=1?parseInt(p)+"小时后":d>=1?parseInt(d)+"分钟后":"1分钟后",c}},getStrLen:function(t){return null===t?0:("string"!==typeof t&&(t+=""),t.replace(/[^\x00-\xff]/g,"01").length)},parseQueryString:function(t){for(var e,n=t.split("?")[1]||"",r=n.split("&"),a={},o=0;o<r.length;o++){if(e=r[o].split("="),e[1]){var i=e[1].indexOf("#");i>0&&(e[1]=e[1].substring(0,i))}a[e[0]]=e[1]}return a},forOwn:function(t,e){var n=t||{};for(var r in n)n.hasOwnProperty(r)&&(i.isEmpty(e)||"function"!==typeof e||e(n[r],r,n))},isEmpty:function(t){var e=[null,"undefined",void 0,"N/A","null"];return!(e.indexOf(t)<0)},RecPayState:function(t,e,n,r){window.WebSock.Subscribe("once",{topic:"wxpay_notify@"+t},function(t){console.log("ws,resp",t),0===t.ret&&t.data&&e(t.data)})},RecAliPayState:function(t,e,n,r){window.WebSock.Subscribe("once",{topic:"alipay_notify@"+t},function(t){0===t.ret&&t.data&&e(t.data)})}};e["a"]=i},9975:function(t,e,n){"use strict";n.d(e,"a",function(){return i});n("d75a"),n("7847"),n("441f"),n("d6b9"),n("f4b1"),n("a2f3"),n("e468");var r=n("905c"),a=n("a041"),o=n("4360"),i={getSpecPrice:function(t){var e=[];if(t.specArr!==[]){var n=t.specArr.find(function(t){return 2===t.type});if(!n)return t.orgin_price;var r=n.value[n.selected];for(var a in r)"max_price"!==a&&"mid_price"!==a&&"min_price"!==a||e.push(r[a]);return e.sort(function(t,e){return t-e}),void 0!==e[0]?e[0]:void 0}},setTypeNum:function(t,e){var n="",r=0,a=0;if(t.specArr.length>0&&void 0!==t.specArr||void 0!==t.specArr.value&&null!==t.specArr.value)for(var o in t.specArr.forEach(function(t){var e=t.selected;t.value&&void 0!==t.value[e]&&(n+="#"+t.value[e].id)}),n=t.food_id+n,e)e[o].food_id==t.food_id&&(r+=e[o].num);return a=e[n]?e[n].num:0,{typeNum:a,speNum:r}},getUnitPrice:function(t,e){var n=t||{},o=e||"",i=[],c=n.specArr||[],u=c.find(function(t){return t.type===a["b"].YES})||{},s=(u.value||[]).find(function(t){return t.id===o}),_=function(t){r["a"].forOwn(t,function(t,e){"price"!=e&&"min_price"!=e&&"mid_price"!=e&&"max_price"!=e||i.push(t)})};return n&&n.price&&void 0!==n.price.price?_(n.price):_(s),i},getTotalPrice:function(t,e){var n=e||0,c=o["a"].state.goodsMapId||{},u=o["a"].state.tableInfo.price||0,s=o["a"].state.tableInfo.price_type||0,_=[],p=0;r["a"].forOwn(t,function(t,e){var n=t.food_id,r="",o=c[n],u=t.selected,s={};if(o&&(void 0===o.price.price&&(o.price=o.price||{}),o.price=o.price,0!==(o.specArr||[]).length)){var p=(o.specArr||[]).find(function(t){return t.type===a["b"].YES})||{};p.value&&p.value[u]&&(r=(p.value||[])[u].id)}var d=i.getUnitPrice(o,r);s["unitPrice"]=d,s["food_name"]=o.food_name,s["accessory"]=o.accessory||0,s["accessory_num"]=o.accessory_num||0,_.push(Object.assign({},t,s))}),_.forEach(function(t){var e=t.num*t.unitPrice[0]+t.packing_num*t.accessory*t.accessory_num;p+=e});var d=0;switch(s){case a["d"].PEOPLENUM:d=u*n;break;case a["d"].FIXED:d=u;break;case a["d"].PERCENTAGE:d=u/100*p;break;default:d=0}return p+=d,[p,d]},getShopid:function(){return window.Store.GetGlobalData("SHOPID")},setShopid:function(t){return window.Store.SetGlobalData("SHOPID",t)},delShopid:function(){return window.Store.DeleteGlobalData("SHOPID")},getUserid:function(){return window.Store.GetGlobalData("USERID")},setUserid:function(t){return window.Store.SetGlobalData("USERID",t)},delUserid:function(){return window.Store.DeleteGlobalData("USERID")},getLoginPhone:function(){return window.Store.GetGlobalData("PHONE")},setLoginPhone:function(t){return window.Store.SetGlobalData("PHONE",t)},delLoginPhone:function(){return window.Store.DeleteGlobalData("PHONE")}}},a041:function(t,e,n){"use strict";n.d(e,"e",function(){return o}),n.d(e,"f",function(){return i}),n.d(e,"a",function(){return c}),n.d(e,"d",function(){return u}),n.d(e,"b",function(){return s}),n.d(e,"c",function(){return _});var r,a=n("7d86"),o=(n("f4b1"),n("a2f3"),n("e468"),{OK:0,SYS_ERR:-10001,PARAM_ERR:-10002,SYS_BUSY:-10003,USER_NO_EXIST:-10005,USER_HAD_REG:-10006,USER_PASSWD_ERR:-10007,DATA_PASSWD_ERR:-10008,DATA_CHANGE:-10009,DATA_OWNER_ERR:-10010,DATA_KEY_USED:-10011,USER_NAME_EMPTY:-10012,FILE_NOT_EXIST:-10013,CREATE_ZIPFILE_ERR:-10014,FILE_UPLOAD_ERR:-10015,OPEN_ZIPFILE_ERR:-10016,NO_BAK_FILE:-10017,BAKFILE_DATA_ERR:-10018,BAKFILE_PASSWD_ERR:-10019,USER_NOLOGIN:-10020,USER_LOGINED:-10021,DATA_KEY_NOT_EXIST:-10022,DB_OPR_ERR:-10023,PHONE_IS_EXIST:-10026,USER_SETTING_ERR:-10028,EMAIL_IS_EXIST:-10029,NOT_BIND_PHONE:-10030,CFG_NO_EXIST:-20001,CFG_WRITE_ERR:-20002,GOODS_SERIAL_USED:-20003,CLASS_NAME_USED:-20004,GOODS_NOT_EXIST:-20005,USER_OLD_PASSWD_ERR:-20006,FILE_IS_DIR:-20007,FILE_BAK_ERR:-20008,FILE_PATH_ERR:-20009,FILE_WRITE_ERR:-20010,USER_PERMISSION_ERR:-20011,FILE_NO_EXIST:-20012,LOG_OPR_ERR:-20013,LOG_NO_EXIST:-20014,DB_ERR:-20030,UPDATE_PACK_ERR:-20031,BATCH_FILE_NOT_UNIQ:-20032,NOT_GROUP_FILE:-20033,NAME_IS_EXIST:-20034,HOTEL_NOT_EXIST:-20035,PHONE_ERR:-20037,EMAIL_ERR:-20038,MAIL_CODE_ERR:-20039,COKE_ERR:-20040,SHOP_LABEL_ERR:-20041,PHONE_COKE_ERR:-20042,PHONE_SEND_FAIL:-20043,NEWS_NUM_MAX:-20044,PASSWORD_TWO_SAME:-20045,EMAIL_SEND_FAIL:-20046,DEPARTMENT_IS_EXIST:-20047,IDCARD_ERR:-20048,PHONE_VERIFY_ERR:-20049,USER_NOT_ZC:-20050,ORDER_SEAT_NO:-20051,EMPLOYEE_IS_EXIT:-20060,PHONE_TWO_NOT:-20061,WEIXIN_NO_LOGIN:-20062,WEIXIN_NO_BINDING:-20063,WEIXIN_NO_REBINDING:-20064,INVOICING_NOT:-20065,FOOD_ERR:-20066,INVOICE_IS_ERR:-20067,FEE_MONEY_ERR:-20068,CODE_NOT_SET:-20069,NEWS_NOT_CONTENT:-20070,NEWS_ID_NOT_EP:-20071,FOOD_NO_SPC:-20072,ADDRESS_REPEAT:-20073,RESERVATION_TIME_GO:-20074,AGENT_NO_EXIST:-20075,NEWS_IS_SEND:-20076,PASSWORD_SAME:-20077,EMPLOYEE_NOT_LOGIN:-20078,AGENT_IS_EXIST:-20079,AGENT_NO_CHANGE:-20080,FOOD_IMG_TOOMANY:-30010,FOOD_EXIST:-30011,ORDER_STATUS_ERR:-30012,BROWSER_NOT_WEIXIN:-30013,SHOP_NOT_WEIXIN:-30014,ORDER_NOT_MODIFY:-30015,ORDER_ST_CONFIRMED:-30016,ORDER_ST_PAID:-30017,ORDER_ST_FINISH:-30018,ORDER_ST_CANCEL:-30019,ORDER_ST_TIMEOUT:-30020,ORDER_ST_PRINTED:-30021,ORDER_ST_ERR:-30022,SEAT_NOT_EXIST:-30023,SHOP_NOT_EXIST:-30024,FOOD_NOT_ENOUGH:-30025,ORDER_OPR_ERR:-30026,FOOD_NOT_EXIST:-30027,ORDER_NOT_EXIST:-30028,ORDER_HAD_CHANGE:-30029,PHONE_CODE_LAPSE:-30030,MAIL_TIME_LAPSE:-30031,CATE_NOT_DEL:-30032,DEPARTMENT_NOT_DEL:-30033,EMPLOYEE_IS_FREEZE:-30034,SEAT_IS_EXIST:-30035,SHOP_SUSPEND:-30036,RESERVATION_NOT_EXIST:-30037,FOOD_SALE_OFF:-30038,POSITION_NOT_DEL:-30039,NO_CATE:-30040,IMG_NOT_MORE:-30043,SHOP_IS_FREEZE:-30044,AGENT_IS_FREEZE:-30045,PAY_ERR:-30041,PAY_NEED_PASSWD:-30042,ORDER_IS_URGE:-30052,ORDER_URGE_TIME:-30053,ORDER_NOT_PRINTED:-30054,SHOP_ID_NOT:-20036,WX_NO_SUPPORT:-30055,SVC_ERR_SYS:-100001,SVC_ERR_PARAM:-100002,SVC_ERR_USER_REGISTER:-100003,SVC_ERR_USER_NOT_EXIST:-100004,SVC_ERR_DB:-100005,SVC_ERR_RSA_ENC:-100006,SVC_ERR_RSA_DEC:-100007,SVC_ERR_USER_NOT_LOGIN:-100008,SVC_ERR_LOGIN_ERR:-100009,SVC_ERR_WS_CMD_UNKNOWN:-100010,SVC_ERR_DATA_SEND:-100011,SVC_ERR_CHANNEL_NOT_EXIST:-100012,SVC_ERR_CONNECT_CLOSED:-100013,SVC_ERR_DATA_SIGN:-100020,CERTIFI_REPEAT:-200030,NO_SALESMEN:-200040,SMS_SEND_ERR:-200020,SHOP_IS_AUDIT:-200030,NO_EMPLOYEE:-200040,FOOD_MADE_FINISH:-200050,GOOD_HOT_TOPLIMIT:-200060,SEAT_HAVE_RES:-200070,PROVINCE_ERR:-200080,FROM_ERR:-200090,VERSION_ERR:-200100,SET_ERR:-200110,PAY_PASSWD_ERR:-200120,MONEY_NOT_ENOUGH:-200130,GOODS_NOT_ENOUGH:-200140,PASSWORD_ERR_TOOMANY:-200150,RESOURCES_NOT_ENOUGH:-200160,NOT_BIND_TERM:-200170,LOGIN_LAPSE:-200180,EXPRESS_COMPANY_ERR:-200190,ADDRESS_NOT:-200200,GOODS_SALE_OFF:-200210,EVA_IS_EXIST:-200220,GOODS_SPEC_CHANGE:-200230,USED_BY_OTHERS:-200310,code:(r={0:"正确","-10001":"系统出错","-10002":"参数出错","-10003":"系统忙","-10005":"用户不存在","-10006":"登录手机号已被注册","-10007":"账号或登录密码出错","-10008":"密码错误","-10009":"数据已有变动（刷新后再修改）","-10010":"不是当前用户的数据","-10011":"key已被使用","-10012":"用户名不能为空","-10013":"文件不存在","-10014":"创建zip压缩文件出错","-10015":"文件上传出错","-10016":"打开压缩文件出错","-10017":"文件格式出错（不是备份文件）","-10018":"备份文件中数据出错","-10019":"备份文件密码出错","-10020":"用户未登录","-10021":"用户已经登录过","-10022":"通讯用key不存在","-10023":"数据库操作出错","-10026":"手机号已被使用","-10028":"设置登录用户出错","-10029":"邮箱已被使用","-10030":"不是绑定的手机号码","-20001":"配置文件不存在","-20002":"配置文件写入出错","-20003":"货品编号已被使用","-20004":"类别名已存在","-20005":"货品不存在","-20006":"原密码错误","-20007":"是个目录","-20008":"备份文件出错","-20009":"路径出错","-20010":"文件写入出错","-20011":"操作权限不足","-20012":"文件不存在","-20013":"日志操作出错","-20014":"日志不存在","-20030":"数据库操作出错","-20031":"升级包出错","-20032":"同批次文件中存在相同文件","-20033":"没有组文件","-20034":"名称已存在","-20035":"酒店不存在","-20037":"电话号码不正确","-20038":"邮箱不正确","-20039":"邮箱密文不正确","-20040":"验证码不正确","-20041":"标签名为空","-20042":"手机验证码不正确","-20043":"手机发送失败","-20044":"发送消息数超出限制","-20045":"2次输入的密码不一样","-20046":"邮箱发送失败","-20047":"部门名称重复","-20048":"身份证号码不正确","-20049":"手机验证过程出错","-20050":"用户没有注册","-20051":"订单中含有该餐桌","-20060":"该员工已经邀请过了","-20061":"俩次输入的电话号码不一样","-20062":"此微信未绑定,不能登录","-20063":"此微信已绑定账号,不能重复绑定","-20064":"该账号已绑定其它微信号","-20065":"用户没开票","-20066":"餐品出错","-20067":"发票错误","-20068":"减免金额出错","-20069":"二维码未设置","-20070":"系统消息无内容","-20071":"消息id不能为空","-20072":"该餐品无份量规格","-20073":"地址已存在","-20074":"预约时间不在当前时间之后","-20075":"区域商代理不存在","-20076":"系统消息已发送","-20077":"原密码和新密码一样","-20078":"该员工无登录权限","-20079":"代理商区域重复","-20080":"此代理商下有店铺，不能修改","-200030":"未到审核级或已被同级审核","-30010":"图片过多","-30011":"餐品名称已存在","-30012":"订单状态出错","-30013":"请在微信中打开","-30014":"店铺不存在","-30015":"订单处于不可更改阶段，可重新下单","-30016":"订单已确认，不能修改","-30017":"订单已支付，不能修改","-30018":"订单已完成，不能修改","-30019":"订单已作废，不能修改","-30020":"订单超过7天，不能评价","-30021":"订单已出单，不能修改","-30022":"订单出错，请重新下单","-30023":"餐桌号不存在","-30024":"店铺不存在","-30025":"餐品存量不够","-30026":"订单操作出错","-30027":"餐品不存在","-30028":"订单不存在","-30029":"订单已有变动，请刷新后再操作","-30030":"手机验证码超时","-30031":"手机验证码超时","-30032":"此分类下有商品，不能删除","-30033":"此部门下有员工，不能删除","-30034":"此部门员工已被冻结","-30035":"餐桌号已存在","-30036":"系统暂停使用","-30037":"预约不存在","-30038":"餐品已下架","-30039":"此职位下有员工，不能删除","-30040":"没有此分类名","-30043":"一个月内只能更换一次店铺Logo","-30044":"此店铺已被冻结","-30045":"此代理商已被冻结","-30041":"支付取消或失败","-30042":"支付需要密码","-30052":"订单已催单","-30053":"催单时间还未到","-30054":"订单还未下单","-20036":"没有餐馆ID","-30055":"未开通微信支付","-100001":"内部错误","-100002":"参数错误","-100003":"注册用户出错","-100004":"用户不存在","-100005":"数据库出错","-100006":"rsa加密出错","-100007":"rsa解密出错","-100008":"用户未登录","-100009":"用户登录出错","-100010":"未知的websocket处理命令","-100011":"数据发送出错","-100012":"订阅频道不存在","-100013":"连接已断开","-100020":"签名出错","-200040":"销售人员不存在","-200020":"短信发送出错"},Object(a["a"])(r,"-200030","未到审核级或已被同级审核"),Object(a["a"])(r,"-200040","员工不存在"),Object(a["a"])(r,"-200050","该订单已完成制作"),Object(a["a"])(r,"-200060","热卖商品超过上限"),Object(a["a"])(r,"-200070","餐桌已被预订"),Object(a["a"])(r,"-200080","代区域不合法"),Object(a["a"])(r,"-200090","来源已经被运用"),Object(a["a"])(r,"-200100","没有版本号存在,请联系管理员"),Object(a["a"])(r,"-200110","代理商设置重复"),Object(a["a"])(r,"-200120","支付密码错误"),Object(a["a"])(r,"-200130","余额不足"),Object(a["a"])(r,"-200140","商品库存不足"),Object(a["a"])(r,"-200150","密码出错次数过多"),Object(a["a"])(r,"-200160","登录授权数不足"),Object(a["a"])(r,"-200170","不是绑定终端"),Object(a["a"])(r,"-200180","登录超时"),Object(a["a"])(r,"-200190","快递公司错误"),Object(a["a"])(r,"-200200","地址未设置"),Object(a["a"])(r,"-200210","商品已下架"),Object(a["a"])(r,"-200220","请勿重复评价"),Object(a["a"])(r,"-200230","商品信息已改变"),Object(a["a"])(r,"-200310","该账号正在使用，无法重复登录"),r),toString:function(t){return t=parseInt(t||0),this.code[t]||"未知["+t+"]"}}),i={YES:1,NO:0,code:{1:"是",0:"否"},toString:function(t){return t=parseInt(t||0),this.code[t]||"未知["+t+"]"}},c={NO:0,YES:1,code:{0:"否",1:"是"},toString:function(t){return t=parseInt(t||0),this.code[t]||"未知["+t+"]"}},u={NO:0,PEOPLENUM:1,FIXED:2,PERCENTAGE:3,code:{0:"无餐位费",1:"按就餐人数",2:"固定餐位费",3:"订单金额百分比"},toString:function(t){return t=parseInt(t||0),this.code[t]||"未知["+t+"]"}},s={YES:2,NO:1,code:{1:"不影响价格",2:"影响价格"},toString:function(t){return t=parseInt(t||2),this.code[t]||"未知["+t+"]"}},_={CASH:1,WECHAT:2,ALIPAY:3,AFE:6,code:{1:"现金支付",2:"微信支付",3:"支付宝支付",6:"餐后支付"},toString:function(t){return t=parseInt(t||0),this.code[t]||"未知["+t+"]"}}},d39c:function(t,e,n){"use strict";n.d(e,"c",function(){return i}),n.d(e,"a",function(){return c});n("cd03"),n("30ba"),n("c5c8");var r=n("019a"),a=n.n(r),o=new function(){this.Encmode=Object.freeze({AES:"aes"}),this.Md5=function(t){return a()(t)},this.Rsa=new function(){var t=null;this.SetPublicKey=function(e){t=new JSEncrypt,t.setPublicKey(e)},this.Encrypt=function(e){return null===t?e:t.encrypt(e)},this.Decrypt=function(e){return null===t?e:t.decrypt(e)}},this.Aes=new function(){var t=null,e=null;this.SetKey=function(n){n=o.Md5(n).substr(0,16),t=CryptoJS.enc.Utf8.parse(n),e=t,CryptoJS.pad.ZeroPadding||console.log("CryptoJS.pad.ZeroPadding lose")},this.Encrypt=function(n){return null===t?n:CryptoJS.AES.encrypt(n,t,{iv:e,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.ZeroPadding}).toString()},this.Decrypt=function(n){return null===t?n:CryptoJS.AES.decrypt(n,t,{iv:e,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.ZeroPadding}).toString(CryptoJS.enc.Utf8)}},this.Encrypt=function(t,e){try{return n(t,e)}catch(r){console.log(r.stack)}return""},this.Decrypt=function(t,e){try{return r(t,e)}catch(n){console.log(n.stack)}return""};var t={doit:function(t,e){var n=e.split(""),r=n.length;t+=r;for(var a=0;a<r;a++){var o=r-a-1,i=this.Rand(t,a,0,o),c=n[o];n[o]=n[i],n[i]=c}return n.join("")},undo:function(t,e){var n=e.split(""),r=n.length;t+=r;for(var a=r-1;a>=0;a--){var o=r-a-1,i=this.Rand(t,a,0,o),c=n[i];n[i]=n[o],n[o]=c}return n.join("")},Rand:function(t,e,n,r){var a=268435455&~(262147*t*e),o=r+1-n;o<=0&&(o=1);var i=(n+a)%o;return i}},e=function(t){for(var e=0,n=0;n<t.length;n++)e=e+t.charCodeAt(n)&2147483647;return 2147483647&e},n=function(n,r){for(var a=encodeURIComponent(n),o=encodeURIComponent(r),i="",c=0,u=0;u<o.length;u++){var s=(o.charCodeAt(u)^a.charCodeAt(c)).toString(16);s.length<2&&(s="0"+s),i+=s,c++,c==a.length&&(c=0)}var _=e(a);return t.doit(_,i)},r=function(n,r){for(var a=encodeURIComponent(n),o=e(a),i=t.undo(o,r),c="",u=0,s=0;s<i.length;s+=2){var _=parseInt("0x"+i.charAt(s)+i.charAt(s+1)),p=_^a.charCodeAt(u);c+=String.fromCharCode(p),u++,u==a.length&&(u=0)}return decodeURIComponent(c)}};e["b"]=o;var i=function(t,e){try{return o.Encrypt(t,e)}catch(n){console.log(n.stack)}return""},c=function(t,e){try{return o.Decrypt(t,e)}catch(n){console.log(n.stack)}return""}}});