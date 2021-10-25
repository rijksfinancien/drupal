(function(e){function t(t){for(var o,s,a=t[0],l=t[1],u=t[2],c=0,h=[];c<a.length;c++)s=a[c],Object.prototype.hasOwnProperty.call(i,s)&&i[s]&&h.push(i[s][0]),i[s]=0;for(o in l)Object.prototype.hasOwnProperty.call(l,o)&&(e[o]=l[o]);d&&d(t);while(h.length)h.shift()();return r.push.apply(r,u||[]),n()}function n(){for(var e,t=0;t<r.length;t++){for(var n=r[t],o=!0,a=1;a<n.length;a++){var l=n[a];0!==i[l]&&(o=!1)}o&&(r.splice(t--,1),e=s(s.s=n[0]))}return e}var o={},i={app:0},r=[];function s(t){if(o[t])return o[t].exports;var n=o[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,s),n.l=!0,n.exports}s.m=e,s.c=o,s.d=function(e,t,n){s.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},s.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},s.t=function(e,t){if(1&t&&(e=s(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(s.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)s.d(n,o,function(t){return e[t]}.bind(null,o));return n},s.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return s.d(t,"a",t),t},s.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},s.p="";var a=window["webpackJsonp"]=window["webpackJsonp"]||[],l=a.push.bind(a);a.push=t,a=a.slice();for(var u=0;u<a.length;u++)t(a[u]);var d=l;r.push([0,"chunk-vendors"]),n()})({0:function(e,t,n){e.exports=n("56d7")},"034f":function(e,t,n){"use strict";var o=n("64a9"),i=n.n(o);i.a},"0f1d":function(e,t,n){"use strict";var o=n("e29f"),i=n.n(o);i.a},"401f":function(e,t,n){},"56d7":function(e,t,n){"use strict";n.r(t);n("cadf"),n("551c"),n("f751"),n("097d");var o=n("2b0e"),i=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{attrs:{id:"app"}},[n("Selection"),n("Diagram")],1)},r=[],s=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{ref:"main",attrs:{id:"main"}},[n("svg",{ref:"svg",attrs:{width:e.width,height:e.height}},[n("defs",[n("filter",{attrs:{id:"shadow",x:"0",y:"0",width:"200%",height:"200%"}},[n("feOffset",{attrs:{result:"offOut",in:"SourceAlpha",dx:"0",dy:"0"}}),n("feGaussianBlur",{attrs:{result:"blurOut",in:"offOut",stdDeviation:"5"}}),n("feBlend",{attrs:{in:"SourceGraphic",in2:"blurOut",mode:"normal"}})],1)]),n("g",{ref:"flows"},e._l(e.network.flows,function(t){return n("path",{key:e.getFlowId(t),ref:e.getFlowId(t),refInFor:!0,attrs:{d:e.getPathForFlow(t),fill:-1!=e.selectedFlows.indexOf(t)?"#ffe169":e.flowColors[t.type],opacity:-1!=e.selectedFlows.indexOf(t)?.9:.7},on:{mouseover:function(n){return e.onMouseOverFlow(n,t)},mouseout:e.onMouseOutFlow}})}),0),n("g",e._l(e.network.nodes,function(t){return n("rect",{key:t.id+"_"+t.category+"_"+t.type,staticClass:"node",attrs:{x:t.x,y:t.y,width:t.width,height:t.height,fill:t==e.selectedNode?"#ffe169":e.nodeColors[t.type]},on:{mouseover:function(n){return e.onMouseOverNode(t)},mouseout:e.onMouseOutNode}})}),0),n("g",[e._v("\n      //chapter labels, left of node, only for uitgaven\n      "),e._v("\n\n      //mn labels, right of node, only for uitgaven\n      "),e._l(e.network.nodes.filter(function(e){return"poster"==e.category||"uitgave"==e.type}),function(t){return n("text",{key:"label_"+t.id+"_"+t.category+"_"+t.type,staticClass:"label",class:{explainable:e.explanations.hasOwnProperty(t.id),nonSelectable:!e.explanations.hasOwnProperty(t.id)},attrs:{x:t.x+t.width+5,y:t.y+t.height/2+4,"text-anchor":"start"},on:{mouseover:function(n){return e.explanations.hasOwnProperty(t.id)&&e.onMouseOverTitle(n,t.id)},mouseout:e.onMouseOutTitle}},[e._v("\n        "+e._s(e.shortenLabel(t.id))+"\n      ")])})],2),n("g",{attrs:{id:"numbers"}},e._l(e.network.nodes,function(t){return n("text",{key:"number_"+t.id+"_"+t.category+"_"+t.type,staticClass:"number",style:{visibility:t==e.selectedNode||e.hasSelectedFlow(t)?"visible":"hidden","font-weight":t==e.selectedNode?"bolder":"normal"},attrs:{x:t.x-5,y:t.y+10,"text-anchor":"end"}},[e._v("\n        "+e._s(e.formatAmount(e.getSumOfSelectedFlows(t)))+"\n      ")])}),0),e._v("\n    getSumOfSelectedFlows\n    "),n("g",[e._v("\n      //title\n      "),n("text",{staticClass:"title",attrs:{x:e.positionPerCategory.chapter.x+e.nodeSize.width/2,y:e.positionPerCategory.chapter.y-20,"text-anchor":"middle"}},[e._v("\n        Rijksbegroting\n      ")]),n("text",{staticClass:"title",attrs:{x:e.positionPerCategory.mn.x+e.nodeSize.width/2,y:e.positionPerCategory.mn.y-20,"text-anchor":"middle"}},[e._v("\n        Miljoenennota\n      ")]),n("text",{staticClass:"title",attrs:{x:e.positionPerCategory.poster.x+e.nodeSize.width/2,y:e.positionPerCategory.poster.y-20,"text-anchor":"middle"}},[e._v("\n        Miljoenennotaposter\n      ")])]),n("g",{attrs:{id:"legend"}},[n("text",{staticClass:"legendLabel",attrs:{x:"20",y:"21"}},[e._v("\n        Bedragen in duizenden Euro's\n      ")]),n("rect",{staticClass:"node",attrs:{x:"250",y:"10",width:e.nodeSize.width,height:e.nodeSize.height,fill:e.nodeColors.uitgave}}),n("text",{staticClass:"legendLabel",attrs:{x:250+e.nodeSize.width+5,y:"21"}},[e._v("\n        Uitgave\n      ")]),n("rect",{staticClass:"node",attrs:{x:"400",y:"10",width:e.nodeSize.width,height:e.nodeSize.height,fill:e.nodeColors.saldering}}),n("text",{staticClass:"legendLabel",attrs:{x:400+e.nodeSize.width+5,y:"21"}},[e._v("\n        Niet-belastingontvangst\n      ")]),n("rect",{staticClass:"node",attrs:{x:"650",y:"10",width:e.nodeSize.width,height:e.nodeSize.height,fill:e.nodeColors.premie}}),n("text",{staticClass:"legendLabel explainable",attrs:{x:650+e.nodeSize.width+5,y:"21"},on:{mouseover:function(t){return e.onMouseOverTitle(t,"Premie")},mouseout:function(t){return e.onMouseOutTitle()}}},[e._v("\n        Premie\n      ")]),n("rect",{staticClass:"node",attrs:{x:"800",y:"10",width:e.nodeSize.width,height:e.nodeSize.height,fill:e.nodeColors.saldo}}),n("text",{staticClass:"legendLabel explainable",attrs:{x:800+e.nodeSize.width+5,y:"21"},on:{mouseover:function(t){return e.onMouseOverTitle(t,"Netto uitgave")},mouseout:function(t){return e.onMouseOutTitle()}}},[e._v("\n        Netto uitgave\n      ")])]),e.explanation.length>0?n("g",{ref:"explanationWindow",attrs:{transform:"translate("+e.explanationWindowPosX+","+e.explanationWindowPosY+")"}},[n("rect",{staticClass:"textBackground",attrs:{x:"-10",y:"-10",width:e.textWidth+20,height:e.textHeight+20,filter:"url(#shadow)"}}),n("text",{ref:"explanationText",staticClass:"explanationText"},e._l(e.explanation,function(t){return n("tspan",{attrs:{x:"0",dy:e.lineSpacing}},[e._v(e._s(t))])}),0)]):e._e()])])},a=[],l=(n("6b54"),n("a481"),n("20d6"),n("ac6a"),n("7514"),n("55dd"),{data:function(){return{width:1200,height:1100,scaleFactor:2e-6,nodeSize:{width:50,height:12},spacing:{betweenNodes:10,betweenTypes:1,betweenPosterNodes:20},selectedFlows:[],selectedNode:null,explanation:[],positionPerCategory:{chapter:{x:100,y:100},mn:{x:475,y:100},poster:{x:850,y:200}},nodeColors:{uitgave:"#8fcae7",premie:"#42145f",saldering:"#ca005d",saldo:"#007bc7"},flowColors:{uitgave:"#eef7fb",premie:"#e3dce7",saldering:"#f7d9e7"},explainedElementX:0,explainedElementY:0,lineSpacing:18,textWidth:540}},mounted:function(){this.updateNodePositions(),this.updateFlowPositions()},computed:{network:function(){return this.$store.getters.network},abbreviations:function(){return this.$store.getters.abbreviations},explanations:function(){return this.$store.getters.explanations},textHeight:function(){return this.explanation.length*this.lineSpacing},explanationWindowPosX:function(){return this.explainedElementX-this.textWidth/2<0?this.explainedElementX:this.explainedElementX+this.textWidth/2>this.width?this.explainedElementX-this.textWidth:this.explainedElementX-this.textWidth/2},explanationWindowPosY:function(){return this.explainedElementY<this.height/2?this.explainedElementY+30:this.explainedElementY-30-this.textHeight}},watch:{network:function(){this.updateNodePositions(),this.updateFlowPositions()}},methods:{updateNodePositions:function(){var e=this;if(null!=this.network&&this.network.nodes.length>0){var t=["saldering","uitgave","premie"],n=this.network.nodes.filter(function(e){return"chapter"==e.category}).sort(function(n,o){if(n.id==o.id)return t.indexOf(n.type)-t.indexOf(o.type);var i=e.network.nodes.find(function(e){return e.category==n.category&&e.id==n.id&&"uitgave"==e.type});null==i&&e.network.nodes.find(function(e){return e.category==n.category&&e.id==n.id&&"saldering"==e.type});var r=e.network.nodes.find(function(e){return e.category==o.category&&e.id==o.id&&"uitgave"==e.type});return null==r&&e.network.nodes.find(function(e){return e.category==o.category&&e.id==o.id&&"saldering"==e.type}),r.saldo-i.saldo}),o=this.positionPerCategory.chapter.x,i=this.positionPerCategory.chapter.y,r=null;n.forEach(function(t){if(null!=r){var n=r.id==t.id?e.spacing.betweenTypes:e.spacing.betweenNodes;i+=n}t.width=e.nodeSize.width,t.height=e.nodeSize.height,t.x=o,t.y=i,i+=t.height,r=t});var s=this.network.nodes.filter(function(e){return"mn"==e.category}).sort(function(n,o){if(n.id==o.id)return t.indexOf(n.type)-t.indexOf(o.type);var i=e.network.nodes.find(function(e){return e.category==n.category&&e.id==n.id&&"uitgave"==e.type}),r=e.network.nodes.find(function(e){return e.category==o.category&&e.id==o.id&&"uitgave"==e.type}),s=0,a=0;return i.flows.forEach(function(e){null!=e.chapter&&(s+=e.chapter.y)}),r.flows.forEach(function(e){null!=e.chapter&&(a+=e.chapter.y)}),s/i.flows.length-a/r.flows.length});o=this.positionPerCategory.mn.x,i=this.positionPerCategory.mn.y,r=null,s.forEach(function(t){if(null!=r){var n=r.id==t.id?e.spacing.betweenTypes:e.spacing.betweenNodes;i+=n}t.width=e.nodeSize.width,t.height=e.nodeSize.height,t.x=o,t.y=i,i+=t.height,r=t});var a=this.network.nodes.filter(function(e){return"poster"==e.category}).sort(function(e,t){var n=[],o=0;e.flows.forEach(function(e){-1==n.indexOf(e.mn)&&null!=e.mn&&(o+=e.mn.y,n.push(e.mn))});var i=[],r=0;return t.flows.forEach(function(e){-1==i.indexOf(e.mn)&&null!=e.mn&&(r+=e.mn.y,i.push(e.mn))}),o/n.length-r/i.length}),l=a.findIndex(function(e){return"Buitenlandse Zaken"==e.id}),d=a.findIndex(function(e){return"Buitenlandse Zaken / HGIS"==e.id});u(a,l,d-1),o=this.positionPerCategory.poster.x,i=this.positionPerCategory.poster.y,r=null,a.forEach(function(t){null!=r&&(i+="Buitenlandse Zaken / HGIS"==t.id?e.spacing.betweenTypes:e.spacing.betweenPosterNodes),t.width=e.nodeSize.width,t.height=e.nodeSize.height,t.x=o,t.y=i,i+=t.height,r=t})}},updateFlowPositions:function(){var e=this;null!=this.network&&this.network.flows.length>0&&this.network.flows.forEach(function(t){t.positions=[],null!=t.chapter&&t.positions.push({x:t.chapter.x+t.chapter.width,y:t.chapter.y,thickness:e.nodeSize.height}),null!=t.mn&&(t.positions.push({x:t.mn.x,y:t.mn.y,thickness:e.nodeSize.height}),t.positions.push({x:t.mn.x+t.mn.width,y:t.mn.y,thickness:e.nodeSize.height})),null!=t.poster&&t.positions.push({x:t.poster.x,y:t.poster.y,thickness:e.nodeSize.height})})},getPathForFlow:function(e){for(var t="",n=0;n<e.positions.length;n++){var o=e.positions[n];t+=0==n?"M":" L",t+=o.x+","+o.y}for(var i=e.positions.length-1;i>=0;i--){var r=e.positions[i];t+=" L"+r.x+","+(r.y+r.thickness)}return t+=" Z",t},getFlowId:function(e){return(null!=e.chapter?e.chapter.id:"no-chapter")+"_"+(null!=e.mn?e.mn.id:"no_mn")+"_"+(null!=e.poster?e.poster.id:"no_poster")+"__"+e.type},shortenLabel:function(e){for(var t=0;t<this.abbreviations.length;t++){var n=this.abbreviations[t].long;if(-1!=e.indexOf(n)){var o=this.abbreviations[t].short;return e.replace(n,o)}}return e},onMouseOverFlow:function(e,t){this.selectedFlows=[t],e.target.parentNode.appendChild(e.target)},onMouseOutFlow:function(){this.selectedFlows=[]},onMouseOverNode:function(e){var t=this;this.selectedNode=e,this.selectedFlows=e.flows,e.flows.forEach(function(e){var n=t.$refs[t.getFlowId(e)][0];n.parentNode.appendChild(n)})},onMouseOutNode:function(){this.selectedNode=null,this.selectedFlows=[]},onMouseOverTitle:function(e,t){this.explanation=this.explanations[t];var n=e?e.target:window.event.srcElement;this.explainedElementX=n.x.baseVal[0].value,this.explainedElementY=n.y.baseVal[0].value},onMouseOutTitle:function(){this.explanation=[]},formatAmount:function(e){var t=Math.round(e).toString(),n=/(\d+)(\d{3})/;while(n.test(t))t=t.replace(n,"$1.$2");return"€ "+t},getSumOfSelectedFlows:function(e){var t=this,n=0;return e.flows.forEach(function(e){-1!=t.selectedFlows.indexOf(e)&&(n+=e.saldo)}),n},hasSelectedFlow:function(e){var t=this;return e.flows.find(function(e){return-1!=t.selectedFlows.indexOf(e)})}}});function u(e,t,n){if(n>=e.length){var o=n-e.length+1;while(o--)e.push(void 0)}e.splice(n,0,e.splice(t,1)[0])}var d=l,c=(n("0f1d"),n("2877")),h=Object(c["a"])(d,s,a,!1,null,"e80208de",null),f=h.exports,p=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{attrs:{id:"main"}},[n("b-form-group",{attrs:{label:"Kies jaar"}},[n("b-form-radio-group",{attrs:{options:e.years},on:{input:e.inputChanged},model:{value:e.selectedYear,callback:function(t){e.selectedYear=t},expression:"selectedYear"}})],1)],1)},g=[],w={data:function(){return{selectedYear:""}},computed:{years:function(){return this.$store.getters.years}},methods:{inputChanged:function(){this.$store.dispatch("getData",this.selectedYear)}},watch:{years:function(){this.selectedYear=this.years[0]}}},y=w,m=(n("773b"),Object(c["a"])(y,p,g,!1,null,"5b88fc48",null)),x=m.exports,v={name:"app",components:{Selection:x,Diagram:f},created:function(){this.$store.dispatch("init")}},b=v,_=(n("034f"),Object(c["a"])(b,i,r,!1,null,null,null)),S=_.exports,O=n("2f62");n("6d93");o["default"].use(O["a"]);var E=new O["a"].Store({state:{api_endpoint:"",years:[],abbreviations:[],explanations:{},hoofdstukken_niet_meenemen:[],network:{nodes:[],flows:[]}},getters:{abbreviations:function(e){return e.abbreviations},explanations:function(e){return e.explanations},api_endpoint:function(e){return e.api_endpoint},hoofdstukken_niet_meenemen:function(e){return e.hoofdstukken_niet_meenemen},years:function(e){return e.years},network:function(e){return e.network}},mutations:{SET_ABBREVIATIONS:function(e,t){e.abbreviations=t},SET_EXPLANATIONS:function(e,t){e.explanations=t},SET_HOOFDSTUKKEN_NIET_MEENEMEN:function(e,t){e.hoofdstukken_niet_meenemen=t},SET_API_ENDPOINT:function(e,t){e.api_endpoint=t},SET_YEARS:function(e,t){e.years=t},SET_NETWORK:function(e,t){e.network=t}},actions:{init:function(e){fetch("/modules/custom/rijksfinancien_visuals/assets/begroting_vs_miljoenennota/config/config.json").then(function(e){return e.json()}).then(function(t){e.commit("SET_YEARS",t.jaren),e.commit("SET_EXPLANATIONS",t.toelichtingen),e.commit("SET_ABBREVIATIONS",t.afkortingen),e.commit("SET_HOOFDSTUKKEN_NIET_MEENEMEN",t.hoofdstukken_niet_meenemen),e.commit("SET_API_ENDPOINT",t.api_endpoint),e.dispatch("getData",t.jaren[0])})},getData:function(e,t){var n=e.getters.api_endpoint+"/"+t;fetch(n).then(function(e){return e.json()}).then(function(t){var n=[],o=[];t.forEach(function(t){var i=+t["bedrag_plus_min"];if("V"!=t["vuo"]&&0!=i&&"Main"!=t["uitsplitsing"]){var r;r="1"==t["premie"]?"premie":"O"==t["vuo"]?"saldering":"uitgave";var s=(t["hoofdstuk"]+" "+t["hoofdstuk_naam"]).trim();-1==e.getters.hoofdstukken_niet_meenemen.indexOf(s.toLowerCase().trim())&&"premie"!=r||(s="");var a=""==s?null:k(n,s,"chapter",r),l=t["miljoenennota"].trim(),u=""==l?null:k(n,l,"mn",r),d=t["poster"].trim();if(-1!=d.toLowerCase().indexOf("buitenlandse zaken")&&(d="Buitenlandse Zaken"),"HGIS"==d)d="Buitenlandse Zaken / HGIS";else{var c=d.indexOf("-");-1!=c&&(d=d.substring(0,c).trim())}var h=""==d?null:k(n,d,"poster","saldo"),f={chapter:a,mn:u,poster:h,saldo:i,type:r,positions:[]};P(o,f)}});var i={nodes:n,flows:o};e.commit("SET_NETWORK",i)})}}});function k(e,t,n,o){var i=e.find(function(e){return e.id==t&&e.category==n&&e.type==o});return null==i&&(i={id:t,category:n,type:o,saldo:0,flows:[]},e.push(i)),i}function P(e,t){null!=t.chapter&&(t.chapter.saldo+=t.saldo),null!=t.mn&&(t.mn.saldo+=t.saldo),null!=t.poster&&(t.poster.saldo+=t.saldo);var n=e.find(function(e){return e.chapter==t.chapter&&e.mn==t.mn&&e.poster==t.poster&&e.type==t.type});null==n?(e.push(t),null!=t.chapter&&t.chapter.flows.push(t),null!=t.mn&&t.mn.flows.push(t),null!=t.poster&&t.poster.flows.push(t)):n.saldo+=t.saldo}var C=n("5f5b");n("f9e3"),n("2dd8");o["default"].use(C["a"]),o["default"].config.productionTip=!1,new o["default"]({store:E,render:function(e){return e(S)}}).$mount("#app")},"64a9":function(e,t,n){},"773b":function(e,t,n){"use strict";var o=n("401f"),i=n.n(o);i.a},e29f:function(e,t,n){}});