webpackJsonp([44],{"2etM":function(t,e){},LqYc:function(t,e,n){var r,a;a=function(){function t(t){this.mode=n.MODE_8BIT_BYTE,this.data=t,this.parsedData=[];for(var e=0,r=this.data.length;e<r;e++){var a=[],i=this.data.charCodeAt(e);i>65536?(a[0]=240|(1835008&i)>>>18,a[1]=128|(258048&i)>>>12,a[2]=128|(4032&i)>>>6,a[3]=128|63&i):i>2048?(a[0]=224|(61440&i)>>>12,a[1]=128|(4032&i)>>>6,a[2]=128|63&i):i>128?(a[0]=192|(1984&i)>>>6,a[1]=128|63&i):a[0]=i,this.parsedData.push(a)}this.parsedData=Array.prototype.concat.apply([],this.parsedData),this.parsedData.length!=this.data.length&&(this.parsedData.unshift(191),this.parsedData.unshift(187),this.parsedData.unshift(239))}function e(t,e){this.typeNumber=t,this.errorCorrectLevel=e,this.modules=null,this.moduleCount=0,this.dataCache=null,this.dataList=[]}t.prototype={getLength:function(t){return this.parsedData.length},write:function(t){for(var e=0,n=this.parsedData.length;e<n;e++)t.put(this.parsedData[e],8)}},e.prototype={addData:function(e){var n=new t(e);this.dataList.push(n),this.dataCache=null},isDark:function(t,e){if(t<0||this.moduleCount<=t||e<0||this.moduleCount<=e)throw new Error(t+","+e);return this.modules[t][e]},getModuleCount:function(){return this.moduleCount},make:function(){this.makeImpl(!1,this.getBestMaskPattern())},makeImpl:function(t,n){this.moduleCount=4*this.typeNumber+17,this.modules=new Array(this.moduleCount);for(var r=0;r<this.moduleCount;r++){this.modules[r]=new Array(this.moduleCount);for(var a=0;a<this.moduleCount;a++)this.modules[r][a]=null}this.setupPositionProbePattern(0,0),this.setupPositionProbePattern(this.moduleCount-7,0),this.setupPositionProbePattern(0,this.moduleCount-7),this.setupPositionAdjustPattern(),this.setupTimingPattern(),this.setupTypeInfo(t,n),this.typeNumber>=7&&this.setupTypeNumber(t),null==this.dataCache&&(this.dataCache=e.createData(this.typeNumber,this.errorCorrectLevel,this.dataList)),this.mapData(this.dataCache,n)},setupPositionProbePattern:function(t,e){for(var n=-1;n<=7;n++)if(!(t+n<=-1||this.moduleCount<=t+n))for(var r=-1;r<=7;r++)e+r<=-1||this.moduleCount<=e+r||(this.modules[t+n][e+r]=0<=n&&n<=6&&(0==r||6==r)||0<=r&&r<=6&&(0==n||6==n)||2<=n&&n<=4&&2<=r&&r<=4)},getBestMaskPattern:function(){for(var t=0,e=0,n=0;n<8;n++){this.makeImpl(!0,n);var r=g.getLostPoint(this);(0==n||t>r)&&(t=r,e=n)}return e},createMovieClip:function(t,e,n){var r=t.createEmptyMovieClip(e,n);this.make();for(var a=0;a<this.modules.length;a++)for(var i=1*a,o=0;o<this.modules[a].length;o++){var s=1*o;this.modules[a][o]&&(r.beginFill(0,100),r.moveTo(s,i),r.lineTo(s+1,i),r.lineTo(s+1,i+1),r.lineTo(s,i+1),r.endFill())}return r},setupTimingPattern:function(){for(var t=8;t<this.moduleCount-8;t++)null==this.modules[t][6]&&(this.modules[t][6]=t%2==0);for(var e=8;e<this.moduleCount-8;e++)null==this.modules[6][e]&&(this.modules[6][e]=e%2==0)},setupPositionAdjustPattern:function(){for(var t=g.getPatternPosition(this.typeNumber),e=0;e<t.length;e++)for(var n=0;n<t.length;n++){var r=t[e],a=t[n];if(null==this.modules[r][a])for(var i=-2;i<=2;i++)for(var o=-2;o<=2;o++)this.modules[r+i][a+o]=-2==i||2==i||-2==o||2==o||0==i&&0==o}},setupTypeNumber:function(t){for(var e=g.getBCHTypeNumber(this.typeNumber),n=0;n<18;n++){var r=!t&&1==(e>>n&1);this.modules[Math.floor(n/3)][n%3+this.moduleCount-8-3]=r}for(n=0;n<18;n++){r=!t&&1==(e>>n&1);this.modules[n%3+this.moduleCount-8-3][Math.floor(n/3)]=r}},setupTypeInfo:function(t,e){for(var n=this.errorCorrectLevel<<3|e,r=g.getBCHTypeInfo(n),a=0;a<15;a++){var i=!t&&1==(r>>a&1);a<6?this.modules[a][8]=i:a<8?this.modules[a+1][8]=i:this.modules[this.moduleCount-15+a][8]=i}for(a=0;a<15;a++){i=!t&&1==(r>>a&1);a<8?this.modules[8][this.moduleCount-a-1]=i:a<9?this.modules[8][15-a-1+1]=i:this.modules[8][15-a-1]=i}this.modules[this.moduleCount-8][8]=!t},mapData:function(t,e){for(var n=-1,r=this.moduleCount-1,a=7,i=0,o=this.moduleCount-1;o>0;o-=2)for(6==o&&o--;;){for(var s=0;s<2;s++)if(null==this.modules[r][o-s]){var l=!1;i<t.length&&(l=1==(t[i]>>>a&1)),g.getMask(e,r,o-s)&&(l=!l),this.modules[r][o-s]=l,-1==--a&&(i++,a=7)}if((r+=n)<0||this.moduleCount<=r){r-=n,n=-n;break}}}},e.PAD0=236,e.PAD1=17,e.createData=function(t,n,r){for(var a=v.getRSBlocks(t,n),i=new m,o=0;o<r.length;o++){var s=r[o];i.put(s.mode,4),i.put(s.getLength(),g.getLengthInBits(s.mode,t)),s.write(i)}var l=0;for(o=0;o<a.length;o++)l+=a[o].dataCount;if(i.getLengthInBits()>8*l)throw new Error("code length overflow. ("+i.getLengthInBits()+">"+8*l+")");for(i.getLengthInBits()+4<=8*l&&i.put(0,4);i.getLengthInBits()%8!=0;)i.putBit(!1);for(;!(i.getLengthInBits()>=8*l||(i.put(e.PAD0,8),i.getLengthInBits()>=8*l));)i.put(e.PAD1,8);return e.createBytes(i,a)},e.createBytes=function(t,e){for(var n=0,r=0,a=0,i=new Array(e.length),o=new Array(e.length),s=0;s<e.length;s++){var l=e[s].dataCount,h=e[s].totalCount-l;r=Math.max(r,l),a=Math.max(a,h),i[s]=new Array(l);for(var u=0;u<i[s].length;u++)i[s][u]=255&t.buffer[u+n];n+=l;var c=g.getErrorCorrectPolynomial(h),A=new p(i[s],c.getLength()-1).mod(c);o[s]=new Array(c.getLength()-1);for(u=0;u<o[s].length;u++){var d=u+A.getLength()-o[s].length;o[s][u]=d>=0?A.get(d):0}}var f=0;for(u=0;u<e.length;u++)f+=e[u].totalCount;var v=new Array(f),m=0;for(u=0;u<r;u++)for(s=0;s<e.length;s++)u<i[s].length&&(v[m++]=i[s][u]);for(u=0;u<a;u++)for(s=0;s<e.length;s++)u<o[s].length&&(v[m++]=o[s][u]);return v};for(var n={MODE_NUMBER:1,MODE_ALPHA_NUM:2,MODE_8BIT_BYTE:4,MODE_KANJI:8},a={L:1,M:0,Q:3,H:2},i=0,o=1,s=2,l=3,h=4,u=5,c=6,A=7,g={PATTERN_POSITION_TABLE:[[],[6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],[6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],[6,34,62,90],[6,28,50,72,94],[6,26,50,74,98],[6,30,54,78,102],[6,28,54,80,106],[6,32,58,84,110],[6,30,58,86,114],[6,34,62,90,118],[6,26,50,74,98,122],[6,30,54,78,102,126],[6,26,52,78,104,130],[6,30,56,82,108,134],[6,34,60,86,112,138],[6,30,58,86,114,142],[6,34,62,90,118,146],[6,30,54,78,102,126,150],[6,24,50,76,102,128,154],[6,28,54,80,106,132,158],[6,32,58,84,110,136,162],[6,26,54,82,110,138,166],[6,30,58,86,114,142,170]],G15:1335,G18:7973,G15_MASK:21522,getBCHTypeInfo:function(t){for(var e=t<<10;g.getBCHDigit(e)-g.getBCHDigit(g.G15)>=0;)e^=g.G15<<g.getBCHDigit(e)-g.getBCHDigit(g.G15);return(t<<10|e)^g.G15_MASK},getBCHTypeNumber:function(t){for(var e=t<<12;g.getBCHDigit(e)-g.getBCHDigit(g.G18)>=0;)e^=g.G18<<g.getBCHDigit(e)-g.getBCHDigit(g.G18);return t<<12|e},getBCHDigit:function(t){for(var e=0;0!=t;)e++,t>>>=1;return e},getPatternPosition:function(t){return g.PATTERN_POSITION_TABLE[t-1]},getMask:function(t,e,n){switch(t){case i:return(e+n)%2==0;case o:return e%2==0;case s:return n%3==0;case l:return(e+n)%3==0;case h:return(Math.floor(e/2)+Math.floor(n/3))%2==0;case u:return e*n%2+e*n%3==0;case c:return(e*n%2+e*n%3)%2==0;case A:return(e*n%3+(e+n)%2)%2==0;default:throw new Error("bad maskPattern:"+t)}},getErrorCorrectPolynomial:function(t){for(var e=new p([1],0),n=0;n<t;n++)e=e.multiply(new p([1,d.gexp(n)],0));return e},getLengthInBits:function(t,e){if(1<=e&&e<10)switch(t){case n.MODE_NUMBER:return 10;case n.MODE_ALPHA_NUM:return 9;case n.MODE_8BIT_BYTE:case n.MODE_KANJI:return 8;default:throw new Error("mode:"+t)}else if(e<27)switch(t){case n.MODE_NUMBER:return 12;case n.MODE_ALPHA_NUM:return 11;case n.MODE_8BIT_BYTE:return 16;case n.MODE_KANJI:return 10;default:throw new Error("mode:"+t)}else{if(!(e<41))throw new Error("type:"+e);switch(t){case n.MODE_NUMBER:return 14;case n.MODE_ALPHA_NUM:return 13;case n.MODE_8BIT_BYTE:return 16;case n.MODE_KANJI:return 12;default:throw new Error("mode:"+t)}}},getLostPoint:function(t){for(var e=t.getModuleCount(),n=0,r=0;r<e;r++)for(var a=0;a<e;a++){for(var i=0,o=t.isDark(r,a),s=-1;s<=1;s++)if(!(r+s<0||e<=r+s))for(var l=-1;l<=1;l++)a+l<0||e<=a+l||0==s&&0==l||o==t.isDark(r+s,a+l)&&i++;i>5&&(n+=3+i-5)}for(r=0;r<e-1;r++)for(a=0;a<e-1;a++){var h=0;t.isDark(r,a)&&h++,t.isDark(r+1,a)&&h++,t.isDark(r,a+1)&&h++,t.isDark(r+1,a+1)&&h++,0!=h&&4!=h||(n+=3)}for(r=0;r<e;r++)for(a=0;a<e-6;a++)t.isDark(r,a)&&!t.isDark(r,a+1)&&t.isDark(r,a+2)&&t.isDark(r,a+3)&&t.isDark(r,a+4)&&!t.isDark(r,a+5)&&t.isDark(r,a+6)&&(n+=40);for(a=0;a<e;a++)for(r=0;r<e-6;r++)t.isDark(r,a)&&!t.isDark(r+1,a)&&t.isDark(r+2,a)&&t.isDark(r+3,a)&&t.isDark(r+4,a)&&!t.isDark(r+5,a)&&t.isDark(r+6,a)&&(n+=40);var u=0;for(a=0;a<e;a++)for(r=0;r<e;r++)t.isDark(r,a)&&u++;return n+=10*(Math.abs(100*u/e/e-50)/5)}},d={glog:function(t){if(t<1)throw new Error("glog("+t+")");return d.LOG_TABLE[t]},gexp:function(t){for(;t<0;)t+=255;for(;t>=256;)t-=255;return d.EXP_TABLE[t]},EXP_TABLE:new Array(256),LOG_TABLE:new Array(256)},f=0;f<8;f++)d.EXP_TABLE[f]=1<<f;for(f=8;f<256;f++)d.EXP_TABLE[f]=d.EXP_TABLE[f-4]^d.EXP_TABLE[f-5]^d.EXP_TABLE[f-6]^d.EXP_TABLE[f-8];for(f=0;f<255;f++)d.LOG_TABLE[d.EXP_TABLE[f]]=f;function p(t,e){if(void 0==t.length)throw new Error(t.length+"/"+e);for(var n=0;n<t.length&&0==t[n];)n++;this.num=new Array(t.length-n+e);for(var r=0;r<t.length-n;r++)this.num[r]=t[r+n]}function v(t,e){this.totalCount=t,this.dataCount=e}function m(){this.buffer=[],this.length=0}p.prototype={get:function(t){return this.num[t]},getLength:function(){return this.num.length},multiply:function(t){for(var e=new Array(this.getLength()+t.getLength()-1),n=0;n<this.getLength();n++)for(var r=0;r<t.getLength();r++)e[n+r]^=d.gexp(d.glog(this.get(n))+d.glog(t.get(r)));return new p(e,0)},mod:function(t){if(this.getLength()-t.getLength()<0)return this;for(var e=d.glog(this.get(0))-d.glog(t.get(0)),n=new Array(this.getLength()),r=0;r<this.getLength();r++)n[r]=this.get(r);for(r=0;r<t.getLength();r++)n[r]^=d.gexp(d.glog(t.get(r))+e);return new p(n,0).mod(t)}},v.RS_BLOCK_TABLE=[[1,26,19],[1,26,16],[1,26,13],[1,26,9],[1,44,34],[1,44,28],[1,44,22],[1,44,16],[1,70,55],[1,70,44],[2,35,17],[2,35,13],[1,100,80],[2,50,32],[2,50,24],[4,25,9],[1,134,108],[2,67,43],[2,33,15,2,34,16],[2,33,11,2,34,12],[2,86,68],[4,43,27],[4,43,19],[4,43,15],[2,98,78],[4,49,31],[2,32,14,4,33,15],[4,39,13,1,40,14],[2,121,97],[2,60,38,2,61,39],[4,40,18,2,41,19],[4,40,14,2,41,15],[2,146,116],[3,58,36,2,59,37],[4,36,16,4,37,17],[4,36,12,4,37,13],[2,86,68,2,87,69],[4,69,43,1,70,44],[6,43,19,2,44,20],[6,43,15,2,44,16],[4,101,81],[1,80,50,4,81,51],[4,50,22,4,51,23],[3,36,12,8,37,13],[2,116,92,2,117,93],[6,58,36,2,59,37],[4,46,20,6,47,21],[7,42,14,4,43,15],[4,133,107],[8,59,37,1,60,38],[8,44,20,4,45,21],[12,33,11,4,34,12],[3,145,115,1,146,116],[4,64,40,5,65,41],[11,36,16,5,37,17],[11,36,12,5,37,13],[5,109,87,1,110,88],[5,65,41,5,66,42],[5,54,24,7,55,25],[11,36,12],[5,122,98,1,123,99],[7,73,45,3,74,46],[15,43,19,2,44,20],[3,45,15,13,46,16],[1,135,107,5,136,108],[10,74,46,1,75,47],[1,50,22,15,51,23],[2,42,14,17,43,15],[5,150,120,1,151,121],[9,69,43,4,70,44],[17,50,22,1,51,23],[2,42,14,19,43,15],[3,141,113,4,142,114],[3,70,44,11,71,45],[17,47,21,4,48,22],[9,39,13,16,40,14],[3,135,107,5,136,108],[3,67,41,13,68,42],[15,54,24,5,55,25],[15,43,15,10,44,16],[4,144,116,4,145,117],[17,68,42],[17,50,22,6,51,23],[19,46,16,6,47,17],[2,139,111,7,140,112],[17,74,46],[7,54,24,16,55,25],[34,37,13],[4,151,121,5,152,122],[4,75,47,14,76,48],[11,54,24,14,55,25],[16,45,15,14,46,16],[6,147,117,4,148,118],[6,73,45,14,74,46],[11,54,24,16,55,25],[30,46,16,2,47,17],[8,132,106,4,133,107],[8,75,47,13,76,48],[7,54,24,22,55,25],[22,45,15,13,46,16],[10,142,114,2,143,115],[19,74,46,4,75,47],[28,50,22,6,51,23],[33,46,16,4,47,17],[8,152,122,4,153,123],[22,73,45,3,74,46],[8,53,23,26,54,24],[12,45,15,28,46,16],[3,147,117,10,148,118],[3,73,45,23,74,46],[4,54,24,31,55,25],[11,45,15,31,46,16],[7,146,116,7,147,117],[21,73,45,7,74,46],[1,53,23,37,54,24],[19,45,15,26,46,16],[5,145,115,10,146,116],[19,75,47,10,76,48],[15,54,24,25,55,25],[23,45,15,25,46,16],[13,145,115,3,146,116],[2,74,46,29,75,47],[42,54,24,1,55,25],[23,45,15,28,46,16],[17,145,115],[10,74,46,23,75,47],[10,54,24,35,55,25],[19,45,15,35,46,16],[17,145,115,1,146,116],[14,74,46,21,75,47],[29,54,24,19,55,25],[11,45,15,46,46,16],[13,145,115,6,146,116],[14,74,46,23,75,47],[44,54,24,7,55,25],[59,46,16,1,47,17],[12,151,121,7,152,122],[12,75,47,26,76,48],[39,54,24,14,55,25],[22,45,15,41,46,16],[6,151,121,14,152,122],[6,75,47,34,76,48],[46,54,24,10,55,25],[2,45,15,64,46,16],[17,152,122,4,153,123],[29,74,46,14,75,47],[49,54,24,10,55,25],[24,45,15,46,46,16],[4,152,122,18,153,123],[13,74,46,32,75,47],[48,54,24,14,55,25],[42,45,15,32,46,16],[20,147,117,4,148,118],[40,75,47,7,76,48],[43,54,24,22,55,25],[10,45,15,67,46,16],[19,148,118,6,149,119],[18,75,47,31,76,48],[34,54,24,34,55,25],[20,45,15,61,46,16]],v.getRSBlocks=function(t,e){var n=v.getRsBlockTable(t,e);if(void 0==n)throw new Error("bad rs block @ typeNumber:"+t+"/errorCorrectLevel:"+e);for(var r=n.length/3,a=[],i=0;i<r;i++)for(var o=n[3*i+0],s=n[3*i+1],l=n[3*i+2],h=0;h<o;h++)a.push(new v(s,l));return a},v.getRsBlockTable=function(t,e){switch(e){case a.L:return v.RS_BLOCK_TABLE[4*(t-1)+0];case a.M:return v.RS_BLOCK_TABLE[4*(t-1)+1];case a.Q:return v.RS_BLOCK_TABLE[4*(t-1)+2];case a.H:return v.RS_BLOCK_TABLE[4*(t-1)+3];default:return}},m.prototype={get:function(t){var e=Math.floor(t/8);return 1==(this.buffer[e]>>>7-t%8&1)},put:function(t,e){for(var n=0;n<e;n++)this.putBit(1==(t>>>e-n-1&1))},getLengthInBits:function(){return this.length},putBit:function(t){var e=Math.floor(this.length/8);this.buffer.length<=e&&this.buffer.push(0),t&&(this.buffer[e]|=128>>>this.length%8),this.length++}};var y=[[17,14,11,7],[32,26,20,14],[53,42,32,24],[78,62,46,34],[106,84,60,44],[134,106,74,58],[154,122,86,64],[192,152,108,84],[230,180,130,98],[271,213,151,119],[321,251,177,137],[367,287,203,155],[425,331,241,177],[458,362,258,194],[520,412,292,220],[586,450,322,250],[644,504,364,280],[718,560,394,310],[792,624,442,338],[858,666,482,382],[929,711,509,403],[1003,779,565,439],[1091,857,611,461],[1171,911,661,511],[1273,997,715,535],[1367,1059,751,593],[1465,1125,805,625],[1528,1190,868,658],[1628,1264,908,698],[1732,1370,982,742],[1840,1452,1030,790],[1952,1538,1112,842],[2068,1628,1168,898],[2188,1722,1228,958],[2303,1809,1283,983],[2431,1911,1351,1051],[2563,1989,1423,1093],[2699,2099,1499,1139],[2809,2213,1579,1219],[2953,2331,1663,1273]];function _(){var t=!1,e=navigator.userAgent;if(/android/i.test(e)){t=!0;var n=e.toString().match(/android ([0-9]\.[0-9])/i);n&&n[1]&&(t=parseFloat(n[1]))}return t}var w=function(){var t=function(t,e){this._el=t,this._htOption=e};return t.prototype.draw=function(t){var e=this._htOption,n=this._el,r=t.getModuleCount();Math.floor(e.width/r),Math.floor(e.height/r);function a(t,e){var n=document.createElementNS("http://www.w3.org/2000/svg",t);for(var r in e)e.hasOwnProperty(r)&&n.setAttribute(r,e[r]);return n}this.clear();var i=a("svg",{viewBox:"0 0 "+String(r)+" "+String(r),width:"100%",height:"100%",fill:e.colorLight});i.setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xlink","http://www.w3.org/1999/xlink"),n.appendChild(i),i.appendChild(a("rect",{fill:e.colorLight,width:"100%",height:"100%"})),i.appendChild(a("rect",{fill:e.colorDark,width:"1",height:"1",id:"template"}));for(var o=0;o<r;o++)for(var s=0;s<r;s++)if(t.isDark(o,s)){var l=a("use",{x:String(s),y:String(o)});l.setAttributeNS("http://www.w3.org/1999/xlink","href","#template"),i.appendChild(l)}},t.prototype.clear=function(){for(;this._el.hasChildNodes();)this._el.removeChild(this._el.lastChild)},t}(),C="svg"===document.documentElement.tagName.toLowerCase()?w:"undefined"==typeof CanvasRenderingContext2D?function(){var t=function(t,e){this._el=t,this._htOption=e};return t.prototype.draw=function(t){for(var e=this._htOption,n=this._el,r=t.getModuleCount(),a=Math.floor(e.width/r),i=Math.floor(e.height/r),o=['<table style="border:0;border-collapse:collapse;">'],s=0;s<r;s++){o.push("<tr>");for(var l=0;l<r;l++)o.push('<td style="border:0;border-collapse:collapse;padding:0;margin:0;width:'+a+"px;height:"+i+"px;background-color:"+(t.isDark(s,l)?e.colorDark:e.colorLight)+';"></td>');o.push("</tr>")}o.push("</table>"),n.innerHTML=o.join("");var h=n.childNodes[0],u=(e.width-h.offsetWidth)/2,c=(e.height-h.offsetHeight)/2;u>0&&c>0&&(h.style.margin=c+"px "+u+"px")},t.prototype.clear=function(){this._el.innerHTML=""},t}():function(){function t(){this._elImage.src=this._elCanvas.toDataURL("image/png"),this._elImage.style.display="block",this._elCanvas.style.display="none"}if(this._android&&this._android<=2.1){var e=1/window.devicePixelRatio,n=CanvasRenderingContext2D.prototype.drawImage;CanvasRenderingContext2D.prototype.drawImage=function(t,r,a,i,o,s,l,h,u){if("nodeName"in t&&/img/i.test(t.nodeName))for(var c=arguments.length-1;c>=1;c--)arguments[c]=arguments[c]*e;else void 0===h&&(arguments[1]*=e,arguments[2]*=e,arguments[3]*=e,arguments[4]*=e);n.apply(this,arguments)}}var r=function(t,e){this._bIsPainted=!1,this._android=_(),this._htOption=e,this._elCanvas=document.createElement("canvas"),this._elCanvas.width=e.width,this._elCanvas.height=e.height,t.appendChild(this._elCanvas),this._el=t,this._oContext=this._elCanvas.getContext("2d"),this._bIsPainted=!1,this._elImage=document.createElement("img"),this._elImage.alt="Scan me!",this._elImage.style.display="none",this._el.appendChild(this._elImage),this._bSupportDataURI=null};return r.prototype.draw=function(t){var e=this._elImage,n=this._oContext,r=this._htOption,a=t.getModuleCount(),i=r.width/a,o=r.height/a,s=Math.round(i),l=Math.round(o);e.style.display="none",this.clear();for(var h=0;h<a;h++)for(var u=0;u<a;u++){var c=t.isDark(h,u),A=u*i,g=h*o;n.strokeStyle=c?r.colorDark:r.colorLight,n.lineWidth=1,n.fillStyle=c?r.colorDark:r.colorLight,n.fillRect(A,g,i,o),n.strokeRect(Math.floor(A)+.5,Math.floor(g)+.5,s,l),n.strokeRect(Math.ceil(A)-.5,Math.ceil(g)-.5,s,l)}this._bIsPainted=!0},r.prototype.makeImage=function(){this._bIsPainted&&function(t,e){var n=this;if(n._fFail=e,n._fSuccess=t,null===n._bSupportDataURI){var r=document.createElement("img"),a=function(){n._bSupportDataURI=!1,n._fFail&&n._fFail.call(n)};return r.onabort=a,r.onerror=a,r.onload=function(){n._bSupportDataURI=!0,n._fSuccess&&n._fSuccess.call(n)},void(r.src="data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==")}!0===n._bSupportDataURI&&n._fSuccess?n._fSuccess.call(n):!1===n._bSupportDataURI&&n._fFail&&n._fFail.call(n)}.call(this,t)},r.prototype.isPainted=function(){return this._bIsPainted},r.prototype.clear=function(){this._oContext.clearRect(0,0,this._elCanvas.width,this._elCanvas.height),this._bIsPainted=!1},r.prototype.round=function(t){return t?Math.floor(1e3*t)/1e3:t},r}();function b(t,e){for(var n=1,r=function(t){var e=encodeURI(t).toString().replace(/\%[0-9a-fA-F]{2}/g,"a");return e.length+(e.length!=t?3:0)}(t),i=0,o=y.length;i<=o;i++){var s=0;switch(e){case a.L:s=y[i][0];break;case a.M:s=y[i][1];break;case a.Q:s=y[i][2];break;case a.H:s=y[i][3]}if(r<=s)break;n++}if(n>y.length)throw new Error("Too long data");return n}return(r=function(t,e){if(this._htOption={width:256,height:256,typeNumber:4,colorDark:"#000000",colorLight:"#ffffff",correctLevel:a.H},"string"==typeof e&&(e={text:e}),e)for(var n in e)this._htOption[n]=e[n];"string"==typeof t&&(t=document.getElementById(t)),this._htOption.useSVG&&(C=w),this._android=_(),this._el=t,this._oQRCode=null,this._oDrawing=new C(this._el,this._htOption),this._htOption.text&&this.makeCode(this._htOption.text)}).prototype.makeCode=function(t){this._oQRCode=new e(b(t,this._htOption.correctLevel),this._htOption.correctLevel),this._oQRCode.addData(t),this._oQRCode.make(),this._el.title=t,this._oDrawing.draw(this._oQRCode),this.makeImage()},r.prototype.makeImage=function(){"function"==typeof this._oDrawing.makeImage&&(!this._android||this._android>=3)&&this._oDrawing.makeImage()},r.prototype.clear=function(){this._oDrawing.clear()},r.CorrectLevel=a,r},t.exports=a()},dJTH:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMYAAADbCAYAAAA20+4YAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo3OTg5OTM3MzY0QTkxMUU4OEE4QUMxMEM3MkM1QjlCNyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo3OTg5OTM3NDY0QTkxMUU4OEE4QUMxMEM3MkM1QjlCNyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc5ODk5MzcxNjRBOTExRTg4QThBQzEwQzcyQzVCOUI3IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjc5ODk5MzcyNjRBOTExRTg4QThBQzEwQzcyQzVCOUI3Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+snfDcQAAC4FJREFUeNrsnb+OIssVh3vWG9m6snrk1AmbOIdHgDcwhJYjyBw4geRGtmTwE5h+Amu4bzA8AjyBNQQOHWwHHtuyZN1xFfur4Wxt/6MHdmD2+6TWDAzdXVXn/M45VU33vH98fHxKAOAz3jEEAAgDAGEAIAwAhAGAMAAQBgDCAEAYAAgDAGEAIAwAhAGAMAAAYQAgDACEAYAwABAGAMIAQBgACAMAYQAgDACEAYAwABAGAMIAAIQBgDAAEAYAwgBAGAAIAwBhACAMAIQBgDAAEAYAwgBAGADgeX8tDd3+7X/Jn/76n5Mc61e//Enyx9/+FOsDGQMAYQAgDACEAYAwABAGAMIAQBgACAMAYQAgDACEAYAwAABhACAMAIQBgDAAEAYAwgBAGAAIAwBhACAMAIQBgDAAEAYAIAwAhAGAMABewnuG4Ovx93/8mPx++a+THOsXP3+X/OV3P2NQyRgACAMAYQAgDACEAYAwABAGAMIAQBgAgDAAEAYAwgBAGAAIAwBhACAMAIQBgDAAEAbAdcM933AU//7vU/KbPz+e7Hg/fP8dGQOAUgoAYQAgDACEAYAwAABhACAMAIQBgDAAEAYAwgBAGAAIAwBhACAMAIQBgDAAEAYAwgAAhAGAMAAQBgDCAEAYAAgDAGEAIAwAhAGAMAAQBgDCAEAYAIAwABAGAMIAQBgACAMAYQAgDACEAYAwABAGAMIAQBgACAMAYQAAwgBAGAAIAwBhACAMAIQBgDAAEAYAwgBAGAAIAwBhACAMAITBEAAgDACEAYAwABAGAMIAQBgACAMAYQAgDACEAYAwABAGAMIAAIQBgDAAjuLm8fHx4hv56z/88yzH/eH77+jDN9aHxsJ4enoiPABQSgEgDACEAYAwABAGAMIAQBgv5ebmpnDr9XrTsr/Zjfafp/1N+3CxfnXt1zGKBrfb7Y7dj7nbem5buq1Tsntvs9nktL/k4L3exjen4ce3jt412qCI928tBTqDpO7H1G0zZ6ide52538f680o/+9qSMzrVnfsxbNDefbtcW0eX1H6Pc9heS2FfTB8QxoG5olfmDOIjnjfKTiEtk9H8ZzL3Oj+jU41aZoyLaP/XssElZos3N/l2Az5UlN663336Tk2ECqk8RPG1+0yH9r9uH1xm7SCM8+ONkKsuHiuV5web7SNVV5+7d9vDhRnm2tv/VvpwmZPvIyd9X0z+3OD7+j53ryfmtY9SM6X1VEbz24fXTOclNfpFtN/Zwdtg0/Tj3gCX1gdWpQ4GmWqwB5rcrRWZcqkniOfBp3hnkBntP7mwr6oPb37ybVZDUkW6PBhDUWqqZcSQ8jPaTx++lYyxn/gZY3RkoJ6i11Tv+yXSyWv3/621/9g+uGwxYfL9daJVqF33EzsZYaf6N7uCaHu17X8rfXhzGcPUtlttfu3cLxkukk9LhuEKbC7DTFzEWp9h0tro4l7EQu169fafaI50NX34JkqpgtWdbqhtZYyBrsbeKa2/ajq/1Pa3FPfzFfxrssE3JQxd/LpTpAoG3n9FIYpu/ir1gvafRdhX0Yc3KQwAJt8ACAMAYQAgDACEAYAwABAGAMIAQBgACAMAEAYAwgBox4tvbW37mMXlcumfK7SbTCaZec9/A3Ps3htU7BeeSTRzn8ujv/lvavrv9nfc31Y1x1jq/DPcoD2nsMdrUvYl2lJhZFnWV4dH4/F413CQ9l8hdoOwaDCY/maWhft9bP7kv6bcid5LrHiSw91h4cYey1jv9b3I3H6jgnP7c9zrOLP4XH7/lxpR4zCP3vaO4/v7oN+rhOsDxNJ95lav/f0M9+F1TbBJisSuY4Ynr3jbbt3nJubvHz/tenTfW9vDvReC06Jg/FK99O1e27YW9Mt/tb3nPrJtaJ+ufMDfartrLAwnihBRG4tC7NTflWtk1X7h0Yx+0KYFAz2NOrKSaKwTTz+N6/5xLGm0v39v6AVoo5hEME8ON+jH5/bn8IN7iujmHa+n896Z8+/vUVDbLbdquxdO7ATe+FmLSP7RtkfR27NoIMy7qn7JuVrbQ+3z5xm534sel7RWm7OaQLs71jBeQO6ciyCoYzKGd5itE8W2RDjPA1dgYM9Dwfu5iXhjdTiT09voEB67EjqxqzhPOJaNznPzev/MIhlxrsHf32JphStHmMsYJ7uTLETwaFw/i2xq28YIMy+IvENl0mnBaUY1Ub4ykipThPG/M86dxxlK56+7q6/WHmYsfLZY65z7fhhBz5pmgDYoe4+dLzs3H2dNhTGschB3IG+ImzDHUGfu1dFRjbN05KArM3BxOfNgPv/BO7EGaaL9x4p6O0XhncS0lHOvTEayGWqgc9+7/QZyxGkQTF0J+ELmioBdn1JDNjFBI5dIUhN0nuSk+1JCTtyTkAYa8zwu3fR60tBBbuNSSoGiNuq2tYcppScFx/X9+VoPX1uVZeN3Bdlgbxzn/OuGUTGIYmVF4d6/LxngeZRqQwMH0RbXo1PVpFOTup8fL68MsNPrUEM+Rwc/oZcxV3LQjRxwfw/ymUUR5hg9GWJVUWLO1HcvlptgONfVfoFjBlEt9NmFHPTmROVg5TyqrT0kmLRNGXRi1knJEy+LMkanaYNVUs1LJpMjpeY0TJ4llH7B8YfJcTfeb9WhONXmZjK4jurZfnJ4nmqiKBGeytGPolQer7CckK4i7ccggKj9WchwyiBb1cRTY8hcfUoLyq6uJvihHN1UlKG3UT+Hcu5jg0Rje5h5VtzueN4Vt/u2xiZtRLZvn/PjTjyXftdWGP5gyeFRKL5TT3ZTndjXZHxqjl00icxUe9qtF9eE2j9MysKqSpiD9MMKSHJ4KkUo3TZqa1cRuSeDzXXMucq3sM1PJILPHEBOsVY03cbRymfcyPhz46RjZYG+mUTb8uS5JHXH+GDO6x3qJs4o2ryjDrU4kJrSqtb+be2h84xK5kqhnWuVdTe2rWcIUjvTpqROGCH9Vc+wnMLcdquODELqN2k8pPRbReRUKX9WMqe5j7a7EkdLTWQMKTk1k+cgPv/3ezmhN9gHc31ko316kRjDkuOpypA4oo/Nsb8YZz9GcvAQKleq26dmGbmvY3SiSX7INlszVk3K4NCmNv1uY49FzYplYx88UeBKCrJXYSmVFymoQXmwLYksuV1lqkjFqwKnmkdGLCwd9N7IlEl5NLD++GOVcmH58LlOV1YJq1Y+cr3oAWCKpguVSx0TIedRybCLov+d2urbN9eq3TCsZhmBrENE1vjOClbBuloMySva+WybZUW9VSGqtvaoWphZyhfGTZdew0JQ24xedEniXUEmWKvu6jYcoLCyk1Wti/toUSOsabSNS6LT0KynT3TegZmnTLS/ncuE56WutHyZKYvdqbwJGWRQJwrvkCoXO3WTUzn9LEw8bWmgDJVGRh4oo9q50dSspk2NyOqCV//ME/CX2KOsvRu1eaXl6a7G4UFjUeZbT01W0o4J6GXLtZk6ta2ZZ8zV6borpp2k+v9d1GYMM7BhSXOt8mwtJx0q2m9NiZDJ4fwSZDwZ3xqDHbNUGyLxrioQmGsWvhTaRU4dVm1uFamrsm3IFFMJd1dh1FQT877Go3eko9jon2quWGSrF9mjpOTsS1ChLFwokc3OWFaF/9fRWBi+UQ/O8Rcug+QFghgnh+eRFq0WrKNJd1LQgFRO0TWGLsIvA2YmAmYaPP/6IVpNilc2lnKsVBmiYyaDYfCzIyd2nZrsmMZOrHQ/Mhn2IaqnRzUiG4asoavEn+0XXakehUWGJhfIzFXnXSS0ugt8re1RUHHsJCIv6on6MkgO/3VpXWYjBeSjS6lQluqa3BeUPolQzj90O1bOD9p8iVDLidkRkSCT8dbmSniIMlUlRa5sEVZsQk2/bjuPUNsnL52HXDvRCttR9riAtj9/V6r0K09eGC/ZWjYsfYXBSBOAhv7/fwEGAAIOdaVpBvv6AAAAAElFTkSuQmCC"},exeb:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n("rrNu"),a={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"recharge-pane clearfix"},[n("div",{staticClass:"left-content fl"},[n("img",{attrs:{src:t.rebateSrc,alt:""}}),t._v(" "),n("span",{staticClass:"agent-level agent-level-1"},[t._v(t._s(t.moneyOption.one.rebates)+"折")]),t._v(" "),n("span",{staticClass:"agent-level agent-level-2"},[t._v(t._s(t.moneyOption.two.rebates)+"折")]),t._v(" "),n("span",{staticClass:"agent-level agent-level-3"},[t._v(t._s(t.moneyOption.three.rebates)+"折")])]),t._v(" "),n("div",{staticClass:"right-content fl"},[n("div",{staticClass:"money-content clearfix"},[n("div",{staticClass:"title fl"},[t._v("充值金额：")]),t._v(" "),t._l(t.moneyOption,function(e){return n("div",{key:e.agent_level,staticClass:"money-item fl",class:{"sl-btn-select-active":e.label===t.play_money},on:{click:function(n){t.changeMoney(e)}}},[n("p",[e.label?n("span",[t._v(t._s(e.label)+"元")]):t._e()]),t._v(" "),n("p",[t._v("享 "+t._s(e.rebates)+" 折")])])})],2),t._v(" "),n("div",{staticClass:"way-content clearfix"},[n("div",{staticClass:"title fl"},[t._v("充值方式：")]),t._v(" "),t._l(t.rechargeWayOption,function(e){return n("div",{key:e.value,staticClass:"recharge-way-item fl",class:{"sl-btn-select-active":e.value===t.rechargeWaySelect},on:{click:function(n){t.changePayWay(e)}}},[n("p",[t._v(t._s(e.label))])])})],2),t._v(" "),n("div",{staticClass:"btn-content"},[t.isShowPayBtn?n("div",{staticClass:"sl-btn-bd-b-blue payBtn",on:{click:t.payment}},[t._v("立即支付")]):n("div",{staticClass:"payBtn sl-btn-bg-b-gray"},[t._v("立即支付")])])]),t._v(" "),n("div",{ref:"adjustHeight",staticClass:"adjustHeight"}),t._v(" "),t.dialogPay?n("div",{staticClass:"code-box"},[n("div",{ref:"qrcode",attrs:{id:"qrcode"}},[n("span",{staticClass:"close-pay",on:{click:t.closePay}}),t._v(" "),n("div",{staticClass:"pay-tip"},[t._v("（请打开"+t._s(t.rechargeWaySelectText)+"扫描二维码完成支付）")])])]):t._e()])},staticRenderFns:[]};var i=function(t){n("2etM")},o=n("C7Lr")(r.a,a,!1,i,"data-v-2efb8336",null);e.default=o.exports},rrNu:function(t,e,n){"use strict";(function(t){var r=n("4YfN"),a=n.n(r),i=n("9rMa"),o=n("EuEE"),s=n("a2vD"),l=n("LqYc"),h=n.n(l),u=n("P9l9"),c=n("6nXL");e.a={components:{QRCode:h.a},data:function(){return{dialogPay:!1,token:o.a.creatToken(),key:o.a.creatKey(),agentId:s.a.getAgentid(),rebateSrc:n("dJTH"),moneyOption:{one:{label:"",rebates:null,agent_level:1},two:{label:"",rebates:null,agent_level:2},three:{label:"",rebates:null,agent_level:3}},moneySelect:0,agent_level:null,play_money:0,rechargeWayOption:[{value:1,label:"支付宝"},{value:2,label:"微信"}],rechargeWaySelect:0,isQrcode:!1,record_id:null,record_money:null}},computed:a()({isShowPayBtn:function(){return!(!this.play_money||!this.rechargeWaySelect)},rechargeWaySelectText:function(){switch(this.rechargeWaySelect){case 1:return"支付宝";case 2:return"微信"}}},Object(i.d)({ACS:function(t){return t.perimission.sysPermis},AG_K:function(t){return t.perimission.AG_K}})),created:function(){var t=this;this.getAgentCfg(),this.pay_msg(),this.pay_ali_msg(),window.WebSock.Init("ws://srv"+window.full_url+":13010/websocket",this.token,this.key,function(){t.pay_msg(),t.pay_ali_msg()})},mounted:function(){var t=this;this.$nextTick(function(){var e=t.$refs.adjustHeight;o.a.AdjustHeight(e)})},methods:{changeMoney:function(t){t.label&&(this.agent_level=t.agent_level,this.play_money=t.label)},changePayWay:function(t){this.play_money&&(this.rechargeWaySelect=t.value)},getAgentCfg:function(){var t=this,e={get_agent_upmoney:1,agent_id:this.agentId};Object(u.l)(e).then(function(e){if(0===e.ret){var n=t.moneyOption;e.data.list.forEach(function(t){switch(Number(t.agent_level)){case 1:n.one.rebates=t.hardware_rebates,n.one.label=t.uplevel_money&&1e4*t.uplevel_money*1e4/1e4;break;case 2:n.two.rebates=t.hardware_rebates,n.two.label=t.uplevel_money&&1e4*t.uplevel_money*1e4/1e4;break;case 3:n.three.rebates=t.hardware_rebates,n.three.label=t.uplevel_money&&1e4*t.uplevel_money*1e4/1e4}})}else t.$slnotify({message:c.X.toString(e.ret),duration:1500})})},pay_msg:function(){var t=this;o.a.RecPayState(this.token,function(e){t.closePay(),t.clearState(),t.pay_msg(),t.$slnotify({message:"支付成功",duration:2e3})},function(){t.$slnotify({message:"签名错误：前刷新页面重试",duration:1500})},this.$slnotify)},pay_ali_msg:function(){var t=this;o.a.RecAliPayState(this.token,function(e){t.closePay(),t.clearState(),t.pay_ali_msg(),t.$slnotify({message:"支付成功",duration:2e3})},function(){t.$slnotify({message:"签名错误：前刷新页面重试",duration:1500})},this.$slnotify)},qrcode:function(){var e=this;if(!this.isQrcode){var n;switch(this.rechargeWaySelect){case 2:n="http://wx"+window.full_url+"/wx_record_pay.php?record_id="+this.record_id+"&token="+this.token+"&record_money="+this.record_money+"&type=cz&agent_level="+this.agent_level;break;case 1:n="http://alipay"+window.full_url+"/alipay_record_pay.php?record_id="+this.record_id+"&token="+this.token+"&record_money="+this.record_money+"&type=cz&agent_level="+this.agent_level}t.ajax({type:"GET",url:n,dataType:"json",success:function(t){if(0==t.ret){new h.a("qrcode",{width:265,height:265,text:t.data.url});e.isQrcode=!0}},error:function(t){}})}},payment:function(){var t=this;if(this.ACS[this.AG_K.NOW_PAY]){var e={agent_money_save:1,agent_id:this.agentId,money:this.play_money,agent_level:this.agent_level};Object(u.o)(e).then(function(e){0===e.ret?(t.record_id=e.data.record_id,t.record_money=e.data.record_money,t.dialogPay=!0,t.$nextTick(function(){t.qrcode()})):t.$slnotify({message:c.X.toString(e.ret),duration:1500})})}else this.$slnotify({message:"操作权限不足"})},closePay:function(){this.dialogPay=!1,this.isQrcode=!1,this.record_id=null,this.record_money=null},clearState:function(){this.rechargeWaySelect=0,this.play_money=0,this.agent_level=null}}}}).call(e,n("L7Pj"))}});