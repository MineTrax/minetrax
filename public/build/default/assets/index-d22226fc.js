import{u as R,$ as H,a0 as K,r as h,a1 as j,E as D,K as $,a2 as B,x as M,a3 as I,A as q}from"./app-edbaa8b4.js";function J(e){return H()?(K(e),!0):!1}function A(e){return typeof e=="function"?e():R(e)}const N=typeof window<"u"&&typeof document<"u",G=Object.prototype.toString,Q=e=>G.call(e)==="[object Object]",U=()=>{};function X(e,t){function n(...o){return new Promise((i,u)=>{Promise.resolve(e(()=>t.apply(this,o),{fn:t,thisArg:this,args:o})).then(i).catch(u)})}return n}const V=e=>e();function Y(e=V){const t=h(!0);function n(){t.value=!1}function o(){t.value=!0}const i=(...u)=>{t.value&&e(...u)};return{isActive:j(t),pause:n,resume:o,eventFilter:i}}function Z(e,t,n={}){const{eventFilter:o=V,...i}=n;return D(e,X(o,t),i)}function k(e,t,n={}){const{eventFilter:o,...i}=n,{eventFilter:u,pause:l,resume:p,isActive:c}=Y(o);return{stop:Z(e,t,{...i,eventFilter:u}),pause:l,resume:p,isActive:c}}function ee(e,t,n={}){const{immediate:o=!0}=n,i=h(!1);let u=null;function l(){u&&(clearTimeout(u),u=null)}function p(){i.value=!1,l()}function c(...f){l(),i.value=!0,u=setTimeout(()=>{i.value=!1,u=null,e(...f)},A(t))}return o&&(i.value=!0,N&&c()),J(p),{isPending:j(i),start:c,stop:p}}function te(e){var t;const n=A(e);return(t=n==null?void 0:n.$el)!=null?t:n}const E=N?window:void 0,ne=N?window.navigator:void 0;function F(...e){let t,n,o,i;if(typeof e[0]=="string"||Array.isArray(e[0])?([n,o,i]=e,t=E):[t,n,o,i]=e,!t)return U;Array.isArray(n)||(n=[n]),Array.isArray(o)||(o=[o]);const u=[],l=()=>{u.forEach(y=>y()),u.length=0},p=(y,s,w,g)=>(y.addEventListener(s,w,g),()=>y.removeEventListener(s,w,g)),c=D(()=>[te(t),A(i)],([y,s])=>{if(l(),!y)return;const w=Q(s)?{...s}:s;u.push(...n.flatMap(g=>o.map(v=>p(y,g,v,w))))},{immediate:!0,flush:"post"}),f=()=>{c(),l()};return J(f),f}function re(){const e=h(!1);return I()&&q(()=>{e.value=!0}),e}function oe(e){const t=re();return M(()=>(t.value,!!e()))}function fe(e={}){const{navigator:t=ne,read:n=!1,source:o,copiedDuring:i=1500,legacy:u=!1}=e,l=oe(()=>t&&"clipboard"in t),p=M(()=>l.value||u),c=h(""),f=h(!1),y=ee(()=>f.value=!1,i);function s(){l.value?t.clipboard.readText().then(a=>{c.value=a}):c.value=v()}p.value&&n&&F(["copy","cut"],s);async function w(a=A(o)){p.value&&a!=null&&(l.value?await t.clipboard.writeText(a):g(a),c.value=a,f.value=!0,y.start())}function g(a){const m=document.createElement("textarea");m.value=a??"",m.style.position="absolute",m.style.opacity="0",document.body.appendChild(m),m.select(),document.execCommand("copy"),m.remove()}function v(){var a,m,S;return(S=(m=(a=document==null?void 0:document.getSelection)==null?void 0:a.call(document))==null?void 0:m.toString())!=null?S:""}return{isSupported:p,text:c,copied:f,copy:w}}const O=typeof globalThis<"u"?globalThis:typeof window<"u"?window:typeof global<"u"?global:typeof self<"u"?self:{},_="__vueuse_ssr_handlers__",ie=ae();function ae(){return _ in O||(O[_]=O[_]||{}),O[_]}function ue(e,t){return ie[e]||t}function se(e){return e==null?"any":e instanceof Set?"set":e instanceof Map?"map":e instanceof Date?"date":typeof e=="boolean"?"boolean":typeof e=="string"?"string":typeof e=="object"?"object":Number.isNaN(e)?"any":"number"}const le={boolean:{read:e=>e==="true",write:e=>String(e)},object:{read:e=>JSON.parse(e),write:e=>JSON.stringify(e)},number:{read:e=>Number.parseFloat(e),write:e=>String(e)},any:{read:e=>e,write:e=>String(e)},string:{read:e=>e,write:e=>String(e)},map:{read:e=>new Map(JSON.parse(e)),write:e=>JSON.stringify(Array.from(e.entries()))},set:{read:e=>new Set(JSON.parse(e)),write:e=>JSON.stringify(Array.from(e))},date:{read:e=>new Date(e),write:e=>e.toISOString()}},x="vueuse-storage";function de(e,t,n,o={}){var i;const{flush:u="pre",deep:l=!0,listenToStorageChanges:p=!0,writeDefaults:c=!0,mergeDefaults:f=!1,shallow:y,window:s=E,eventFilter:w,onError:g=r=>{console.error(r)}}=o,v=(y?$:h)(t);if(!n)try{n=ue("getDefaultStorage",()=>{var r;return(r=E)==null?void 0:r.localStorage})()}catch(r){g(r)}if(!n)return v;const a=A(t),m=se(a),S=(i=o.serializer)!=null?i:le[m],{pause:W,resume:T}=k(v,()=>z(v.value),{flush:u,deep:l,eventFilter:w});return s&&p&&(F(s,"storage",C),F(s,x,L)),C(),v;function z(r){try{if(r==null)n.removeItem(e);else{const d=S.write(r),b=n.getItem(e);b!==d&&(n.setItem(e,d),s&&s.dispatchEvent(new CustomEvent(x,{detail:{key:e,oldValue:b,newValue:d,storageArea:n}})))}}catch(d){g(d)}}function P(r){const d=r?r.newValue:n.getItem(e);if(d==null)return c&&a!==null&&n.setItem(e,S.write(a)),a;if(!r&&f){const b=S.read(d);return typeof f=="function"?f(b,a):m==="object"&&!Array.isArray(b)?{...a,...b}:b}else return typeof d!="string"?d:S.read(d)}function L(r){C(r.detail)}function C(r){if(!(r&&r.storageArea!==n)){if(r&&r.key==null){v.value=a;return}if(!(r&&r.key!==e)){W();try{(r==null?void 0:r.newValue)!==S.write(v.value)&&(v.value=P(r))}catch(d){g(d)}finally{r?B(T):T()}}}}}export{fe as a,de as u};