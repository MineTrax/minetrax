import{X as d}from"./XSelect-2a9a783b.js";import{r as p,x as v,y as f,O as m,B as y,o as _,d as g,a as h,t as u,b as x,u as b,C as k}from"./app-219916e6.js";const S={class:"flex items-center justify-between"},B={class:"text-xl font-extrabold text-gray-800 dark:text-gray-200"},q={__name:"ServerIntelServerSelector",props:{title:{type:String,required:!0},serverList:{type:Object,required:!0},filters:{type:Object,required:!0}},setup(l){var a,o,i;const e=l;let s=p((o=(a=e.filters)==null?void 0:a.servers)!=null&&o.length?(i=e.filters)==null?void 0:i.servers[0]:null);const c=v(()=>e.filters.servers&&e.filters.servers.length>0?e.filters.servers.map(r=>e.serverList[r]).join(", "):null);return f(s,r=>{const t={servers:r?[r]:null};m.get(route(route().current()),y.pickBy(t))}),(r,t)=>(_(),g("div",S,[h("h3",B,u(l.title)+": "+u(c.value??r.__("All Servers")),1),x(d,{id:"select_servers",modelValue:b(s),"onUpdate:modelValue":t[0]||(t[0]=n=>k(s)?s.value=n:s=n),name:"select_servers","select-list":e.serverList,placeholder:r.__("All Servers"),class:"w-48 max-w-48 dark:border dark:rounded dark:border-gray-700"},null,8,["modelValue","select-list","placeholder"])]))}};export{q as _};