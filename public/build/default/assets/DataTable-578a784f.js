import{o as h,d as I,a as d,s as U,r as F,x as B,C as te,z as re,y as ie,G as V,F as R,A as be,B as me,l as he,c as C,w as O,f as T,t as _,b as N,u as $,n as z,g as oe,h as Be,E as Me,I as le,O as Te,j as Z,v as ve,m as Ne,R as pe,V as Oe,e as q,a5 as Ce}from"./app-c5b9eb34.js";import{I as ne}from"./Icon-cc46e8c2.js";import{s as je}from"./vue-multiselect.esm-71e07c69.js";import{r as Ve}from"./AppLayout-618a91c1.js";import{r as De}from"./XMarkIcon-0480915a.js";import{o as c,u as G,H as ue,t as K,b as qe,N as ge,a as A}from"./use-resolve-button-type-e76f763c.js";import{c as de,w as ke,h as xe,m as ae,a as Q,f as X,E as ce,P as H,N as D,T as se}from"./hidden-cc5b5bf3.js";import{c as Ae,l as ee,p as He}from"./open-closed-a4ec46ac.js";function Re(t,s){return h(),I("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true"},[d("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M15.75 19.5L8.25 12l7.5-7.5"})])}function Ue(t,s){return h(),I("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true"},[d("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M8.25 4.5l7.5 7.5-7.5 7.5"})])}function J(t,s,r){de.isServer||U(y=>{document.addEventListener(t,s,r),y(()=>document.removeEventListener(t,s,r))})}function _e(t,s,r){de.isServer||U(y=>{window.addEventListener(t,s,r),y(()=>window.removeEventListener(t,s,r))})}function ze(t,s,r=B(()=>!0)){function y(e,f){if(!r.value||e.defaultPrevented)return;let i=f(e);if(i===null||!i.getRootNode().contains(i))return;let n=function g(o){return typeof o=="function"?g(o()):Array.isArray(o)||o instanceof Set?o:[o]}(t);for(let g of n){if(g===null)continue;let o=g instanceof HTMLElement?g:c(g);if(o!=null&&o.contains(i)||e.composed&&e.composedPath().includes(o))return}return!ke(i,xe.Loose)&&i.tabIndex!==-1&&e.preventDefault(),s(e,i)}let l=F(null);J("pointerdown",e=>{var f,i;r.value&&(l.value=((i=(f=e.composedPath)==null?void 0:f.call(e))==null?void 0:i[0])||e.target)},!0),J("mousedown",e=>{var f,i;r.value&&(l.value=((i=(f=e.composedPath)==null?void 0:f.call(e))==null?void 0:i[0])||e.target)},!0),J("click",e=>{l.value&&(y(e,()=>l.value),l.value=null)},!0),J("touchend",e=>y(e,()=>e.target instanceof HTMLElement?e.target:null),!0),_e("blur",e=>y(e,()=>window.document.activeElement instanceof HTMLIFrameElement?window.document.activeElement:null),!0)}var j=(t=>(t[t.Forwards=0]="Forwards",t[t.Backwards=1]="Backwards",t))(j||{});function we(){let t=F(0);return _e("keydown",s=>{s.key==="Tab"&&(t.value=s.shiftKey?1:0)}),t}function Ge(t,s,r,y){de.isServer||U(l=>{t=t??window,t.addEventListener(s,r,y),l(()=>t.removeEventListener(s,r,y))})}let ye=Symbol("PortalParentContext");function Ke(){let t=te(ye,null),s=F([]);function r(e){return s.value.push(e),t&&t.register(e),()=>y(e)}function y(e){let f=s.value.indexOf(e);f!==-1&&s.value.splice(f,1),t&&t.unregister(e)}let l={register:r,unregister:y,portals:s};return[s,re({name:"PortalWrapper",setup(e,{slots:f}){return ie(ye,l),()=>{var i;return(i=f.default)==null?void 0:i.call(f)}}})]}function We({defaultContainers:t=[],portals:s,mainTreeNodeRef:r}={}){let y=F(null),l=ae(y);function e(){var f;let i=[];for(let n of t)n!==null&&(n instanceof HTMLElement?i.push(n):"value"in n&&n.value instanceof HTMLElement&&i.push(n.value));if(s!=null&&s.value)for(let n of s.value)i.push(n);for(let n of(f=l==null?void 0:l.querySelectorAll("html > *, body > *"))!=null?f:[])n!==document.body&&n!==document.head&&n instanceof HTMLElement&&n.id!=="headlessui-portal-root"&&(n.contains(c(y))||i.some(g=>n.contains(g))||i.push(n));return i}return{resolveContainers:e,contains(f){return e().some(i=>i.contains(f))},mainTreeNodeRef:y,MainTreeNode(){return r!=null?null:V(X,{features:Q.Hidden,ref:y})}}}var Ye=(t=>(t[t.Open=0]="Open",t[t.Closed=1]="Closed",t))(Ye||{});let Pe=Symbol("PopoverContext");function fe(t){let s=te(Pe,null);if(s===null){let r=new Error(`<${t} /> is missing a parent <${$e.name} /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(r,fe),r}return s}let Ze=Symbol("PopoverGroupContext");function Se(){return te(Ze,null)}let Ee=Symbol("PopoverPanelContext");function Je(){return te(Ee,null)}let $e=re({name:"Popover",inheritAttrs:!1,props:{as:{type:[Object,String],default:"div"}},setup(t,{slots:s,attrs:r,expose:y}){var l;let e=F(null);y({el:e,$el:e});let f=F(1),i=F(null),n=F(null),g=F(null),o=F(null),a=B(()=>ae(e)),m=B(()=>{var v,b;if(!c(i)||!c(o))return!1;for(let Y of document.querySelectorAll("body > *"))if(Number(Y==null?void 0:Y.contains(c(i)))^Number(Y==null?void 0:Y.contains(c(o))))return!0;let x=ce(),M=x.indexOf(c(i)),W=(M+x.length-1)%x.length,Ie=(M+1)%x.length,Fe=x[W],Le=x[Ie];return!((v=c(o))!=null&&v.contains(Fe))&&!((b=c(o))!=null&&b.contains(Le))}),u={popoverState:f,buttonId:F(null),panelId:F(null),panel:o,button:i,isPortalled:m,beforePanelSentinel:n,afterPanelSentinel:g,togglePopover(){f.value=G(f.value,{0:1,1:0})},closePopover(){f.value!==1&&(f.value=1)},close(v){u.closePopover();let b=(()=>v?v instanceof HTMLElement?v:v.value instanceof HTMLElement?c(v):c(u.button):c(u.button))();b==null||b.focus()}};ie(Pe,u),Ae(B(()=>G(f.value,{0:ee.Open,1:ee.Closed})));let E={buttonId:u.buttonId,panelId:u.panelId,close(){u.closePopover()}},L=Se(),w=L==null?void 0:L.registerPopover,[P,k]=Ke(),p=We({mainTreeNodeRef:L==null?void 0:L.mainTreeNodeRef,portals:P,defaultContainers:[i,o]});function S(){var v,b,x,M;return(M=L==null?void 0:L.isFocusWithinPopoverGroup())!=null?M:((v=a.value)==null?void 0:v.activeElement)&&(((b=c(i))==null?void 0:b.contains(a.value.activeElement))||((x=c(o))==null?void 0:x.contains(a.value.activeElement)))}return U(()=>w==null?void 0:w(E)),Ge((l=a.value)==null?void 0:l.defaultView,"focus",v=>{var b,x;v.target!==window&&v.target instanceof HTMLElement&&f.value===0&&(S()||i&&o&&(p.contains(v.target)||(b=c(u.beforePanelSentinel))!=null&&b.contains(v.target)||(x=c(u.afterPanelSentinel))!=null&&x.contains(v.target)||u.closePopover()))},!0),ze(p.resolveContainers,(v,b)=>{var x;u.closePopover(),ke(b,xe.Loose)||(v.preventDefault(),(x=c(i))==null||x.focus())},B(()=>f.value===0)),()=>{let v={open:f.value===0,close:u.close};return V(R,[V(k,{},()=>ue({theirProps:{...t,...r},ourProps:{ref:e},slot:v,slots:s,attrs:r,name:"Popover"})),V(p.MainTreeNode)])}}}),Qe=re({name:"PopoverButton",props:{as:{type:[Object,String],default:"button"},disabled:{type:[Boolean],default:!1},id:{type:String,default:()=>`headlessui-popover-button-${K()}`}},inheritAttrs:!1,setup(t,{attrs:s,slots:r,expose:y}){let l=fe("PopoverButton"),e=B(()=>ae(l.button));y({el:l.button,$el:l.button}),be(()=>{l.buttonId.value=t.id}),me(()=>{l.buttonId.value=null});let f=Se(),i=f==null?void 0:f.closeOthers,n=Je(),g=B(()=>n===null?!1:n.value===l.panelId.value),o=F(null),a=`headlessui-focus-sentinel-${K()}`;g.value||U(()=>{l.button.value=o.value});let m=qe(B(()=>({as:t.as,type:s.type})),o);function u(p){var S,v,b,x,M;if(g.value){if(l.popoverState.value===1)return;switch(p.key){case A.Space:case A.Enter:p.preventDefault(),(v=(S=p.target).click)==null||v.call(S),l.closePopover(),(b=c(l.button))==null||b.focus();break}}else switch(p.key){case A.Space:case A.Enter:p.preventDefault(),p.stopPropagation(),l.popoverState.value===1&&(i==null||i(l.buttonId.value)),l.togglePopover();break;case A.Escape:if(l.popoverState.value!==0)return i==null?void 0:i(l.buttonId.value);if(!c(l.button)||(x=e.value)!=null&&x.activeElement&&!((M=c(l.button))!=null&&M.contains(e.value.activeElement)))return;p.preventDefault(),p.stopPropagation(),l.closePopover();break}}function E(p){g.value||p.key===A.Space&&p.preventDefault()}function L(p){var S,v;t.disabled||(g.value?(l.closePopover(),(S=c(l.button))==null||S.focus()):(p.preventDefault(),p.stopPropagation(),l.popoverState.value===1&&(i==null||i(l.buttonId.value)),l.togglePopover(),(v=c(l.button))==null||v.focus()))}function w(p){p.preventDefault(),p.stopPropagation()}let P=we();function k(){let p=c(l.panel);if(!p)return;function S(){G(P.value,{[j.Forwards]:()=>H(p,D.First),[j.Backwards]:()=>H(p,D.Last)})===se.Error&&H(ce().filter(v=>v.dataset.headlessuiFocusGuard!=="true"),G(P.value,{[j.Forwards]:D.Next,[j.Backwards]:D.Previous}),{relativeTo:c(l.button)})}S()}return()=>{let p=l.popoverState.value===0,S={open:p},{id:v,...b}=t,x=g.value?{ref:o,type:m.value,onKeydown:u,onClick:L}:{ref:o,id:v,type:m.value,"aria-expanded":l.popoverState.value===0,"aria-controls":c(l.panel)?l.panelId.value:void 0,disabled:t.disabled?!0:void 0,onKeydown:u,onKeyup:E,onClick:L,onMousedown:w};return V(R,[ue({ourProps:x,theirProps:{...s,...b},slot:S,attrs:s,slots:r,name:"PopoverButton"}),p&&!g.value&&l.isPortalled.value&&V(X,{id:a,features:Q.Focusable,"data-headlessui-focus-guard":!0,as:"button",type:"button",onFocus:k})])}}}),Xe=re({name:"PopoverPanel",props:{as:{type:[Object,String],default:"div"},static:{type:Boolean,default:!1},unmount:{type:Boolean,default:!0},focus:{type:Boolean,default:!1},id:{type:String,default:()=>`headlessui-popover-panel-${K()}`}},inheritAttrs:!1,setup(t,{attrs:s,slots:r,expose:y}){let{focus:l}=t,e=fe("PopoverPanel"),f=B(()=>ae(e.panel)),i=`headlessui-focus-sentinel-before-${K()}`,n=`headlessui-focus-sentinel-after-${K()}`;y({el:e.panel,$el:e.panel}),be(()=>{e.panelId.value=t.id}),me(()=>{e.panelId.value=null}),ie(Ee,e.panelId),U(()=>{var w,P;if(!l||e.popoverState.value!==0||!e.panel)return;let k=(w=f.value)==null?void 0:w.activeElement;(P=c(e.panel))!=null&&P.contains(k)||H(c(e.panel),D.First)});let g=He(),o=B(()=>g!==null?(g.value&ee.Open)===ee.Open:e.popoverState.value===0);function a(w){var P,k;switch(w.key){case A.Escape:if(e.popoverState.value!==0||!c(e.panel)||f.value&&!((P=c(e.panel))!=null&&P.contains(f.value.activeElement)))return;w.preventDefault(),w.stopPropagation(),e.closePopover(),(k=c(e.button))==null||k.focus();break}}function m(w){var P,k,p,S,v;let b=w.relatedTarget;b&&c(e.panel)&&((P=c(e.panel))!=null&&P.contains(b)||(e.closePopover(),((p=(k=c(e.beforePanelSentinel))==null?void 0:k.contains)!=null&&p.call(k,b)||(v=(S=c(e.afterPanelSentinel))==null?void 0:S.contains)!=null&&v.call(S,b))&&b.focus({preventScroll:!0})))}let u=we();function E(){let w=c(e.panel);if(!w)return;function P(){G(u.value,{[j.Forwards]:()=>{var k;H(w,D.First)===se.Error&&((k=c(e.afterPanelSentinel))==null||k.focus())},[j.Backwards]:()=>{var k;(k=c(e.button))==null||k.focus({preventScroll:!0})}})}P()}function L(){let w=c(e.panel);if(!w)return;function P(){G(u.value,{[j.Forwards]:()=>{let k=c(e.button),p=c(e.panel);if(!k)return;let S=ce(),v=S.indexOf(k),b=S.slice(0,v+1),x=[...S.slice(v+1),...b];for(let M of x.slice())if(M.dataset.headlessuiFocusGuard==="true"||p!=null&&p.contains(M)){let W=x.indexOf(M);W!==-1&&x.splice(W,1)}H(x,D.First,{sorted:!1})},[j.Backwards]:()=>{var k;H(w,D.Previous)===se.Error&&((k=c(e.button))==null||k.focus())}})}P()}return()=>{let w={open:e.popoverState.value===0,close:e.close},{id:P,focus:k,...p}=t,S={ref:e.panel,id:P,onKeydown:a,onFocusout:l&&e.popoverState.value===0?m:void 0,tabIndex:-1};return ue({ourProps:S,theirProps:{...s,...p},attrs:s,slot:w,slots:{...r,default:(...v)=>{var b;return[V(R,[o.value&&e.isPortalled.value&&V(X,{id:i,ref:e.beforePanelSentinel,features:Q.Focusable,"data-headlessui-focus-guard":!0,as:"button",type:"button",onFocus:E}),(b=r.default)==null?void 0:b.call(r,...v),o.value&&e.isPortalled.value&&V(X,{id:n,ref:e.afterPanelSentinel,features:Q.Focusable,"data-headlessui-focus-guard":!0,as:"button",type:"button",onFocus:L})])]}},features:ge.RenderStrategy|ge.Static,visible:o.value,name:"PopoverPanel"})}}});const et={key:0,class:"isolate inline-flex space-x-2 rounded-md shadow-sm"},tt={key:1,disabled:"",class:"relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-500 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"},rt={key:3,disabled:"",class:"relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-500 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"},at={key:1,class:"isolate inline-flex -space-x-px rounded-md shadow-sm","aria-label":"Pagination"},lt={class:"sr-only"},nt={key:0,class:"relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:outline-offset-0"},ot={class:"sr-only"},st={__name:"DtPagination",props:{data:{required:!0,type:Object}},setup(t){const s=t;let r=F([]),y=F({}),l=F({}),e=F([]);const f=B(()=>s.data.total==null);i(),U(()=>{i()});function i(){if(f.value)return;const o=s.data.links.map(u=>({url:u.url,label:u.label,active:u.active}));r.value=o,y.value=r.value.shift(),l.value=r.value.pop();const a=r.value.findIndex(u=>u.active),m=r.value.length;e.value=g(a+1,m)}function n(o,a){return Array(a-o+1).fill().map((m,u)=>u+o)}function g(o,a){let m=null;a<=7?m=7:m=o>4&&o<a-3?2:4;const u={start:Math.round(o-m/2),end:Math.round(o+m/2)};(u.start-1===1||u.end+1===a)&&(u.start+=1,u.end+=1);let E=o>m?n(Math.min(u.start,a-m),Math.min(u.end,a)):n(1,Math.min(a,m+1));const L=(w,P)=>E.length+1!==a?P:[w];return E[0]!==1&&(E=L(1,[1,"..."]).concat(E)),E[E.length-1]<a&&(E=E.concat(L(a,["...",a]))),E}return(o,a)=>{const m=he("InertiaLink");return f.value?(h(),I("nav",et,[s.data.prev_page_url?(h(),C(m,{key:0,href:t.data.prev_page_url,class:"relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"},{default:O(()=>[T(_(o.__("Previous")),1)]),_:1},8,["href"])):(h(),I("button",tt,_(o.__("Previous")),1)),s.data.next_page_url?(h(),C(m,{key:2,href:t.data.next_page_url,class:"relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"},{default:O(()=>[T(_(o.__("Next")),1)]),_:1},8,["href"])):(h(),I("button",rt,_(o.__("Next")),1))])):(h(),I("nav",at,[N(m,{href:$(y).url??"#",class:z(["relative disabled:bg-gray-900 inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0",[$(y).url==null?"pointer-events-none":"hover:bg-gray-50 dark:hover:bg-gray-700"]])},{default:O(()=>[d("span",lt,_(o.__("Previous")),1),N($(Re),{class:"h-5 w-5"})]),_:1},8,["href","class"]),(h(!0),I(R,null,oe($(e),(u,E)=>(h(),I(R,{key:E},[u==="..."?(h(),I("span",nt,"...")):(h(),C(m,{key:1,href:$(r)[u-1].url,class:z(["relative inline-flex items-center px-4 py-2 text-sm text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0",[$(r)[u-1].active?"bg-gray-300 dark:bg-gray-900 font-semibold":"hover:bg-gray-50 dark:hover:bg-gray-700"]])},{default:O(()=>[T(_($(r)[u-1].label),1)]),_:2},1032,["href","class"]))],64))),128)),N(m,{disabled:!$(l).url==null,href:$(l).url??"#",class:z(["relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:z-20 focus:outline-offset-0",[$(l).url==null?"pointer-events-none":"hover:bg-gray-50 dark:hover:bg-gray-700"]])},{default:O(()=>[d("span",ot,_(o.__("Next")),1),N($(Ue),{class:"h-5 w-5"})]),_:1},8,["disabled","href","class"])]))}}},it={class:"flex flex-col"},ut={id:"tableHeader",class:"flex justify-between p-4"},dt={id:"headerLeft",class:"flex"},ct={id:"searchBox"},ft={class:"relative mt-1"},vt={class:"absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"},pt=["placeholder"],gt={id:"headerRight",class:"flex"},yt={id:"resetButton"},bt={id:"tableSection",class:"flex flex-col"},mt={class:"overflow-x-auto"},ht={class:"inline-block min-w-full align-middle"},kt={class:"overflow-hidden"},xt={class:"min-w-full divide-y divide-gray-200 dark:divide-gray-700"},_t={class:"bg-gray-100 dark:bg-gray-700"},wt={class:"inline-flex items-center"},Pt={class:"mb-1 text-sm font-semibold"},St=["onUpdate:modelValue","placeholder"],Et=["disabled","onClick"],$t=["onClick"],It={class:"divide-y divide-gray-200 dark:divide-gray-700"},Ft={key:0},Lt=["colspan"],Bt={id:"tableFooter",class:"flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700"},Mt={class:"flex justify-between flex-1 sm:hidden"},Tt={class:"hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"},Nt={class:"flex items-center"},Ot=["selected"],Ct=["selected"],jt=["selected"],Vt=["selected"],Dt={key:0,class:"ml-2 text-sm text-gray-700 dark:text-gray-400"},qt={class:"font-semibold dark:text-gray-300"},At={class:"font-semibold dark:text-gray-300"},Ht={class:"font-semibold dark:text-gray-300"},Jt={__name:"DataTable",props:{data:{required:!0,type:Object},header:{required:!0,type:Array},filters:{required:!1,type:Object,default:()=>({sort:"",perPage:"",filter:{},servers:void 0})},routeParams:{required:!1,type:Object,default:()=>({})}},setup(t){const s=t,r=Be({filter:s.filters.filter??{q:""},sort:s.filters.sort??"",perPage:s.filters.perPage??10,servers:s.filters.servers??void 0});Me(r,le.throttle(n=>{let g=le.pickBy(n,le.identity);g.filter.q||delete g.filter.q,g.perPage==10&&delete g.perPage,Te.get(route(route().current(),s.routeParams),g,{replace:!0,preserveScroll:!0,preserveState:!0})},200));const y=B(()=>{if(r.sort||r.perPage!=10)return!0;for(let n in r.filter)if(r.filter[n])return!0;return!1});function l(){for(let n in r.filter)delete r.filter[n];r.filter.q="",r.sort="",r.perPage=10}const e=B(()=>r.sort?r.sort.replace("-",""):""),f=B(()=>r.sort?r.sort.startsWith("-")?"desc":"asc":"");function i(n){r.sort===n?r.sort="-"+n:r.sort==="-"+n?r.sort="":r.sort=n}return(n,g)=>{const o=he("InertiaLink");return h(),I("div",it,[d("div",ut,[d("div",dt,[d("div",ct,[d("div",ft,[d("div",vt,[N($(Ve),{class:"w-4 h-4 text-gray-500 stroke-2 dark:text-gray-400"})]),Z(d("input",{id:"table-search","onUpdate:modelValue":g[0]||(g[0]=a=>r.filter.q=a),type:"text",class:"block p-2 text-sm text-gray-900 border border-gray-300 rounded-lg pl-9 md:w-80 bg-gray-50 dark:bg-gray-900 dark:border-gray-800 dark:placeholder-gray-400 dark:text-gray-300 focus:border-light-blue-300 focus:ring focus:ring-light-blue-200 focus:ring-opacity-50",placeholder:n.__("Search..")},null,8,pt),[[ve,r.filter.q]])])])]),d("div",gt,[Z(d("div",yt,[d("button",{class:"hidden px-4 py-1 font-semibold text-gray-500 bg-white border border-gray-200 rounded md:block dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-400 dark:border-gray-800 hover:bg-gray-100",onClick:g[1]||(g[1]=a=>l())},[N($(De),{class:"inline-block w-4 h-4 text-gray-500"}),T(" "+_(n.__("Reset")),1)])],512),[[Ne,y.value]])])]),d("div",bt,[d("div",mt,[d("div",ht,[d("div",kt,[d("table",xt,[d("thead",_t,[d("tr",null,[pe(n.$slots,"header",{},()=>[(h(!0),I(R,null,oe(t.header,a=>(h(),I("th",{key:a.key,scope:"col",class:z(["px-4 py-3 text-xs font-semibold text-left text-gray-400 dark:text-gray-300",[a.class?a.class:""]])},[d("div",wt,[a.filterable?(h(),C($($e),{key:0},{default:O(()=>[N($(Qe),{class:"focus:outline-none"},{default:O(()=>[r.filter[a.filterable.key??a.key]?(h(),C(ne,{key:0,name:"funnel-fill",class:"inline-block h-4 mr-1 text-green-500 cursor-pointer dark:text-green-500 hover:text-gray-700 dark:hover:text-white"})):(h(),C(ne,{key:1,name:"funnel-outline",class:"inline-block h-4 mr-1 text-gray-400 cursor-pointer dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"}))]),_:2},1024),N(Oe,{"enter-active-class":"transition duration-200 ease-out","enter-from-class":"translate-y-1 opacity-0","enter-to-class":"translate-y-0 opacity-100","leave-active-class":"transition duration-150 ease-in","leave-from-class":"translate-y-0 opacity-100","leave-to-class":"translate-y-1 opacity-0"},{default:O(()=>[N($(Xe),{class:"absolute z-10 p-4 text-gray-800 bg-white border border-gray-200 rounded shadow dark:text-gray-300 min-w-64 dark:bg-gray-700 dark:border-gray-600"},{default:O(({close:m})=>[d("h3",Pt,_(n.__("Filter by :column",{column:a.label})),1),d("div",null,[a.filterable.type==="text"?Z((h(),I("input",{key:0,"onUpdate:modelValue":u=>r.filter[a.filterable.key??a.key]=u,class:"block w-full p-2 border-gray-200 rounded-md shadow-sm dark:bg-cool-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm",placeholder:`Enter ${a.label}...`,type:"text"},null,8,St)),[[ve,r.filter[a.filterable.key??a.key]]]):q("",!0),["multiselect","select"].includes(a.filterable.type)?(h(),C($(je),{key:1,modelValue:r.filter[a.filterable.key??a.key],"onUpdate:modelValue":u=>r.filter[a.filterable.key??a.key]=u,class:"block w-full border-gray-300 rounded-md shadow-sm focus:ring-light-blue-500 focus:border-light-blue-500 sm:text-sm",options:a.filterable.options,multiple:a.filterable.type==="multiselect","close-on-select":a.filterable.type==="select",limit:1,"clear-on-select":!1,searchable:a.filterable.searchable??!1,placeholder:`Select ${a.label}...`},null,8,["modelValue","onUpdate:modelValue","options","multiple","close-on-select","searchable","placeholder"])):q("",!0),d("button",{class:"inline-flex w-full justify-center py-1.5 px-4 mt-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none disabled:opacity-50",disabled:!r.filter[a.filterable.key??a.key],type:"button",onClick:()=>{r.filter[a.filterable.key??a.key]&&(delete r.filter[a.filterable.key??a.key],m())}},_(n.__("Clear")),9,Et)])]),_:2},1024)]),_:2},1024)]),_:2},1024)):q("",!0),d("div",{class:z(["inline-flex items-center uppercase",[a.sortable?"cursor-pointer":""]]),onClick:m=>a.sortable?i(a.key):null},[T(_(a.label)+" ",1),a.sortable?(h(),C(ne,{key:0,name:e.value===a.key?f.value==="asc"?"sort-up":"sort-down":"sort-updown",class:z(["inline-block w-3 h-3 ml-1 text-gray-400 dark:text-gray-300",[e.value===a.key?"text-light-blue-500 dark:text-light-blue-400":""]])},null,8,["name","class"])):q("",!0)],10,$t)])],2))),128))])])]),d("tbody",It,[(h(!0),I(R,null,oe(t.data.data,a=>(h(),I("tr",{key:a.id},[pe(n.$slots,"default",{item:a,data:t.data})]))),128)),t.data.data.length<=0?(h(),I("tr",Ft,[d("td",{colspan:t.header.length,class:"px-4 py-3 text-sm font-medium text-center text-gray-500 dark:text-gray-300"},_(n.__("No data found")),9,Lt)])):q("",!0)])])])])])]),d("div",Bt,[d("div",Mt,[N(o,{href:t.data.prev_page_url??"#",class:"relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 hover:bg-gray-50"},{default:O(()=>[T(_(n.__("Previous")),1)]),_:1},8,["href"]),N(o,{href:t.data.next_page_url??"#",class:"relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:border-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 hover:bg-gray-50"},{default:O(()=>[T(_(n.__("Next")),1)]),_:1},8,["href"])]),d("div",Tt,[d("div",Nt,[d("div",null,[Z(d("select",{id:"perPage","onUpdate:modelValue":g[2]||(g[2]=a=>r.perPage=a),class:"block w-full text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-light-blue-500 focus:border-light-blue-500 dark:bg-gray-900 dark:border-gray-800 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-light-blue-500 dark:focus:border-light-blue-500"},[d("option",{value:10,selected:t.data.per_page==10}," 10 "+_(n.__("per page")),9,Ot),d("option",{value:20,selected:t.data.per_page==20}," 20 "+_(n.__("per page")),9,Ct),d("option",{value:50,selected:t.data.per_page==50}," 50 "+_(n.__("per page")),9,jt),d("option",{value:100,selected:t.data.per_page==100}," 100 "+_(n.__("per page")),9,Vt)],512),[[Ce,r.perPage]])]),t.data.total!=null?(h(),I("p",Dt,[T(_(n.__("Showing"))+" ",1),d("span",qt,_(t.data.from??0),1),T(" "+_(n.__("to"))+" ",1),d("span",At,_(t.data.to??0),1),T(" "+_(n.__("of"))+" ",1),d("span",Ht,_(t.data.total),1),T(" "+_(n.__("results")),1)])):q("",!0)]),d("div",null,[t.data.next_page_url||t.data.prev_page_url?(h(),C(st,{key:0,data:t.data},null,8,["data"])):q("",!0)])])])])}}};export{Jt as _};